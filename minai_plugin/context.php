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

//---------------------------------------------------------------------------
// Slop cleanup:
// Delete useless information.
// Values are case insensitive.
// Values should ordered from longer first to shortest last. 
//---------------------------------------------------------------------------

$str_to_clean_list = [ // all these are deleted from output
	'## Snow Fox (far away)',  
	'## Rabbit (far away)',
	'## Snake (far away)',
	'## Deer (far away)',
	'## Goat (far away)',
	'## Cow (far away)',
	'## Fox (far away)',
	'2 Snow Fox (far away)', 
	'Snow Fox (far away)', 
	'2 Rabbit (far away)',
	'Rabbit (far away)',
	'Snake (far away)',
	'2 Deer (far away)',
	'Deer (far away)',
	'2 Goat (far away)',
	'Goat (far away)',
	//'2 Wolf (far away)',
	//'Wolf (far away)',
	'2 Cow (far away)',
	'Cow (far away)',
	'2 Fox (far away)',
	'Fox (far away)',
	
	'2 Frost Troll (dead)',
	'Frost Troll (dead)',
	'2 Frostbite Spider (dead)',
	'Frostbite Spider (dead)',
	'2 Giant Youngling (dead)',
	'Giant Youngling (dead)',
	'2 Giantess (dead)',
	'Giantess (dead)',
	'2 Giant (dead)',
	'Giant (dead)',
	'2 Wolf (dead)',
	'Wolf (dead)',
	'2 Bear (dead)',
	'Bear (dead)',
	'2 Snow Bear (dead)',
	'Snow Bear (dead)',
	'2 Deer (dead)',
	'Deer (dead)',
	'2 Goat (dead)',
	'Goat (dead)',
	'2 Cow (dead)',
	'Cow (dead)',
	'2 Fox (dead)',
	'Fox (dead)',

	// 	
	'(hint:)' 
];

$targets_to_clean_list = [ // all these are deleted from targets list
	'2 Snow Fox (far away),', 
	'Snow Fox (far away),', 
	'2 Rabbit (far away),',
	'Rabbit (far away),',
	'Snake (far away),',
	'2 Deer (far away),',
	'Deer (far away),',
	'2 Goat (far away),',
	'Goat (far away),',
	//'2 Wolf (far away),',
	//'Wolf (far away),',
	'2 Cow (far away),',
	'Cow (far away),',
	'2 Fox (far away),',
	'Fox (far away),',
	
	'2 Frost Troll (dead),',
	'Frost Troll (dead),',
	'2 Frostbite Spider (dead),',
	'Frostbite Spider (dead),',
	'2 Giant Youngling (dead),',
	'Giant Youngling (dead),',
	'2 Giantess (dead),',
	'Giantess (dead),',
	'2 Giant (dead),',
	'Giant (dead),',
	'2 Wolf (dead),',
	'Wolf (dead),',
	'2 Bear (dead),',
	'Bear (dead),',
	'2 Snow Bear (dead),',
	'Snow Bear (dead),',
	'2 Deer (dead),',
	'Deer (dead),',
	'2 Goat (dead),',
	'Goat (dead),',
	'2 Cow (dead),',
	'Cow (dead),',
	'2 Fox (dead),',
	'Fox (dead),'
	
	
];

//---------------------------------------------------------------------------
// Multiple replacements:
// Dictionary is parsed first to last for replacements. 
// 'key' => 'value'
// Any key found is replaced with value. 
// Keys are case sensitive.
// Keys should ordered from longer first to shortest last. 
//---------------------------------------------------------------------------

$replacements_dictionary = [ // hardwired for now, probably better as an external resource

	//# HISTORIC DIALOGUE AND EVENTS IN CHRONOLOGICAL ORDER
	'HISTORIC DIALOGUE AND EVENTS IN CHRONOLOGICAL ORDER' => 'DIALOGUE HISTORY and RECENT EVENTS in chronological order',
	//# NEARBY ACTORS/NPC IN THE SCENE 
	'NEARBY ACTORS/NPC IN THE SCENE' => 'NEARBY CHARACTERS IN THE SCENE',

    //Player:The Narrator:	
    $GLOBALS["PLAYER_NAME"].":The Narrator:" => $GLOBALS["PLAYER_NAME"].":",
	'Snow Fox (hostile)' => 'Snow Fox', 
	'Rabbit (hostile)' => 'Rabbit', 
	'Snake (hostile)' => 'Snake', 
	'2 Deer (hostile)' => '2 Deer',
	'Deer (hostile)' => 'Deer',
	'2 Goat (hostile)' => '2 Goat', 
	'Goat (hostile)' => 'Goat', 
	'Cow (hostile)' => 'Cow', 
	'Fox (hostile)' => 'Fox', 
	'Rat (hostile)' => 'Rat',

	// cleanup:
	'  ' => ' ',
	', ,' => ',',
	', .' => '.',
	',.' => '.',
	'),' => ',',
	',,' => ','
];            


function CustomLineProcess($contextLine="", $s2clean_list, $repl_dictionary) {
// clean context element 
	$s_res = "";
	if (strlen(trim($contextLine)) > 0) {
		$s_clean1 = str_ireplace($s2clean_list, [' '], $contextLine);
		$s_clean2 = strtr($s_clean1, $repl_dictionary);
		$s_res = $s_clean2;
	}
	return $s_res;
}

function CustomContextProcess($contextData, $str2clean_list, $replace_dictionary) {
// clean context array elements 
    if (!is_array($contextData)) {
        return $contextData;
    }

    if (!is_array($str2clean_list)) {
		if (strlen($str2clean_list)<1)
			return $contextData;
    }

    if (!is_array($replace_dictionary)) {
        return $contextData;
    } else {
		if (count($replace_dictionary)<1)
			return $contextData;
	}

	$i = 0;
	
	$cleaned_res = [];
	foreach ($contextData as $entry) {
        if (!isset($entry['content'])) {
            continue;
        }
		
        $originalContent = $entry['content'];	
		$s_clean = CustomLineProcess($originalContent, $str2clean_list, $replace_dictionary);
		$entry['content'] = $s_clean;
		$cleaned_res[] = $entry;
		
		$i = $i + 1;
	}

	return $cleaned_res;
}

function CustomFunctionsProcess($contextData, $str2clean_list, $replace_dictionary) {
// clean functions
    if (!is_array($contextData)) {
        return $contextData;
    }

    if (!is_array($str2clean_list)) {
		if (strlen($str2clean_list)<1)
			return $contextData;
    }

    if (!is_array($replace_dictionary)) {
        return $contextData;
    } else {
		if (count($replace_dictionary)<1)
			return $contextData;
	}
	
	$i = 0;
	
	$cleaned_res = [];
	foreach ($contextData as $entry) {
        if (!isset($entry['description'])) {
            continue;
        }
		
        $originalContent = $entry['description'];	

		$s_clean = CustomLineProcess($originalContent, $str2clean_list, $replace_dictionary);
		$entry['description'] = $s_clean;
		$cleaned_res[] = $entry;
		
		$i = $i + 1;
	}
	return $cleaned_res;
}

Function CustomCleanTargets($clean_corpses=false, $clean_far_away=false, $clean_hostile_rabbits=false) {
// clean targets list
	if (isset($GLOBALS["FUNCTION_PARM_INSPECT"]) && ($clean_corpses || $clean_far_away) ) {
		$s_x = implode(",", $GLOBALS["FUNCTION_PARM_INSPECT"]);

		foreach ($GLOBALS["FUNCTION_PARM_INSPECT"] as $ix => $s_target) {
			//$s_x .= $s_target.",";
			if ($clean_corpses && stripos($s_target,'(dead)')) {
				unset($GLOBALS["FUNCTION_PARM_INSPECT"][$ix]);
			}
			if ($clean_far_away && stripos($s_target,'(far away)')) {
				unset($GLOBALS["FUNCTION_PARM_INSPECT"][$ix]);
			}
			if ($clean_hostile_rabbits && stripos($s_target,'(hostile)')) {
				if (stripos($s_target,'rabbit ') || 
					stripos($s_target,'horse ') || 
					stripos($s_target,'deer ') || 
					stripos($s_target,'goat ') || 
					stripos($s_target,'elk ') || 
					stripos($s_target,'cow ') || 
					stripos($s_target,'cat ') || 
					stripos($s_target,'fox ') || 
					
					stripos($s_target,'rat ') 
				) {
					unset($GLOBALS["FUNCTION_PARM_INSPECT"][$ix]);
				}
			}
			//$s_y .= $s_target.",";
		}
		$s_y = implode(",", $GLOBALS["FUNCTION_PARM_INSPECT"]);
	}
}


//--------------------------------------------------------------
// context replacements:
//--------------------------------------------------------------

if (isset($GLOBALS['head'])) { // clean system (head) prompt
	if (is_array($GLOBALS['head'])) {
		$a_x = CustomContextProcess($GLOBALS['head'], $str_to_clean_list, $replacements_dictionary); 
		$GLOBALS['head'] = $a_x; 
	}
	
	//warn about relationship
	if (stripos($GLOBALS['head'][0]['content'],'rival, foe')) {
			error_log(" - WARNING - relationship " . ($GLOBALS["HERIKA_NAME"] ?? "?") );
	}
	
}	

if (isset($GLOBALS["contextDataFull"])) { // clean context array parsing all elements
	$GLOBALS['contextDataFull'] = CustomContextProcess($GLOBALS['contextDataFull'], $str_to_clean_list, $replacements_dictionary); 
}

if (isset($GLOBALS["FUNCTIONS_ARE_ENABLED"]) && $GLOBALS["FUNCTIONS_ARE_ENABLED"]) { // clean function descriptions (targets)

	CustomCleanTargets(true, true, true);
	/*
	if (isset($GLOBALS["FUNCTION_PARM_INSPECT"])) {
		$s_x = implode(",", $GLOBALS["FUNCTION_PARM_INSPECT"]);
		$s_y = str_ireplace($targets_to_clean_list, [','], $s_x);
		$GLOBALS["FUNCTION_PARM_INSPECT"] = explode(",",$s_y);
	}
	*/
	//if (isset($GLOBALS["FUNCTIONS"])) {
	//	$GLOBALS["FUNCTIONS"] = CustomFunctionsProcess($GLOBALS["FUNCTIONS"], $str_to_clean_list, $replacements_dictionary);
	//}
	
}

if (isset($GLOBALS["TTS_FFMPEG_FILTERS"]["tempo"])) {
	error_log($GLOBALS["TTS_FFMPEG_FILTERS"]["tempo"] . " - exec trace ");
	//='atempo=1.15';
}

//--------------------------------------------------------------

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
//$GLOBALS["contextDataFull"] = array_values($GLOBALS["contextDataFull"]);

$arr_prefix = [
    'role' => 'user', 
    'content' => "<DIALOGUE_HISTORY_and_RECENT_EVENTS># DIALOGUE HISTORY and RECENT EVENTS are recorded in the following messages: "
]; 

/*$arr_suffix = [
    'role' => 'assistant', 
    'content' => " </DIALOGUE_HISTORY_and_RECENT_EVENTS> "
];*/ 

$n_elements = array_unshift($GLOBALS["contextDataFull"], $arr_prefix);
//$GLOBALS["contextDataFull"][] = $arr_suffix;

$s_line = $GLOBALS["contextDataFull"][$n_elements-1]['content'];
$GLOBALS["contextDataFull"][$n_elements-1]['content'] = $s_line . "\n </DIALOGUE_HISTORY_and_RECENT_EVENTS> "; 
 
minai_stop_timer('contextProcessing');

// Update the system prompt (0th entry) with our optimized version
UpdateSystemPrompt();

require "/var/www/html/HerikaServer/ext/minai_plugin/command_prompt_custom.php";
minai_stop_timer('context_php');
// minai_stop_timer('Pre-LLM');
