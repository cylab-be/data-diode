<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Rule;

class RuleTest extends TestCase
{
    
    private $user;
    private $rule;
    private $rule2;
    
    public function setUp() {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->rule = Rule::create([
            "input_port" => "25565",
            "output_port" => "25565",
            "destination" => "192.168.1.1"
        ]);
        $this->rule2 = Rule::create([
            "input_port" => "25566",
            "output_port" => "25566",
            "destination" => "192.168.1.1"
        ]);
    }
    
    public function testGetRuleNotConnectedJson(){
        $this->json("GET", "/rule/".$this->rule->id)
            ->assertStatus(401);
    }
    
    public function testGetRuleNotConnected(){
        $this->get("/rule/".$this->rule->id)
            ->assertRedirect("/login");
    }
    
    public function testGetRuleConnectedJson(){
        $this->actingAs($this->user)->json("GET", "/rule/".$this->rule->id)
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->rule->id])
            ->assertJsonFragment(["input_port" => $this->rule->input_port])
            ->assertJsonFragment(["output_port" => $this->rule->output_port])
            ->assertJsonFragment(["destination" => $this->rule->destination]);
    }
    
    public function testGetRuleConnected(){
        $this->actingAs($this->user)->get("/rule/".$this->rule->id)
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->rule->id])
            ->assertJsonFragment(["input_port" => $this->rule->input_port])
            ->assertJsonFragment(["output_port" => $this->rule->output_port])
            ->assertJsonFragment(["destination" => $this->rule->destination]);
    }
    
    public function testGetRuleNotFoundJson(){
        $this->actingAs($this->user)->json("GET", "/rule/9999999999999999999999")
            ->assertStatus(404);
    }
    
    public function testGetRuleNotFound(){
        $this->actingAs($this->user)->get("/rule/9999999999999999999999")
            ->assertStatus(404);
    }
    
    public function testGetAllRulesNotConnectedJson(){
        $this->json("GET", "/rule")
            ->assertStatus(401);
    }
    
    public function testGetAllRulesNotConnected(){
        $this->get("/rule")
            ->assertRedirect("/login");
    }
    
    public function testGetAllRulesConnectedJson(){
        $this->actingAs($this->user)->json("GET", "/rule")
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->rule->id])
            ->assertJsonFragment(["input_port" => $this->rule->input_port])
            ->assertJsonFragment(["output_port" => $this->rule->output_port])
            ->assertJsonFragment(["destination" => $this->rule->destination])
            ->assertJsonFragment(["id" => $this->rule2->id])
            ->assertJsonFragment(["input_port" => $this->rule2->input_port])
            ->assertJsonFragment(["output_port" => $this->rule2->output_port])
            ->assertJsonFragment(["destination" => $this->rule2->destination]);
    }
    
    public function testGetAllRulesConnected(){
        $this->actingAs($this->user)->get("/rule")
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $this->rule->id])
            ->assertJsonFragment(["input_port" => $this->rule->input_port])
            ->assertJsonFragment(["output_port" => $this->rule->output_port])
            ->assertJsonFragment(["destination" => $this->rule->destination])
            ->assertJsonFragment(["id" => $this->rule2->id])
            ->assertJsonFragment(["input_port" => $this->rule2->input_port])
            ->assertJsonFragment(["output_port" => $this->rule2->output_port])
            ->assertJsonFragment(["destination" => $this->rule2->destination]);
    }
    
    public function testDeleteRuleNotConnectedJson(){
        $this->json("DELETE", "/rule/" . $this->rule->id)
            ->assertStatus(401);
    }
    
    public function testDeleteRuleNotConnected(){
        $this->delete("/rule/" . $this->rule->id)
            ->assertRedirect("/login");
    }
    
    public function testDeleteRuleConnectedJson(){
        $this->actingAs($this->user)->json("DELETE", "/rule/" . $this->rule->id)
            ->assertStatus(204);
        $this->actingAs($this->user)->get("/rule/" . $this->rule->id)
            ->assertStatus(404);
    }
    
    public function testDeleteRuleConnected(){
        $id = $this->rule->id;
        $this->actingAs($this->user)->delete("/rule/" . $id)
            ->assertStatus(204);
        $this->actingAs($this->user)->get("/rule/" . $id)
            ->assertStatus(404);
    }
    
    public function testDeleteRuleNotFoundJson(){
        $this->actingAs($this->user)->json("DELETE", "/rule/9999999999999999999999")
            ->assertStatus(404);
    }
    
    public function testDeleteRuleNotFound(){
        $this->actingAs($this->user)->delete("/rule/9999999999999999999999")
            ->assertStatus(404);
    }
    
    public function testPostRuleNotConnectedJson(){
        $this->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(401);
    }
    
    public function testPostRuleNotConnected(){
        $this->post("/rule", [
            "input_port" => 25567,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertRedirect("/login");
    }
    
    public function testPostRuleConnectedJson(){
        $json = $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(201)
          ->getContent();
        $obj = json_decode($json, true);
        $this->actingAs($this->user)->get("/rule/" . $obj["id"])
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $obj["id"]])
            ->assertJsonFragment(["input_port" => "25567"])
            ->assertJsonFragment(["output_port" => "25567"])
            ->assertJsonFragment(["destination" => "192.168.1.1"]);
        $this->actingAs($this->user)->delete("/rule/" . $obj["id"]);
    }
    
    public function testPostRuleConnected(){
        $json = $this->actingAs($this->user)->post("/rule", [
            "input_port" => 25567,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(201)
          ->getContent();
        $obj = json_decode($json, true);
        $this->actingAs($this->user)->get("/rule/" . $obj["id"])
            ->assertStatus(200)
            ->assertJsonFragment(["id" => $obj["id"]])
            ->assertJsonFragment(["input_port" => "25567"])
            ->assertJsonFragment(["output_port" => "25567"])
            ->assertJsonFragment(["destination" => "192.168.1.1"]);
        $this->actingAs($this->user)->delete("/rule/" . $obj["id"]);
    }
    
    public function testPostRuleMissingInputPort(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleMissingOutputPort(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleMissingDestination(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 25567
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongInputPortType(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => "nope",
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongOutputPortType(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => "nope",
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongIP(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 25567,
            "destination" => "192.168.1.256"
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongInputPortBound(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 0,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongInputPortBound2(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 65536,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongOutputPortBound(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 0,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleWrongOutputPortBound2(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 65536,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleNonUniqueInputPort(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25565,
            "output_port" => 25567,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPostRuleNonUniqueOutputPort(){
        $this->actingAs($this->user)->json("POST", "/rule", [
            "input_port" => 25567,
            "output_port" => 25565,
            "destination" => "192.168.1.1"
        ])->assertStatus(422);
    }
    
    public function testPutRuleNotConnectedJson(){
        $this->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000
        ])->assertStatus(401);
    }
    
    public function testPutRuleNotConnected(){
        $this->put("/rule/" . $this->rule->id, [
            "input_port" => 30000
        ])->assertRedirect("/login");
    }
    
    public function testPutRuleConnectedJson(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(200);
        $json = $this->actingAs($this->user)->json("GET", "rule/" . $this->rule->id)
            ->assertJsonFragment(["input_port" => "30000"])
            ->assertJsonFragment(["output_port" => "30000"])
            ->assertJsonFragment(["destination" => "192.168.1.2"]);
    }
    
    public function testPutRuleConnected(){
        $this->actingAs($this->user)->put("/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(200);
        $json = $this->actingAs($this->user)->get("rule/" . $this->rule->id)
            ->assertJsonFragment(["input_port" => "30000"])
            ->assertJsonFragment(["output_port" => "30000"])
            ->assertJsonFragment(["destination" => "192.168.1.2"]);
    }
    
    public function testPutRuleNoModification(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 25565,
            "output_port" => 25565,
            "destination" => "192.168.1.1"
        ])->assertStatus(200);
        $json = $this->actingAs($this->user)->get("rule/" . $this->rule->id)
            ->assertJsonFragment(["input_port" => "25565"])
            ->assertJsonFragment(["output_port" => "25565"])
            ->assertJsonFragment(["destination" => "192.168.1.1"]);
    }
    
    public function testPutRuleMissingInputPort(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleMissingOutputPort(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleMissingDestination(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 30000
        ])->assertStatus(422);
    }
    
    public function testPutRuleWrongInputPortType(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => "nope",
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleWrongOutputPortType(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => "nope",
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleWrongDestinationType(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 30000,
            "destination" => "192.168.1.256"
        ])->assertStatus(422);
    }
    
    public function testPutRuleInputPortBound(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 0,
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleInputPortBound2(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 65536,
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleOutputPortBound(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 0,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleOutputPortBound2(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 65536,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleNonUniqueInputPort(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 25566,
            "output_port" => 30000,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function testPutRuleNonUniqueOutputPort(){
        $this->actingAs($this->user)->json("PUT", "/rule/" . $this->rule->id, [
            "input_port" => 30000,
            "output_port" => 25566,
            "destination" => "192.168.1.2"
        ])->assertStatus(422);
    }
    
    public function tearDown(){
        $this->user->delete();
        if(Rule::find($this->rule->id))
            Rule::destroy($this->rule->id);
        if(Rule::find($this->rule2->id))
            Rule::destroy($this->rule2->id);
        parent::tearDown();
    }
}
