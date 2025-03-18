<?php

require_once("action_builder.php");

// StartLooting Action
registerMinAIAction("ExtCmdStartLooting", "StartLooting")
    ->withDescription("Start looting the area")
    ->withParameter("target", "string", "Target NPC, Actor, or being", [], true)
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// StopLooting Action
registerMinAIAction("ExtCmdStopLooting", "StopLooting")
    ->withDescription("Stop looting the area")
    ->withParameter("target", "string", "Target NPC, Actor, or being", [], true)
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

