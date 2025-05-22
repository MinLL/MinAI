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
                $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId LIMIT 1");
                if(!isset($thread)) {
                    minai_log("error", "Failed to fetch thread with ID: $threadId");
                    return;
                }
                
                if (isset($thread[0])) {
                    $thread0 = $thread[0];
                }

                if(isset($thread0) && (strtolower($type) !== "startthread")) {
                    if (isset($thread0["curr_scene_id"])) {
                        $currSceneId = $thread0["curr_scene_id"];
                    } else {
                        minai_log("warn", "curr_scene_id not found for thread with ID: $threadId");
                        $currSceneId = 'none';
                    }
                    
                    $db->update('minai_threads', "prev_scene_id = '$currSceneId', curr_scene_id = '$scene', fallback = '{$db->escape($fallback)}'", "thread_id = $threadId");
                    minai_log("info", "Updated existing thread $threadId with new scene: $scene");
                } else {
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
                    minai_log("info", "Inserted new thread with data: " . json_encode($insertData));
                }
                $scene = getScene("", $threadId);
                $sceneDesc = $scene["description"] ?? "";
        
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
    
