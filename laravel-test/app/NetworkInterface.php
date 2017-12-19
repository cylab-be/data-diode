<?php

namespace App;

class NetworkInterface
{
    public static function getAllInterfaces()
    {
        $nics = scandir("/sys/class/net");
        array_splice($nics, array_search(".", $nics), 1);
        array_splice($nics, array_search("..", $nics), 1);
        return $nics;
    }
}
