<?php

require_once("config.php");
require_once("util.php");

function ParseEncodedEquipmentData($encodedString)
{
  // Check if the string starts with the version indicator "V1:"
  if (strpos($encodedString, 'v1:') !== 0) {
    return [];
  }

  // Remove the "V1:" prefix - Just ignore this until we have more versions
  $encodedString = substr($encodedString, 3);

  // Split the string into segments by the pipe delimiter
  $segments = explode(':', $encodedString);

  // Prepare the result array
  $parsedData = [];
  $segmentCount = count($segments);

  // Process in chunks of 5 elements (baseFormId, modName, slotMask, keywords, name)
  for ($i = 0; $i < $segmentCount; $i += 5) {
    // Ensure we have enough elements left for a complete segment
    if ($i + 4 < $segmentCount) {
      $baseFormId = $segments[$i];
      $modName = $segments[$i + 1];
      $slotMask = (int)$segments[$i + 2]; // Convert to integer
      $keywords = !empty($segments[$i + 3]) ? explode(',', $segments[$i + 3]) : [];
      $name = $segments[$i + 4];

      // Add the parsed segment to the result array
      $parsedData[] = [
        'baseFormId' => $baseFormId,
        'modName' => $modName,
        'slotMask' => $slotMask,
        'keywords' => $keywords,
        'name' => $name
      ];
    }
  }

  return $parsedData;
}

function EnrichEquipmentDataFromDb(&$parsedData)
{
  // Database connection
  $db = $GLOBALS['db'];

  if (!$db) {
    return [];
  }

  foreach ($parsedData as &$segment) {
    $baseFormId = $segment['baseFormId'];
    $modName = $segment['modName'];
    $name = $segment['name'];
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

    if (empty($description)) {
      $context .= "{$name}, ";
    } else {
      $context .= "{$name} - {$description}, ";
    }

    foreach ($segment['keywords'] as $keyword) {
      $skipKeywords[strtolower($keyword)] = true;
    }
  }
  return [
    'context' => $context . ". ",
    'skipKeywords' => $skipKeywords
  ];
}

function CreateEquipmentDescriptionTableIfNotExist()
{
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

function GetAllEquipmentContext($actorName)
{
  // only support postgresql for now / not sure which case sqllite is used
  if ($GLOBALS["disable_worn_equipment"] || $GLOBALS["DBDRIVER"] !== "postgresql") {
    return [
      'context' => "",
      'skipKeywords' => []
    ];
  }

  CreateEquipmentDescriptionTableIfNotExist();

  try {
    $encodedString = GetActorValue($actorName, "AllWornEquipment");
    $parsedResult = ParseEncodedEquipmentData($encodedString);
    EnrichEquipmentDataFromDb($parsedResult);
    return BuildEquipmentContext($parsedResult);
  } catch (Exception $e) {
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
