<?php
require_once("util.php");


class dnb {
    // includes is a common name so put it in a class to avoid name collisions
    static function includes($str, $longerStr) {
        if(strpos($longerStr, strtolower($str)) !== false) return true;
    }
    static function rollUpAList($someArray, $hasHave = false) {
        $count = count($someArray);
        if($count>1) {
            // prepend last element with "and" for "and so-and-so" in list
            $someArray[count($someArray) - 1] = "and " .  $someArray[count($someArray) - 1];
        }
        $str = "";
        if(count($someArray)>1) {
            $str = implode(", ", $someArray); 
            $str = substr($str, 0, -2);
        } else $str = $someArray[0];
        if($hasHave) {
            if($count>1) {
                $str .= " have ";
            } else {
                $str .= " has ";
            }
        } else {
            if($count>1) {
                $str .= " are ";
            } else {
                $str .= " is ";
            }
        }
        return $str;
    }
}

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetDirtAndBloodContext($localActors) {
    $utilities = new Utilities();
    if (!$utilities->IsModEnabled("DirtAndBlood")) {
        return "";
    }

    // lists of people
    $clean = [];
    $dirt1 = [];
    $dirt2 = [];
    $dirt3 = [];
    $dirt4 = [];
    $blood1 = [];
    $blood2 = [];
    $blood3 = [];
    $blood4 = [];
    $bathing = [];

    // more soaps add on
    $lavender = [];
    $blue = [];
    $dragonsTongue = [];
    $purple = [];
    $red = [];
    $superior = [];
    



    $actorList = explode("|",$localActors);
    $actorList[] = $GLOBALS["PLAYER_NAME"]; 
    foreach($actorList as $name) {
        $listOfTags = $utilities->GetActorValue($name, "dirtAndBlood");
        if(dnb::includes("Clean", $listOfTags)) $clean[] = $name;
        if(dnb::includes("Dirt1", $listOfTags)) $dirt1[] = $name;
        if(dnb::includes("Dirt2", $listOfTags)) $dirt2[] = $name;
        if(dnb::includes("Dirt3", $listOfTags)) $dirt3[] = $name;
        if(dnb::includes("Dirt4", $listOfTags)) $dirt4[] = $name;
        if(dnb::includes("Bloody1", $listOfTags)) $bloody1[] = $name;
        if(dnb::includes("Bloody2", $listOfTags)) $bloody2[] = $name;
        if(dnb::includes("Bloody3", $listOfTags)) $bloody3[] = $name;
        if(dnb::includes("Bloody4", $listOfTags)) $bloody4[] = $name;
        if(dnb::includes("Bathing", $listOfTags)) $bathing[] = $name;
        if(dnb::includes("Lavender", $listOfTags)) $lavender[] = $name;
        if(dnb::includes("Blue", $listOfTags)) $blue[] = $name;
        if(dnb::includes("DragonsTongue", $listOfTags)) $dragonsTongue[] = $name;
        if(dnb::includes("Red", $listOfTags)) $red[] = $name;
        if(dnb::includes("Purple", $listOfTags)) $purple[] = $name;
        if(dnb::includes("Superior", $listOfTags)) $superior[] = $name;
    }

    // build lists
    $verbiage = "";
    if(!empty($clean)) {
        $verbiage .= dnb::rollUpAList($clean) . "immaculately clean and well groomed.\n";
    }
    if(!empty($dirt1)) {
        $verbiage .= dnb::rollUpAList($dirt1) . "pretty clean by the standards of Skyrim.\n";
    }
    if(!empty($dirt2)) {
        $verbiage .= dnb::rollUpAList($dirt2) . "dirty from the normal day.\n";
    }
    if(!empty($dirt3)) {
        $verbiage .= dnb::rollUpAList($dirt3) . "really dirty and could use a bath.\n";
    }
    if(!empty($dirt4)) {
        $verbiage .= dnb::rollUpAList($dirt4) . "disgustingly filthy. They smell awful.\n";
    }
    if(!empty($blood1)) {
        $verbiage .= dnb::rollUpAList($blood1) . "splattered with light blotches of blood.\n";
    }
    if(!empty($blood2)) {
        $verbiage .= dnb::rollUpAList($blood2) . "splattered with blotches of blood.\n";
    }
    if(!empty($blood3)) {
        $verbiage .= dnb::rollUpAList($blood3) . "covered in blood from battle.\n";
    }
    if(!empty($blood4)) {
        $verbiage .= dnb::rollUpAList($blood4) . "seeping with blood from battle, the blood oozes and drips from their armor.\n";
    }
    if(!empty($bathing)) {
        $verbiage .= dnb::rollUpAList($bathing) . "bathing.\n";
    }
    if(!empty($lavender)) {
        $verbiage .= dnb::rollUpAList($lavender, true) . "a pleasingly aroma of lavender.\n";
    }
    if(!empty($blue)) {
        $verbiage .= dnb::rollUpAList($blue, true) . "a pleasingly aroma of blue mountain flowers.\n";
    }
    if(!empty($red)) {
        $verbiage .= dnb::rollUpAList($red, true) . "a faint but attractive aroma of red mountain flowers.\n";
    }
    if(!empty($dragonsTongue)) {
        $verbiage .= dnb::rollUpAList($dragonsTongue, true) . "a pleasant aroma of dragons tongue flowers.\n";
    }
    if(!empty($blue)) {
        $verbiage .= dnb::rollUpAList($blue, true) . "a pleasing aroma of blue mountain flowers.\n";
    }
    if(!empty($superior)) {
        $verbiage .= dnb::rollUpAList($superior, true) . "an intoxicating aroma of many mountain flowers.\n";
    }

    if(!$verbiage) {
        return "";
    }
    return "\n". $verbiage;
}


?>