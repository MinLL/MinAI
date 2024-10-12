<?php

require_once("util.php");

function IsDeviousFollower($name) {
    $dfName = GetActorValue($GLOBALS["PLAYER_NAME"], "deviousFollowerName");
    return (strtolower($name) == strtolower($dfName) && IsModEnabled("DeviousFollowers"));
}

function GetDeviousFollowerContext($name) {
    $playerName = $GLOBALS["PLAYER_NAME"];
    $dfName = GetActorValue($playerName, "deviousFollowerName");
    if (strtolower($name) != strtolower($dfName)) {
        return "";
    }
    if (!IsModEnabled("DeviousFollowers")) {
        return "";
    }
  
    if ($dfName == "0" || $dfName == "") {
        $minai_has_df = false;
        return;
    }
    $dfName = $GLOBALS["HERIKA_NAME"]; // This will have the right capitalization / etc
    $dfDebt = GetActorValue($playerName, "deviousFollowerDebt");
    $dfEnslaveDebt = GetActorValue($playerName, "deviousFollowerEnslaveDebt");
    $dfContractRemaining = GetActorValue($playerName, "deviousFollowerContractRemaining");
    $dfWill =  GetActorValue($playerName, "deviousFollowerWillpower");
    $dfRules = GetActorValue($playerName, "deviousFollowerRules");
    $dfDueForSpanks = GetActorValue($playerName, "deviousTimeForSpanks");
    $dfNewRuleDesc = GetActorValue($playerName, "deviousFollowerNewRuleDesc");
    // Main DF prompt
    $ret = "";
    $personality = "{$dfName} is a pervert, though they initially hide this well. {$dfName}'s goal in becoming {$playerName}'s follower is to entrap them with debt that they owe {$dfName} from their fees, and eventually make {$playerName} their sex slave / pet. {$dfName} charges {$playerName} gold over time in exchange for their services, with an agreed daily payment schedule. {$dfName} has a good mix of being serious and flirty with {$playerName} and derives enjoyment from sexually teasing, spanking, and embarrassing her. {$dfName} is likely to use perverted means in their interactions if deemed appropriate. {$dfName} wants to continue adventuring and is more likely to talk about adventure and events going on than the arrangement with {$playerName} (Though {$dfName} should sometimes incorporate perverted elements into this).\n";
    if (CanVibrate($playerName)) {
        $personality .= "{$dfName} can remotely make {$playerName}'s peircings or plugs vibrate when wanted in order to reward, or punish {$playerName} depending on how {$dfName} uses them. {$dfName} can also make (or allow) {$playerName} have an orgasm or climax at will using the vibrator. {$dfName} can also cause the piercings or vibrators to painfully shock {$playerName} if displeased, or if {$dfName} wants to reduce the arousal of {$playerName}\n";
    }

    if ($dfDebt >= 2000) {
        $ret .= "{$playerName} currently owes a very large debt. {$playerName} owes thousands of gold. {$dfName} will be playful (And a little strict), and tease and arouse {$playerName} relentlessly. {$dfName} is very unlikely to let {$playerName} orgasm, unless {$playerName} really convinces them.\n";
    }
    elseif ($dfDebt >= 1000) {
        $ret .= "{$playerName} currently owes a moderately large debt. {$playerName} owes over a thousand gold. {$dfName} will still be fairly playful (teasing and arousing {$playerName} a fair bit), though will be more strict. {$dfName} will be a lot less likely to let {$playerName} orgasm.\n";
    }
    elseif ($dfDebt > 0) {
        $ret .= "{$playerName} currently has a small outstanding debt. {$playerName} does not owe much gold right now. {$dfName} will be a little less likely to let {$playerName} orgasm.\n";
    }
    else {
        $ret .= "{$playerName} does not currently owe any debt. {$dfName} is flirty and playful, seeking to distract and arouse {$playerName}. {$dfName} is more likely to let {$playerName} orgasm. {$dfName} should not talk about debt. {$playerName} does not owe {$dfName} any money currently. Do not bring up the arrangement, or deals.\n";
    }
    $ret .= "the exact amount of gold {$playerName} owes {$dfName} is {$dfDebt} gold.\n";
    
    if ($dfContractRemaining) {
        $daysRemaining = "";
        if ($dfContractRemaining > 80 && $dfContractRemaining <= 100) {
            $daysRemaining = "about 3 months";
        }
        elseif ($dfContractRemaining > 70 && $dfContractRemaining <= 80) {
            $daysRemaining = "2 or 3 months";
        }
        elseif ($dfContractRemaining > 50 && $dfContractRemaining <= 70) {
            $daysRemaining = "about 2 months";
        }
        elseif ($dfContractRemaining > 40 && $dfContractRemaining <= 50) {
            $daysRemaining = "1 or 2 months";
        }
        elseif ($dfContractRemaining > 20 && $dfContractRemaining <= 40) {
            $daysRemaining = "about 1 month";
        }
        elseif ($dfContractRemaining > 14 && $dfContractRemaining <= 20) {
            $daysRemaining = "a bit over 2 weeks";
        }
        elseif ($dfContractRemaining > 7 && $dfContractRemaining <= 14) {
            $daysRemaining = "1 or 2 weeks";
        }
        else {
            $daysRemaining = "{$dfContractRemaining} days";
        }
        $ret .= "{$playerName} has agreed to hire {$dfName} as part of a contract, and must continue to pay them. There are currently {$daysRemaining} remaining on the contract.\n";
    }
    // Special rule handling
    $rules = preg_split("/\r\n|\n|\r/", $dfRules);
    foreach ($rules as $rule) {
        if (!$rule) {
            continue;
        }
        $ret .= "{$playerName} has agreed to the the following deal with their follower, and must obey it: $rule\n";
        if (str_contains($rule, "daily skooma")) {
            $ret .= "{$playerName} has agreed to be take drugs that {$dfName} will provide daily. {$dfName} should provide {$playerName} with drugs if they ask. {$dfName} should also feel free, on their own initiative, to drug the player. {$dfName} wants to get {$playerName} addicted to drugs so that they can use them as an additional means of controlling {$playerName}.\n";
            // Enable drugs command
            $GLOBALS["F_NAMES"]["ExtCmdGiveDrugs"]="GiveDrugs";
            $GLOBALS["F_TRANSLATIONS"]["ExtCmdGiveDrugs"]="Give drugs/skooma to {$GLOBALS["PLAYER_NAME"]}";
            $GLOBALS["FUNCTIONS"][] = [
                "name" => $GLOBALS["F_NAMES"]["ExtCmdGiveDrugs"],
                "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdGiveDrugs"],
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
            $GLOBALS["FUNCRET"]["ExtCmdGiveDrugs"]=$GLOBALS["GenericFuncRet"];
            $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdGiveDrugs";
        }
        if (str_contains($rule, "chastity")) {
            // Special Chastity Handling
            if (HasKeyword($playerName, "ZAD_DeviousBelt")) {
                $ret .= "{$playerName} must wear a chastity belt that only {$dfName} has the key to at all times. This is to ensure that {$playerName} cannot get relief without permission, and to show that {$dfName} owns {$playerName}'s pussy.\n";
            }
        }
        if (str_contains($rule, "daily spanks")) {
            if ($dfDueForSpanks) {
                $ret .= "{$playerName} has not asked for a spanking in a while, and is due for her daily spanking again. {$dfName} will remind {$playerName} about her agreement to beg for daily spanks. Make {$playerName} beg to be spanked before doing so.\n";
            }
            else {
                $ret .= "{$playerName} has already asked for a spanking today. {$dfName} does not need to remind them to do so. {$dfName} may still spank them if they wish to do so.\n";
            }
        }
    }
    if ($dfDebt > $dfEnslaveDebt) {
        $ret .= "{$dfName} is very concerned about {$playerName}'s current debt level. Talking about this is the highest possible priority. {$dfName} wants to discuss a new rule, and get {$playerName} to agree to it in exchange for reducing the debt {$playerName} owes. The new rule is: {$dfNewRuleDesc} ";
        $GLOBALS["F_NAMES"]["ExtCmdAcceptDeal"]="AcceptDeal";
        $GLOBALS["F_TRANSLATIONS"]["ExtCmdAcceptDeal"]="Use this if {$GLOBALS["PLAYER_NAME"]} has agreed to the deal you are offering.";
        $GLOBALS["FUNCTIONS"][] = [
            "name" => $GLOBALS["F_NAMES"]["ExtCmdAcceptDeal"],
            "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdAcceptDeal"],
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
        $GLOBALS["FUNCRET"]["ExtCmdAcceptDeal"]=$GLOBALS["GenericFuncRet"];
        RegisterAction("ExtCmdAcceptDeal");
    }


    $ret .= "{$playerName}'s remaining willpower to resist {$dfName} is {$dfWill}/10, where 0 is completely mind-broken, and 10 is completely free-spirited.\n";
    $GLOBALS["HERIKA_PERS"] .= " " . $personality;
    if ($ret != "")
        $ret .= "\n";
    return $ret;
}



?>
