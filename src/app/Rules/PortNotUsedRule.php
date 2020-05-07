<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Uploader;

class PortNotUsedRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $uploaders = Uploader::all();
        foreach ($uploaders as $u) {
            if ($u->port == $value || $u->pipport == $value || $u->aptport == $value) {
                return false;
            }
        }
        $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh ' . strval($value);
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $output = $process->getOutput();
            if (strlen($output) > 1) {
                return false;
            }
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            if (strlen($output) > 1) {
                return false;
            } 
            // Else there is no real error, just an empty output considered as one.
            // Therefore we don't place a return false here.
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ' The :attribute is used by another program.';
    }
}
