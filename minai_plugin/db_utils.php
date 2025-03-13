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

function CreateEquipmentDescriptionTableIfNotExist() {
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

function DropItemsTableIfExists() {
    $db = $GLOBALS['db'];
    try {
        $db->execQuery("DROP TABLE IF EXISTS minai_items CASCADE");
    } catch (Exception $e) {
        // Ignore errors during cleanup
    }
}

function CreateItemsTableIfNotExists() {
    $db = $GLOBALS['db'];
    try {
        // Check if table exists first
        $result = $db->fetchAll("SELECT to_regclass('minai_items') as exists");
        if (!$result[0]['exists']) {
            // Create table with SERIAL
            $db->execQuery(
              "CREATE TABLE IF NOT EXISTS minai_items (
                id SERIAL PRIMARY KEY,
                item_id TEXT NOT NULL,
                file_name TEXT NOT NULL,
                name TEXT NOT NULL,
                description TEXT,
                is_available BOOLEAN DEFAULT TRUE,
                item_type TEXT DEFAULT 'Item',
                category TEXT,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(item_id, file_name)
              )"
            );
        }
    } catch (Exception $e) {
        // Log error but don't fail
        error_log("Error creating items table: " . $e->getMessage());
    }
}

function DropScenariosTableIfExists() {
    $db = $GLOBALS['db'];
    try {
        $db->execQuery("DROP TABLE IF EXISTS minai_scenarios CASCADE");
    } catch (Exception $e) {
        // Ignore errors during cleanup
    }
}

function CreateScenariosTableIfNotExists() {
    $db = $GLOBALS['db'];
    try {
        // Check if table exists first
        $result = $db->fetchAll("SELECT to_regclass('minai_scenarios') as exists");
        if (!$result[0]['exists']) {
            // Create table with SERIAL
            $db->execQuery(
              "CREATE TABLE IF NOT EXISTS minai_scenarios (
                id SERIAL PRIMARY KEY,
                name TEXT NOT NULL UNIQUE,
                description TEXT,
                category TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
              )"
            );
        }
    } catch (Exception $e) {
        // Log error but don't fail
        error_log("Error creating scenarios table: " . $e->getMessage());
    }
}

function DropItemRelevanceTableIfExists() {
    $db = $GLOBALS['db'];
    try {
        $db->execQuery("DROP TABLE IF EXISTS minai_item_relevance CASCADE");
    } catch (Exception $e) {
        // Ignore errors during cleanup
    }
}

function CreateItemRelevanceTableIfNotExists() {
    $db = $GLOBALS['db'];
    try {
        // Check if table exists first
        $result = $db->fetchAll("SELECT to_regclass('minai_item_relevance') as exists");
        if (!$result[0]['exists']) {
            // Create table with SERIAL
            $db->execQuery(
              "CREATE TABLE IF NOT EXISTS minai_item_relevance (
                id SERIAL PRIMARY KEY,
                item_id INTEGER NOT NULL REFERENCES minai_items(id) ON DELETE CASCADE,
                scenario_id INTEGER NOT NULL REFERENCES minai_scenarios(id) ON DELETE CASCADE,
                relevance_score DECIMAL(4,2) NOT NULL CHECK (relevance_score >= 0 AND relevance_score <= 100),
                notes TEXT,
                last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(item_id, scenario_id)
              )"
            );
        }
    } catch (Exception $e) {
        // Log error but don't fail
        error_log("Error creating item relevance table: " . $e->getMessage());
    }
}

function InitiateDBTables() {
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
    CreateItemsTableIfNotExists();
    //CreateScenariosTableIfNotExists();
    //CreateItemRelevanceTableIfNotExists();
}

function ResetDBTables() {
    DropThreadsTableIfExists();
    DropItemRelevanceTableIfExists();
    DropScenariosTableIfExists();
    DropItemsTableIfExists();
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
    CreateItemsTableIfNotExists();
    //CreateScenariosTableIfNotExists();
    //CreateItemRelevanceTableIfNotExists();
}
