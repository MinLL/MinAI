<?php

header('Content-Type: application/json');

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
require_once("$pluginPath/db_utils.php");
require_once("$pluginPath/logger.php");
$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
$GLOBALS["db"] = new sql();
$db = $GLOBALS["db"];

// Ensure tables exist
try {
    // Create the tattoo_description table if it doesn't exist
    $db->execQuery("CREATE TABLE IF NOT EXISTS tattoo_description (
        section TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        hidden_by TEXT,
        PRIMARY KEY (section, name)
    )");
    
    // Create the actor_tattoos table if it doesn't exist
    $db->execQuery("CREATE TABLE IF NOT EXISTS actor_tattoos (
        actor_name TEXT NOT NULL,
        tattoo_data TEXT NOT NULL,
        updated_at INTEGER NOT NULL,
        PRIMARY KEY (actor_name)
    )");
} catch (Exception $e) {
    error_log("Error initializing tattoo tables: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database initialization error: ' . $e->getMessage()
    ]);
    exit;
}

// Get all tattoo descriptions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['actor']) && !isset($_GET['all_tattoos'])) {
    // Get all tattoo descriptions
    $tattoos = $db->fetchAll("SELECT * FROM tattoo_description ORDER BY section, name");
    
    // Get all actors with tattoos
    $actors = $db->fetchAll("SELECT actor_name, updated_at FROM actor_tattoos ORDER BY actor_name");
    
    // Return the data
    echo json_encode([
        'success' => true,
        'tattoos' => $tattoos,
        'actors' => $actors
    ]);
}

// Get all unique tattoos across all actors
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['all_tattoos'])) {
    try {
        // Get all actors with tattoos
        $actors = $db->fetchAll("SELECT actor_name, tattoo_data FROM actor_tattoos");
        
        // Process all tattoos to find unique ones
        $uniqueTattoos = [];
        
        foreach ($actors as $actor) {
            $tattooData = $actor['tattoo_data'];
            $tattooItems = explode("~", $tattooData);
            
            foreach ($tattooItems as $tattoo) {
                if (empty(trim($tattoo))) {
                    continue; // Skip empty entries
                }
                
                $fields = explode("&", $tattoo);
                
                // Make sure we have at least the minimum required fields
                if (count($fields) >= 2) {
                    $section = $fields[0];
                    $name = $fields[1];
                    
                    // Skip if section or name is empty
                    if (empty(trim($section)) || empty(trim($name))) {
                        continue;
                    }
                    
                    // Create a unique key for this tattoo
                    $key = $section . '|' . $name;
                    
                    // Add to unique tattoos if not already there
                    if (!isset($uniqueTattoos[$key])) {
                        // Get the tattoo description from the database
                        $tattooInfo = $db->fetchAll(
                            "SELECT description, hidden_by FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "' LIMIT 1"
                        );
                        
                        $description = '';
                        $hiddenBy = '';
                        
                        if ($tattooInfo && count($tattooInfo) > 0) {
                            $description = $tattooInfo[0]['description'];
                            $hiddenBy = $tattooInfo[0]['hidden_by'];
                        } else {
                            $description = "A " . $name . " tattoo";
                            $hiddenBy = "full_body,cuirass";
                        }
                        
                        $uniqueTattoos[$key] = [
                            'section' => $section,
                            'name' => $name,
                            'description' => $description,
                            'hidden_by' => $hiddenBy,
                            'actors' => [$actor['actor_name']],
                            'actor_count' => 1
                        ];
                    } else {
                        // Add this actor to the list of actors with this tattoo
                        if (!in_array($actor['actor_name'], $uniqueTattoos[$key]['actors'])) {
                            $uniqueTattoos[$key]['actors'][] = $actor['actor_name'];
                            $uniqueTattoos[$key]['actor_count']++;
                        }
                    }
                }
            }
        }
        
        // Convert to array for JSON output
        $result = array_values($uniqueTattoos);
        
        // Sort by section, then by name
        usort($result, function($a, $b) {
            // First sort by section
            $sectionCompare = strcmp($a['section'], $b['section']);
            if ($sectionCompare !== 0) {
                return $sectionCompare;
            }
            
            // Then sort by name
            return strcmp($a['name'], $b['name']);
        });
        
        echo json_encode([
            'success' => true,
            'tattoos' => $result,
            'total_count' => count($result)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while retrieving all tattoos: ' . $e->getMessage()
        ]);
    }
}

// Add or update a tattoo description
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log the received data for debugging
    error_log("Tattoo save request received: " . json_encode($data));
    
    if (!isset($data['section']) || !isset($data['name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }
    
    try {
        // Delete any existing tattoo with the same section and name
        $deleteResult = $db->delete(
            'tattoo_description',
            "section='" . $db->escape($data['section']) . "' AND name='" . $db->escape($data['name']) . "'"
        );
        error_log("Delete result: " . json_encode($deleteResult));
        
        // Insert the tattoo (whether it existed before or not)
        $insertResult = $db->insert(
            'tattoo_description',
            [
                'section' => $db->escape($data['section']),
                'name' => $db->escape($data['name']),
                'description' => $db->escape($data['description'] ?? ''),
                'hidden_by' => $db->escape($data['hidden_by'] ?? '')
            ]
        );
        error_log("Insert result: " . json_encode($insertResult));
        
        echo json_encode([
            'success' => true,
            'message' => 'Tattoo description saved successfully'
        ]);
    } catch (Exception $e) {
        // Rollback on error
        $db->rollback();
        
        error_log("Error saving tattoo: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error saving tattoo description: ' . $e->getMessage()
        ]);
    }
}

// Delete a tattoo description
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['section']) || !isset($data['name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }
    
    // Delete the tattoo
    $db->delete(
        'tattoo_description',
        "section='" . $db->escape($data['section']) . "' AND name='" . $db->escape($data['name']) . "'"
    );
    
    echo json_encode([
        'success' => true
    ]);
}

// Import tattoo descriptions
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'import') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tattoos']) || !is_array($data['tattoos'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid import data'
        ]);
        exit;
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Clear existing tattoo descriptions if requested
        if (isset($data['clear']) && $data['clear']) {
            $db->execQuery("DELETE FROM tattoo_description");
        }
        
        // Import the new tattoo descriptions
        foreach ($data['tattoos'] as $tattoo) {
            if (!isset($tattoo['section']) || !isset($tattoo['name'])) {
                continue;
            }
            
            // Check if the tattoo already exists
            $exists = $db->fetchOne(
                "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($tattoo['section']) . "' AND name='" . $db->escape($tattoo['name']) . "'"
            );
            
            if ($exists) {
                // Update the existing tattoo
                $db->update(
                    'tattoo_description',
                    [
                        'description' => $db->escape($tattoo['description'] ?? ''),
                        'hidden_by' => $db->escape($tattoo['hidden_by'] ?? '')
                    ],
                    "section='" . $db->escape($tattoo['section']) . "' AND name='" . $db->escape($tattoo['name']) . "'"
                );
            } else {
                // Insert a new tattoo
                $db->insert(
                    'tattoo_description',
                    [
                        'section' => $db->escape($tattoo['section']),
                        'name' => $db->escape($tattoo['name']),
                        'description' => $db->escape($tattoo['description'] ?? ''),
                        'hidden_by' => $db->escape($tattoo['hidden_by'] ?? '')
                    ]
                );
            }
        }
        
        // Commit the transaction
        $db->commit();
        
        echo json_encode([
            'success' => true
        ]);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $db->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Get tattoo data for a specific actor
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['actor'])) {
    $actorName = strtolower($_GET['actor']);
    
    try {
        // Get the actor's tattoo data
        $tattooData = $db->fetchAll(
            "SELECT tattoo_data FROM actor_tattoos WHERE LOWER(actor_name)='" . $db->escape(strtolower($actorName)) . "'"
        );
        
        if (!$tattooData) {
            echo json_encode([
                'success' => false,
                'message' => 'No tattoo data found for this actor'
            ]);
            exit;
        }
        
        // Parse the tattoo data
        $tattoos = [];
        $tattooItems = explode("~", $tattooData[0]['tattoo_data']);
        
        foreach ($tattooItems as $tattoo) {
            if (empty(trim($tattoo))) {
                continue; // Skip empty entries
            }
            
            $fields = explode("&", $tattoo);
            
            // Make sure we have at least the minimum required fields
            if (count($fields) >= 2) {
                $tattooEntry = [
                    'section' => $fields[0] ?? '',
                    'name' => $fields[1] ?? '',
                    'area' => $fields[2] ?? '',
                    'texture' => $fields[3] ?? '',
                    'slot' => $fields[4] ?? '',
                    'color' => $fields[5] ?? '',
                    'glow' => $fields[6] ?? '',
                    'gloss' => $fields[7] ?? '',
                    'alpha' => $fields[8] ?? '',
                    'locked' => $fields[9] ?? '',
                    'excluded_by' => $fields[10] ?? '',
                    'requires' => $fields[11] ?? '',
                    'requires_plugin' => $fields[12] ?? '',
                    'requires_formid' => $fields[13] ?? '',
                    'domain' => $fields[14] ?? ''
                ];
                
                $tattoos[] = $tattooEntry;
            }
        }
        
        echo json_encode([
            'success' => true,
            'actor' => $actorName,
            'tattoos' => $tattoos
        ]);
    } catch (Exception $e) {
        // Log the error
        error_log("Error retrieving tattoo data for actor $actorName: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while retrieving tattoo data: ' . $e->getMessage()
        ]);
    }
}

// Bulk edit tattoo descriptions
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'bulk_edit') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['section']) || !isset($data['hidden_by'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Update all tattoos in the specified section
        $db->update(
            'tattoo_description',
            [
                'hidden_by' => $db->escape($data['hidden_by'])
            ],
            "section='" . $db->escape($data['section']) . "'"
        );
        
        // Get the count of updated rows
        $updatedCount = $db->fetchOne(
            "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($data['section']) . "'"
        );
        
        // Commit the transaction
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'updated_count' => $updatedCount
        ]);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $db->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Test endpoint to add sample tattoo data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['add_test_data'])) {
    try {
        // Begin transaction
        $db->beginTransaction();
        
        // Add sample tattoo descriptions
        $sampleTattoos = [
            ['section' => 'Face', 'name' => 'War Paint', 'description' => 'Traditional Nordic war paint across the face', 'hidden_by' => 'full_body,hood,helmet'],
            ['section' => 'Arm', 'name' => 'Dragon', 'description' => 'A fierce dragon wrapping around the arm', 'hidden_by' => 'full_body,cuirass,gauntlets'],
            ['section' => 'Back', 'name' => 'Wolf', 'description' => 'A howling wolf tattoo covering the entire back', 'hidden_by' => 'full_body,cuirass'],
            ['section' => 'Chest', 'name' => 'Tribal', 'description' => 'Intricate tribal patterns across the chest', 'hidden_by' => 'full_body,cuirass'],
            ['section' => 'Leg', 'name' => 'Serpent', 'description' => 'A serpent coiling up the leg', 'hidden_by' => 'full_body,boots,greaves']
        ];
        
        foreach ($sampleTattoos as $tattoo) {
            // Check if it already exists
            $exists = $db->fetchOne(
                "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($tattoo['section']) . "' AND name='" . $db->escape($tattoo['name']) . "'"
            );
            
            if (!$exists) {
                $db->insert(
                    'tattoo_description',
                    [
                        'section' => $db->escape($tattoo['section']),
                        'name' => $db->escape($tattoo['name']),
                        'description' => $db->escape($tattoo['description']),
                        'hidden_by' => $db->escape($tattoo['hidden_by'])
                    ]
                );
            }
        }
        
        // Add sample actor tattoo data
        $sampleActors = [
            'Min' => 'Face&War Paint&FACE&texture1&1&red&0&0&100&0&&&&',
            'Lydia' => 'Arm&Dragon&ARM&texture2&2&blue&0&0&100&0&&&&~Back&Wolf&BACK&texture3&3&black&0&0&100&0&&&&',
            'Serana' => 'Chest&Tribal&CHEST&texture4&4&purple&0&0&100&0&&&&~Leg&Serpent&LEG&texture5&5&green&0&0&100&0&&&&'
        ];
        
        foreach ($sampleActors as $actor => $tattooData) {
            // Delete any existing data
            $db->delete("actor_tattoos", "actor_name='" . $db->escape($actor) . "'");
            
            // Insert new data
            $db->insert(
                'actor_tattoos',
                [
                    'actor_name' => $db->escape($actor),
                    'tattoo_data' => $db->escape($tattooData),
                    'updated_at' => time()
                ]
            );
        }
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Test data added successfully'
        ]);
    } catch (Exception $e) {
        // Rollback on error
        $db->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => 'Error adding test data: ' . $e->getMessage()
        ]);
    }
}

// Debug endpoint to check tattoo context
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['debug_context'])) {
    try {
        $actorName = $_GET['debug_context'];
        
        // Include the context builder functions
        require_once("$pluginPath/contextbuilders.php");
        
        // Get the tattoo context for the actor
        $context = GetTattooContext($actorName);
        
        // Get the raw tattoo data
        $tattooData = $db->fetchOne(
            "SELECT tattoo_data FROM actor_tattoos WHERE actor_name='" . $db->escape($actorName) . "'"
        );
        
        // Parse the tattoo data
        $parsedTattoos = [];
        if ($tattooData) {
            $tattoos = explode("~", $tattooData);
            foreach ($tattoos as $tattoo) {
                if (empty(trim($tattoo))) {
                    continue;
                }
                
                $fields = explode("&", $tattoo);
                if (count($fields) >= 3) {
                    $parsedTattoos[] = [
                        'section' => $fields[0],
                        'name' => $fields[1],
                        'area' => $fields[2],
                        'full_data' => $tattoo
                    ];
                }
            }
        }
        
        // Get the tattoo descriptions from the database
        $descriptions = $db->fetchAll("SELECT * FROM tattoo_description");
        
        echo json_encode([
            'success' => true,
            'actor' => $actorName,
            'context' => $context,
            'raw_data' => $tattooData,
            'parsed_tattoos' => $parsedTattoos,
            'descriptions' => $descriptions
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error in debug endpoint: ' . $e->getMessage()
        ]);
    }
}

// Add a tattoo to an actor (for testing)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['add_tattoo']) && isset($_GET['actor'])) {
    try {
        $actorName = strtolower($_GET['actor']);
        $section = isset($_GET['section']) ? $_GET['section'] : 'Face';
        $name = isset($_GET['name']) ? $_GET['name'] : 'War Paint';
        $area = isset($_GET['area']) ? $_GET['area'] : 'FACE';
        
        // Get existing tattoo data for the actor
        $existingData = $db->fetchOne(
            "SELECT tattoo_data FROM actor_tattoos WHERE actor_name='" . $db->escape($actorName) . "'"
        );
        
        // Create new tattoo data
        $newTattoo = "{$section}&{$name}&{$area}&texture1&1&red&0&0&100&0&&&&";
        
        // Combine with existing data if any
        if ($existingData) {
            $tattooData = $existingData . "~" . $newTattoo;
        } else {
            $tattooData = $newTattoo;
        }
        
        // Store the tattoo data
        require_once("$pluginPath/customintegrations.php");
        StoreTattooData($actorName, $tattooData);
        
        echo json_encode([
            'success' => true,
            'message' => "Added {$section}/{$name} tattoo to {$actorName}",
            'tattoo_data' => $tattooData
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error adding tattoo: ' . $e->getMessage()
        ]);
    }
}

// Debug endpoint to test saving a tattoo description directly
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['debug_save_description'])) {
    try {
        $section = isset($_GET['section']) ? $_GET['section'] : 'Face';
        $name = isset($_GET['name']) ? $_GET['name'] : 'War Paint';
        $description = isset($_GET['description']) ? $_GET['description'] : 'A fierce war paint design across the face';
        $hiddenBy = isset($_GET['hidden_by']) ? $_GET['hidden_by'] : 'full_body,hood,helmet';
        
        // Check if the tattoo already exists
        $exists = $db->fetchOne(
            "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
        );
        
        if ($exists) {
            // Update the existing tattoo
            $result = $db->update(
                'tattoo_description',
                [
                    'description' => $db->escape($description),
                    'hidden_by' => $db->escape($hiddenBy)
                ],
                "section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
            );
            
            $message = "Updated existing tattoo description";
        } else {
            // Insert a new tattoo
            $result = $db->insert(
                'tattoo_description',
                [
                    'section' => $db->escape($section),
                    'name' => $db->escape($name),
                    'description' => $db->escape($description),
                    'hidden_by' => $db->escape($hiddenBy)
                ]
            );
            
            $message = "Added new tattoo description";
        }
        
        // Get the current description to verify
        $currentDesc = $db->fetchAll(
            "SELECT * FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
        );
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'operation_result' => $result,
            'current_description' => $currentDesc
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error in debug save: ' . $e->getMessage()
        ]);
    }
}

// Debug endpoint to check the current state of the tattoo_description table
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['debug_check_descriptions'])) {
    try {
        // Check if the table exists
        $tableExists = $db->fetchOne("SELECT name FROM sqlite_master WHERE type='table' AND name='tattoo_description'");
        
        if (!$tableExists) {
            echo json_encode([
                'success' => false,
                'message' => 'The tattoo_description table does not exist',
                'action' => 'Creating table now'
            ]);
            
            // Create the table
            $db->execQuery("CREATE TABLE IF NOT EXISTS tattoo_description (
                section TEXT NOT NULL,
                name TEXT NOT NULL,
                description TEXT,
                hidden_by TEXT,
                PRIMARY KEY (section, name)
            )");
        }
        
        // Get all tattoo descriptions
        $descriptions = $db->fetchAll("SELECT * FROM tattoo_description ORDER BY section, name");
        
        // Get table structure
        $tableInfo = $db->fetchAll("PRAGMA table_info(tattoo_description)");
        
        // Get all actors with tattoos
        $actors = $db->fetchAll("SELECT actor_name, tattoo_data FROM actor_tattoos");
        
        echo json_encode([
            'success' => true,
            'table_exists' => !empty($tableExists),
            'table_structure' => $tableInfo,
            'descriptions_count' => count($descriptions),
            'descriptions' => $descriptions,
            'actors_with_tattoos' => $actors
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking descriptions: ' . $e->getMessage()
        ]);
    }
} 