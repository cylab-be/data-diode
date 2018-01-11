<?php

namespace App\Observers;

use App\Rule;
use App\Jobs\CreateIptablesRuleJob;
use App\Jobs\DeleteIptablesRuleJob;

class RuleObserver
{
    
    public function created(Rule $rule)
    {
        CreateIptablesRuleJob::dispatch($rule);
    }
    
    public function deleted(Rule $rule)
    {
        DeleteIptablesRuleJob::dispatch($rule);
    }
    
    public function updating(Rule $rule)
    {
        $oldRule = Rule::find($rule->id);
        DeleteIptablesRuleJob::dispatch($oldRule);
    }
    
    public function updated(Rule $rule)
    {
        CreateIptablesRuleJob::dispatch($rule);
    }
}
