<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
/* 
 * 'Process' will not be used because of the errors it generates when used
 * in the BlindftpServerController.
 */
// use Symfony\Component\Process\Process;

/**
 * This code is a modified version from the following:
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
        /* The following code generates the error:
         * The command "ps -p 2626 -opid=,command=" failed.Exit Code: 1(General error)
         * Working directory: /var/www/data-diode/src/public
         * Output:================Error Output:================
         * 
         * $process = new Process("ps -p {$pid} -opid=,command=");
         * $process->mustRun();
         * $output = $process->getOutput();
         * $processIsQueueWorker = str_contains($output, 'queue:work');
         */
        // It will therefore be replaced by this one:
        $process = exec("ps -p {$pid} -opid=,command=");
        $processIsQueueWorker = str_contains($process, 'queue:work');
        // Note that exec do not throw an error if something went wrong. It must be used carefully.
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
        /* The following code does not provide a reliable pid.
         *
         * $command = 'sudo php ' . base_path('artisan') . ' queue:work database --timeout=0';
         * $process = new Process($command);
         * $process->start();        
         * $pid = $process->getPid();
         */
        // It will therefore be replaced by this one:
        $command = 'sudo php ' . base_path('artisan') . ' queue:work database --timeout=0 > /dev/null & echo $!';
        $pid = exec($command);
        // Note that exec do not throw an error if something went wrong. It must be used carefully.
        return $pid;
    }
}