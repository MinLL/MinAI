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
    try {
        $db = $GLOBALS['db'];
        $gameRequest = $GLOBALS["gameRequest"];
        $param = explode("@", $gameRequest[3])[2];
        $obj = json_decode($param, true);
        $vars = explode("@", $GLOBALS["gameRequest"][3]);
        $jsonData = json_decode($param, true);

        if (!$jsonData) {
            minai_log("error", "Failed to parse JSON data in updateThreadsDB: $param");
            return;
        }

        $type = $jsonData["type"];
        $framework = $jsonData["framework"];
        $threadId = $jsonData["threadId"];
        
        // Replace "The Narrator" with player name in actor lists
        $playerName = $GLOBALS["PLAYER_NAME"];
        $maleActors = str_ireplace("The Narrator", $playerName, $jsonData["maleActors"]);
        $femaleActors = str_ireplace("The Narrator", $playerName, $jsonData["femaleActors"]);
        $victimActors = str_ireplace("The Narrator", $playerName, $jsonData["victimActors"]);
        
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

        // Log the thread info we're about to process
        minai_log("info", "Processing thread update with data: " . json_encode($threadInfo));

        CreateThreadsTableIfNotExists();

        switch(strtolower($type)) {
            case "startthread": 
            case "scenechange": {
                $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId");
                if(!$thread || sizeof($thread) === 0) {
                    minai_log("info", "No existing thread found with ID: $threadId, creating new thread");
                    // Ensure victimActors is properly formatted for DB storage
                    $victimActors = !empty($victimActors) ? $victimActors : null;
                    
                    $insertData = [
                        "thread_id" => $threadId,
                        "curr_scene_id" => $scene,
                        "female_actors" => $femaleActors,
                        "male_actors" => $maleActors,
                        "victim_actors" => $victimActors,
                        "framework" => strtolower($framework),
                        "fallback" => $fallback
                    ];
                    
                    $db->upsertRowOnConflict('minai_threads', $insertData, 'thread_id');
                    minai_log("info", "Created new thread with data: " . json_encode($insertData));
                } else {
                    $thread = $thread[0];
                    $currSceneId = isset($thread["curr_scene_id"]) ? $thread["curr_scene_id"] : null;
                    
                    $db->update('minai_threads', "prev_scene_id = " . ($currSceneId ? "'$currSceneId'" : "NULL") . ", curr_scene_id = '$scene', fallback = '{$db->escape($fallback)}'", "thread_id = $threadId");
                    minai_log("info", "Updated existing thread $threadId with new scene: $scene");
                }
                $scene = getScene("", $threadId);
                if ($scene === null) {
                    minai_log("error", "Failed to get scene for thread $threadId");
                    return;
                }
                $sceneDesc = getSceneDesc($scene);
                if (empty($sceneDesc)) {
                    minai_log("warning", "No description found for scene in thread $threadId");
                }
        
                addSexEventsToEventLog($sceneDesc, $threadId);
                break;
            }
            case "end": {
                minai_log("info", "Ending thread: $threadId");
                $db->delete("minai_threads", "thread_id = $threadId");
                break;
            }
            case "clean": {
                minai_log("info", "Cleaning all threads from database");
                $db->query("DELETE FROM minai_threads");
                break;
            }
        }
    } catch (Exception $e) {
        minai_log("error", "Error in updateThreadsDB: " . $e->getMessage());
    }
};