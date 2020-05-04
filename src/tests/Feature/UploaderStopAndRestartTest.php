<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Check if the stopping and restarting features of the BFTP 
 * program of a channel do stop and restart the program. 
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. The diodein test will 
 * wait for 20 seconds before deleting the created channel,
 * allowing the diodeout test to use the uploader created 
 * by the diodein test during those ~20 seconds.
 */
class UploaderStopAndRestartTest extends TestCase
{
    private $user;

    /**
     *  Method run before each test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /**
     *  Method run after each test.
     */
    public function tearDown()
    {
        $this->user->delete();
        parent::tearDown();
    }

    public function testPutUploaderStopAndRestartProgram() {
        // It's a PUT method for conceptual reasons;
        // It doesn't make any DB modification.

        if (env("DIODE_IN", true)) {
            // Adding the new uploader via POST (to launch the Python script)
            $json = $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)->getContent();
            $obj = json_decode($json, true);            
        } else {
            // Getting the new uploader
            $obj = Uploader::where('name', '=', 'test0')->first();
        }

        $cmd = 'supervisorctl pid blindftp-' . $obj['name'];
        $process = new Process($cmd);
        try {
            $process->mustRun();            
        } catch (ProcessFailedException $exception) {
            // An error is generated even if the command works.
            // Therefore we do the assertion here.
            $output = $process->getOutput();
            $this->assertTrue(ctype_digit(trim($output)) && intval(trim($output)) > 0);
        }
        
        // Stopping program                    
        $this->actingAs($this->user)->json("PUT", "/uploader/stop/" . $obj["id"])
            ->assertStatus(200);
        
        $process = new Process($cmd);
        try {
            $process->mustRun();            
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            $this->assertTrue(ctype_digit(trim($output)) && intval(trim($output)) == 0);
        }

        // Restarting program
        $this->actingAs($this->user)->json("PUT", "/uploader/restart/" . $obj["id"])
            ->assertStatus(200);
        
        $process = new Process($cmd);
        try {
            $process->mustRun();            
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            $this->assertTrue(ctype_digit(trim($output)) && intval(trim($output)) > 0);
        }

        if (env("DIODE_IN", true)) {
            sleep(20);
            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);
        }
    }


}
