<?php

namespace App\Observers;

use App\Rule;
use App\Jobs\CreateIptablesRuleJob;
use App\Jobs\DeleteIptablesRuleJob;

/**
 * Observer for Rule manipulations
 */
class RuleObserver
{

    /**
     * Observes any created rule event
     * @param  Rule   $rule the created rule
     * @return void
     */
    public function created(Rule $rule)
    {
        CreateIptablesRuleJob::dispatch($rule);
    }

    /**
     * Observes any deleted rule event
     * @param  Rule   $rule the deleted rule
     * @return void
     */
    public function deleted(Rule $rule)
    {
        DeleteIptablesRuleJob::dispatch($rule);
    }


    /**
     * Observes any updated rule event (before it is updated)
     * @param  Rule   $rule the not yet updated rule
     * @return void
     */
    public function updating(Rule $rule)
    {
        $oldRule = Rule::find($rule->id);
        DeleteIptablesRuleJob::dispatch($oldRule);
    }


    /**
     * Observes any updated rule event (after it got updated)
     * @param  Rule   $rule the updated rule
     * @return void
     */
    public function updated(Rule $rule)
    {
        CreateIptablesRuleJob::dispatch($rule);
    }
}
