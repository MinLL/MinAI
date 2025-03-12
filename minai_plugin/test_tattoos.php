<?php
// Test script for tattoo functionality

// Include necessary files
$pluginPath = __DIR__;
require_once("$pluginPath/db_utils.php");
require_once("$pluginPath/logger.php");
$path = "/var/www/html/HerikaServer/";
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
$GLOBALS["db"] = new sql();
$db = $GLOBALS["db"];

// Include the customintegrations.php file which contains the StoreTattooData function
require_once("$pluginPath/customintegrations.php");

// Function to log test results
function test_log($message) {
    echo date('Y-m-d H:i:s') . " - " . $message . "\n";
}

// Test 1: Ensure tables exist
test_log("Test 1: Ensuring tables exist");
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
    
    test_log("Tables created successfully");
} catch (Exception $e) {
    test_log("Error creating tables: " . $e->getMessage());
}

// Test 2: Add a test tattoo description directly
test_log("Test 2: Adding a test tattoo description directly");
try {
    $section = "Test";
    $name = "TestTattoo";
    $description = "A test tattoo for debugging";
    $hiddenBy = "full_body,cuirass";
    
    // Check if it already exists
    $exists = $db->fetchOne(
        "SELECT COUNT(*) FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
    );
    
    if ($exists) {
        // Update
        $result = $db->update(
            'tattoo_description',
            [
                'description' => $db->escape($description),
                'hidden_by' => $db->escape($hiddenBy)
            ],
            "section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
        );
        test_log("Updated existing tattoo description, result: " . ($result ? "Success" : "Failed"));
    } else {
        // Insert
        $result = $db->insert(
            'tattoo_description',
            [
                'section' => $db->escape($section),
                'name' => $db->escape($name),
                'description' => $db->escape($description),
                'hidden_by' => $db->escape($hiddenBy)
            ]
        );
        test_log("Inserted new tattoo description, result: " . ($result ? "Success" : "Failed"));
    }
    
    // Verify
    $tattooInfo = $db->fetchAll(
        "SELECT * FROM tattoo_description WHERE section='" . $db->escape($section) . "' AND name='" . $db->escape($name) . "'"
    );
    
    if ($tattooInfo && count($tattooInfo) > 0) {
        test_log("Verification successful: " . json_encode($tattooInfo[0]));
    } else {
        test_log("Verification failed: No tattoo found");
    }
} catch (Exception $e) {
    test_log("Error in Test 2: " . $e->getMessage());
}

// Test 3: Store tattoo data for a test actor
test_log("Test 3: Storing tattoo data for a test actor");
try {
    $actorName = "TestActor";
    $tattooData = "Test&TestTattoo&FACE&texture1&1&red&0&0&100&0&&&&";
    
    $result = StoreTattooData($actorName, $tattooData);
    test_log("StoreTattooData result: " . ($result ? "Success" : "Failed"));
    
    // Verify actor tattoo data
    $storedData = $db->fetchOne(
        "SELECT tattoo_data FROM actor_tattoos WHERE actor_name='" . $db->escape($actorName) . "'"
    );
    
    if ($storedData) {
        if (is_array($storedData)) {
            $storedData = $storedData['tattoo_data'] ?? '';
        }
        test_log("Verification successful: " . $storedData);
    } else {
        test_log("Verification failed: No data found for actor");
    }
    
    // Verify tattoo description
    $tattooInfo = $db->fetchAll(
        "SELECT * FROM tattoo_description WHERE section='Test' AND name='TestTattoo'"
    );
    
    if ($tattooInfo && count($tattooInfo) > 0) {
        test_log("Tattoo description verification successful: " . json_encode($tattooInfo[0]));
    } else {
        test_log("Tattoo description verification failed: No tattoo found");
    }
} catch (Exception $e) {
    test_log("Error in Test 3: " . $e->getMessage());
}

// Test 4: List all tattoo descriptions
test_log("Test 4: Listing all tattoo descriptions");
try {
    $descriptions = $db->fetchAll("SELECT * FROM tattoo_description");
    test_log("Found " . count($descriptions) . " tattoo descriptions");
    foreach ($descriptions as $index => $desc) {
        test_log("Tattoo " . ($index + 1) . ": " . $desc['section'] . "/" . $desc['name'] . " - " . $desc['description']);
    }
} catch (Exception $e) {
    test_log("Error in Test 4: " . $e->getMessage());
}

// Test 5: List all actors with tattoos
test_log("Test 5: Listing all actors with tattoos");
try {
    $actors = $db->fetchAll("SELECT * FROM actor_tattoos");
    test_log("Found " . count($actors) . " actors with tattoos");
    foreach ($actors as $index => $actor) {
        test_log("Actor " . ($index + 1) . ": " . $actor['actor_name'] . " - Data length: " . strlen($actor['tattoo_data']));
    }
} catch (Exception $e) {
    test_log("Error in Test 5: " . $e->getMessage());
}

test_log("All tests completed"); 