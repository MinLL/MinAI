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





$GLOBALS["FUNCRET"]["ExtCmdHug"]=$GenericFuncRet;



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





$GLOBALS["FUNCRET"]["ExtCmdKiss"]=$GenericFuncRet;




$GLOBALS["F_NAMES"]["ExtCmdMolest"]="Molest";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdMolest"]="Force yourself on the target sexually";

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

$GLOBALS["FUNCRET"]["ExtCmdMolest"]=$GenericFuncRet;

if (IsModEnabled("SLAPP")) {
    $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdHug";
    $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdKiss";
}
if (IsModEnabled("SLHH")) {
    $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdMolest";
}



?>

