<?php

if (IsModEnabled("OSL") || IsModEnabled("Aroused")) {
    $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdIncreaseArousal";
    $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdDecreaseArousal";
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
                "change" => [
                    "type" => "integer",
                    "description" => "How much to increase arousal (0-100 scale)",
                    "enum" => range(1, 100)
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
                "change" => [
                    "type" => "integer",
                    "description" => "How much to decrease arousal (0-100 scale)",
                    "enum" => range(1, 100)
                ]
            ],
            "required" => [],
        ],
    ];


?>
