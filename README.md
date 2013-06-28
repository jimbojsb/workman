[![Build Status](https://travis-ci.org/jimbojsb/workman.png?branch=master)](https://travis-ci.org/jimbojsb/workman)

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
$callback = function($arg1) {
    echo getmypid() . ": " . $arg1 . "\n";
};

$callbackQueue = new Workman\CallbackQueue($callback, 3);
for ($c = 0; $c < 100000; $c++) {
    $callbackQueue->push([$c]);
}
$callbackQueue->work(7);

```