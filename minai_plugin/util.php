<?php
require_once("config.php");
$allKeywords = "";
$allFactions = "";

function CanVibrate($name) {
    return IsEnabled($name, "CanVibrate") && IsActionEnabled("MinaiGlobalVibrator");
}

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key, $preserveCase=false) {
    $name = addslashes($name);
    $key = addslashes($key);
    global $allKeywords;
    global $allFactions;

    if ($allKeywords != "") {
        return $allKeywords;
    }
    if ($allFactions != "") {
        return $allFactions;
    }
    // return strtolower("JobInnkeeper,Whiterun,,,,Bannered Mare Services,,Whiterun Bannered Mare Faction,,SLA TimeRate,sla_Arousal,sla_Exposure,slapp_HaveSeenBody,slapp_IsAnimatingWKidFaction,");
    $query = "select * from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}')";
    if ($preserveCase) {
        $tmp = strtolower($name);
        $query = "select * from conf_opts where LOWER(id)='_minai_{$tmp}//{$key}'";
    }
    $ret = $GLOBALS["db"]->fetchAll($query);
    if (!$ret) {
        return "";
    }
    $ret = strtolower($ret[0]['value']);
    if ($name == "AllKeywords") {
        $allKeywords = $ret;
    }
    if ($name == "AllFactions") {
        $allFactions = $ret;
    }
    return $ret;
}

Function IsEnabled($name, $key) {
  $name = strtolower($name);
  return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}') and LOWER(value)=LOWER('TRUE')");
}

Function IsSexActive() {
    return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('sexscene') and LOWER(value)=LOWER('on')");
}

Function IsPlayer($name) {
    return ($GLOBALS["PLAYER_NAME"] == $name);
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
    return str_contains(GetActorValue($name, "AllFactions"), $faction);
}

Function HasKeyword($name, $keyword) {
    $keyword = strtolower($keyword);
    return str_contains(GetActorValue($name, "AllKeywords"), $keyword);
}

Function IsConfigEnabled($configKey) {
    return IsEnabled($GLOBALS['PLAYER_NAME'], $configKey);
}

Function IsFollower($name) {
    return (IsInFaction($name, "Framework Follower Faction") || IsInFaction($name, "Follower Role Faction") || IsInFaction($name, "PotentialFollowerFaction") || IsInFaction($name, "Potential Follower"));
}


Function ShouldClearFollowerFunctions() {
    return ($GLOBALS["restrict_nonfollower_functions"] && !IsFollower($GLOBALS["HERIKA_NAME"]));
}

Function ShouldEnableSexFunctions($name) {
    $arousalThreshold = GetActorValue($GLOBALS['PLAYER_NAME'], "arousalForSex");
    $arousal = GetActorValue($name, "arousal");
    if (empty($arousalThreshold) || empty($arousal)) {
        // If the config isn't set, default to enabled.
        // User may also not have arousal mod, so default to enabled
        return true;
    }
    return (intval($arousal) >= intval($arousalThreshold));
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


if (IsChildActor($GLOBALS['HERIKA_NAME'])) {
    $GLOBALS["disable_nsfw"] = true;
}


Function IsMale($name) {
    return HasKeyword($name, "ActorSexMale");
}

Function IsFemale($name) {
    return HasKeyword($name, "ActorSexFemale");
}


Function IsActionEnabled($actionName) {
  $actionName = strtolower($actionName);
  return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('_minai_ACTION//{$actionName}') and LOWER(value)=LOWER('TRUE')");
}


Function RegisterAction($actionName) {
    if (IsActionEnabled($actionName)) {
        $GLOBALS["ENABLED_FUNCTIONS"][]=$actionName;
        // error_log("minai: Registering {$actionName}");
    }
    else {
        // error_log("minai: Not Registering {$actionName}");
    }
}



// Override player name
if ($GLOBALS["force_aiff_name_to_ingame_name"]) {
    $playerName = GetActorValue("PLAYER", "playerName");
    if ($playerName) {
        $GLOBALS["PLAYER_NAME"] = $playerName;
    }
}


?>
