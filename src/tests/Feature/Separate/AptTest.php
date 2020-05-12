<?php

namespace Tests\Feature\Separate;

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
    
    public function testPostAptAddMirrorNotConnectedJson()
    {
        $this->json("POST", "apt/mirror/" . $this->uploader->id, [
            "url" => 'https://deb.opera.com/opera',
        ])->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostAptAddMirrorNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("apt/mirror/" . $this->uploader->id, [
                "url" => 'https://deb.opera.com/opera',
            ])->assertRedirect("/login");
        } else {
            $this->post("apt/mirror/" . $this->uploader->id, [
                "url" => 'https://deb.opera.com/opera',
            ])->assertStatus(404);
        }
    }

    public function testPostAptAddMirrorMissingUrl()
    {
        $this->actingAs($this->user)->json("POST", "apt/mirror/" . $this->uploader->id, [
            // no url
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostAptAddMirrorWrongUrlType()
    {
        $this->actingAs($this->user)->json("POST", "apt/mirror/" . $this->uploader->id, [
            "url" => 1,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostAptAddMirrorBadRegexUrl1()
    {
        $this->actingAs($this->user)->json("POST", "apt/mirror/" . $this->uploader->id, [
            "url" => 'htt://deb.opera.com/opera',
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

}
