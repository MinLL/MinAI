<?php
require_once("util.php");


function GetFertilityModeV3Context($name, $viewingActor) {
    if (!IsModEnabled("FertilityModV3")) {
        return "";
    }
    // narrator has no physical presence
    if($name == $GLOBALS["HERIKA_NAME"]) return "";
    // player character gets their context from playing the game
    if($viewingActor == $GLOBALS["PLAYER_NAME"]) return "";
    
    $utilities = new Utilities();
    if($viewingActor == $GLOBALS["HERIKA_NAME"]) {
        $narrator_description = $utilities->GetActorValue($name, "fertilityModV3NarratorStatus");
        return $narrator_description . "\n";
    }
    if($name == $viewingActor) {
        $private_description = $utilities->GetActorValue($name, "fertilityModV3PrivateStatus");
        return $private_description . "\n";
    }

    $public_description = $utilities->GetActorValue($name, "fertilityModV3PublicStatus");
    return $public_description . "\n";
}


?>