-- ---------------------------------------------------------------------
-- FULL FIXED SCRIPT WITH ENGINE=INNODB AND UUIDs AS CHAR(36)
-- This resolves the "Foreign key constraint is incorrectly formed" error
-- by ensuring that:
--   1) The Users table is created before DeviceFingerprints.
--   2) All UUIDs are stored as CHAR(36) strings.
--   3) All foreign key references match the updated UUID format.
-- ---------------------------------------------------------------------

-- --------------------------
-- CREATE TABLE PasswordPolicies
-- --------------------------
CREATE TABLE PasswordPolicies (
    PolicyID TINYINT UNSIGNED PRIMARY KEY,
    PolicyName VARCHAR(50) NOT NULL,
    MinLength TINYINT UNSIGNED NOT NULL,
    MaxLength TINYINT UNSIGNED DEFAULT NULL,
    Complexity JSON NOT NULL, -- e.g., rules: {"uppercase": true, "special": true}
    MaxAgeDays TINYINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE DataRetentionPolicies
-- --------------------------
CREATE TABLE DataRetentionPolicies (
    PolicyID TINYINT UNSIGNED PRIMARY KEY,
    PolicyName VARCHAR(50) NOT NULL,
    RetentionPeriodDays INT UNSIGNED DEFAULT NULL, -- Number of days before data is deleted
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE Users
-- --------------------------
CREATE TABLE Users (
    ID CHAR(36) PRIMARY KEY NOT NULL,
    Initials VARCHAR(2),
    DisplayName VARCHAR(50),
    Email VARCHAR(255) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    PasswordSalt BINARY(16) NOT NULL, -- Consider removing if using password_hash()
    last_login_at DATETIME DEFAULT NULL,
    FailedLoginAttempts TINYINT UNSIGNED DEFAULT 0 NOT NULL,
    CHECK (FailedLoginAttempts >= 0 AND FailedLoginAttempts <= 255), -- numeric CHECK
    account_expires_at DATETIME DEFAULT NULL,
    UserProfilePhoto VARCHAR(255) DEFAULT NULL,
    UserType ENUM('Admin', 'Manager', 'Employee', 'Guest') DEFAULT 'Guest' NOT NULL,
    CreationType ENUM('Manual - Admin', 'Manual - Manager', 'SSO', 'API', 'Other') DEFAULT 'Other' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    last_password_changed_at DATETIME DEFAULT NULL,
    PasswordPolicyID TINYINT UNSIGNED DEFAULT NULL,
    PreferredLanguage CHAR(2) DEFAULT 'en',
    IsEnabled BOOLEAN DEFAULT TRUE NOT NULL,
    Status ENUM('Active', 'Locked', 'Suspended', 'Pending', 'Disabled', 'Other') DEFAULT 'Active' NOT NULL,
    LogonAllowedHours JSON DEFAULT NULL,
    LogonIfClockedInOnly BOOLEAN DEFAULT FALSE,
    ForcePasswordReset BOOLEAN DEFAULT FALSE,
    CannotChangePassword BOOLEAN DEFAULT FALSE,
    PasswordNeverExpires BOOLEAN DEFAULT FALSE,
    LastIP VARBINARY(16) DEFAULT NULL, -- IP in binary form
    TwoFactorEnabled BOOLEAN DEFAULT FALSE NOT NULL,
    TimeZone VARCHAR(50) DEFAULT 'UTC' NOT NULL,
    last_failed_login_at DATETIME DEFAULT NULL,
    AccountLockout BOOLEAN DEFAULT FALSE,
    AccountLockoutReason ENUM('FailedLogins', 'Manual', 'Fraud') DEFAULT NULL,
    CustomAttributes JSON DEFAULT NULL,
    LastLoginGeoLocation JSON DEFAULT NULL,
    DataRetentionPolicy TINYINT UNSIGNED DEFAULT NULL,
    FOREIGN KEY (PasswordPolicyID) REFERENCES PasswordPolicies(PolicyID),
    FOREIGN KEY (DataRetentionPolicy) REFERENCES DataRetentionPolicies(PolicyID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO Users (
    ID, 
    Initials, 
    DisplayName, 
    Email, 
    PasswordHash, 
    PasswordSalt, 
    last_login_at, 
    FailedLoginAttempts, 
    account_expires_at, 
    UserProfilePhoto, 
    UserType, 
    CreationType, 
    created_at, 
    last_password_changed_at, 
    PasswordPolicyID, 
    PreferredLanguage, 
    IsEnabled, 
    Status, 
    LogonAllowedHours, 
    LogonIfClockedInOnly, 
    ForcePasswordReset, 
    CannotChangePassword, 
    PasswordNeverExpires, 
    LastIP, 
    TwoFactorEnabled, 
    TimeZone, 
    last_failed_login_at, 
    AccountLockout, 
    AccountLockoutReason, 
    CustomAttributes, 
    LastLoginGeoLocation, 
    DataRetentionPolicy
) VALUES (
    '6eb1db22-30f2-42f7-8382-be4e9b172829', -- ID
    NULL,                                  -- Initials
    NULL,                                  -- DisplayName
    'anthonyhud843@outlook.com',           -- Email
    '$2y$10$seEjkUliq3ThKxcfubnjCOyJz67tijm25PdJYPfcXNK...', -- PasswordHash
    0x00000000000000000000000000000000,    -- PasswordSalt
    NULL,                                  -- last_login_at
    0,                                     -- FailedLoginAttempts
    NULL,                                  -- account_expires_at
    NULL,                                  -- UserProfilePhoto
    'Admin',                               -- UserType
    'Other',                               -- CreationType
    '2025-01-05 13:19:53',                 -- created_at
    NULL,                                  -- last_password_changed_at
    NULL,                                  -- PasswordPolicyID
    'en',                                  -- PreferredLanguage
    1,                                     -- IsEnabled
    'Active',                              -- Status
    NULL,                                  -- LogonAllowedHours
    0,                                     -- LogonIfClockedInOnly
    0,                                     -- ForcePasswordReset
    0,                                     -- CannotChangePassword
    0,                                     -- PasswordNeverExpires
    NULL,                                  -- LastIP
    0,                                     -- TwoFactorEnabled
    'UTC',                                 -- TimeZone
    NULL,                                  -- last_failed_login_at
    0,                                     -- AccountLockout
    NULL,                                  -- AccountLockoutReason
    NULL,                                  -- CustomAttributes
    NULL,                                  -- LastLoginGeoLocation
    NULL                                   -- DataRetentionPolicy
);

-- --------------------------
-- INSERT SAMPLE DATA INTO PasswordPolicies
-- --------------------------
INSERT INTO PasswordPolicies (PolicyID, PolicyName, MinLength, MaxLength, Complexity, MaxAgeDays)
VALUES
    (1, 'Default Policy', 8, 16, '{"uppercase": true, "special": true}', 90),
    (2, 'Strict Policy', 12, 20, '{"uppercase": true, "special": true, "numbers": true}', 60);

-- --------------------------
-- INSERT SAMPLE DATA INTO DataRetentionPolicies
-- --------------------------
INSERT INTO DataRetentionPolicies (PolicyID, PolicyName, RetentionPeriodDays)
VALUES
    (1, 'Standard Retention', 365),
    (2, 'Extended Retention', 730);

-- --------------------------
-- CREATE TABLE LoginHistory
-- --------------------------
CREATE TABLE LoginHistory (
    LoginID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    login_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARBINARY(16) NOT NULL,
    Device VARCHAR(255) DEFAULT NULL,
    LoginMethod ENUM('Password', 'TOTP', 'Push', 'Biometric') DEFAULT 'Password',
    Status ENUM('Success', 'Failed') NOT NULL,
    FailureReason VARCHAR(255) DEFAULT NULL,
    GeoLocation JSON DEFAULT NULL,
    -- Generated column + index for JSON example:
    GeoCountry CHAR(2) AS (JSON_UNQUOTE(JSON_EXTRACT(GeoLocation, '$.country'))) VIRTUAL,
    KEY idx_geo_country (GeoCountry),
    FOREIGN KEY (UserID) REFERENCES Users(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE TwoFactorEnrollments
-- --------------------------
CREATE TABLE TwoFactorEnrollments (
    EnrollmentID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    MethodType ENUM('TOTP', 'SMS', 'Email', 'Push', 'SecurityKey', 'Biometric') NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME DEFAULT NULL,
    IsPrimary BOOLEAN DEFAULT FALSE,
    Status ENUM('Active', 'Revoked', 'Expired') DEFAULT 'Active',
    FOREIGN KEY (UserID) REFERENCES Users(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE UserGroups
-- --------------------------
CREATE TABLE UserGroups (
    GroupID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    GroupName VARCHAR(255) NOT NULL UNIQUE,
    Description TEXT DEFAULT NULL,
    ParentGroupID BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ParentGroupID) REFERENCES UserGroups(GroupID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE UserGroupMemberships
-- --------------------------
CREATE TABLE UserGroupMemberships (
    MembershipID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    GroupID BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (GroupID) REFERENCES UserGroups(GroupID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE INDEXES ON UserGroupMemberships
-- --------------------------
CREATE INDEX idx_ugm_userid ON UserGroupMemberships(UserID);
CREATE INDEX idx_ugm_groupid ON UserGroupMemberships(GroupID);

-- --------------------------
-- CREATE TABLE Roles
-- --------------------------
CREATE TABLE Roles (
    RoleID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    RoleName VARCHAR(255) NOT NULL UNIQUE,
    Description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE UserRoles
-- --------------------------
CREATE TABLE UserRoles (
    UserRoleID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    RoleID BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE INDEXES ON UserRoles
-- --------------------------
CREATE INDEX idx_userroles_userid ON UserRoles(UserID);
CREATE INDEX idx_userroles_roleid ON UserRoles(RoleID);

-- --------------------------
-- CREATE TABLE GroupRoles
-- --------------------------
CREATE TABLE GroupRoles (
    GroupRoleID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    GroupID BIGINT UNSIGNED NOT NULL,
    RoleID BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (GroupID) REFERENCES UserGroups(GroupID) ON DELETE CASCADE,
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE INDEXES ON GroupRoles
-- --------------------------
CREATE INDEX idx_grouproles_groupid ON GroupRoles(GroupID);
CREATE INDEX idx_grouproles_roleid ON GroupRoles(RoleID);

-- --------------------------
-- CREATE TABLE BlockedDomains
-- --------------------------
CREATE TABLE BlockedDomains (
    DomainID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    DomainName VARCHAR(255) UNIQUE NOT NULL,
    Reason TEXT DEFAULT NULL,
    BlockedBy CHAR(36) DEFAULT NULL,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (BlockedBy) REFERENCES Users(ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE TwoFactorTokens
-- --------------------------
CREATE TABLE TwoFactorTokens (
    TokenID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    TokenValue CHAR(64) NOT NULL,
    TokenType ENUM('TOTP', 'SMS', 'Email', 'Push') NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    Status ENUM('Active', 'Used', 'Expired') DEFAULT 'Active',
    FOREIGN KEY (UserID) REFERENCES Users(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE PasswordHistory
-- --------------------------
CREATE TABLE PasswordHistory (
    HistoryID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE DeviceFingerprints
-- --------------------------
CREATE TABLE DeviceFingerprints (
    DeviceID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    DeviceType ENUM('Laptop', 'Mobile', 'Tablet', 'Desktop', 'Other') DEFAULT 'Other',
    OperatingSystem VARCHAR(255) NOT NULL,
    Browser VARCHAR(255) DEFAULT NULL,
    DeviceIdentifier CHAR(64) NOT NULL,
    IsTrusted BOOLEAN DEFAULT FALSE,
    first_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at DATETIME DEFAULT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE Sessions
-- --------------------------
CREATE TABLE Sessions (
    SessionID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_at DATETIME DEFAULT NULL,
    ip_address VARBINARY(16) NOT NULL,
    DeviceID BIGINT UNSIGNED DEFAULT NULL,
    Status ENUM('Active', 'Expired', 'Terminated') DEFAULT 'Active',
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (DeviceID) REFERENCES DeviceFingerprints(DeviceID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE BlockedRegions
-- --------------------------
CREATE TABLE BlockedRegions (
    RegionID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    CountryCode CHAR(2) DEFAULT NULL,
    RegionName VARCHAR(255) DEFAULT NULL,
    IPRangeStart VARBINARY(16) DEFAULT NULL,
    IPRangeEnd VARBINARY(16) DEFAULT NULL,
    BlockedBy CHAR(36) DEFAULT NULL,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Reason TEXT DEFAULT NULL,
    FOREIGN KEY (BlockedBy) REFERENCES Users(ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE BlockedIPs
-- --------------------------
CREATE TABLE BlockedIPs (
    BlockedIPID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    IPAddressStart VARBINARY(16) NOT NULL,
    IPAddressEnd VARBINARY(16) DEFAULT NULL,
    BlockedBy CHAR(36) DEFAULT NULL,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Reason TEXT DEFAULT NULL,
    FOREIGN KEY (BlockedBy) REFERENCES Users(ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE Delegations
-- --------------------------
CREATE TABLE Delegations (
    DelegationID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    DelegatorUserID CHAR(36) NOT NULL,
    DelegateeUserID CHAR(36) NOT NULL,
    Permissions JSON NOT NULL,
    start_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_at DATETIME DEFAULT NULL,
    CreatedBy CHAR(36) DEFAULT NULL,
    FOREIGN KEY (DelegatorUserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (DelegateeUserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (CreatedBy) REFERENCES Users(ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE AuditLog
-- --------------------------
CREATE TABLE AuditLog (
    AuditID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) DEFAULT NULL,
    Action VARCHAR(255) NOT NULL,
    TargetUserID CHAR(36) DEFAULT NULL,
    ActionDetails JSON DEFAULT NULL,
    action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE UserPreferences
-- --------------------------
CREATE TABLE UserPreferences (
    PreferenceID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    PreferenceKey VARCHAR(255) NOT NULL,
    PreferenceValue VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE NotificationSettings
-- --------------------------
CREATE TABLE NotificationSettings (
    NotificationID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    NotificationType ENUM('Email', 'SMS', 'Push') NOT NULL,
    IsEnabled BOOLEAN DEFAULT TRUE,
    Frequency ENUM('Immediate', 'Daily', 'Weekly') DEFAULT 'Immediate',
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE AccountRecoveryRequests
-- --------------------------
CREATE TABLE AccountRecoveryRequests (
    RecoveryID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    RecoveryCode CHAR(64) NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    IsUsed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE CompromisedCredentials
-- --------------------------
CREATE TABLE CompromisedCredentials (
    CredentialID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    HashedPassword CHAR(64) NOT NULL,
    discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Source VARCHAR(255) DEFAULT NULL,
    Notes TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE RiskScoring
-- --------------------------
CREATE TABLE RiskScoring (
    RiskID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    SessionID BIGINT UNSIGNED DEFAULT NULL,
    RiskScore DECIMAL(5,2) NOT NULL,
    RiskFactors JSON NOT NULL,
    ActionRecommended ENUM('Allow', 'Challenge', 'Block') NOT NULL,
    scored_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (SessionID) REFERENCES Sessions(SessionID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE RiskAssessment
-- --------------------------
CREATE TABLE RiskAssessment (
    AssessmentID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    SessionID BIGINT UNSIGNED NOT NULL,
    AnomalyType ENUM('Time', 'Location', 'Device') NOT NULL,
    Details JSON NOT NULL,
    assessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE,
    FOREIGN KEY (SessionID) REFERENCES Sessions(SessionID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE BehavioralAnalytics
-- --------------------------
CREATE TABLE BehavioralAnalytics (
    BehaviorID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    MetricKey VARCHAR(255) NOT NULL,
    MetricValue JSON NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE AccessControlPolicies
-- --------------------------
CREATE TABLE AccessControlPolicies (
    PolicyID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    PolicyName VARCHAR(255) NOT NULL,
    Conditions JSON NOT NULL,
    Action ENUM('Allow', 'Deny', 'Require MFA') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE ActionTriggers
-- --------------------------
CREATE TABLE ActionTriggers (
    TriggerID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    EventType ENUM('HighRiskLogin', 'PasswordChange', 'AccountLock') NOT NULL,
    Action ENUM('Notify', 'Block', 'Require MFA', 'Log') NOT NULL,
    Parameters JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE RecoveryOptions
-- --------------------------
CREATE TABLE RecoveryOptions (
    RecoveryOptionID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    OptionType ENUM('BackupCode', 'SecurityQuestion', 'TrustedContact') NOT NULL,
    OptionValue TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE BackupCodes
-- --------------------------
CREATE TABLE BackupCodes (
    BackupCodeID BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    UserID CHAR(36) NOT NULL,
    CodeHash CHAR(64) NOT NULL,
    IsUsed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------
-- CREATE TABLE employees
-- --------------------------
CREATE TABLE employees (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    middle_name VARCHAR(255) DEFAULT NULL,
    last_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
