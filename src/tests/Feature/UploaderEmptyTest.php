<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * Check if the emptying feature of the folder associated to
 * a channel do empty that folder. 
 * 
 * This test must be run on diodein first. Then wait 10 
 * seconds and run it on diodeout. The diodein test will 
 * wait for 20 seconds before deleting the created channel,
 * allowing the diodeout test to use the uploader created 
 * by the diodein test during those ~20 seconds.
 */
class UploaderEmptyTest extends TestCase
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

    public function testPutUploaderEmptyFolder() {
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
            // Stopping program to avoid the file to be sent to diodeout
            $this->actingAs($this->user)->json("PUT", "/uploader/stop/" . $obj["id"])
                 ->assertStatus(200);
                
            // Upload file
            $this->json('POST', '/upload', [
                'uploader' => $obj['name'],
                'input_file_0' => UploadedFile::fake()->image('upload.jpg')->size(1),
                'input_file_full_path_0' => 'upload.jpg',
            ]);

            // Checking there is a file
            Storage::disk('diode_local_test')->assertExists('upload.jpg');

            // Empyting folder
            $this->actingAs($this->user)->json("PUT", "/uploader/empty/" . $obj["id"])
                 ->assertStatus(200);             
                
            // Checking there is no file
            Storage::disk('diode_local_test')->assertMissing('upload.jpg');

            // Restarting program so that the next file will be sent to diodeout
            $this->actingAs($this->user)->json("PUT", "/uploader/restart/" . $obj["id"])
                ->assertStatus(200);

            // Upload the new file
            $this->json('POST', '/upload', [
                'uploader' => $obj['name'],
                'input_file_0' => UploadedFile::fake()->image('upload.jpg')->size(1),
                'input_file_full_path_0' => 'upload.jpg',
            ]);

            // Checking there is a file
            Storage::disk('diode_local_test')->assertExists('upload.jpg');

            // The file should be sent to diodeout in ~10 seconds max...
        } else {
            // The file should have been received by diodeout...

            // Checking there is a file
            Storage::disk('diode_local_test')->assertExists('upload.jpg');

            // Empyting folder
            $this->actingAs($this->user)->json("PUT", "/uploader/empty/" . $obj["id"])
                 ->assertStatus(200);             
                
            // Checking there is no file
            Storage::disk('diode_local_test')->assertMissing('upload.jpg');
        }

        if (env("DIODE_IN", true)) {
            sleep(20);

            // Empyting folder
            $this->actingAs($this->user)->json("PUT", "/uploader/empty/" . $obj["id"])
                 ->assertStatus(200);             
                
            // Checking there is no file
            Storage::disk('diode_local_test')->assertMissing('upload.jpg');

            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);
        }
    }

}
