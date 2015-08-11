<?php
require 'vendor/autoload.php';

$bruteForceDatabase = new \BruteForce\Database\Memcache(new stdClass());
$bruteForce = new \BruteForce\BruteForce($bruteForceDatabase);
