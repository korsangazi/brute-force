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

/**
 * Abstract Class Database
 * @package Austinw\BruteForce\Database
 */
abstract class Database {

    /**
     * @var Object
     */
    protected $db;

    /**
     * Time to lockout the user for
     * @var int
     */
    protected $lockout = 300; // 5 minutes

    /**
     * Time to be checking for failed login attempts
     * @var int
     */
    protected $timeframe = 300; // 5 minutes

    /**
     * @param $_db
     */
    public function __construct($_db)
    {
        $this->db = $_db;
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

    /**
     * @return int
     */
    public function getTimeframe()
    {
        return $this->timeframe;
    }

    /**
     * @param $_timeframe
     */
    public function setTimeframe($_timeframe)
    {
        $this->timeframe = $_timeframe;
    }
}
