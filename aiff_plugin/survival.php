<?php

$GLOBALS["F_NAMES"]["ExtCmdRentRoom"]="RentRoom";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdRentRoom"]="Allow the target to rent a room";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdRentRoom"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdRentRoom"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["FUNCTION_PARM_INSPECT"]

                ]
            ],
            "required" => [],
        ],
    ];



$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdRentRoom";


$GLOBALS["FUNCRET"]["ExtCmdRentRoom"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdRentRoom"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to rent out a room. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



$GLOBALS["F_NAMES"]["ExtCmdServeFood"]="ServeFood";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdServeFood"]="Serve food to the target";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdServeFood"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdServeFood"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["FUNCTION_PARM_INSPECT"]

                ]
            ],
            "required" => [],
        ],
    ];



$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdServeFood";


$GLOBALS["FUNCRET"]["ExtCmdServeFood"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdServeFood"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to serve food. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



?>