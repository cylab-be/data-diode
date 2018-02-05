<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Rule;
use App\Http\Resources\Rule as RuleResource;
use App\Http\Requests\CreateRuleRequest;
use App\Http\Requests\EditRuleRequest;

class RuleController extends Controller
{

    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
    }

    public function create(CreateRuleRequest $request)
    {
        return response()->json(Rule::create($request->all()), 201);
    }

    public function retrieveAll()
    {
        return RuleResource::collection(Rule::all());
    }

    public function retrieve(Rule $rule)
    {
        return new RuleResource($rule);
    }

    public function update(EditRuleRequest $request, Rule $rule)
    {
        $rule->update($request->all());
        return response()->json($rule, 200);
    }

    public function delete(Rule $rule)
    {
        $rule->delete();
        return response()->json(null, 204);
    }
}
