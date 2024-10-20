<?php
require_once("util.php");
$target = $GLOBALS["target"];

if ((IsModEnabled("Sexlab") || IsModEnabled("Ostim")) && ((IsEnabled("PLAYER", "enableAISex") && IsRadiant()) || !IsRadiant())) {
    // Always enabled
    RegisterAction("ExtCmdMasturbate");
    RegisterAction("ExtCmdStartVaginal");
    RegisterAction("ExtCmdStartAnal");
    RegisterAction("ExtCmdStartBlowjob");
    RegisterAction("ExtCmdStartHandjob");
    RegisterAction("ExtCmdStartOrgy");
    RegisterAction("ExtCmdPutOnClothes");
    RegisterAction("ExtCmdRemoveClothes");

    if (!IsFollower($GLOBALS['HERIKA_NAME']) && $GLOBALS["target"] == $GLOBALS["PLAYER_NAME"]) {
        if (IsFollowing($GLOBALS['HERIKA_NAME'])) {
            RegisterAction("ExtCmdEndFollow");
        } else {
            if (!IsInScene($GLOBALS['HERIKA_NAME'])) {
                error_log($GLOBALS['HERIKA_NAME']." is not in a scene");
                RegisterAction("ExtCmdFollow");
            } else {
                error_log($GLOBALS['HERIKA_NAME']." is in a scene");
            }
        }
    }

    // Always enabled for female actors
    if (IsFemale(GetTargetActor())) {
        RegisterAction("ExtCmdStartFingering");
        RegisterAction("ExtCmdStartCunnilingus");
    }
    // Only enabled if already in a sex scene
    if (IsSexActive()) {
        RegisterAction("ExtCmdStartCuddleSex");
        RegisterAction("ExtCmdStartKissingSex");
        RegisterAction("ExtCmdStartFootjob");
        RegisterAction("ExtCmdStartBoobjob");
        RegisterAction("ExtCmdStartCunnilingus");
        RegisterAction("ExtCmdStartFacial");
        RegisterAction("ExtCmdStartCumonchest");
        RegisterAction("ExtCmdStartRubbingclitoris");
        RegisterAction("ExtCmdStartDeepthroat");
        RegisterAction("ExtCmdStartRimjob");
        RegisterAction("ExtCmdStartFingering");
        RegisterAction("ExtCmdStartMissionarySex");
        RegisterAction("ExtCmdStartCowgirlSex");
        RegisterAction("ExtCmdStartReverseCowgirl");
        RegisterAction("ExtCmdStartDoggystyle");
        RegisterAction("ExtCmdStartFacesitting");
        RegisterAction("ExtCmdStart69Sex");
        RegisterAction("ExtCmdStartGrindingSex");
        RegisterAction("ExtCmdStartThighjob");
        RegisterAction("ExtCmdStartAggressive");
        RegisterAction("ExtCmdEndSex");
        // Speed control for OStim scenes 
        if (IsModEnabled("Ostim")) {
          RegisterAction("ExtCmdSpeedUpSex");
          RegisterAction("ExtCmdSlowDownSex");
        }
    }
}


$GLOBALS["F_NAMES"]["ExtCmdSpeedUpSex"]="SpeedUpSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdSpeedUpSex"]="Increase the speed of sexual activity";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdSpeedUpSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdSpeedUpSex"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["nearby"]
                ]
            ],
            "required" => [],
        ],
    ];
$GLOBALS["FUNCRET"]["ExtCmdSpeedUpSex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdSlowDownSex"]="SlowDownSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdSlowDownSex"]="Reduce the speed of sexual activity";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdSlowDownSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdSlowDownSex"],
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
$GLOBALS["FUNCRET"]["ExtCmdSlowDownSex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdEndSex"]="EndSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdEndSex"]="Immediately disengage from sexual activity";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdEndSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdEndSex"],
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
$GLOBALS["FUNCRET"]["ExtCmdEndSex"]=$GLOBALS["GenericFuncRet"];

/* $GLOBALS["F_NAMES"]["ExtCmdStartSexScene"]="StartSexScene";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartSexScene"]="Immediately engage in sexual activity with the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartSexScene"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartSexScene"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartSexScene"]=$GLOBALS["GenericFuncRet"]; */

$GLOBALS["F_NAMES"]["ExtCmdMasturbate"]="Masturbate";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdMasturbate"]="Immediately begin masturbating";

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdMasturbate"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdMasturbate"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["nearby"]
                ]
            ],
            "required" => [],
        ],
    ];
$GLOBALS["FUNCRET"]["ExtCmdMasturbate"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartOrgy"]="StartOrgy";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartOrgy"]="Immediately engage in an orgy with multiple participants";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartOrgy"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartOrgy"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["nearby"]
                ]
            ],
            "required" => [],
        ],
    ];
$GLOBALS["FUNCRET"]["ExtCmdStartOrgy"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartVaginal"]="StartVaginal";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartVaginal"]="Immediately engage in vaginal sex with the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartVaginal"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartVaginal"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartVaginal"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartAnal"]="StartAnal";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartAnal"]="Immediately engage in anal sex with the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartAnal"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartAnal"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartAnal"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartBlowjob"]="StartBlowjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBlowjob"]="Immediately engage in oral sex with the target by either giving or receiving a blowjob";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartBlowjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBlowjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartBlowjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartHandjob"]="StartHandjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartHandjob"]="Immediately engage in manual stimulation with the target by either giving or receiving a handjob";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartHandjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartHandjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartHandjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFootjob"]="StartFootjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFootjob"]="Immediately engage in a footjob with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFootjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFootjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFootjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartBoobjob"]="StartBoobjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBoobjob"]="Immediately engage in a boobjob with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartBoobjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartBoobjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartBoobjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartCunnilingus"]="StartCunnilingus";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCunnilingus"]="Immediately engage in cunnilingus with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartCunnilingus"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCunnilingus"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartCunnilingus"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFacial"]="StartFacial";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFacial"]="Immediately engage in a facial cumshot with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFacial"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFacial"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFacial"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartCumonchest"]="StartCumonchest";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCumonchest"]="Immediately engage in a chest cumshot with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartCumonchest"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCumonchest"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartCumonchest"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartRubbingclitoris"]="StartRubbingclitoris";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRubbingclitoris"]="Immediately engage in manual stimulation with the target by rubbing the clitoris";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartRubbingclitoris"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRubbingclitoris"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartRubbingclitoris"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartDeepthroat"]="StartDeepthroat";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartDeepthroat"]="Immediately engage in extra deep blowjob or facefucking with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartDeepthroat"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartDeepthroat"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartDeepthroat"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartRimjob"]="StartRimjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRimjob"]="Immediately engage in a rimjob with target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartRimjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartRimjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartRimjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFingering"]="StartFingering";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFingering"]="Immediately engage in manual stimulation with the target by fingering the vagina";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFingering"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFingering"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFingering"]=$GLOBALS["GenericFuncRet"];



$GLOBALS["F_NAMES"]["ExtCmdStartMissionarySex"]="StartMissionarySex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartMissionarySex"]="Immediately engage in missionary position sex.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartMissionarySex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartMissionarySex"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartMissionarySex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartCowgirlSex"]="StartCowgirlSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCowgirlSex"]="Immediately engage in cowgirl position sex.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartCowgirlSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCowgirlSex"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartCowgirlSex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartReverseCowgirl"]="StartReverseCowgirl";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartReverseCowgirl"]="Immediately engage in reverse cowgirl position sex.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartReverseCowgirl"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartReverseCowgirl"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartReverseCowgirl"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartDoggystyle"]="StartDoggystyle";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartDoggystyle"]="Immediately engage in doggystyle position sex.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartDoggystyle"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartDoggystyle"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartDoggystyle"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartFacesitting"]="StartFacesitting";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFacesitting"]="Immediately engage in woman on top position, grinding pussy on face.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartFacesitting"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartFacesitting"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartFacesitting"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStart69Sex"]="Start69Sex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStart69Sex"]="Immediately engage in mutual oral stimulation in the 69 position.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStart69Sex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStart69Sex"],
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
$GLOBALS["FUNCRET"]["ExtCmdStart69Sex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartGrindingSex"]="StartGrindingSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartGrindingSex"]="Immediately engage in grinding body against penis";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartGrindingSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartGrindingSex"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartGrindingSex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartThighjob"]="StartThighjob";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartThighjob"]="Immediately engage in grinding penis between thighs.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartThighjob"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartThighjob"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartThighjob"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartCuddleSex"]="StartCuddleSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCuddleSex"]="Immediately engage in empathetic hugging that leads to sex.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartCuddleSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartCuddleSex"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartCuddleSex"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStartKissingSex"]="StartKissingSex";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStartKissingSex"]="Immediately engage in passionate, sexual kissing.";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStartKissingSex"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStartKissingSex"],
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
$GLOBALS["FUNCRET"]["ExtCmdStartKissingSex"]=$GLOBALS["GenericFuncRet"];



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
                    "enum" => $GLOBALS["nearby"]
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
                    "enum" => $GLOBALS["nearby"]
                ]
            ],
            "required" => [],
        ],
    ];
$GLOBALS["FUNCRET"]["ExtCmdPutOnClothes"]=$GLOBALS["GenericFuncRet"];


$GLOBALS["F_NAMES"]["ExtCmdFollow"]="Follow";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdFollow"]="Begin following the target to another location";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdFollow"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdFollow"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target Actor",
                    "enum" => $GLOBALS["nearby"]
                ]
            ],
            "required" => [],
        ],
    ];
$GLOBALS["FUNCRET"]["ExtCmdFollow"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdStopFollowing"]="StopFollowing";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStopFollowing"]="Stop following the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStopFollowing"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStopFollowing"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target Actor",
                    "enum" => $GLOBALS["nearby"]
                ]
            ],
            "required" => [],
        ],
    ];
$GLOBALS["FUNCRET"]["ExtCmdStopFollowing"]=$GLOBALS["GenericFuncRet"];
?>

