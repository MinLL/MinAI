<?php

$allKeywords = "";
$allFactions = "";

function CanVibrate($name) {
  return IsEnabled($name, "CanVibrate");
}

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key) {
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
    $ret = $GLOBALS["db"]->fetchAll("select * from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}')");
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

Function IsPlayer($name) {
    return ($GLOBALS["PLAYER_NAME"] == $name);
}

$GenericFuncRet =function($gameRequest) {
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
?>
