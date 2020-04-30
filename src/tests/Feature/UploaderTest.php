<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;

class UploaderTest extends TestCase
{

    private $user;
    private $uploader;
    private $uploader2;
    private $uploader3;

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
        $this->uploader2 = Uploader::create([
            "name"      => "pip",
            "state"     => "0",
            "port"      => "20000",
            "pipport"   => "20001",
            "aptport"   => "0",
        ]);
        $this->uploader3 = Uploader::create([
            "name"      => "apt",
            "state"     => "0",
            "port"      => "30000",
            "pipport"   => "0",
            "aptport"   => "30001",
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
        if (Uploader::find($this->uploader2->id)) {
            Uploader::destroy($this->uploader2->id);
        }
        if (Uploader::find($this->uploader3->id)) {
            Uploader::destroy($this->uploader3->id);
        }
        parent::tearDown();
    }


    public function testGetUploaderNotConnectedJson()
    {
        $this->json("GET", "/uploader/" . $this->uploader->id)
            ->assertStatus(401);
    }

    public function testGetUploaderNotConnected()
    {
        $this->get("/uploader/" . $this->uploader->id)
            ->assertRedirect("/login");
    }

    public function testGetUploaderConnectedJson()
    {
        $this->actingAs($this->user)->json("GET", "/uploader/" . $this->uploader->id)
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->uploader->id])
            ->assertJsonFragment(["name" => $this->uploader->name])
            ->assertJsonFragment(["state" => $this->uploader->state])
            ->assertJsonFragment(["port" => $this->uploader->port])
            ->assertJsonFragment(["pipport" => $this->uploader->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader->aptport]);
    }

    public function testGetUploaderConnected()
    {
        $this->actingAs($this->user)->get("/uploader/" . $this->uploader->id)
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->uploader->id])
            ->assertJsonFragment(["name" => $this->uploader->name])
            ->assertJsonFragment(["state" => $this->uploader->state])
            ->assertJsonFragment(["port" => $this->uploader->port])
            ->assertJsonFragment(["pipport" => $this->uploader->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader->aptport]);
    }

    public function testGetUploaderNotFoundJson()
    {
        $this->actingAs($this->user)->json("GET", "/uploader/9999999999999999999999")
            ->assertStatus(404);
    }

    public function testGetUploaderNotFound()
    {
        $this->actingAs($this->user)->get("/uploader/9999999999999999999999")
            ->assertStatus(404);
    }

    public function testGetAllUploadersNotConnectedJson()
    {
        $this->json("GET", "/uploader")
            ->assertStatus(401);
    }

    public function testGetAllUploadersNotConnected()
    {
        $this->get("/uploader")
            ->assertRedirect("/login");
    }

    public function testGetAllUploadersConnectedJson()
    {
        $this->actingAs($this->user)->json("GET", "/uploader")
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->uploader->id])
            ->assertJsonFragment(["name" => $this->uploader->name])
            ->assertJsonFragment(["state" => $this->uploader->state])
            ->assertJsonFragment(["port" => $this->uploader->port])
            ->assertJsonFragment(["pipport" => $this->uploader->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader->aptport])
            ->assertJsonFragment(["id" => $this->uploader2->id])
            ->assertJsonFragment(["name" => $this->uploader2->name])
            ->assertJsonFragment(["state" => $this->uploader2->state])
            ->assertJsonFragment(["port" => $this->uploader2->port])
            ->assertJsonFragment(["pipport" => $this->uploader2->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader2->aptport])
            ->assertJsonFragment(["id" => $this->uploader3->id])
            ->assertJsonFragment(["name" => $this->uploader3->name])
            ->assertJsonFragment(["state" => $this->uploader3->state])
            ->assertJsonFragment(["port" => $this->uploader3->port])
            ->assertJsonFragment(["pipport" => $this->uploader3->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader3->aptport]);
    }

    public function testGetAllUploadersConnected()
    {
        $this->actingAs($this->user)->get("/uploader")
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->uploader->id])
            ->assertJsonFragment(["name" => $this->uploader->name])
            ->assertJsonFragment(["state" => $this->uploader->state])
            ->assertJsonFragment(["port" => $this->uploader->port])
            ->assertJsonFragment(["pipport" => $this->uploader->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader->aptport])
            ->assertJsonFragment(["id" => $this->uploader2->id])
            ->assertJsonFragment(["name" => $this->uploader2->name])
            ->assertJsonFragment(["state" => $this->uploader2->state])
            ->assertJsonFragment(["port" => $this->uploader2->port])
            ->assertJsonFragment(["pipport" => $this->uploader2->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader2->aptport])
            ->assertJsonFragment(["id" => $this->uploader3->id])
            ->assertJsonFragment(["name" => $this->uploader3->name])
            ->assertJsonFragment(["state" => $this->uploader3->state])
            ->assertJsonFragment(["port" => $this->uploader3->port])
            ->assertJsonFragment(["pipport" => $this->uploader3->pipport])
            ->assertJsonFragment(["aptport" => $this->uploader3->aptport]);
    }

    public function testDeleteUploaderNotConnectedJson()
    {
        if (env("DIODE_IN", true)) {
            $this->json("DELETE", "/uploader/" . $this->uploader->id)
                ->assertStatus(401);
        } else {
            $this->json("DELETE", "/uploader/" . $this->uploader->id)
                ->assertStatus(405);
        }
    }

    public function testDeleteUploaderNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->delete("/uploader/" . $this->uploader->id)
                ->assertRedirect("/login");
        } else {
            $this->delete("/uploader/" . $this->uploader->id)
                ->assertStatus(405);
        }
    }

    public function testDeleteUploaderConnectedJson()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("DELETE", "/uploader/" . $this->uploader->id)
                ->assertStatus(204);
            $this->actingAs($this->user)->get("/uploader/" . $this->uploader->id)
                ->assertStatus(404);
        } else {
            $this->actingAs($this->user)->json("DELETE", "/uploader/" . $this->uploader->id)
                ->assertStatus(405); // 405: method not allowed
        }
    }

    public function testDeleteUploaderConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->delete("/uploader/" . $this->uploader->id)
                ->assertStatus(204);
            $this->actingAs($this->user)->get("/uploader/" . $this->uploader->id)
                ->assertStatus(404);

        } else {
            $this->actingAs($this->user)->delete("/uploader/" . $this->uploader->id)
                ->assertStatus(405);
        }
    }

    public function testDeleteUploaderNotFoundJson()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("DELETE", "/uploader/9999999999999999999999")
                ->assertStatus(404);
        } else {
            $this->actingAs($this->user)->json("DELETE", "/uploader/9999999999999999999999")
                ->assertStatus(405);
        }
    }

    public function testDeleteUploaderNotFound()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->delete("/uploader/9999999999999999999999")
                ->assertStatus(404);
        } else {
            $this->actingAs($this->user)->delete("/uploader/9999999999999999999999")
                ->assertStatus(405);
        }
    }

    public function testPostUploaderNotConnectedJson()
    {
        if (env("DIODE_IN", true)) {
            $this->json("POST", "/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(401);
        } else {
            $this->json("POST", "/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertRedirect("/login");
        } else {
            $this->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderConnectedJson()
    {
        if (env("DIODE_IN", true)) {
            $json = $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)
            ->getContent();
            $obj = json_decode($json, true);
            $this->actingAs($this->user)->get("/uploader/" . $obj["id"])
                ->assertStatus(200)
                ->assertJsonFragment(["id" => $obj["id"]])
                ->assertJsonFragment(["name" => "test0"])
                ->assertJsonFragment(["state" => "0"])
                ->assertJsonFragment(["port" => "40000"])
                ->assertJsonFragment(["pipport" => "0"])
                ->assertJsonFragment(["aptport" => "0"]);
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"]);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderConnected()
    {
        if (env("DIODE_IN", true)) {
            $json = $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(201)
            ->getContent();
            $obj = json_decode($json, true);
            $this->actingAs($this->user)->get("/uploader/" . $obj["id"])
                ->assertStatus(200)
                ->assertJsonFragment(["id" => $obj["id"]])
                ->assertJsonFragment(["name" => "test0"])
                ->assertJsonFragment(["state" => "0"])
                ->assertJsonFragment(["port" => "40000"])
                ->assertJsonFragment(["pipport" => "0"])
                ->assertJsonFragment(["aptport" => "0"]);
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"]);
        } else {
            $this->actingAs($this->user)->post("/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderMissingName()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "port" => 40000,
            ])->assertStatus(422);
        } 
        // else we already proved that POST is not allowed on /uploader
        // but too avoid a risky test result we prove it again (idem 
        // the next tests)
        else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderWrongNameType()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 1,
                "port" => 40000,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 1,
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderWrongNameRegex()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test.0',
                "port" => 40000,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test.0',
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderNonUniqueName()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'ftp',
                "port" => 50000,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'ftp',
                "port" => 50000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderMinSizeName()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'aa',
                "port" => 40000,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'aa',
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderMaxSizeName()
    {
        $name = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $this->assertEquals(strlen($name), 256);
        if (env("DIODE_IN", true)) {            
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => $name,
                "port" => 40000,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => $name,
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderMissingPort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderWrongPortType()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
                "port" => "fail",
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
                "port" => "fail",
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderNonUniquePort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 10000,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 10000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderUnderRangePort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 1024,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 1024,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderAboveRangePort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 65536,
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 65536,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderUsedByOtherProgramPort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 9001, // used by supervisor
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 9001, // used by supervisor
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderUsedByOtherPipModulePort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 20001, // used by pip uploader's PIP module
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 20001, // used by pip uploader's PIP module
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderUsedByOtherAptModulePort()
    {
        if (env("DIODE_IN", true)) {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 30001, // used by apt uploader's APT module
            ])->assertStatus(422);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => 'test0',
                "port" => 30001, // used by apt uploader's APT module
            ])->assertStatus(405);
        }
    }

}