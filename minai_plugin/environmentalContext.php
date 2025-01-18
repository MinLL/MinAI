<?php
require_once("util.php");

function GetEnvironmentalContext($localActors, $targetActor) {
    $utilities = new Utilities();

    // it doesn't matter if Frostfall exists, this PHP loads sentances verbatim from the db

    // short snippet about time of day and weather
    // then about player 
    // then list local actors for notable observations
    $player_name = $GLOBALS["PLAYER_NAME"];
    $basic_weather = $utilities->GetActorValue($player_name, "EnviromentalAwarenessPlayerEnviroment");
    $players_appearance = $utilities->GetActorValue($player_name, "EnviromentalAwarenessMoreStableData");
    $players_appearance .= $utilities->GetActorValue($player_name, "EnvironmentalAwarenessDynamicData");

    $private_knowledge = $utilities->GetActorValue($targetActor, "EnvironmentalAwarenessPrivateKnowledge");

    $others_appearances = [];

    $actorList = explode("|", $localActors);
    foreach($actorList as $actor) {
        $new_text = $utilities->GetActorValue($actor, "EnviromentalAwarenessMoreStableData") . $utilities->GetActorValue($actor, "EnvironmentalAwarenessDynamicData");
        $new_text = str_replace(strtolower($actor), $actor, $new_text);        
        if($new_text!=""&&!str_ends_with($new_text, ".")) $new_text .= ".";
        $others_appearances[] = $new_text;
    }
    $others = implode("\n", $others_appearances);
    $text = $players_appearance . "\n" . $others . "\n" . $basic_weather . "\n" . $private_knowledge . "\n";
    $text = str_ireplace("a Old People Race", "an elderly person", $text);
    $text = str_ireplace("IMPERIAL", "imperial", $text);
    $text = str_replace(" , ", " ", $text);
    $text = str_replace(":, ", ":", $text);
    $text = str_replace("player spellsword class", "spellsword", $text);
    $text = str_replace(strtolower($player_name), $player_name, $text);

    return $text;
}

        


