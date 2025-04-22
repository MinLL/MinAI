<?php
// This file contains the functions for parsing the encoded equipment data
// It is used by the equipment_context.php file to parse the equipment data
// and return an array of equipment items.

function ParseEncodedEquipmentData($encodedString) {
  $results = [];
  $currentIndex = 0;
  $length = strlen($encodedString);

  while ($currentIndex < $length) {
      // Parse baseFormId
      $baseFormId = ReadUntilColon($encodedString, $currentIndex);
      // Parse modName
      $modName = ReadUntilColon($encodedString, $currentIndex);
      // Parse slotMask as an integer
      $slotMask = hexdec(ReadUntilColon($encodedString, $currentIndex));

      // Parse keywords as an array of strings, separated by commas
      $keywordsString = ReadUntilColon($encodedString, $currentIndex);
      $keywords = explode(',', $keywordsString);

      // Parse name (format: <length>#<name>)
      $name = ParseName($encodedString, $currentIndex);

      // Determine item types from keywords and slotMask
      $itemTypes = DetermineItemTypes($keywords, $slotMask, $name);

      // Store the parsed data for this segment
      $results[] = [
          'baseFormId' => $baseFormId,
          'modName' => $modName,
          'slotMask' => $slotMask,
          'keywords' => $keywords,
          'name' => $name,
          'itemTypes' => $itemTypes
      ];
  }

  return $results;
}

// Helper function to determine item types based on keywords and slotMask
function DetermineItemTypes($keywords, $slotMask, $itemName) {
  $types = [];
  
  // SlotMask-based type detection
  if ($slotMask & 0x00000001) $types[] = 'helmet'; // HEAD
  if ($slotMask & 0x00000002) $types[] = 'hair'; // Hair
  if ($slotMask & 0x00000004) $types[] = 'body'; // BODY
  if ($slotMask & 0x00000008) $types[] = 'gloves'; // Hands
  if ($slotMask & 0x00000010) $types[] = 'forearms'; // Forearms
  if ($slotMask & 0x00000020) $types[] = 'amulet'; // Amulet
  if ($slotMask & 0x00000040) $types[] = 'ring'; // Ring
  if ($slotMask & 0x00000080) $types[] = 'boots'; // Feet
  if ($slotMask & 0x00000100) $types[] = 'greaves'; // Calves
  if ($slotMask & 0x00000200) $types[] = 'shield'; // SHIELD
  if ($slotMask & 0x00000400) $types[] = 'tail'; // TAIL
  if ($slotMask & 0x00000800) $types[] = 'longhair'; // LongHair
  if ($slotMask & 0x00001000) $types[] = 'circlet'; // Circlet
  if ($slotMask & 0x00002000) $types[] = 'ears'; // Ears
  
  // Keyword-based type detection for standard armor/clothing
  foreach ($keywords as $keyword) {
    $kw = strtolower($keyword);
    
    // Basic vanilla armor types
    if (strpos($kw, 'clothesmonk') !== false) $types[] = 'robes';
    if (strpos($kw, 'clotheswizard') !== false) $types[] = 'robes';
    if (strpos($kw, 'clothesfarm') !== false) $types[] = 'common_clothes';
    if (strpos($kw, 'clothesmerchant') !== false) $types[] = 'fine_clothes';
    if (strpos($kw, 'clothesnoble') !== false) $types[] = 'fine_clothes';
    if (strpos($kw, 'clothespoor') !== false) $types[] = 'common_clothes';
    if (strpos($kw, 'clothesbeggar') !== false) $types[] = 'ragged_clothes';
    
    // Devious Devices (restraints and toys)
    if (strpos($kw, 'zad_devious') !== false) {
      $types[] = 'devious';
      
      // Head restraints
      if (strpos($kw, 'gag') !== false) {
        $types[] = 'gag';
        if (strpos($kw, 'gagpanel') !== false) $types[] = 'panel_gag';
        if (strpos($kw, 'gaglarge') !== false) $types[] = 'large_gag';
        else $types[] = 'mouth_gag';
      }
      if (strpos($kw, 'blindfold') !== false) $types[] = 'blindfold';
      if (strpos($kw, 'hood') !== false) $types[] = 'hood';
      
      // Neck/torso restraints
      if (strpos($kw, 'collar') !== false) $types[] = 'collar';
      if (strpos($kw, 'corset') !== false) $types[] = 'corset';
      if (strpos($kw, 'harness') !== false) $types[] = 'harness';
      if (strpos($kw, 'straitjacket') !== false) $types[] = 'strait_jacket';
      
      // Arm restraints
      if (strpos($kw, 'armbinder') !== false) $types[] = 'armbinder';
      if (strpos($kw, 'armcuffs') !== false) $types[] = 'arm_cuffs';
      if (strpos($kw, 'elbowtie') !== false) $types[] = 'elbow_tie';
      if (strpos($kw, 'yoke') !== false) $types[] = 'restraining_yoke';
      if (strpos($kw, 'gloves') !== false) $types[] = 'bondage_gloves';
      
      // Leg restraints
      if (strpos($kw, 'legcuffs') !== false) $types[] = 'leg_cuffs';
      if (strpos($kw, 'ankleshackles') !== false) $types[] = 'ankle_shackles';
      if (strpos($kw, 'hobbleskirt') !== false) $types[] = 'hobble_skirt';
      if (strpos($kw, 'boots') !== false && !in_array('boots', $types)) $types[] = 'slave_boots';
      
      // Chastity devices
      if (strpos($kw, 'belt') !== false) $types[] = 'chastity_belt';
      if (strpos($kw, 'bra') !== false) $types[] = 'chastity_bra';
      
      // Full body restraints
      if (strpos($kw, 'suit') !== false) {
        $types[] = 'bodysuit';
        if (strpos($kw, 'petsuit') !== false) $types[] = 'pony_gear';
      }
      
      // Insertables
      if (strpos($kw, 'plugvaginal') !== false) $types[] = 'vaginal_plug';
      if (strpos($kw, 'pluganal') !== false) $types[] = 'anal_plug';
      
      // Piercings
      if (strpos($kw, 'piercingsnipple') !== false) {
        $types[] = 'piercing';
        $types[] = 'nipple_piercing';
        $types[] = 'nipple_piercings';
      }
      if (strpos($kw, 'piercingsvaginal') !== false) {
        $types[] = 'piercing';
        $types[] = 'genital_piercing';
        if (strpos($kw, 'clit') !== false) $types[] = 'clitoral_piercing';
        else $types[] = 'labia_piercings';
      }
    }
    
    // Piercings (generic detection)
    if (strpos($kw, 'piercings') !== false) {
      $types[] = 'piercing';
      
      if (strpos($kw, 'nipple') !== false) {
        $types[] = 'nipple_piercing';
        $types[] = 'nipple_piercings';
      }
      if (strpos($kw, 'vaginal') !== false || strpos($kw, 'vulva') !== false) $types[] = 'genital_piercing';
      if (strpos($kw, 'clit') !== false) $types[] = 'clitoral_piercing';
      if (strpos($kw, 'belly') !== false) {
        $types[] = 'belly_piercing';
        $types[] = 'navel_piercing';
      }
    }
    
    // SexLab Aroused (SLA) keywords
    if (strpos($kw, 'sla_') !== false) {
      // Tops and bras
      if (strpos($kw, 'brabikini') !== false) $types[] = 'bra';
      
      // Bottoms
      if (strpos($kw, 'thong') !== false) $types[] = 'thong';
      if (strpos($kw, 'pantiesnormal') !== false) $types[] = 'panties';
      if (strpos($kw, 'pantsnormal') !== false) $types[] = 'pants';
      if (strpos($kw, 'microhotpants') !== false) $types[] = 'hot_pants';
      if (strpos($kw, 'pelviccurtain') !== false) $types[] = 'pelvic_curtain';
      if (strpos($kw, 'fullskirt') !== false) $types[] = 'full_skirt';
      if (strpos($kw, 'miniskirt') !== false) $types[] = 'mini_skirt';
      
      // Full clothing
      if (strpos($kw, 'armorharness') !== false) $types[] = 'body_harness';
      if (strpos($kw, 'armorhalfnaked') !== false) $types[] = 'bikini_armor';
      if (strpos($kw, 'halfnakedbikini') !== false) $types[] = 'bikini';
      if (strpos($kw, 'armorspendex') !== false) $types[] = 'form_fitting_outfit';
      if (strpos($kw, 'armortransparent') !== false) $types[] = 'transparent_outfit';
      if (strpos($kw, 'armorlewdleotard') !== false) $types[] = 'leotard';
      if (strpos($kw, 'armorrubber') !== false) $types[] = 'revealing_attire';
      
      // Footwear
      if (strpos($kw, 'heels') !== false) $types[] = 'high_heels';
      
      // Piercings
      if (strpos($kw, 'piercing') !== false) {
        $types[] = 'piercing';
        if (strpos($kw, 'vulva') !== false) $types[] = 'genital_piercing';
        if (strpos($kw, 'belly') !== false) {
          $types[] = 'belly_piercing';
          $types[] = 'navel_piercing';
        }
        if (strpos($kw, 'nipple') !== false) {
          $types[] = 'nipple_piercing';
          $types[] = 'nipple_piercings';
        }
        if (strpos($kw, 'clit') !== false) $types[] = 'clitoral_piercing';
      }
    }
    
    // Generic clothing/armor types
    if (strpos($kw, 'eroticarmor') !== false) $types[] = 'erotic_armor';
    if (strpos($kw, 'armor') !== false && !in_array('revealing_armor', $types)) $types[] = 'armor';
    if (strpos($kw, 'jewelry') !== false) $types[] = 'jewelry';
  }
  
  // Deduplicate and return
  return array_unique($types);
}

// Helper function to read until the next colon and return the string between
function ReadUntilColon($string, &$currentIndex) {
  $colonPos = strpos($string, ':', $currentIndex);

  if ($colonPos === false) {
      throw new Exception("Missing colon, last read index: $currentIndex");
  }

  $result = substr($string, $currentIndex, $colonPos - $currentIndex);

  $currentIndex = $colonPos + 1;  // Move past the colon
  return $result;
}

// Helper function to parse the name field
function ParseName($string, &$currentIndex) {
  $colonPos = strpos($string, ':', $currentIndex);
  $hashPos = strpos($string, '#', $currentIndex);
  
  // hash not found or hash is after a colon
  if ($hashPos === false || (($hashPos > $colonPos) && $colonPos)) {
    if ($colonPos) {
      // skip to this position + 1
      $currentIndex = $colonPos + 1;
    } else {
      // no more colon ?? or otherwise, bad data, just going to step to the end
      $currentIndex = strlen($string);
    }
    return "";
  }

  // Extract the length of the name
  $nameLengthStr = substr($string, $currentIndex, $hashPos - $currentIndex);
  if (!is_numeric($nameLengthStr)) {
      throw new Exception("Invalid name length: expected an integer, got '$nameLengthStr'");
  }
  $nameLength = (int)$nameLengthStr;

  // Move the index to the start of the actual name
  $currentIndex = $hashPos + 1;

  // Ensure the length of the name is valid
  if ($currentIndex + $nameLength > strlen($string)) {
      throw new Exception("Name length exceeds available string data");
  }

  // Extract the name based on the character length
  $name = substr($string, $currentIndex, $nameLength);

  // Move the index past the name and expect the next colon after the name
  $currentIndex += $nameLength;
  if (
    $currentIndex + 1 < strlen($string) &&
    isset($string[$currentIndex]) && $string[$currentIndex] !== ':'
  ) {
      throw new Exception("Expected colon after name, found '" . $string[$currentIndex] . "'");
  }

  $currentIndex++;  // Move past the colon after the name
  return $name;
}

function EnrichEquipmentDataFromDb(&$parsedData)
{
  // Database connection
  $db = $GLOBALS['db'];

  if (!$db) {
    return;
  }

  foreach ($parsedData as &$segment) {
    $baseFormId = $db->escape($segment['baseFormId']);
    $modName = $db->escape($segment['modName']);
    $name = $db->escape($segment['name']);
    /*if ($name == '') {
      continue;
    }*/
    // Check if the row exists
    $result = $db->fetchAll(
      "SELECT * FROM equipment_description WHERE lower(baseFormId) = lower('{$baseFormId}') AND lower(modName) = lower('{$modName}')"
    );

    if (count($result) > 0) {
      // Row exists, enrich the segment with all the data
      $segment["name"] = $result[0]["name"] ?? '';
      $segment["description"] = $result[0]["description"] ?? '';
      $segment["is_restraint"] = $result[0]["is_restraint"] ?? 0;
      $segment["hidden_by"] = $result[0]["hidden_by"] ?? '';
      $segment["is_enabled"] = $result[0]["is_enabled"] ?? 1;
      $segment["body_part"] = $result[0]["body_part"] ?? '';
      
      // If not already detected as a restraint but marked as one in DB
      if ($segment["is_restraint"] && !in_array('devious', $segment['itemTypes'])) {
        $segment['itemTypes'][] = 'devious';
      }
    } else {
      // Row doesn't exist, perform an insert with default values
      // Auto-detect if it's a restraint based on item types that physically restrict movement
      $isRestraint = (int)(in_array('gag', $segment['itemTypes']) || 
                           in_array('panel_gag', $segment['itemTypes']) ||
                           in_array('large_gag', $segment['itemTypes']) ||
                           in_array('mouth_gag', $segment['itemTypes']) ||
                           in_array('blindfold', $segment['itemTypes']) ||
                           in_array('hood', $segment['itemTypes']) ||
                           in_array('cuffs', $segment['itemTypes']) ||
                           in_array('arm_cuffs', $segment['itemTypes']) ||
                           in_array('leg_cuffs', $segment['itemTypes']) ||
                           in_array('armbinder', $segment['itemTypes']) ||
                           in_array('elbow_tie', $segment['itemTypes']) ||
                           in_array('restraining_yoke', $segment['itemTypes']) ||
                           in_array('strait_jacket', $segment['itemTypes']) ||
                           in_array('hobble_skirt', $segment['itemTypes']) ||
                           in_array('ankle_shackles', $segment['itemTypes']) ||
                           in_array('bondage_gloves', $segment['itemTypes']) ||
                           in_array('slave_boots', $segment['itemTypes']));
      
      // Determine body part and hidden by values
      $bodyPart = DetermineBodyPartFromTypes($segment['itemTypes']);
      $hiddenBy = DetermineHiddenByFromTypes($segment['itemTypes'], $segment['slotMask']);
      
      $insertQuery = "INSERT INTO equipment_description (baseFormId, modName, name, description, is_restraint, hidden_by, is_enabled, body_part)
                      VALUES ('{$baseFormId}', '{$modName}', '{$name}', '', {$isRestraint}, '{$hiddenBy}', 1, '{$bodyPart}')";
      $db->execQuery($insertQuery);
      
      $segment["description"] = '';
      $segment["is_restraint"] = $isRestraint;
      $segment["hidden_by"] = $hiddenBy;
      $segment["is_enabled"] = 1;
      $segment["body_part"] = $bodyPart;
    }
  }
}

// Helper function to determine body part from item types
function DetermineBodyPartFromTypes($itemTypes) {
  if (in_array('helmet', $itemTypes) || in_array('circlet', $itemTypes) || 
      in_array('hood', $itemTypes) || in_array('gag', $itemTypes) || 
      in_array('blindfold', $itemTypes)) {
    return 'head';
  }
  
  if (in_array('body', $itemTypes) || in_array('chest', $itemTypes) || 
      in_array('back', $itemTypes) || in_array('harness', $itemTypes) || 
      in_array('bra', $itemTypes) || in_array('chastity_bra', $itemTypes)) {
    return 'torso';
  }
  
  if (in_array('gloves', $itemTypes) || in_array('gauntlets', $itemTypes) || 
      in_array('bondage_gloves', $itemTypes) || in_array('cuffs', $itemTypes) || 
      in_array('armbinder', $itemTypes)) {
    return 'arms';
  }
  
  if (in_array('boots', $itemTypes) || in_array('greaves', $itemTypes) || 
      in_array('legs', $itemTypes)) {
    return 'legs';
  }
  
  if (in_array('pelvis', $itemTypes) || in_array('chastity_belt', $itemTypes) || 
      in_array('panties', $itemTypes) || in_array('thong', $itemTypes) || 
      in_array('skirt', $itemTypes) || in_array('hotpants', $itemTypes)) {
    return 'groin';
  }
  
  if (in_array('amulet', $itemTypes) || in_array('neck', $itemTypes) || 
      in_array('collar', $itemTypes)) {
    return 'neck';
  }
  
  if (in_array('nipple_piercing', $itemTypes)) {
    return 'chest';
  }
  
  if (in_array('genital_piercing', $itemTypes) || 
      in_array('vaginal_plug', $itemTypes)) {
    return 'groin';
  }
  
  if (in_array('anal_plug', $itemTypes)) {
    return 'device';
  }
  
  if (in_array('piercing', $itemTypes)) {
    return 'piercing';
  }
  
  return '';
}

// Helper function to determine which items would hide the current item based on keywords
function DetermineHiddenByFromTypes($itemTypes, $slotMask) {
  $hideKeywords = [];
  
  // Map item types to actual keywords
  // Devious Devices (zad_) keywords
  if (in_arrayi('nipple_piercing', $itemTypes) || in_arrayi('nipple_piercings', $itemTypes)) {
    // Nipple piercings hidden by chest covering items
    $hideKeywords[] = 'zad_DeviousCorset';
    $hideKeywords[] = 'zad_DeviousHarness';
    $hideKeywords[] = 'zad_DeviousBra';
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'wearing_top';
  }
  
  if (in_arrayi('genital_piercing', $itemTypes) || in_arrayi('clitoral_piercing', $itemTypes) || in_arrayi('labia_piercings', $itemTypes)) {
    // Genital piercings hidden by lower body items
    $hideKeywords[] = 'zad_DeviousBelt';
    $hideKeywords[] = 'wearing_bottom';
    $hideKeywords[] = 'cuirass';
  }
  
  if (in_arrayi('gag', $itemTypes) || in_arrayi('mouth_gag', $itemTypes) || in_arrayi('panel_gag', $itemTypes)) {
    // Gags hidden by hoods
    $hideKeywords[] = 'zad_DeviousHood';
  }
  
  if (in_arrayi('arm_cuffs', $itemTypes) || in_arrayi('wrist_restraints', $itemTypes)) {
    // Arm cuffs hidden by armbinders
    $hideKeywords[] = 'zad_DeviousArmbinder';
    $hideKeywords[] = 'cuirass';
  }
  
  if (in_arrayi('armbinder', $itemTypes)) {
    // Armbinders hidden by straitjackets
    $hideKeywords[] = 'zad_DeviousStraitjacket';
  }
  
  if (in_arrayi('ankle_shackles', $itemTypes) || in_arrayi('leg_cuffs', $itemTypes)) {
    // Leg restraints hidden by hobble skirts
    $hideKeywords[] = 'zad_DeviousHobbleSkirt';
    $hideKeywords[] = 'cuirass';
  }
  
  if (in_arrayi('collar', $itemTypes)) {
    // Collars hidden by hoods and posture collars
    $hideKeywords[] = 'zad_DeviousHood';
    
  }
  
  // SexLab Aroused (sla_) keywords for clothing
  if (in_arrayi('bra', $itemTypes) && !in_arrayi('chastity_bra', $itemTypes)) {
    // Bras hidden by body covering items
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'wearing_top';
  }
  
  if (in_arrayi('panties', $itemTypes) || in_arrayi('thong', $itemTypes)) {
    // Underwear hidden by pants and skirts
    $hideKeywords[] = 'sla_PantsNormal';
    $hideKeywords[] = 'sla_FullSkirt';
    $hideKeywords[] = 'sla_MiniSkirt';
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'wearing_bottom';
  }
  
  if (in_arrayi('bikini', $itemTypes)) {
    // Bikinis hidden by body covering items
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'wearing_top';
  }
  
  if (in_arrayi('belly_piercing', $itemTypes) || in_arrayi('navel_piercing', $itemTypes)) {
    // Belly piercings hidden by body covering items
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'wearing_top';
    $hideKeywords[] = 'zad_DeviousCorset';
  }
  

  if (in_arrayi('anal_plug', $itemTypes) || in_arrayi('vaginal_plug', $itemTypes)) {
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'wearing_bottom';
    $hideKeywords[] = 'zad_DeviousBelt';
  }
  
  // Chastity devices
  if (in_arrayi('chastity_belt', $itemTypes)) {
    // Chastity belts hidden by full body coverage
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'bodysuit';
    $hideKeywords[] = 'zad_DeviousSuit';
    $hideKeywords[] = 'wearing_bottom';
  }
  
  if (in_arrayi('chastity_bra', $itemTypes)) {
    // Chastity bras hidden by full body coverage
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'bodysuit';
    $hideKeywords[] = 'zad_DeviousSuit';
    $hideKeywords[] = 'zad_DeviousStraitjacket';
  }
  
  // Full body restraints
  if (in_arrayi('bodysuit', $itemTypes) || in_arrayi('form_fitting_outfit', $itemTypes)) {
    // Bodysuits generally not hidden by anything except full armor
    $hideKeywords[] = 'cuirass';
  }
  
  if (in_arrayi('corset', $itemTypes)) {
    // Corsets hidden by full body coverage
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'zad_DeviousSuit';
    $hideKeywords[] = 'bodysuit';
    $hideKeywords[] = 'zad_DeviousStraitjacket';
  }
  
  if (in_arrayi('harness', $itemTypes) || in_arrayi('body_harness', $itemTypes)) {
    // Harnesses hidden by most upper body coverage
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'zad_DeviousSuit';
    $hideKeywords[] = 'bodysuit';
    $hideKeywords[] = 'zad_DeviousStraitjacket';
    $hideKeywords[] = 'zad_DeviousCorset';
    $hideKeywords[] = 'wearing_top';
  }
  
  if (in_arrayi('strait_jacket', $itemTypes) || in_arrayi('straitjacket', $itemTypes)) {
    // Straitjackets hidden by full body coverage
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'zad_DeviousSuit';
  }
  
  if (in_arrayi('hobble_skirt', $itemTypes)) {
    // Hobble skirts hidden by full body coverage
    $hideKeywords[] = 'cuirass';
    $hideKeywords[] = 'zad_DeviousSuit';
  }
  
  if (in_arrayi('blindfold', $itemTypes)) {
    // Blindfolds hidden by hoods
    $hideKeywords[] = 'zad_DeviousHood';
    $hideKeywords[] = 'helmet';
  }
  
  if (in_arrayi('slave_boots', $itemTypes)) {

  }
  
  if (in_arrayi('high_heels', $itemTypes)) {

  }
  
  if (in_arrayi('pony_gear', $itemTypes)) {
    // Pony gear generally a comprehensive outfit
    $hideKeywords[] = 'cuirass';
  }

  // Deduplicate and return
  return implode(',', array_unique($hideKeywords));
}

function ProcessEquipment($actorName)
{
  // Only support postgresql for now
  if ($GLOBALS["disable_worn_equipment"] || $GLOBALS["DBDRIVER"] !== "postgresql") {
    return [
      'visibleItems' => [],
      'hiddenItems' => []
    ];
  }

  try {
    // Check if we already have parsed equipment data in the cache
    if (isset($GLOBALS['equipment_cache'][$actorName]) && $GLOBALS['equipment_cache'][$actorName] !== false) {
      $parsedEquipment = $GLOBALS['equipment_cache'][$actorName]['parsed'];
    } else {
      $encodedString = GetActorValue($actorName, "AllWornEquipment");
      $parsedEquipment = ParseEncodedEquipmentData($encodedString);
      
      // Cache the parsed data
      $GLOBALS['equipment_cache'][$actorName] = [
        'parsed' => $parsedEquipment
      ];
      
      // Build keyword cache when first processing equipment
      BuildKeywordCache($actorName, $parsedEquipment);
    }
    
    // Enrich the data with database information
    EnrichEquipmentDataFromDb($parsedEquipment);
    
    // Process into visible and hidden items
    return SeparateVisibleAndHiddenItems($parsedEquipment, $actorName);
  } catch (Exception $e) {
    minai_log("info", "Failed to process equipment: " . $e->getMessage());
    minai_log("info", $e->getTraceAsString());
    return [
      'visibleItems' => [],
      'hiddenItems' => [],
      'revealedStatus' => []
    ];
  }
}

function SeparateVisibleAndHiddenItems($parsedEquipment, $actorName)
{
  $visibleItems = [];
  $hiddenItems = [];
  $revealedStatus = GetRevealedStatus($actorName);
  $wearingTop = $revealedStatus["wearingTop"];
  $wearingBottom = $revealedStatus["wearingBottom"];
  $cuirass = $revealedStatus["cuirass"];
  $gloves = $revealedStatus["gloves"];
  $boots = $revealedStatus["boots"];
  $helmet = $revealedStatus["helmet"];


  foreach ($parsedEquipment as $item) {
    // Skip disabled items
    if (!isset($item['is_enabled']) || !$item['is_enabled']) {
      continue;
    }
    
    // Check if this item is hidden by other items
    $isHidden = false;
    if (!empty($item['hidden_by'])) {
      $hidingItems = explode(',', $item['hidden_by']);
      foreach ($hidingItems as $hidingItem) {
        if ($hidingItem == "cuirass" && $cuirass) {
          $isHidden = true;
          continue;
        }
        if ($hidingItem == "helmet" && $helmet) {
          $isHidden = true;
          continue;
          }
        if ($hidingItem == "gloves" && $gloves) {
          $isHidden = true;
          continue;
        }
        if ($hidingItem == "boots" && $boots) {
          $isHidden = true;
          continue;
        }
        if ($hidingItem == "wearing_top" && $wearingTop) {
          $isHidden = true;
          continue;
        }
        if ($hidingItem == "wearing_bottom" && $wearingBottom) {
          $isHidden = true;
          continue;
        }

        if (IsItemHidden($parsedEquipment, trim($hidingItem))) {
          $isHidden = true;
          break;
        }
      }
    }
    
    // Add to the appropriate array
    if ($isHidden) {
      $hiddenItems[] = FormatEquipmentItem($item);
    } else {
      $visibleItems[] = FormatEquipmentItem($item);
    }
  }
  
  return [
    'visibleItems' => $visibleItems,
    'hiddenItems' => $hiddenItems,
    'revealedStatus' => $revealedStatus
  ];
}

function IsItemHidden($parsedEquipment, $itemIdentifier)
{
  foreach ($parsedEquipment as $item) {
    // Check if this keyword exists in the equipment's keywords
    if (isset($item['keywords']) && is_array($item['keywords'])) {
      foreach ($item['keywords'] as $keyword) {
        if (strtolower($keyword) === strtolower($itemIdentifier)) {
          return true;
        }
      }
    }
  }
  return false;
}

function FormatEquipmentItem($item)
{
  // If no description is set, try to get one from the item types
  $description = $item['description'];
  
  if (empty($description) && !empty($item['itemTypes'])) {
    // Try to find a matching erotic description based on item types
    foreach ($item['itemTypes'] as $itemType) {
      $eroticDescription = GetEroticDeviceDescription($itemType);
      if (!empty($eroticDescription)) {
        // Include the item name with the generic description
        $description = (!empty($item['name']) ? $item['name'] . " - " : "") . $eroticDescription;
        break;
      }
    }
  }
  
  return [
    'baseFormId' => $item['baseFormId'],
    'modName' => $item['modName'],
    'name' => $item['name'],
    'description' => $description,
    'is_restraint' => $item['is_restraint'],
    'hidden_by' => $item['hidden_by'],
    'is_enabled' => $item['is_enabled'],
    'body_part' => $item['body_part'],
    'keywords' => $item['keywords'] ?? [],
    'itemTypes' => $item['itemTypes'] ?? [],
    'slotMask' => $item['slotMask'] ?? 0,
    'formattedDescription' => !empty($description) ? $description : $item['name']
  ];
}

function BuildKeywordCache($actorName, $parsedEquipment) {
  $keywordCache = [];
  
  foreach ($parsedEquipment as $equipment) {
    if (isset($equipment['keywords']) && is_array($equipment['keywords'])) {
      foreach ($equipment['keywords'] as $kw) {
        $keywordCache[strtolower($kw)] = true;
      }
    }
  }
  
  // Store in global equipment cache
  if (!isset($GLOBALS['equipment_cache'][$actorName]) || $GLOBALS['equipment_cache'][$actorName] === false) {
    $GLOBALS['equipment_cache'][$actorName] = ['parsed' => $parsedEquipment];
  }
  
  $GLOBALS['equipment_cache'][$actorName]['keyword_cache'] = $keywordCache;
  return $keywordCache;
}

function HasEquipmentKeyword($actorName, $keyword)
{
  // Check if we have a keyword cache for this actor
  if (!isset($GLOBALS['equipment_cache'][$actorName])) {
    ProcessEquipment($actorName);
  }
  
  if (isset($GLOBALS['equipment_cache'][$actorName]) && 
      $GLOBALS['equipment_cache'][$actorName] !== false) {
      
    // If keyword cache doesn't exist yet, build it
    if (!isset($GLOBALS['equipment_cache'][$actorName]['keyword_cache'])) {
      $parsedEquipment = $GLOBALS['equipment_cache'][$actorName]['parsed'];
      BuildKeywordCache($actorName, $parsedEquipment);
    }
    
    // Check keyword cache (O(1) operation instead of nested loops)
    $keywordLower = strtolower($keyword);
    return isset($GLOBALS['equipment_cache'][$actorName]['keyword_cache'][$keywordLower]);
  }
  
  return false;
}

Function GetRevealedStatus($name) {
  $cuirass = GetActorValue($name, "cuirass", false, true);
  $gloves = GetActorValue($name, "gloves", false, true);
  $boots = GetActorValue($name, "boots", false, true);
  $helmet = GetActorValue($name, "helmet", false, true);

  $wearingBottom = false;
  $wearingTop = false;
  
  // if $eqContext["context"] not empty, then will set ret
  if (!empty($cuirass)) {
      $wearingTop = true;
  }
  if (HasEquipmentKeyword($name, "SLA_HalfNakedBikini")) {
      $wearingTop = true;
  }
  if (HasEquipmentKeyword($name, "SLA_ArmorHalfNaked")) {
      $wearingTop = true;
  }
  if (HasEquipmentKeyword($name, "SLA_Brabikini" )) {
      $wearingTop = true;
  }
  if (HasEquipmentKeyword($name, "SLA_Thong")) {
      $wearingBottom = true;
  }
  if (HasEquipmentKeyword($name, "SLA_PantiesNormal")) {
      $wearingBottom = true;
  }
  if (HasEquipmentKeyword($name, "SLA_PantsNormal")) {
      $wearingBottom = true;
  }
  if (HasEquipmentKeyword($name, "SLA_MicroHotPants")) {
      $wearingBottom = true;
  }
  
  if (HasEquipmentKeyword($name, "SLA_ArmorTransparent")) {
      $wearingBottom = false;
      $wearingTop = false;
  }
  if (HasEquipmentKeyword($name, "SLA_ArmorLewdLeotard")) {
      $wearingBottom = true;
      $wearingTop = true;
  }
  if (HasEquipmentKeyword($name, "SLA_PelvicCurtain")) {
      $wearingBottom = true;
  }
  if (HasEquipmentKeyword($name, "SLA_FullSkirt")) {
      $wearingBottom = true;
  }
  if (HasEquipmentKeyword($name, "SLA_MiniSkirt")) {
      $wearingBottom = true;
  }
  if (HasEquipmentKeyword($name, "EroticArmor")) {
      $wearingBottom = true;
      $wearingTop = true;
  }
  //error_log("DEBUG Actor: $name, wearingTop: $wearingTop, wearingBottom: $wearingBottom");
  return ["wearingTop" => $wearingTop, "wearingBottom" => $wearingBottom, "cuirass" => $cuirass, "gloves" => $gloves, "boots" => $boots, "helmet" => $helmet];
}

function ClearEquipmentCache($actorName = null)
{
  if ($actorName === null) {
    $GLOBALS['equipment_cache'] = [];
  } elseif (isset($GLOBALS['equipment_cache'][$actorName])) {
    unset($GLOBALS['equipment_cache'][$actorName]);
  }
} 



function GetEroticDeviceDescription($deviceType) {
  // Mapping of basic device types to their erotic descriptions
  $descriptions = [
      // Piercings
      "nipple_piercings" => "gleaming rings adorned with pulsing soulgem fragments throb with inner light",
      "nipple_piercing" => "gleaming rings adorned with pulsing soulgem fragments throb with inner light",
      "clitoral_ring" => "exquisite metal ring with embedded soulgem sending waves of pleasure with each movement",
      "clitoral_piercing" => "delicate piercing with pulsing soulgem radiating warmth through sensitive flesh",
      "genital_piercing" => "ornate jewelry with soulgem fragments pulsing in rhythm with heartbeats",
      "labia_piercings" => "ornate jewelry with soulgem fragments pulsing in rhythm with heartbeats",
      "navel_piercing" => "decorative jeweled ornament catching light with each movement",
      "belly_piercing" => "decorative jeweled ornament catching light with each movement",
      
      // Plugs and vibrators
      "vaginal_plug" => "sleek plug with pulsing soulgems radiating warmth inside her pussy",
      "anal_plug" => "firm anal plug with soulgems sending waves of pleasure through surrounding flesh",
      
      // Restraints - Head
      "hood" => "supple leather hood restricting vision and speech while heightening other sensations",
      "mouth_gag" => "jaw-filling gag forcing her mouth open with drool escaping constantly",
      "panel_gag" => "panel sealing the mouth shut while pressing against the tongue",
      "large_gag" => "oversized ball stretching the jaw wide with drool dripping freely",
      "gag" => "speech-preventing device forcing communication through desperate gestures",
      "blindfold" => "soft blindfold plunging its wearer into darkness, heightening other senses",
      
      // Restraints - Arms and upper body
      "arm_cuffs" => "decorative wrist restraints ready for attachment of bindings",
      "cuffs" => "decorative metal restraints ready for attachment of bindings",
      "armbinder" => "leather restraint forcing arms together behind the back, thrusting chest forward",
      "elbow_tie" => "binding pulling elbows together behind the back, forcing chest forward",
      "restraining_yoke" => "rigid frame holding arms out to the sides, leaving the body exposed",
      "yoke" => "neck frame forcing arms apart and body fully exposed",
      "locking_gloves" => "hand coverings preventing fingers from grasping or touching",
      "bondage_gloves" => "tight gloves preventing fingers from grasping or touching",
      "front_cuffs" => "wrist restraints keeping hands visible yet immobile",
      "breast_yoke" => "frame displaying breasts prominently while restraining arms",
      "bondage_mittens" => "hand coverings rendering fingers useless, emphasizing helplessness",
      
      // Restraints - Lower body
      "leg_cuffs" => "ankle restraints ready for attachment of bindings",
      "ankle_shackles" => "chained restraints forcing small, mincing steps",
      "hobble_skirt" => "tight skirt keeping legs pressed together, allowing only tiny steps",
      "relaxed_hobble_skirt" => "restrictive skirt allowing limited movement while emphasizing curves",
      "shackles" => "heavy metal restraints binding limbs together",
      
      // Full body restraints
      "strait_jacket" => "canvas restraint binding arms tightly against the body",
      "harness" => "leather straps wrapping sensually around curves, with rings for additional restraints",
      "bodysuit" => "tight latex clinging to every curve with gleaming surface",
      "corset" => "rigid, tightly-laced garment cinching the waist and forcing breasts upward",
      "heavy_bondage" => "multiple restrictive layers enhancing complete helplessness",
      "pony_gear" => "restraints transforming the wearer into an obedient mount",
      
      // Chastity devices
      "chastity_belt" => "locked metal shield covering the genitals, a constant reminder of denied pleasure",
      "chastity_bra" => "rigid cage encasing breasts, preventing any stimulation of sensitive flesh",
      
      // Clothing - Tops
      "bra" => "supportive garment lifting and framing the breasts",
      
      // Clothing - Bottoms
      "thong" => "narrow strip disappearing between the cheeks, leaving buttocks exposed",
      "panties" => "silky undergarment clinging to intimate curves",
      "pants" => "form-fitting garment hugging every curve of the legs and hips",
      "hot_pants" => "extremely short shorts emphasizing curves of hips and thighs",
      "pelvic_curtain" => "hanging fabric swaying to reveal glimpses beneath",
      "full_skirt" => "flowing garment swishing around the legs",
      "mini_skirt" => "extremely short covering barely hiding the buttocks",
      
      // Full outfits
      "body_harness" => "thin straps wrapping around curves while leaving most skin bare",
      "bikini_armor" => "minimal armor covering only intimate areas",
      "bikini" => "minimal scraps barely covering intimate areas",
      "revealing_attire" => "strategic openings exposing significant portions of the body",
      "revealing_armor" => "minimal protection with maximum skin exposure",
      "form_fitting_outfit" => "clingy garment leaving nothing to imagination",
      "transparent_outfit" => "see-through covering revealing everything beneath",
      "leotard" => "one-piece with high-cut leg openings emphasizing curves",
      
      // Accessories
      "slave_boots" => "locked high-heels with ankle chain forcing dainty steps",
      "boots" => "tall footwear accentuating leg curves",
      "collar" => "lockable collar with ring signifying ownership",
      "high_heels" => "tall, narrow shoes emphasizing hip sway with each step",
      
      // Permissions
      "oral_permission" => "Her mouth is available for use.",
      "anal_permission" => "Her ass is available for use.",
      "vaginal_permission" => "Her pussy is available for use."
  ];
  
  // Return the description if available, otherwise return an empty string
  if (isset($descriptions[$deviceType])) {
      return $descriptions[$deviceType];
  }
  return "";
}