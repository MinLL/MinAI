<?php
require_once("config.php");
require_once("util.php");
require_once("contextbuilders.php");
require_once("mind_influence.php");
require_once("environmentalContext.php");

$nearbyActors = GetActorValue("PLAYER", "nearbyActors", true);
// Build context
$new_content = "";

if (!$GLOBALS["disable_nsfw"]) {
  if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $new_content .= BuildContext("The Narrator") . "\n";
  } else {
    $new_content .= BuildContext(GetTargetActor()) . "\n";
    $new_content .= BuildContext($GLOBALS["HERIKA_NAME"]);
  }
  $new_content .= BuildNSFWReputationContext($GLOBALS["HERIKA_NAME"]) . "\n";
}

// SFW Descriptions
// We're going to scan everyone who is nearby
// for highly visible traits of people, like 
// they reek or are filthy.
bundleSFWContext($new_content);
function bundleSFWContext(&$nc) {
  $utilities = new Utilities();
  // list of local npcs (sans narrator)
  $nc .= "\n";
  $localActors = $utilities->beingsInCloseRange();
  // send localActors list to GetDirtAndBlood so as to make comma seperated lists
  $nc .= GetRelationshipContext($GLOBALS["HERIKA_NAME"]);
  $nc .= GetDirtAndBloodContext($localActors);
  $nc .= GetExposureContext($localActors);
  $nc .= GetEnvironmentalContext($GLOBALS["HERIKA_NAME"]);
  $nc .= BuildSFWReputationContext($GLOBALS["HERIKA_NAME"]);
  $nc .= GetThirdPartyContext();
  $nc .= GetWeatherContext() . "\n";
}

$GLOBALS["HERIKA_PERS"] = $GLOBALS["HERIKA_PERS"] . "\n" . $new_content;

// Clean up context
$locaLastElement=[];
$narratorElements=[];
$sexInfoElements=[];
$physicsInfoElements=[];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    if (strpos($ctxLine["content"],"#SEX_SCENARIO")!==false) {
        preg_match('/#ID_(\d+)/', $ctxLine["content"], $matches);
        if (!empty($matches)) {
          $threadId = $matches[1];
        } else {
          $threadId = "other";
        }

        if (!isset($locaLastElement[$threadId])) {
          $locaLastElement[$threadId] = []; // Initialize as an array if it doesn't exist
        }
        array_push($locaLastElement[$threadId], $n);
    }
    if ($GLOBALS["stop_narrator_context_leak"] && $GLOBALS["HERIKA_NAME"] != "The Narrator") {
        if (strpos($ctxLine["content"],"The Narrator:")!==false && strpos($ctxLine["content"],"(talking to")!==false) {
            $narratorElements[]=$n;
        }
    }
    if (strpos($ctxLine["content"],"#SEX_INFO")!==false) {
        $sexInfoElements[]=$n;
    }
    if (strpos($ctxLine["content"],"#PHYSICS_INFO")!==false) {
        $physicsInfoElements[]=$n;
    }
}
// Remove all references to sex scene, and only keep the last one.
// Add support for multithread scenes to keep scene descriptions for all threads
foreach ($locaLastElement as $thredId => $threadCtxLines) {
  if(is_array($threadCtxLines) && !empty($threadCtxLines)) {
    // try to find context #SEX_SCENARIO scene among currently running scenes
    $scene = getScene("", $thredId);
    // We want to keep last context line from 'other' category(if any), or for active scenes. If scene stopeed and not playing anymore we don't want to put it into context
    if($thredId === "other" || isset($scene)) {
      array_pop($threadCtxLines);
    }
    foreach ($threadCtxLines as $n) {
      unset($GLOBALS["contextDataFull"][$n]); 
    }
  }
}

// Cleanup narrator context for non-narrator actors
foreach ($narratorElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

// Remove all references to sex scene info, and only keep the last one.
array_pop($sexInfoElements);
foreach ($sexInfoElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

// Remove all references to physics / collision info, and only keep the last three.
array_pop($physicsInfoElements);
array_pop($physicsInfoElements);
array_pop($physicsInfoElements);
foreach ($physicsInfoElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

/*$nullValues = [];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    minai_log("info", "Checking ({$n}) {$ctxLine["content"]}");
    if (!$ctxLine["content"] || $ctxLine["content"] == null || $ctxLine["content"]  == "") {
        $nullValues[] = $n;
        minai_log("info", "Found null value in context ({$n})");
    }
}
foreach ($nullValues as $n) {
    minai_log("info", "Unsetting null value $n");
    unset($GLOBALS["contextDataFull"][$n]); 
}*/


// Cleanup self narrator dialogue to avoid contaminating general context
if ($GLOBALS["minai_processing_input"]) {
    minai_log("info", "Cleaning up player input");
    DeleteLastPlayerInput();
}
$GLOBALS["contextDataFull"] = array_values($GLOBALS["contextDataFull"]);

require "/var/www/html/HerikaServer/ext/minai_plugin/command_prompt_custom.php";
