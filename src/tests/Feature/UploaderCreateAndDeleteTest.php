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
 * Check if the creating channel feature.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. 
 */
class UploaderCreateAndDeleteTest extends TestCase
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

    public function testPostUploaderConnected()
    {
        if (env("DIODE_IN", true)) {
            // Adding the new uploader via POST (to launch the Python script)
            $json = $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)
              ->getContent();
            $obj = json_decode($json, true);
            // Checking that the uploader is in the DB
            $this->actingAs($this->user)->get("/uploader/" . $obj["id"])
                ->assertStatus(200)
                ->assertJsonFragment(["id" => $obj["id"]])
                ->assertJsonFragment(["name" => "test0"])
                ->assertJsonFragment(["state" => "0"])
                ->assertJsonFragment(["port" => "40000"])
                ->assertJsonFragment(["pipport" => "0"])
                ->assertJsonFragment(["aptport" => "0"]);
        } else {
            // Getting the new uploader
            $obj = Uploader::where("name", "=", "test0")->first();
            // Checking that the uploader is in the DB
            $this->assertTrue($obj["name"] == "test0");
            $this->assertTrue($obj["state"] == "0");
            $this->assertTrue($obj["port"] == 40000);
            $this->assertTrue($obj["pipport"] == 0);
            $this->assertTrue($obj["aptport"] == 0);
        }

        // Checking if a folder has been created
        $cmd = 'cd /var/www/data-diode/src/storage/app/files && ';
        $cmd .= '[ -d test0 ] &&  echo 1 || echo 0';
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $output = $process->getOutput();
            $this->assertTrue(trim($output) == "1");
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            $this->assertTrue(trim($output) == "1");
        }

        // Checking if supervisorctl has run the new uploader's config
        $cmd = 'supervisorctl pid blindftp-' . $obj['name'];
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $output = $process->getOutput();
            $this->assertTrue(ctype_digit(trim($output)) && intval(trim($output)) > 0);
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            $this->assertTrue(ctype_digit(trim($output)) && intval(trim($output)) > 0);
        }

        if (env("DIODE_IN", true)) {
            sleep(20);

            // Deleting the uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);
            
            // Checking that the uploader is not in the DB
            $this->assertTrue(Uploader::where("name", "=", "test0")->count() == 0);

            // Checking if a folder has been created
            $cmd = 'cd /var/www/data-diode/src/storage/app/files && ';
            $cmd .= '[ -d test0 ] &&  echo 1 || echo 0';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
                $output = $process->getOutput();
                $this->assertTrue(trim($output) == "0");
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(trim($output) == "0");
            }

            // Checking if supervisorctl has run the new uploader's config
            $cmd = 'supervisorctl pid blindftp-' . $obj['name'];
            $process = new Process($cmd);
            try {
                $process->mustRun();
                $output = $process->getOutput();
                $this->assertFalse(ctype_digit(trim($output)));
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                // The output should be a program non existing error
                // and not a number
                $this->assertFalse(ctype_digit(trim($output)));
            }

        } else {
            sleep(20);
            // The uploader should have been deleted thanks to the Python script...

            // Checking that the uploader is not in the DB
            $this->assertTrue(Uploader::where("name", "=", "test0")->count() == 0);

            // Checking if a folder has been created
            $cmd = 'cd /var/www/data-diode/src/storage/app/files && ';
            $cmd .= '[ -d test0 ] &&  echo 1 || echo 0';
            $process = new Process($cmd);
            try {
                $process->mustRun();            
                $output = $process->getOutput();
                $this->assertTrue(trim($output) == "0");
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                $this->assertTrue(trim($output) == "0");
            }

            // Checking if supervisorctl has run the new uploader's config
            $cmd = 'supervisorctl pid blindftp-' . $obj['name'];
            $process = new Process($cmd);
            try {
                $process->mustRun();            
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                // The output should be a program non existing error
                // and not a number
                $this->assertFalse(ctype_digit(trim($output)));
            }
        }
    }
}
