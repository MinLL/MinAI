<?php
require_once("util.php");

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetDirtAndBloodContext($name) {
    error_log("Dirt and Blood entering");
    if (!IsModEnabled("DirtAndBlood")) {
        return "";
    }

    $dirt_and_blood_tag_list = GetActorValue($name, "dirt_and_blood_tag_list");

    $tag_list = explode(",",$dirt_and_blood_tag_list);

    $cleanlinessString = "";
    foreach($tag_list as $tag) {    
        switch($tag) {
            case "Dirt1":  
                $cleanlinessString += "{$name} is barely dirty and fits right in with the people of Skyrim. ";
                break;
            case "Dirt2":
                $cleanlinessString += "{$name} is starting to look dirty and could use a bath. ";
                break;
            case "Dirt3":
                $cleanlinessString += "{$name} is very dirty and in need of a bath. ";
                break;
            case "Dirt4":
                $cleanlinessString += "{$name} is filthy, disgustingly dirty, so gross it is uncomfortable to be close to {$name}. {$name} smells. {$name} is so dirty {$name} may get sick. {$name} appears to have bad hygiene. ";
                break;
            case "Blood1":
                $cleanlinessString += "{$name} is hardly covered in blood, but some shows. ";
                break;
            case "Blood2":
                $cleanlinessString +=  "{$name} has blood smeared upon themselves. ";
                break;
            case "Blood3":
                $cleanlinessString += "{$name} is covered in blood from head to toe. ";
                break;
            case "Blood4":
                $cleanlinessString += "{$name} is soaked in blood, and the ground below {$name} seeps with blood dripping off them. ";
                break;
            case "SoapEffectSpell":
                $cleanlinessString += "{$name} is bathing. {$name} is soaping up and washing themselves. ";
                break;
            case "Clean":
                $cleanlinessString += "{$name} is clean and well groomed. ";
                break;
        }
    }
    return $cleanlinessString;
}


?>