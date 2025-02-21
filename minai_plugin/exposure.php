<?php
require_once("util.php");


class tng {
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
        } else $str = $someArray[0];
        if($hasHave) {
            if($count>1) {
                $str .= " each have ";
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
function GetExposureContext($localActors) {
    $utilities = new Utilities();
    if (!$utilities->IsModEnabled("TNG")) {
        return "";
    }

    // lists of people
    $isnaked = [];
    $tngsize0 = [];
    $tngsize1 = [];
    $tngsize2 = [];
    $tngsize3 = [];
    $tngsize4 = [];


    $actorList = explode("|",$localActors);
    $actorList[] = $GLOBALS["PLAYER_NAME"]; 
    foreach($actorList as $name) {
        $naked = $utilities->GetActorValue($name, "isexposed");
        $tngsize = $utilities->GetActorValue($name, "tngsize");
        if($tngsize == 0) $tngsize0[] = $name;
        if($tngsize == 1) $tngsize1[] = $name;
        if($tngsize == 2) $tngsize2[] = $name;
        if($tngsize == 3) $tngsize3[] = $name;
        if($tngsize == 4) $tngsize4[] = $name;
        if($naked == true) $isnaked[] = $name;
    }

    // build lists
    $verbiage = "";
    if(!empty($naked)) {
        $verbiage .= tng::rollUpAList($isnaked) . "naked and exposed.\n";
    }
    if(!empty($tngsize1)) {
        $verbiage .= tng::rollUpAList($tngsize0, true) . "an embarrassingly tiny prick.\n";
    }
    if(!empty($tngsize2)) {
        $verbiage .= tng::rollUpAList($tngsize1, true) . "a very small cock.\n";
    }
    if(!empty($tngsize3)) {
        $verbiage .= tng::rollUpAList($tngsize2, true) . "an average size cock.\n";
    }
    if(!empty($tngsize4)) {
        $verbiage .= tng::rollUpAList($tngsize3, true) . "a large, impressive cock.\n";
    }
    if(!empty($tngsize5)) {
        $verbiage .= tng::rollUpAList($tngsize4, true) . "one of the biggest cocks you've ever seen.\n";
    }

    if(!$verbiage) {
        return "";
    }
    return "\n". $verbiage;
}


