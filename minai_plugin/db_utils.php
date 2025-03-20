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
        baseformid TEXT NOT NULL,
        modname TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        is_hidden BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (baseformid, modname)
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
                mod_index TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

function InitiateDBTables() {
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
    CreateItemsTableIfNotExists();
    
    // Seed default items
    SeedDefaultItems();
}

function ResetDBTables() {
    DropThreadsTableIfExists();
    // DropItemRelevanceTableIfExists();
    //DropScenariosTableIfExists();
    DropItemsTableIfExists();
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
    CreateItemsTableIfNotExists();
    SeedDefaultItems();
}

/**
 * Seeds the minai_items table with default items from a JSON file
 */
function SeedDefaultItems() {
    $db = $GLOBALS['db'];
    $default_items_file = __DIR__ . '/default_items.json';
    
    // Check if the JSON file exists
    if (!file_exists($default_items_file)) {
        error_log("Default items file not found: $default_items_file");
        return false;
    }
    
    // Read and decode the JSON file
    $json_content = file_get_contents($default_items_file);
    $items = json_decode($json_content, true);
    
    if (!$items || !is_array($items)) {
        error_log("Invalid JSON format in default items file");
        return false;
    }
    
    // Insert each item into the database
    $inserted_count = 0;
    foreach ($items as $item) {
        try {
            // Check if the item already exists
            $existing = $db->fetchAll(
                "SELECT id FROM minai_items WHERE item_id = '" . $item['item_id'] . "' AND file_name = '" . $item['file_name'] . "'"
            );
            
            if (empty($existing)) {
                // Insert new item using db->insert() method
                $db->insert(
                    'minai_items',
                    array(
                        'item_id' => $item['item_id'],
                        'file_name' => $item['file_name'],
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'item_type' => $item['item_type'] ?? 'Item',
                        'category' => $item['category'] ?? null,
                        'mod_index' => $item['mod_index'] ?? null
                    )
                );
                $inserted_count++;
            }
        } catch (Exception $e) {
            error_log("Error inserting default item '{$item['name']}': " . $e->getMessage());
        }
    }
    
    return $inserted_count;
}

/**
 * Ensures all database schemas are up to date by adding missing columns
 */
function ensureAllDatabaseSchemas() {
    $db = $GLOBALS['db'];
    
    try {
        // Add is_hidden to minai_items if needed
        $query = "SELECT column_name FROM information_schema.columns 
                 WHERE table_name = 'minai_items' AND column_name = 'is_hidden'";
        $result = $db->fetchAll($query);
        if (empty($result)) {
            minai_log("info", "Adding is_hidden column to minai_items table");
            $db->execQuery("ALTER TABLE minai_items ADD COLUMN is_hidden BOOLEAN DEFAULT FALSE");
        }
        
        // Add is_hidden to equipment_description if needed
        $query = "SELECT column_name FROM information_schema.columns 
                 WHERE table_name = 'equipment_description' AND column_name = 'is_hidden'";
        $result = $db->fetchAll($query);
        if (empty($result)) {
            minai_log("info", "Adding is_hidden column to equipment_description table");
            $db->execQuery("ALTER TABLE equipment_description ADD COLUMN IF NOT EXISTS is_hidden BOOLEAN DEFAULT FALSE");
        }
        
        return true;
    } catch (Exception $e) {
        minai_log("error", "Failed to update database schemas: " . $e->getMessage());
        return false;
    }
}
