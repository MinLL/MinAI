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
    
    // Register player background context builder
    $registry->register('player_background', [
        //'section' => 'interaction',
        'section' => 'character',
        'header' => 'Description of #player_name#',
        'description' => 'NPC perspective of the player',
        'priority' => 7,
        'enabled' => isset($GLOBALS['minai_context']['player_background']) ? (bool)$GLOBALS['minai_context']['player_background'] : true,
        'builder_callback' => 'BuildPlayerBackgroundContext'
    ]);

    // Register personality context builder
    $registry->register('personality', [
        'section' => 'character',
        'header' => 'Personality', // 'header' => 'PROFILE', ???
        'description' => 'Core personality description',
        'priority' => 5, // High priority - should be first in character section
        'enabled' => isset($GLOBALS['minai_context']['personality']) ? (bool)$GLOBALS['minai_context']['personality'] : true,
        'builder_callback' => 'BuildPersonalityContext'
    ]);
    
    // Register basic interaction context builder
    $registry->register('interaction', [
        'section' => 'interaction',
        'description' => 'Basic information about who the character is interacting with',
        'priority' => 10, // High priority - should be first in interaction section
        'enabled' => isset($GLOBALS['minai_context']['interaction']) ? (bool)$GLOBALS['minai_context']['interaction'] : true,
        'builder_callback' => 'BuildInteractionContext'
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
    
    // Register current task context builder
    $registry->register('current_task', [
        'section' => 'interaction',
        'header' => 'Current Task',
        'description' => 'Information about the current task or objective',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['current_task']) ? (bool)$GLOBALS['minai_context']['current_task'] : true,
        'builder_callback' => 'BuildCurrentTaskContext'
    ]);
    
    // Oghma context builder
    $registry->register('oghma_infinium', [
        'section' => 'misc',
        'header' => 'Oghma Infinium Lore',
        'description' => 'Lore Information from the Oghma Infinium.',
        'priority' => 25,
        'enabled' => true,
        'builder_callback' => 'BuildOghmaInfiniumContext'
    ]);
    
}

//---------------------------------------    

function StrCleanBullets($s_input = ""){
    $s_res = strtr($s_input,[
        '** ' => ' ',
        '* ' => ' ',
    ]);
    return $s_res;
}


/**
 * Build the Oghma Infinium context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted Oghma Infinium context
 */
function BuildOghmaInfiniumContext($params) {

    if (isset($GLOBALS["OGHMA_HINT"]) && (!empty($GLOBALS["OGHMA_HINT"])) ) {
        //error_log("oghma minai: ". ($GLOBALS["OGHMA_HINT"] ?? "") . " - dbg");        
        return $GLOBALS["OGHMA_HINT"];
    } else 
        return "";
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
    $herika_pers = ($GLOBALS["HERIKA_PERS"] ?? "");
    
    if (isset($GLOBALS['HERIKA_PERSONALITY']) && (trim($GLOBALS['HERIKA_PERSONALITY']) > "")) {
        $herika_pers .= "\n\n## Behavioral patterns\n" . trim($GLOBALS['HERIKA_PERSONALITY']);
    }
    if (isset($GLOBALS['HERIKA_BACKGROUND']) && (trim($GLOBALS['HERIKA_BACKGROUND']) > "")) {
        $herika_pers .= "\n\n## Background\n" . trim($GLOBALS['HERIKA_BACKGROUND']);
    }
    if (isset($GLOBALS['HERIKA_GOALS']) && (trim($GLOBALS['HERIKA_GOALS']) > "")) {
        $herika_pers .= "\n\n## Goals\n" .  StrCleanBullets(trim($GLOBALS['HERIKA_GOALS']));
    }
    if (isset($GLOBALS['HERIKA_SPEECHSTYLE']) && (trim($GLOBALS['HERIKA_SPEECHSTYLE']) > "")) {
        $herika_pers .= "\n\n## Speech style\n" . trim($GLOBALS['HERIKA_SPEECHSTYLE']);
    }
    if (isset($GLOBALS['HERIKA_RELATIONSHIPS']) && (trim($GLOBALS['HERIKA_RELATIONSHIPS']) > "")) {
        $herika_pers .= "\n\n## Social connections\n" . StrCleanBullets(trim($GLOBALS['HERIKA_RELATIONSHIPS']));
    }
    if (isset($GLOBALS['HERIKA_APPEARANCE']) && (trim($GLOBALS['HERIKA_APPEARANCE']) > "")) {
        $herika_pers .= "\n\n## Appearance\n" . trim($GLOBALS['HERIKA_APPEARANCE']);
    }
    if (isset($GLOBALS['HERIKA_OCCUPATION']) && (trim($GLOBALS['HERIKA_OCCUPATION']) > "")) {
        $herika_pers .= "\n\n## Occupation\n" . trim($GLOBALS['HERIKA_OCCUPATION']);
    }
    if (isset($GLOBALS['HERIKA_SKILLS']) && (trim($GLOBALS['HERIKA_SKILLS']) > "")) {
        $herika_pers .= "\n\n## Skills\n" . StrCleanBullets(trim($GLOBALS['HERIKA_SKILLS']));
    }
    if (isset($GLOBALS["PROFILE_PROMPT"]) && (trim($GLOBALS['PROFILE_PROMPT']) > "")) {
        $herika_pers .= "\n\n<group_profile_prompt>\n## Other\n".trim($GLOBALS["PROFILE_PROMPT"])."\n</group_profile_prompt>";
    }
    
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
        if (function_exists('DataRetrieveLastTimeTalk')) {
            $s_last_talk = DataRetrieveLastTimeTalk($herika_name, $target);
            if ($s_last_talk > "")
                $ret .= "\n{$s_last_talk}";
        }
    }

    return $ret;
}

/**
 * Build the player achievements
 * 
 * @player_name
 * @return string Formatted player achievements
 */
function BuildPlayerAchievementsContext($playername) {
    $s_res = "";
    
    if (strlen(trim($playername)) > 0) { 
        
        // The Circle
        $bx = IsInFaction($playername, "The Circle");
        if ($bx) 
            $s_res .= "- member of the inner circle of The Companions, Lycanthropy is mandatory condition for membership\n";
        else {
            // The Companions
            if (IsInFaction($playername, "The Companions"))
                $s_res .= "- member of The Companions\n";
        }
        // Nightingales
        $bx = IsInFaction($playername, "Nightingales");
        if ($bx) 
            $s_res .= "- member of the Nightingale Trinity (higher echelon of the Thieves Guild, dedicated to the service of Nocturnal)\n";
        else {
            //Thieves' Guild
            if (IsInFaction($playername, "Thieves' Guild"))
                $s_res .= "- skilled thief, member of Thieves' Guild\n";
        }
        
        // College of Winterhold - Arch-Mage, also known as Archmagus or Archmagister, the leader of the Mages Guild known as College of Winterhold. 
        $bx = IsInFaction($playername, "College of Winterhold Arch-Mage Faction");
        if ($bx) 
            $s_res .= "- Arch-Mage, the leader of the Mages Guild known as College of Winterhold\n";
        else {
            if (IsInFaction($playername, "College of Winterhold"))
                $s_res .= "- mage, member of College of Winterhold\n";
        }
        
        // Greybeards
        if (IsInFaction($playername, "Greybeards"))
            $s_res .= "- recognized as The Dragonborn, member of Greybeards\n";

        // Bards College
        if (IsInFaction($playername, "Bards College"))
            $s_res .= "- presumed (debatable) skilled bard, member of Bards College\n";

        // Blood-Kin of the Orcs
        if (IsInFaction($playername, "Blood-Kin of the Orcs"))
            $s_res .= "- Blood-Kin of the Orcs, unlimited access to orc settlements\n";

        // The Dawnguard
        if (IsInFaction($playername, "The Dawnguard"))
            $s_res .= "- vampire hunter, member of The Dawnguard\n";

        // Thirsk Hall Riekling Tribe
        if (IsInFaction($playername, "Thirsk Hall Riekling Tribe"))
            $s_res .= "- chief of Thirsk Hall Riekling Tribe\n";

        // Dark Brotherhood
        if (IsInFaction($playername, "Dark Brotherhood"))
            $s_res .= "- professional assassin, member of the Dark Brotherhood\n";

        // Tribunal Temple
        if (IsInFaction($playername, "Tribunal Temple"))
            $s_res .= "- member of Tribunal Temple (heretical Dunmeri faction devoted to worship of the Tribunal, the former living gods Almalexia, Sotha Sil, and Vivec)\n";

        // Imperial Legion
        if (IsInFaction($playername, "Imperial Legion"))
            $s_res .= "- member of Imperial Legion\n";
        
        // Stormcloaks
        if (IsInFaction($playername, "Stormcloaks"))
            $s_res .= "- member of Stormcloaks\n";
        
        // Volkihar Vampire Clan
        if (IsInFaction($playername, "Volkihar Vampire Clan"))
            $s_res .= "- vampire, member of Volkihar Vampire Clan\n";
        
        // Blades
        if (IsInFaction($playername, "Blades"))
            $s_res .= "- member of the Blades\n";

        // Vigilant of Stendarr For Player
        if (IsInFaction($playername, "Vigilant of Stendarr For Player"))
            $s_res .= "- member of Vigilant of Stendarr\n";

        // Riften Fishery Faction
        if (IsInFaction($playername, "Riften Fishery Faction"))
            $s_res .= "- exceptional fisherman, member of Riften Fishery Guild\n";

        // Coven of Namira
        if (IsInFaction($playername, "Coven of Namira"))
            $s_res .= "- cannibal, member of Coven of Namira\n";

        // 
        //if (IsInFaction($playername, ""))
        //    $s_res .= "- member of \n";

        if (strlen($s_res) > 0) {
            $s_res = "\n### {$playername}'s affiliations: \n" . $s_res;
        }
    }
    //error_log("achievements: $s_res ");
    return $s_res;
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
    $player_bio = $player_bio . BuildPlayerAchievementsContext($player_name);

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
    $dynamic_state = ($GLOBALS["HERIKA_DYNAMIC"] ?? "" );
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