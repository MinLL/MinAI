<?php
/**
 * Information Prompts for Device Actions
 * 
 * This file serves as an entry point for all prompts,
 * using the new registry system.
 */

// Include the prompts registry which handles all prompt definitions
//require_once(dirname(__FILE__) . "/registry.php");

// This file now acts as a simple entry point to the new registry system.
// All prompt definitions have been moved to the 'definitions' directory and
// are registered through the PromptRegistry system.

// Require all prompt files
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_arousal_increase.php");
require_once(dirname(__FILE__) . "/info_device_equip_collar.php");
require_once(dirname(__FILE__) . "/info_device_equip_gag.php");
require_once(dirname(__FILE__) . "/info_device_remove_fail.php");
require_once(dirname(__FILE__) . "/info_device_remove_gag.php");
require_once(dirname(__FILE__) . "/info_edged.php");
require_once(dirname(__FILE__) . "/info_kiss.php");
require_once(dirname(__FILE__) . "/info_orgasm.php");
require_once(dirname(__FILE__) . "/info_shock.php");
require_once(dirname(__FILE__) . "/info_spank_ass.php");
require_once(dirname(__FILE__) . "/info_spank_breast.php");
require_once(dirname(__FILE__) . "/info_stimulate.php");
require_once(dirname(__FILE__) . "/info_tease.php");
require_once(dirname(__FILE__) . "/info_touch_grope.php");
require_once(dirname(__FILE__) . "/info_touch_moan.php");
require_once(dirname(__FILE__) . "/info_touch_pinch.php");
require_once(dirname(__FILE__) . "/info_turn_off.php");
require_once(dirname(__FILE__) . "/info_vibrate.php");
// For any code that directly accesses the old prompts, we can map them here
// But new code should use the PromptRegistry methods:
// - PromptRegistry::get($id)
// - PromptRegistry::generate($id, $context)
// - GetPromptsByTarget($target)
// - GeneratePrompt($promptId, $context)
