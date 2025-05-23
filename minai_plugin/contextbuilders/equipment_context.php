<?php
require_once("wornequipment_context.php");

/**
 * Function to provide a unified view of equipment an actor is wearing.
 * This uses the new fields: is_restraint, hidden_by, and is_enabled to simplify processing.
 * 
 * @param string $name The actor's name
 * @param bool $forceNarrator Whether to force narrator-perspective (sees hidden items)
 * @param bool $debug Whether to include debug information
 * @return string A formatted description of all the actor's worn items
 */
Function GetUnifiedEquipmentContext($name, $forceNarrator = false, $debug = false) {
    $ret = "";
    $isNarrator = $forceNarrator || ($GLOBALS["HERIKA_NAME"] == "The Narrator");
    
    // Get equipment data
    $equipment = ProcessEquipment($name);
    if (isset($equipment["revealedStatus"])) 
        $revealedStatus = $equipment["revealedStatus"];
    else
        $revealedStatus = [];
    
    // Get pronouns
    $pronouns = GetActorPronouns($name);
    $their = $pronouns["possessive"];
    
    // Check if the actor is naked
    $isNaked = IsEnabled($name, "isNaked") && !($revealedStatus["wearingTop"] ?? false) && !($revealedStatus["wearingBottom"] ?? false); 
    
    // Check if weapon is drawn
    $isWeaponDrawn = IsEnabled($name, "weaponDrawn");
    
    // Organize items by category
    $categories = CategorizeItems($equipment["visibleItems"], $equipment["hiddenItems"]);
    
    // Count restraints to determine helplessness level
    $visibleRestraints = count($categories["restraints"]["visible"]);
    $totalRestraints = $visibleRestraints + count($categories["restraints"]["hidden"]);
    
    // Determine restraint description
    $restraintDesc = "";
    if ($totalRestraints > 2) {
        $restraintDesc = "thoroughly restrained and helpless";
    } elseif ($totalRestraints > 0) {
        $restraintDesc = "partially restrained";
    }
    
    // Build description based on categories
    
    // Handle nakedness
    if ($isNaked) {
        if (empty($categories["clothing"]["visible"]) && 
            empty($categories["armor"]["head"]) && 
            empty($categories["armor"]["torso"]) && 
            empty($categories["armor"]["arms"]) && 
            empty($categories["armor"]["legs"])) {
            // Truly naked
            $ret .= "- {$name} is completely naked and exposed";
            
            // Add restraint status to nakedness if applicable
            if (!empty($restraintDesc)) {
                $ret .= ", and is " . $restraintDesc;
            }
            
            $ret .= ".\n";
        } else {
            // Naked with some items
            $ret .= "- {$name} is naked";
            
            // Add exceptions for armor pieces
            $exceptions = [];
            if (!empty($categories["armor"]["head"])) $exceptions[] = "headwear";
            if (!empty($categories["armor"]["arms"])) $exceptions[] = "arm protection";
            if (!empty($categories["armor"]["legs"])) $exceptions[] = "leg protection";
            
            if (!empty($exceptions)) {
                $ret .= " except for " . implode(" and ", $exceptions);
            }
            
            // Add restraint status
            if (!empty($restraintDesc)) {
                $ret .= ", and is " . $restraintDesc;
            }
            
            $ret .= ".\n";
        }
    }
    
    // Build clothing/armor description
    $armorPieces = [];
    foreach ($categories["armor"] as $bodyPart => $items) {
        if (!empty($items)) {
            $armorPieces = array_merge($armorPieces, $items);
        }
    }
    
    if (!empty($categories["clothing"]["visible"])) {
        $armorPieces = array_merge($armorPieces, $categories["clothing"]["visible"]);
    }
    
    // Deduplicate armor pieces
    $armorPieces = array_unique($armorPieces);
    
    if (!empty($armorPieces) && !$isNaked) {
        $ret .= "- {$name} is wearing the following:\n";
        $ret .= "  - " . implode("\n  - ", $armorPieces) . "\n";
    }
    
    // Add accessories
    if (!empty($categories["accessories"])) {
        // Deduplicate accessories
        $accessories = array_unique($categories["accessories"]);
        $ret .= "- {$name} is " . ((!$isNaked && !empty($armorPieces)) ? "also " : "") . 
                "wearing the following accessories:\n";
        $ret .= "  - " . implode("\n  - ", $accessories) . "\n";
    }
    
    // Update restraints display
    if (!empty($categories['restraints']['visible'])) {
        // Group similar restraints by their base description
        $groupedRestraints = GroupSimilarItems($categories['restraints']['visible']);
        $formattedRestraints = FormatGroupedItems($groupedRestraints);
        
        if (!empty($formattedRestraints)) {
            if (empty($restraintDesc)) {
                $ret .= "- {$name} " . ((!$isNaked && !empty($armorPieces) || !empty($categories["accessories"])) ? "also " : "") . 
                          "visibly wears the following restraints:\n";
                $ret .= "  - " . implode("\n  - ", $formattedRestraints) . "\n";
            } else {
                // If we already mentioned being restrained, list the specific items
                $ret .= "- {$name} is visibly wearing the following restraints:\n";
                $ret .= "  - " . implode("\n  - ", $formattedRestraints);
                
                // Add restraint status to clothing description if applicable
                if (!empty($restraintDesc)) {
                    $ret .= ", and is " . $restraintDesc;
                }
                
                $ret .= ".\n";
            }
        }
    }
    
    // Add hidden restraints based on perspective
    if (!empty($categories['restraints']['hidden'])) {
        $groupedHiddenRestraints = GroupSimilarItems($categories['restraints']['hidden']);
        $formattedHiddenRestraints = FormatGroupedItems($groupedHiddenRestraints);
        
        if (!empty($formattedHiddenRestraints)) {
            if ($isNarrator) {
                // Player perspective
                $ret .= "- Hidden beneath $their outfit, {$name} is wearing the following restraints:\n";
                $ret .= "  - " . implode("\n  - ", $formattedHiddenRestraints) . "\n";
            }
        }
    }
    
    // Update piercings display
    if (!empty($categories['piercings']['visible'])) {
        $groupedPiercings = GroupSimilarItems($categories['piercings']['visible']);
        $formattedPiercings = FormatGroupedItems($groupedPiercings);
        
        if (!empty($formattedPiercings)) {
            $ret .= "- {$name}'s body is adorned with the following piercings:\n";
            $ret .= "  - " . implode("\n  - ", $formattedPiercings) . "\n";
        }
    }
    
    // Add hidden piercings based on perspective
    if (!empty($categories['piercings']['hidden'])) {
        $groupedHiddenPiercings = GroupSimilarItems($categories['piercings']['hidden']);
        $formattedHiddenPiercings = FormatGroupedItems($groupedHiddenPiercings);
        
        if (!empty($formattedHiddenPiercings)) {
            if ($isNarrator) {
                $ret .= "- Hidden beneath $their outfit, {$name}'s body is adorned with the following piercings:\n";
                $ret .= "  - " . implode("\n  - ", $formattedHiddenPiercings) . "\n";
            }
        }
    }
    
    // Update plugs display
    if (!empty($categories['plugs']['visible'])) {
        $groupedPlugs = GroupSimilarItems($categories['plugs']['visible']);
        $formattedPlugs = FormatGroupedItems($groupedPlugs, true); // Use "and" for last item
        
        if (!empty($formattedPlugs)) {
            $ret .= "- {$name} has the following inserted into $their body:\n";
            $ret .= "  - " . implode("\n  - ", $formattedPlugs) . "\n";
        }
    }
    
    // Add hidden plugs based on perspective
    if (!empty($categories['plugs']['hidden'])) {
        $groupedHiddenPlugs = GroupSimilarItems($categories['plugs']['hidden']);
        $formattedHiddenPlugs = FormatGroupedItems($groupedHiddenPlugs, true); // Use "and" for last item
        
        if (!empty($formattedHiddenPlugs)) {
            if ($isNarrator) {
                $ret .= "- Hidden beneath $their outfit, {$name} can feel the following inserted inside $their body:\n";
                $ret .= "  - " . implode("\n  - ", $formattedHiddenPlugs) . "\n";
            }
        }
    }
    
    // Add hidden clothing for narrator only
    if ($isNarrator && !empty($categories["clothing"]["hidden"])) {
        // Deduplicate hidden clothing
        $hiddenClothing = array_unique($categories["clothing"]["hidden"]);
        $ret .= "- Hidden beneath $their outfit, {$name} is also wearing the following:\n";
        $ret .= "  - " . implode("\n  - ", $hiddenClothing) . "\n";
    }
    
    // Add debug information if requested
    if ($debug) {
        $ret .= "\n### DEBUG INFO\n";
        $ret .= "- Armor (Head): " . count($categories["armor"]["head"]) . "\n";
        $ret .= "- Armor (Torso): " . count($categories["armor"]["torso"]) . "\n";
        $ret .= "- Armor (Arms): " . count($categories["armor"]["arms"]) . "\n";
        $ret .= "- Armor (Legs): " . count($categories["armor"]["legs"]) . "\n";
        $ret .= "- Clothing: " . count($categories["clothing"]["visible"]) . " visible, " . 
                count($categories["clothing"]["hidden"]) . " hidden\n";
        $ret .= "- Restraints: " . count($categories["restraints"]["visible"]) . " visible, " . 
                count($categories["restraints"]["hidden"]) . " hidden " .
                "(" . ($totalRestraints > 0 ? $restraintDesc : "not restrained") . ")\n";
        $ret .= "- Accessories: " . count($categories["accessories"]) . "\n";
        $ret .= "- Piercings: " . count($categories["piercings"]["visible"]) . " visible, " . 
                count($categories["piercings"]["hidden"]) . " hidden\n";
        $ret .= "- Plugs: " . count($categories["plugs"]["visible"]) . " visible, " . 
                count($categories["plugs"]["hidden"]) . " hidden\n";
    }
    
    return $ret;
}

/**
 * Helper function to categorize items based on their itemTypes
 */
function CategorizeItems($visibleItems, $hiddenItems) {
    $categories = [
        "armor" => [
            "head" => [],
            "torso" => [],
            "arms" => [],
            "legs" => []
        ],
        "clothing" => [
            "visible" => [],
            "hidden" => []
        ],
        "restraints" => [
            "visible" => [],
            "hidden" => []
        ],
        "accessories" => [],
        "piercings" => [
            "visible" => [],
            "hidden" => []
        ],
        "plugs" => [
            "visible" => [],
            "hidden" => []
        ]
    ];
    
    // Helper function to add item to category only if not already present
    $addToCategory = function($category, $subCategory, $description) use (&$categories) {
        // Check for duplicates
        if (!in_array($description, $categories[$category][$subCategory])) {
            $categories[$category][$subCategory][] = $description;
        }
    };
    
    // Helper function to add item to flat category only if not already present
    $addToFlatCategory = function($category, $description) use (&$categories) {
        // Check for duplicates
        if (!in_array($description, $categories[$category])) {
            $categories[$category][] = $description;
        }
    };
    
    // Process visible items
    foreach ($visibleItems as $item) {
        $description = !empty($item['description']) ? $item['description'] : $item['name'];
        $types = $item['itemTypes'] ?? [];
        
        // Restraints - only use is_restraint flag
        if (!empty($item['is_restraint'])) {
            // Add restraint type if available
            $restraintType = GetRestraintTypeFromTypes($types);
            if (!empty($restraintType) && stripos($description, $restraintType) === false) {
                $description = $restraintType . " " . $description;
            }
            
            // Clean up description
            $description = trim(str_replace('  ', ' ', $description));
            $addToCategory('restraints', 'visible', $description);
            continue;
        }
        
        // Use direct type matching rather than complex logic
        
        // Plugs
        if (in_array('vaginal_plug', $types) || in_array('anal_plug', $types)) {
            // Add plug type if not in description
            $plugType = in_array('vaginal_plug', $types) ? "vaginal" : 
                       (in_array('anal_plug', $types) ? "anal" : "");
            
            // Make sure we have a meaningful description - at minimum "plug"
            if (trim($description) == "") {
                $description = "plug";
            }
            
            // Only add plug type if it's not already in the description
            if (!empty($plugType) && stripos($description, $plugType) === false) {
                $description = $description . " (" . $plugType . ")";
            }
            
            $addToCategory('plugs', 'visible', $description);
            continue;
        }
        
        // Piercings
        if (in_array('piercing', $types) || in_array('nipple_piercing', $types) || 
            in_array('genital_piercing', $types) || in_array('belly_piercing', $types)) {
            
            // Add piercing location if not in description
            $piercingType = "";
            if (in_array('nipple_piercing', $types) && stripos($description, 'nipple') === false) {
                $piercingType = "nipples";
            } elseif (in_array('genital_piercing', $types) && 
                     stripos($description, 'genital') === false && 
                     stripos($description, 'clitoral') === false && 
                     stripos($description, 'labia') === false) {
                $piercingType = "genital";
            } elseif (in_array('belly_piercing', $types) && 
                     stripos($description, 'belly') === false && 
                     stripos($description, 'navel') === false) {
                $piercingType = "navel";
            }
            
            if (!empty($piercingType)) {
                $description = $description . " (" . $piercingType . ")";
            }
            
            $addToCategory('piercings', 'visible', $description);
            continue;
        }
        
        // Accessories
        if (in_array('amulet', $types) || in_array('ring', $types) || 
            in_array('jewelry', $types) || in_array('circlet', $types)) {
            $addToFlatCategory('accessories', $description);
            continue;
        }
        
        // Head armor
        if (in_array('helmet', $types) || in_array('circlet', $types) || 
            in_array('hood', $types) || stripos($item['name'], 'faceguard') !== false ||
            stripos($item['name'], 'visor') !== false) {
            $addToCategory('armor', 'head', $description);
            continue;
        }
        
        // Torso armor
        if (in_array('body', $types) || in_array('chest', $types) || 
            in_array('back', $types) || in_array('armor', $types) || 
            in_array('revealing_armor', $types) || in_array('erotic_armor', $types) || 
            in_array('bra', $types) || in_array('harness', $types) || 
            in_array('chastity_bra', $types) || in_array('bodysuit', $types) || 
            in_array('leotard', $types)) {
            $addToCategory('armor', 'torso', $description);
            continue;
        }
        
        // Arm armor
        if (in_array('gloves', $types) || in_array('gauntlets', $types) || 
            in_array('bondage_gloves', $types)) {
            $addToCategory('armor', 'arms', $description);
            continue;
        }
        
        // Leg armor
        if (in_array('boots', $types) || in_array('greaves', $types) || 
            (stripos($item['name'], 'boots') !== false) ||
            in_array('legs', $types) || in_array('pants', $types) || 
            in_array('hotpants', $types) || in_array('thong', $types) || 
            in_array('panties', $types) || in_array('skirt', $types) || 
            in_array('pelvis', $types) || in_array('chastity_belt', $types)) {
            $addToCategory('armor', 'legs', $description);
            continue;
        }
        
        // Clothing (catch-all for other clothing types)
        if (in_array('robes', $types) || in_array('common_clothes', $types) || 
            in_array('fine_clothes', $types) || in_array('ragged_clothes', $types) || 
            in_array('bikini', $types)) {
            $addToCategory('clothing', 'visible', $description);
            continue;
        }
        
        // Fallback: Use body_part field if available
        if (!empty($item['body_part'])) {
            switch(strtolower($item['body_part'])) {
                case 'head':
                case 'face':
                case 'hair':
                    $addToCategory('armor', 'head', $description);
                    break;
                case 'torso':
                case 'chest':
                case 'back':
                    $addToCategory('armor', 'torso', $description);
                    break;
                case 'arms':
                case 'hands':
                case 'wrists':
                    $addToCategory('armor', 'arms', $description);
                    break;
                case 'legs':
                case 'feet':
                case 'ankles':
                case 'groin':
                    $addToCategory('armor', 'legs', $description);
                    break;
                case 'accessory':
                case 'neck':
                case 'finger':
                    $addToFlatCategory('accessories', $description);
                    break;
                case 'piercing':
                    $addToCategory('piercings', 'visible', $description);
                    break;
                case 'device':
                case 'plug':
                    $addToCategory('plugs', 'visible', $description);
                    break;
                default:
                    $addToCategory('clothing', 'visible', $description);
            }
            continue;
        }
        
        // Ultimate fallback: any remaining items go to visible clothing
        $addToCategory('clothing', 'visible', $description);
    }
    
    // Process hidden items - simplified categorization
    foreach ($hiddenItems as $item) {
        $description = !empty($item['description']) ? $item['description'] : $item['name'];
        $types = $item['itemTypes'] ?? [];
        
        // Restraints - only use is_restraint flag
        if (!empty($item['is_restraint'])) {
            // Add restraint type if available
            $restraintType = GetRestraintTypeFromTypes($types);
            if (!empty($restraintType) && stripos($description, $restraintType) === false) {
                $description = $restraintType . " " . $description;
            }
            
            // Clean up description
            $description = trim(str_replace('  ', ' ', $description));
            $addToCategory('restraints', 'hidden', $description);
            continue;
        }
        
        // Plugs
        if (in_array('vaginal_plug', $types) || in_array('anal_plug', $types) || 
            (!empty($item['body_part']) && strtolower($item['body_part']) == 'plug')) {
            
            // Add plug type if not in description
            $plugType = in_array('vaginal_plug', $types) ? "vaginal" : 
                       (in_array('anal_plug', $types) ? "anal" : "");
            
            // Make sure we have a meaningful description - at minimum "plug"
            if (trim($description) == "") {
                $description = "plug";
            }
            
            // Only add plug type if it's not already in the description
            if (!empty($plugType) && stripos($description, $plugType) === false) {
                $description = $description . " (" . $plugType . ")";
            }
            
            $addToCategory('plugs', 'hidden', $description);
            continue;
        }
        
        // Piercings
        if (in_array('piercing', $types) || in_array('nipple_piercing', $types) || 
            in_array('genital_piercing', $types) || in_array('belly_piercing', $types) ||
            (!empty($item['body_part']) && strtolower($item['body_part']) == 'piercing')) {
            
            // Add piercing location if not in description
            $piercingType = "";
            if (in_array('nipple_piercing', $types) && stripos($description, 'nipple') === false) {
                $piercingType = "nipples";
            } elseif (in_array('genital_piercing', $types) && 
                     stripos($description, 'genital') === false && 
                     stripos($description, 'clitoral') === false && 
                     stripos($description, 'labia') === false) {
                $piercingType = "genital";
            } elseif (in_array('belly_piercing', $types) && 
                     stripos($description, 'belly') === false && 
                     stripos($description, 'navel') === false) {
                $piercingType = "navel";
            }
            
            if (!empty($piercingType)) {
                $description = $description . " (" . $piercingType . ")";
            }
            
            $addToCategory('piercings', 'hidden', $description);
            continue;
        }
        
        // All other hidden items go to clothing
        $addToCategory('clothing', 'hidden', $description);
    }
    
    return $categories;
}

/**
 * Helper function to get a descriptive restraint type from itemTypes
 */
function GetRestraintTypeFromTypes($types) {
    if (in_array('gag', $types)) return 'gag';
    if (in_array('blindfold', $types)) return 'blindfold';
    if (in_array('hood', $types)) return 'hood';
    if (in_array('collar', $types)) return 'collar';
    if (in_array('armbinder', $types)) return 'armbinder';
    if (in_array('cuffs', $types)) return 'cuffs';
    if (in_array('yoke', $types)) return 'yoke';
    if (in_array('harness', $types)) return 'harness';
    if (in_array('chastity_belt', $types)) return 'chastity belt';
    if (in_array('chastity_bra', $types)) return 'chastity bra';
    if (in_array('bodysuit', $types)) return 'bondage bodysuit';
    if (in_array('bondage_gloves', $types)) return 'bondage gloves';
    
    return '';
}


/**
 * Helper function to group similar items that have the same description
 * @param array $items Array of item strings to group
 * @return array Grouped items by description
 */
function GroupSimilarItems($items) {
  $groupedByDescription = [];
  $descriptionMap = [];
  
  // First pass: extract descriptions and identify standalone descriptions
  foreach ($items as $item) {
    // Check if this is a "name - description" format
    if (strpos($item, ' - ') !== false) {
      list($name, $description) = explode(' - ', $item, 2);
      $descriptionMap[$item] = trim($description);
    } else {
      // If it's a standalone description, add it as is
      $descriptionMap[$item] = trim($item);
    }
  }
  
  // Second pass: group items by description
  foreach ($items as $item) {
    $description = $descriptionMap[$item];
    $added = false;
    
    // Check for exact match with existing description
    foreach ($groupedByDescription as $groupDesc => $groupItems) {
      // If we find an exact description match
      if (trim($description) === trim($groupDesc)) {
        $groupedByDescription[$groupDesc][] = $item;
        $added = true;
        break;
      }
      
      // Check for significant partial match within descriptions
      // This helps match "collar lockable neck restraint" with "lockable neck restraint"
      if (
        (stripos($description, $groupDesc) !== false && strlen($groupDesc) > 15) || 
        (stripos($groupDesc, $description) !== false && strlen($description) > 15)
      ) {
        // Use the longer description as the key
        $newKey = (strlen($groupDesc) > strlen($description)) ? $groupDesc : $description;
        
        // Move existing items to new key if needed
        if ($newKey !== $groupDesc) {
          $groupedByDescription[$newKey] = $groupItems;
          unset($groupedByDescription[$groupDesc]);
        }
        
        $groupedByDescription[$newKey][] = $item;
        $added = true;
        break;
      }
      
      // Check for similarity based on word overlap (for longer descriptions)
      if (strlen($description) > 20 && strlen($groupDesc) > 20) {
        $descWords = explode(' ', strtolower($description));
        $groupWords = explode(' ', strtolower($groupDesc));
        
        // Count common words
        $common = array_intersect($descWords, $groupWords);
        $commonCount = count($common);
        $totalWords = count($descWords) + count($groupWords);
        
        // If more than 60% words are common, consider them similar
        if ($commonCount > 3 && ($commonCount / ($totalWords / 2) > 0.6)) {
          // Use the longer description as the key
          $newKey = (strlen($groupDesc) > strlen($description)) ? $groupDesc : $description;
          
          // Move existing items to new key if needed
          if ($newKey !== $groupDesc) {
            $groupedByDescription[$newKey] = $groupItems;
            unset($groupedByDescription[$groupDesc]);
          }
          
          $groupedByDescription[$newKey][] = $item;
          $added = true;
          break;
        }
      }
    }
    
    // If no match found, create a new group
    if (!$added) {
      $groupedByDescription[$description] = [$item];
    }
  }
  
  return $groupedByDescription;
}

/**
 * Helper function to format grouped items
 * @param array $groupedItems Items grouped by description
 * @param bool $useAndForLast Whether to use "and" before the last item
 * @return array Formatted strings
 */
function FormatGroupedItems($groupedItems, $useAndForLast = false) {
  $result = [];
  
  foreach ($groupedItems as $description => $items) {
    // Skip empty groups
    if (empty($items)) continue;
    
    // Extract names from items with name-description format
    $names = [];
    $standaloneItems = [];
    
    foreach ($items as $item) {
      // If item has a name - description format
      if (strpos($item, ' - ') !== false) {
        list($name, $itemDesc) = explode(' - ', $item, 2);
        if (!empty(trim($name))) {
          $names[] = trim($name);
        }
      } else {
        // This is a standalone description
        $standaloneItems[] = $item;
      }
    }
    
    // Format the output
    if (!empty($names)) {
      // Format name list with commas and optional "and"
      if (count($names) > 1) {
        if ($useAndForLast) {
          $lastItem = array_pop($names);
          $nameList = implode(', ', $names) . ' and ' . $lastItem;
        } else {
          $nameList = implode(', ', $names);
        }
        $result[] = $nameList . ' - ' . $description;
      } else {
        // Single item with name
        $result[] = $names[0] . ' - ' . $description;
      }
    } 
    
    // Add standalone items (descriptions without names)
    foreach ($standaloneItems as $item) {
      // Only add if we don't already have a named item with this description
      if (empty($names) || $item !== $description) {
        $result[] = $item;
      }
    }
  }
  
  return $result;
}
  
  