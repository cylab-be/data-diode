<?php

namespace Tests\Feature\Separate;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Uploader;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        $this->json("DELETE", "/uploader/" . $this->uploader->id)
            ->assertStatus(env("DIODE_IN", true) ? 401 : 405);
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
        $this->actingAs($this->user)->json("DELETE", "/uploader/9999999999999999999999")
            ->assertStatus(env("DIODE_IN", true) ? 404 : 405);
    }

    public function testDeleteUploaderNotFound()
    {
        $this->actingAs($this->user)->delete("/uploader/9999999999999999999999")
            ->assertStatus(env("DIODE_IN", true) ? 404 : 405);
    }

    public function testPostUploaderNotConnectedJson()
    {
        $this->json("POST", "/uploader", [
            "name" => "test0",
            "port" => 40000,
        ])->assertStatus(env("DIODE_IN", true) ? 401 : 405);
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
            $this->actingAs($this->user)->delete("/uploader/" . $obj["id"])
                ->assertStatus(204);
        } else {
            $this->actingAs($this->user)->json("POST", "/uploader", [
                "name" => "test0",
                "port" => 40000,
            ])->assertStatus(405);
        }
    }

    public function testPostUploaderMissingName()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "port" => 40000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);
    }

    public function testPostUploaderWrongNameType()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 1,
            "port" => 40000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);
    }

    public function testPostUploaderWrongNameRegex()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test$0',
            "port" => 40000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);
    }

    public function testPostUploaderNonUniqueName()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'ftp',
            "port" => 50000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);
    }

    public function testPostUploaderMinSizeName()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'aa',
            "port" => 40000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderMaxSizeName()
    {
        $name = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $name .= "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        $this->assertEquals(strlen($name), 256);
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => $name,
            "port" => 40000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderMissingPort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => "test0",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderWrongPortType()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => "test0",
            "port" => "fail",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderNonUniquePort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test0',
            "port" => 10000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderUnderRangePort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test0',
            "port" => 1024,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderAboveRangePort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test0',
            "port" => 65536,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderUsedByOtherProgramPort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test0',
            "port" => 9001, // used by supervisor
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderUsedByOtherPipModulePort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test0',
            "port" => 20001, // used by pip uploader's PIP module
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostUploaderUsedByOtherAptModulePort()
    {
        $this->actingAs($this->user)->json("POST", "/uploader", [
            "name" => 'test0',
            "port" => 30001, // used by apt uploader's APT module
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 405);        
    }

    public function testPostRemovePipNotConnectedJson()
    {
        $this->json("DELETE", "pip/" . $this->uploader->id)
             ->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostRemovePipNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->delete("pip/" . $this->uploader->id)
                 ->assertRedirect("/login");
        } else {
            $this->post("pip/" . $this->uploader->id)
                 ->assertStatus(404);
        }
    }

    public function testPostAddPipNotConnectedJson()
    {
        $this->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 10001,
        ])->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostAddPipNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("pip/" . $this->uploader->id, [
                "name" => 'test0',
                "port" => 10001,
            ])->assertRedirect("/login");
        } else {
            $this->post("pip/" . $this->uploader->id, [
                "name" => 'test0',
                "port" => 10001,
            ])->assertStatus(404);
        }
    }

    public function testPostAddPipMissingPort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => "test0",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddPipWrongPortType()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => "test0",
            "port" => "fail",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddPipNonUniquePort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 10000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostAddPipUnderRangePort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 1024,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddPipAboveRangePort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 65536,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddPipUsedByOtherProgramPort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 9001, // used by supervisor
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddPipUsedByOtherPipModulePort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 20001, // used by pip uploader's PIP module
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddPipUsedByOtherAptModulePort()
    {
        $this->actingAs($this->user)->json("POST", "pip/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 30001, // used by apt uploader's APT module
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostRemoveAptNotConnectedJson()
    {
        $this->json("DELETE", "apt/" . $this->uploader->id)
             ->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostRemoveAptNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->delete("apt/" . $this->uploader->id)
                 ->assertRedirect("/login");
        } else {
            $this->post("apt/" . $this->uploader->id)
                 ->assertStatus(404);
        }
    }

    public function testPostAddAptNotConnectedJson()
    {
        $this->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 10001,
        ])->assertStatus(env("DIODE_IN", true) ? 401 : 404);
    }

    public function testPostAddAptNotConnected()
    {
        if (env("DIODE_IN", true)) {
            $this->post("apt/" . $this->uploader->id, [
                "name" => 'test0',
                "port" => 10001,
            ])->assertRedirect("/login");
        } else {
            $this->post("apt/" . $this->uploader->id, [
                "name" => 'test0',
                "port" => 10001,
            ])->assertStatus(404);
        }
    }

    public function testPostAddAptMissingPort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => "test0",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);
    }

    public function testPostAddAptWrongPortType()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => "test0",
            "port" => "fail",
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddAptNonUniquePort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 10000,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddAptUnderRangePort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 1024,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddAptAboveRangePort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 65536,
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddAptUsedByOtherProgramPort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 9001, // used by supervisor
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddAptUsedByOtherPipModulePort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 20001, // used by pip uploader's PIP module
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostAddAptUsedByOtherAptModulePort()
    {
        $this->actingAs($this->user)->json("POST", "apt/" . $this->uploader->id, [
            "name" => 'test0',
            "port" => 30001, // used by apt uploader's APT module
        ])->assertStatus(env("DIODE_IN", true) ? 422 : 404);        
    }

    public function testPostUploaderEmptyFolderNotConnectedJson()
    {
        $this->json("PUT", "/uploader/empty/" . $this->uploader->id)
             ->assertStatus(401);
    }

    public function testPostUploaderEmptyFolderNotConnected()
    {
        $this->put("/uploader/empty/" . $this->uploader->id)
             ->assertRedirect("/login");
    }

    public function testPostUploaderStopProgramNotConnectedJson()
    {
        $this->json("PUT", "/uploader/stop/" . $this->uploader->id)
             ->assertStatus(401);
    }

    public function testPostUploaderStopProgramNotConnected()
    {
        $this->put("/uploader/stop/" . $this->uploader->id)
             ->assertRedirect("/login");
    }

    public function testPostUploaderRestartProgramNotConnectedJson()
    {
        $this->json("PUT", "/uploader/restart/" . $this->uploader->id)
             ->assertStatus(401);
    }

    public function testPostUploaderRestartProgramNotConnected()
    {
        $this->put("/uploader/restart/" . $this->uploader->id)
             ->assertRedirect("/login");
    }
}