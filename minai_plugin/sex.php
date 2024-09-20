<?php
if (IsModEnabled("Sexlab") || IsModEnabled("Ostim")) {
    $sexSceneActive=$GLOBALS["db"]->fetchAll("select 1 from conf_opts where id='sexscene' and value='on'");
    // if ($sexSceneActive) {
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartOrgy";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdMasturbate";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartVaginal";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartAnal";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartBlowjob";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartHandjob";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartAggressive";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdPutOnClothes";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdRemoveClothes";
        /*}
    else {
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartSexScene";
        }*/
}


$GLOBALS["F_NAMES"]["ExtCmdStartSexScene"]="StartSexScene";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartSexScene"]="Engage in sexual activity with the target";
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




$GLOBALS["FUNCRET"]["ExtCmdMasturbate"]=$GenericFuncRet;



$GLOBALS["F_NAMES"]["ExtCmdStartOrgy"]="StartOrgy";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartOrgy"]="Start an orgy with all nearby participants";
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
$GLOBALS["FUNCRET"]["ExtCmdStartOrgy"]=$GenericFuncRet;

$GLOBALS["F_NAMES"]["ExtCmdStartVaginal"]="StartVaginal";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartVaginal"]="Engage in vaginal sex with the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartVaginal"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartVaginal"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartVaginal"]=$GenericFuncRet;

$GLOBALS["F_NAMES"]["ExtCmdStartAnal"]="StartAnal";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartAnal"]="Engage in anal sex with the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartAnal"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartAnal"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartAnal"]=$GenericFuncRet;

$GLOBALS["F_NAMES"]["ExtCmdStartBlowjob"]="StartBlowjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBlowjob"]="Engage in oral sex with the target by either giving or receiving a blowjob";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartBlowjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBlowjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartBlowjob"]=$GenericFuncRet;

$GLOBALS["F_NAMES"]["ExtCmdStartHandjob"]="StartHandjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartHandjob"]="Engage in manual stimulation with the target by either giving or receiving a handjob";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartHandjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartHandjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartHandjob"]=$GenericFuncRet;

$GLOBALS["F_NAMES"]["ExtCmdRemoveClothes"]="RemoveClothes";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdRemoveClothes"]="Remove all of your clothes and armor";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdRemoveClothes"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdRemoveClothes"],
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
$GLOBALS["FUNCRET"]["ExtCmdRemoveClothes"]=$GenericFuncRet;


$GLOBALS["F_NAMES"]["ExtCmdPutOnClothes"]="PutOnClothes";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdPutOnClothes"]="Put on your clothes and armor";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdPutOnClothes"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdPutOnClothes"],
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
$GLOBALS["FUNCRET"]["ExtCmdPutOnClothes"]=$GenericFuncRet;

?>

