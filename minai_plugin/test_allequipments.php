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

// Example usage
$encodedString = "v1:0x444BAA:[Dint999] BDOr_Hairstyles.esp:0x2::[BDOR Hair] - Luanda:0xD6C83B:Merta Assassin Armor.esp:0x400004:SLA_Brabikini,EroticArmor,:Merta Assassin Reinforced Tunic:0xD6C802:Merta Assassin Armor.esp:0x8:EroticArmor,:Merta Assassin Fishnet Gloves:0xD6C832:Merta Assassin Armor.esp:0x10::Merta Assassin Reinforced Armlets:0xD6C801:Merta Assassin Armor.esp:0x20::Merta Assassin Belt Choker:0xD6C847:Merta Assassin Armor.esp:0x80:SLA_Heels,SLA_Heels,:Merta Assassin Elegant Boots:0xD6C881:Merta Assassin Armor.esp:0x100:SLA_MiniSkirt,EroticArmor,:Merta Assassin Reinforced Skirt [38]:0xD75813:Winter Snow Dress.esp:0x2000::Winter Snow Dress - Earrings:0xD6C838:Merta Assassin Armor.esp:0x800000:SLA_Thong,EroticArmor,:Merta Assassin Reinforced Stockings:0xD60823:Sharkish_Piercings.esp:0x2000000:SLA_PiercingVulva,SLA_PiercingClit,:Piercing B - Bellybar Long [Steel]:";
try {
  $parsedResult = ParseEncodedEquipmentData($encodedString);
  print("<pre>");
  print_r($parsedResult);
  print("</pre>");
  EnrichEquipmentDataFromDb($parsedResult);
  print("<pre>");
  print_r($parsedResult);
  print("</pre>");
  $context = BuildEquipmentContext($parsedResult);
  print("<pre>");
  print_r($parsedResult);
  print_r($context);
  print("</pre>");


} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
}
