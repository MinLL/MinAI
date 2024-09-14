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


$GLOBALS["FUNCRET"]["ExtCmdStartSexScene"]=$GenericFuncRet;


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


$GLOBALS["FUNCRET"]["ExtCmdMasturbate"]=$GenericFuncRet;



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


$GLOBALS["FUNCRET"]["ExtCmdStartOrgy"]=$GenericFuncRet;


?>

