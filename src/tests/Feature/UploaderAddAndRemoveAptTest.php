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
 * Check the adding and removing features concerning a 
 * channel's APT module of do add to and remove it from
 * that channel.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. 
 */
class UploaderAddAndRemoveAptTest extends TestCase
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

    public function testPostUploaderAddAndRemoveApt()
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

            // Checking the uploader's aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 0);

            // Adding the APT module to diodeout (only a DB update for diodein)
            $this->actingAs($this->user)->post("/apt/" . $obj["id"], [
                "port" => 40001,
            ])->assertStatus(200);

            // Checking the uploader's aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 40001);
        } else {
            // The APT module should have been added now...

            // Checking the uploader's aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 40001);

            // Checking if something is running on port 40001
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40001';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) != 0); // The APT module should be running
            }
        }

        if (env("DIODE_IN", true)) {
            sleep(20);

            // Removing the APT module from diodeout (only a DB update for diodein)
            $this->actingAs($this->user)->delete("/apt/" . $obj["id"])
                 ->assertStatus(200);

            // Checking the uploader's aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 0);

            sleep(20);

            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);

        } else {
            sleep(20);
            // The new uploader having been deleted, the module should have disappeared...

            // Checking the uploader's aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 0);

            // Checking if something is running on port 40001
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40001';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) == 0); // Nothing should be running
            }

        }
    }
}
