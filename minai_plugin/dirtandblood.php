<?php
require_once("util.php");
// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetDirtAndBloodContext($name) {
    $utilities = new Utilities();
    if (!$utilities->IsModEnabled("DirtAndBlood")) {
        return "";
    }
    $description = $utilities->GetActorValue($name, "dirtAndBlood");
    // the names are complicated, just let the llm handle upper casing
    if(!$description) {
        return "";
    }
    return " ". $description . " ";
}


?>