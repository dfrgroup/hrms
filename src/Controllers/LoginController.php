<?php

namespace App\Controllers;

use App\Models\User;
use PDOException;

/**
 * Class LoginController
 * Handles user login logic with all edge-case branches based on the provided schema,
 * now with more detailed logging to debug parameter issues.
 */
class LoginController
{
    /**
     * @var User
     */
    private User $userModel;

    /**
     * Constructor.
     *
     * @param User $userModel The User model instance for DB operations.
     */
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
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
     * Attempt to log a user in based on email and password, with extremely detailed logging.
     *
     * @param string $email      The user’s email address.
     * @param string $password   The user’s plaintext password.
     * @param string $ip         (Optional) The IP address from the request.
     * @param array  $deviceInfo (Optional) e.g., ['os'=>'Windows 10','browser'=>'Chrome']
     *
     * @return array An associative array with "success" (bool) and "message" (string).
     */
    public function login(
        string $email,
        string $password,
        string $ip = '',
        array $deviceInfo = []
    ): array {
        // If no IP is provided, try to get it from server vars
        if ($ip === '') {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        $this->logError("Login process started for email: {$email}, IP: {$ip}");

        try {
            // 1. Check if domain is blocked
            $this->logError("Checking domain blockage for email: {$email}");
            $domainBlocked = $this->userModel->isDomainBlocked($email);
            if ($domainBlocked) {
                $this->logError("Domain is blocked for email: {$email}");
                $this->userModel->logLoginAttempt(null, $ip, 'BlockedDomain', 'Failed');
                return ['success' => false, 'message' => 'Login blocked: your email domain is blocked'];
            }

            // 2. Check IP blocking
            $this->logError("Checking IP blocking for IP: {$ip}");
            $ipBlocked = $this->userModel->isIPBlocked($ip);
            if ($ipBlocked) {
                $this->logError("IP is blocked: {$ip}");
                $this->userModel->logLoginAttempt(null, $ip, 'BlockedIP', 'Failed');
                return ['success' => false, 'message' => 'Login blocked: your IP is in a blocked range'];
            }

            // 3. Check region blocking
            $this->logError("Checking region blocking for IP: {$ip}");
            $regionBlocked = $this->userModel->isRegionBlocked($ip);
            if ($regionBlocked) {
                $this->logError("Region is blocked: {$ip}");
                $this->userModel->logLoginAttempt(null, $ip, 'BlockedRegion', 'Failed');
                return ['success' => false, 'message' => 'Login blocked: your region is restricted'];
            }

            // 4. Fetch user by email
            $this->logError("Fetching user by email: {$email}");
            $user = $this->userModel->findUserByEmail($email);
        } catch (PDOException $e) {
            $this->logError("Database error during findUserByEmail: " . $e->getMessage());
            $this->logError("Error Code: " . $e->getCode());
            $this->logError("Error File: " . $e->getFile());
            $this->logError("Error Line: " . $e->getLine());
            $this->logError("Error Trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Database error occurred'];
        }

        // 5. User not found
        if (!$user) {
            $this->logError("No user found for email: {$email}");
            $this->userModel->logLoginAttempt(null, $ip, 'NoSuchUser', 'Failed');
            return ['success' => false, 'message' => 'User not found'];
        }
        $this->logError("User found: ID=" . $user['id']);

        // 6. Account status checks
        $this->logError("Check account status for user ID=" . $user['id']);
        if (!$this->userModel->checkAccountStatus($user)) {
            $this->logError("Account locked/disabled for user ID=" . $user['id']);
            $this->userModel->logLoginAttempt($user['id'], $ip, 'AccountLockedOrDisabled', 'Failed');
            return ['success' => false, 'message' => 'Account is not allowed to log in'];
        }

        // 7. Validate password
        $this->logError("Validating password for user ID=" . $user['id']);
        if (!$this->userModel->validatePassword($user, $password)) {
            $this->logError("Invalid password for user ID=" . $user['id']);
            $this->userModel->incrementFailedLoginAttempts($user);
            $this->userModel->logLoginAttempt($user['id'], $ip, 'InvalidCredentials', 'Failed');
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // 8. Check password policy
        $this->logError("Checking password policy for user ID=" . $user['id']);
        $policyCheck = $this->userModel->checkPasswordPolicy($user);
        if (!$policyCheck['pass']) {
            $reason = $policyCheck['reason'] ?? 'UnknownPolicyFail';
            $this->logError("Password policy fail for user ID=" . $user['id'] . ": {$reason}");
            $this->userModel->logLoginAttempt($user['id'], $ip, $reason, 'Failed');
            return ['success' => false, 'message' => $reason];
        }

        // 9. 2FA checks
        $this->logError("Check 2FA for user ID=" . $user['id']);
        if ($this->userModel->isTwoFactorRequired($user)) {
            $this->logError("User ID=" . $user['id'] . " requires 2FA, verifying...");
            if (!$this->userModel->handleTwoFactor($user)) {
                $this->logError("2FA failed for user ID=" . $user['id']);
                $this->userModel->logLoginAttempt($user['id'], $ip, '2FAFailed', 'Failed');
                return ['success' => false, 'message' => 'Two-Factor Authentication failed'];
            }
        }

        // 10. Risk scoring & triggers
        $this->logError("Assessing risk for user ID=" . $user['id']);
        $riskResult = $this->userModel->assessRisk($user, $ip, $deviceInfo);
        $this->logError("Risk action => " . $riskResult['action'] . " for user ID=" . $user['id']);

        if ($riskResult['action'] === 'Block') {
            $this->logError("Risk scoring => Block user ID=" . $user['id']);
            $this->userModel->logLoginAttempt($user['id'], $ip, 'RiskScoreBlock', 'Failed');
            return ['success' => false, 'message' => 'Login blocked due to high risk'];
        } elseif ($riskResult['action'] === 'Challenge') {
            $this->logError("Risk scoring => Challenge user ID=" . $user['id']);
            $this->userModel->logLoginAttempt($user['id'], $ip, 'RiskChallengeFail', 'Failed');
            return ['success' => false, 'message' => 'Additional verification needed'];
        }

        // 11. Create session
        $this->logError("Creating session for user ID=" . $user['id']);
        $sessionId = $this->userModel->createSession($user['id'], $ip, $deviceInfo);
        $this->logError("Session created => ID={$sessionId} for user ID=" . $user['id']);

        // 12. Success
        $this->logError("Login success! user ID=" . $user['id']);
        $this->userModel->logLoginAttempt($user['id'], $ip, 'LoginSuccess', 'Success', $deviceInfo);

        return [
            'success'    => true,
            'message'    => 'Login successful',
            'session_id' => $sessionId
        ];
    }
}
