<?php
// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}
require_once("logger.php");
define("MINAI_ACTOR_VALUE_CACHE", "minai_actor_value_cache");
require_once("db_utils.php");
require_once("importDataToDB.php");
require_once("mind_influence.php");

$GLOBALS[MINAI_ACTOR_VALUE_CACHE] = [];
$targetOverride = null;

// Get Value from the cache. $name/$key should be lowercase
Function GetActorValueCache($name, $key) {
    if (isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name])
        && isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key])
    ) {
        return $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key];
    }
    else {
        // no value in the cache
        return null;
    }
}

// Check if actor value has been cached. $name/$key should be lowercase
Function HasActorValueCache($name, $key=null) {
    if ($key === null) {
        return isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]);
    }
    return isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]) && isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key]);
}

Function BuildActorValueCache($name) {
    $name = strtolower($name);
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name] = [];

    $idPrefix = "_minai_{$name}//";
    $origLength = strlen($idPrefix);
    $idPrefix = $GLOBALS["db"]->escape($idPrefix);
    $query = "select * from conf_opts where LOWER(id) like LOWER('{$idPrefix}%')";
    $ret = $GLOBALS["db"]->fetchAll($query);
    // minai_log("info", "Building cache for $name");
    foreach ($ret as $row) {
        //do this instead of split // because $name could have // in it
        $key = substr(strtolower($row['id']), $origLength);
        $value = $row['value'];
        // minai_log("info", $name . ':: (' . $key . ') ' . $row['id'] . ' = ' . $row['value']);
        $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key] = $value;
    }
}

function DeleteLastPlayerInput() {
    return $GLOBALS["db"]->query("
        DELETE FROM eventlog 
        WHERE ctid IN (
            SELECT ctid FROM eventlog 
            WHERE type IN ('ginputtext', 'user_input') 
            ORDER BY ts DESC 
            LIMIT 2
        )
    ");
}

function CanVibrate($name) {
    return IsEnabled($name, "CanVibrate") && IsActionEnabled("MinaiGlobalVibrator");
}

function CanStartVibrator($name) {
    return CanVibrate($name) && !IsEnabled($name, "isVibratorActive") && !IsInFaction($name, "Vibrator Effect Faction");
}

function ActorCanOrgasm($name) {
    // For now, just check to see if they're not a badgirl/verybadgirl/punishment
    $state = GetMindInfluenceState($name);
    if (is_array($state)) {
        foreach ($state as $s) {
            if (in_array($s, ["badgirl", "verybadgirl", "punishment"])) {
                return false;
            }
        }
        return true;
    }
    return !in_array($state, ["badgirl", "verybadgirl", "punishment"]);
}
/**
 * Set an actor value in the database
 * 
 * @param string $name The actor name
 * @param string $key The key to set
 * @param string $value The value to set
 * @return bool True if successful
 */
function SetActorValue($name, $key, $value) {
    // Upsert new value
    return $GLOBALS['db']->upsertRowOnConflict(
        'conf_opts',
        array(
            'id' => "_minai_{$name}//{$key}",
            'value' => $value
        ),
        'id'
    );
}

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key, $preserveCase=false, $skipCache=false) {
    $db = $GLOBALS['db'];
    // minai_log("info", "Looking up $name: $key");
    If (!$preserveCase && !$skipCache) {
        $name = strtolower($name);
        $key = strtolower($key);

        If (!HasActorValueCache($name)) {
            BuildActorValueCache($name);
        }
        // minai_log("info", "Checking cache: $name, $key");
        $ret = GetActorValueCache($name, $key);
        return $ret === null ? "" : strtolower($ret);
    }

    // return strtolower("JobInnkeeper,Whiterun,,,,Bannered Mare Services,,Whiterun Bannered Mare Faction,,SLA TimeRate,sla_Arousal,sla_Exposure,slapp_HaveSeenBody,slapp_IsAnimatingWKidFaction,");
    $name = $db->escape($name);
    $query = "select * from conf_opts where LOWER(id)=LOWER('_minai_{$db->escape($name)}//{$db->escape($key)}')";
    if ($preserveCase) {
        $tmp = strtolower($name);
        $query = "select * from conf_opts where LOWER(id)='_minai_{$db->escape($tmp)}//{$db->escape($key)}'";
    }
    $ret = $GLOBALS["db"]->fetchAll($query);
    if (!$ret) {
        return "";
    }
    $ret = strtolower($ret[0]['value']);
    
    return $ret;
}

Function IsEnabled($name, $key) {
    $name = strtolower($GLOBALS["db"]->escape($name));
    return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}') and LOWER(value)=LOWER('TRUE')");
}

Function SetEnabled($name, $key, $enabled) {
    $name = strtolower($GLOBALS["db"]->escape($name));
    $key = strtolower($GLOBALS["db"]->escape($key));
    $value = $enabled ? 'TRUE' : 'FALSE';

    return $GLOBALS["db"]->query("UPDATE conf_opts SET value = '{$value}' WHERE LOWER(id) = LOWER('_minai_{$name}//{$key}')");
}


Function IsSexActive() {
    // if there is active scene thread involving current speaker
    return getScene($GLOBALS["HERIKA_NAME"]) || getScene($GLOBALS["PLAYER_NAME"]);
}

Function IsPlayer($name) {
    return (strtolower($GLOBALS["PLAYER_NAME"]) == strtolower($name));
}

$GLOBALS["GenericFuncRet"] =function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdSpankAss"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;

    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to complete the task. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};

Function IsModEnabled($mod) {
    return IsEnabled($GLOBALS['PLAYER_NAME'], "mod_{$mod}");
}

Function IsInFaction($name, $faction) {
    $faction = strtolower($faction);
    return str_contains(strtolower(GetActorValue($name, "AllFactions")), $faction);
}

Function HasKeyword($name, $keyword) {
    $keyword = strtolower($keyword);
    return str_contains(strtolower(GetActorValue($name, "AllKeywords")), $keyword);
}

Function IsConfigEnabled($configKey) {
    return IsEnabled($GLOBALS['PLAYER_NAME'], $configKey);
}

Function IsFollower($name) {
    /*return (
        IsInFaction($name, "Framework Follower Faction") ||
        IsInFaction($name, "Follower Role Faction") ||
        IsInFaction($name, "PotentialFollowerFaction") ||
        IsInFaction($name, "CurrentFollowerFaction") ||
        IsInFaction($name, "DLC1SeranaFaction") ||
        IsInFaction($name, "Potential Follower")
    );*/
    
    return IsInParty($name);
}

// Check if the specified actor is following (not follower)
Function IsFollowing($name) {
    return IsInFaction($name, "FollowingPlayerFaction");
}

Function IsInScene($name) {
    $value = strtolower(trim(GetActorValue($name, "scene")));
    return $value != null && $value != "" && $value != "none";
}

Function ShouldClearFollowerFunctions() {
    return ($GLOBALS["restrict_nonfollower_functions"] && !IsFollower($GLOBALS["HERIKA_NAME"]));
}

Function ShouldEnableSexFunctions($name) {
    // Check if sex mods are enabled
    if (!IsModEnabled("Sexlab") && !IsModEnabled("Ostim")) {
        return false;
    }

    // Check if AI sex is enabled for NPC-NPC interactions
    if (IsRadiant() && !IsEnabled("PLAYER", "enableAISex")) {
        return false;
    }

    // Get existing conditions
    $arousalOk = GetActorArousal($name) >= GetMinArousalForSex();
    $inCombat = IsEnabled($name, "inCombat");
    
    // Add new conditions
    $inScene = IsActorInSexScene($name);
    $transitionsAllowed = AreSexTransitionsAllowed($GLOBALS["PLAYER_NAME"]);
    
    // Block sex functions if:
    // - Actor is in a scene AND transitions aren't allowed
    // - Actor is in combat
    // - Actor's arousal is too low
    if ($inScene && !$transitionsAllowed) {
        return false;
    }
    
    return $arousalOk && !$inCombat;
}


Function ShouldEnableHarassFunctions($name) {
    $arousalThreshold = GetActorValue($GLOBALS['PLAYER_NAME'], "arousalForHarass");
    $arousal = GetActorValue($name, "arousal");
    if (empty($arousalThreshold) || empty($arousal)) {
        // If the config isn't set, default to enabled.
        // User may also not have arousal mod, so default to enabled
        return true;
    }
    return (intval($arousal) >= intval($arousalThreshold));
}

Function IsChildActor($name) {
    return str_contains(GetActorValue($name, "Race"), "child") || IsEnabled($name, "isChild");
}


Function IsMale($name) {
    return HasKeyword($name, "ActorSexMale");
}

Function IsFemale($name) {
    return HasKeyword($name, "ActorSexFemale");
}


Function IsActionEnabled($actionName) {
    $actionName = strtolower($GLOBALS["db"]->escape($actionName));
    return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('_minai_ACTION//{$actionName}') and LOWER(value)=LOWER('TRUE')");
}


Function RegisterAction($actionName) {
    $checkName = $actionName;
    if (str_contains(strtolower($actionName), 'stimulatewith') || str_contains(strtolower($actionName), 'teasewith')) {
        $checkName = 'MinaiGlobalVibrator';
    }
    if (IsActionEnabled($checkName)) {
        $GLOBALS["ENABLED_FUNCTIONS"][]=$actionName;
        minai_log("info", "Registering {$actionName}");
    }
    else {
        minai_log("info", "Not Registering {$actionName}");
    }
}

/**
 * Override the game request prompt with the provided text
 * 
 * @param string $promptText The prompt text to set
 * @return string The same prompt text (for convenience)
 */
Function OverrideGameRequestPrompt($promptText) {   
    // Update the database record for this request
    minai_log("info", "Overriding game player_request prompt to: {$promptText}");
    $db = $GLOBALS["db"];
    $gameRequest = $GLOBALS["gameRequest"];
    // Set the game request to the provided prompt text
    $GLOBALS["gameRequest"][3] = $promptText;
    return $promptText;
}

// Override player name
if (isset($GLOBALS["force_aiff_name_to_ingame_name"]) && $GLOBALS["force_aiff_name_to_ingame_name"]) {
    $playerName = GetActorValue("PLAYER", "playerName", true);
    if ($playerName) {
        $GLOBALS["PLAYER_NAME"] = $playerName;
    }
}


Function StoreRadiantActors($actor1, $actor2) {
    $db = $GLOBALS['db'];
    $id = "_minai_RADIANT//actor1";
    $db->upsertRowOnConflict(
        'conf_opts',
        array(
            'id' => $id,
            'value' => $actor1
        ),
        'id'
    );
    $id = "_minai_RADIANT//actor2";
    $db->upsertRowOnConflict(
        'conf_opts',
        array(
            'id' => $id,
            'value' => $actor2
        ),
        'id'
    );
    $id = "_minai_RADIANT//initial";
    $db->upsertRowOnConflict(
        'conf_opts',
        array(
            'id' => $id,
            'value' => 'TRUE'
        ),
        'id'
    );
    minai_log("info", "Storing Radiant Actors");
}

Function ClearRadiantActors() {
    // minai_log("info", "Clearing Radiant Actors");
    $db = $GLOBALS['db'];
    $id = "_minai_RADIANT//actor1";
    $db->delete("conf_opts", "id='{$id}'");
    $id = "_minai_RADIANT//actor2";
    $db->delete("conf_opts", "id='{$id}'");
    $db->delete("conf_opts", "id='_minai_RADIANT//initial'");
}

Function GetTargetActor() {
    global $targetOverride;
    if($targetOverride) {
        return $targetOverride;
    }
    $db = $GLOBALS['db'];
    $query = "select * from conf_opts where id='_minai_RADIANT//actor1'";
    $ret1 = $GLOBALS["db"]->fetchAll($query);
    if (!$ret1)
        return $GLOBALS["PLAYER_NAME"];
    $query = "select * from conf_opts where id='_minai_RADIANT//actor2'";
    $ret2 = $GLOBALS["db"]->fetchAll($query);
    if (!$ret2)
        return $GLOBALS["PLAYER_NAME"];
    if ($GLOBALS['HERIKA_NAME'] == $ret1[0]['value'])
        return $ret2[0]['value'];
    if ($GLOBALS['HERIKA_NAME'] == $ret2[0]['value'])
        return $ret1[0]['value'];
    return $GLOBALS["PLAYER_NAME"];
}

Function IsNewRadiantConversation() {
    return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where id='_minai_RADIANT//initial' and value='TRUE'");
}

Function GetLastInput() {
    $db = $GLOBALS['db'];
    $ret = $GLOBALS["db"]->fetchAll("select * from conf_opts where id='_minai_RADIANT//lastInput'");
    if (!$ret) {
        return 0;
    }
    return intval($ret[0]['value']);
}

Function IsRadiant() {
    return (GetTargetActor() != $GLOBALS["PLAYER_NAME"]);
}


// in case when we want to change target from radiant options and directly tell npc whom they need to talk to
function overrideTargetToTalk($name) {
    global $targetOverride;
    $targetOverride = $name;
}

function isPlayerInput() {
    return  in_array($GLOBALS["gameRequest"][0],["inputtext","inputtext_s","ginputtext","ginputtext_s","instruction","init"]);
}


$GLOBALS["target"] = GetTargetActor();
$GLOBALS["nearby"] = explode(",", GetActorValue("PLAYER", "nearbyActors"));
if (IsChildActor($GLOBALS['HERIKA_NAME']) || IsChildActor($GLOBALS["target"])) {
    $GLOBALS["disable_nsfw"] = true;
}

function GetCleanedMessage() {
    $cleanedMessage = $GLOBALS["gameRequest"][3];
    if (preg_match('/^.*?:\s*(.*)$/i', $cleanedMessage, $matches)) {
        $cleanedMessage = $matches[1];
        
        // Get player name for regex pattern
        $playerName = preg_quote($GLOBALS["PLAYER_NAME"], '/');
        
        // Clean location information (typically at the beginning before "PlayerName:")
        $cleanedMessage = preg_replace('/^(.*?Hold:.*?\))' . $playerName . ':/i', '', $cleanedMessage);
        
        // Clean any parenthetical context at the end
        $cleanedMessage = preg_replace('/\s*\([^)]*\)\s*$/', '', $cleanedMessage);
        
        // If just "PlayerName:" remains at the start, remove it too
        $cleanedMessage = preg_replace('/^' . $playerName . ':\s*/i', '', $cleanedMessage);
        
        // Trim any extra spaces
        $cleanedMessage = trim($cleanedMessage);
    } 
    return $cleanedMessage;
}

require_once("utils/guard_utils.php");
require_once("utils/misc_utils.php");
require_once("utils/sex_utils.php");
require_once("utils/llm_utils.php");
require_once("utils/profile_utils.php");
require_once("utils/variable_utils.php");
require_once("utils/equipment_utils.php");

/**
 * Get current party members from the database
 * 
 * @return array An array with two elements:
 *               - 'members': Array of party member data with name, level, race, etc.
 *               - 'names': Simple array of just the member names for easy access
 */
function GetCurrentPartyMembers() {
    // Initialize return structure
    $result = [
        'members' => [],
        'names' => []
    ];
    
    // Check if CurrentParty data exists in the database
    $query = "SELECT value FROM conf_opts WHERE id='CurrentParty'";
    $dbResult = $GLOBALS["db"]->fetchAll($query);
    
    if (!$dbResult || empty($dbResult[0]['value'])) {
        return $result;
    }
    
    $rawData = $dbResult[0]['value'];
    
    // Remove potential trailing comma
    $rawData = rtrim($rawData, ',');
    
    // Wrap with array brackets to make it valid JSON
    $rawData = '[' . $rawData . ']';
    
    // Parse the JSON data
    $partyData = json_decode($rawData, true);
    
    // If parsing failed, return empty result
    if (!is_array($partyData)) {
        return $result;
    }
    
    // Store member data and names
    foreach ($partyData as $member) {
        if (isset($member['name'])) {
            $result['members'][] = $member;
            $result['names'][] = $member['name'];
        }
    }
    
    return $result;
}

/**
 * Check if a character is in the player's current party
 *
 * @param string $characterName The name of the character to check
 * @return bool True if the character is in the party, false otherwise
 */
function IsInParty($characterName) {
    $partyInfo = GetCurrentPartyMembers();
    
    // Check if character name exists in party member names (case insensitive)
    foreach ($partyInfo['names'] as $memberName) {
        if (strtolower($memberName) === strtolower($characterName)) {
            return true;
        }
    }
    
    return false;
}


function GetRecentContext($actor, $contextMessages) {
    if (!isset($GLOBALS["gameRequest"])) {
        // Dummy values for the roleplay builder and preview pages to mute warnings
        $GLOBALS["gameRequest"] = ["", 0, 0];
    }
    return DataLastDataExpandedFor($actor, $contextMessages * -1);
}

/**
 * Gets the current location information from recent context
 * 
 * @param string $actor The actor name to check context for
 * @return array Location data containing current, hold, full location string, date information, and buildings
 */
function GetCurrentLocationContext($actor) {
    global $db;
    
    // Query to fetch recent context data with focus on location information
    $query = "SELECT location 
              FROM eventlog 
              WHERE location IS NOT NULL AND location != ''
              AND type = 'infoloc'
              ORDER BY gamets DESC, ts DESC, rowid DESC 
              LIMIT 3";
    
    $results = $db->fetchAll($query);
    
    $locationData = [
        'current' => '',
        'hold' => '',
        'full' => '',
        'date' => '',
        'buildings' => []
    ];
    
    // Process results to extract location information
    foreach ($results as $row) {
        $locationString = $row["location"];
        
        // Match location with optional "Context" prefix, optional "new" keyword, and optional "outdoors" as part of location
        preg_match('/Context\s*(new\s*)?location:\s*([^$]+?(?:\s*,\s*outdoors)?(?=\s*[,$]))/', $locationString, $locationMatch);
        
        // Match hold information if present
        preg_match('/Hold:\s*([^$,\)]+)/', $locationString, $holdMatch);
        
        // Match date information in format like "Current Date in Skyrim World: Turdas, 15:50, 3rd Day of Winter"
        preg_match('/Current Date in Skyrim World:\s*([^$]+)/', $locationString, $dateMatch);
        
        // Match buildings/passages information
        preg_match('/buildings to go:(.+), Current/', $locationString, $buildingsMatch);
        
        // Process location if we found it
        if (isset($locationMatch[2])) {
            $location = trim($locationMatch[2]);
            $locationData['current'] = $location;
            
            // Process hold if present
            if (isset($holdMatch[1])) {
                $hold = trim($holdMatch[1]);
                $locationData['hold'] = $hold;
                $locationData['full'] = "$location, hold: $hold";
            } else {
                // If no hold, just use the location as the full string
                $locationData['full'] = $location;
            }
            
            // Add date information if available
            if (isset($dateMatch[1])) {
                $locationData['date'] = rtrim(trim($dateMatch[1]), ')');
            }
            
            // Add buildings information if available
            if (isset($buildingsMatch[1])) {
                $buildingsString = trim($buildingsMatch[1]);
                // Parse the buildings string into an array
                $buildingsList = explode(',', $buildingsString);
                $buildings = [];
                
                foreach ($buildingsList as $building) {
                    $building = trim($building);
                    if (!empty($building)) {
                        // Extract the door/passage name and destination
                        if (preg_match('/^([^(]+)\(([^)]+)\)$/', $building, $parts)) {
                            $buildings[] = [
                                'name' => trim($parts[1]),
                                'destination' => trim($parts[2])
                            ];
                        } else {
                            // If it doesn't match the pattern, just add the full string
                            $buildings[] = [
                                'name' => $building,
                                'destination' => ''
                            ];
                        }
                    }
                }
                
                $locationData['buildings'] = $buildings;
            }
            
            // We found valid location data, return it
            return $locationData;
        }
    }
    
    return $locationData;
}
