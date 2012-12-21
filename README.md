workman
=======

PHP Process Forking &amp; Daemonizing Library


$w = new Workman\Worker(function() { // do work});

$w->fork(5); // fork 5 children

// optionally, if you have blockin IO that can do long runing processes in your callback

$w->daemonize('/path/to/pidfile')

$w->work();
