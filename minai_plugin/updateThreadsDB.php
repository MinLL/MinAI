<?php

function CreateThreadsTableIfNotExists() {
    file_put_contents("my_logs.txt", "\nCreateThreadsTableIfNotExists\n", FILE_APPEND);
    $db = $GLOBALS['db'];
    $db->execQuery(
      "CREATE TABLE IF NOT EXISTS minai_threads (
        prev_scene_id character varying(256),
        curr_scene_id character varying(256),
        actors text,
        prev_speed integer,
        curr_speed integer,
        thread_id integer,
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

    file_put_contents("my_logs.txt", "\n\n".strtolower($type)."\n\n", FILE_APPEND);

    switch(strtolower($type)) {
        case "start": {
            $db->delete("minai_threads", "thread_id = $threadId");
            $db->insert('minai_threads', [
                "thread_id" => $threadId,
                "curr_scene_id" => $sceneId,
                "curr_speed" => $speed,
                "actors" => $actors,
                "framework" => $framework,
            ]);
            break;
        }
        case "scenechange": {
            $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId")[0];
            $currSceneId = $thread["curr_scene_id"];
            if($currSceneId) {
                $db->update('minai_threads', "prev_scene_id = '$currSceneId', curr_scene_id = '$sceneId'", "thread_id = $threadId");
            }
            break;
        }
        case "speedchange": {
            $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId")[0];
            if($thread["framework"] == "sexlab") {
                return;
            }
            $currSpeed = $thread["curr_speed"];
            if($thread["curr_scene_id"]) {
                $db->update('minai_threads', "prev_speed = $currSpeed, curr_speed = $speed", "thread_id = $threadId");
            }
            break;
        }
        case "end": {
            $db->delete("minai_threads", "thread_id = $threadId");
            break;
        }
        case "clean": {
            file_put_contents("my_logs.txt", "DB update clean", FILE_APPEND);
            $db->execute("DELETE FROM minai_threads");
            break;
        }
    }
};
    
