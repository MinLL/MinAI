<?php
require_once("config.php");
require_once("util.php");
require_once("customintegrations.php");

// Custom command / third party integrations support
// Done here, as this is mounted early in main.php
ProcessIntegrations();


?>
