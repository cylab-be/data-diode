<?php

namespace App;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;

class NetworkConfiguration
{
    private $mode;
    private $options;

    public function __construct()
    {
        $this->mode = "dhcp";
        $this->options = array();
    }

    public function setStatic()
    {
        $this->mode = "static";
        return $this;
    }

    public function setDHCP()
    {
        $this->mode = "dhcp";
        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getOption($key)
    {
        if (!array_key_exists($key)) {
            throw new Exception("The given key : $key is not set");
        }
        return $this->options[$key];
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    private function save($fileName, $interface)
    {
        Storage::put($fileName, "auto " . $interface);
        Storage::append($fileName, "iface " . $interface . " inet ". $this->mode);
        if ($this->mode == "static") {
            foreach ($this->options as $key => $value) {
                $valueString;
                if (is_array($value)) {
                    $valueString = implode(" ", $value);
                } else {
                    $valueString = $value;
                }
                Storage::append($fileName, $key . " " . $valueString);
            }
        }
        Storage::append($fileName, "");
    }

    public function saveInput()
    {
        $this->save("input", env("INPUT_INTERFACE", "lo"));
    }

    public function saveOutput()
    {
        $this->save("output", env("OUTPUT_INTERFACE", "lo"));
    }

    private static function get($fileName)
    {
        $config = new NetworkConfiguration();
        foreach (preg_split("(\\r\\n|\\n)", Storage::get($fileName)) as $line) {
            if ($line !== "") {
                $words = explode(" ", $line);
                if ($words[0] === "iface") {
                    $config->mode = $words[3];
                } else {
                    if ($words[0] !== "auto") {
                        $key = $words[0];
                        array_shift($words);
                        $config->setOption($key, count($words) == 1 ? $words[0] : $words);
                    }
                }
            }
        }
        return $config;
    }

    public static function getInput()
    {
        return NetworkConfiguration::get("input");
    }

    public static function getOutput()
    {
        return NetworkConfiguration::get("output");
    }
}
