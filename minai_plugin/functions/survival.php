<?php

require_once("action_builder.php");

// Function to check if the stop following action should be enabled
function shouldEnableStopFollowing() {
    return !IsFollower($GLOBALS['HERIKA_NAME']) && 
           !IsRadiant() && 
           IsFollowing($GLOBALS['HERIKA_NAME']);
}

// Function to check if the follow target action should be enabled
function shouldEnableFollowTarget() {
    return !IsFollower($GLOBALS['HERIKA_NAME']) && 
           !IsRadiant() && 
           !IsFollowing($GLOBALS['HERIKA_NAME']);
}

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
directRegisterAction(
    "ExtCmdServeFood", 
    "ServeFood", 
    "Provide food and drink to {$GLOBALS["PLAYER_NAME"]} - alleviates hunger and thirst in survival mode",
    canServeFood()
);

// Register RentRoom action
directRegisterAction(
    "ExtCmdRentRoom", 
    "RentRoom", 
    "Offer a room for rent to {$GLOBALS["PLAYER_NAME"]} - provides a place to sleep and store belongings",
    canRentRoom()
);

// Register CarriageRide action
directRegisterAction(
    "ExtCmdCarriageRide", 
    "CarriageRide", 
    "Transport {$GLOBALS["PLAYER_NAME"]} to another settlement - faster than walking but costs gold. Must specify target as the destination. Available destinations: " . implode(", ", $destinations),
    canOfferCarriageRide()
);

// Register Trade action
directRegisterAction(
    "ExtCmdTrade", 
    "Trade", 
    "Engage in buying and selling items with {$GLOBALS["PLAYER_NAME"]} - facilitates commerce and equipment upgrades",
    canTrade()
);

// Register TrainSkill action
directRegisterAction(
    "ExtCmdTrainSkill", 
    "TrainSkill", 
    "Provide instruction to {$GLOBALS["PLAYER_NAME"]} in a skill - helps them improve abilities",
    canTrainSkill(),
    [],
    ["target"]
);

// Register "Stop Following" action
directRegisterAction(
    "ExtCmdStopFollowing", 
    "StopFollowing", 
    "Cease following the target - use when you want to remain in current location",
    shouldEnableStopFollowing()
);

// Register "Follow Target" action
directRegisterAction(
    "ExtCmdFollow", 
    "FollowTarget", 
    "Start following the target to a new location - use when you want to accompany them",
    shouldEnableFollowTarget()
);