<?php
/**
 * MinAI Pre-request Processing
 * 
 * This file is included at the beginning of each request to perform initialization and preparation.
 */

// Start metrics for this entry point
require_once("utils/metrics_util.php");
minai_start_timer('prerequest_php', 'MinAI');

// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}
require_once("config.php");
require_once("util.php");
require_once("contextbuilders.php");
require_once("prompts/info_prompts.php");
// Set speaker, but ensure narrator gets proper voice assignment
if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $GLOBALS["speaker"] = "The Narrator";
    // Force narrator voice type instead of inheriting from previous speaker
    if (isset($GLOBALS['devious_narrator_eldritch_voice'])) {
        $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $GLOBALS['devious_narrator_eldritch_voice'];
        $GLOBALS["TTS"]["MELOTTS"]["voiceid"] = $GLOBALS['devious_narrator_eldritch_voice'];
    }
} else {
    $GLOBALS["speaker"] = $GLOBALS["HERIKA_NAME"];
}
$GLOBALS["minai_processing_input"] = false;

// Configure metrics collection with default values if not set in config
if (!isset($GLOBALS['minai_metrics_enabled'])) {
    $GLOBALS['minai_metrics_enabled'] = true;
}
if (!isset($GLOBALS['minai_metrics_sampling_rate'])) {
    $GLOBALS['minai_metrics_sampling_rate'] = 0.1; // Default to 10% sampling
}
if (!isset($GLOBALS['minai_metrics_file'])) {
    $GLOBALS['minai_metrics_file'] = "/var/www/html/HerikaServer/log/minai_metrics.jsonl";
}


Function SetRadiance($rechat_h, $rechat_p) {
    // minai_log("info", "Setting Rechat Parameters (h={$rechat_h}, p={$rechat_p})");
    $GLOBALS["RECHAT_H"] = $rechat_h;
    $GLOBALS["RECHAT_P"] = $rechat_p;
}

if (IsRadiant()) {
    SetRadiance(0, 0); // Disable rechat during radiant conversations, as this is handled by MinAI's controller in-game
}

SetNarratorProfile();

// If talking to the narrator, force it to respond.
if (IsEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator") && isPlayerInput() ) {
    minai_log("info", "Forcing herika_name to the narrator: Is talking to narrator");
    SetEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator", false);
    $GLOBALS["HERIKA_NAME"] = "The Narrator";
    $GLOBALS["minai_processing_input"] = true;
    $GLOBALS["using_self_narrator"] = true;
    
    SetNarratorProfile();
    
    if ($GLOBALS["self_narrator"]) {
        $pronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
        OverrideGameRequestPrompt($GLOBALS["PLAYER_NAME"] . " thinks to " . $pronouns["object"] . "self: " . GetCleanedMessage());
    }
}

// If using dungeon master, set appropriate state
if (IsEnabled($GLOBALS["PLAYER_NAME"], "isDungeonMaster") && isPlayerInput()) {
    SetEnabled($GLOBALS["PLAYER_NAME"], "isDungeonMaster", false);
    $GLOBALS["minai_processing_input"] = true;
    $GLOBALS["gameRequest"][0] = "minai_dungeon_master";
    $GLOBALS["gameRequest"][3] = str_replace("{$GLOBALS["PLAYER_NAME"]}:", "The Narrator: ", $GLOBALS["gameRequest"][3]);
    minai_log("info", "Processing dungeon master input: {$GLOBALS["gameRequest"][3]}");
}

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    minai_log("info", "Forcing herika_name to the narrator: Is singing");
    // $GLOBALS["HERIKA_NAME"] = "The Narrator";
    // SetNarratorProfile();
}


require_once("functions/deviousnarrator.php");
if (ShouldUseDeviousNarrator()) {
    SetDeviousNarrator();
}


Function GetConfigPath($npcName) {
    // If use symlink, php code is actually in repo folder but included in wsl php server
    // with just dirname((__FILE__)) it was getting directory of repo not php server 
    $path = getcwd().DIRECTORY_SEPARATOR;
    $newConfFile=md5($npcName);
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

if (isset($GLOBALS["realnames_support"]) && $GLOBALS["realnames_support"]) {
    $matches = [];
    if (preg_match('/^(.+?) \[(.+)\]$/', $GLOBALS["HERIKA_NAME"], $matches)) {
        $fullPath = GetConfigPath($matches[0]);
        $badPers = "Roleplay as {$matches[0]}";
        if (!file_exists($fullPath) || $GLOBALS["HERIKA_PERS"] == $badPers) {
            $npcName = $matches[2];
            $codename=addslashes(strtr(strtolower(trim($npcName)),[" "=>"_","'"=>"+"]));
            minai_log("info", "Detected generic NPC, seeding profile. Original: {$matches[0]}, new: {$matches[2]}, codename: $codename");
            $npcTemlate=$GLOBALS["db"]->fetchAll("SELECT npc_pers FROM npc_templates where npc_name='$codename'");
            $personality = 'Roleplay as '.addslashes(trim($matches[1])) . ", who is a " . addslashes(trim($matches[2]));;
            
            // Check if we got results and they have the expected structure
            if (!empty($npcTemlate) && isset($npcTemlate[0]['npc_pers'])) {
                $personality = addslashes(trim($npcTemlate[0]["npc_pers"]));
            } else {
                $npcTemlate=$GLOBALS["db"]->fetchAll("SELECT npc_pers FROM npc_templates_custom where npc_name='$codename'");
                if (!empty($npcTemlate) && isset($npcTemlate[0]['npc_pers'])) {
                    $personality = addslashes(trim($npcTemlate[0]["npc_pers"]));
                }
            }

            // Swap out the generic name for the new name
            $personality = str_replace("Roleplay as {$matches[2]}", "Roleplay as {$matches[0]}", $personality);
            minai_log("info", "Initializing generic NPC {$matches[0]} with personality: $personality");
            createProfile($matches[0],
                          ["HERIKA_PERS" => $personality],
                          true
            );
            global $HERIKA_PERS;
            include($fullPath);
                
        }
    }
}

$GLOBALS["LLM_RETRY_FNCT"] = function() {
    if (isset($GLOBALS['use_llm_fallback']) && !$GLOBALS['use_llm_fallback']) {
        minai_log("info", "LLM fallback is disabled - skipping retry");
        return false;
    }
    
    minai_log("info", "Retrying LLM...");
    SetLLMFallbackProfile();
    $outputWasValid = call_llm();   
    if (!$outputWasValid) {
        minai_log("info", "Warning: LLM returned invalid output after retry.");
    }
    return $outputWasValid;
};

$GLOBALS["VALIDATE_LLM_OUTPUT_FNCT"] = function($output) {
    return validateLLMResponse($output);
};


// Helper function to get item details by name
function getItemByName($itemName) {
    $db = $GLOBALS['db'];
    
    try {
        $escapedName = $db->escape($itemName);
        // First try exact match
        $query = "SELECT * FROM minai_items WHERE LOWER(name) = LOWER('{$escapedName}')";
        $result = $db->fetchAll($query);
        
        // If not found, try partial match
        if (count($result) == 0) {
            $query = "SELECT * FROM minai_items WHERE LOWER(name) LIKE LOWER('%{$escapedName}%') ORDER BY LENGTH(name) ASC";
            $result = $db->fetchAll($query);
        }
        
        // If we found multiple items with the same name, sort by form ID
        if (count($result) > 1) {
            minai_log("info", "Found multiple items with name '{$itemName}', sorting by form ID");
            
            // Sort by form ID as hex values
            usort($result, function($a, $b) {
                // Strip the '0x' prefix and convert hex to decimal for comparison
                $valueA = hexdec(ltrim($a['item_id'], '0x'));
                $valueB = hexdec(ltrim($b['item_id'], '0x'));
                
                // Higher hex values should come last (so we'll select them with end())
                return $valueA <=> $valueB;
            });
            
            // Log what we're selecting
            $selected = end($result);
            minai_log("info", "Selected item with form ID '{$selected['item_id']}' from multiple matches");
            
            return $selected;
        }
        
        return count($result) > 0 ? $result[0] : null;
    } catch (Exception $e) {
        minai_log("error", "Error in getItemByName: " . $e->getMessage());
        return null;
    }
}

$GLOBALS["action_post_process_fnct"] = function($actions) {
    minai_log("info", "Processing actions: ".json_encode($actions));
    // Process each action
    if (isset($actions)) {
        foreach ($actions as $key => $action) {
            // Check if this is one of our item commands
            if (strpos($action, 'ExtCmdGiveItem@') !== false || 
                strpos($action, 'ExtCmdTakeItem@') !== false || 
                strpos($action, 'ExtCmdTradeItem@') !== false) {
                
                minai_log("info", "Processing item command: " . $action);
                
                // Extract parts: format is like "Agdaz|command|ExtCmdGiveItem@Min\r\n"
                $parts = explode('|', $action);
                if (count($parts) >= 3) {
                    $actor = trim($parts[0]);
                    $cmdType = trim($parts[1]);
                    
                    // Remove any trailing \r\n from the command part
                    $commandPart = rtrim($parts[2], "\r\n");
                    
                    // Split command part into segments by @ symbol
                    $cmdSegments = explode('@', $commandPart);
                    
                    if (count($cmdSegments) >= 2) {
                        $cmd = $cmdSegments[0];
                        $target = $cmdSegments[1];
                        
                        // Check if the target contains parameters (format: Target:Count or more complex)
                        $paramPos = strpos($target, ':');
                        if ($paramPos !== false) {
                            // For commands like ExtCmdTakeItem@Septim:5, extract Septim as itemName and 5 as count
                            $parameter = $target;
                            
                            // Process GiveItem and TakeItem commands
                            if ($cmd === 'ExtCmdGiveItem' || $cmd === 'ExtCmdTakeItem') {
                                $paramParts = explode(':', $parameter);
                                $itemName = trim($paramParts[0]);
                                $count = isset($paramParts[1]) ? intval(trim($paramParts[1])) : 1;
                                
                                $itemInfo = getItemByName($itemName);
                                if ($itemInfo) {
                                    $newParameter = "{$itemInfo['item_id']}:{$itemInfo['file_name']}:{$count}";
                                    minai_log("info", "Converted '{$parameter}' to '{$newParameter}'");
                                    
                                    // Rebuild the action string
                                    $actions[$key] = "{$actor}|{$cmdType}|{$cmd}@{$newParameter}\r\n";
                                } else {
                                    minai_log("warn", "Item not found in database: {$itemName}");
                                }
                            }
                            // Process TradeItem command
                            elseif ($cmd === 'ExtCmdTradeItem') {
                                $paramParts = explode(':', $parameter);
                                
                                if (count($paramParts) == 2) {
                                    // Format: GiveItem:TakeItem
                                    $giveItemName = trim($paramParts[0]);
                                    $takeItemName = trim($paramParts[1]);
                                    $giveCount = 1;
                                    $takeCount = 1;
                                    
                                    $giveItemInfo = getItemByName($giveItemName);
                                    $takeItemInfo = getItemByName($takeItemName);
                                    
                                    if ($giveItemInfo && $takeItemInfo) {
                                        $newParameter = "{$giveItemInfo['item_id']}:{$giveItemInfo['file_name']}:{$giveCount}:{$takeItemInfo['item_id']}:{$takeItemInfo['file_name']}:{$takeCount}";
                                        minai_log("info", "Converted '{$parameter}' to '{$newParameter}'");
                                        
                                        // Rebuild the action string
                                        $actions[$key] = "{$actor}|{$cmdType}|{$cmd}@{$newParameter}\r\n";
                                    } else {
                                        if (!$giveItemInfo) minai_log("warn", "Give item not found in database: {$giveItemName}");
                                        if (!$takeItemInfo) minai_log("warn", "Take item not found in database: {$takeItemName}");
                                    }
                                } 
                                elseif (count($paramParts) == 4) {
                                    // Format: GiveItem:GiveCount:TakeItem:TakeCount
                                    $giveItemName = trim($paramParts[0]);
                                    $giveCount = intval(trim($paramParts[1]));
                                    $takeItemName = trim($paramParts[2]);
                                    $takeCount = intval(trim($paramParts[3]));
                                    
                                    $giveItemInfo = getItemByName($giveItemName);
                                    $takeItemInfo = getItemByName($takeItemName);
                                    
                                    if ($giveItemInfo && $takeItemInfo) {
                                        $newParameter = "{$giveItemInfo['item_id']}:{$giveItemInfo['file_name']}:{$giveCount}:{$takeItemInfo['item_id']}:{$takeItemInfo['file_name']}:{$takeCount}";
                                        minai_log("info", "Converted '{$parameter}' to '{$newParameter}'");
                                        
                                        // Rebuild the action string
                                        $actions[$key] = "{$actor}|{$cmdType}|{$cmd}@{$newParameter}\r\n";
                                    } else {
                                        if (!$giveItemInfo) minai_log("warn", "Give item not found in database: {$giveItemName}");
                                        if (!$takeItemInfo) minai_log("warn", "Take item not found in database: {$takeItemName}");
                                    }
                                } else {
                                    minai_log("warn", "Invalid parameter format for TradeItem: {$parameter}");
                                }
                            }
                        } else {
                            minai_log("info", "Command has no parameters: {$action}");
                        }
                    }
                }
            }
        }
    } else {
        minai_log("warn", "Actions not defined! ");
    }
    
    minai_log("info", "Processed actions: ".json_encode($actions));
    return $actions;
};

// Only create the fallback config if the feature is enabled
if (isset($GLOBALS['use_llm_fallback']) && $GLOBALS['use_llm_fallback']) {
    CreateFallbackConfig();
}

// Clean up dungeon master input
if (IsEnabled($GLOBALS["PLAYER_NAME"], "isDungeonMaster")) {
    SetEnabled($GLOBALS["PLAYER_NAME"], "isDungeonMaster", false);
}
// Incompatible with new context system
$GLOBALS["ADD_PLAYER_BIOS"]  = false;

// If the name matches the player's name, they are talking to the narrator
// This will cause problems if the player's name is the same as the npc's name, though.
// CHIM itself is sending the player's name as herika name sometimes. Need to debug / fix that.
// This is a temporary fix / workaround.
if (strtolower($GLOBALS["HERIKA_NAME"]) == strtolower($GLOBALS["PLAYER_NAME"])) {
    $GLOBALS["HERIKA_NAME"] = "The Narrator";
}

// If the name is "player", set name to "The Narrator"
if (strtolower($GLOBALS["HERIKA_NAME"]) == "player") {
    $GLOBALS["HERIKA_NAME"] = "The Narrator";
    SetNarratorProfile();
}


// Entries will be removed from the context history while cleanining up slop. 
// To avoid having too few, we will overfetch the context history and later reduce it.
if (isset($GLOBALS["enable_prompt_slop_cleanup"]) && $GLOBALS["enable_prompt_slop_cleanup"]) {
    // Store original context history setting
    $nDataForContext = intval(isset($GLOBALS["CONTEXT_HISTORY"]) ? $GLOBALS["CONTEXT_HISTORY"] : 50);
    $GLOBALS["ORIGINAL_CONTEXT_HISTORY"] = $nDataForContext;

    // Triple the original context history setting
    $GLOBALS["CONTEXT_HISTORY"] = $nDataForContext * 3;
    // error_log("DEBUG: Context history set to " . $GLOBALS["CONTEXT_HISTORY"]);
}

minai_stop_timer('prerequest_php');
