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
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartFootjob";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartBoobjob";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartCunnilingus";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartFacial";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartCumonchest";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartRubbingclitoris";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartDeepthroat";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartRimjob";
        $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStartVaginalfingering";
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
$GLOBALS["FUNCRET"]["ExtCmdStartSexScene"]=$GLOBALS["GenericFuncRet"];


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




$GLOBALS["FUNCRET"]["ExtCmdMasturbate"]=$GLOBALS["GenericFuncRet"];



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
$GLOBALS["FUNCRET"]["ExtCmdStartOrgy"]=$GLOBALS["GenericFuncRet"];

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
$GLOBALS["FUNCRET"]["ExtCmdStartVaginal"]=$GLOBALS["GenericFuncRet"];

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
$GLOBALS["FUNCRET"]["ExtCmdStartAnal"]=$GLOBALS["GenericFuncRet"];

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
$GLOBALS["FUNCRET"]["ExtCmdStartBlowjob"]=$GLOBALS["GenericFuncRet"];

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
$GLOBALS["FUNCRET"]["ExtCmdStartHandjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFootjob"]="StartFootjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFootjob"]="Engage in a footjob with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFootjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFootjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFootjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartBoobjob"]="StartBoobjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBoobjob"]="Engage in a boobjob with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartBoobjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBoobjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartBoobjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartCunnilingus"]="StartCunnilingus";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCunnilingus"]="Engage in cunnilingus with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartCunnilingus"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCunnilingus"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartCunnilingus"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFacial"]="StartFacial";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFacial"]="Engage in a facial cumshot with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFacial"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFacial"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFacial"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartCumonchest"]="StartCumonchest";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCumonchest"]="Engage in a chest cumshot with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartCumonchest"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCumonchest"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartCumonchest"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartRubbingclitoris"]="StartRubbingclitoris";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRubbingclitoris"]="Engage in manual stimulation with the target by rubbing the clitoris";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartRubbingclitoris"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRubbingclitoris"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartRubbingclitoris"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartDeepthroat"]="StartDeepthroat";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartDeepthroat"]="Engage in extra deep blowjob or facefucking with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartDeepthroat"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartDeepthroat"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartDeepthroat"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartRimjob"]="StartRimjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRimjob"]="Engage in a rimjob with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartRimjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRimjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartRimjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFingering"]="StartFingering";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFingering"]="Engage in manual stimulation with the target by fingering the vagina";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFingering"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFingering"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFingering"]=$GLOBALS["GenericFuncRet"];


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
$GLOBALS["FUNCRET"]["ExtCmdRemoveClothes"]=$GLOBALS["GenericFuncRet"];


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
$GLOBALS["FUNCRET"]["ExtCmdPutOnClothes"]=$GLOBALS["GenericFuncRet"];

?>

