<?php
require_once("util.php");
require_once("deviousnarrator.php");
if (IsModEnabled("DeviouslyAccessible")) {
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        SetDeviousNarrator();
    }
}

?>
