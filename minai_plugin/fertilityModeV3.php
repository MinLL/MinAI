<?php
require_once("util.php");

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetFertilityModContext($name) {
    if (!IsModEnabled("FertilityModV3")) {
        return "";
    }
    $description = GetActorValue($name, "fertilityModV3Status");
    $whoAllKnows = GetActorValue($name, "fertilityModV3ContextAwareness")
    $lowerCaseName = strtolower($name);
    $result = str_replace($lowerCaseName, $name, $description);
    if(!$description) {
        return "";
    }

    

    return "\n". $result . "\n";
}


?>