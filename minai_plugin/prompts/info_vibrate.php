<?php

if ($GLOBALS["gameRequest"][0] == "info_minai_vibrate_stop" || $GLOBALS["gameRequest"][0] == "info_minai_vibrate_start") {
    require_once(dirname(__FILE__) . "/info_vibrator_prompts.php");
}
