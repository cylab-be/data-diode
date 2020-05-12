<?php

namespace Tests\Feature\Parallel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Check the adding and removing features concerning a 
 * channel's PIP module of do add to and remove it from
 * that channel.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. 
 */
class UploaderAddAndRemovePipTest extends TestCase
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

    public function testPostUploaderAddAndRemovePip()
    {
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

        if (env("DIODE_IN", true)) {

            // Checking the uploader's pipport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 0);

            // Adding the PIP module to diodeout (only a DB update for diodein)
            $this->actingAs($this->user)->post("/pip/" . $obj["id"], [
                "port" => 40001,
            ])->assertStatus(200);

            // Checking the uploader's pipport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 40001);
        } else {
            // The PIP module should have been added now...

            // Checking the uploader's pipport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 40001);

            // Checking if something is running on port 40001
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40001';
            $process = new Process($cmd);
            try {
                $process->mustRun();
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) != 0); // The PIP module should be running
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) != 0); // The PIP module should be running
            }
        }

        if (env("DIODE_IN", true)) {
            sleep(20);

            // Removing the PIP module from diodeout (only a DB update for diodein)
            $this->actingAs($this->user)->delete("/pip/" . $obj["id"])
                 ->assertStatus(200);

            // Checking the uploader's pipport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 0);

            sleep(20);

            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);

        } else {
            sleep(20);
            // The PIP module should have been removed now...

            // Checking the uploader's pipport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 0);

            // Checking if something is running on port 40001
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40001';
            $process = new Process($cmd);
            try {
                $process->mustRun();
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) == 0); // Nothing should be running
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) == 0); // Nothing should be running
            }

        }
    }
}
