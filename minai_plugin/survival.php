<?php

$GLOBALS["F_NAMES"]["ExtCmdRentRoom"]="RentRoom";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdRentRoom"]="Allow the target to rent a room";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdRentRoom"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdRentRoom"],
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
$GLOBALS["FUNCRET"]["ExtCmdRentRoom"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdRentRoom"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to rent out a room. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};



$GLOBALS["F_NAMES"]["ExtCmdServeFood"]="ServeFood";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdServeFood"]="Serve food to the target";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdServeFood"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdServeFood"],
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
$GLOBALS["FUNCRET"]["ExtCmdServeFood"]=$GLOBALS["GenericFuncRet"];

$GLOBALS["F_NAMES"]["ExtCmdTrade"]="BeginTrading";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdTrade"]="Buy from or sell items to {$GLOBALS["PLAYER_NAME"]}";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdTrade"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdTrade"],
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


$destinations = array();
$destinations[] = "Whiterun";
$destinations[] = "Solitude";
$destinations[] = "Markarth";
$destinations[] = "Riften";
$destinations[] = "Windhelm";
$destinations[] = "Morthal";
$destinations[] = "Dawnstar";
$destinations[] = "Falkreath";
$destinations[] = "Winterhold";
$destinations[] = "Darkwater Crossing";
$destinations[] = "Dragon Bridge";
$destinations[] = "Ivarstead";
$destinations[] = "Karthwasten";
$destinations[] = "Kynesgrove";
$destinations[] = "Old Hroldan";
$destinations[] = "Riverwood";
$destinations[] = "Rorikstead";
$destinations[] = "Shor's Stone";
$destinations[] = "Stonehills";
if (IsModEnabled("BetterFastTravel")) {
    $destinations[] = "HalfMoonMill";
    $destinations[] = "HeartwoodMill";
    $destinations[] = "AngasMill";
    $destinations[] = "LakeviewManor";
    $destinations[] = "WindstadManor";
    $destinations[] = "HeljarchenHall";
    $destinations[] = "DayspringCanyon";
    $destinations[] = "Helgen";
}
$GLOBALS["F_NAMES"]["ExtCmdCarriageRide"]="BeginCarriageRide";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdCarriageRide"]="Give {$GLOBALS["PLAYER_NAME"]} a ride in your carriage";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdCarriageRide"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdCarriageRide"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "The destination you are taking {$GLOBALS["PLAYER_NAME"]}",
                    "enum" => $destinations
                ]
            ],
            "required" => [],
        ],
    ];


$GLOBALS["F_NAMES"]["ExtCmdTrainSkill"]="TrainSkill";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdTrainSkill"]="Give {$GLOBALS["PLAYER_NAME"]} a lesson at a skill that you are good at";
$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdTrainSkill"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdTrainSkill"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "The skill you are teaching to {$GLOBALS["PLAYER_NAME"]}",
                    "enum" => $destinations
                ]
            ],
            "required" => [],
        ],
    ];

$isInnKeeper = IsInFaction($GLOBALS['HERIKA_NAME'], "JobInnKeeper");
$isServer = IsInFaction($GLOBALS['HERIKA_NAME'], "JobInnServer");

if (IsModEnabled("Sunhelm") && ($isInnKeeper || $isServer)) {
  $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdServeFood";
}
if ($isInnKeeper) {
  $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdRentRoom";
}

if (IsInFaction($GLOBALS['HERIKA_NAME'], "Carriage System Vendors")) {
    $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdCarriageRide";
}

// Allow anyone to buy or sell. Don't restrict this to shop-keepers.
$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdTrade";
// Allow anyone to offer training.
$GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdTrainSkill";
?>
