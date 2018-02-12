<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class InterfaceTest extends TestCase
{

    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGetInterfacesNotConnectedJson()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->json("GET", "/interface")
                ->assertStatus(401);
        }
    }

    public function testGetInterfacesNotConnected()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->get("/interface")
            ->assertRedirect("/login");
        }
    }

    public function testGetInterfacesConnectedJson()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->json("GET", "/interface")
            ->assertStatus(200)
            ->assertExactJson([
                "lo",
                "enp0s3"
            ]);
        }
    }

    public function testGetInterfacesConnected()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->get("/interface")
            ->assertStatus(200)
            ->assertExactJson([
                "lo",
                "enp0s3"
            ]);
        }
    }

    public function testGetCurrentInterfaceNotConnectedJson()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->json("GET", "/interface/current")
            ->assertStatus(401);
        }
    }

    public function testGetCurrentInterfaceNotConnected()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->get("/interface/current")
            ->assertRedirect("/login");
        }
    }

    public function testGetCurrentInterfaceConnectedJson()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->json("GET", "/interface/current")
            ->assertStatus(200)
            ->assertSee("lo");
        }
    }

    public function testGetCurrentInterfaceConnected()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->get("/interface/current")
            ->assertStatus(200)
            ->assertSee("lo");
        }
    }

    public function testEditCurrentInterfaceNotConnectedJson()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->json("POST", "/interface/edit", ["id" => "enp0s3"])
            ->assertStatus(401);
        }
    }

    public function testEditCurrentInterfaceNotConnected()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->post("/interface/edit", ["id" => "enp0s3"])
            ->assertRedirect("/login");
        }
    }

    public function testEditCurrentInterfaceJsonOK()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->json("POST", "/interface/edit", ["id" => "enp0s3"])
            ->assertStatus(200);
            $this->actingAs($this->user)->json("GET", "/interface/current")
            ->assertSee("enp0s3");
            $this->actingAs($this->user)->json("POST", "/interface/edit", ["id" => "lo"]);
        }
    }

    public function testEditCurrentInterfaceOK()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->post("/interface/edit", ["id" => "enp0s3"])
            ->assertStatus(200);
            $this->actingAs($this->user)->get("/interface/current")
            ->assertSee("enp0s3");
            $this->actingAs($this->user)->json("POST", "/interface/edit", ["id" => "lo"]);
        }
    }

    public function testEditCurrentInterfaceJsonWrongInterface()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->json("POST", "/interface/edit", ["id" => "wrong"])
            ->assertStatus(422);
            $this->actingAs($this->user)->json("GET", "/interface/current")
            ->assertSee("lo");
        }
    }

    public function testEditCurrentInterfaceWrongInterface()
    {
        if (!env('SKIP_INTERFACE_TESTS', false)) {
            $this->actingAs($this->user)->post("/interface/edit", ["id" => "wrong"])
            ->assertStatus(302);
            $this->actingAs($this->user)->get("/interface/current")
            ->assertSee("lo");
        }
    }

    public function tearDown()
    {
        $this->user->delete();
        parent::tearDown();
    }
}
