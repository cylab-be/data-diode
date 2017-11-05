<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Rule;
use App\Http\Resources\Rule as RuleResource;

class RuleController extends Controller
{
	
	public function __construct(){
		$this->middleware("auth");
	}
    
    public function create(Request $request) {
        //
    }
    
    public function getAll() {
        return RuleResource::collection(Rule::all());
    }

    public function get($id) {
        return new RuleResource(Rule::findOrFail($id));
    }

    public function edit(Request $request, $id) {
        //
    }

    public function delete($id) {
        //
    }
}
