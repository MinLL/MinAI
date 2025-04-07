<?php

require_once("action_builder.php");

// Crime-related functions for guards to use
// These functions are only available to NPCs in the Guard Faction
$smallBountyAmount = GetActorValue($GLOBALS["PLAYER_NAME"], "CrimeSmallBountyAmount");
$mediumBountyAmount = GetActorValue($GLOBALS["PLAYER_NAME"], "CrimeMediumBountyAmount");
$largeBountyAmount = GetActorValue($GLOBALS["PLAYER_NAME"], "CrimeLargeBountyAmount");

// Define all crime functions with examples of when to use each
$crimeCommands = [
    "AddBountySmall" => "Add a small bounty of {$smallBountyAmount} gold to {$GLOBALS['PLAYER_NAME']} (for minor infractions like trespassing, petty theft, disrespect to guards, or public disturbance)",
    "AddBountyMedium" => "Add a medium bounty of {$mediumBountyAmount} gold to {$GLOBALS['PLAYER_NAME']} (for moderate crimes like assault, significant theft, property damage, or breaking and entering)",
    "AddBountyLarge" => "Add a large bounty of {$largeBountyAmount} gold to {$GLOBALS['PLAYER_NAME']} (for serious crimes like murder, grievous assault, major theft, or attacking a guard)",
    "Arrest" => "Arrest {$GLOBALS['PLAYER_NAME']} and send them to jail immediately (for uncooperative criminals or when caught in the act)",
    "ClearBounty" => "Clear all bounty for {$GLOBALS['PLAYER_NAME']} in the current hold (Use in response to fines being paid (When you've already collected payment), or when you otherwise want to clear the bounty)"
];
$isGuardTargetingPlayer = isGuardTargetingPlayer();
// Register crime actions using directRegisterAction
foreach ($crimeCommands as $command => $description) {
    directRegisterAction(
        "ExtCmd".$command, 
        $command, 
        $description,
        $isGuardTargetingPlayer
    );
} 