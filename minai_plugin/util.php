<?php

function CanVibrate($name) {
  return IsEnabled($name, "CanVibrate");
}

Function GetActorValue($name, $key) {
  $name = strtolower($name);
  $ret = $GLOBALS["db"]->fetchAll("select * from conf_opts where LOWER(id)=LOWER('_minai_{$name}//{$key}')")[0]['value'];
  if (!$ret) {
      return "";
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

$GenericFuncRet =function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdSpankAss"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to complete the task. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};

Function IsModEnabled($mod) {
    return IsEnabled($GLOBALS['PLAYER_NAME'], "mod_{$mod}");
}

?>
