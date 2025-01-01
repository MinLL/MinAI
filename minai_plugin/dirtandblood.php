<?php
require_once("util.php");

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetDirtAndBloodContext() {
    error_log("Dirt and Blood entering");
    if (!IsModEnabled("DirtAndBlood")) {
        return "";
    }

    $playerName = $GLOBALS["PLAYER_NAME"];


    $ret = "";
    $personality = "";

    $dirt_and_blood_tag_list = GetActorValue($playerName, "dirt_and_blood_tag_list");

    $tag_list = explode(",",$dirt_and_blood_tag_list);

    $cleanlinessString = "";
    foreach($tag_list as $tag) {    
        switch($tag) {
            case "Dirt1":  
                $cleanlinessString += "{$playerName} is barely dirty and fits right in with the people of Skyrim. ";
                break;
            case "Dirt2":
                $cleanlinessString += "{$playerName} is starting to look dirty and could use a bath. ";
                break;
            case "Dirt3":
                $cleanlinessString += "{$playerName} is very dirty and in need of a bath. ";
                break;
            case "Dirt4":
                $cleanlinessString += "{$playerName} is filthy, disgustingly dirty, so gross it is uncomfortable to be close to {$playerName}. {$playerName} is so dirty {$playerName} may get sick. {$playerName} appears to have bad hygiene. ";
                break;
            case "Blood1":
                $cleanlinessString += "{$playerName} is hardly covered in blood, but some shows. ";
                break;
            case "Blood2":
                $cleanlinessString +=  "{$playerName} has blood smeared upon themselves. ";
                break;
            case "Blood3":
                $cleanlinessString += "{$playerName} is covered in blood from head to toe. ";
                break;
            case "Blood4":
                $cleanlinessString += "{$playerName} is soaked in blood, and the ground below {$playerName} seeps with blood dripping off them. ";
                break;
            case "SoapEffectSpell":
                $cleanlinessString += "{$playerName} is bathing. {$playerName} is soaping up and washing themselves. ";
                break;
            case "Clean":
                $cleanlinessString += "{$playerName} is clean and well groomed. ";
                break;
        }
    }
    return $cleanlinessString;
}


?>