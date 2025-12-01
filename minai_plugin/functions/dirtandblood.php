<?php

// Check if actor can start bathing
function canStartBathing() {
    return IsModEnabled("DirtAndBlood") && 
        !IsSexActive() && 
        !IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat") && 
        !IsInFaction($GLOBALS["HERIKA_NAME"], "NoActionsFaction");
}

// Cache condition result
$canBathe = canStartBathing();

// Only register if condition is met
if ($canBathe) {
    // Register StartBathing action using direct registration
    directRegisterAction(
        "ExtCmdStartBathing", 
        "StartBathing", 
        "Take a bath, cleanse yourself of dirt and blood, clean off the grime of battle",
        true
    );
} 