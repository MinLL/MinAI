<?php
// require_once("config.php");
require_once("util.php");
require_once("db_utils.php");

/**
 * Add a new item to the database
 * 
 * @param string $item_id The 8 digit hex ID of the item (format: 0x??012345)
 * @param string $file_name The name of the file the item lives in (e.g., "Skyrim.esm")
 * @param string $name The plain text name of the item
 * @param string $description A description of the item
 * @param bool $is_available Whether the item is available for use
 * @param string $category Optional category for the item
 * @return bool True if the item was added successfully, false otherwise
 */
function AddItem($item_id, $file_name, $name, $description, $is_available = true, $category = null) {
    $db = $GLOBALS['db'];
    
    // Process the form ID
    // Add 0x prefix if missing
    if (strpos($item_id, '0x') !== 0) {
        $item_id = '0x' . $item_id;
    }
    
    // If form ID is 8 digits (after 0x prefix), extract just the last 6 digits
    if (strlen($item_id) == 10) { // "0x" + 8 hex digits
        $item_id = '0x' . substr($item_id, 4, 6); // Keep just the last 6 digits with 0x prefix
        minai_log("info", "Truncated form ID to 6 digits: " . $item_id);
    }
    
    // Validate item_id format (0x??012345 or 0x012345)
    if (!preg_match('/^0x[0-9A-Fa-f]{6,8}$/', $item_id)) {
        minai_log("error", "Invalid form ID format: " . $item_id);
        return false;
    }
    
    // Validate file extension
    $valid_extensions = ['esm', 'esp', 'esl'];
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    if (!in_array(strtolower($file_extension), $valid_extensions)) {
        return false;
    }
    
    // Escape inputs
    $item_id = $db->escape($item_id);
    $file_name = $db->escape($file_name);
    $name = $db->escape($name);
    $description = $db->escape($description);
    $is_available = $is_available ? 'TRUE' : 'FALSE';
    $category = $category ? "'" . $db->escape($category) . "'" : 'NULL';
    
    // Check if item already exists
    $result = $db->fetchAll("SELECT id FROM minai_items WHERE item_id = '{$item_id}' AND file_name = '{$file_name}'");
    
    try {
        if (count($result) > 0) {
            // Item exists, update it
            $query = "UPDATE minai_items SET 
                      name = '{$name}', 
                      description = '{$description}', 
                      is_available = {$is_available}, 
                      category = {$category},
                      last_seen = CURRENT_TIMESTAMP
                      WHERE item_id = '{$item_id}' AND file_name = '{$file_name}'";
        } else {
            // Item doesn't exist, insert it
            $query = "INSERT INTO minai_items 
                      (item_id, file_name, name, description, is_available, category) 
                      VALUES 
                      ('{$item_id}', '{$file_name}', '{$name}', '{$description}', {$is_available}, {$category})";
        }
        
        $db->execQuery($query);
        return true; // If we get here, the query was successful
    } catch (Exception $e) {
        error_log("Error in AddItem: " . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing item in the database
 * 
 * @param int $id The ID of the item to update
 * @param array $data Associative array of fields to update
 * @return bool True if the item was updated successfully, false otherwise
 */
function UpdateItem($id, $data) {
    $db = $GLOBALS['db'];
    
    // Escape ID
    $id = intval($id);
    
    // Build update query
    $updates = [];
    
    try {
        if (isset($data['item_id'])) {
            // Process the form ID
            $item_id = $data['item_id'];
            
            // Add 0x prefix if missing
            if (strpos($item_id, '0x') !== 0) {
                $item_id = '0x' . $item_id;
            }
            
            // If form ID is 8 digits (after 0x prefix), extract just the last 6 digits
            if (strlen($item_id) == 10) { // "0x" + 8 hex digits
                $item_id = '0x' . substr($item_id, 4, 6); // Keep just the last 6 digits with 0x prefix
                minai_log("info", "Truncated form ID to 6 digits: " . $item_id);
            }
            
            // Validate item_id format (0x??012345 or 0x012345)
            if (!preg_match('/^0x[0-9A-Fa-f]{6,8}$/', $item_id)) {
                minai_log("error", "Invalid form ID format: " . $item_id);
                return false;
            }
            
            $updates[] = "item_id = '" . $db->escape($item_id) . "'";
        }
        
        if (isset($data['file_name'])) {
            // Validate file extension
            $valid_extensions = ['esm', 'esp', 'esl'];
            $file_extension = pathinfo($data['file_name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($file_extension), $valid_extensions)) {
                return false;
            }
            $updates[] = "file_name = '" . $db->escape($data['file_name']) . "'";
        }
        
        if (isset($data['name'])) {
            $updates[] = "name = '" . $db->escape($data['name']) . "'";
        }
        
        if (isset($data['description'])) {
            $updates[] = "description = '" . $db->escape($data['description']) . "'";
        }
        
        if (isset($data['is_available'])) {
            $is_available = $data['is_available'] ? 'TRUE' : 'FALSE';
            $updates[] = "is_available = {$is_available}";
        }
        
        if (isset($data['category'])) {
            $category = $data['category'] ? "'" . $db->escape($data['category']) . "'" : 'NULL';
            $updates[] = "category = {$category}";
        }
        
        // Add support for item_type field
        if (isset($data['item_type'])) {
            $item_type = $data['item_type'] ? "'" . $db->escape($data['item_type']) . "'" : "'Item'";
            $updates[] = "item_type = {$item_type}";
        }
        
        // Add support for mod_index field
        if (isset($data['mod_index'])) {
            $mod_index = $data['mod_index'] ? "'" . $db->escape($data['mod_index']) . "'" : 'NULL';
            $updates[] = "mod_index = {$mod_index}";
        }
        
        if (empty($updates)) {
            return false;
        }
        
        // Add last_seen update
        $updates[] = "last_seen = CURRENT_TIMESTAMP";
        
        $query = "UPDATE minai_items SET " . implode(", ", $updates) . " WHERE id = {$id}";
        $db->execQuery($query);
        return true;
    } catch (Exception $e) {
        error_log("Error in UpdateItem: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete an item from the database
 * 
 * @param int $id The ID of the item to delete
 * @return bool True if the item was deleted successfully, false otherwise
 */
function DeleteItem($id) {
    $db = $GLOBALS['db'];
    
    try {
        // Escape ID
        $id = intval($id);
        $query = "DELETE FROM minai_items WHERE id = {$id}";
        $db->execQuery($query);
        return true;
    } catch (Exception $e) {
        error_log("Error in DeleteItem: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all items from the database
 * 
 * @param array $filters Optional filters to apply
 * @param string $sort_by Field to sort by
 * @param string $sort_order Sort order (ASC or DESC)
 * @return array Array of items, empty array if no items found or on error
 */
function GetItems($filters = [], $sort_by = 'name', $sort_order = 'ASC') {
    $db = $GLOBALS['db'];
    
    try {
        // Build WHERE clause
        $where_clauses = [];
        
        if (isset($filters['item_id'])) {
            // For form IDs, we need to handle exact matches (when formId starts with 0x)
            // and partial matches (for search functionality)
            $item_id = $db->escape($filters['item_id']);
            if (substr($item_id, 0, 2) === '0x') {
                // This is a complete form ID, do exact match (case-insensitive)
                $where_clauses[] = "UPPER(item_id) = UPPER('{$item_id}')";
            } else {
                // This is a partial ID or search term, use LIKE
                $where_clauses[] = "item_id LIKE '%" . $item_id . "%'";
            }
        }
        
        if (isset($filters['file_name'])) {
            $where_clauses[] = "file_name LIKE '%" . $db->escape($filters['file_name']) . "%'";
        }
        
        if (isset($filters['name'])) {
            $where_clauses[] = "name LIKE '%" . $db->escape($filters['name']) . "%'";
        }
        
        if (isset($filters['description'])) {
            $where_clauses[] = "description LIKE '%" . $db->escape($filters['description']) . "%'";
        }
        
        if (isset($filters['is_available'])) {
            $is_available = $filters['is_available'] ? 'TRUE' : 'FALSE';
            $where_clauses[] = "is_available = {$is_available}";
        }
        
        if (isset($filters['category'])) {
            $where_clauses[] = "category = '" . $db->escape($filters['category']) . "'";
        }
        
        // Build query
        $query = "SELECT * FROM minai_items";
        
        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Validate sort_by
        $valid_sort_fields = ['id', 'item_id', 'file_name', 'name', 'description', 'is_available', 'category', 'last_seen'];
        if (!in_array($sort_by, $valid_sort_fields)) {
            $sort_by = 'name';
        }
        
        // Validate sort_order
        $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
        
        $query .= " ORDER BY {$sort_by} {$sort_order}";
        
        return $db->fetchAll($query);
    } catch (Exception $e) {
        error_log("Error in GetItems: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single item by ID
 * 
 * @param int $id The ID of the item to get
 * @return array|null The item data or null if not found or on error
 */
function GetItemById($id) {
    $db = $GLOBALS['db'];
    
    try {
        // Escape ID
        $id = intval($id);
        $query = "SELECT * FROM minai_items WHERE id = {$id}";
        $result = $db->fetchAll($query);
        return count($result) > 0 ? $result[0] : null;
    } catch (Exception $e) {
        error_log("Error in GetItemById: " . $e->getMessage());
        return null;
    }
}

/**
 * Get items by category
 * 
 * @param string $category The category to filter by
 * @return array Array of items in the specified category, empty array if none found or on error
 */
function GetItemsByCategory($category) {
    $db = $GLOBALS['db'];
    
    try {
        $category = $db->escape($category);
        $query = "SELECT * FROM minai_items WHERE category = '{$category}' ORDER BY name ASC";
        return $db->fetchAll($query);
    } catch (Exception $e) {
        error_log("Error in GetItemsByCategory: " . $e->getMessage());
        return [];
    }
}

/**
 * Get available items
 * 
 * @return array Array of available items, empty array if none found or on error
 */
function GetAvailableItems() {
    $db = $GLOBALS['db'];
    
    try {
        $query = "SELECT * FROM minai_items WHERE is_available = TRUE ORDER BY name ASC";
        return $db->fetchAll($query);
    } catch (Exception $e) {
        error_log("Error in GetAvailableItems: " . $e->getMessage());
        return [];
    }
}


/**
 * Add a new item to the database with mod index support
 * 
 * @param string $item_id The 8 digit hex ID of the item (format: 0x??012345)
 * @param string $file_name The name of the file the item lives in (e.g., "Skyrim.esm")
 * @param string $name The plain text name of the item
 * @param string $description A description of the item
 * @param string $mod_index The mod index (FE, 00, etc.)
 * @param bool $is_available Whether the item is available for use
 * @param string $category Optional category for the item
 * @return bool True if the item was added successfully, false otherwise
 */
function AddItemWithModIndex($item_id, $file_name, $name, $description, $mod_index, $is_available = true, $category = null) {
    $db = $GLOBALS['db'];
    
    // Process the form ID
    // Add 0x prefix if missing
    if (strpos($item_id, '0x') !== 0) {
        $item_id = '0x' . $item_id;
    }
    
    // If form ID is 8 digits (after 0x prefix), extract just the last 6 digits
    if (strlen($item_id) == 10) { // "0x" + 8 hex digits
        $item_id = '0x' . substr($item_id, 4, 6); // Keep just the last 6 digits with 0x prefix
        // minai_log("info", "Truncated form ID to 6 digits: " . $item_id);
    }
    
    // Validate item_id format (0x??012345 or 0x012345)
    if (!preg_match('/^0x[0-9A-Fa-f]{6,8}$/', $item_id)) {
        minai_log("error", "Invalid form ID format: " . $item_id);
        return false;
    }
    
    // Validate file extension
    $valid_extensions = ['esm', 'esp', 'esl'];
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    if (!in_array(strtolower($file_extension), $valid_extensions)) {
        minai_log("error", "Invalid file extension: " . $file_extension . " for file " . $file_name);
        return false;
    }
    
    // Log incoming data for debugging
    // minai_log("debug", "AddItemWithModIndex - Processing: ID={$item_id}, File={$file_name}, Name={$name}, ModIdx={$mod_index}, Category={$category}");
    
    try {
        // Escape inputs
        $item_id = $db->escape($item_id);
        $file_name = $db->escape($file_name);
        $name = $db->escape($name);
        $description = $db->escape($description);
        $mod_index = $db->escape($mod_index);
        $is_available = $is_available ? 'TRUE' : 'FALSE';
        $category = $category ? "'" . $db->escape($category) . "'" : 'NULL';
        
        // Check if item already exists
        $result = $db->fetchAll("SELECT id FROM minai_items WHERE item_id = '{$item_id}' AND file_name = '{$file_name}'");
        
        if (count($result) > 0) {
            // Don't delete items that users may have customized.   
            // minai_log("debug", "Skipping deletion of item: " . $item_id . " from " . $file_name . " because it has been customized.");
            return true;
        }
        else {
            // Item doesn't exist, insert it
            $query = "INSERT INTO minai_items 
                    (item_id, file_name, name, description, is_available, category, mod_index) 
                    VALUES 
                    ('{$item_id}', '{$file_name}', '{$name}', '{$description}', {$is_available}, {$category}, '{$mod_index}')";
        
        $db->execQuery($query);
            minai_log("debug", "Successfully stored item: " . $name . " (" . $item_id . ")");
            return true; // If we get here, the query was successful
        }
    } catch (Exception $e) {
        minai_log("error", "Error in AddItemWithModIndex: " . $e->getMessage() . " for item: " . $item_id . " from " . $file_name);
        return false;
    }
}