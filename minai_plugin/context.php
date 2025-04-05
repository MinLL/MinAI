<?php
// Start metrics for this entry point
require_once("utils/metrics_util.php");
minai_start_timer('context_php', 'MinAI');

// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
  return;
}
require_once("config.php");
require_once("util.php");
require_once("contextbuilders.php");
require_once("mind_influence.php");
require_once("environmentalContext.php");
require_once("contextbuilders/system_prompt_context.php");
require_once("utils/prompt_slop_cleanup.php");


minai_start_timer("contextProcessing", "context_php");
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

// Clean up slop text patterns
minai_start_timer('cleanupSlop', 'contextProcessing');
if (isset($GLOBALS["enable_prompt_slop_cleanup"]) && $GLOBALS["enable_prompt_slop_cleanup"]) {
    $GLOBALS["contextDataFull"] = cleanupSlop($GLOBALS["contextDataFull"]);
}
minai_stop_timer('cleanupSlop');

// Re-index the array after removing elements
$GLOBALS["contextDataFull"] = array_values($GLOBALS["contextDataFull"]);
minai_stop_timer('contextProcessing');

// Update the system prompt (0th entry) with our optimized version
UpdateSystemPrompt();

require "/var/www/html/HerikaServer/ext/minai_plugin/command_prompt_custom.php";
minai_stop_timer('context_php');
// minai_stop_timer('Pre-LLM');