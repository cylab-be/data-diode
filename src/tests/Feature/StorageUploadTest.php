<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\User;
use App\Uploader;

/**
 * Check if the upload feature allows to upload a file
 * and check that the file is sent through the data 
 * diode.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. 
 */
class StorageUploadTest extends TestCase
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

    public function testPostStorageUpload() {
        if (env("DIODE_IN", true)) {
            // Adding the new uploader via POST (to launch the Python script)
            $json = $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)->getContent();
            $obj = json_decode($json, true);

            // Uploading a file
            $this->actingAs($this->user)->json("POST", "upload/" . $obj['id'], [
                "input_file" => UploadedFile::fake()->image('upload.jpg')->size(1),
                "input_file_full_path" => "upload.jpg",
            ])->assertStatus(200);
            
            // Checking the file has been uploaded
            Storage::disk('diode_local_test')->assertExists('upload.jpg');
            
            // Waiting for diodeout            
            sleep(20);

            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);

        } else {
            // The file should have been sent to diodeout...
            Storage::disk('diode_local_test')->assertExists('upload.jpg');
        }
    }
}
