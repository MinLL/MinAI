<?php
// We need access to gameRequest here, but it's not global.
// Impl copied from main.php

function CreateContextTableIfNotExists() {
  $db = $GLOBALS['db'];
  $db->execQuery(
    "CREATE TABLE IF NOT EXISTS custom_context (
      modName TEXT NOT NULL,
      eventKey TEXT NOT NULL,
      eventValue TEXT NOT NULL,
      ttl INT,
      expiresAt INT,
      PRIMARY KEY (modName, eventKey)
    )"
  );
}

function SetGameRequest() {
    if (strpos($_SERVER["QUERY_STRING"],"&")===false)
        $receivedData = mb_scrub(base64_decode(substr($_SERVER["QUERY_STRING"],5)));
    else
        $receivedData = mb_scrub(base64_decode(substr($_SERVER["QUERY_STRING"],5,strpos($_SERVER["QUERY_STRING"],"&")-4)));
    $GLOBALS["gameRequest"] = explode("|", $receivedData);
    error_log("minai: Received Data: {$receivedData}");
}


function ProcessIntegrations() {
    // Handle allowing third party mods to register things with the context system
    SetGameRequest();
    CreateContextTableIfNotExists();
    $MUST_DIE=false;
    if (isset($GLOBALS["gameRequest"]) && strtolower($GLOBALS["gameRequest"][0]) == "storecontext") {
        $db = $GLOBALS['db'];
        $vars=explode("@",$GLOBALS["gameRequest"][3]);
        $modName = $vars[0];
        $eventKey = $vars[1];
        $eventValue = $vars[2];
        $ttl = intval($vars[3]);
        error_log("minai: Storing custom context: {$modName}, {$eventKey}, {$eventValue}, {$ttl}");
        $db->delete("custom_context", "modName='".$db->escape($modName)."' AND eventKey='".$db->escape($eventKey)."'");
        $db->insert(
            'custom_context',
            array(
                'modName' => $db->escape($modName),
                'eventKey' => $db->escape($eventKey),
                'eventValue' => $db->escape($eventValue),
                'expiresAt' => time() + $ttl,
                'ttl' => $ttl // already converted to int, no need to escape
            )
        );
        $MUST_DIE=true;
    }
    if ($MUST_DIE) {
        error_log("minai: Done procesing custom request");
        die('X-CUSTOM-CLOSE');
    }
}

function GetThirdpartyContext() {
    $db = $GLOBALS['db'];
    $ret = "";
    $currentTime = time();
    // $db->delete("custom_context", "expiresAt < {$currentTime}");
    $rows = $db->fetchAll(
      "SELECT * FROM custom_context WHERE expiresAt > {$currentTime}"
    );
    foreach ($rows as $row) {
        error_log("minai: Inserting third-party context: {$row["eventvalue"]}");
        $ret .= $row["eventvalue"] . "\n";
    }
    return $ret;
}
?>
