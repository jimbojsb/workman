<?php
namespace Workman;

class CallbackQueue
{
    private $callback;
    private $queue;
    private $childPids = array();

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function push($data)
    {
        $this->queue[] = $data;
    }

    public function work($numForks)
    {
        $jobsPerFork = ceil(count($this->queue) / $numForks);

        for ($c = 0; $c < $numForks; $c++) {
            $jobs = array_slice($this->queue, 0, $jobsPerFork);
            $this->queue = array_slice($this->queue, $jobsPerFork);
            $pid = pcntl_fork();
            if ($pid > 0) {
                $this->childPids[$pid] = true;
            } else {
                while ($jobs) {
                    call_user_func_array($this->callback, array_shift($jobs));
                }
                exit();
            }
        }

        while (count($this->childPids)) {
            $status = 0;
            $exitedPid = pcntl_wait($status);
            unset($this->childPids[$exitedPid]);
        }
    }
}