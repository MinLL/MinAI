<?php

$GLOBALS["F_NAMES"]["ExtCmdStartSexScene"]="StartSexScene";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartSexScene"]="Start having sex with the target";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartSexScene"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartSexScene"],
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


$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartSexScene";


$GLOBALS["FUNCRET"]["ExtCmdStartSexScene"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request here.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdStartSexScene"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to have sex at this time. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};


$GLOBALS["F_NAMES"]["ExtCmdMasturbate"]="Masturbate";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdMasturbate"]="Start masturbating";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdMasturbate"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdMasturbate"],
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


$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdMasturbate";


$GLOBALS["FUNCRET"]["ExtCmdMasturbate"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request here.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdMasturbate"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to have sex at this time. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



$GLOBALS["F_NAMES"]["ExtCmdStartOrgy"]="StartOrgy";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartOrgy"]="Start having an orgy with all nearby participants";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartOrgy"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartOrgy"],
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


$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartOrgy";


$GLOBALS["FUNCRET"]["ExtCmdStartOrgy"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request here.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdStartOrgy"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to have sex at this time. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



?>

