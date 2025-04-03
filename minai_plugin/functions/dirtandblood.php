<?php

// Register StartBathing action
registerMinAIAction("ExtCmdStartBathing", "StartBathing")
    ->withDescription("Unequip your gear and strip naked to take a bath, cleansing yourself of dirt and blood (You do not need to RemoveClothes first)")
    ->withParameter("target", "string", "Target Actor", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
    ->withEnableCondition('canStartBathing')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Check if actor can start bathing
function canStartBathing() {
    return IsModEnabled("DirtAndBlood") && 
           !IsInFaction($GLOBALS["HERIKA_NAME"], "NoActionsFaction");
}

// Add the function to the enabled functions list
RegisterAction("StartBathing"); 