<?php

Function GetTattooContext($name) {
    $db = $GLOBALS['db'];
    $ret = "";
    
    try {
        if ($name == "The Narrator") {
            $name = $GLOBALS["PLAYER_NAME"];
        }
        
        // Get the actor's tattoo data
        $tattooData = $db->fetchOne(
            "SELECT tattoo_data FROM actor_tattoos WHERE LOWER(actor_name)='" . $db->escape(strtolower($name)) . "'"
        );
        
        if (!$tattooData) {
            // error_log("No tattoo data found for " . $name);
            return $ret; // No tattoos for this actor
        }
        
        // Handle case where tattooData is returned as an array
        if (is_array($tattooData)) {
            $tattooData = $tattooData['tattoo_data'] ?? '';
            if (empty($tattooData)) {
                error_log("Tattoo data is empty for " . $name);
                return $ret;
            }
        }
        
        // Get the actor's current equipment to determine what's visible
        $eqContext = GetAllEquipmentContext($name);
        $tmp = GetRevealedStatus($name);
        $wearingBottom = $tmp["wearingBottom"];
        $wearingTop = $tmp["wearingTop"];
        $isNarrator = ($GLOBALS["HERIKA_NAME"] == "The Narrator");
        $cuirass = GetActorValue($name, "cuirass", false, true);
        
        // Get additional equipment values
        $helmet = GetActorValue($name, "helmet", false, true);
        $gloves = GetActorValue($name, "gloves", false, true);
        $boots = GetActorValue($name, "boots", false, true);
        
        // Parse the tattoo data
        $tattoos = explode("~", $tattooData);
        $visibleTattoos = [];
        $hiddenTattoos = [];

        foreach ($tattoos as $tattoo) {
            if (empty(trim($tattoo))) {
                error_log("Empty tattoo entry found");
                continue; // Skip empty entries
            }
            
            $fields = explode("&", $tattoo);
            if (count($fields) < 3) {
                error_log("Invalid tattoo entry: " . $tattoo);
                continue; // Need at least section, name, and area
            }
            
            $section = trim($fields[0]);
            $tattooName = trim($fields[1]);
            $area = strtoupper(trim($fields[2]));
            
            // Skip if section or name is empty
            if (empty($section) || empty($tattooName)) {
                error_log("Empty section or name in tattoo entry: " . $tattoo);
                continue;
            }
            
            // Check for alpha value (9th field, index 8)
            $alpha = 1.0; // Default alpha is 1.0 (fully visible)
            if (count($fields) >= 9) {
                $alpha = floatval($fields[8]);
            }
            
            // Skip tattoos with alpha <= 0 (invisible)
            if ($alpha <= 0) {
                error_log("Skipping invisible tattoo (alpha <= 0): " . $section . "/" . $tattooName);
                continue;
            }
            
            // Get the tattoo description from the database
            $query = "SELECT description, hidden_by FROM tattoo_description WHERE section='" . 
                     $db->escape($section) . "' AND name='" . $db->escape($tattooName) . "' LIMIT 1";
            $tattooInfo = $db->fetchAll($query);
            
            // Set default values if no description found
            $description = "a " . $tattooName . " tattoo";
            $hiddenBy = "";
            
            // Use database values if available
            if ($tattooInfo && count($tattooInfo) > 0) {
                if (!empty($tattooInfo[0]['description'])) {
                    $description = $tattooInfo[0]['description'];
                }
                if (!empty($tattooInfo[0]['hidden_by'])) {
                    $hiddenBy = $tattooInfo[0]['hidden_by'];
                }
            }
            
            // Determine if the tattoo is visible based on body area and clothing
            $isVisible = true;
            
            // If we have specific hiding rules, use only those
            if (!empty($hiddenBy)) {
                // Process specific hiding rules
                $hidingItems = explode(',', $hiddenBy);
                foreach ($hidingItems as $item) {
                    $item = trim($item);
                    if (empty($item)) continue;
                    
                    // Special keywords handling
                    if ($item == "wearing_top" && $wearingTop) {
                      $isVisible = false;
                      break;
                    }
                    else if ($item == "wearing_bottom" && $wearingBottom) { 
                      $isVisible = false;
                      break;
                    }
                    else if ($item == "cuirass" && !empty($cuirass)) {
                        $isVisible = false;
                        break;
                    }
                    else if ($item == "helmet" && !empty($helmet)) {
                      $isVisible = false;
                      break;
                    }
                    else if ($item == "gloves" && !empty($gloves)) {
                      $isVisible = false;
                      break;
                    }
                    else if ($item == "boots" && !empty($boots)) {
                      $isVisible = false;
                      break;
                    }
                    // Check for SLA_ keywords
                    else if (strpos($item, 'SLA_') === 0 && HasKeyword($name, $item)) {
                        $isVisible = false;
                        break;
                    }
                    // Check for zad_ keywords
                    else if (strpos($item, 'zad_') === 0 && HasKeyword($name, $item)) {
                        $isVisible = false;
                        break;
                    }
                    // Check for other equipment keywords
                    else if (HasKeywordAndNotSkip($name, $eqContext, $item)) {
                        $isVisible = false;
                        break;
                    }
                }
            } 
      
            
            // Add to visible tattoos if it's visible
            if ($isVisible) {
                $visibleTattoos[] = $description;
            }
            elseif ($isNarrator) {
              $hiddenTattoos[] = $description;
            }
        }
        
        // Add visible tattoos to the context
        if (!empty($visibleTattoos)) {
            $ret .= "{$name} has the following visible tattoos: " . implode("; ", $visibleTattoos) . ".\n";
        }
        if (!empty($hiddenTattoos)) {
            $ret .= "{$name} has the following tattoos hidden by clothing: " . implode("; ", $hiddenTattoos) . ".\n";
        }
    } catch (Exception $e) {
        error_log("Error in GetTattooContext for {$name}: " . $e->getMessage());
    }
    
    return $ret;
}