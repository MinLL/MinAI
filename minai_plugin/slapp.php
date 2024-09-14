<?php


$GLOBALS["F_NAMES"]["ExtCmdHug"]="Hug";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdHug"]="Hug the target";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdHug"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdHug"],
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



$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdHug";


$GLOBALS["FUNCRET"]["ExtCmdHug"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdHug"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to rent out a room. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



$GLOBALS["F_NAMES"]["ExtCmdKiss"]="Kiss";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdKiss"]="Kiss the target";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdKiss"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdKiss"],
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



$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdKiss";


$GLOBALS["FUNCRET"]["ExtCmdKiss"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdKiss"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to serve food. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};




$GLOBALS["F_NAMES"]["ExtCmdMolest"]="Molest";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdMolest"]="Sexually assault / molest the target";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdMolest"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdMolest"],
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



$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdMolest";


$GLOBALS["FUNCRET"]["ExtCmdMolest"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdMolest"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to serve food. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



?>

