<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * This code is a modified version taken from:
 * 
 * https://gist.github.com/crishoj/79c6a708dbd90ada4b5f3911c473baf2
 */
class EnsureQueueWorkerIsRunning extends Command
{
    protected $signature = 'queue:checkup';
    
    protected $description = 'Ensure that the queue worker is running.';

    protected $pidFile;

    public function handle()
    {
        $this->pidFile = storage_path('app/queue.pid');

        if (! $this->isWorkerRunning()) {
            $this->comment('Queue worker is being started.');
            $pid = $this->startWorker();
            $this->saveWorkerPID($pid);
        }
        $this->comment('Queue worker is running.');
    }

    /**
     * Check if the queue worker is running.
     */
    private function isWorkerRunning(): bool
    {
        if (! $pid = $this->getLastWorkerPID()) {
            return false;
        }
        $process = exec("ps -p {$pid} -opid=,command=");
        $processIsQueueWorker = str_contains($process, 'queue:work');

        return $processIsQueueWorker;
    }

    /**
     * Get any existing queue worker PID.
     */
    private function getLastWorkerPID()
    {
        if (! file_exists($this->pidFile)) {
            return false;
        }

        return file_get_contents($this->pidFile);
    }

    /**
     * Save the queue worker PID to a file.
     */
    private function saveWorkerPID($pid)
    {
        file_put_contents($this->pidFile, $pid);
    }

    /**
     * Start the queue worker.
     */
    private function startWorker(): int
    {
        $command = 'php ' . base_path('artisan') . ' queue:work database --timeout=0 > /dev/null';
        $pid = exec($command);

        return $pid;
    }
}