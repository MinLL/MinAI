<?php
require_once("config.php");
require_once("util.php");

$GLOBALS["F_NAMES"]["ExtCmdStartLooting"]="StartLooting";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartLooting"]="Start looting the area";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartLooting"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartLooting"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartLooting"]=$GLOBALS["GenericFuncRet"];


$GLOBALS["F_NAMES"]["ExtCmdStopLooting"]="StopLooting";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStopLooting"]="Stop looting the area";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStopLooting"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStopLooting"],
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
$GLOBALS["FUNCRET"]["ExtCmdStopLooting"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartLooting";
$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStopLooting";

?>
