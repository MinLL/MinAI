<?php
require_once("FillHerUp_context.php");

Function GetFertilityContext($name) {
    $ret = "";
    $state = GetActorValue($name, "fertility_state");
    
    // Skip if no fertility state or male
    if (empty($state) || GetActorValue($name, "gender") != "female") {
        return $ret;
    }

    $isNarrator = ($GLOBALS["HERIKA_NAME"] == "The Narrator");
    $isSelf = false; //GetTargetActor() == $name;
    
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
    // Other states only visible to Narrator or self
    elseif ($isNarrator || $isSelf) {
        if ($state == "ovulating") {
            $ret .= "{$name} is currently ovulating and fertile.\n";
        }
        elseif ($state == "pms") {
            $ret .= "{$name} is experiencing PMS symptoms.\n";
        }
        elseif ($state == "menstruating") {
            $ret .= "{$name} is currently menstruating.\n";
        }
    }

    // Add Fill Her Up context
    $ret .= GetFillHerUpContext($name);

    if ($ret != "") {
        $ret .= "\n";
    }
    return $ret;
}
