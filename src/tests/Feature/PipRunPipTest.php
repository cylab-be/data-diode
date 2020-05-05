<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;
use Illuminate\Support\Facades\Storage;

/**
 * Check if if the downloaded package is in the uploader's
 * folder. Also checks if the package is sent from diodein
 * to diodeout.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. 
 */
class PipRunPipTest extends TestCase
{
    private $user;
    private $uploader;

    /**
     *  Method run before each test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        if (env("DIODE_IN", true)) {
            // Adding the new uploader via POST (to launch the Python script)
            $json = $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)->getContent();
            $this->uploader = json_decode($json, true);
        } else {
            // Getting the new uploader
            $this->uploader = Uploader::where('name', '=', 'test0')->first();
        }
    }

    /**
     *  Method run after each test.
     */
    public function tearDown()
    {
        if (env("DIODE_IN", true)) {
            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $this->uploader["id"])
                ->assertStatus(204);
        }
        $this->user->delete();
        parent::tearDown();
    }

    public function testPostPipRunPip()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "pip/package/" . $this->uploader["id"], [
                "package" => "requests",
            ])->assertStatus(200);

            // Checking that there is the mirror folder
            Storage::disk('diode_local_test')->assertExists('simple/requests');

            // The requests package takes very little time to be downloaded,
            // this time will allow its transfer to diodeout before the 
            // uploader is deleted.
            sleep(20);
        } else {
            $contains = false;
            while (Uploader::where('name', '=', 'test0')->count() != 0) {
                // Diodeout should receive the folder while diodein is sending
                // it at least once before diodein's test destroys the channel.
                if (Storage::disk('diode_local_test')->exists('simple/requests')) {
                    $this->assertTrue(true);
                    $contains = true;
                    break;
                }
            }
            if (!$contains) {
                $this->assertTrue(false);
            }
        }
    }
}