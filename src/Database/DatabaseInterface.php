<?php
/*
 * This file is part of the Austinw\BruteForce package.
 *
 * (c) Austin White <austingym@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austinw\BruteForce\Database;

interface DatabaseInterface {
    public function insertFailedLoginAttempt($userId, $ipAddress);

    public function retrieveUserFailedLoginAttempts($userId);

    public function retrieveIpFailedLoginAttempts($ipAddress);

    public function clear();

    public function getLockout();

    public function setLockout($lockout);
}
