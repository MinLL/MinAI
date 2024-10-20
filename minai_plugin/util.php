<?php
require_once("config.php");
define("MINAI_ACTOR_VALUE_CACHE", "minai_actor_value_cache");
require_once("importDataToDB.php");

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

    $query = "select * from conf_opts where LOWER(id) like LOWER('{$idPrefix}%')";
    $ret = $GLOBALS["db"]->fetchAll($query);

    foreach ($ret as $row) {
        //do this instead of split // because $name could have // in it
        $key = substr(strtolower($row['id']), strlen($idPrefix));
        $value = $row['value'];
        $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key] = $value;
    }
}

function CanVibrate($name) {
    return IsEnabled($name, "CanVibrate") && IsActionEnabled("MinaiGlobalVibrator");
}

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key, $preserveCase=false, $skipCache=false) {
    $name = addslashes($name);
    $key = addslashes($key);

    If (!$preserveCase && !$skipCache) {
        $name = strtolower($name);
        $key = strtolower($key);

        If (!HasActorValueCache($name)) {
            BuildActorValueCache($name);
        }

        $ret = GetActorValueCache($name, $key);
        return $ret === null ? "" : strtolower($ret);
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
    
    return $ret;
}

Function IsEnabled($name, $key) {
  $name = strtolower($name);
  return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}') and LOWER(value)=LOWER('TRUE')");
}

Function IsSexActive() {
    // if there is active scene thread involving current speaker
    getScene($GLOBALS["HERIKA_NAME"]);
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
    return (IsInFaction($name, "Framework Follower Faction") || IsInFaction($name, "Follower Role Faction") || IsInFaction($name, "PotentialFollowerFaction") || IsInFaction($name, "Potential Follower"));
}

// Check if the specified actor is following (not follower)
Function IsFollowing($name) {
    return IsInFaction($name, "FollowFaction");
}

Function IsInScene($name) {
    $value = strtolower(trim(GetActorValue($name, "scene")));
    return $value != null && $value != "" && $value != "none";
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


Function StoreRadiantActors($actor1, $actor2) {
    $db = $GLOBALS['db'];
    $id = "_minai_RADIANT//actor1";
    $db->delete("conf_opts", "id='{$id}'");
    $db->insert(
        'conf_opts',
        array(
            'id' => $id,
            'value' => $actor1
        )
    );
    $id = "_minai_RADIANT//actor2";
    $db->delete("conf_opts", "id='{$id}'");
    $db->insert(
        'conf_opts',
        array(
            'id' => $id,
            'value' => $actor2
        )
    );
    $id = "_minai_RADIANT//initial";
    $db->delete("conf_opts", "id='{$id}'");
    $db->insert(
        'conf_opts',
        array(
            'id' => $id,
            'value' => 'TRUE'
        )
    );
    error_log("minai: Storing Radiant Actors");
}

Function ClearRadiantActors() {
    error_log("minai: Clearing Radiant Actors");
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

$GLOBALS["target"] = GetTargetActor();
$GLOBALS["nearby"] = explode(",", GetActorValue("PLAYER", "nearbyActors"));

function getScene($actor) {
    $scene = $GLOBALS["db"]->fetchAll("SELECT * from minai_threads WHERE male_actors ~* '(,|^)$actor(,|$)' OR female_actors ~* '(,|^)$actor(,|$)'");

    if(!$scene) {
        return null;
    }
    $scene = $scene[0];
    $sceneDesc = getSceneDesc($scene);

    if($scene["female_actors"] && $scene["male_actors"]) {
        // push females at the beginning for sexlab
        if($scene["framework"] == "sexlab") {
            $scene["actors"] = $scene["female_actors"].",".$scene["male_actors"];
        }
        // push males at the beginning for ostim
        else {
            $scene["actors"] = $scene["male_actors"].",".$scene["female_actors"];
        }
    } elseif($scene["female_actors"]) {
        $scene["actors"] = $scene["female_actors"];
    } else {
        $scene["actors"] = $scene["male_actors"];
    }

    $actors = explode(",", $scene["actors"]);
    $sceneDesc = replaceActorsNamesInSceneDesc($actors, $sceneDesc);
    $scene["description"] = $sceneDesc;
            
    return $scene;
}

function addXPersonality($jsonXPersonality, $isInSex) {
    if(!$jsonXPersonality) {
        return;
    }

    $GLOBALS["HERIKA_PERS"] .= "
    - Orientation: {$jsonXPersonality["orientation"]}
    - Romantic relationship type: {$jsonXPersonality["relationshipStyle"]}";

    if($isInSex) {
        $GLOBALS["HERIKA_PERS"] .= "
During sex {$GLOBALS["HERIKA_PERS"]}:
- speaks in this style {$jsonXPersonality["speakStyleDuringSex"]};
- prefers these positions: ".implode(", ", $jsonXPersonality["preferredSexPositions"]).";
- likes to participate in such sex activities: ".implode(", ", $jsonXPersonality["sexualBehavior"]).";
- has secret sex fantasies:
  ".implode("\n  ",$jsonXPersonality["sexFantasies"]);
    }
}

function getSceneDesc($scene) {
    $query = "SELECT * FROM minai_scenes_descriptions WHERE ";
    $currSceneId = $scene["curr_scene_id"];
    
    if($scene["framework"] == "ostim") {
        $query .= "ostim_id ";
    } else {
        $query .= "sexlab_id ";
        // since in scene descriptions there is one description per scene for all actors
        // sexlab id in minai_scenes_descriptions has this format SomeName_S1
        // and original sexlab ids are usually with _A0 on the end: SOmeName_A1_S1
        // need to remove _A0 part from ids to be able to find rows in minai_scenes_descriptions
        $currSceneId = preg_replace('/_A\d+$/', '', $currSceneId);
    }

    $query .= "= '$currSceneId'";
    $queryRet = $GLOBALS["db"]->fetchAll($query);
    if ($queryRet)
        return $queryRet[0]["description"];
    return "";
}

function replaceActorsNamesInSceneDesc($actors, $sceneDesc) {
    foreach ($actors as $index => $actor) {
        $sceneDesc = str_replace("{actor$index}", $actor, $sceneDesc);
    }

    return $sceneDesc;
}

function getXPersonality($currentName) {
    $codename=strtr(strtolower(trim($currentName)),[" "=>"_","'"=>"+"]);
    $queryRet = $GLOBALS["db"]->fetchAll("SELECT * from minai_x_personalities WHERE id = '$codename'");
    $jsonXPersonality = null;
    if ($queryRet)
        $jsonXPersonality =  $queryRet[0]["x_personality"];
    if(isset($jsonXPersonality)) {
        $jsonXPersonality = json_decode($jsonXPersonality,true);
    }

    return $jsonXPersonality;
}

// in case when we want to change target from radiant options and directly tell npc whom they need to talk to
function overrideTargetToTalk($name) {
    global $targetOverride;
    $targetOverride = $name;
}

function getTargetDuringSex($scene) {
    global $targetOverride;
    if($targetOverride) {
        return $targetOverride;
    }
    $actors = explode(",", $scene["actors"]);

    $actorsToSpeak = array_filter($actors, function($str){
        return $str !== $GLOBALS["HERIKA_NAME"];
    });
    $actorsToSpeak = array_values($actorsToSpeak);
    $targetToSpeak = $actorsToSpeak[array_rand($actorsToSpeak)];

    // if more then 1 actor to speak in scene make it 50% chance speaker will address all participants
    if(count($actorsToSpeak) > 1 && mt_rand(0, 1) === 1) {
        $targetToSpeak = implode(", ", $actorsToSpeak);
    }

    overrideTargetToTalk($targetToSpeak);

    return $targetToSpeak;
}
?>
