<?php
Function GetSurvivalContext($name) {
    $ret = "";
    // If it's a follower, use the player's needs instead
    // if/when we add support for ineed, we'll need to handle that differently
    if (IsFollower($name)) {
        $name = $GLOBALS["PLAYER_NAME"];
    }
    
    if ((!IsModEnabled("Sunhelm") && !IsModEnabled("SurvivalMode"))) {
        return $ret;
    }
    
    $hunger = floatval(GetActorValue($name, "hunger"));
    $thirst = floatval(GetActorValue($name, "thirst")); 
    $fatigue = floatval(GetActorValue($name, "fatigue"));
    $cold = floatval(GetActorValue($name, "cold"));

    $ret .= "{$name}'s hunger level is at {$hunger}%, where 0 is not hungry at all, and 100 is starving. ";

    if (IsModEnabled("Sunhelm")) {
        $ret .= "{$name}'s thirst level is at {$thirst}%, where 0 is not thirsty at all, and 100 is dying of thirst. ";
    }

    $ret .= "{$name}'s fatigue level is at {$fatigue}%, where 0 is not tired at all, and 100 is exhausted. ";

    if (IsModEnabled("SurvivalMode")) {
        $ret .= "{$name}'s cold level is at {$cold}%, where 0 is not cold at all, and 100 is freezing to death. ";
    }

    if ($ret != "")
        $ret .= "\n";
    return $ret;
}