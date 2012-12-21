<?php
declare(ticks = 1);
namespace Workman;

class Worker
{
    protected $fork = false;
    protected $daemonize = false;
    protected $callback;
    protected $isChild = false;
    protected $isDaemon = false;
    protected $pidFile;
    protected $pid;
    protected $refork;
    protected $childPids = [];

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
        $this->pid = getmypid();
    }

    public function fork($num, $refork = false)
    {
        $this->fork = $num;
        $this->refork = $refork;
    }

    public function daemonize($pidfile)
    {
        $this->daemonize = true;
        $this->pidFile = $pidfile;
    }

    public function work()
    {
        //echo "Worker started with pid $this->pid...\n";
        if ($this->daemonize) {
            $pid = pcntl_fork();
            if ($pid == 0) {
                $this->pid = getmypid();
                $this->isDaemon = true;
                pcntl_signal(SIGTERM, function() {
                    //echo "Daemon process caught SIGTERM\n";
                    if ($this->childPids) {
                        foreach (array_keys($this->childPids) as $pid) {
                            //echo "Sending SIGKILL to child $pid\n";
                            posix_kill($pid, SIGKILL);
                        }
                    }
                    //echo "Daemon process $this->pid exiting\n";
                    unlink($this->pidFile);
                    exit();
                });
            } else {
                //echo "Successfully daemonized. PID $pid written to $this->pidFile\n";
                file_put_contents($this->pidFile, $pid);
                exit();
            }
        }
        $this->childPids = $pids = [];
        if ($this->fork) {
            RE_FORK:
            for ($c = count($this->childPids); $c < $this->fork; $c++) {
                $pid = pcntl_fork();
                if ($pid > 0) {
                    // "Forked child with PID $pid\n";
                    $this->childPids[$pid] = time();
                } else {
                    $this->pid = getmypid();
                    $this->isChild = true;
                    $this->isDaemon = false;
                    goto DO_WORK;
                }
            }
            while (count($this->childPids)) {
                $status = 0;
                $exitedPid = pcntl_wait($status);
                unset($this->childPids[$exitedPid]);
                if ($this->refork) {
                    goto RE_FORK;
                }
            }
            goto END_FORK;
        }
        DO_WORK:
            $func = $this->callback;
            $func();
            exit();

        END_FORK:
            exit();
    }

}