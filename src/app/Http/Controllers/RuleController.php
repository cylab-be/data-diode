<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Rule;
use App\Http\Resources\Rule as RuleResource;
use App\Http\Requests\CreateRuleRequest;
use App\Http\Requests\EditRuleRequest;

/**
 * Controller for Rule related request
 */
class RuleController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
    }

    /**
     * Creates a new Rule and stores it
     * @param  CreateRuleRequest $request the request
     * @return mixed the json answer
     */
    public function create(CreateRuleRequest $request)
    {
        $rule = null;
        if (env("DIODE_IN", true)) {
            $rule = Rule::create([
                "input_port" => $request->input("input_port"),
                "output_port" => $request->input("output_port"),
                "destination" => env("DIODE_OUT_IP")
            ]);
        } else {
            $rule = Rule::create($request->all());
        }
        return response()->json($rule, 201);
    }

    /**
     * Gets all currently stored rules
     * @return mixed the answer, a collection of RuleResources
     */
    public function retrieveAll()
    {
        return RuleResource::collection(Rule::all());
    }

    /**
     * Gets one specific rule
     * @param  Rule   $rule the rule
     * @return RuleResource The Rule transformed into a resource for json formatting
     */
    public function retrieve(Rule $rule)
    {
        return new RuleResource($rule);
    }

    /**
     * Updates a given rule
     * @param  EditRuleRequest $request The request made by the user
     * @param  Rule            $rule    The rule to edit
     * @return mixed                    The json rule
     */
    public function update(EditRuleRequest $request, Rule $rule)
    {
        if (env("DIODE_IN", true)) {
            $rule->update([
                "input_port" => $request->input("input_port"),
                "output_port" => $request->input("output_port"),
                "destination" => env("DIODE_OUT_IP")
            ]);
        } else {
            $rule->update($request->all());
        }
        return response()->json($rule, 200);
    }

    /**
     * Deleted a given rule
     * @param  Rule   $rule The rule to delete
     * @return mixed        The json response
     */
    public function delete(Rule $rule)
    {
        $rule->delete();
        return response()->json(null, 204);
    }
}
