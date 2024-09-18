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


require_once("config.php");
if (!$disable_nsfw) {
    require "generalperverted.php";
    require "deviousdevices.php";
    require "arousal.php";
    require "sex.php";
    require "slapp.php";
}
require "survival.php";
if ($force_voice_type) {
    require "fix_xtts.php";
}

if (!$disable_nsfw) {
    require_once("deviousnarrator.php");
    if (ShouldUseDeviousNarrator()) {
        EnableDeviousNarratorActions();
    }
}

?>
