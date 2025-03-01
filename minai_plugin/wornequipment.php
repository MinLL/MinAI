<?php

require_once("config.php");
require_once("util.php");

function ParseEncodedEquipmentData($encodedString) {
  $results = [];
  $currentIndex = 0;
  $length = strlen($encodedString);

  while ($currentIndex < $length) {
      // Parse baseFormId
      $baseFormId = readUntilColon($encodedString, $currentIndex);
      // Parse modName
      $modName = ReadUntilColon($encodedString, $currentIndex);
      // Parse slotMask as an integer
      $slotMask = hexdec(ReadUntilColon($encodedString, $currentIndex));

      // Parse keywords as an array of strings, separated by commas
      $keywordsString = ReadUntilColon($encodedString, $currentIndex);
      $keywords = explode(',', $keywordsString);

      // Parse name (format: <length>#<name>)
      $name = ParseName($encodedString, $currentIndex);

      // Store the parsed data for this segment
      $results[] = [
          'baseFormId' => $baseFormId,
          'modName' => $modName,
          'slotMask' => $slotMask,
          'keywords' => $keywords,
          'name' => $name
      ];
  }

  return $results;
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
    return [];
  }

  foreach ($parsedData as &$segment) {
      $baseFormId = $db -> escape($segment['baseFormId']);
      $modName = $db->escape($segment['modName']);
      $name = $db -> escape($segment['name']);
    $description = '';  // Placeholder description

    // Check if the row exists
    $result = $db->fetchAll(
      "SELECT * FROM equipment_description WHERE lower(baseFormId) = lower('{$baseFormId}') AND lower(modName) = lower('{$modName}')"
    );

    if (count($result) > 0) {
      // Row exists, enrich the segment with the description
      $segment["description"] = $result[0]["description"];
    } else {
      // Row doesn't exist, perform an insert
      $insertQuery = "INSERT INTO equipment_description (baseFormId, modName, name, description)
                            VALUES ('{$baseFormId}', '{$modName}', '{$name}', '{$description}')";
      $db->execQuery($insertQuery);
      $segment["description"] = '';
    }
  }
}

function BuildEquipmentContext(&$parsedData) 
{
  $context = "";
  $skipKeywords = [];
  foreach ($parsedData as $segment) {
    $name = $segment['name'];
    $description = $segment['description'];

    if (!empty($context)) {
      $context .= ", ";
    }

    // If there's a description, use only that
    if (!empty($description)) {
      $context .= $description;
      // Add keywords to skip since we used the description
      foreach ($segment['keywords'] as $keyword) {
        $skipKeywords[strtolower($keyword)] = true;
      }
    } 
    // If no description but has a name, use the name
    else if (!empty($name)) {
      $context .= $name;
      // Don't add to skipKeywords since we may want keyword-based descriptions
    }
  }
  
  if (!empty($context)) {
    $context .= ". ";
  }
  return [
    'context' => $context,
    'skipKeywords' => $skipKeywords
  ];
}

function GetAllEquipmentContext($actorName)
{
  // only support postgresql for now / not sure which case sqllite is used
  if ($GLOBALS["disable_worn_equipment"] || $GLOBALS["DBDRIVER"] !== "postgresql") {
    return [
      'context' => "",
      'skipKeywords' => []
    ];
  }

  // if this fails, still be able to continue without this functionality
  try {
    $encodedString = GetActorValue($actorName, "AllWornEquipment");
    minai_log("info", "AllWornEquipment: " . $encodedString);
    // we can potentially cache this by hashing the encodedString since equipment doesn't change often
    // especially for npc, but this should be fine for now
    $parsedResult = ParseEncodedEquipmentData($encodedString);
    EnrichEquipmentDataFromDb($parsedResult);
    return BuildEquipmentContext($parsedResult);
  } catch (Exception $e) {
    minai_log("info", "Failed to get equipment context: " . $e->getMessage());
    minai_log("info", $e->getTraceAsString());
    return [
      'context' => "",
      'skipKeywords' => []
    ];
  }
}

function IsSkipKeyword($keyword, $skipKeywords)
{
  return isset($skipKeywords[strtolower($keyword)]);
}
