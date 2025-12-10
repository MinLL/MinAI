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
        is_restraint INTEGER DEFAULT 0,
        body_part TEXT,
        hidden_by TEXT,
        is_enabled INTEGER DEFAULT 1,
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
                mod_index TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(item_id, file_name)
              )"
            );
            error_log("MinAI create table: minai_items - exec trace"); //debug
        }
    } catch (Exception $e) {
        // Log error but don't fail
        error_log("Error creating items table: " . $e->getMessage());
    }
}

function UpdateSpeechTableIfNotHaveEmotionFields() {
    $db = $GLOBALS['db'];
    try {
        $query = "
        ALTER TABLE speech ADD COLUMN IF NOT EXISTS mood TEXT; 
        ALTER TABLE speech ADD COLUMN IF NOT EXISTS emotion TEXT; 
        ALTER TABLE speech ADD COLUMN IF NOT EXISTS emotion_intensity TEXT; 
        ";
        $db->execQuery($query);        
        //error_log("MinAI alter table 'speech' - exec trace"); //debug
    } catch (Exception $e) {
        // Log error but don't fail
        error_log("Error altering 'speech' table: " . $e->getMessage());
    }
}

//----------------------------------------------------
// database maintenance tools
// - autovacuum / table
//----------------------------------------------------

function SetAutoVacuum() {

    $db = $GLOBALS['db'];

    $checkVersion2 = function($tablename) {
        $db = $GLOBALS['db'];
        $query = "
        SELECT version 
        FROM public.database_versioning
        WHERE tablename = '$tablename'
        ";

        $existsColumn=$db->fetchAll($query);

        if (sizeof($existsColumn) == 0 || !$existsColumn[0]["version"] )
            return -1;
        else
            return intval($existsColumn[0]["version"]);
    };

    $updateVersion2 = function($tablename,$version) {
        $db = $GLOBALS['db'];
        $db->execQuery("INSERT INTO public.database_versioning SELECT '$tablename',$version where not exists (SELECT 1 from public.database_versioning where tablename='$tablename')");
        $db->execQuery("UPDATE public.database_versioning set version=$version WHERE tablename='$tablename'");
        //Logger::info("TABLE $tablename updated to version $version");
        error_log("TABLE $tablename updated to version $version"); //debug
        
    };

    if ($checkVersion2("db_maintenance")<20251129002) {
        //Logger::debug(" try patch: db_maintenance 20251129002");
        error_log(" try patch: db_maintenance 20251129002"); //debug

        try {
            $db->execQuery("DROP FUNCTION IF EXISTS public.sql_exec2(text) CASCADE");

            $db->execQuery("
            CREATE FUNCTION public.sql_exec2(text) returns text 
            language plpgsql volatile 
            AS 
            $$
                BEGIN
                  EXECUTE $1;
                  RETURN $1;
                END;
            $$; 
            ");


            $db->execQuery("SELECT public.sql_exec2('ALTER TABLE '||quote_ident(pgn.nspname)||'.'||quote_ident(pgc.relname)||' SET (autovacuum_enabled = on, toast.autovacuum_enabled = on);') 
                FROM pg_catalog.pg_class pgc 
                LEFT JOIN pg_catalog.pg_namespace pgn ON pgn.oid = pgc.relnamespace 
                WHERE (pgc.relkind ='r') 
                AND (pgn.nspname='public'); "); 

            $updateVersion2("db_maintenance",20251129002);

        } catch (Exception $e) {
            error_log("Error altering 'speech' table: " . $e->getMessage());
        }

        //Logger::info("Applied patch db_maintenance 20251129002");
        error_log("Applied patch db_maintenance 20251129002"); //debug
    }
}


function InitiateDBTables() {
    CreateThreadsTableIfNotExists();
    CreateActionsTableIfNotExists();
    CreateContextTableIfNotExists();
    CreateEquipmentDescriptionTableIfNotExist();
    CreateTattooDescriptionTableIfNotExists();
    CreateItemsTableIfNotExists();
    UpdateSpeechTableIfNotHaveEmotionFields();
    // Seed default items
    SeedDefaultItems();
    error_log("MinAI InitiateDBTables - exec trace"); //debug
}

function ResetDBTables() {
    DropThreadsTableIfExists();
    //DropItemRelevanceTableIfExists();
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
