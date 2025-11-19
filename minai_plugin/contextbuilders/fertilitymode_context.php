<?php
require_once("FillHerUp_context.php");

Function GetFertilityContext($name) {
    $ret = "";
    // Skip if no fertility state or male
    if (GetActorValue($name, "gender") != "female") {
        return $ret;
    }

//error_log("Fertility $name - exec trace");    

    $state = strtolower(GetActorValue($name, "fertility_state"));
    if (empty($state)) {
        return $ret;
    }
    
//error_log("Fertility $name $state - exec trace");    

    //$isNarrator = ($GLOBALS["HERIKA_NAME"] == "The Narrator");
    $isNarrator = (strtolower($name) == "the narrator");
    
    //$isSelf = false; //GetTargetActor() == $name;
    
    // Pregnancy states are visible to everyone
    if ($state == "third_trimester") {
        $ret .= "{$name} is in the third trimester of pregnancy and is very visibly pregnant.\n";
    }
    elseif ($state == "second_trimester") {
        $ret .= "{$name} is in the second trimester of pregnancy and is showing a noticeable baby bump.\n";
    }
    elseif ($state == "first_trimester") {
        $ret .= "{$name} is in the first trimester of pregnancy, though it's not very noticeable yet.\n";
    }
    // Other states only visible to Narrator or self <fertility_status>
    //elseif ($isNarrator || $isSelf) {
    //elseif (!$isNarrator) {
        if ($state == "ovulating") {
            $ret .= "<fertility_status>{$name} is currently ovulating and fertile, there is a high chance of getting pregnant.</fertility_status>\n";
            //error_log("Fertility $name $state - exec trace");
        }
        elseif ($state == "pms") {
            $ret .= "<fertility_status>{$name} is experiencing PMS symptoms.</fertility_status>\n";
        }
        elseif ($state == "menstruating") {
            $ret .= "<fertility_status>{$name} is currently menstruating.</fertility_status>\n";
            //error_log("Fertility $name $state - exec trace");
        }
        elseif ($state == "normal") {
            $ret .= "<fertility_status>{$name} is not near ovulation, unlikely to get pregnant.</fertility_status>\n";
        }
    //}

    // Add Fill Her Up context
    $ret .= GetFillHerUpContext($name);

    if ($ret != "") {
        $ret .= "\n";
    }
    return $ret;
}
