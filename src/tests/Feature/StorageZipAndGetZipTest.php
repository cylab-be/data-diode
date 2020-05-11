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
 * Check if the zip feature allows to zip
 * a file uploaded and sent through the data diode.
 * 
 * This test must be run on diodein first. Then wait ~10 
 * seconds and run it on diodeout. 
 */
class StorageZipAndGetZipTest extends TestCase
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

    public function testStorageZipAndGetZip() {
        if (env("DIODE_IN", true)) {
            // Adding the new uploader via POST (to launch the Python script)
            $json = $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)->getContent();
            $obj = json_decode($json, true);

            // Uploading a file
            $this->actingAs($this->user)->json("POST", "upload/" . $obj['id'], [
                "input_file" => UploadedFile::fake()->image('folder')->size(1),
                "input_file_full_path" => "folder",
            ])->assertStatus(200);
            
            // Checking the file has been uploaded
            Storage::disk('diode_local_test')->assertExists('folder');
            
            // Waiting for diodeout            
            sleep(20);

            // Deleting the new uploader
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                 ->assertStatus(204);

        } else {
            // Getting the new uploader
            $obj = Uploader::where('name', '=', 'test0')->first();

            // The file should have been sent to diodeout...
            Storage::disk('diode_local_test')->assertExists('folder');

            // Zipping the file
            $this->actingAs($this->user)->json("POST", "zip", [
                "time" => 1,
                "name" => $obj['name'],
                "path" => "/" . $obj['name'],
            ])->assertStatus(200);

            // Checking the new zipped folder has appeared
            Storage::disk('diode_local')->assertExists('.zips/' . $obj['name'] . '_1.zip');

            // Getting the zipped file
            $this->actingAs($this->user)->json("POST", "getzip", [
                "time" => 1,
                "name" => $obj['name'],
            ])->assertStatus(200);

            // Removing the file
            $this->actingAs($this->user)->json("POST", "remove", [
                "name" => $obj['name'] . '_1.zip',
                "path" => '/.zips/' . $obj['name'] . '_1.zip',
            ])->assertStatus(200);

            $this->assertFalse(Storage::disk('diode_local')->exists('.zips/' . $obj['name'] . '_1.zip'));
        }
    }
}
