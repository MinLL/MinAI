<?php
require_once("util.php");

function GetEnvironmentalContext($targetActor) {
    $utilities = new Utilities();

    // it doesn't matter if Frostfall exists, this PHP loads sentances verbatim from the db

    // then about player 
    // then list local actors for notable observations
    $player_name = $GLOBALS["PLAYER_NAME"];
    $players_appearance = "" 
        . $utilities->GetActorValue($player_name, "EnviromentalAwarenessMoreStableData")
        . $utilities->GetActorValue($player_name, "EnvironmentalAwarenessDynamicData");
    $private_knowledge = "" 
        . $utilities->GetActorValue($targetActor, "EnvironmentalAwarenessPrivateKnowledge")
        . $utilities->GetActorValue($targetActor, "EnvironmentalAwarenessDynamicPrivateData");
    $others_appearances = [];

    $actorList = [];
    if(!in_array($targetActor, $actorList)) $actorList[] = $targetActor;

    $dedupe = [];

    foreach($actorList as $actor) {
        $actor = str_replace("(","",$actor);
        // we already did the player -- this can come up if the player forgets to set their name right in CHiM
        $bValidToInclude = $actor!="" && $player_name != $actor && !in_array($actor, $dedupe);
        if($bValidToInclude) {
            $new_text = ""
                . $utilities->GetActorValue($actor, "EnviromentalAwarenessMoreStableData") 
                . $utilities->GetActorValue($actor, "EnvironmentalAwarenessDynamicData");
            $new_text = str_replace(strtolower($actor), $actor, $new_text);        
            if($new_text!=""&&!str_ends_with($new_text, ".")) $new_text .= ".";
            $others_appearances[] = $new_text;
            // if the player messes up their name they can end up with relisted npcs
            $dedupe[] = $actor;
        }
    }
    $others = implode("\n", $others_appearances);
    $text = $players_appearance . "\n" . $others . "\n" . $private_knowledge . "\n";
    $text = str_ireplace("a Old People Race", "an elderly person", $text);
    $text = str_ireplace("IMPERIAL", "imperial", $text);
    $text = str_replace(" , ", " ", $text);
    $text = str_replace(":, ", ":", $text);
    $text = str_ireplace("{$GLOBALS["PLAYER_NAME"]} is a player spellsword class.", "", $text); // Player is always a spellsword in-game, don't show it.
    $text = str_replace(strtolower($player_name), $player_name, $text);

    return $text;
}

        


