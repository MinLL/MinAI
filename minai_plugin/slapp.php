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
                    "enum" => $GLOBALS["nearby"]

                ]
            ],
            "required" => ["target"],
        ],
    ];





$GLOBALS["FUNCRET"]["ExtCmdHug"]=$GLOBALS["GenericFuncRet"];



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
                    "enum" => $GLOBALS["nearby"]

                ]
            ],
            "required" => ["target"],
        ],
    ];





$GLOBALS["FUNCRET"]["ExtCmdKiss"]=$GLOBALS["GenericFuncRet"];




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
                    "enum" => $GLOBALS["nearby"]

                ]
            ],
            "required" => ["target"],
        ],
    ];

$GLOBALS["FUNCRET"]["ExtCmdMolest"]=$GLOBALS["GenericFuncRet"];

require_once("deviousfollower.php");
if (IsModEnabled("SLAPP") && !IsDeviousFollower($GLOBALS['HERIKA_NAME'])) {
    RegisterAction("ExtCmdHug");
    RegisterAction("ExtCmdKiss");
}
if (IsModEnabled("SLHH") && !IsDeviousFollower($GLOBALS['HERIKA_NAME'])) {
    RegisterAction("ExtCmdMolest");
}



?>

