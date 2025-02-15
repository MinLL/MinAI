<?php

// import AIFF funcction to send eventlog to db
$rootEnginePath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($rootEnginePath.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."chat_helper_functions.php");

function addSexEventsToEventLog($sceneDesc, $threadId) {
    $gameRequest = $GLOBALS["gameRequest"];
    
    logEvent([
        'info_sexscenechange',
        $gameRequest[1],
        $gameRequest[2],
        $sceneDesc." #SEX_SCENARIO #ID_$threadId",
    ]);
}

function updateThreadsDB() {
    $db = $GLOBALS['db'];
    $gameRequest = $GLOBALS["gameRequest"];
    $param = explode("@", $gameRequest[3])[2];
    $obj = json_decode($param, true);
    $vars = explode("@", $GLOBALS["gameRequest"][3]);
    $jsonData = json_decode($param, true);

    if (!$jsonData) {
        error_log("minai: Failed to parse JSON data in updateThreadsDB: $param");
        return;
    }

    $type = $jsonData["type"];
    $framework = $jsonData["framework"];
    $threadId = $jsonData["threadId"];
    $maleActors = $jsonData["maleActors"];
    $femaleActors = $jsonData["femaleActors"];
    $victimActors = $jsonData["victimActors"];
    $scene = $jsonData["scene"];
    $fallback = isset($jsonData["fallback"]) ? $jsonData["fallback"] : "";

    $threadInfo = [
        "type" => $type,
        "framework" => $framework,
        "threadId" => $threadId,
        "maleActors" => $maleActors,
        "femaleActors" => $femaleActors,
        "victimActors" => $victimActors,
        "scene" => $scene,
        "fallback" => $fallback
    ];

    CreateThreadsTableIfNotExists();

    switch(strtolower($type)) {
        // at least in ostim for some reason it fires scenechange event before start thread event
        // to avoid such case handle both cases with same logic.
        case "startthread": 
        case "scenechange": {
            $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId");
            if(!isset($thread)) {
                return;
            }
            $thread = $thread[0];
            if(isset($thread) && strtolower($type) !== "startthread") {
                $currSceneId = $thread["curr_scene_id"];
            
                $db->update('minai_threads', "prev_scene_id = '$currSceneId', curr_scene_id = '$scene', fallback = '$fallback'", "thread_id = $threadId");
            } else {
                $db->delete('minai_threads', "thread_id='{$threadId}'");
                // Ensure victimActors is properly formatted for DB storage
                $victimActors = !empty($victimActors) ? $victimActors : null;
                
                $db->insert('minai_threads', [
                    "thread_id" => $threadId,
                    "curr_scene_id" => $scene,
                    "female_actors" => $femaleActors,
                    "male_actors" => $maleActors,
                    "victim_actors" => $victimActors,
                    "framework" => strtolower($framework),
                    "fallback" => $fallback
                ]);
            }
            $scene = getScene("", $threadId);
            $sceneDesc = $scene["description"];
    
            addSexEventsToEventLog($sceneDesc, $threadId);
            break;
        }
        case "end": {
            $db->delete("minai_threads", "thread_id = $threadId");
            break;
        }
        case "clean": {
            $db->query("DELETE FROM minai_threads");
            break;
        }
    }
};
    
