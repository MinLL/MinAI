<?php
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
    $origLength = strlen($idPrefix);
    $idPrefix = $GLOBALS["db"]->escape($idPrefix);
    $query = "select * from conf_opts where LOWER(id) like LOWER('{$idPrefix}%')";
    $ret = $GLOBALS["db"]->fetchAll($query);
    // error_log("Building cache for $name");
    foreach ($ret as $row) {
        //do this instead of split // because $name could have // in it
        $key = substr(strtolower($row['id']), $origLength);
        $value = $row['value'];
        // error_log($name . ':: (' . $key . ') ' . $row['id'] . ' = ' . $row['value']);
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

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key, $preserveCase=false, $skipCache=false) {
    // error_log("Looking up $name: $key");
    If (!$preserveCase && !$skipCache) {
        $name = strtolower($name);
        $key = strtolower($key);

        If (!HasActorValueCache($name)) {
            BuildActorValueCache($name);
        }
        // error_log("Checking cache: $name, $key");
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
    return (
        IsInFaction($name, "Framework Follower Faction") ||
        IsInFaction($name, "Follower Role Faction") ||
        IsInFaction($name, "PotentialFollowerFaction") ||
        IsInFaction($name, "CurrentFollowerFaction") ||
        IsInFaction($name, "DLC1SeranaFaction") ||
        IsInFaction($name, "Potential Follower")
    );
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
    $playerName = GetActorValue("PLAYER", "playerName", true);
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

function getScene($actor, $threadId = null) {
    error_log("minai: getScene($actor, $threadId)");
    $actor = str_replace('[', '\[', $actor);
    $actor = str_replace(']', '\]', $actor);
    if(isset($threadId)) {
        $scene = $GLOBALS["db"]->fetchAll("SELECT * from minai_threads WHERE thread_id = '$threadId'");
    } else {
        // Use case-insensitive regex pattern matching for actor names
        $scene = $GLOBALS["db"]->fetchAll("SELECT * from minai_threads WHERE 
            male_actors ~* '(,|^)\\s*$actor\\s*(,|$)' OR 
            female_actors ~* '(,|^)\\s*$actor\\s*(,|$)' OR 
            victim_actors ~* '(,|^)\\s*$actor\\s*(,|$)'");
    }

    if(!$scene) {
        error_log("minai: No scene found.")     ;
        return null;
    }
    $scene = $scene[0];
    $sceneDesc = getSceneDesc($scene);
    
    // Build list of all actors
    $allActors = [];
    if($scene["female_actors"]) {
        $allActors = array_merge($allActors, array_map('trim', explode(",", $scene["female_actors"])));
    }
    if($scene["male_actors"]) {
        $allActors = array_merge($allActors, array_map('trim', explode(",", $scene["male_actors"])));
    }
    
    // Add assault context if victims are present
    if (!empty($scene["victim_actors"]) && $scene["victim_actors"] !== null) {
        $victims = array_map('trim', explode(",", $scene["victim_actors"]));
        $aggressors = array_diff($allActors, $victims);
        $isAre = (count($aggressors) > 1 ? "are" : "is") ;
        if (!$sceneDesc) {
            $sceneDesc = "A non-consensual sexual act is actively taking place. ";
            $sceneDesc .= implode(", ", $aggressors) . " $isAre raping " . implode(", ", $victims) . ". ";
            if ($scene["fallback"]) {
                $sceneDesc .= $scene["fallback"];
            }
        } else {
            // Add assault context to existing scene description
            $assaultContext = implode(", ", $aggressors) . " $isAre raping " . implode(", ", $victims) . ". ";
            $sceneDesc = $assaultContext . $sceneDesc;
        }
    } else if (!$sceneDesc) {
        // Original fallback description for consensual scenes
        $sceneDesc = "A sex scene is actively taking place. ";
        $sceneDesc .= "These participants are currently engaged in sex in the scene: " . implode(", ", $allActors) . ".\n";
        if ($scene["fallback"]) {
            $sceneDesc .= $scene["fallback"];
        }
    }

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
    error_log("minai: Returning scene: $sceneDesc.");

    return $scene;
}

function addXPersonality($jsonXPersonality) {
    if(!$jsonXPersonality) {
        return;
    }

    $GLOBALS["HERIKA_PERS"] .= "
    - Orientation: {$jsonXPersonality["orientation"]}
    - Romantic relationship type: {$jsonXPersonality["relationshipStyle"]}";

    if(IsSexActive()) {
        $GLOBALS["HERIKA_PERS"] .= "
During sex {$GLOBALS["HERIKA_NAME"]}:
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
        $query .= "LOWER(ostim_id) ";
    } else {
        $query .= "LOWER(sexlab_id) ";
        // since in scene descriptions there is one description per scene for all actors
        // sexlab id in minai_scenes_descriptions has this format SomeName_S1
        // and original sexlab ids are usually with _A0 on the end: SOmeName_A1_S1
        // need to remove _A0 part from ids to be able to find rows in minai_scenes_descriptions
        $currSceneId = preg_replace('/_[Aa]\d+/', '', $currSceneId);
    }

    $query .= "= LOWER('$currSceneId')";
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
    $queryRet = $GLOBALS["db"]->fetchAll("SELECT * from minai_x_personalities WHERE LOWER(id) = LOWER('$currentName')");
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

    // if in a solo scene, talk to everyone (or no one in particular)
    if(count($actorsToSpeak) === 0) {
        return "everyone";
    }

    $targetToSpeak = $actorsToSpeak[array_rand($actorsToSpeak)];

    // if more then 1 actor to speak in scene make it 50% chance speaker will address all participants
    if(count($actorsToSpeak) > 1 && mt_rand(0, 1) === 1) {
        $targetToSpeak = implode(", ", $actorsToSpeak);
    }

    overrideTargetToTalk($targetToSpeak);

    return $targetToSpeak;
}

function GetRevealedStatus($name) {
  $cuirass = GetActorValue($name, "cuirass", false, true);
  
  $wearingBottom = false;
  $wearingTop = false;
  
  // if $eqContext["context"] not empty, then will set ret
  if (!empty($cuirass)) {
      $wearingTop = true;
  }
  if (HasKeyword($name, "SLA_HalfNakedBikini")) {
    $wearingTop = true;
  }
  if (HasKeyword($name, "SLA_ArmorHalfNaked")) {
    $wearingTop = true;
  }
  if (HasKeyword($name, "SLA_Brabikini" )) {
    $wearingTop = true;
  }
  if (HasKeyword($name, "SLA_Thong")) {
    $wearingBottom = true;
  }
  if (HasKeyword($name, "SLA_PantiesNormal")) {
    $wearingBottom = true;
  }
  if (HasKeyword($name, "SLA_PantsNormal")) {
    $wearingBottom = true;
  }
  if (HasKeyword($name, "SLA_MicroHotPants")) {
    $wearingBottom = true;
  }

  if (HasKeyword($name, "SLA_ArmorTransparent")) {
    $wearingBottom = false;
    $wearingTop = false;
  }
  if (HasKeyword($name, "SLA_ArmorLewdLeotard")) {
    $wearingBottom = true;
    $wearingTop = true;
  }
  if (HasKeyword($name, "SLA_PelvicCurtain")) {
    $wearingBottom = true;
  }
  if (HasKeyword($name, "SLA_FullSkirt")) {
    $wearingBottom = true;
  }
  if (HasKeyword($name, "SLA_MiniSkirt")) {
    $wearingBottom = true;
  }
  if (HasKeyword($name, "EroticArmor")) {
      $wearingBottom = true;
      $wearingTop = true;
  }
  return ["wearingTop" => $wearingTop, "wearingBottom" => $wearingBottom];
}

Function IsExplicitScene() {
    // Check if we're in a sex scene
    if (IsSexActive()) {
        return true;
    }
    
    // Check if this is a TNTR event
    if (isset($GLOBALS["gameRequest"]) && strpos($GLOBALS["gameRequest"][0], "minai_tntr_") === 0) {
        return true;
    }
    
    return false;
}

Function SetNarratorPrompts($isFirstPerson = false) {
    if ($isFirstPerson) {
        if (IsExplicitScene()) {
            $GLOBALS["PROMPTS"]["minai_narrator_talk"] = [
                "cue" => [
                    "write a first-person erotic narrative response as {$GLOBALS["PLAYER_NAME"]}, focusing entirely on your immediate physical sensations and emotional state. Describe in vivid detail exactly what you are feeling in this moment, both physically and mentally."
                ],
                "player_request"=>[    
                    "{$GLOBALS["PLAYER_NAME"]} is overwhelmed by the intense sensations coursing through her body.",
                ]
            ];
            
            $GLOBALS["TEMPLATE_DIALOG"] = "Respond in first-person perspective as {$GLOBALS["PLAYER_NAME"]}, describing only your current physical and emotional state. Focus purely on the present moment - what you're feeling, how your body is responding, and your immediate emotional reactions. Don't reflect on the past or future, stay completely in the now.";
        } else {
            $GLOBALS["PROMPTS"]["minai_narrator_talk"] = [
                "cue" => [
                    "write a first-person narrative response as {$GLOBALS["PLAYER_NAME"]}, describing your thoughts, feelings, and experiences in this moment. Speak introspectively about your journey and current situation."
                ],
                "player_request"=>[    
                    "{$GLOBALS["PLAYER_NAME"]} thinks to herself about the current situation.",
                ]
            ];
            
            $GLOBALS["TEMPLATE_DIALOG"] = "Respond in first-person perspective as {$GLOBALS["PLAYER_NAME"]}, sharing your personal thoughts and feelings.";
        }
    } else {
        if (IsExplicitScene()) {
            $GLOBALS["PROMPTS"]["minai_narrator_talk"] = [
                "cue" => [
                    "write a response as The Narrator, describing {$GLOBALS["PLAYER_NAME"]}'s immediate physical and emotional experiences in vivid sensual detail. Focus entirely on what she is feeling in this exact moment."
                ]
            ];
            
            $GLOBALS["TEMPLATE_DIALOG"] = "You are The Narrator. Describe the intense sensations and emotions being experienced right now, focusing purely on the present moment.";
        } else {
            $GLOBALS["PROMPTS"]["minai_narrator_talk"] = [
                "cue" => [
                    "write a response as The Narrator, speaking from an omniscient perspective about the world and the player's journey."
                ]
            ];
            
            $GLOBALS["TEMPLATE_DIALOG"] = "You are The Narrator. Respond in an omniscient, storyteller-like manner.";
        }
    }
}

$GLOBALS["target"] = GetTargetActor();
$GLOBALS["nearby"] = explode(",", GetActorValue("PLAYER", "nearbyActors"));

if (IsChildActor($GLOBALS['HERIKA_NAME']) || IsChildActor($GLOBALS["target"])) {
    $GLOBALS["disable_nsfw"] = true;
}



// object oriented way to organize this
// use it like $utilities->GetRevealedStatus($actorName);
class Utilities {
    private $existingFunctionsNames = array(
        "GetRevealedStatus",
        "GetActorValueCache",
        "HasActorValueCache",
        "BuildActorValueCache",
        "CanVibrate",
        "GetActorValue",
        "IsEnabled",
        "IsSexActive",
        "IsPlayer",
        "IsModEnabled",
        "IsInFaction",
        "HasKeyword",
        "IsConfigEnabled",
        "IsFollower",
        "IsFollowing",
        "IsInScene",
        "IsFollower",
        "ShouldClearFollowerFunctions",
        "ShouldEnableSexFunctions",
        "IsChildActor",
        "IsMale",
        "IsFemale",
        "IsActionEnabled",
        "RegisterAction",
        "StoreRadiantActors",
        "ClearRadiantActors",
        "IsNewRadiantConversation",
        "GetLastInput",
        "IsRadiant",
        "getScene",
        "addXPersonality",
        "getSceneDesc",
        "replaceActorsNamesInSceneDesc",
        "getXPersonality",
        "overrideTargetToTalk",
        "getTargetDuringSex",
        "GetRevealedStatus",
    );

    public function hasMethod($methodName) {
        if(in_array($methodName, $this->existingFunctionsNames)) {
            return true;
        }
        return false;
    }

    public function __call($name, $params=array()) {
        if(method_exists($this, $name)) {
            // for methods attached to this class
            return call_user_func(array($this, $name), $params);
        } else if ($this->hasMethod($name)) {
           // function exists outside of class in this utlities file
           return $name(...$params); 
        }
        else {
            error_log("Error calling Utilities clas: ". $name . " is not defined as a method or function in util.php");
        }
    }

    public function beingsInCloseRange() {
        $beingsInCloseRange = DataBeingsInCloseRange();
        $realBeings = [];
        $beingsList = explode("|",$beingsInCloseRange);
        $count = 0;
        foreach($beingsList as $bListItem) {
            if(strpos($bListItem, " ")===0) {
                // account for Igor| bandit|
                if(count($realBeings)>0){
                    $realBeings[count($realBeings) - 1] .= ",".$bListItem;
                }    
            } else {
                $realBeings[] = $bListItem;
            }
            $count++;
        }
        $result = implode("|", $realBeings);
        return $result;
    }   

    public function beingsInRange() {
        $beingsInRange = DataBeingsInRange();

        $realBeings = [];
        $beingsList = explode("|",$beingsInRange);
        $count = 0;
        foreach($beingsList as $bListItem) {
            if(strpos($bListItem, " ")===0) {
                // account for Igor| bandit|
                if(count($realBeings)>0){
                    $realBeings[count($realBeings) - 1] .= ",".$bListItem;
                }    
            } else {
                $realBeings[] = $bListItem;
            }
            $count++;
        }
        $result = implode("|", $realBeings);
        return $result;
    }
}

function determineSpeakStyle($currentName, $scene, $jsonXPersonality) {
    $speakStyles = ["dirty talk", "sweet talk", "sensual whispering", "dominant talk", 
                    "submissive talk", "teasing talk", "erotic storytelling", 
                    "breathless gasps", "sultry seduction", "playful banter"];
    
    // Check if current speaker is a victim or potential aggressor
    $isVictim = false;
    $isAggressor = false;
    $hasVictims = false;
    
    if (isset($scene["victim_actors"]) && !empty($scene["victim_actors"]) && $scene["victim_actors"] !== null) {
        $victimActors = array_map('trim', array_map('strtolower', explode(",", $scene["victim_actors"])));
        $isVictim = in_array(strtolower($currentName), $victimActors);
        $hasVictims = true;
        
        // If there are victims and speaker isn't one, they're an aggressor
        if ($hasVictims && !$isVictim) {
            $isAggressor = true;
        }
    }
    
    // Override speak style based on role in scene
    if ($isVictim) {
        return ["style" => "victim talk", "role" => "victim"];
    } elseif ($isAggressor) {
        return ["style" => "aggressor talk", "role" => "aggressor"];
    } else {
        $speakStyle = null;
        if ($jsonXPersonality) {
            $speakStyle = $jsonXPersonality["speakStyleDuringSex"];
        }
        if(!$speakStyle) {
            $speakStyle = $speakStyles[array_rand($speakStyles)];
        }
        return ["style" => $speakStyle, "role" => "normal"];
    }
}

function GetActorArousal($actorName) {
    $arousal = GetActorValue($actorName, "arousal");
    if (empty($arousal)) {
        // If arousal isn't set, default to 100 to allow sex functions
        return 100;
    }
    return intval($arousal);
}

function GetMinArousalForSex() {
    $threshold = GetActorValue($GLOBALS['PLAYER_NAME'], "arousalForSex");
    if (empty($threshold)) {
        // If threshold isn't set, default to 0 to allow sex functions
        return 0;
    }
    return intval($threshold);
}

// Add helper to check if sex transitions are allowed for an actor
function AreSexTransitionsAllowed($actorName) {
    return GetActorValue($actorName, "allowSexTransitions", false, true) !== "false";
}

// Add helper to check if actor is in sex scene
function IsActorInSexScene($actorName) {
    $scene = getScene($actorName);
    return isset($scene) && !empty($scene);
}

function GetActorPronouns($name) {
    // Default to "they/them/their" if we can't determine gender
    $pronouns = [
        "subject" => "they",
        "object" => "them", 
        "possessive" => "their"
    ];
    
    // Try to determine gender from keywords
    if (IsMale($name)) {
        $pronouns = [
            "subject" => "he",
            "object" => "him",
            "possessive" => "his"
        ];
    } else if (IsFemale($name)) {
        $pronouns = [
            "subject" => "she", 
            "object" => "her",
            "possessive" => "her"
        ];
    }
    
    return $pronouns;
}

?>
