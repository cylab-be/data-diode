<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\User;
use App\Uploader;

class StorageTest extends TestCase
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
        $this->uploader = Uploader::create([
            "name"      => "ftp",
            "state"     => "0",
            "port"      => "10000",
            "pipport"   => "0",
            "aptport"   => "0",
        ]);
    }

    /**
     *  Method run after each test.
     */
    public function tearDown()
    {
        $this->user->delete();
        if (Uploader::find($this->uploader->id)) {
            Uploader::destroy($this->uploader->id);
        }
    }

    public function testPostUploadNotConnectedJson()
    {
        $this->json("POST", "upload/" . $this->uploader->id, [
            "input_file" => UploadedFile::fake()->image('upload.jpg')->size(1),
            "input_file_full_path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostUploadNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("upload/" . $this->uploader->id, [
                "input_file" => UploadedFile::fake()->image('upload.jpg')->size(1),
                "input_file_full_path" => "image.png",
            ])->assertRedirect("/login");
        } else {
            $this->post("upload/" . $this->uploader->id, [
                "input_file" => UploadedFile::fake()->image('upload.jpg')->size(1),
                "input_file_full_path" => "image.png",
            ])->assertStatus(404);
        }
    }

    public function testPostUploadMissingInputFile()
    {
        $this->actingAs($this->user)->json("POST", "upload/" . $this->uploader->id, [
            // no input_file
            "input_file_full_path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostUploadWrongInputFileType()
    {
        $this->actingAs($this->user)->json("POST", "upload/" . $this->uploader->id, [
            "input_file" => 1,
            "input_file_full_path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostUploadTooBigInputFileSize()
    {
        $this->actingAs($this->user)->json("POST", "upload/" . $this->uploader->id, [
            "input_file" => UploadedFile::fake()->image('upload.jpg')->size(2 * env('MAX_UPLOAD_SIZE_GB', 1) * 1024 * 1024),
            "input_file_full_path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostUploadMissingInputFileFullPath()
    {
        $this->actingAs($this->user)->json("POST", "upload/" . $this->uploader->id, [
            "input_file" => UploadedFile::fake()->image('upload.jpg')->size(1),
            // no input_file_full_path
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostUploadWrongInputFileFullPathType()
    {
        $this->actingAs($this->user)->json("POST", "upload/" . $this->uploader->id, [
            "input_file" => UploadedFile::fake()->image('upload.jpg')->size(1),
            "input_file_full_path" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostDownloadNotConnectedJson()
    {
        $this->json("POST", "download", [
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 401);
    }

    public function testPostDownloadNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("download", [
                "path" => "image.png",
            ])->assertStatus(404);                
        } else {
            $this->post("download", [
                "path" => "image.png",
            ])->assertRedirect("/login");
        }
    }

    public function testPostDownloadMissingPath()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            // no path
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostDownloadWrongPathType()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            "path" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostDownloadBadPathRegex1()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            "path" => "../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostDownloadBadPathRegex2()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            "path" => "test/../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostDownloadBadPathRegex3()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            "path" => "test/..",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipNotConnectedJson()
    {
        $this->json("POST", "zip", [
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 401);
    }

    public function testPostZipNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("zip", [
                "time" => 1,
                "name" => "image.png",
                "path" => "image.png",
            ])->assertStatus(404);
        } else {
            $this->post("zip", [
                "time" => 1,
                "name" => "image.png",
                "path" => "image.png",
            ])->assertRedirect("/login");
        }
    }

    public function testPostZipMissingTime()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            // no time
            "name" => "image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipWrongTimeType()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => "fail",
            "name" => "image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipMissingName()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            // no name
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipWrongNameType()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => 1,
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadNameRegex1()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "im..age.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadNameRegex2()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "im/age.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadNameRegex3()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "../image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadNameRegex4()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "../image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipMissingPath()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "name",
            // no path
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipWrongPathType()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "name",
            "path" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadPathRegex1()
    {
        $this->actingAs($this->user)->json("POST", "zip", [
            "time" => 1,
            "name" => "name",
            "path" => "../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadPathRegex2()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            "time" => 1,
            "name" => "name",
            "path" => "test/../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostZipBadPathRegex3()
    {
        $this->actingAs($this->user)->json("POST", "download", [
            "time" => 1,
            "name" => "name",
            "path" => "test/..",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipNotConnectedJson()
    {
        $this->json("POST", "getzip", [
            "time" => 1,
            "name" => "name",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 401);
    }

    public function testPostGetZipNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("getzip", [
                "time" => 1,
                "name" => "image.png",
            ])->assertStatus(404);                
        } else {
            $this->post("getzip", [
                "time" => 1,
                "name" => "image.png",
            ])->assertRedirect("/login");
        }
    }

    public function testPostGetZipMissingTime()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "name" => "name",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipWrongTimeType()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => "fail",
            "name" => "name",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipMissingName()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => 1,
            // no name
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipWrongNameType()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => 1,
            "name" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipBadNameRegex1()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => 1,
            "name" => "im..age.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipBadNameRegex2()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => 1,
            "name" => "im/age.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipBadNameRegex3()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => 1,
            "name" => "../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostGetZipBadNameRegex4()
    {
        $this->actingAs($this->user)->json("POST", "getzip", [
            "time" => 1,
            "name" => "../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveNotConnectedJson()
    {
        $this->json("POST", "remove", [
            "name" => "image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 401);
    }

    public function testPostRemoveNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("remove", [
                "name" => "image.png",
                "path" => "image.png",
            ])->assertStatus(404);                
        } else {
            $this->post("remove", [
                "name" => "image.png",
                "path" => "image.png",
            ])->assertRedirect("/login");
        }
    }

    public function testPostRemoveMissingName()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            // no name
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveWrongNameType()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => 1,
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadNameRegex1()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "im..age.png",
            "path" => "image.png,"
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadNameRegex2()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "im/age.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadNameRegex3()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "../image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadNameRegex4()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "../image.png",
            "path" => "image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }
    
    public function testPostRemoveMissingPath()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "image.png",
            // no path
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveWrongPathType()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "image.png",
            "path" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadPathRegex1()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "image.png",
            "path" => "../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadPathRegex2()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "image.png",
            "path" => "test/../image.png",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }

    public function testPostRemoveBadPathRegex3()
    {
        $this->actingAs($this->user)->json("POST", "remove", [
            "name" => "image.png",
            "path" => "test/..",
        ])->assertStatus(env("DIODE_IN", true) ? 404 : 422);
    }
}
