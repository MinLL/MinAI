<?php

require_once("action_builder.php");

// Hug Action
registerMinAIAction("ExtCmdHug", "Hug")
    ->withDescription("Embrace #target_object# in a warm hug - shows affection, comfort, or friendship")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [], true)
    ->withEnableCondition(function() {
        return ShouldEnableHarassFunctions($GLOBALS['HERIKA_NAME']);
    })
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Kiss Action
registerMinAIAction("ExtCmdKiss", "Kiss")
    ->withDescription("Kiss #target_object# on the lips - expresses romantic or sexual interest")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [], true)
    ->isNSFW()
    ->withEnableCondition(function() {
        return ShouldEnableHarassFunctions($GLOBALS['HERIKA_NAME']);
    })
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Molest Action
registerMinAIAction("ExtCmdMolest", "Molest")
    ->withDescription("Force unwanted sexual contact on #target_object# - a criminal act of assault (use with caution)")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [], true)
    ->isNSFW()
    ->withEnableCondition(function() {
        return ShouldEnableHarassFunctions($GLOBALS['HERIKA_NAME']);
    })
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// actions registered in deviousfollowers_context.php