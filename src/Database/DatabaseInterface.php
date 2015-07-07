<?php

namespace Austinw\BruteForce\Database;

interface DatabaseInterface {
    public function insertFailedLoginAttempt($userId, $ipAddress);

    public function retrieveUserFailedLoginAttempts($userId);

    public function retrieveIpFailedLoginAttempts($ipAddress);

    public function clear();

    public function getLockout();

    public function setLockout($lockout);
}
