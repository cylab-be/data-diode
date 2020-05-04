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
 * Check if the modules added to a deleted channel are also
 * deleted.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. The diodein test will 
 * wait for 20 seconds before deleting the created channel,
 * allowing the diodeout test to use the uploader created 
 * by the diodein test during those ~20 seconds.
 */
class UploaderDeleteModulesTest extends TestCase
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

    public function testPostUploaderDeleteModules()
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

            // Checking the uploader's pipport & aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 0);
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 0);

            // Adding the PIP module to diodeout (only a DB update for diodein)
            $this->actingAs($this->user)->post("/pip/" . $obj["id"], [
                "port" => 40001,
            ])->assertStatus(200);

            // Adding the APT module to diodeout (only a DB update for diodein)
            $this->actingAs($this->user)->post("/apt/" . $obj["id"], [
                "port" => 40002,
            ])->assertStatus(200);

            // Checking the uploader's pipport & aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 40001);
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 40002);

            sleep(20);
            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);            

        } else {
            // The PIP & APT modules should have been added now...

            // Checking the uploader's pipport & aptport in the DB
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->pipport == 40001);
            $this->assertTrue(Uploader::where('name', '=', 'test0')->first()->aptport == 40002);

            // Checking if something is running on port 40001
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40001';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) != 0); // The PIP module should be running
            }

            // Checking if something is running on port 40002
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40002';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) != 0); // The APT module should be running
            }

            sleep(20);
            // The new uploader having been deleted by diodein, the 
            // PIP & APT modules should have disappeared...

            // Checking if something is running on port 40001
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40001';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(strlen($output) == 0); // Nothing should be running
            }

            // Checking if something is running on port 40002
            $cmd = 'sudo ' . base_path('app/Scripts') . '/get-netstat.sh 40002';
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
