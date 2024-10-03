<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$configFilepath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR;
$rootEnginePath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;

if (!file_exists($configFilepath . "conf.php")) {
  @copy($configFilepath . "conf.sample.php", $configFilepath . "conf.php");   // Defaults
  if (!file_exists($rootEnginePath . "data" . DIRECTORY_SEPARATOR . "mysqlitedb.db")) {
    require($rootEnginePath . "ui" . DIRECTORY_SEPARATOR . "cmd" . DIRECTORY_SEPARATOR . "install-db.php");
  }
  die(header("Location: conf_wizard.php"));
}

require_once($rootEnginePath . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($rootEnginePath . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS["DBDRIVER"]}.class.php");

print("dbdriver: " . $GLOBALS["DBDRIVER"] . "\n");

$GLOBALS['HERIKA_NAME'] = "Herika";

$db = new sql();
$GLOBALS['db'] = $db;
$HERIKA_NAME = "Herika";

require_once("util.php");
require_once("wornequipment.php");

function parse_valid_encoded_string_correctly_test() {
  print("parse_valid_encoded_string_correctly_test: ");

  $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2:mod2:456:keyword4,keyword5:7#Goodbye";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
  ];
  
  assert($parsedData === $expectedOutput);
  print("Passed\n");
}

function parse_valid_encoded_string_correctly_with_colon_in_name_test() {
  print("parse_valid_encoded_string_correctly_with_colon_in_name_test: ");

  $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2:mod2:456:keyword4,keyword5:7#Goodbye:baseForm3:mod3:789:keyword6,keyword7:11#Hello:World";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
      [
          'baseFormId' => 'baseForm3',
          'modName' => 'mod3',
          'slotMask' => 789,
          'keywords' => ['keyword6', 'keyword7'],
          'name' => 'Hello:World',
      ],
  ];
  
  assert($parsedData === $expectedOutput);
  print("Passed\n");
}

function parse_valid_encoded_string_if_string_end_with_segment_separator_colon_test() {
  print("parse_valid_encoded_string_if_string_end_with_segment_separator_colon_test: ");

  $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2:mod2:456:keyword4,keyword5:7#Goodbye:";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
  ];
  
  assert($parsedData === $expectedOutput);
  print("Passed\n");
}

function error_if_a_colon_is_missing_test() {
  print("error_if_a_colon_is_missing_test: ");
  try {
    $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2mod2:456:keyword4,keyword5:7#Goodbye";
    ParseEncodedEquipmentData($encodedString);
    
  } catch (Exception $e) {
    assert($e->getMessage() === "Invalid encoded string");
    print("Passed\n");
    return;
  }
  assert(false);
}

function throw_error_if_slotmask_is_not_a_number_test() {
  print("throw_error_if_slotmask_is_not_a_number_test: ");
  try {
    $encodedString = "baseForm1:mod1:123a:keyword1,keyword2:5#Hello";
    ParseEncodedEquipmentData($encodedString);
  } catch (Exception $e) {
    print("passed\n");
    return;
  }
  assert(false);
}

function throw_error_when_name_is_missing_hash_code_test() {
  print("throw_error_when_name_is_missing_hash_code_test: ");
  try {
    $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5Hello";
    ParseEncodedEquipmentData($encodedString);
  } catch (Exception $e) {
    print("passed\n");
    return;
  }
  assert(false);
}

function throw_error_when_name_length_is_not_a_number_test() {
  print("throw_error_when_name_length_is_not_a_number_test: ");
  try {
    $encodedString = "baseForm1:mod1:123:keyword1,keyword2:Hello#Hello";
    ParseEncodedEquipmentData($encodedString);
  } catch (Exception $e) {
    print("passed\n");
    return;
  }
  assert(false);
}

function throw_error_when_name_length_is_not_correct_test() {
  print("throw_error_when_name_length_is_not_correct_test: ");
  try {
    $encodedString = "baseForm1:mod1:123:keyword1,keyword2:10#Hello";
    ParseEncodedEquipmentData($encodedString);
  } catch (Exception $e) {
    print("passed\n");
    return;
  }
  assert(false);
}

function parse_unicode_correctly_test() {
  print("parse_unicode_correctly_test: ");
  $encodedString = "0xd7280d:bdor outlaws of margoria by team tal.esp:0x400004:sla_brabikini,:30#bdor outlaws of margoria armor:0xd76d69:[trusty] vtw7 katarina.esp:0x80:sla_bootsheels,:36#vtw Катaрина - сапоги:0xd75813:winter snow dress.esp:0x2000::28#winter snow dress - earrings:";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  print("Passed\n");
}

print("<pre>Test cases for ParseEncodedEquipmentData: \n");
parse_valid_encoded_string_correctly_test();
parse_valid_encoded_string_correctly_with_colon_in_name_test();
parse_valid_encoded_string_if_string_end_with_segment_separator_colon_test();
error_if_a_colon_is_missing_test();
throw_error_if_slotmask_is_not_a_number_test();
throw_error_when_name_is_missing_hash_code_test();
throw_error_when_name_length_is_not_a_number_test();
throw_error_when_name_length_is_not_correct_test();
parse_unicode_correctly_test();
print("Done\n");

// Test for storing and retrieving equipment data

function creates_new_entry_when_not_exist_test() {
  print("store_and_retrieve_equipment_data_test: ");

  $db = $GLOBALS['db'];
  $db->execQuery("DELETE FROM equipment_description WHERE baseFormId = '0xfffff0' AND modName = 'testmod1'");
  $db->execQuery("DELETE FROM equipment_description WHERE baseFormId = '0xfffff1' AND modName = 'testmod2'");

  $encodedString = "0xfffff0:testmod1:2:keyword1,keyword2:7#outfit1:0xfffff1:testmod2:4:keyword4,keyword5:7#Goodbye";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  EnrichEquipmentDataFromDb($parsedData);

  $result = $db->fetchAll("SELECT * FROM equipment_description WHERE (baseFormId = '0xfffff0' AND modName = 'testmod1') OR (baseFormId = '0xfffff1' AND modName = 'testmod2')");
  assert(count($result) === 2);
  assert($result[0]['name'] === 'outfit1');
  assert($result[0]['description'] === '');
  assert($result[1]['name'] === 'Goodbye');
  assert($result[1]['description'] === '');

  print("Passed\n");
}

function enrich_if_description_does_exists_test() {
  print("enrich_if_description_does_exists_test: ");

  $db = $GLOBALS['db'];
  $db->execQuery("DELETE FROM equipment_description WHERE baseFormId = '0xfffff0' AND modName = 'testmod1'");
  $db->execQuery("DELETE FROM equipment_description WHERE baseFormId = '0xfffff1' AND modName = 'testmod2'");

  $encodedString = "0xfffff0:testmod1:2:keyword1,keyword2:7#outfit1:0xfffff1:testmod2:4:keyword4,keyword5:7#Goodbye";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  EnrichEquipmentDataFromDb($parsedData);

  $db->execQuery("UPDATE equipment_description SET description = 'outfit1_desc' WHERE baseFormId = '0xfffff0' AND modName = 'testmod1'");
  $db->execQuery("UPDATE equipment_description SET description = 'outfit2_desc' WHERE baseFormId = '0xfffff1' AND modName = 'testmod2'");

  $parsedData = ParseEncodedEquipmentData($encodedString);
  EnrichEquipmentDataFromDb($parsedData);

  $result = $db->fetchAll("SELECT * FROM equipment_description WHERE (baseFormId = '0xfffff0' AND modName = 'testmod1') OR (baseFormId = '0xfffff1' AND modName = 'testmod2')");
  assert(count($result) === 2);
  assert($result[0]['name'] === 'outfit1');
  assert($result[0]['description'] === 'outfit1_desc');
  assert($result[1]['name'] === 'Goodbye');
  assert($result[1]['description'] === 'outfit2_desc');

  print("Passed\n");
}

function build_context_correctly_test() {
  print("build_context_correctly_test: ");

  $parsedData = [
    [
      'baseFormId' => '0xfffff0',
      'modName' => 'testmod1',
      'slotMask' => 2,
      'keywords' => ['keyword1', 'keyword2'],
      'name' => 'outfit1',
      'description' => 'outfit1_desc',
    ],
    [
      'baseFormId' => '0xfffff1',
      'modName' => 'testmod2',
      'slotMask' => 4,
      'keywords' => ['keyword4', 'keyword5'],
      'name' => 'Goodbye',
      'description' => 'outfit2_desc',
    ],
  ];

  $context = BuildEquipmentContext($parsedData);
  $expectedContext = "outfit1 - outfit1_desc, Goodbye - outfit2_desc";
  assert($context === $expectedContext);

  print("Passed\n");
}

print("<pre>Test cases for storing and retrieving equipment data: \n");
creates_new_entry_when_not_exist_test();
enrich_if_description_does_exists_test();
build_context_correctly_test();
print("Done\n");  

print("</pre>");
?>