<?php

require_once("action_builder.php");

$target = $GLOBALS["target"];



// Function specifically for checking spanking actions
function shouldEnableSpankingAction() {
    return IsModEnabled("DeviousFollowers") && IsModEnabled("STA");
}

// Function specifically for checking general perverted actions
function shouldEnableGeneralPervertedAction() {
    return IsModEnabled("Sexlab") || IsModEnabled("Ostim");
}

// Register SpankAss action
registerMinAIAction("ExtCmdSpankAss", "SpankAss")
    ->withDescription("Strike #target_possessive# buttocks firmly - can be playful, disciplinary, or erotic")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [$target], true)
    ->isNSFW()
    ->withEnableCondition('shouldEnableSpankingAction')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register SpankTits action (female-only)
registerMinAIAction("ExtCmdSpankTits", "SpankTits")
    ->withDescription("Strike #target_possessive# breasts firmly - an intense erotic act that mixes pain and pleasure")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [$target], true)
    ->isNSFW()
    ->withEnableCondition(function() {
        // Only allow this action for female targets
        return shouldEnableSpankingAction() && IsFemale(GetTargetActor());
    })
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register Grope action
registerMinAIAction("ExtCmdGrope", "Grope")
    ->withDescription("Touch and fondle #target_possessive# body in a sexual manner - shows desire and dominance")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [$target], true)
    ->isNSFW()
    ->withEnableCondition('shouldEnableGeneralPervertedAction')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register PinchNipples action
registerMinAIAction("ExtCmdPinchNipples", "PinchNipples")
    ->withDescription("Firmly pinch and manipulate #target_possessive# nipples - stimulates sensitive nerve endings")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [$target], true)
    ->isNSFW()
    ->withEnableCondition('shouldEnableGeneralPervertedAction')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();


