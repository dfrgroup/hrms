<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Class User
 * Responsible for all user-related database operations and extended checks
 * (blocked domains, IPs, region checks, 2FA, risk scoring, session creation, etc.).
 */
class User
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor.
     *
     * @param PDO $pdo An established PDO database connection.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Custom logging function for errors and debug info.
     *
     * @param string $message
     */
    public function logError(string $message): void
    {
        $logFile   = __DIR__ . '/error.log'; // Log file in the same directory
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }

    /**
     * Retrieve a user record by email with extra debugging logs.
     *
     * @param string $email The email to search for.
     * @return array|null An associative array of user data or null if none found.
     * @throws PDOException If a database error occurs.
     */
    public function findUserByEmail(string $email): ?array
    {
        // Log the email being searched for
        $this->logError("[findUserByEmail] Preparing query for email: {$email}");

        $sql = "
            SELECT 
                ID AS id,
                Email,
                PasswordHash AS password_hash,
                created_at,
                IsEnabled AS isenabled,
                Status AS status,
                FailedLoginAttempts AS failedloginattempts,
                AccountLockout AS accountlockout,
                AccountLockoutReason AS accountlockoutreason,
                account_expires_at,
                last_password_changed_at,
                ForcePasswordReset AS forcepasswordreset,
                CannotChangePassword AS cannotchangepassword,
                PasswordNeverExpires AS passwordneverexpires,
                PasswordPolicyID AS passwordpolicyid,
                TwoFactorEnabled AS twofactorenabled,
                LogonAllowedHours AS logonallowedhours,
                LogonIfClockedInOnly AS logonifclockedinonly
            FROM Users
            WHERE Email = :email
            LIMIT 1
        ";

        $this->logError("[findUserByEmail] SQL Statement: {$sql}");

        try {
            $stmt = $this->pdo->prepare($sql);

            if (!$stmt) {
                $errorInfo = $this->pdo->errorInfo();
                $this->logError("[findUserByEmail] Failed to prepare statement: " . implode(", ", $errorInfo));
                throw new PDOException("Failed to prepare statement");
            }

            // Log the parameters being bound
            $this->logError("[findUserByEmail] Binding parameter: email={$email}");

            // Bind the parameter explicitly
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            // Execute the statement
            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $this->logError("[findUserByEmail] Failed to execute statement: " . implode(", ", $errorInfo));
                throw new PDOException("Failed to execute statement");
            }

            // Dump debug parameters
            ob_start();
            $stmt->debugDumpParams();
            $debugDump = ob_get_clean();
            $this->logError("[findUserByEmail] debugDumpParams => \n" . $debugDump);

            // Fetch the user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log the fetched user data
            $this->logError("[findUserByEmail] Fetched user: " . json_encode($user));

            return $user ?: null;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[findUserByEmail] PDOException: " . $e->getMessage());
            $this->logError("[findUserByEmail] SQLSTATE: " . $sqlState);
            $this->logError("[findUserByEmail] Driver Error Code: " . $driverErrorCode);
            $this->logError("[findUserByEmail] Driver Error Message: " . $driverErrorMessage);

            // Re-throw the exception to be caught in the controller
            throw $e;
        }
    }

    /**
     * Retrieve a user record by ID (example with a left join).
     *
     * @param string $userId The user ID (UUID string).
     * @return array|null
     * @throws PDOException
     */
    public function findUserById(string $userId): ?array
    {
        $sql = "
            SELECT 
    Users.ID AS id,
    Users.Email,
    employees.first_name,
    employees.middle_name,
    employees.last_name
FROM Users
LEFT JOIN employees ON Users.ID = employees.user_id
WHERE Users.ID = :user_id
LIMIT 1

        ";

        $this->logError("[findUserById] SQL Statement: {$sql}");
        $this->logError("[findUserById] Binding parameters: user_id={$userId}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
            $stmt->execute();

            // Dump debug parameters
            ob_start();
            $stmt->debugDumpParams();
            $debugDump = ob_get_clean();
            $this->logError("[findUserById] debugDumpParams => \n" . $debugDump);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log the fetched user data
            $this->logError("[findUserById] Fetched user: " . json_encode($user));

            return $user ?: null;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[findUserById] PDOException: " . $e->getMessage());
            $this->logError("[findUserById] SQLSTATE: " . $sqlState);
            $this->logError("[findUserById] Driver Error Code: " . $driverErrorCode);
            $this->logError("[findUserById] Driver Error Message: " . $driverErrorMessage);

            throw $e;
        }
    }

    /**
     * Create a new user in the database.
     *
     * @param string $email    User’s email address (unique).
     * @param string $password Plaintext password to be hashed.
     * @return string The newly inserted user’s ID.
     * @throws PDOException If a database error occurs (e.g., duplicate email).
     */
    public function createUser(string $email, string $password): string
    {
        // Generate a UUID v4 string
        $uuid = $this->generateUUID();

        // Hash the password using bcrypt (includes salt)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "
            INSERT INTO Users (ID, Email, PasswordHash)
            VALUES (:id, :email, :password_hash)
        ";

        $this->logError("[createUser] SQL Statement: {$sql}");
        $this->logError("[createUser] Binding parameters: id={$uuid}, email={$email}, password_hash={$hashedPassword}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $uuid, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password_hash', $hashedPassword, PDO::PARAM_STR);
            $stmt->execute();

            $this->logError("[createUser] User created successfully with ID={$uuid}.");

            return $uuid;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[createUser] PDOException: " . $e->getMessage());
            $this->logError("[createUser] SQLSTATE: " . $sqlState);
            $this->logError("[createUser] Driver Error Code: " . $driverErrorCode);
            $this->logError("[createUser] Driver Error Message: " . $driverErrorMessage);

            throw $e;
        }
    }

    /**
     * Get all users with linked employee details.
     *
     * @return array List of users.
     * @throws PDOException If a database error occurs.
     */
    public function getAllUsers(): array
    {
        $sql = "
SELECT 
    Users.ID AS id,
    Users.Email,
    employees.first_name,
    employees.middle_name,
    employees.last_name,
    Users.UserType
FROM Users
LEFT JOIN employees ON Users.ID = employees.user_id
ORDER BY Users.created_at DESC

        ";

        $this->logError("[getAllUsers] SQL Statement: {$sql}");
        $this->logError("[getAllUsers] Executing query to fetch all users with employee details.");

        try {
            $stmt = $this->pdo->query($sql);

            // Dump debug parameters (none in this case)
            ob_start();
            $stmt->debugDumpParams();
            $debugDump = ob_get_clean();
            $this->logError("[getAllUsers] debugDumpParams => \n" . $debugDump);

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log the fetched users
            $this->logError("[getAllUsers] Fetched users: " . json_encode($users));

            return $users;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[getAllUsers] PDOException: " . $e->getMessage());
            $this->logError("[getAllUsers] SQLSTATE: " . $sqlState);
            $this->logError("[getAllUsers] Driver Error Code: " . $driverErrorCode);
            $this->logError("[getAllUsers] Driver Error Message: " . $driverErrorMessage);

            throw $e;
        }
    }

    /* ----------------------------------------------------------------
     * ADDED METHODS FOR COMPLETE EDGE-CASE BRANCHES & LOGIC
     * ---------------------------------------------------------------- */

    /**
     * Check if the user’s email domain is blocked.
     *
     * @param string $email
     * @return bool
     */
    public function isDomainBlocked(string $email): bool
    {
        $domainPart = strtolower(substr(strrchr($email, '@'), 1)); // e.g., "example.com"

        $sql = "
            SELECT 1
            FROM BlockedDomains
            WHERE DomainName = :domain
            LIMIT 1
        ";

        $this->logError("[isDomainBlocked] SQL Statement: {$sql}");
        $this->logError("[isDomainBlocked] Binding parameters: domain={$domainPart}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':domain', $domainPart, PDO::PARAM_STR);
            $stmt->execute();

            $isBlocked = (bool) $stmt->fetchColumn();
            $this->logError("[isDomainBlocked] Domain '{$domainPart}' blocked: " . ($isBlocked ? 'Yes' : 'No'));

            return $isBlocked;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log the error details
            $this->logError("[isDomainBlocked] PDOException: " . $e->getMessage());
            $this->logError("[isDomainBlocked] SQLSTATE: " . $sqlState);
            $this->logError("[isDomainBlocked] Driver Error Code: " . $driverErrorCode);
            $this->logError("[isDomainBlocked] Driver Error Message: " . $driverErrorMessage);

            // Depending on your logic, you might want to treat this as not blocked or handle differently
            return false;
        }
    }

    /**
 * Check if the given IP is in a blocked range.
 *
 * @param string $ip (IPv4 or IPv6 string)
 * @return bool
 */
public function isIPBlocked(string $ip): bool
{
    $binaryIP = inet_pton($ip);
    if ($binaryIP === false) {
        $this->logError("[isIPBlocked] Invalid IP address format: {$ip}");
        return false; // Or handle as needed
    }

    $sql = "
        SELECT 1
        FROM BlockedIPs
        WHERE 
            :binaryIP_start >= IPAddressStart
            AND
            (:binaryIP_end <= IPAddressEnd OR IPAddressEnd IS NULL)
        LIMIT 1
    ";

    $this->logError("[isIPBlocked] SQL Statement: {$sql}");
    $this->logError("[isIPBlocked] Binding parameters: binaryIP_start=" . bin2hex($binaryIP) . ", binaryIP_end=" . bin2hex($binaryIP));

    try {
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt) {
            $errorInfo = $this->pdo->errorInfo();
            $this->logError("[isIPBlocked] Failed to prepare statement: " . implode(", ", $errorInfo));
            throw new PDOException("Failed to prepare statement");
        }

        // Bind unique placeholders with binary IP
        $stmt->bindParam(':binaryIP_start', $binaryIP, PDO::PARAM_LOB);
        $stmt->bindParam(':binaryIP_end', $binaryIP, PDO::PARAM_LOB);

        // Execute the statement
        $result = $stmt->execute();

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            $this->logError("[isIPBlocked] Failed to execute statement: " . implode(", ", $errorInfo));
            throw new PDOException("Failed to execute statement");
        }

        // Dump debug parameters
        ob_start();
        $stmt->debugDumpParams();
        $debugDump = ob_get_clean();
        $this->logError("[isIPBlocked] debugDumpParams => \n" . $debugDump);

        $isBlocked = (bool) $stmt->fetchColumn();
        $this->logError("[isIPBlocked] IP '{$ip}' blocked: " . ($isBlocked ? 'Yes' : 'No'));

        return $isBlocked;
    } catch (PDOException $e) {
        // Safely access errorInfo elements
        $errorInfo = $e->errorInfo;
        $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
        $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
        $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

        // Log detailed exception information
        $this->logError("[isIPBlocked] PDOException: " . $e->getMessage());
        $this->logError("[isIPBlocked] SQLSTATE: " . $sqlState);
        $this->logError("[isIPBlocked] Driver Error Code: " . $driverErrorCode);
        $this->logError("[isIPBlocked] Driver Error Message: " . $driverErrorMessage);

        // Depending on your logic, decide how to handle this
        return false;
    }
}

/**
 * Check if the IP falls into a blocked region range.
 *
 * @param string $ip
 * @return bool
 */
public function isRegionBlocked(string $ip): bool
{
    $countryCode = $this->geolocateCountryCode($ip);
    if (!$countryCode) {
        $this->logError("[isRegionBlocked] Could not geolocate country code for IP: {$ip}");
        return false; // Or handle as needed
    }

    $binaryIP = inet_pton($ip);
    if ($binaryIP === false) {
        $this->logError("[isRegionBlocked] Invalid IP address format: {$ip}");
        return false; // Or handle as needed
    }

    $sql = "
        SELECT 1
        FROM BlockedRegions
        WHERE 
            CountryCode = :cc
            OR
            (
                :binaryIP_start >= IPRangeStart
                AND :binaryIP_end <= IPRangeEnd
            )
        LIMIT 1
    ";

    $this->logError("[isRegionBlocked] SQL Statement: {$sql}");
    $this->logError("[isRegionBlocked] Binding parameters: cc={$countryCode}, binaryIP_start=" . bin2hex($binaryIP) . ", binaryIP_end=" . bin2hex($binaryIP));

    try {
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt) {
            $errorInfo = $this->pdo->errorInfo();
            $this->logError("[isRegionBlocked] Failed to prepare statement: " . implode(", ", $errorInfo));
            throw new PDOException("Failed to prepare statement");
        }

        // Bind unique placeholders with binary IP
        $stmt->bindParam(':cc', $countryCode, PDO::PARAM_STR);
        $stmt->bindParam(':binaryIP_start', $binaryIP, PDO::PARAM_LOB);
        $stmt->bindParam(':binaryIP_end', $binaryIP, PDO::PARAM_LOB);

        // Execute the statement
        $result = $stmt->execute();

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            $this->logError("[isRegionBlocked] Failed to execute statement: " . implode(", ", $errorInfo));
            throw new PDOException("Failed to execute statement");
        }

        // Dump debug parameters
        ob_start();
        $stmt->debugDumpParams();
        $debugDump = ob_get_clean();
        $this->logError("[isRegionBlocked] debugDumpParams => \n" . $debugDump);

        $isBlocked = (bool) $stmt->fetchColumn();
        $this->logError("[isRegionBlocked] Region blocked for IP '{$ip}' (CountryCode: {$countryCode}): " . ($isBlocked ? 'Yes' : 'No'));

        return $isBlocked;
    } catch (PDOException $e) {
        // Safely access errorInfo elements
        $errorInfo = $e->errorInfo;
        $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
        $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
        $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

        // Log detailed exception information
        $this->logError("[isRegionBlocked] PDOException: " . $e->getMessage());
        $this->logError("[isRegionBlocked] SQLSTATE: " . $sqlState);
        $this->logError("[isRegionBlocked] Driver Error Code: " . $driverErrorCode);
        $this->logError("[isRegionBlocked] Driver Error Message: " . $driverErrorMessage);

        // Depending on your logic, decide how to handle this
        return false;
    }
}


    /**
     * Example geolocation to get a country code. In practice, use a real service or DB.
     *
     * @param string $ip
     * @return string|null Country code or null if not resolved
     */
    private function geolocateCountryCode(string $ip): ?string
    {
        // Stub for demonstration
        // In reality, you'd integrate with a geolocation service or database
        // For example purposes, let's assume all IPs are from 'US'
        return 'US';
    }

    /**
     * Log a login attempt in the LoginHistory table.
     *
     * @param string|null $userId        The user ID (UUID string or null if user unknown).
     * @param string      $ip            The plain-text IP.
     * @param string      $failureReason The reason for failure or 'LoginSuccess'.
     * @param string      $status        'Success' or 'Failed'.
     * @param array       $deviceInfo    Optional device details.
     */
    public function logLoginAttempt(?string $userId, string $ip, string $failureReason, string $status, array $deviceInfo = []): void
    {
        try {
            $geoJson = json_encode($deviceInfo['geo'] ?? null); // Handle optional geo data
            $device = $deviceInfo['device'] ?? ($deviceInfo['browser'] ?? null);
    
            $sql = "
                INSERT INTO LoginHistory 
                    (UserID, IP_Address, Device, Status, FailureReason, GeoLocation)
                VALUES 
                    (:user, :ip, :dev, :status, :reason, :geo)
            ";
    
            $stmt = $this->pdo->prepare($sql);
    
            $stmt->bindParam(':user', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindParam(':dev', $device, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':reason', $failureReason, PDO::PARAM_STR);
            $stmt->bindParam(':geo', $geoJson, PDO::PARAM_STR);
    
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Failed to log login attempt: " . $e->getMessage());
        }
    }
    

    /**
     * Check account status (IsEnabled, locked, suspended, expired, etc.).
     *
     * @param array $user The user row
     * @return bool True if okay to proceed, false if blocked
     */
    public function checkAccountStatus(array $user): bool
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[checkAccountStatus] Undefined array key 'id'. User data: " . json_encode($user));
            return false;
        }

        // isEnabled check
        if (!(bool) $user['isenabled']) {
            $this->logError("[checkAccountStatus] Account is disabled for user ID=" . $user['id']);
            return false;
        }

        // status check
        if (in_array($user['status'], ['Locked', 'Suspended', 'Disabled', 'Pending', 'Other'], true)) {
            $this->logError("[checkAccountStatus] Account status '{$user['status']}' is not allowed for user ID=" . $user['id']);
            return false;
        }

        // account lockout
        if ((bool) $user['accountlockout']) {
            $this->logError("[checkAccountStatus] Account is locked out for user ID=" . $user['id']);
            return false;
        }

        // expiration check
        if ($user['account_expires_at'] !== null) {
            $expiresAt = strtotime($user['account_expires_at']);
            if ($expiresAt !== false && time() > $expiresAt) {
                $this->logError("[checkAccountStatus] Account expired at {$user['account_expires_at']} for user ID=" . $user['id']);
                return false;
            }
        }

        // All checks passed
        $this->logError("[checkAccountStatus] Account is active for user ID=" . $user['id']);
        return true;
    }

    /**
     * Validate password with stored hash.
     *
     * @param array  $user     The user row
     * @param string $password Plaintext password from user input
     * @return bool
     */
    public function validatePassword(array $user, string $password): bool
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[validatePassword] Undefined array key 'id'. User data: " . json_encode($user));
            return false;
        }

        // Validate the password using password_verify
        $isValid = password_verify($password, $user['password_hash']);
        $this->logError("[validatePassword] Password validation for user ID=" . $user['id'] . " => " . ($isValid ? 'Valid' : 'Invalid'));
        return $isValid;
    }

    /**
     * Increment user’s failed login attempts and possibly set lockout.
     *
     * @param array $user The user row
     */
    public function incrementFailedLoginAttempts(array $user): void
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[incrementFailedLoginAttempts] Undefined array key 'id'. User data: " . json_encode($user));
            return;
        }

        $newCount = (int)$user['failedloginattempts'] + 1;

        // Example lock threshold = 5 attempts
        $lock = ($newCount >= 5);

        $sql = "
            UPDATE Users
               SET FailedLoginAttempts = :count,
                   last_failed_login_at = NOW(),
                   AccountLockout = :lock,
                   AccountLockoutReason = IF(:lock=TRUE,'FailedLogins',NULL)
             WHERE ID = :uid
        ";

        $this->logError("[incrementFailedLoginAttempts] SQL Statement: {$sql}");
        $this->logError("[incrementFailedLoginAttempts] Binding parameters: count={$newCount}, lock=" . ($lock ? '1' : '0') . ", uid={$user['id']}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':count', $newCount, PDO::PARAM_INT);
            $stmt->bindParam(':lock', $lock, PDO::PARAM_BOOL);
            $stmt->bindParam(':uid', $user['id'], PDO::PARAM_STR);
            $stmt->execute();

            $this->logError("[incrementFailedLoginAttempts] Failed login attempts updated to {$newCount} for user ID=" . $user['id'] . ". Lockout: " . ($lock ? 'Yes' : 'No'));
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[incrementFailedLoginAttempts] PDOException: " . $e->getMessage());
            $this->logError("[incrementFailedLoginAttempts] SQLSTATE: " . $sqlState);
            $this->logError("[incrementFailedLoginAttempts] Driver Error Code: " . $driverErrorCode);
            $this->logError("[incrementFailedLoginAttempts] Driver Error Message: " . $driverErrorMessage);

            // Depending on your logic, decide how to handle this
        }
    }

    /**
     * Check advanced password policies (expiration, forced reset, etc.).
     *
     * @param array $user
     * @return array ['pass' => bool, 'reason' => string]
     */
    public function checkPasswordPolicy(array $user): array
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[checkPasswordPolicy] Undefined array key 'id'. User data: " . json_encode($user));
            return ['pass' => false, 'reason' => 'UndefinedUserID'];
        }

        // If user has forced password reset
        if ((bool) $user['forcepasswordreset']) {
            $this->logError("[checkPasswordPolicy] User ID=" . $user['id'] . " is forced to reset password.");
            return ['pass' => false, 'reason' => 'ForcePasswordReset'];
        }

        // If user cannot change password but is forced? Contradiction
        if ((bool) $user['cannotchangepassword'] && (bool) $user['forcepasswordreset']) {
            $this->logError("[checkPasswordPolicy] User ID=" . $user['id'] . " cannot change password but is forced to reset.");
            return ['pass' => false, 'reason' => 'CannotChangePasswordButNeeded'];
        }

        // If user password never expires, skip
        if ((bool) $user['passwordneverexpires']) {
            $this->logError("[checkPasswordPolicy] User ID=" . $user['id'] . " password never expires.");
            return ['pass' => true, 'reason' => 'NoExpiry'];
        }

        // Otherwise check policy
        if ($user['passwordpolicyid'] !== null) {
            // Retrieve policy
            $policy = $this->getPasswordPolicy((int)$user['passwordpolicyid']);
            if ($policy && $policy['MaxAgeDays'] !== null) {
                if ($user['last_password_changed_at']) {
                    $changedAt = strtotime($user['last_password_changed_at']);
                    $maxAgeSec = (int)$policy['MaxAgeDays'] * 86400;
                    if ($changedAt !== false && time() > ($changedAt + $maxAgeSec)) {
                        // Password is expired
                        $this->logError("[checkPasswordPolicy] User ID=" . $user['id'] . " password expired.");
                        return ['pass' => false, 'reason' => 'PasswordExpired'];
                    }
                }
            }
        }

        // If we pass all checks
        $this->logError("[checkPasswordPolicy] User ID=" . $user['id'] . " passed password policy.");
        return ['pass' => true, 'reason' => 'OK'];
    }

    /**
     * Helper to get password policy row.
     *
     * @param int $policyId
     * @return array|null
     */
    private function getPasswordPolicy(int $policyId): ?array
    {
        $sql = "
            SELECT *
            FROM PasswordPolicies
            WHERE PolicyID = :pid
            LIMIT 1
        ";

        $this->logError("[getPasswordPolicy] SQL Statement: {$sql}");
        $this->logError("[getPasswordPolicy] Binding parameters: pid={$policyId}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':pid', $policyId, PDO::PARAM_INT);
            $stmt->execute();

            // Dump debug parameters
            ob_start();
            $stmt->debugDumpParams();
            $debugDump = ob_get_clean();
            $this->logError("[getPasswordPolicy] debugDumpParams => \n" . $debugDump);

            $policy = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($policy) {
                $this->logError("[getPasswordPolicy] Retrieved policy ID={$policyId}: " . json_encode($policy));
            } else {
                $this->logError("[getPasswordPolicy] No policy found for ID={$policyId}");
            }

            return $policy ?: null;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[getPasswordPolicy] PDOException: " . $e->getMessage());
            $this->logError("[getPasswordPolicy] SQLSTATE: " . $sqlState);
            $this->logError("[getPasswordPolicy] Driver Error Code: " . $driverErrorCode);
            $this->logError("[getPasswordPolicy] Driver Error Message: " . $driverErrorMessage);

            // Depending on your logic, decide how to handle this
            return null;
        }
    }

    /**
     * Determine if 2FA is required for the user.
     *
     * @param array $user
     * @return bool
     */
    public function isTwoFactorRequired(array $user): bool
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[isTwoFactorRequired] Undefined array key 'id'. User data: " . json_encode($user));
            return false;
        }

        $required = (bool) $user['twofactorenabled'];
        $this->logError("[isTwoFactorRequired] 2FA required for user ID=" . $user['id'] . ": " . ($required ? 'Yes' : 'No'));
        return $required;
    }

    /**
     * Handle the 2FA flow (stubbed).
     * Typically, you'd check TwoFactorEnrollments or send a token.
     *
     * @param array $user
     * @return bool True if 2FA successful
     */
    public function handleTwoFactor(array $user): bool
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[handleTwoFactor] Undefined array key 'id'. User data: " . json_encode($user));
            return false;
        }

        // Example stub: In a real system, implement actual 2FA verification
        $this->logError("[handleTwoFactor] Handling 2FA for user ID=" . $user['id']);

        // For demonstration purposes, assume success
        $isSuccess = true;
        $this->logError("[handleTwoFactor] 2FA " . ($isSuccess ? 'successful' : 'failed') . " for user ID=" . $user['id']);
        return $isSuccess;
    }

    /**
     * Risk assessment & scoring, plus triggers.
     *
     * @param array  $user
     * @param string $ip
     * @param array  $deviceInfo
     * @return array e.g. ['score' => 12.5, 'action' => 'Allow'|'Challenge'|'Block']
     */
    public function assessRisk(array $user, string $ip, array $deviceInfo): array
    {
        // Check if 'id' key exists
        if (!isset($user['id'])) {
            $this->logError("[assessRisk] Undefined array key 'id'. User data: " . json_encode($user));
            return ['score' => 0.0, 'action' => 'Allow'];
        }

        $score = 0.0;

        // Example simplistic scoring:
        if ((int)$user['failedloginattempts'] > 3) {
            $score += 20.0;
        }

        // If device is brand new, up the risk (stub)
        if (!empty($deviceInfo['device_id'])) {
            // Check if device is trusted
            // For demonstration, assume all devices are trusted
            // Implement actual device trust logic here
            $score += 5.0;
        }

        // Additional factors can be added here

        // Decide action based on score
        $action = 'Allow';
        if ($score > 80) {
            $action = 'Block';
        } elseif ($score > 50) {
            $action = 'Challenge';
        }

        // Log the risk assessment
        $this->logError("[assessRisk] User ID=" . $user['id'] . " Risk Score={$score}, Action={$action}");

        // Insert into RiskScoring
        $this->logRiskScore($user['id'], $score, $action);

        return [
            'score'  => $score,
            'action' => $action
        ];
    }

    /**
     * Insert a row into RiskScoring table.
     *
     * @param string $userId
     * @param float  $score
     * @param string $action
     */
    private function logRiskScore(string $userId, float $score, string $action): void
    {
        $sql = "
            INSERT INTO RiskScoring
                (UserID, SessionID, RiskScore, RiskFactors, ActionRecommended)
            VALUES
                (:uid, NULL, :scr, :factors, :act)
        ";

        $riskFactorsJson = json_encode(['reason' => 'Demo scoring']);

        $this->logError("[logRiskScore] SQL Statement: {$sql}");
        $this->logError("[logRiskScore] Binding parameters: uid={$userId}, scr={$score}, factors={$riskFactorsJson}, act={$action}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':uid', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':scr', $score, PDO::PARAM_STR);
            $stmt->bindParam(':factors', $riskFactorsJson, PDO::PARAM_STR);
            $stmt->bindParam(':act', $action, PDO::PARAM_STR);
            $stmt->execute();

            $this->logError("[logRiskScore] Risk score logged successfully for user ID={$userId}.");
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[logRiskScore] PDOException: " . $e->getMessage());
            $this->logError("[logRiskScore] SQLSTATE: " . $sqlState);
            $this->logError("[logRiskScore] Driver Error Code: " . $driverErrorCode);
            $this->logError("[logRiskScore] Driver Error Message: " . $driverErrorMessage);

            // Depending on your logic, decide how to handle this
        }
    }

    /**
     * Create a session record in the Sessions table after successful login.
     *
     * @param string $userId
     * @param string $ip
     * @param array  $deviceInfo
     * @return int Session ID
     */
    public function createSession(string $userId, string $ip, array $deviceInfo = []): int
    {
        // Check if 'id' key exists
        if (empty($userId)) {
            $this->logError("[createSession] Undefined or null user ID. Parameters: uid={$userId}, ip={$ip}, deviceInfo=" . json_encode($deviceInfo));
            return 0; // Or handle as needed
        }

        $binaryIP = inet_pton($ip);

        // Possibly create or look up DeviceFingerprints
        $deviceID = $this->lookupOrCreateDevice($userId, $deviceInfo);

        $sql = "
            INSERT INTO Sessions (UserID, IP_Address, DeviceID, Status)
            VALUES (:uid, :ip, :dev, 'Active')
        ";

        $this->logError("[createSession] SQL Statement: {$sql}");
        $this->logError("[createSession] Binding parameters: uid={$userId}, ip=" . bin2hex($binaryIP) . ", dev={$deviceID}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':uid', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':ip', $binaryIP, PDO::PARAM_LOB); // Assuming VARBINARY(16)
            $stmt->bindParam(':dev', $deviceID, PDO::PARAM_INT);
            $stmt->execute();

            // Retrieve the newly created session's ID
            $sessionId = (int)$this->pdo->lastInsertId();
            $this->logError("[createSession] Session created with ID={$sessionId} for user ID={$userId}");

            return $sessionId;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[createSession] PDOException: " . $e->getMessage());
            $this->logError("[createSession] SQLSTATE: " . $sqlState);
            $this->logError("[createSession] Driver Error Code: " . $driverErrorCode);
            $this->logError("[createSession] Driver Error Message: " . $driverErrorMessage);

            // Depending on your logic, decide how to handle this
            return 0; // Or throw exception
        }
    }

    /**
     * Find or create a record in DeviceFingerprints for the user’s device.
     *
     * @param string $userId
     * @param array  $deviceInfo
     * @return int|null DeviceID
     */
    private function lookupOrCreateDevice(string $userId, array $deviceInfo): ?int
    {
        if (empty($deviceInfo['device_id'])) {
            $this->logError("[lookupOrCreateDevice] No device_id provided for user ID={$userId}. Skipping device fingerprinting.");
            return null;
        }

        $sql = "
            SELECT DeviceID
            FROM DeviceFingerprints
            WHERE UserID = :uid
              AND DeviceIdentifier = :did
            LIMIT 1
        ";

        $this->logError("[lookupOrCreateDevice] SQL Statement: {$sql}");
        $this->logError("[lookupOrCreateDevice] Binding parameters: uid={$userId}, did={$deviceInfo['device_id']}");

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':uid', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':did', $deviceInfo['device_id'], PDO::PARAM_STR);
            $stmt->execute();

            $existing = $stmt->fetchColumn();

            if ($existing) {
                // Update last_seen_at
                $updateSql = "
                    UPDATE DeviceFingerprints
                       SET Last_seen_at = NOW()
                     WHERE DeviceID = :did
                ";

                $this->logError("[lookupOrCreateDevice] Existing device fingerprint found: DeviceID={$existing} for user ID={$userId}");
                $this->logError("[lookupOrCreateDevice] SQL Statement: {$updateSql}");
                $this->logError("[lookupOrCreateDevice] Binding parameters: did={$existing}");

                $upd = $this->pdo->prepare($updateSql);
                $upd->bindParam(':did', $existing, PDO::PARAM_INT);
                $upd->execute();

                return (int)$existing;
            }

            // Otherwise create new device fingerprint record
            $deviceType = $deviceInfo['type'] ?? 'Other';
            $os         = $deviceInfo['os']   ?? 'Unknown OS';
            $browser    = $deviceInfo['browser'] ?? null;
            $identifier = $deviceInfo['device_id'];

            $insertSql = "
                INSERT INTO DeviceFingerprints
                    (UserID, DeviceType, OperatingSystem, Browser, DeviceIdentifier)
                VALUES
                    (:uid, :dt, :os, :br, :di)
            ";

            $this->logError("[lookupOrCreateDevice] SQL Statement: {$insertSql}");
            $this->logError("[lookupOrCreateDevice] Binding parameters: uid={$userId}, dt={$deviceType}, os={$os}, br={$browser}, di={$identifier}");

            $stmt = $this->pdo->prepare($insertSql);
            $stmt->bindParam(':uid', $userId, PDO::PARAM_STR);
            $stmt->bindParam(':dt', $deviceType, PDO::PARAM_STR);
            $stmt->bindParam(':os', $os, PDO::PARAM_STR);
            $stmt->bindParam(':br', $browser, PDO::PARAM_STR);
            $stmt->bindParam(':di', $identifier, PDO::PARAM_STR);
            $stmt->execute();

            // Retrieve the newly created device's ID
            $newDeviceId = (int)$this->pdo->lastInsertId();
            $this->logError("[lookupOrCreateDevice] New device fingerprint created: DeviceID={$newDeviceId} for user ID={$userId}");

            return $newDeviceId;
        } catch (PDOException $e) {
            // Safely access errorInfo elements
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0] ?? 'Unknown SQLSTATE';
            $driverErrorCode = $errorInfo[1] ?? 'Unknown Driver Error Code';
            $driverErrorMessage = $errorInfo[2] ?? 'Unknown Driver Error Message';

            // Log detailed exception information
            $this->logError("[lookupOrCreateDevice] PDOException: " . $e->getMessage());
            $this->logError("[lookupOrCreateDevice] SQLSTATE: " . $sqlState);
            $this->logError("[lookupOrCreateDevice] Driver Error Code: " . $driverErrorCode);
            $this->logError("[lookupOrCreateDevice] Driver Error Message: " . $driverErrorMessage);

            // Depending on your logic, decide how to handle this
            return null;
        }
    }
}
