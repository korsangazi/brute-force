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
    private $failedIpLoginLimit = 5;

    /**
     * @var DatabaseInterface
     */
    private $storage;

    /**
     * @var Message
     */
    private $message;

    /**
     * @param DatabaseInterface $_db
     * @param int $_lockout Amount of time the user will not be allowed to login for
     */
    public function __construct(DatabaseInterface $_db, $_lockout = 300) {
        $this->setStorage($_db);

        $this->getStorage()->setLockout($_lockout);

        $this->setMessage(new Message());
    }

    /**
     * @param $userId
     * @param $ipAddress
     * @return bool
     */
    public function addFailedAttempt($userId, $ipAddress){

        $this->getStorage()->insertFailedLoginAttempt($userId, $ipAddress);

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
     *   'callback'  => function(Message $message) {}
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

        if ($userFailedAttempts['attempts'] >= $this->failedUserLoginLimit) {

            $this->message->setType('user')
                ->setNumAttempts($userFailedAttempts)
                ->setLockedUntil($userFailedAttempts['timeout'])
                ->setLockoutTime($this->storage->getLockout());

            if (isset($params['callback']) && $params['callback'] instanceof \Closure) {
                call_user_func($params['callback'], $this->message);
            }

            return true;

        } else if ($ipFailedAttempts['attempts'] >= $this->failedIpLoginLimit) {

            $this->message->setType('ip')
                ->setNumAttempts($userFailedAttempts)
                ->setLockedUntil($userFailedAttempts['timeout'])
                ->setLockoutTime($this->storage->getLockout());

            if (isset($params['callback']) && $params['callback'] instanceof \Closure) {
                call_user_func($params['callback'], $this->message);
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

    /**
     * @return int
     */
    public function getFailedUserLoginLimit()
    {
        return $this->failedUserLoginLimit;
    }

    /**
     * @param int $failedUserLoginLimit
     * @return BruteForce
     */
    public function setFailedUserLoginLimit($failedUserLoginLimit)
    {
        $this->failedUserLoginLimit = $failedUserLoginLimit;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailedIpLoginLimit()
    {
        return $this->failedIpLoginLimit;
    }

    /**
     * @param int $failedIpLoginLimit
     * @return BruteForce
     */
    public function setFailedIpLoginLimit($failedIpLoginLimit)
    {
        $this->failedIpLoginLimit = $failedIpLoginLimit;
        return $this;
    }

    /**
     * @return DatabaseInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param DatabaseInterface $storage
     * @return BruteForce
     */
    public function setStorage(DatabaseInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        if ( ! $this->message) {
            throw new BruteForceException("Must call BruteForce::checkLocked() before accessing this property");
        }

        return $this->message;
    }

    /**
     * @param Message $message
     * @return BruteForce
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }


}
