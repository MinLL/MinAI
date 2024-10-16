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

Function has_actor_value_cache_test() {
    print("has_actor_value_cache_test: ");
    $name = uniqid();
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]['key1'] = "test value";

    assertTrue(HasActorValueCache($name, "key1"), "doesn't have actor value cache");
    assertTrue(HasActorValueCache($name), "doesn't have actor value cache");
    assertFalse(HasActorValueCache($name, "key2"), "has actor value cache");
    assertFalse(HasActorValueCache("name_not_exist"), "has actor value cache");

    print("PASSED\n");
}

Function get_cache_value_return_null_if_not_exists_test() {
    print("get_cache_value_return_null_if_not_exists_test: ");
    $name = uniqid();
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]['key1'] = "test value";

    assertString("test value", GetActorValueCache($name, "key1"), "doesn't return value if exists");
    print("PASSED\n");
}

Function get_cache_value_return_null_if_not_exists() {
    print("get_cache_value_return_null_if_not_exists: ");
    $name = uniqid();
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]['key1'] = "test value";

    assertTrue(GetActorValueCache($name, "key2") === null, "doesn't return null if not exists");
    assertTrue(GetActorValueCache("name_not_exist", "key") === null, "doesn't return null if not exists");
    print("PASSED\n");
}

Function clean_and_create_test_cache() {
    $GLOBALS["db"]->execQuery("delete from conf_opts where id like '_minai_test_cache//%'");
    $GLOBALS["db"]->execQuery("insert into conf_opts (id, value) values ('_minai_test_cache//key1', 'test value')");
    $GLOBALS["db"]->execQuery("insert into conf_opts (id, value) values ('_minai_test_cache//key2', 'test value2')");

    $GLOBALS["db"]->execQuery("delete from conf_opts where id like '_minai_test//cache//%'");
    $GLOBALS["db"]->execQuery("insert into conf_opts (id, value) values ('_minai_test//cache//key1', 'test//value')");
}

Function build_cache_get_value_from_cache_correctly_test() {
    print("build_cache_get_value_from_cache_correctly_test: ");
    BuildActorValueCache("test_cache");
    BuildActorValueCache("test//cache");

    assertString("test value", GetActorValueCache("test_cache", "key1"), "doesn't build cache correctly");
    assertString("test value2", GetActorValueCache("test_cache", "key2"), "doesn't build cache correctly");
    assertString("test//value", GetActorValueCache("test//cache", "key1"), "doesn't build cache correctly");
    assertString(null, GetActorValueCache("test_cache", "key3"), "doesn't build cache correctly");
    assertString(null, GetActorValueCache("test_cache2", "key1"), "doesn't build cache correctly");
    print("PASSED\n");
}

Function get_actor_value_from_cache_test() {
    print("get_actor_value_from_cache_test: ");
    $name = uniqid();
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]['key1'] = "test value";
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name]['key2'] = "test value2";

    assertString("test value", GetActorValue($name, "key1"), "doesn't get actor value from cache");
    assertString("test value2", GetActorValue($name, "key2"), "doesn't get actor value from cache");
    print("PASSED\n");
}

?>

<pre>

<?php
clean_and_create_test_cache();

has_actor_value_cache_test();
get_cache_value_return_null_if_not_exists_test();
build_cache_get_value_from_cache_correctly_test();
get_actor_value_from_cache_test();

?>

</pre>