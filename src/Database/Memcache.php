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

/**
 * Class Memcache
 * @package Austinw\BruteForce\Database
 */
class Memcache implements DatabaseInterface {

    /**
     * @var
     */
    private $db;
    /**
     * @var
     */
    private $keyMaker;

    /**
     * @var int
     */
    private $lockout = 300; // 5 minutes

    /**
     * @param $_db
     */
    public function __construct($_db)
    {
        $this->db = $_db;
    }

    /**
     * @param $callback
     */
    public function registerKeymaker($callback)
    {
        $this->keyMaker = $callback;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function makeKey($key)
    {
        return call_user_func($this->keyMaker, $key);
    }

    /**
     * @param $userId
     * @return mixed
     */
    private function userKey($userId)
    {
        return $this->makeKey('failed_user_login_' . $userId);
    }

    /**
     * @param $ipAddress
     * @return mixed
     */
    private function ipKey($ipAddress)
    {
        return $this->makeKey('failed_ip_login_' . $ipAddress);
    }

    /**
     * @param $userId
     * @param $ipAddress
     */
    public function insertFailedLoginAttempt($userId, $ipAddress)
    {
        $userKey = $this->userKey($userId);
        $ipKey = $this->ipKey($ipAddress);

        $userAttempts = $this->retrieveUserFailedLoginAttempts($userId);
        $userAttempts['timeout'] = time() + $this->lockout;
        $userAttempts['attempts'] += 1;

        $this->db->set($userKey, $userAttempts, array('brute_force', 'brute_force_failed'), $this->lockout); // 10 minutes

        $ipAttempts = $this->retrieveIpFailedLoginAttempts($ipAddress);
        $ipAttempts['timeout'] = time() + $this->lockout;
        $ipAttempts['attempts'] += 1;

        $this->db->set($ipKey, $ipAttempts, array('brute_force', 'brute_force_failed'), $this->lockout); // 10 minutes
    }

    /**
     * @param $userId
     * @return array
     */
    public function retrieveUserFailedLoginAttempts($userId)
    {
        $timeout = $this->lockout;
        return $this->db->get($this->userKey($userId), array('brute_force', 'brute_force_failed'), function() use($timeout) {
            return array(
                'timeout' => $timeout,
                'attempts' => 0,
            );
        });
    }

    /**
     * @param $ipAddress
     * @return array
     */
    public function retrieveIpFailedLoginAttempts($ipAddress)
    {
        $timeout = $this->lockout;
        return $this->db->get($this->ipKey($ipAddress), array('brute_force', 'brute_force_failed'), function() use($timeout) {
            return array(
                'timeout' => $timeout,
                'attempts' => 0,
            );
        });
    }

    /**
     * @throws MemcacheException
     */
    public function clear()
    {
        throw new MemcacheException("Method not yet implemented");
        throw new MemcacheException("Could not clear the database");
    }

    /**
     * @return int
     */
    public function getLockout()
    {
        return $this->lockout;
    }

    /**
     * @param $_lockout
     */
    public function setLockout($_lockout)
    {
        $this->lockout = $_lockout;
    }
}
