<?php
/*
 * This file is part of the Austinw\BruteForce package.
 *
 * (c) Austin White <austingym@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austinw\BruteForce;

use Carbon\Carbon;


/**
 * Class Message
 * @package Austinw\BruteForce
 */
class Message
{
    /**
     * @var string 'user|ip'
     */
    private $type;

    /**
     * @var int
     */
    private $numAttempts;

    /**
     * @var Carbon time of when the lockout will cease
     */
    private $lockedUntil;

    /**
     * @var int Total seconds of time that the lockout exists
     */
    private $lockoutTime;


    /**
     * @param bool|true $human Human readable format or number of seconds
     * @return int|string
     */
    public function timeRemaining($human = true)
    {
        return ($human) ? $this->getLockedUntil()->diffForHumans() : Carbon::now()->diffInSeconds($this->getLockedUntil());
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Message
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumAttempts()
    {
        return $this->numAttempts;
    }

    /**
     * @param int $numAttempts
     * @return Message
     */
    public function setNumAttempts($numAttempts)
    {
        $this->numAttempts = $numAttempts;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getLockedUntil()
    {
        return $this->lockedUntil;
    }

    /**
     * @param Carbon $lockedUntil
     * @return Message
     */
    public function setLockedUntil(Carbon $lockedUntil)
    {
        $this->lockedUntil = $lockedUntil;
        return $this;
    }

    /**
     * @return int
     */
    public function getLockoutTime()
    {
        return $this->lockoutTime;
    }

    /**
     * @param int $lockoutTime
     * @return Message
     */
    public function setLockoutTime($lockoutTime)
    {
        $this->lockoutTime = $lockoutTime;
        return $this;
    }
}