<?php

function CanVibrate($name) {
  return IsEnabled($name, "CanVibrate");
}

Function GetActorValue($name, $key) {
  $name = strtolower($name);
  $ret = $GLOBALS["db"]->fetchAll("select * from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}')")[0]['value'];
  if ($ret) {
      return "0";
  }
  return $ret;

}

Function IsEnabled($name, $key) {
  $name = strtolower($name);
  return $GLOBALS["db"]->fetchAll("select 1 from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}') and LOWER(value)=LOWER('TRUE')");
}

Function IsPlayer($name) {
    return ($GLOBALS["PLAYER_NAME"] == $name);
}
?>
