<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;

class PipTest extends TestCase
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
        parent::tearDown();
    }

    public function testPostPipRunPipNotConnectedJson()
    {
        $this->json("POST", "pip/package/" . $this->uploader->id, [
            "package" => "requests",
        ])->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostPipRunPipNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("pip/package/" . $this->uploader->id, [
                "package" => "requests",
            ])->assertRedirect("/login");
        } else {
            $this->post("pip/package/" . $this->uploader->id, [
                "package" => "requests",
            ])->assertStatus(404);
        }
    }

    public function testPostPipRunPipMissingPackage()
    {
        $this->actingAs($this->user)->json("POST", "pip/package/" . $this->uploader->id, [
            // no package
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostPipRunPipWrongPackageType()
    {
        $this->actingAs($this->user)->json("POST", "pip/package/" . $this->uploader->id, [
            "package" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

}
