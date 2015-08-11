<?php

namespace BruteForce\Test;

use BruteForce\BruteForce;
use BruteForce\Database\Memcache;
use BruteForce\Message;
use Mockery;
use Carbon\Carbon;

class BruteForceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mockery\Mock
     */
    protected $database;

    public function setUp()
    {
        $this->database = Mockery::mock('BruteForce\Database\Memcache');
        $this->database->shouldReceive('setTimeframe')->once();
    }

    public function testMessageObject()
    {
        $bruteForce = new BruteForce($this->database);

        $this->assertInstanceOf('BruteForce\Message', $bruteForce->getMessage());
    }

    public function testDatabaseObject()
    {
        $bruteForce = new BruteForce($this->database);

        $this->assertInstanceOf('BruteForce\Database\DatabaseInterface', $bruteForce->database());
    }

    public function testSettingLimits()
    {
        $bruteForce = new BruteForce($this->database);

        $bruteForce->setFailedUserLoginLimit(100);
        $bruteForce->setFailedIpLoginLimit(200);

        $this->assertEquals(100, $bruteForce->getFailedUserLoginLimit());
        $this->assertEquals(200, $bruteForce->getFailedIpLoginLimit());
    }

    public function testAddFailedAttempt()
    {
        $bruteForce = new BruteForce($this->database);

        $this->database->shouldReceive('insertFailedLoginAttempt')->withArgs(array('jdoe', '127.0.0.1'));
        $bruteForce->addFailedAttempt('jdoe', '127.0.0.1');
    }

    /**
     * @expectedException \BruteForce\BruteForceException
     */
    public function testInvalidCheckLocked()
    {
        $bruteForce = new BruteForce($this->database);

        $bruteForce->checkLocked(array());
    }

    public function testUserLockingMechanism()
    {
        $memcache = Mockery::mock('Memcache');
        $bruteForce = new BruteForce($this->database);

        $this->database->shouldReceive('getTimeframe')->andReturn(100);
        $this->database->shouldReceive('getLockout')->andReturn(300);

        $timeout = Carbon::now()->addSeconds($this->database->getTimeframe());

        $this->database->shouldReceive('retrieveUserFailedLoginAttempts')
            ->withArgs(array('jdoe'))
            ->andReturn(array('timeout' => $timeout, 'attempts' => 3));

        $this->database->shouldReceive('retrieveIpFailedLoginAttempts')
            ->withArgs(array('127.0.0.1'))
            ->andReturn(array('timeout' => $timeout, 'attempts' => 1));

        $this->assertEquals(true, $bruteForce->checkLocked(array('username' => 'jdoe', 'ipAddress' => '127.0.0.1')));
    }

    public function testIpLockingMechanism()
    {
        $memcache = Mockery::mock('Memcache');
        $bruteForce = new BruteForce($this->database);

        $this->database->shouldReceive('getTimeframe')->andReturn(100);
        $this->database->shouldReceive('getLockout')->andReturn(300);

        $timeout = Carbon::now()->addSeconds($this->database->getTimeframe());

        $this->database->shouldReceive('retrieveUserFailedLoginAttempts')
            ->withArgs(array('jdoe'))
            ->andReturn(array('timeout' => $timeout, 'attempts' => 1));

        $this->database->shouldReceive('retrieveIpFailedLoginAttempts')
            ->withArgs(array('127.0.0.1'))
            ->andReturn(array('timeout' => $timeout, 'attempts' => 100));

        $this->assertEquals(true, $bruteForce->checkLocked(array('username' => 'jdoe', 'ipAddress' => '127.0.0.1')));
    }

    public function tearDown()
    {
        Mockery::close();
    }
}