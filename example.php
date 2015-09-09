<?php
require_once __DIR__ . '/src/Workman/Worker.php';
$testCallback = function() {
    $sleepTime = mt_rand(1, 5);
    sleep($sleepTime);
    echo "Hello from worker " . getmypid() . "\n";
};

$worker = new \Workman\Worker($testCallback);
$worker->daemonize('test.pid');
$worker->fork(5, true);
$worker->work();