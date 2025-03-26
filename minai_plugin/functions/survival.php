<?php

require_once("action_builder.php");


// Function to check if Herika is in a specific faction
function IsInRole($role) {
    $herikaName = $GLOBALS["HERIKA_NAME"];
    
    switch ($role) {
        case "innkeeper":
            return IsInFaction($herikaName, "JobInnKeeper");
        case "server":
            return IsInFaction($herikaName, "JobInnServer");
        default:
            return false;
    }
}

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

// Condition functions for different action types

// Checks if actor can serve food
function canServeFood() {
    return (IsInRole("innkeeper") || IsInRole("server")) && 
           IsModEnabled("Sunhelm") && 
           !IsRadiant();
}

// Checks if actor can rent rooms
function canRentRoom() {
    return IsInRole("innkeeper") && 
           !IsRadiant();
}

// Checks if actor can offer carriage rides
function canOfferCarriageRide() {
    return IsInFaction($GLOBALS["HERIKA_NAME"], "Carriage System Vendors") && 
           !IsRadiant();
}

// Checks if actor can trade
function canTrade() {
    return !IsFollower($GLOBALS["HERIKA_NAME"]) && 
           !IsRadiant();
}

// Checks if actor can train skills
function canTrainSkill() {
    return IsInFaction($GLOBALS["HERIKA_NAME"], "Skill Trainer") && 
           !IsRadiant();
}


// Register ServeFood action
registerMinAIAction("ExtCmdServeFood", "ServeFood")
    ->withDescription("Provide food and drink to {$GLOBALS["PLAYER_NAME"]} - alleviates hunger and thirst in survival mode")
    ->withEnableCondition('canServeFood')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register RentRoom action
registerMinAIAction("ExtCmdRentRoom", "RentRoom")
    ->withDescription("Offer a room for rent to {$GLOBALS["PLAYER_NAME"]} - provides a place to sleep and store belongings")
    ->withEnableCondition('canRentRoom')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register CarriageRide action
registerMinAIAction("ExtCmdCarriageRide", "CarriageRide")
    ->withDescription("Transport {$GLOBALS["PLAYER_NAME"]} to another settlement - faster than walking but costs gold. Must specify target as the destination. Available destinations: " . implode(", ", $destinations))
    ->withEnableCondition('canOfferCarriageRide')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register Trade action
registerMinAIAction("ExtCmdTrade", "Trade")
    ->withDescription("Engage in buying and selling items with {$GLOBALS["PLAYER_NAME"]} - facilitates commerce and equipment upgrades")
    ->withEnableCondition('canTrade')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register TrainSkill action
registerMinAIAction("ExtCmdTrainSkill", "TrainSkill")
    ->withDescription("Provide instruction to {$GLOBALS["PLAYER_NAME"]} in a skill - helps them improve abilities")
    ->withParameter("target", "string", "The skill you are teaching to {$GLOBALS["PLAYER_NAME"]}")
    ->withEnableCondition('canTrainSkill')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

