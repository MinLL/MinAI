<?php
require_once("util.php");

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetFertilityModContext($name, $viewingActor) {
    $utilities = new Utilities();
    if (!IsModEnabled("FertilityModV3")) {
        return "";
    }
    $description = $utilities->GetActorValue($name, "fertilityModV3Status");
    $whoAllKnows = $utilities->GetActorValue($name, "fertilityModV3ContextAwareness");
    $lowerCaseName = strtolower($name);
    if(!$description) {
        return "";
    }
    $result = str_replace($lowerCaseName, $name, $description);
    if($whoAllKnows=="everybody"){
        return "\n". $result . "\n";
    }
    if(strtolower($whoAllKnows) === strtolower($name)) {
        return "\n". $result . "\n";
    }

}


?>