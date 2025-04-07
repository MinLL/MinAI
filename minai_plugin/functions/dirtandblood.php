<?php

// Check if actor can start bathing
function canStartBathing() {
    return IsModEnabled("DirtAndBlood") && 
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
        "Unequip your gear and strip naked to take a bath, cleansing yourself of dirt and blood (You do not need to RemoveClothes first)",
        true
    );
} 