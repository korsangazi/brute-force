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

use Austinw\BruteForce\Database\DatabaseInterface;

/**
 * Class BruteForce
 * @package Austinw\BruteForce
 */
class BruteForce {

    /**
     * @var int
     */
    private $failedUserLoginLimit = 3;
    /**
     * @var int
     */
    private $failedIpLoginLimit = 3;

    /**
     * @var DatabaseInterface
     */
    private $storage;

    /**
     * @param DatabaseInterface $_db
     * @param int $_lockout Amount of time the user will not be allowed to login for
     */
    public function __construct(DatabaseInterface $_db, $_lockout = 300) {
		$this->storage = $_db;

        $this->storage->setLockout($_lockout);
	}

    /**
     * @param $userId
     * @param $ipAddress
     * @return bool
     */
    public function addFailedAttempt($userId, $ipAddress){

        $this->storage->insertFailedLoginAttempt($userId, $ipAddress);

        return true;
	}

    /**
     * @param array $params
     * @return bool
     * @throws BruteForceException
     * <code>
     * $params = array(
     *   'userId'    => $userId,      // user's id
     *   'ipAddress' => $ipAddress,   // ip address (ensure it's dependable i.e. REMOTE_ADDR, HTTP_X_FORWARDED_FOR
     *   'callback'  => function($type, $numAttempts, $lockedUntil, $lockoutTime) {
     *     $type = 'user|ip';
     *   }
     * );
     *
     * </code>
     */
    public function checkLocked(array $params)
    {
        if (!isset($params['userId']) || !isset($params['ipAddress'])) {
            throw new BruteForceException('Both userId and ipAddress must be passed to BruteForceBlock::isLocked(array $params)');
        }
        $userFailedAttempts = $this->storage->retrieveUserFailedLoginAttempts($params['userId']);
        $ipFailedAttempts = $this->storage->retrieveIpFailedLoginAttempts($params['ipAddress']);

        if ($userFailedAttempts >= $this->failedUserLoginLimit) {
            if (isset($params['callback']) && $params['callback'] instanceof \Closure) {
                call_user_func($params['callback'], 'user', $userFailedAttempts, $userFailedAttempts['timeout'], $this->storage->getLockout());
            }
            return true;
        } else if ($ipFailedAttempts >= $this->failedIpLoginLimit) {
            if (isset($params['callback']) && $params['callback'] instanceof \Closure) {
                call_user_func($params['callback'], 'ip', $userFailedAttempts, $ipFailedAttempts['timeout'], $this->storage->getLockout());
            }
            return true;
        }

        return false;
    }

    /**
     * @return DatabaseInterface
     */
    public function database()
    {
        return $this->storage;
    }

    /**
     * Clear the database
     */
    public function clear()
    {
        $this->storage->clear();
	}
}
