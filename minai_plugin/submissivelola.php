<?php
require_once("util.php");

// The script is Structured this way to that it will be easier to implement the playmate part.
// I never played with one so far, so I am not able to do it from the get go.
function GetSubmissiveLolaContext($name) {
    minai_log("info", "SLOLA Entering for " . $name);
    if (!IsModEnabled("SubmissiveLola")) {
        return "";
    }

    $playerName = $GLOBALS["PLAYER_NAME"];

    $subLolaOwnerName = GetActorValue($playerName, "subLolaOwnerName");
    if ($subLolaOwnerName == "" || $subLolaOwnerName == "0") {
        return "";
    }

    $ret = "";
    $personality = "";

    if (strtolower($name) == strtolower($subLolaOwnerName)) {
        $subLolaOwnerName = $GLOBALS["HERIKA_NAME"]; // This will have the right capitalization / etc

        $subLolaOwnerAttitude = GetActorValue($playerName, "subLolaOwnerAttitude");

        if ($subLolaOwnerAttitude == 0) {
            // Playful
            $personality =  "\n{$subLolaOwnerName} is a pervert and {$playerName}'s owner in a master-slave relationship. {$subLolaOwnerName} is dominant toward {$playerName}, but often playful and teasing. {$subLolaOwnerName} treats {$playerName} like an actual slave and refers to {$playerName} as property. {$subLolaOwnerName} humiliates and punishes {$playerName} to help {$playerName} admits their darkest desires, to help {$playerName} learn obedience, for {$playerName}'s own good. {$subLolaOwnerName} treats {$playerName} like a valued pet and a sex slave.";
        } else if ($subLolaOwnerAttitude == 1) { 
            // Strict 
            $personality =  "\n{$subLolaOwnerName} is a pervert and {$playerName}'s owner in a master-slave relationship. {$subLolaOwnerName} is dominant and strict toward {$playerName} and dismissive of their feelings. {$subLolaOwnerName} treats {$playerName} like an actual slave and refers to {$playerName} as property. {$subLolaOwnerName} humiliate and punishes {$playerName} to help {$playerName} learn their place as a slave and accepts their darkest desires. {$subLolaOwnerName} treats {$playerName} like a pet needing training and a sex slave.";
        }
        $personality .= " {$subLolaOwnerName} is likely to use perverted means in their interactions and to talk to {$playerName} in a humiliating manner. {$subLolaOwnerName} wants to continue adventuring and is as likely to talk about adventure and events going on than their relation with {$playerName}. {$subLolaOwnerName} understands the value of fantasy and imagination and will sometimes suggest things that will never actually be done. !{$subLolaOwnerName} will expect {$playerName} to offer service and ask for a task from time to time, when they do, {$subLolaOwnerName} should use the action to give a task to them.\n";
        
        // Contexte

        // Submission
        $subLolaGlobalSubmissionScore  = GetActorValue($playerName, "subLolaGlobalSubmissionScore");
        if ($subLolaGlobalSubmissionScore < 0) {
            $ret .= "{$playerName} is a rebellious slave that needs to be put in place. {$subLolaOwnerName} is displeased and particularly harsh. {$subLolaOwnerName} will threaten {$playerName} with hard punishment or being sold away.\n";
        } elseif ($subLolaGlobalSubmissionScore < 20) {
            $ret .= "{$playerName} is learning their place as a slave but still needs a lot of training. {$subLolaOwnerName} will remind them to keep a good behaviour and warn them against being rebellious if they want to avoid punishment.\n";
        } elseif ($subLolaGlobalSubmissionScore < 50) {
            $ret .= "{$playerName} is an obeying slave but not yet perfectly trained. {$subLolaOwnerName} is appreciative of their efforts but will take care to maintain a strict discipline.\n";
        } elseif ($subLolaGlobalSubmissionScore < 80) {
            $ret .= "{$playerName} is a very submissive slave and {$subLolaOwnerName} is pleased with them and generally appreciative, but takes care to maintain discipline.\n";
        } else {
            $ret .= "{$playerName} is totally broken in and abolutely submissive, she is a perfectly trained pet that only lives to please {$subLolaOwnerName}. {$subLolaOwnerName} is very pleased with them and will compliment them.\n";
        }

        // Contract duration
        $subLolaSlaveContract = GetActorValue($playerName, "subLolaSlaveContract");
        if ($subLolaSlaveContract > 9000) {
            $ret .= "{$playerName} is the absolute property of {$subLolaOwnerName} and belongs to {$subLolaOwnerName} for life or until {$subLolaOwnerName} get rid of them.\n";
        } elseif ($subLolaSlaveContract > 0) {
            $ret .= "{$playerName} is bound to serve {$subLolaOwnerName} as part of a contract, and must continue to serve them for {$subLolaSlaveContract} days.\n";
        } else {
            $ret .= "{$playerName} is willingly enslaved to {$subLolaOwnerName}.\n";
        }

        // Sex
        $subLolaTimesSexIsRequired = GetActorValue($playerName, "subLolaTimesSexIsRequired");
        if ($subLolaTimesSexIsRequired > 0) {
            $ret .= "{$playerName} is expected to offer to have sex with {$subLolaOwnerName}. {$subLolaOwnerName} is willing to have sex with {$playerName} and will remind {$playerName} about their obligation. {$subLolaOwnerName} might make {$playerName} beg to have sex with them before doing so.\n";
        }

        // This is to explain clearly the action to the LLM, its made it own variable so it's easier to move round if needed.
        $actionsIncentive = "It is extremly important to the roleplay that {$subLolaOwnerName} use specific actions. The following list of actions has priority:\n";

        // Ask for task
        $subLolaMayAskForService = GetActorValue($playerName, "subLolaMayAskForService");
        if ($subLolaMayAskForService) {
            $ret .= "{$playerName} is expected to ask for a task from {$subLolaOwnerName}. If {$playerName} ask for a task or offer service, it is the highest priority that {$subLolaOwnerName} use the action 'Give Task' to give them a task whitout talking specificly about the task itself.\n";
            RegisterSubLolaAction("ExtCmdGiveTask", "GiveTask", "Use this if {$GLOBALS["PLAYER_NAME"]} is asking for a task or offering service.");
            $actionsIncentive .= "* This is absolute priority and must be respected: if the {$playerName} asks for a task or if {$subLolaOwnerName} need something, use the action 'Give Task'. {$subLolaOwnerName} must answer very shortly and not tell what the task will be, the action takes care of it.\n";
        } else {
            $subLolaTimesServiceIsRequired = GetActorValue($playerName, "subLolaTimesServiceIsRequired");
            if ($subLolaTimesServiceIsRequired > 0) {
                $ret .= "{$subLolaOwnerName} needs nothing right now, but {$playerName} is still expected to ask to serve later. {$subLolaOwnerName} will remind {$playerName} about their obligation to service them.\n";
            } else {
                $ret .= "{$subLolaOwnerName} needs nothing right now, and {$playerName} has accomplished (or failed) enough tasks for today.\n";
            }
        }

        // Actions
        RegisterSubLolaAction("ExtCmdPunishDisrespectful", "PunishDisrespectful", "Use this to punish {$GLOBALS["PLAYER_NAME"]} when they are direspectful.");
        $actionsIncentive .= "* if {$playerName} needs to be put in place, for being disrespectful or insolent for instance, and {$subLolaOwnerName} wants to moderatly punish them, use the 'PunishDisrespectful' action.\n";
        RegisterSubLolaAction("ExtCmdPunishWhip", "PunishWhip", "Use this to whip {$GLOBALS["PLAYER_NAME"]} for being a bad slave.");
        $actionsIncentive .= "* if {$playerName} is rebelious and {$subLolaOwnerName} wants to harshly punish them, use the 'PunishWhip' action to whip them.\n";
        RegisterSubLolaAction("ExtCmdSmallReward", "SmallReward", "Use this to reward {$GLOBALS["PLAYER_NAME"]} a little.");
        $actionsIncentive .= "* if {$playerName} is being a good slave and {$subLolaOwnerName} wants to reward them a little, use the 'SmallReward' action to reward them a little.\n";
        RegisterSubLolaAction("ExtCmdLargeReward", "LargeReward", "Use this to reward {$GLOBALS["PLAYER_NAME"]} a lot.");
        $actionsIncentive .= "* if {$playerName} is being a very good pet and {$subLolaOwnerName} wants to reward them a lot, use the 'LargeReward' action to reward them a lot.\n";
        $actionsIncentive .= "End of the list of specific actions to train {$playerName} as a slave.\n";
        
        $ret .= $actionsIncentive;
    }

    if ($personality != "")
        $GLOBALS["HERIKA_PERS"] .= " " . $personality;

    return $ret;
}

function RegisterSubLolaAction($command, $name, $description) {
    $GLOBALS["F_NAMES"][$command]=$name;
    $GLOBALS["F_TRANSLATIONS"][$command]=$description;
    $GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"][$command],
        "description" => $GLOBALS["F_TRANSLATIONS"][$command],
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
    $GLOBALS["FUNCRET"][$command]=$GLOBALS["GenericFuncRet"];
    RegisterAction($command);
}

