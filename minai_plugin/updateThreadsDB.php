<?php

// import AIFF funcction to send eventlog to db
$rootEnginePath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once($rootEnginePath.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."chat_helper_functions.php");

function CreateThreadsTableIfNotExists() {
    $db = $GLOBALS['db'];
    $db->execQuery(
      "CREATE TABLE IF NOT EXISTS minai_threads (
        prev_scene_id character varying(256),
        curr_scene_id character varying(256),
        female_actors text,
        male_actors text,
        thread_id integer PRIMARY KEY,
        framework character varying(256),
        fallback text
      )"
    );
}

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
    global $gameRequest,$db,$GLOBALS;

    CreateThreadsTableIfNotExists();

    $param = explode("@", $gameRequest[3])[2];
    $obj = json_decode($param, true);
    
    $sceneId = $obj["scene"];
    $femaleActors = $obj["femaleActors"];
    $maleActors = $obj["maleActors"];
    $threadId = $obj["threadId"];
    $type = $obj["type"];
    $framework = $obj["framework"];
    $fallback = isset($obj["fallback"]) ? $obj["fallback"] : "";

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
            
                $db->update('minai_threads', "prev_scene_id = '$currSceneId', curr_scene_id = '$sceneId', fallback = '$fallback'", "thread_id = $threadId");
            } else {
                $db->delete('minai_threads', "thread_id='{$threadId}'");
                $db->insert('minai_threads', [
                    "thread_id" => $threadId,
                    "curr_scene_id" => $sceneId,
                    "female_actors" => $femaleActors,
                    "male_actors" => $maleActors,
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
    
