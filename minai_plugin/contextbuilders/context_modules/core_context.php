<?php
/**
 * Core Context Builders
 * 
 * This file contains the basic context builders for the system prompt
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");

/**
 * Initialize core context builders
 */
function InitializeCoreContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register personality context builder
    $registry->register('personality', [
        'section' => 'character',
        'header' => 'Personality',
        'description' => 'Core personality description',
        'priority' => 10, // High priority - should be first in character section
        'enabled' => isset($GLOBALS['minai_context']['personality']) ? (bool)$GLOBALS['minai_context']['personality'] : true,
        'builder_callback' => 'BuildPersonalityContext'
    ]);
    
    // Register dynamic state context builder
    $registry->register('dynamic_state', [
        'section' => 'character',
        'header' => 'Current State',
        'description' => 'Dynamic state information for the character',
        'priority' => 15, // Just after personality but before most other attributes
        'enabled' => isset($GLOBALS['minai_context']['dynamic_state']) ? (bool)$GLOBALS['minai_context']['dynamic_state'] : true,
        'builder_callback' => 'BuildDynamicStateContext'
    ]);
    
    // Register combat context builder
    $registry->register('combat', [
        'section' => 'interaction',
        'header' => 'Combat Status',
        'description' => 'Information about current combat situation',
        'priority' => 15, // High priority in interaction section
        'enabled' => isset($GLOBALS['minai_context']['combat']) ? (bool)$GLOBALS['minai_context']['combat'] : true,
        'builder_callback' => 'BuildCombatContext'
    ]);
    
    // Register basic interaction context builder
    $registry->register('interaction', [
        'section' => 'interaction',
        'description' => 'Basic information about who the character is interacting with',
        'priority' => 10, // High priority - should be first in interaction section
        'enabled' => isset($GLOBALS['minai_context']['interaction']) ? (bool)$GLOBALS['minai_context']['interaction'] : true,
        'builder_callback' => 'BuildInteractionContext'
    ]);

    
    
    // Register player background context builder
    $registry->register('player_background', [
        'section' => 'interaction',
        'header' => 'Description of #player_name#',
        'description' => 'NPC perspective of the player',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['player_background']) ? (bool)$GLOBALS['minai_context']['player_background'] : true,
        'builder_callback' => 'BuildPlayerBackgroundContext'
    ]);

    // Register current task context builder
    $registry->register('current_task', [
        'section' => 'interaction',
        'header' => 'Current Task',
        'description' => 'Information about the current task or objective',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['current_task']) ? (bool)$GLOBALS['minai_context']['current_task'] : true,
        'builder_callback' => 'BuildCurrentTaskContext'
    ]);
}

/**
 * Build the personality context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted personality context
 */
function BuildPersonalityContext($params) {
    $herika_name = $params['herika_name'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    $player_name = isset($params['player_name']) ? $params['player_name'] : "";
    $target = isset($params['target']) ? $params['target'] : "";


    if ($herika_name == $target) {
        return "";
    }
    
    // Get the personality from global variables
    $herika_pers = isset($GLOBALS["HERIKA_PERS"]) ? $GLOBALS["HERIKA_PERS"] : "";
    
    if (empty($herika_pers)) {
        return "";
    }
    
    return trim($herika_pers);
}

/**
 * Build the combat context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted combat context
 */
function BuildCombatContext($params) {
    $target = $params['target'];
    $ret = "";
    
    // Add combat information if available
    $inCombat = GetActorValue($target, "inCombat");
    if ($inCombat === "true") {
        $ret .= "{$target} is currently engaged in battle!\n";
        
        // Add combat allies if any
        $allies = GetActorValue($target, "combatAllies");
        if (!empty($allies)) {
            $allies = explode('~', $allies);
            // Remove target and narrator from allies list (case insensitive)
            $allies = array_filter($allies, function($ally) use ($target) {
                return strcasecmp(trim($ally), trim($target)) !== 0 && strcasecmp(trim($ally), "The Narrator") !== 0;
            });

            if (!empty($allies)) {
                $ret .= "{$target} is fighting alongside: " . implode(', ', $allies) . "\n";
            }
            else {
                $ret .= "{$target} is fighting alone!\n";
            }
        }
        
        // Add combat targets if any
        $targets = GetActorValue($target, "combatTargets");
        if (!empty($targets)) {
            $targets = explode('~', $targets);
            // Remove narrator from targets list (case insensitive)
            $targets = array_filter($targets, function($t) {
                return strcasecmp(trim($t), "The Narrator") !== 0;
            });
            
            if (!empty($targets)) {
                $ret .= "{$target} is fighting against: " . implode(', ', $targets) . "\n";
            }
        }
    }
    
    return $ret;
}

/**
 * Build the basic interaction context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted interaction context
 */
function BuildInteractionContext($params) {
    $herika_name = $params['herika_name'];
    // Only display this once.
    if ($GLOBALS["HERIKA_NAME"] != $herika_name) {
        return "";
    }
    $target = $params['target'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    $player_name = isset($params['player_name']) ? $params['player_name'] : "";
    
    $ret = "";
    // Only check trespassing for player, follower, or narrator interactions
    if ($target === $player_name || $GLOBALS["HERIKA_NAME"] === "The Narrator" || IsFollower($target)) {
        if (IsEnabled($player_name, "isTrespassing")) {
            $ret .= "{$target} is currently trespassing in this location.\n";
        }
    }

    if ($is_self_narrator) {
        $ret .= "You are {$player_name}'s inner voice, providing thoughts, perspective, and advice directly to them.";
    }
    else {
        $ret .= "{$herika_name} currently interacting with {$target}."; // could be 2 NPCs interacting
    }

    return $ret;
}

/**
 * Build the player background context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted player background context
 */
function BuildPlayerBackgroundContext($params) {
    $player_name = $params['player_name'];
    $herika_name = $params['herika_name'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    
    // Include player background if interacting with the player or in self_narrator mode
    if ($herika_name != $player_name && $GLOBALS["HERIKA_NAME"] != "The Narrator") {
        return "";
    }
    
    // Get player bio from global variables
    $player_bio = isset($GLOBALS["PLAYER_BIOS"]) ? $GLOBALS["PLAYER_BIOS"] : "";
    $player_bio = str_replace("#PLAYER_NAME#", $player_name, $player_bio);
    if (empty($player_bio)) {
        if ($is_self_narrator) {
            return "You are the embodiment of {$player_name}'s thoughts, representing their subconscious perspective of the world around them.";
        }
        return "";
    }
    
    // Add additional context for self_narrator mode
    if ($is_self_narrator) {
        return "As {$player_name}'s inner voice, you understand the following about them:\n\n" . trim($player_bio);
    }
    
    return trim($player_bio);
}

/**
 * Build the dynamic state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted dynamic state context
 */
function BuildDynamicStateContext($params) {
    $herika_name = $params['herika_name'];
    $target = isset($params['target']) ? $params['target'] : "";
    
    // Only show dynamic state for the character speaking
    if ($herika_name == $target) {
        return "";
    }
    
    // Get dynamic state from global variables
    $dynamic_state = isset($GLOBALS["HERIKA_DYNAMIC"]) ? $GLOBALS["HERIKA_DYNAMIC"] : "";
    // Replace "The Narrator" with player name if in self-narrator mode
    if (isset($params['is_self_narrator']) && $params['is_self_narrator']) {
        $player_name = $params['player_name'];
        $dynamic_state = str_replace("The Narrator", $player_name, $dynamic_state);
    }
    
    if (empty($dynamic_state)) {
        return "";
    }
    // Strip out any excluded dynamic state entries
    $exclusions = [
        "Updated Character Profile",
        "Updated Character Sheet",
        "Updated Character Sheet"
    ];
    
    // Split into lines and filter out any containing excluded phrases
    $lines = explode("\n", $dynamic_state);
    $filtered_lines = array_filter($lines, function($line) use ($exclusions) {
        foreach ($exclusions as $exclude) {
            if (stripos($line, $exclude) !== false) {
                return false;
            }
        }
        return true;
    });
    $dynamic_state = implode("\n", $filtered_lines);
    return trim($dynamic_state);
}

/**
 * Build the current task context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted current task context
 */
function BuildCurrentTaskContext($params) {
    $herika_name = $params['herika_name'];
    $target = isset($params['target']) ? $params['target'] : "";
    // Only show current task for the character speaking
    if ($herika_name == $target) {
        return "";
    }
    $current_task = null;
    if (isset($GLOBALS["CURRENT_TASK"]) && $GLOBALS["CURRENT_TASK"]) {
        if (IsFollower($herika_name) || $GLOBALS["HERIKA_NAME"]=="The Narrator") {
            $current_task=DataGetCurrentTask();
            if (empty($current_task)) {
                $current_task="No active quests right now.";
            }
            if (!is_array($current_task)) {
                $current_task = explode(".", $current_task);
            }
            $current_task = array_map('trim', $current_task);
        }
    }
    
    if (!$current_task) {
        return "";
    }
    
    return implode("\n", $current_task);
}