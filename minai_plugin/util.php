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
if (!isset($GLOBALS["db"]))
	$GLOBALS["db"] = new sql();


Function SetRadiance($rechat_h, $rechat_p) {

    // minai_log("info", "Setting Rechat Parameters (h={$rechat_h}, p={$rechat_p})");
    $GLOBALS["RECHAT_H"] = $rechat_h;
    $GLOBALS["RECHAT_P"] = $rechat_p;
}

Function CheckRechat($rechat_h, $rechat_p) {

    // minai_log("info", "CheckRadiance: Setting Rechat Parameters (h={$rechat_h}, p={$rechat_p})");
    
    if (!isset($GLOBALS["RECHAT_H"]))
		$GLOBALS["RECHAT_H"] = $rechat_h;
	elseif (intval($GLOBALS["RECHAT_H"]) < 1)
		$GLOBALS["RECHAT_H"] = $rechat_h;

    if (!isset($GLOBALS["RECHAT_P"]))
		$GLOBALS["RECHAT_P"] = $rechat_p;
	elseif (intval($GLOBALS["RECHAT_P"]) < 1)
		$GLOBALS["RECHAT_P"] = $rechat_p;
        
    //error_log(" CheckRadiance: Setting Rechat Parameters (h={$GLOBALS["RECHAT_H"]}, p={$GLOBALS["RECHAT_P"]}) - exec trace"); // debug    
}

function array_unique_caseinsensitive($arr_input) {
    return array_intersect_key(
        $arr_input,
        array_unique(array_map('strtolower', $arr_input))
    );
}

function array_unique_multi(array $array_in, string $unq_key): array 
{
    $arr_unique = [];
    foreach ($array_in as $v) {
        if (!array_key_exists($v[$unq_key], $arr_unique)) {
            $arr_unique[$v[$unq_key]] = $v;
        }
    }
    return array_values($arr_unique);
}

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
    $query = "select * from conf_opts where (id ilike '{$idPrefix}%' )";
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

if (!function_exists('set_conf_opts_value')) {
function set_conf_opts_value($key, $value) {
    // Upsert new value
    $l_key = strtolower($key);
    return $GLOBALS['db']->upsertRowOnConflict(
        'conf_opts',
        array(
            'id' => "{$l_key}",
            'value' => "{$value}"
        ),
        'id'
    );
}
}

if (!function_exists('get_conf_opts_value')) {
function get_conf_opts_value($key, $preserveCase=false) {
    $s_res = "";
    $db = $GLOBALS['db'];
    $l_key = strtolower($key);
    $e_key = $db->escape($l_key);    
    
    if (strlen($key) > 0) {

        $query = "SELECT * FROM conf_opts WHERE (LOWER(id)='{$e_key}') LIMIT 1 ";

        $ret = $GLOBALS["db"]->fetchAll($query);
        if ($ret) {
            if ($preserveCase)
                $s_res = ($ret[0]['value'] ?? '');
            else
                $s_res = strtolower($ret[0]['value'] ?? '');        
        }
    }
    return $s_res;
}
}

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key, $preserveCase=false, $skipCache=false, $checkOnlyInCache=false) {
    $db = $GLOBALS['db'];
    // minai_log("info", "Looking up $name: $key");
    $l_key = strtolower($key);
    $l_name = strtolower($name);

    if (!$skipCache) {
        if (!HasActorValueCache($l_name)) {
            BuildActorValueCache($l_name);
        }
        // minai_log("info", "Checking cache: $name, $key");
        $ret = (GetActorValueCache($l_name, $l_key) ?? "");
        if ($checkOnlyInCache) {
            if (!$preserveCase)
                $ret = strtolower($ret);
            return $ret;
        } else {
            if (strlen($ret) > 0) {
                if (!$preserveCase)
                    $ret = strtolower($ret);
                return $ret;
            }
        }
    }

    // return strtolower("JobInnkeeper,Whiterun,,,,Bannered Mare Services,,Whiterun Bannered Mare Faction,,SLA TimeRate,sla_Arousal,sla_Exposure,slapp_HaveSeenBody,slapp_IsAnimatingWKidFaction,");
    $e_name = $db->escape($l_name);
    $e_key = $db->escape($l_key);
    $query = "SELECT * FROM conf_opts WHERE (LOWER(id)=LOWER('_minai_{$e_name}//{$e_key}'))";

    $ret = $GLOBALS["db"]->fetchAll($query);
    if (!$ret) {
        return "";
    }

    if ($preserveCase)
        $ret = ($ret[0]['value'] ?? '');
    else
        $ret = strtolower($ret[0]['value'] ?? '');
    
    return $ret;
}

// Cache for IsEnabled checks to reduce database queries
if (!isset($GLOBALS["minai_is_enabled_cache"])) {
    $GLOBALS["minai_is_enabled_cache"] = [];
}

Function IsEnabled($name, $key) {
    // Create unique cache key
    $name = strtolower($name);
    $key = strtolower($key);
    $cacheKey = "{$name}|{$key}";
    
    // Check cache first
    if (isset($GLOBALS["minai_is_enabled_cache"][$cacheKey])) {
        return $GLOBALS["minai_is_enabled_cache"][$cacheKey];
    }
    
    // Not in cache, try actor value cache
    if (!HasActorValueCache($name)) {
        BuildActorValueCache($name);
    }
    
    // Check if value exists in actor value cache
    $value = GetActorValueCache($name, $key);
    $isEnabled = ($value !== null && strtolower($value) === 'true');
    
    // Cache the result
    $GLOBALS["minai_is_enabled_cache"][$cacheKey] = $isEnabled;
    
    return $isEnabled;
}

Function SetEnabled($name, $key, $enabled) {
    $name = strtolower($GLOBALS["db"]->escape($name));
    $key = strtolower($GLOBALS["db"]->escape($key));
    $value = $enabled ? 'TRUE' : 'FALSE';

    // Update cache
    $cacheKey = "{$name}|{$key}";
    $GLOBALS["minai_is_enabled_cache"][$cacheKey] = $enabled;

    return $GLOBALS["db"]->query("UPDATE conf_opts SET value = '{$value}' WHERE LOWER(id) = LOWER('_minai_{$name}//{$key}')");
}

// Cache for faction membership checks
if (!isset($GLOBALS["minai_faction_cache"])) {
    $GLOBALS["minai_faction_cache"] = [];
}

Function IsInFaction($name, $faction) {
    $name = strtolower($name);
    $faction = strtolower($faction);
    
    // Check cache first
    if (!isset($GLOBALS["minai_faction_cache"][$name])) {
        // Cache all factions for this actor at once
        $allFactions = strtolower(GetActorValue($name, "AllFactions"));
        $GLOBALS["minai_faction_cache"][$name] = $allFactions;
    }
    
    return str_contains($GLOBALS["minai_faction_cache"][$name], $faction);
}

function IsCreature($name) {
    $b_res = false;    
    
    $b_res = IsInFaction($name,"Creature Faction");
    if (!$b_res) { // check also races
        $charKey = strtolower($name);
        if (isset($actorValues[$charKey]['race']) && (!empty($actorValues[$charKey]['race']))) {
            $s_race = strtolower($actorValues[$charKey]['race']);        
            $b_res = (stripos('dog,horse,cat,cow,goat,bear,snowcat,chicken,wolf', $s_race) !== false); 
        }
    }

    return $b_res;
}                    
                    
// Cache for keyword checks
if (!isset($GLOBALS["minai_keyword_cache"])) {
    $GLOBALS["minai_keyword_cache"] = [];
}

Function HasKeyword($name, $keyword) {
    $name = strtolower($name);
    $keyword = strtolower($keyword);
    
    // Check cache first
    if (!isset($GLOBALS["minai_keyword_cache"][$name])) {
        // Cache all keywords for this actor at once
        $allKeywords = strtolower(GetActorValue($name, "AllKeywords"));
        $GLOBALS["minai_keyword_cache"][$name] = $allKeywords;
    }
    
    return str_contains($GLOBALS["minai_keyword_cache"][$name], $keyword);
}

// Cache for follower status
if (!isset($GLOBALS["minai_follower_cache"])) {
    $GLOBALS["minai_follower_cache"] = [];
}

Function IsFollower($name) {
    $name = strtolower($name);
    
    // Check cache first
    if (isset($GLOBALS["minai_follower_cache"][$name])) {
        return $GLOBALS["minai_follower_cache"][$name];
    }
    
    // Not in cache, check party membership
    $result = IsInParty($name);
    
    // Cache the result
    $GLOBALS["minai_follower_cache"][$name] = $result;
    
    return $result;
}

// Cache for following status
if (!isset($GLOBALS["minai_following_cache"])) {
    $GLOBALS["minai_following_cache"] = [];
}

// Check if the specified actor is following (not follower)
Function IsFollowing($name) {
    $name = strtolower($name);
    
    // Check cache first
    if (isset($GLOBALS["minai_following_cache"][$name])) {
        return $GLOBALS["minai_following_cache"][$name];
    }
    
    // Not in cache, check faction membership
    $result = IsInFaction($name, "FollowingPlayerFaction");
    
    // Cache the result
    $GLOBALS["minai_following_cache"][$name] = $result;
    
    return $result;
}

// Batch load factions for multiple NPCs at once to reduce DB queries
function PreloadFactions($actorNames) {
    if (empty($actorNames)) {
        return;
    }
    
    // Initialize faction cache if needed
    if (!isset($GLOBALS["minai_faction_cache"])) {
        $GLOBALS["minai_faction_cache"] = [];
    }
    
    $missingActors = [];
    foreach ($actorNames as $name) {
        $lowerName = strtolower($name);
        if (!isset($GLOBALS["minai_faction_cache"][$lowerName])) {
            $missingActors[] = $lowerName;
        }
    }
    
    if (empty($missingActors)) {
        return; // All actors already cached
    }
    
    // Use BatchGetActorValues to get all factions in one query
    $result = BatchGetActorValues($missingActors, ["AllFactions"]);
    
    foreach ($result as $actor => $values) {
        if (isset($values["allfactions"])) {
            $GLOBALS["minai_faction_cache"][$actor] = strtolower($values["allfactions"]);
        } else {
            // Cache empty string to avoid repeated lookups
            $GLOBALS["minai_faction_cache"][$actor] = "";
        }
    }
}

// Batch load enabled flags for multiple NPCs at once
function PreloadEnabledFlags($actorNames, $flagNames) {
    if (empty($actorNames) || empty($flagNames)) {
        return;
    }
    
    // Initialize enabled flags cache if needed
    if (!isset($GLOBALS["minai_is_enabled_cache"])) {
        $GLOBALS["minai_is_enabled_cache"] = [];
    }
    
    // Use BatchIsEnabled to get all flags in one query
    $results = BatchIsEnabled($actorNames, $flagNames);
    
    // Update cache with results
    foreach ($results as $actor => $flags) {
        foreach ($flags as $flag => $isEnabled) {
            $cacheKey = "{$actor}|{$flag}";
            $GLOBALS["minai_is_enabled_cache"][$cacheKey] = $isEnabled;
        }
    }
}

// Function to preload multiple actor value types at once
function PreloadActorValues($actorNames, $valueTypes) {
    if (empty($actorNames) || empty($valueTypes)) {
        return;
    }
    
    // Initialize actor value cache for missing actors
    foreach ($actorNames as $actor) {
        $actor = strtolower($actor);
        if (!HasActorValueCache($actor)) {
            $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$actor] = [];
        }
    }
    
    // Use BatchGetActorValues to get all values in one query
    $results = BatchGetActorValues($actorNames, $valueTypes);
    
    // Update cache with results
    foreach ($results as $actor => $values) {
        foreach ($values as $key => $value) {
            if (!empty($value)) {
                $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$actor][$key] = $value;
            }
        }
    }
}

// Function to preload all commonly accessed data for the current context
function PreloadCommonActorData() {
    // Get the key actors in the current context
    $relevantActors = [$GLOBALS["HERIKA_NAME"], $GLOBALS["PLAYER_NAME"], $GLOBALS["target"]];
    
    // Add nearby actors if available
    if (isset($GLOBALS["nearby"]) && is_array($GLOBALS["nearby"])) {
        $relevantActors = array_merge($relevantActors, $GLOBALS["nearby"]);
    }
    
    // Remove duplicates and empty values
    $relevantActors = array_filter(array_unique_caseinsensitive($relevantActors));
    
    // Common flag checks
    $commonFlags = [
        "inCombat", 
        "isChild", 
        "CanVibrate", 
        "isVibratorActive",
		"isNaked",
        "enableAISex"
    ];
    
    // Common actor values to preload
    $commonValues = [
        "AllFactions", 
        "AllKeywords", 
        "Race", 
        "arousal", 
        "Scene",
        "relationshipRank",
        "playerName"
    ];
    
    // Preload all data in batch
    minai_start_timer("preload_factions", "preload_actor_data");
    PreloadFactions($relevantActors);
    minai_stop_timer("preload_factions");
    minai_start_timer("preload_enabled_flags", "preload_actor_data");
    PreloadEnabledFlags($relevantActors, $commonFlags);
    minai_stop_timer("preload_enabled_flags");
    minai_start_timer("preload_actor_values", "preload_actor_data");
    PreloadActorValues($relevantActors, $commonValues);
    minai_stop_timer("preload_actor_values");
}

Function IsSexActive() {
    // if there is active scene thread involving current speaker or player
    return getScene($GLOBALS["HERIKA_NAME"]) || getScene($GLOBALS["PLAYER_NAME"]);
}

Function IsSexActiveSpeaker() {
    // if there is active scene thread involving current speaker
    $scene = getScene($GLOBALS["HERIKA_NAME"]);
    return (isset($scene) && (!empty($scene)));
}

Function IsInScene($name) {
    //$value = strtolower(trim(GetActorValue($name, "Scene")));
    //return $value != null && $value != "" && $value != "none";
    $scene = getScene($name);
    return (isset($scene) && (!empty($scene)));
}

Function IsPlayer($name) {
    return (strtolower($GLOBALS["PLAYER_NAME"]) == strtolower($name));
}

$GLOBALS["GenericFuncRet"] =function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdSpankAss"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=512; // was 48   // We can overwrite anything here using $GLOBALS;

    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to complete the task. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};

Function IsModEnabled($mod) {
    return IsEnabled($GLOBALS['PLAYER_NAME'], "mod_{$mod}");
}

Function IsConfigEnabled($configKey) {
    return IsEnabled($GLOBALS['PLAYER_NAME'], $configKey);
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
    if ($inScene && (!$transitionsAllowed)) {
        return false;
    }
    
    $b_enable_sex = ($arousalOk && (!$inCombat));
    //error_log("->ShouldEnableSexFunctions: enable=$b_enable_sex arousal=$arousalOk combat=$inCombat  ");
    return $b_enable_sex;
}


Function ShouldEnableHarassFunctions($name) {
    $arousalThreshold = GetActorValue($GLOBALS['PLAYER_NAME'], "arousalForHarass");
    $arousal = GetActorValue($name, "arousal");
    if (empty($arousalThreshold) || empty($arousal)) {
        // If the config isn't set, default to enabled.
        // User may also not have arousal mod, so default to enabled
        return true;
    }
    $b_res = (intval($arousal) >= intval($arousalThreshold));
    //error_log(" - ShouldEnableHarassFunctions: $name $b_res $arousal >= $arousalThreshold - exec trace");
    return $b_res;
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

function GetGender($name) {
        
    $s_res = GetActorValue($name, "gender");
    if (strlen($s_res) < 1) {
        if (IsFemale($name)) {
            $s_res = "female";
        } else {
            if (IsMale($name))
                $s_res = "male";
            else 
                $s_res = "other";
        }
    }
    return $s_res;
}

// Cache for action enabled checks
if (!isset($GLOBALS["minai_action_enabled_cache"])) {
    $GLOBALS["minai_action_enabled_cache"] = [];
}

// Flag to track if all actions have been loaded
if (!isset($GLOBALS["minai_all_actions_loaded"])) {
    $GLOBALS["minai_all_actions_loaded"] = false;
}

Function SetActionEnabled($action_name, $enabled) {
    $sl_act_name = strtolower(trim($action_name));
    $sid = "_minai_ACTION//".trim($action_name);
    $value = $enabled ? 'TRUE' : 'FALSE';
    $GLOBALS["minai_action_enabled_cache"][$sl_act_name] = $enabled;
    $db = $GLOBALS['db'];
    if (isset($db)) {
        $db->upsertRowOnConflict(
            'conf_opts',
            array(
                'id' => $sid,
                'value' => $value
            ),
            'id'
        );    
    } else 
        error_log("ERROR in SetActionEnabled, null db!");
    return $GLOBALS["db"]->query("UPDATE conf_opts SET value = '{$value}' WHERE LOWER(id) = LOWER('$sid')");
}

Function IsActionEnabled($actionName) {
    $action_in = trim($actionName);
    $actionName = strtolower(trim($action_in));

    // If we haven't loaded all actions yet, do it now
    if (!$GLOBALS["minai_all_actions_loaded"]) {
        // Load all enabled actions at once
        $query = "SELECT * FROM conf_opts WHERE (id ILIKE '_minai_ACTION//%') AND (LOWER(value)='true') ";
        $rows = $GLOBALS["db"]->fetchAll($query);
        
        // Mark all found actions as enabled
        foreach ($rows as $row) {
            // This is formated as __minai_ACTION//actionName. Extract it.
            $tmpName = substr($row['id'], 15);
            minai_log("info", "Preloaded action: " . $tmpName);
            $GLOBALS["minai_action_enabled_cache"][strtolower($tmpName)] = true;
        }
        
        // Mark that we've loaded all actions
        $GLOBALS["minai_all_actions_loaded"] = true;
        
        minai_log("info", "Preloaded all enabled actions");
    }
    
    // Return from cache (defaults to false if not found)
    $returnValue = isset($GLOBALS["minai_action_enabled_cache"][$actionName]) ? $GLOBALS["minai_action_enabled_cache"][$actionName] : false;
    //if (!$returnValue)
        SetActionEnabled($action_in, $returnValue);
    minai_log("info", "IsActionEnabled: {$action_in} = ". ($returnValue ? "Y" : "N")  );
    //error_log("IsActionEnabled: {$action_in} = ". ($returnValue ? "Y" : "N") . "  - exec trace"); //debug
    return $returnValue;
}

// Cache for action registrations to avoid duplicates
if (!isset($GLOBALS["minai_registered_actions"])) {
    $GLOBALS["minai_registered_actions"] = [];
}

Function RegisterAction($actionName) {
    // Check if already registered to avoid duplicates
    if (isset($GLOBALS["minai_registered_actions"][$actionName])) {
        return;
    }
    
    $checkName = strtolower($actionName);
    if (str_contains($checkName, 'stimulatewith') || str_contains($checkName, 'teasewith')) {
        $actionName = 'MinaiGlobalVibrator';
    }
    minai_log("info", "Checking IsActionEnabled: {$actionName}");
    if (IsActionEnabled($actionName)) {
        $GLOBALS["ENABLED_FUNCTIONS"][]=$actionName;
        $GLOBALS["minai_registered_actions"][$actionName] = true;
        minai_log("info", "Registering {$actionName}");
    }
    else {
        $GLOBALS["minai_registered_actions"][$actionName] = false;
        minai_log("info", "Not Registering {$actionName}");
    }
}

// Batch preload action status for multiple actions at once
function PreloadActions($actionNames) {
    if (empty($actionNames)) {
        return;
    }
    
    // Initialize action cache if needed
    if (!isset($GLOBALS["minai_action_enabled_cache"])) {
        $GLOBALS["minai_action_enabled_cache"] = [];
    }
    
    $missingActions = [];
    foreach ($actionNames as $action) {
        $action = strtolower($action);
        if (!isset($GLOBALS["minai_action_enabled_cache"][$action])) {
            $missingActions[] = $action;
        }
    }
    
    if (empty($missingActions)) {
        return; // All actions already cached
    }
    
    // Build a single query to get all enabled actions at once
    $whereConditions = [];
    foreach ($missingActions as $action) {
        $escapedAction = $GLOBALS["db"]->escape($action);
        $whereConditions[] = "(LOWER(id)=LOWER('_minai_ACTION//{$escapedAction}'))";
    }
    
    if (empty($whereConditions)) {
        return;
    }
    
    $whereClause = implode(" OR ", $whereConditions);
    $query = "SELECT LOWER(SUBSTRING(id FROM 14)) as action_name FROM conf_opts WHERE ({$whereClause}) AND (LOWER(value)='true') ";
    $rows = $GLOBALS["db"]->fetchAll($query);
    
    // Mark missing actions as disabled by default
    foreach ($missingActions as $action) {
        $GLOBALS["minai_action_enabled_cache"][$action] = false;
    }
    
    // Update cache with enabled actions from query result
    foreach ($rows as $row) {
        $actionName = strtolower($row['action_name']);
        $GLOBALS["minai_action_enabled_cache"][$actionName] = true;
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
    return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where (id='_minai_RADIANT//initial') and (LOWER(value)='true') ");
}

Function GetLastInput() {
    $db = $GLOBALS['db'];
    $ret = $GLOBALS["db"]->fetchAll("select * from conf_opts where (id='_minai_RADIANT//lastInput') ");
    if (!$ret) {
        return 0;
    }
    return intval($ret[0]['value']);
}

Function IsRadiant() {
    return ($GLOBALS["target"] != $GLOBALS["PLAYER_NAME"]);
}


// in case when we want to change target from radiant options and directly tell npc whom they need to talk to
function overrideTargetToTalk($name) {
    global $targetOverride;
    $targetOverride = $name;
}

function isPlayerInput() {
    return  in_array($GLOBALS["gameRequest"][0],["inputtext","inputtext_s","ginputtext","ginputtext_s","instruction","init"]);
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
    
    /*
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
    */
    // Parse the JSON data
    $rawData = DataGetCurrentPartyConf();

    $partyData = json_decode($rawData, true);
    
    // If parsing failed, return empty result
    if (!is_array($partyData)) {
        return $result;
    }

    foreach ($partyData as $member) {
        if (isset($member['name'])) {
            $result['members'][] = $member;
            $result['names'][] = $member['name'];
        }
    }
    
    return $result;
}

/**
 * Get party member number/index $nc from nearby actors list
 * 
 * @return the member name
 */
function GetOnePartyMember($nc=0, $s_fallback="") {
    
    $s_res = $s_fallback;

    $nearbyActors = GetActorValue("PLAYER", "nearbyActors", true);

    if (!empty($nearbyActors)) {
        $arr_actors = explode(",", $nearbyActors);
        if (isset($arr_actors)) {
            $s_res = $arr_actors[$nc] ?? $s_fallback; 
        }
    }

    return $s_res;
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
              WHERE (location IS NOT NULL) AND (location != '')
              AND (type = 'infoloc')
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
        preg_match('/Buildings to go:(.+), Current/', $locationString, $buildingsMatch);
        
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

/**
 * Batch fetch actor values for multiple actors and attributes
 * 
 * @param array $actors List of actor names
 * @param array $attributes List of attributes to fetch
 * @return array Multi-dimensional array with actor->attribute->value mapping
 */
function BatchGetActorValues($actors, $attributes) {
    $db = $GLOBALS["db"];
    $result = [];
    
    // Initialize result array with empty values
    foreach ($actors as $actor) {
        $result[strtolower($actor)] = [];
        foreach ($attributes as $attr) {
            $result[strtolower($actor)][strtolower($attr)] = "";
        }
    }
    
    // Build a single query to get all values at once
    $whereConditions = [];
    foreach ($actors as $actor) {
        $escapedActor = $db->escape(strtolower($actor));
        foreach ($attributes as $attr) {
            $escapedAttr = $db->escape(strtolower($attr));
            $whereConditions[] = "LOWER(id) = LOWER('_minai_{$escapedActor}//{$escapedAttr}')";
        }
    }
    
    if (empty($whereConditions)) {
        return $result;
    }
    
    $whereClause = implode(" OR ", $whereConditions);
    $query = "SELECT id, value FROM conf_opts WHERE {$whereClause}";
    $rows = $db->fetchAll($query);
    
    // Parse results into the result array
    foreach ($rows as $row) {
        $id = strtolower($row['id']);
        $value = $row['value'];
        
        // Extract actor and attribute from the ID
        if (preg_match('/_minai_([^\/]+)\/\/(.+)$/i', $id, $matches)) {
            $actor = strtolower($matches[1]);
            $attr = strtolower($matches[2]);
            $result[$actor][$attr] = $value;
        }
    }
    
    return $result;
}

/**
 * Batch check if multiple flags are enabled for multiple actors
 * 
 * @param array $actors List of actor names
 * @param array $flags List of flags to check
 * @return array Multi-dimensional array with actor->flag->boolean mapping
 */
function BatchIsEnabled($actors, $flags) {
    $db = $GLOBALS["db"];
    $result = [];
    
    // Initialize result array with false values
    foreach ($actors as $actor) {
        $result[strtolower($actor)] = [];
        foreach ($flags as $flag) {
            $result[strtolower($actor)][strtolower($flag)] = false;
        }
    }
    
    // Build a single query to get all enabled flags at once
    $whereConditions = [];
    foreach ($actors as $actor) {
        $escapedActor = $db->escape(strtolower($actor));
        foreach ($flags as $flag) {
            $escapedFlag = $db->escape(strtolower($flag));
            $whereConditions[] = "(LOWER(id) = LOWER('_minai_{$escapedActor}//{$escapedFlag}') AND LOWER(value) = LOWER('TRUE'))";
        }
    }
    
    if (empty($whereConditions)) {
        return $result;
    }
    
    $whereClause = implode(" OR ", $whereConditions);
    $query = "SELECT id FROM conf_opts WHERE {$whereClause}";
    $rows = $db->fetchAll($query);
    
    // Parse results into the result array
    foreach ($rows as $row) {
        $id = strtolower($row['id']);
        
        // Extract actor and flag from the ID
        if (preg_match('/_minai_([^\/]+)\/\/(.+)$/i', $id, $matches)) {
            $actor = strtolower($matches[1]);
            $flag = strtolower($matches[2]);
            $result[$actor][$flag] = true;
        }
    }
    
    return $result;
}

/**
 * Get all actor values for a specific actor in one database query
 * 
 * @param string $actor Actor name
 * @return array Associative array of attribute => value
 */
function GetAllActorValues($actor) {
    $db = $GLOBALS["db"];
    $actor = strtolower($db->escape($actor));
    $result = [];
    
    $query = "SELECT id, value FROM conf_opts WHERE (id ILIKE '_minai_{$actor}//%') ";
    $rows = $db->fetchAll($query);
    
    foreach ($rows as $row) {
        $id = $row['id'];
        $value = $row['value'];
        
        // Extract attribute from the ID
        if (preg_match('/_minai_[^\/]+\/\/(.+)$/i', $id, $matches)) {
            $attr = strtolower($matches[1]);
            $result[$attr] = $value;
        }
    }
    
    return $result;
}

function in_arrayi($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

function getConfOptionValue($s_key = "") {
    $s_res = "";
    if (strlen(trim($s_key)) > 0) {
        $ret = $GLOBALS["db"]->fetchAll("SELECT * FROM conf_opts WHERE (id='{$s_key}') LIMIT 1");
        if ($ret) {
            $s_res = $ret[0]['value'] ?? "";
        }
    }
    return $s_res;
}

function setConfOption($s_key = "", $s_value = "") {
    $b_res = false;
    $s_id = trim($s_key);
    $s_val = trim($s_value);

    if ((strlen($s_id) > 0) && (strlen() > 0)) {
        $b_res = $GLOBALS['db']->upsertRowOnConflict(
            'conf_opts',
            array(
                'id' => $s_id,
                'value' => $s_val
            ),
            'id'
        );
    } 
    return $b_res;
}
    
function GetAdverseInteractions($s_npc_name, $s_player_name) {
    
	$i_res = 0;
	if ((strlen($s_player_name)>0) && (strlen($s_npc_name)>0) && ($s_player_name != $s_npc_name) && 
        ($s_player_name != "The Narrator") && ($s_npc_name != "The Narrator") && 
        ($s_player_name != "everyone") && ($s_npc_name != "everyone")) {
            
		$s_player = $GLOBALS['db']->escape($s_player_name);
		$s_npc = $GLOBALS['db']->escape($s_npc_name);
        
        $s_sql = "SELECT count(rowid) as n_fear FROM speech WHERE 
speaker = '{$s_npc}' AND listener = '{$s_player}' AND 
emotion_intensity IN ('strong','moderate') AND 
mood IN ('fearful','furious', 'anxious', 'angry', 'irritated', 'disgusted', 'contemptuous', 'sardonic','smug') AND 
emotion IN ('rage', 'panic', 'fear', 'anger', 'disgust', 'aversion') AND 
localts > (SELECT (MAX(localts) - 1800) as m15 FROM speech) 
LIMIT 128 "; 
        //error_log($s_sql); //debug
        
		$db_rec = $GLOBALS['db']->fetchAll($s_sql);
		if (is_array($db_rec) && sizeof($db_rec)>0) {
			$i_res = intval($db_rec[0]['n_fear'] ?? 0);
		}
	}
	return $i_res;
}    
    
function getChimExecMode() {
    /* Check modes
    * Standard (STANDARD)
        - when using text input, Easy Roleplay can be done just by prepending ** to the text)
        Example:**(create a long speech about being the Dragonborn) => I am no mere wanderer upon these snow-bitten roads. I am Dovahkiin...
        Example:**you're like a zombie => By the Nine, thou walk’st with the stench of the draugr—undead, cursed, and far from Sovngarde’s grace
        - when using text input, you can achieve Event Injection With Response just putting text bewteen parenthesys
        Example:(Volkur falls to the ground wounded)

    * Whisper (WHISPER)
        (When enabled, we should send to plugin via InternalSetting a reduced DISTANCE_ACTIVATING_NPC,
        from this point, all NPC beyond that distance should be marked as far away, We must take this in 
        account to only store people NOT far away on eventlog (so far away NPCs won't have access to this context).
        If player is in stealh mode, no rechat (this is a standard behavior).

    * Director. (DIRECTOR)
        Call instruction directly.

    * Spawn Character (SPAWN)
        Call spawn character directly.

    * Easy Roleplay (IMPERSONATION)
        (Smart Impersonation) (we should need a prompt parameter so user can customice this). 
        Just prefix two asterisks at user input, and add the prompt. 
        Example: "Hello" => **(Rewrite and translate the following text into English, employing Skyrim lore language and drawing upon the context.) Hello.

    * Easy Roleplay (CREATION)
          
        Example: "Speech about being the Dragonborn" => **(Generate text employing Skyrim lore language and drawing upon the context, following the next instruction:Speech about being the Dragonborn ) 

    * Event Injection (INJECTION_LOG)
        (Whatever is typed/said is injected into event log as an roleplay instruction)
        Just store player speech on eventlog and die.

    * Event Injection With Response  (INJECTION_CHAT)
        (Whatever is typed/said is injected into event log as an roleplay instruction expecting response)
        Just store player speech on eventlog and follow the standard flow.
    */

    $EXECUTION_MODE_= getConfOptionValue("chim_mode"); 
    if (strlen($EXECUTION_MODE) < 3) 
        $EXECUTION_MODE = "STANDARD";
    $EXECUTION_MODE=strtoupper($EXECUTION_MODE);
    if (!in_array(($gameRequest[0] ?? '_?_'),["inputtext","inputtext_s","ginputtext","ginputtext_s"])) {
        $EXECUTION_MODE="STANDARD";
    }
}

require_once("contextbuilders/wornequipment_context.php");
require_once("utils/init_common_variables.php");