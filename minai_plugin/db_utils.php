<?php
function DropThreadsTableIfExists() {
    $db = $GLOBALS['db'];
    $db->execQuery("DROP TABLE IF EXISTS minai_threads");
}

function CreateThreadsTableIfNotExists() {
    $db = $GLOBALS['db'];
    
    $db->execQuery(
      "CREATE TABLE IF NOT EXISTS minai_threads (
        prev_scene_id character varying(256),
        curr_scene_id character varying(256),
        female_actors text,
        male_actors text,
        victim_actors text,
        thread_id integer PRIMARY KEY,
        framework character varying(256),
        fallback text
      )"
    );
}




function CreateContextTableIfNotExists() {
    $db = $GLOBALS['db'];
    $db->execQuery(
      "CREATE TABLE IF NOT EXISTS custom_context (
        modName TEXT NOT NULL,
        eventKey TEXT NOT NULL,
        eventValue TEXT NOT NULL,
        ttl INT,
        expiresAt INT,
        npcName TEXT NOT NULL,
        PRIMARY KEY (modName, eventKey)
      )"
    );
  }
  
  
  function CreateActionsTableIfNotExists() {
    $db = $GLOBALS['db'];
    $db->execQuery(
      "CREATE TABLE IF NOT EXISTS custom_actions (
        actionName TEXT NOT NULL,
        actionPrompt TEXT NOT NULL,
        targetDescription TEXT NOT NULL,
        targetEnum TEXT NOT NULL,
        enabled INT,
        ttl INT,
        npcName TEXT NOT NULL,
        expiresAt INT,
        PRIMARY KEY (actionName, actionPrompt)
      )"
    );
  }
  

function CreateEquipmentDescriptionTableIfNotExist()
{
  $db = $GLOBALS['db'];
  $db->execQuery(
    "CREATE TABLE IF NOT EXISTS equipment_description (
      baseFormId TEXT NOT NULL,
      modName TEXT NOT NULL,
      name TEXT NOT NULL,
      description TEXT,
      PRIMARY KEY (baseFormId, modName)
    )"
  );
}

function CreateTattooDescriptionTableIfNotExists() {
  $db = $GLOBALS['db'];
  $db->execQuery(
    "CREATE TABLE IF NOT EXISTS tattoo_description (
      section TEXT NOT NULL,
      name TEXT NOT NULL,
      description TEXT,
      hidden_by TEXT,
      PRIMARY KEY (section, name)
    )"
  );
}

function InitiateDBTables() {
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
}

function ResetDBTables() {
    DropThreadsTableIfExists();
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
}
