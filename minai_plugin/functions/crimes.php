<?php

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
    "ClearBounty" => "Clear all bounty for {$GLOBALS['PLAYER_NAME']} in the current hold (after paying fines or through official pardons)"
];


// Only register the crime commands if the actor is a guard
if ((IsInFaction($GLOBALS["HERIKA_NAME"], "GuardFaction") || IsInFaction($GLOBALS["HERIKA_NAME"], "Guard Faction")) && GetTargetActor() == $GLOBALS["PLAYER_NAME"]) {
    foreach ($crimeCommands as $command => $description) {
        $GLOBALS["F_NAMES"]["ExtCmd".$command] = $command;
        $GLOBALS["F_TRANSLATIONS"]["ExtCmd".$command] = $description;
        $GLOBALS["FUNCTIONS"][] = [
            "name" => $GLOBALS["F_NAMES"]["ExtCmd".$command],
            "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmd".$command],
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "target" => [
                        "type" => "string",
                        "description" => "Target NPC, Actor, or being (usually the player)",
                        "enum" => []
                    ]
                ],
                "required" => [],
            ],
        ];
        $GLOBALS["FUNCRET"]["ExtCmd".$command] = $GLOBALS["GenericFuncRet"];
        RegisterAction("ExtCmd" . $command);
    }
} 