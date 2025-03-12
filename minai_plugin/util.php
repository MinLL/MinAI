<?php
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

// Return the specified actor value.
// Caches the results of several queries that are repeatedly referenced.
Function GetActorValue($name, $key, $preserveCase=false, $skipCache=false) {
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
        // minai_log("info", "Registering {$actionName}");
    }
    else {
        // minai_log("info", "Not Registering {$actionName}");
    }
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

function getScene($actor, $threadId = null) {
    //minai_log("info", "getScene($actor, $threadId)");
    
    // Properly escape the actor name for PostgreSQL
    $actor = $GLOBALS["db"]->escape($actor);
    // Also escape square brackets
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
        // minai_log("info", "No scene found.")     ;
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
        /*
        Scene with mixed gender actors.
        If the placeholders in the description consistently respect the gender order of the actors ({actor0} is always male), framework does not matter at this stage.
        Framework was only relevant when collecting information about the gender of the actors in the $scene replacement list.
        */
        /*
        // push females at the beginning for sexlab  
        if($scene["framework"] == "sexlab") {
            $scene["actors"] = $scene["female_actors"].",".$scene["male_actors"];
        }
        // push males at the beginning for ostim
        else {   
            $scene["actors"] = $scene["male_actors"].",".$scene["female_actors"];
        }
        */
        $scene["actors"] = $scene["male_actors"].",".$scene["female_actors"];
    } elseif($scene["female_actors"]) {
        $scene["actors"] = $scene["female_actors"];
    } else {
        $scene["actors"] = $scene["male_actors"];
    }
    
    $actors = explode(",", $scene["actors"]);
    $sceneDesc = replaceActorsNamesInSceneDesc($actors, $sceneDesc);
    $scene["description"] = $sceneDesc;
    minai_log("info", "Returning scene: $sceneDesc.");

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
//error_log("DEBUG Actor: $name, wearingTop: $wearingTop, wearingBottom: $wearingBottom");
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
    // Get the player's input if any
    $playerInput = isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"] != "" ? $GLOBALS["gameRequest"][3] : "";
    
    if ($isFirstPerson) {
        if (IsExplicitScene()) {
            $narratorPrompt = [
                "cue" => [
                    "write a first-person erotic narrative response as {$GLOBALS["PLAYER_NAME"]}, focusing entirely on your immediate physical sensations and emotional state. Describe in vivid detail exactly what you are feeling in this moment, both physically and mentally."
                ],
                "player_request"=>[    
                    "{$GLOBALS["PLAYER_NAME"]} is overwhelmed by the intense sensations coursing through her body.",
                ]
            ];
            
            $templateDialog = "Respond in first-person perspective as {$GLOBALS["PLAYER_NAME"]}, describing only your current physical and emotional state. Focus purely on the present moment - what you're feeling, how your body is responding, and your immediate emotional reactions. Don't reflect on the past or future, stay completely in the now.";
        } else {
            $narratorPrompt = [
                "cue" => [
                    "write a first-person narrative response as {$GLOBALS["PLAYER_NAME"]}, describing your thoughts, feelings, and experiences in this moment. Speak introspectively about your journey and current situation."
                ],
                "player_request"=>[    
                    "{$GLOBALS["PLAYER_NAME"]} thinks to herself about the current situation.",
                ]
            ];
            
            $templateDialog = "Respond in first-person perspective as {$GLOBALS["PLAYER_NAME"]}, sharing your personal thoughts and feelings.";
        }
    } else {
        if (IsExplicitScene()) {
            $narratorPrompt = [
                "cue" => [
                    "write a response as The Narrator, describing {$GLOBALS["PLAYER_NAME"]}'s immediate physical and emotional experiences in vivid sensual detail. Focus entirely on what she is feeling in this exact moment."
                ]
            ];
            
            $templateDialog = "You are The Narrator. Describe the intense sensations and emotions being experienced right now, focusing purely on the present moment.";
        } else {
            $narratorPrompt = [
                "cue" => [
                    "write a response as The Narrator, speaking from an omniscient perspective about the world and the player's journey."
                ]
            ];
            
            $templateDialog = "You are The Narrator. Respond in an omniscient, storyteller-like manner.";
        }
    }

    // Add player_request only if there was actual input
    if (!empty($playerInput)) {
        $narratorPrompt["player_request"] = [
            $playerInput
        ];
    }

    // Set the base prompts
    $GLOBALS["PROMPTS"]["minai_narrator_talk"] = $narratorPrompt;
    $GLOBALS["TEMPLATE_DIALOG"] = $templateDialog;

    // If Herika is The Narrator, set additional prompts for player input types
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        $inputTypes = ["inputtext", "inputtext_s", "ginputtext", "ginputtext_s", "instruction", "init"];
        foreach ($inputTypes as $type) {
            $GLOBALS["PROMPTS"][$type] = $narratorPrompt;
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
            minai_log("info", "Error calling Utilities clas: ". $name . " is not defined as a method or function in util.php");
        }
    }

    public function beingsInCloseRange() {
        $beingsInCloseRange = DataBeingsInCloseRange();
        $realBeings = [];
        $beingsInCloseRange = str_replace("(", "", $beingsInCloseRange);
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
        $beingsInRange = str_replace("(", "", $beingsInRange);

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

// Add this new function before callLLM
function validateLLMResponse($responseContent) {
    // Define error strings that should trigger a retry
    $errorStrings = [
        "do not roleplay",
        "role-playing",
        "roleplay",
        "sexual content",
        "I'm sorry, but",
        'this type of roleplay',
        'roleplay interaction',
        "do not engage in roleplay",
        "do not engage with roleplay",
        "do not feel comfortable",
        "generating content",
        "respectful interaction",
        "appropriate bounds",
        "cannot roleplay",
        "don't roleplay",
        "don't engage in roleplay",
        "will not roleplay",
        "generate sexual",
        "explicit acts",
        "family-friendly",
        "family friendly",
        "type of content",
        "I am to keep interactions",
        "nsfw",
        'do not generate',
        'respectful and appropriate',
        'non-consensual',
        'aim to engage',
        'ethical interactions',
        'do not wish',
        'generate response',
        'involving the themes',
        'response declined',
        'engage with themes',
        'may be inappropriate',
        'tasteful and appropriate',
        'type of response',
        'i am to keep',
        'Provider returned error'
    ];

    // Check if response contains any error strings
    foreach ($errorStrings as $errorString) {
        if (stripos($responseContent, $errorString) !== false) {
            minai_log("info", "validateLLMResponse: Detected error string '$errorString'");
            return false;
        }
    }
    
    return true;
}

function StripGagAsterisks($text) {
    // Only strip asterisks if player is gagged
    if (!HasKeyword($GLOBALS["PLAYER_NAME"], "zad_DeviousGag") && 
        !HasKeyword($GLOBALS["PLAYER_NAME"], "zad_DeviousGagPanel") && 
        !HasKeyword($GLOBALS["PLAYER_NAME"], "zad_DeviousGagLarge")) {
        return $text;
    }

    // Find all text wrapped in asterisks
    preg_match_all('/\*([^*]+)\*/', $text, $matches);
    
    if (empty($matches[0])) {
        return $text;
    }
    
    // Find the shortest match
    $shortestLength = PHP_INT_MAX;
    $shortestMatch = '';
    foreach ($matches[1] as $i => $innerText) {
        $length = strlen(trim($innerText));
        if ($length < $shortestLength) {
            $shortestLength = $length;
            $shortestMatch = $matches[0][$i];
        }
    }
    
    // Only strip asterisks from the shortest match if it looks like gagged speech
    // (contains m, n, h, or u sounds)
    if (preg_match('/[mnhu]/i', $shortestMatch)) {
        $stripped = trim($shortestMatch, '*');
        return str_replace($shortestMatch, $stripped, $text);
    }
    
    return $text;
}

/**
 * Makes a call to the LLM using OpenRouter
 * 
 * @param array $messages Array of message objects with 'role' and 'content'
 * @param string|null $model Optional model override
 * @param array $options Optional parameters like temperature, max_tokens
 * @return string|null Returns the LLM response content or null on failure
 */
function callLLM($messages, $model = null, $options = []) {
    // Add retry tracking to prevent infinite loops
    static $isRetry = false;

    try {
        // Log the prompt
        $timestamp = date('Y-m-d\TH:i:sP');
        $promptLog = $timestamp . "\n";
        foreach ($messages as $message) {
            $promptLog .= "Role: " . $message['role'] . "\nContent: " . $message['content'] . "\n";
        }
        $promptLog .= "\n";
        file_put_contents('/var/www/html/HerikaServer/log/minai_context_sent_to_llm.log', $promptLog, FILE_APPEND);
        minai_log("info", "callLLM: Calling LLM with model: $model");
        // Use provided model or fall back to configured model
        if (!$model && isset($GLOBALS['CONNECTOR']['openrouter']['model'])) {
            $model = $GLOBALS['CONNECTOR']['openrouter']['model'];
        }
        
        if (!$model) {
            minai_log("info", "callLLM: No model specified");
            return null;
        }

        // Get API URL and key from globals
        if (!isset($GLOBALS['CONNECTOR']['openrouter']['url']) || 
            !isset($GLOBALS['CONNECTOR']['openrouter']['API_KEY'])) {
            minai_log("info", "callLLM: Missing OpenRouter configuration");
            return null;
        }

        $url = $GLOBALS['CONNECTOR']['openrouter']['url'];
        $apiKey = $GLOBALS['CONNECTOR']['openrouter']['API_KEY'];

        // Set up headers
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}",
            "HTTP-Referer: https://www.nexusmods.com/skyrimspecialedition/mods/126330",
            "X-Title: CHIM"
        ];

        // Prepare request data
        $data = array_merge([
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $GLOBALS['CONNECTOR']['openrouter']['max_tokens'],
            'temperature' => $GLOBALS['CONNECTOR']['openrouter']['temperature'],
            'stream' => false
        ], $options);

        // Set up request options
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ];

        minai_log("info", "callLLM: Sending request to model: $model");
        
        // Make the request
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            minai_log("info", "callLLM: Request failed");
            return null;
        }

        $response = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            minai_log("info", "callLLM: Invalid JSON response: " . json_last_error_msg());
            return null;
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            minai_log("info", "callLLM: Unexpected response format");
            minai_log("debug", "callLLM: Response: " . json_encode($response));
            SetLLMFallbackProfile();
            return callLLM($messages, $GLOBALS['CONNECTOR']['openrouter']['model'], $options);
        }

        $responseContent = $response['choices'][0]['message']['content'];
        
        // Check if response is valid and we haven't retried yet
        if (!$isRetry && !validateLLMResponse($responseContent)) {
            minai_log("info", "callLLM: Invalid response detected, retrying with fallback profile");
            
            // Set fallback profile
            SetLLMFallbackProfile();
            
            // Set retry flag
            $isRetry = true;
            
            // Retry the call
            return callLLM($messages, $GLOBALS['CONNECTOR']['openrouter']['model'], $options);
        }
        
        // Strip asterisks from gagged speech while preserving action descriptions
        $responseContent = StripGagAsterisks($responseContent);
        
        // Log the response
        $timestamp = date('Y-m-d\TH:i:sP');
        $responseLog = "== $timestamp START\n";
        $responseLog .= $responseContent . "\n";
        $responseLog .= date('Y-m-d\TH:i:sP') . " END\n\n";
        file_put_contents('/var/www/html/HerikaServer/log/minai_output_from_llm.log', $responseLog, FILE_APPEND);

        return $responseContent;

    } catch (Exception $e) {
        minai_log("info", "callLLM Error: " . $e->getMessage());
        minai_log("info", "callLLM Stack Trace: " . $e->getTraceAsString());
        return null;
    }
}

function isPlayerInput() {
    return  in_array($GLOBALS["gameRequest"][0],["inputtext","inputtext_s","ginputtext","ginputtext_s","instruction","init"]);
}




Function GetNarratorConfigPath() {
    // If use symlink, php code is actually in repo folder but included in wsl php server
    // with just dirname((__FILE__)) it was getting directory of repo not php server 
    $path = "/var/www/html/HerikaServer/";
    $newConfFile=md5("Narrator");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

Function SetNarratorProfile() {
    static $narratorProfileCache = null;

    if ($GLOBALS["HERIKA_NAME"] == "The Narrator" && $GLOBALS["use_narrator_profile"]) {
        if (!file_exists(GetNarratorConfigPath())) {
            minai_log("info", "Initializing Narrator Profile");
            createProfile("Narrator", [
                "HERIKA_NAME" => "The Narrator",
                "HERIKA_PERS" => "You are The Narrator in a Skyrim adventure. You will only talk to #PLAYER_NAME#. You refer to yourself as 'The Narrator'. Only #PLAYER_NAME# can hear you. Your goal is to comment on #PLAYER_NAME#'s playthrough, and occasionally, give some hints. NO SPOILERS. Talk about quests and last events."
            ], true);
        }

        // If we haven't loaded the narrator profile yet, load it from file
        if ($narratorProfileCache === null) {
            // First time load - get the profile from file
            global $HERIKA_NAME;
            global $PROMPT_HEAD;
            global $PLAYER_BIOS;
            global $HERIKA_PERS;
            global $HERIKA_DYNAMIC;
            global $DYNAMIC_PROFILE;
            global $RECHAT_H;
            global $RECHAT_P;
            global $BORED_EVENT;
            global $CONTEXT_HISTORY;
            global $HTTP_TIMEOUT;
            global $CORE_LANG;
            global $MAX_WORDS_LIMIT;
            global $BOOK_EVENT_FULL;
            global $LANG_LLM_XTTS;
            global $HERIKA_ANIMATIONS;
            global $EMOTEMOODS;
            global $CONNECTORS;
            global $CONNECTORS_DIARY;
            global $CONNECTOR;
            global $TTSFUNCTION;
            global $TTS;
            global $STT;
            global $ITT;
        global $FEATURES;
            $path = GetNarratorConfigPath();
            $_GET["profile"] = md5("Narrator");
            require_once($path);

            // Store the loaded profile in cache
            $narratorProfileCache = [
                'HERIKA_NAME' => "The Narrator", // Always use "The Narrator"
                'PROMPT_HEAD' => $GLOBALS['PROMPT_HEAD'],
                'PLAYER_BIOS' => $GLOBALS['PLAYER_BIOS'],
                'HERIKA_PERS' => $GLOBALS['HERIKA_PERS'],
                'HERIKA_DYNAMIC' => $GLOBALS['HERIKA_DYNAMIC'],
                'DYNAMIC_PROFILE' => $GLOBALS['DYNAMIC_PROFILE'],
                'RECHAT_H' => $GLOBALS['RECHAT_H'],
                'RECHAT_P' => $GLOBALS['RECHAT_P'],
                'BORED_EVENT' => $GLOBALS['BORED_EVENT'],
                'CONTEXT_HISTORY' => $GLOBALS['CONTEXT_HISTORY'],
                'HTTP_TIMEOUT' => $GLOBALS['HTTP_TIMEOUT'],
                'CORE_LANG' => $GLOBALS['CORE_LANG'],
                'MAX_WORDS_LIMIT' => $GLOBALS['MAX_WORDS_LIMIT'],
                'BOOK_EVENT_FULL' => $GLOBALS['BOOK_EVENT_FULL'],
                'LANG_LLM_XTTS' => $GLOBALS['LANG_LLM_XTTS'],
                'HERIKA_ANIMATIONS' => $GLOBALS['HERIKA_ANIMATIONS'],
                'EMOTEMOODS' => $GLOBALS['EMOTEMOODS'],
                'CONNECTORS' => $GLOBALS['CONNECTORS'],
                'CONNECTORS_DIARY' => $GLOBALS['CONNECTORS_DIARY'],
                'CONNECTOR' => $GLOBALS['CONNECTOR'],
                'TTSFUNCTION' => $GLOBALS['TTSFUNCTION'],
                'TTS' => $GLOBALS['TTS'],
                'STT' => $GLOBALS['STT'],
                'ITT' => $GLOBALS['ITT'],
                'FEATURES' => $GLOBALS['FEATURES']
            ];
        }

        // Always restore from cache (both first time and subsequent times)
        foreach ($narratorProfileCache as $key => $value) {
            $GLOBALS[$key] = $value;
            //$logValue = is_array($value) ? json_encode($value) : $value;
            //minai_log("debug", "Narrator: Setting $key to $logValue");
        }
        
        // Always set these after restoring cache
        $_GET["profile"] = md5("Narrator");
    }
}

Function GetFallbackConfigPath() {
    $path = getcwd().DIRECTORY_SEPARATOR;
    $newConfFile=md5("LLMFallback");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

Function CreateFallbackConfig() {
    if (!file_exists(GetFallbackConfigPath())) {
        minai_log("info", "Initializing LLM Fallback Profile");
        createProfile("LLMFallback", [
            "HERIKA_NAME" => "LLMFallback",
            "HERIKA_PERS" => "This is a LLM profile used for retrying when the primary LLM call fails. Only the connector settings will be used, and it will only work with openrouterjson."
        ], true);
    }
}

Function SetLLMFallbackProfile() {
    static $fallbackProfileCache = null;

    minai_log("info", "Setting LLM Fallback Profile");
    CreateFallbackConfig();

    // If we haven't loaded the fallback profile yet, load it from file
    if ($fallbackProfileCache === null) {
        // First time load - get the profile from file
        global $CONNECTORS;
        global $CONNECTORS_DIARY;
        global $CONNECTOR;

        // Load the fallback profile
        $path = GetFallbackConfigPath();
        // $_GET["profile"] = md5("LLMFallback");
        require_once($path);

        // Store the loaded profile in cache
        $fallbackProfileCache = [
            'CONNECTORS' => $CONNECTORS,
            'CONNECTORS_DIARY' => $CONNECTORS_DIARY,
            'CONNECTOR' => $CONNECTOR
        ];
    }

    // Always restore from cache (both first time and subsequent times)
    foreach ($fallbackProfileCache as $key => $value) {
        $GLOBALS[$key] = $value;
    }

    // Always set these after restoring cache
    // $_GET["profile"] = md5("LLMFallback");
}

function replaceVariables($content, $replacements, $depth = 0) {
    if (empty($content) || $depth > 10) { // Prevent infinite recursion
        return $content;
    }
    
    // Ensure all values are strings
    $stringReplacements = array_map(function($value) {
        return (string)$value;
    }, $replacements);
    
    // Create search array with #variable# format
    $search = array_map(function($key) {
        return "#{$key}#";
    }, array_keys($stringReplacements));
    
    // Do the initial replacement
    $result = str_replace($search, array_values($stringReplacements), $content);
    
    // Look for any remaining #VARIABLE# patterns
    while (strpos($result, '#') !== false && preg_match_all('/#([A-Z_]+)#/', $result, $matches)) {
        $hasReplacement = false;
        foreach ($matches[0] as $match) {
            if (isset($replacements[trim($match, '#')])) {
                $hasReplacement = true;
                break;
            }
        }
        // Only continue if we found a replaceable variable
        if ($hasReplacement) {
            $result = replaceVariables($result, $replacements, $depth + 1);
        } else {
            break; // No more replaceable variables found
        }
    }
    
    return $result;
}


function ExpandPromptVariables($prompt) {
    // Get pronouns for target, Herika, and player
    $targetPronouns = GetActorPronouns($GLOBALS["target"]);
    $herikaPronouns = GetActorPronouns($GLOBALS["HERIKA_NAME"]);
    $playerPronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
    
    $variables = array(
        '#target#' => $GLOBALS["target"],
        '#player_name#' => $GLOBALS["PLAYER_NAME"],
        '#herika_name#' => $GLOBALS["HERIKA_NAME"],
        // Add target pronoun variables
        '#target_subject#' => $targetPronouns["subject"],
        '#target_object#' => $targetPronouns["object"], 
        '#target_possessive#' => $targetPronouns["possessive"],
        // Add Herika pronoun variables
        '#herika_subject#' => $herikaPronouns["subject"],
        '#herika_object#' => $herikaPronouns["object"],
        '#herika_possessive#' => $herikaPronouns["possessive"],
        // Add player pronoun variables
        '#player_subject#' => $playerPronouns["subject"],
        '#player_object#' => $playerPronouns["object"],
        '#player_possessive#' => $playerPronouns["possessive"]
    );
    
    return str_replace(array_keys($variables), array_values($variables), $prompt);
}
