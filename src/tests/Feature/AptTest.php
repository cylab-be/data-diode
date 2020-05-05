<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;

class AptTest extends TestCase
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

    public function testPostAptAddMirrorMissingUrl()
    {
        $this->actingAs($this->user)->json("POST", "apt/mirror/" . $this->uploader["id"], [
            // no url
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostAptAddMirrorWrongUrlType()
    {
        $this->actingAs($this->user)->json("POST", "apt/mirror/" . $this->uploader["id"], [
            "url" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostAptAddMirrorBadRegexUrl1()
    {
        $this->actingAs($this->user)->json("POST", "apt/mirror/" . $this->uploader["id"], [
            "url" => 'htt://deb.opera.com/opera',
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

}
