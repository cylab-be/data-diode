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
        $this->json("GET", "/interface")
            ->assertStatus(401);
    }

    public function testGetInterfacesNotConnected()
    {
        $this->get("/interface")
            ->assertRedirect("/login");
    }

    public function testGetInterfacesConnectedJson()
    {
        $this->actingAs($this->user)->json("GET", "/interface")
            ->assertStatus(200)
            ->assertExactJson([
                "lo",
                "enp0s3"
            ]);
    }

    public function testGetInterfacesConnected()
    {
        $this->actingAs($this->user)->get("/interface")
            ->assertStatus(200)
            ->assertExactJson([
                "lo",
                "enp0s3"
            ]);
    }

    public function tearDown()
    {
        $this->user->delete();
        parent::tearDown();
    }

}
 ?>
