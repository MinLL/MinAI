<?php

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
    $codename=strtr(strtolower(trim($GLOBALS["db"]->escape($currentName))),[" "=>"_","'"=>"+"]);
    $queryRet = $GLOBALS["db"]->fetchAll("SELECT * from minai_x_personalities WHERE LOWER(id) = LOWER('$codename')");
    $jsonXPersonality = null;
    if ($queryRet)
        $jsonXPersonality =  $queryRet[0]["x_personality"];
    if(isset($jsonXPersonality)) {
        $jsonXPersonality = json_decode($jsonXPersonality,true);
    }

    return $jsonXPersonality;
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

