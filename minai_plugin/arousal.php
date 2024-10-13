<?php

require_once("util.php");
if (IsModEnabled("OSL") || IsModEnabled("Aroused")) {
    RegisterAction("ExtCmdIncreaseArousal");
    RegisterAction("ExtCmdDecreaseArousal");
}

$GLOBALS["F_NAMES"]["ExtCmdIncreaseArousal"]="IncreaseArousal";
$GLOBALS["F_NAMES"]["ExtCmdDecreaseArousal"]="DecreaseArousal";


$GLOBALS["F_TRANSLATIONS"]["ExtCmdIncreaseArousal"]="Use this if you are getting more aroused";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdDecreaseArousal"]="Use this if you are getting less aroused";



$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdIncreaseArousal"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdIncreaseArousal"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "integer",
                    "description" => "How much to increase arousal (0-100 scale)",
                    "enum" => range(1, 20)
                ]
            ],
            "required" => [],
        ],
    ];

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdDecreaseArousal"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdDecreaseArousal"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "integer",
                    "description" => "How much to decrease arousal (0-100 scale)",
                    "enum" => range(1, 20)
                ]
            ],
            "required" => [],
        ],
    ];


?>
