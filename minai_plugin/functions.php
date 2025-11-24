<?php

//----------------------------------------------------------------
// functions rework
//----------------------------------------------------------------

$s_lang =strtolower($GLOBALS["CORE_LANG"] ?? 'en');

if ($s_lang == 'en') {

    foreach ($GLOBALS["FUNCTIONS"] as $n => $func) {

        $func_name = ($GLOBALS["FUNCTIONS"][$n]["name"] ?? "___");
        //$func_desc = ($GLOBALS["FUNCTIONS"][$n]["description"] ?? "-_-");
        
        if ($func_name == 'GiveItemTo') { //{$GLOBALS["HERIKA_NAME"]} gives item to a single actor (target property is the actor). Amount and item will be inferred from dialogue, so no need to specify.
            $GLOBALS["FUNCTIONS"][$n]["description"] = "{$GLOBALS["HERIKA_NAME"]} gives item to a single actor (target property is the actor). Target actor must be selected from <nearby_characters> list. Amount and item will be inferred from dialogue, so no need to specify.";
        } elseif ($func_name == 'GiveGoldTo') { //{$GLOBALS["HERIKA_NAME"]} takes amount (property target) of septims/coins/gold from {$GLOBALS["PLAYER_NAME"]} (property listener) once {$GLOBALS["PLAYER_NAME"]} agree.
            $GLOBALS["FUNCTIONS"][$n]["description"] = "{$GLOBALS["HERIKA_NAME"]} gives some coins/gold/septims to a single actor (target property is the actor). Target actor must be selected from <nearby_characters> list. Amount will be inferred from dialogue, so no need to specify.";
        } elseif ($func_name == 'Relax') { // Stop whatever you are doing and relax at the current location. Used to Rest, Enjoy Moment, Chill, Eat, Drink, Loosen Up.
            $GLOBALS["FUNCTIONS"][$n]["description"] = "{$GLOBALS["HERIKA_NAME"]} stops whatever is doing and relax or rest at the current location. Used to Rest, Enjoy Moment, Chill, Feast, Loosen Up.";
        }
    }
}

/*
$GLOBALS["FUNCTIONS"] = [
    [
        "name" => $F_NAMES_LOCAL["Inspect"],
        "description" => $F_TRANSLATIONS_LOCAL["Inspect"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS['FUNCTION_PARM_INSPECT']

                ]
            ],
            "required" => ["target"],
        ],
    ],

foreach ($GLOBALS["FUNCTIONS"] as $n=>$f) {
    $internalCode=getFunctionByTrlName($f["name"]);
    if (isset($GLOBALS["F_TRANSLATIONS_NEW"][$internalCode]))
        $GLOBALS["FUNCTIONS"][$n]["description"]=$GLOBALS["F_TRANSLATIONS_NEW"][$internalCode];

    if (isset($GLOBALS["F_NAMES_NEW"][$internalCode]))
        $GLOBALS["FUNCTIONS"][$n]["name"]=$GLOBALS["F_NAMES_NEW"][$internalCode];
}

*/

//----------------------------------------------------------------


// Start metrics for this entry point
require_once("utils/metrics_util.php");
// Avoid processing for fast / storage events
//if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
//    return;
//}

//error_log("->functions MINAI: " . implode(' . ', $GLOBALS["ENABLED_FUNCTIONS"]));

// Use function module caching to avoid repeated inclusion checks
if (!isset($GLOBALS["loaded_function_modules"])) {
    $GLOBALS["loaded_function_modules"] = [];
}

if (!isset($GLOBALS["db"]))
    $GLOBALS["db"] = new sql();

// Helper function for lazy-loading function modules
function load_function_module($module_path) {
    if (!isset($GLOBALS["loaded_function_modules"][$module_path])) {
        minai_start_timer('load_module_' . basename($module_path), 'functions_php');
        require_once($module_path);
        minai_stop_timer('load_module_' . basename($module_path));
        $GLOBALS["loaded_function_modules"][$module_path] = true;
    }
}
minai_start_timer('functions_php', 'MinAI');
load_function_module("functions/deviousnarrator.php");
/***

Example of plugin.

External/3rd party functions should start with prefix ExtCmd or WebCmd
ExtCdm is for functions whose return value is provided by a Papyrus plugin.
WebCmd is for functions which return value is provided by server plugin itself

In this case, function code name will be ExtCmdHeal because we need a papyrus script to do the actual command and reports back result.

For Papyrus plugin developers:
Just need to bind to SPG_CommandReceived event.

Event OnInit()
    RegisterForModEvent("SPG_CommandReceived", "HerikaHealTarget")
EndEvent

Event HerikaHealTarget(String  command, String parameter)

    if (command=="ExtCmdHeal") ; This is my function
        
        ; parse parameters and do stuff
        
        ; Finally, send request of type funcrect with the result. THis will make a request to LLM again.
        SPGPapFunctions.requestMessage("command@"+command+"@"+parameter+"@"+herikaActor.GetDisplayName()+" heals "+player.GetDisplayName()+ " using the spell 'healing hands'","funcret");	// Pass return function to LLM
    
    endif
    
EndEvent


***/

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}

// Load core utilities and configs first
load_function_module("config.php");
load_function_module("util.php");
load_function_module("customintegrations.php");
load_function_module("functions/action_builder.php");

// Preload common actor data to reduce database queries
minai_start_timer('preload_actor_data', 'functions_php');
PreloadCommonActorData();
minai_stop_timer('preload_actor_data');

// Only load additional dependencies if needed
if ($GLOBALS["force_voice_type"]) {
    load_function_module("fix_xtts.php");
}

// Cache function eligibility checks for the session
if (!isset($GLOBALS["function_eligibility_cache"])) {
    // Preload flags needed for eligibility checks
    $actorsToCheck = [$GLOBALS["HERIKA_NAME"], $GLOBALS["PLAYER_NAME"]];
    $flagsToCheck = [
        "NoActionsFaction", 
        "NoNSFWActionsFaction", 
        "NoSexActionsFaction"
    ];
    
    // Preload faction data for the actors we need to check
    PreloadFactions($actorsToCheck);
    
    // Create eligibility cache
    $GLOBALS["function_eligibility_cache"] = [
        "clear_follower" => ShouldClearFollowerFunctions(),
        "in_no_actions_faction" => IsInFaction($GLOBALS["HERIKA_NAME"], "NoActionsFaction"),
        "in_no_nsfw_faction" => IsInFaction($GLOBALS["HERIKA_NAME"], "NoNSFWActionsFaction"),
        "in_no_sex_faction" => IsInFaction($GLOBALS["HERIKA_NAME"], "NoSexActionsFaction"),
        "enable_sex" => ShouldEnableSexFunctions($GLOBALS["HERIKA_NAME"]),
        "enable_harass" => ShouldEnableHarassFunctions($GLOBALS["HERIKA_NAME"]),
        "use_devious_narrator" => ShouldUseDeviousNarrator()
    ];
}

// Load function modules conditionally based on cached eligibility
if ($GLOBALS["function_eligibility_cache"]["clear_follower"]) {
    // Enable baseline set of functions
    $allowed_functions = array();

    // Only add functions if they were already enabled (to avoid deprecated functions)
    // if (in_array('Inspect', $GLOBALS["ENABLED_FUNCTIONS"]))
        // $allowed_functions[] = 'Inspect';
    if (in_array('LookAt', $GLOBALS["ENABLED_FUNCTIONS"]))
        $allowed_functions[] = 'LookAt';
    if (in_array('InspectSurroundings', $GLOBALS["ENABLED_FUNCTIONS"]))
        $allowed_functions[] = 'InspectSurroundings';
    if (in_array('TakeASeat', $GLOBALS["ENABLED_FUNCTIONS"]))
        $allowed_functions[] = 'TakeASeat';
    if (in_array('SearchMemory', $GLOBALS["ENABLED_FUNCTIONS"]))
        $allowed_functions[] = 'SearchMemory';
    if (in_array('Attack', $GLOBALS["ENABLED_FUNCTIONS"]))
        $allowed_functions[] = 'Attack'; // Should this be enabled?

    $GLOBALS["ENABLED_FUNCTIONS"] = $allowed_functions;
}
else {
    // Follower specific commands
    if (!$GLOBALS["function_eligibility_cache"]["in_no_actions_faction"])
        load_function_module("functions/followers.php");
}

if (!$GLOBALS["function_eligibility_cache"]["in_no_actions_faction"]) {
    load_function_module("functions/survival.php");
    load_function_module("functions/crimes.php"); 
    load_function_module("functions/dirtandblood.php");

    if (!$GLOBALS["function_eligibility_cache"]["in_no_nsfw_faction"]) {
        // NSFW comands
        load_function_module("functions/arousal.php");
        if (!$GLOBALS["function_eligibility_cache"]["in_no_sex_faction"]) {
            if ($GLOBALS["function_eligibility_cache"]["enable_sex"]) {
                load_function_module("functions/sex.php");
            }
            if ($GLOBALS["function_eligibility_cache"]["enable_harass"]) {
                load_function_module("functions/slapp.php");
            }
        }
        
//error_log("-> sex functions: " . implode(' . ', $GLOBALS["ENABLED_FUNCTIONS"]));
        
        
        if ($GLOBALS["function_eligibility_cache"]["use_devious_narrator"]) {
            // Anything loaded after this will have functions enabled for the narrator
            EnableDeviousNarratorActions();
            load_function_module("functions/generalperverted.php");
        }
        if ($GLOBALS["function_eligibility_cache"]["enable_harass"]) {
            load_function_module("functions/generalperverted.php");
        }
        load_function_module("functions/deviousdevices.php");
        load_function_module("contextbuilders/deviousfollower_context.php");
        load_function_module("functions/items_commands.php");
        if ($GLOBALS["always_enable_functions"] && $GLOBALS["HERIKA_NAME"] != "The Narrator" && $GLOBALS["HERIKA_NAME"] != "Narrator" && $GLOBALS["HERIKA_NAME"] != "Player") {
            // Always enable actions for followers (During rechats and such)
            $GLOBALS["FUNCTIONS_ARE_ENABLED"]=true;
        }
    }
}

// Use a batch function to register third-party actions
minai_start_timer('register_third_party', 'functions_php');
RegisterThirdPartyActions();
minai_stop_timer('register_third_party');

// Optimize the function purge process with fewer iterations
minai_start_timer('purge_commands', 'functions_php');

// Batch load the data we need for purge decisions
$flagsToCheck = ["inCombat"];
PreloadEnabledFlags([$GLOBALS["HERIKA_NAME"]], $flagsToCheck);

// Execute the purge with cached data
$commandsToPurge=[];
$lastDefeat = GetActorValue("PLAYER", "lastDefeat");
$defeatCooldown = !empty($lastDefeat) && (time() - intval($lastDefeat) < 300);
$inCombat = IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat");
$isFollower = IsFollower($GLOBALS["HERIKA_NAME"]);
$inScene = IsSexActiveSpeaker(); 

foreach ($GLOBALS["ENABLED_FUNCTIONS"] as $n=>$func) {
    // Block Attack command if:
    // - Command is in commands_to_purge list
    // - NPC is in combat and command is Attack
    // - NPC is a follower, there's an active defeat cooldown, and command is Attack
    if ($func == "Attack") {
        if (in_array($func, $GLOBALS["commands_to_purge"]) || 
           $inCombat || $inScene ||
           ($defeatCooldown && $isFollower)) {
            $commandsToPurge[] = $n;
        }
    } elseif ($func == "ExchangeItems") {
        // Purge ExchangeItems if the npc is a follower
        if ($isFollower || $inScene) {
            $commandsToPurge[] = $n;
        } 
    } elseif ($func == "Brawl") {
        if ($inScene)
            $commandsToPurge[] = $n;
    } elseif ($func == "AttackHunt") { 
        if ($inScene)
            $commandsToPurge[] = $n;
    } elseif ($func == "Hunt") { 
        if ($inScene)
            $commandsToPurge[] = $n;
    } elseif ($func == "Fight") {
        if ($inScene)
            $commandsToPurge[] = $n;

    } elseif ($func == "OpenInventory") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "OpenInventory2") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "ExtCmdStartBathing") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "StartBathing") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "LetsRelax") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;

    } elseif ($func == "GoToSleep") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "TravelTo") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "LeadTheWayTo") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
        
    } elseif ($func == "IncreaseWalkSpeed") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "DecreaseWalkSpeed") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "FollowPlayer") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "ReturnBackHome") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;

    } elseif ($func == "TakeASeat") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } elseif ($func == "ComeCloser") {
        if ($inScene || $inCombat)
            $commandsToPurge[] = $n;
    } 
}

// if HERIKA_TARGEt is "The Narrator" and isn't the devious narrator, turn off actions.
if ($GLOBALS["target"] == "The Narrator" && !$GLOBALS["function_eligibility_cache"]["use_devious_narrator"]) {
    $GLOBALS["FUNCTIONS_ARE_ENABLED"] = false;
}

minai_stop_timer('purge_commands');

// Remove purged commands more efficiently
if (!empty($commandsToPurge)) {
    foreach ($commandsToPurge as $n) {
        unset($GLOBALS["ENABLED_FUNCTIONS"][$n]);
    }
}

minai_stop_timer('functions_php');

//error_log("->functions end: " . implode(' . ', $GLOBALS["ENABLED_FUNCTIONS"]));

