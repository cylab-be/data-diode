<?php

namespace Tests\Feature\Separate;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class TestAccess extends TestCase
{
    
    private $user;
    
    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }
    
    public function testAccessConfigurationNotConnected()
    {
        $response = $this->get("/");
        $response->assertRedirect("/login");
    }
    
    public function testAccessConfigurationConnected()
    {
        $response = $this->actingAs($this->user)->get("/");
        $response->assertStatus(200);
    }
    
    public function tearDown()
    {
        $this->user->delete();
        parent::tearDown();
    }
}
