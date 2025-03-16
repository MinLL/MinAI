<?php
/**
 * Legacy Context Builders
 * 
 * This file contains the original context builder functions for backward compatibility.
 * New code should use the modular system in contextbuilders/system_prompt_context.php
 */

require_once("config.php");
require_once("util.php");

// Include dependencies
require_once("contextbuilders/deviousfollower_context.php");
require_once("contextbuilders/system_prompt_context.php");
require_once("customintegrations.php");
require_once("contextbuilders/weather_context.php");
// require_once("reputation.php");
require_once("contextbuilders/relationship_context.php");
require_once("contextbuilders/submissivelola_context.php");
require_once("contextbuilders/dirtandblood_context.php");
require_once("contextbuilders/exposure_context.php");
require_once("contextbuilders/fertilitymode_context.php");
require_once("contextbuilders/equipment_context.php");
require_once("contextbuilders/tattoos_context.php");
