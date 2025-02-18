<?php

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

require_once("config.php");
require_once("util.php");
require_once("customintegrations.php");


if ($GLOBALS["force_voice_type"]) {
    require "fix_xtts.php";
}

if (ShouldClearFollowerFunctions()) {
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
    if (!IsInFaction($GLOBALS["HERIKA_NAME"], "NoActionsFaction"))
        require "followers.php";
}

if (!IsInFaction($GLOBALS["HERIKA_NAME"], "NoActionsFaction")) {
    require "survival.php";



    if (!$GLOBALS["disable_nsfw"] && !IsInFaction($GLOBALS["HERIKA_NAME"], "NoNSFWActionsFaction")) {
        // NSFW comands
        require "arousal.php";
        if (!IsInFaction($GLOBALS["HERIKA_NAME"], "NoSexActionsFaction")) {
            if (ShouldEnableSexFunctions($GLOBALS["HERIKA_NAME"])) {
                require "sex.php";
            }
            if (ShouldEnableHarassFunctions($GLOBALS["HERIKA_NAME"])) {
                require "slapp.php";
            }
        }
        require_once("deviousnarrator.php");
        if (ShouldUseDeviousNarrator()) {
            // Anything loaded after this will have functions enabled for the narrator
            EnableDeviousNarratorActions();
            require_once("generalperverted.php");
        }
        if (ShouldEnableHarassFunctions($GLOBALS["HERIKA_NAME"])) {
            require_once("generalperverted.php");
        }
        require "deviousdevices.php";
        require_once("deviousfollower.php");
        if ($GLOBALS["always_enable_functions"] && $GLOBALS["HERIKA_NAME"] != "The Narrator" && $GLOBALS["HERIKA_NAME"] != "Narrator" && $GLOBALS["HERIKA_NAME"] != "Player") {
            // Always enable actions for followers (During rechats and such)
            $GLOBALS["FUNCTIONS_ARE_ENABLED"]=true;
        }
    }
}

RegisterThirdPartyActions();

$commandsToPurge=[];
foreach ($GLOBALS["ENABLED_FUNCTIONS"] as $n=>$func) {
    if (in_array($func, $GLOBALS["commands_to_purge"]) || (IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat") && $func == "Attack")) {
        $commandsToPurge[] = $n;
    }
}

foreach ($commandsToPurge as $n) {
    unset($GLOBALS["ENABLED_FUNCTIONS"][$n]);
}



