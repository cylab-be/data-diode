<?php

namespace App;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;

/**
 * This class defines an Ubuntu network configuration file
 */
class NetworkConfiguration
{
    private $mode;
    private $options;

    /**
     * Constructs a new NetworkConfiguration with no options and using DHCP
     * @return void
     */
    public function __construct()
    {
        $this->mode = "dhcp";
        $this->options = array();
    }

    /**
     * Configures this instance to be a static network configuration
     * @return NetworkConfiguration this
     */
    public function setStatic()
    {
        $this->mode = "static";
        return $this;
    }

    /**
     * Configures this instance to be using DHCP
     * @return NetworkConfiguration this
     */
    public function setDHCP()
    {
        $this->mode = "dhcp";
        return $this;
    }

    /**
     * Gets the current mode of configuration (DHCP or static)
     * @return string the mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Gets the option value for a given key.
     * @param  string $key The key
     * @return mixed return a string or an array of strings depending if there is one of more than one value
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new Exception("The given key : $key is not set");
        }
        return $this->options[$key];
    }

    /**
     * Sets an option with a given key and value
     * @param string $key the key of the option
     * @param mixed $value the value for this option (string or array of strings)
     * @return NetworkConfiguration this
     */
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

    /**
     * Saves this configuration for the input NIC
     * @return void
     * @return void
     */
    public function saveInput()
    {
        $this->save("input", env("INPUT_INTERFACE", "lo"));
    }

    /**
     * Saves this configuration for the output NIC
     * @return void
     */
    public function saveOutput()
    {
        $this->save("output", env("OUTPUT_INTERFACE", "lo"));
    }

    /**
     * Creates an array representation of the object
     * @return array The array representation
     */
    public function toArray()
    {
        $array = $this->options;
        $array["mode"] = $this->mode;
        return $array;
    }

    private static function get($fileName)
    {
        $config = new NetworkConfiguration();
        foreach (preg_split("(\\r\\n|\\n)", Storage::get($fileName)) as $line) {
            trim($line);
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

    /**
     * Gets the configuration for the input NIC
     * @return NetworkConfiguration the configuration
     */
    public static function getInput()
    {
        return NetworkConfiguration::get("input");
    }

    /**
     * Gets the configuration for the output NIC
     * @return NetworkConfiguration the configuration
     */
    public static function getOutput()
    {
        return NetworkConfiguration::get("output");
    }
}
