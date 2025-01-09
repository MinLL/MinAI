<?php
require_once("util.php");

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetDirtAndBloodContext($name) {
    if (!IsModEnabled("DirtAndBlood")) {
        return "";
    }
    $description = GetActorValue($name, "dirtAndBlood");
    $lowerCaseName = strtolower($name);
    $result = str_replace($lowerCaseName, $name, $description);
    if(!$description) {
        return "";
    }
    return " ". $description . " ";
}


?>