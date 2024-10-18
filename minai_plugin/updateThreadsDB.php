<?php

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
    $fallback = $obj["fallback"];

    switch(strtolower($type)) {
        // at least in ostim for some reason it fires scenechange event before start thread event
        // to avoid such case handle both cases with same logic.
        case "startthread": 
        case "scenechange": {
            $thread = $db->fetchAll("SELECT * from minai_threads WHERE thread_id = $threadId")[0];
            $currSceneId = $thread["curr_scene_id"];
            $prevSceneId = $thread["prev_scene_id"];
            if($currSceneId && strtolower($type) !== "startthread" && $currSceneId !== $prevSceneId) {
                $db->update('minai_threads', "prev_scene_id = '$currSceneId', curr_scene_id = '$sceneId', fallback = '$fallback'", "thread_id = $threadId");
            } else {
                $db->delete('minai_threads', "thread_id='{$threadId}'");
                $db->insert('minai_threads', [
                    "thread_id" => $threadId,
                    "curr_scene_id" => $sceneId,
                    "female_actors" => $femaleActors,
                    "male_actors" => $maleActors,
                    "framework" => $framework,
                    "fallback" => $fallback
                ]);
            }
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
    
