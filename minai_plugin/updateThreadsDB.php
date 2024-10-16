<?php

function CreateThreadsTableIfNotExists() {
    $db = $GLOBALS['db'];
    $db->execQuery(
      "CREATE TABLE IF NOT EXISTS minai_threads (
        prev_scene_id character varying(256),
        curr_scene_id character varying(256),
        actors text,
        thread_id integer PRIMARY KEY,
        framework character varying(256),
        fallback text
      )"
    );
}

function updateThreadsDB() {
    global $gameRequest,$db,$GLOBALS;

    CreateThreadsTableIfNotExists();

    $param = explode("@", $gameRequest[3])[2];
    $obj = json_decode($param, true);
    
    $sceneId = $obj["scene"];
    $actors = $obj["actors"];
    $threadId = $obj["threadId"];
    $speed = $obj["speed"];
    $type = $obj["type"];
    $framework = $obj["framework"];
    $fallback = $obj["fallback"];

    switch(strtolower($type)) {
        // at least in ostim for some reason it fires scenechange event before start thread event
        // to avoid such case handle both cases with same logic.
        case "startthread": 
        case "scenechange": {
            file_put_contents("my_logs.txt", "\n Start or change scene\n", FILE_APPEND);
            $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId")[0];
            $currSceneId = $thread["curr_scene_id"];
            if($currSceneId && strtolower($type) !== "startthread") {
                file_put_contents("my_logs.txt", "\n Update\n", FILE_APPEND);
                $db->update('minai_threads', "prev_scene_id = '$currSceneId', curr_scene_id = '$sceneId', fallback = '$fallback'", "thread_id = $threadId");
            } else {
                file_put_contents("my_logs.txt", "\n Create\n".json_encode([
                    "thread_id" => $threadId,
                    "curr_scene_id" => $sceneId,
                    "actors" => $actors,
                    "framework" => $framework,
                    "fallback" => $fallback
                ]), FILE_APPEND);
                $db->insert('minai_threads', [
                    "thread_id" => $threadId,
                    "curr_scene_id" => $sceneId,
                    "actors" => $actors,
                    "framework" => $framework,
                    "fallback" => $fallback
                ]);
            }
            break;
        }
        // case "speedchange": {
        //     $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId")[0];
        //     if($thread["framework"] == "sexlab") {
        //         return;
        //     }
        //     $currSpeed = $thread["curr_speed"];
        //     if($thread["curr_scene_id"]) {
        //         $db->update('minai_threads', "prev_speed = $currSpeed, curr_speed = $speed", "thread_id = $threadId");
        //     }
        //     break;
        // }
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
    
