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

require_once("test_common.php");

require_once("util.php");
require_once("contextbuilders/wornequipment_context.php");


function parse_valid_encoded_string_correctly_test() {
  print("parse_valid_encoded_string_correctly_test: ");

  $encodedString = "baseForm1:mod1:0x123:keyword1,keyword2:5#Hello:baseForm2:mod2:0x456:keyword4,keyword5:7#Goodbye";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 0x123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 0x456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
  ];
  
  assertObjectAsJson($expectedOutput, $parsedData);
  print("Passed\n");
}

function parse_correctly_when_name_empty_test() {
  print("parse_correctly_when_name_empty_test: ");

  $encodedString = "baseForm1:mod1:123:keyword1,keyword2::baseForm2:mod2:456:keyword4,keyword5:7#Goodbye";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 0x123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => '',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 0x456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
  ];
  
  assertObjectAsJson($expectedOutput, $parsedData);
  print("Passed\n");
}

function parse_correctly_when_name_empty_last_with_colon_test() {
  print("parse_correctly_when_name_empty_last_with_colon_test: ");

  $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2:mod2:456:keyword4,keyword5::";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 0x123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 0x456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => '',
      ],
  ];
  
  assertObjectAsJson($expectedOutput, $parsedData);
  print("Passed\n");
}

function parse_correctly_when_name_empty_last_without_colon_test() {
  print("parse_correctly_when_name_empty_last_without_colon_test: ");

  $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2:mod2:456:keyword4,keyword5:";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  
  // Expected Output:
  $expectedOutput = [
      [
          'baseFormId' => 'baseForm1',
          'modName' => 'mod1',
          'slotMask' => 0x123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 0x456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => '',
      ],
  ];
  
  assertObjectAsJson($expectedOutput, $parsedData);
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
          'slotMask' => 0x123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 0x456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
      [
          'baseFormId' => 'baseForm3',
          'modName' => 'mod3',
          'slotMask' => 0x789,
          'keywords' => ['keyword6', 'keyword7'],
          'name' => 'Hello:World',
      ],
  ];
  
  assertObjectAsJson($expectedOutput, $parsedData);
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
          'slotMask' => 0x123,
          'keywords' => ['keyword1', 'keyword2'],
          'name' => 'Hello',
      ],
      [
          'baseFormId' => 'baseForm2',
          'modName' => 'mod2',
          'slotMask' => 0x456,
          'keywords' => ['keyword4', 'keyword5'],
          'name' => 'Goodbye',
      ],
  ];
  
  assertObjectAsJson($expectedOutput, $parsedData);
  print("Passed\n");
}

function error_if_a_colon_is_missing_test() {
  print("error_if_a_colon_is_missing_test: ");
  try {
    $encodedString = "baseForm1:mod1:123:keyword1,keyword2:5#Hello:baseForm2:mod2:456:keyword4,keyword57#Goodbye";
    ParseEncodedEquipmentData($encodedString);
  } catch (Exception $e) {
    assertString("Missing colon, last read index: 64", $e->getMessage());
    print("Passed\n");
    return;
  }
  assertTrue(false);
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
  assertTrue(false);
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
  assertTrue(false);
}

function parse_unicode_correctly_test() {
  print("parse_unicode_correctly_test: ");
  $encodedString = "0xD7180D:BDOR Outlaws of Margoria by Team TAL.esp:0x400004::30#BDOR Outlaws of Margoria Armor:0xD75D69:[Trusty] VTW7 Katarina.esp:0x80::36#VTW Катарина - сапоги :0xD74813:Winter Snow Dress.esp:0x2000::28#Winter Snow Dress - Earrings:";
  $parsedData = ParseEncodedEquipmentData($encodedString);
  print("Passed\n");
}

print("<pre>Test cases for ParseEncodedEquipmentData: \n");
parse_valid_encoded_string_correctly_test();
parse_correctly_when_name_empty_test();
parse_correctly_when_name_empty_last_with_colon_test();
parse_correctly_when_name_empty_last_without_colon_test();
parse_valid_encoded_string_correctly_with_colon_in_name_test();
parse_valid_encoded_string_if_string_end_with_segment_separator_colon_test();
error_if_a_colon_is_missing_test();
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
  assertTrue(count($result) === 2);
  assertTrue($result[0]['name'] === 'outfit1');
  assertTrue($result[0]['description'] === '');
  assertTrue($result[1]['name'] === 'Goodbye');
  assertTrue($result[1]['description'] === '');

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
  assertTrue(count($result) === 2);
  assertTrue($result[0]['name'] === 'outfit1');
  assertTrue($result[0]['description'] === 'outfit1_desc');
  assertTrue($result[1]['name'] === 'Goodbye');
  assertTrue($result[1]['description'] === 'outfit2_desc');

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
  $expectedContext = "outfit1 - outfit1_desc, Goodbye - outfit2_desc, . ";
  assertString($expectedContext, $context['context']);

  print("Passed\n");
}

print("<pre>Test cases for storing and retrieving equipment data: \n");
creates_new_entry_when_not_exist_test();
enrich_if_description_does_exists_test();
build_context_correctly_test();
print("Done\n");  

print("</pre>");
