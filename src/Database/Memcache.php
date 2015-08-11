<?php
/*
 * This file is part of the BruteForce package.
 *
 * (c) Austin White <austingym@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BruteForce\Database;

use Carbon\Carbon;

/**
 * Class Memcache
 * @package Austinw\BruteForce\Database
 */
class Memcache extends Database implements DatabaseInterface {

    /**
     * @var
     */
    private $keyMaker;

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
     * @param $username
     * @return mixed
     */
    private function userKey($username)
    {
        return $this->makeKey('failed_user_login_' . $username);
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
     * @param $username
     * @param $ipAddress
     */
    public function insertFailedLoginAttempt($username, $ipAddress)
    {
        $userKey = $this->userKey($username);
        $ipKey = $this->ipKey($ipAddress);

        $lockUntil = Carbon::now()->addSeconds($this->getLockout());

        $userAttempts = $this->retrieveUserFailedLoginAttempts($username);

        $userAttempts['timeout'] = $lockUntil;
        $userAttempts['attempts'] += 1;

        $this->db->set($userKey, $userAttempts, array('brute_force', 'brute_force_failed'), $this->timeframe);

        $ipAttempts = $this->retrieveIpFailedLoginAttempts($ipAddress);
        $ipAttempts['timeout'] = $lockUntil;
        $ipAttempts['attempts'] += 1;

        $this->db->set($ipKey, $ipAttempts, array('brute_force', 'brute_force_failed'), $this->timeframe);
    }

    /**
     * @param $username
     * @return array
     */
    public function retrieveUserFailedLoginAttempts($username)
    {
        $timeout = Carbon::now()->addSeconds($this->getTimeframe());
        return $this->db->get($this->userKey($username), array('brute_force', 'brute_force_failed'), function() use($timeout) {
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
        $timeout = $this->getTimeframe();
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
}
