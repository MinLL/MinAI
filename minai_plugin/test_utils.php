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

function stores_cache_key_correctly() {
    print("stores_cache_key_correctly: ");
    SetRequestScopeCache("store_cache_key", "store_cache_key_key", "store_cache_key_value");
    assertString("store_cache_key_value", $GLOBALS[MINAI_CACHE_KEY]["store_cache_key//store_cache_key_key"], "Incorrectly store cache");
    print("PASSED\n");
}

function set_and_get_from_cache_returns_correct_value_test() {
    print("set_and_get_from_cache_returns_correct_value_test: ");
    SetRequestScopeCache("name1", "key", "value");
    assertString("value", GetRequestScopeCache("name1", "key"), "Failed to set and get from cache");
    print("PASSED\n");
}

function update_again_change_to_different_value_test() {
    print("update_again_change_to_different_value_test: ");
    SetRequestScopeCache("name2", "key", "value");
    assertString("value", GetRequestScopeCache("name2", "key"), "fail to get first value");

    SetRequestScopeCache("name2", "key", "value2");
    assertString("value2", GetRequestScopeCache("name2", "key"), "doesn't update value");
    print("PASSED\n");
}

function returns_null_when_key_not_found_test() {
    print("returns_null_when_key_not_found_test: ");
    assertTrue(GetRequestScopeCache("name_not_exist", "key") === null, "doesn't return null when key not found");
    print("PASSED\n");
}

?>

<pre>

<?php
stores_cache_key_correctly();
set_and_get_from_cache_returns_correct_value_test();
update_again_change_to_different_value_test();
returns_null_when_key_not_found_test();
?>

</pre>