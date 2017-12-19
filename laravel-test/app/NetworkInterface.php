<?php

namespace App;

use App\Rule;
use App\Jobs\CreateIptablesRuleJob;
use App\Jobs\DeleteIptablesRuleJob;

class NetworkInterface
{
    public static function getAllInterfaces()
    {
        $nics = scandir("/sys/class/net");
        array_splice($nics, array_search(".", $nics), 1);
        array_splice($nics, array_search("..", $nics), 1);
        return $nics;
    }
    
    public static function setCurrentInterface($interface)
    {
        $rules = Rule::all();
        foreach ($rules as $rule) {
            DeleteIptablesRuleJob::dispatch($rule);
        }
        option(["input_interface" => $interface]);
        foreach ($rules as $rule) {
            CreateIptablesRuleJob::dispatch($rule);
        }
    }
    
    public static function getCurrentInterface()
    {
        if (option_exists("input_interface")) {
            return option("input_interface");
        } else {
            return null;
        }
    }
}
