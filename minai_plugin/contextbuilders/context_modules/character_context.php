<?php
/**
 * Character Context Builders
 * 
 * This file contains context builders related to character status and attributes
 */


/**
 * Helper function to validate and sanitize parameters for context builders
 * 
 * @param array $params Parameters to validate
 * @param array $required List of required parameter keys
 * @return array Validated and sanitized parameters with fallbacks if needed
 */
function ValidateContextParams($params, $required = ['herika_name']) {
    $validated = [];
    
    // Check for required parameters
    foreach ($required as $key) {
        if (isset($params[$key])) {
            $validated[$key] = $params[$key];
        } else {
            // Try to use globals as fallback
            switch ($key) {
                case 'herika_name':
                    $validated[$key] = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "";
                    break;
                case 'player_name':
                    $validated[$key] = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "";
                    break;
                case 'target':
                    $validated[$key] = isset($GLOBALS["HERIKA_TARGET"]) ? 
                                      $GLOBALS["HERIKA_TARGET"] : 
                                      (isset($validated['player_name']) ? $validated['player_name'] : "");
                    break;
                default:
                    $validated[$key] = "";
            }
        }
    }
    
    // Add any other parameters that were in the original params
    foreach ($params as $key => $value) {
        if (!isset($validated[$key])) {
            $validated[$key] = $value;
        }
    }
    
    return $validated;
}

/**
 * Initialize character context builders
 */
function InitializeCharacterContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register physical description context builder
    $registry->register('physical_description', [
        'section' => 'status',
        'header' => 'Physical Appearance',
        'description' => 'Physical description of the character',
        'priority' => 10,
        'enabled' => isset($GLOBALS['minai_context']['physical_description']) ? (bool)$GLOBALS['minai_context']['physical_description'] : true,
        'builder_callback' => 'BuildPhysicalDescriptionContext'
    ]);
    
    // Register career context builder
    $registry->register('career', [
        'section' => 'status',
        'header' => 'Career',
        'description' => 'Character class/career information',
        'priority' => 3,
        'enabled' => isset($GLOBALS['minai_context']['career']) ? (bool)$GLOBALS['minai_context']['career'] : true,
        'builder_callback' => 'BuildCareerContext'
    ]);
    
    // Register character state context builder
    $registry->register('character_state', [
        'section' => 'status',
        'header' => 'Character State',
        'description' => 'Sitting, sleeping, swimming, etc.',
        'priority' => 15,
        'enabled' => isset($GLOBALS['minai_context']['character_state']) ? (bool)$GLOBALS['minai_context']['character_state'] : true,
        'builder_callback' => 'BuildCharacterStateContext'
    ]);
    
    // Register equipment context builder
    $registry->register('equipment', [
        'section' => 'status',
        'header' => 'Equipment',
        'description' => 'Equipment and worn items',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['equipment']) ? (bool)$GLOBALS['minai_context']['equipment'] : true,
        'builder_callback' => 'BuildEquipmentContext'
    ]);
    
    // Register tattoos context builder
    $registry->register('tattoos', [
        'section' => 'status',
        'header' => 'Tattoos',
        'description' => 'Character tattoos',
        'priority' => 30,
        'enabled' => isset($GLOBALS['minai_context']['tattoos']) ? (bool)$GLOBALS['minai_context']['tattoos'] : true,
        'builder_callback' => 'BuildTattooContext'
    ]);
    
    // Register dirt and blood context builder
    $registry->register('dirt_and_blood', [
        'section' => 'status',
        'header' => 'Cleanliness',
        'description' => 'Character dirt and blood status',
        'priority' => 35,
        'enabled' => isset($GLOBALS['minai_context']['dirt_and_blood']) ? (bool)$GLOBALS['minai_context']['dirt_and_blood'] : true,
        'builder_callback' => 'BuildDirtAndBloodContext'
    ]);
    
    // Register arousal context builder
    $registry->register('arousal', [
        'section' => 'status',
        'header' => 'Arousal Status',
        'description' => 'Character arousal level',
        'priority' => 40,
        'is_nsfw' => true,
        'enabled' => isset($GLOBALS['minai_context']['arousal']) ? (bool)$GLOBALS['minai_context']['arousal'] : true,
        'builder_callback' => 'BuildArousalContext'
    ]);
    
    // Register fertility context builder
    $registry->register('fertility', [
        'section' => 'status',
        'header' => 'Fertility Status',
        'description' => 'Character fertility status',
        'priority' => 50,
        'is_nsfw' => true,
        'enabled' => isset($GLOBALS['minai_context']['fertility']) ? (bool)$GLOBALS['minai_context']['fertility'] : true,
        'builder_callback' => 'BuildFertilityContext'
    ]);
    
    // Register following context builder
    $registry->register('following', [
        'section' => 'status',
        'header' => 'Following Status',
        'description' => 'Character following status',
        'priority' => 60,
        'enabled' => isset($GLOBALS['minai_context']['following']) ? (bool)$GLOBALS['minai_context']['following'] : true,
        'builder_callback' => 'BuildFollowingContext'
    ]);
    
    // Register survival context builder
    $registry->register('survival', [
        'section' => 'status',
        'header' => 'Survival Status',
        'description' => 'Character survival needs',
        'priority' => 70,
        'enabled' => isset($GLOBALS['minai_context']['survival']) ? (bool)$GLOBALS['minai_context']['survival'] : true,
        'builder_callback' => 'BuildSurvivalContext'
    ]);
    
    
    // Register bounty context builder
    $registry->register('bounty', [
        'section' => 'interaction',
        'header' => 'Bounty Status',
        'description' => 'Player bounty status',
        'priority' => 40,
        'enabled' => isset($GLOBALS['minai_context']['bounty']) ? (bool)$GLOBALS['minai_context']['bounty'] : true,
        'builder_callback' => 'BuildBountyContext'
    ]);
    
    // Register mind influence context builder
    $registry->register('mind_influence', [
        'section' => 'status',
        'header' => 'Mental State',
        'description' => 'Character mind influence state',
        'priority' => 80,
        'enabled' => isset($GLOBALS['minai_context']['mind_influence']) ? (bool)$GLOBALS['minai_context']['mind_influence'] : true,
        'builder_callback' => 'BuildMindInfluenceContext'
    ]);
    
    // Register level context builder
    $registry->register('level', [
        'section' => 'status',
        'header' => 'Power Level',
        'description' => 'Character level and power description',
        'priority' => 4,
        'enabled' => isset($GLOBALS['minai_context']['level']) ? (bool)$GLOBALS['minai_context']['level'] : true,
        'builder_callback' => 'BuildLevelContext'
    ]);
    
    // Register family status context builder
    $registry->register('family_status', [
        'section' => 'status',
        'header' => 'Family Status',
        'description' => 'Character family information',
        'priority' => 6,
        'enabled' => isset($GLOBALS['minai_context']['family_status']) ? (bool)$GLOBALS['minai_context']['family_status'] : true,
        'builder_callback' => 'BuildFamilyStatusContext'
    ]);
    
    // Register third party context builder for character-specific info
    $registry->register('third_party_info', [
        'section' => 'status',
        'header' => 'Additional Character Information',
        'description' => 'Additional character information from third party mods',
        'priority' => 90,
        'enabled' => isset($GLOBALS['minai_context']['third_party']) ? (bool)$GLOBALS['minai_context']['third_party'] : true,
        'builder_callback' => 'BuildThirdPartyContext'
    ]);
    
    // Register third party context builder for everyone info
    $registry->register('third_party_global_info', [
        'section' => 'misc',
        'header' => 'General Information',
        'description' => 'General information from third parties not tied to a specific character',
        'priority' => 10,
        'enabled' => isset($GLOBALS['minai_context']['third_party']) ? (bool)$GLOBALS['minai_context']['third_party'] : true,
        'builder_callback' => 'BuildThirdPartyGlobalContext'
    ]);

    // Register vitals context builder
    $registry->register('vitals', [
        'section' => 'status',
        'header' => 'Combat Vitals',
        'description' => 'Character health, magicka, stamina and combat status',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['vitals']) ? (bool)$GLOBALS['minai_context']['vitals'] : true,
        'builder_callback' => 'BuildVitalsContext'
    ]);
}

/**
 * Build the physical description context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted physical description context
 */
function BuildPhysicalDescriptionContext($params) {
    // Determine which character we're building context for
    $character = $params['herika_name'];
    
    $gender = GetActorValue($character, "gender");
    $race = GetActorValue($character, "race");
    $beautyScore = GetActorValue($character, "beautyScore");
    $breastsScore = GetActorValue($character, "breastsScore");
    $buttScore = GetActorValue($character, "buttScore");
    $isexposed = GetActorValue($character, "isexposed");
    
    // Get proper pronouns for the character
    $pronouns = GetActorPronouns($character);
    
    $ret = "";
    $isWerewolf = false;
    
    if ($gender != "" && $race != "") {
        $ret .= "{$character} is a {$gender} {$race}. ";
        if ($race == "werewolf") {
            $isWerewolf = true;
            $ret .= "{$character} is currently transformed into a terrifying werewolf! ";
        }
    }
    
    // Don't add beauty/physical attributes for NPCs unless specified
    $isPlayer = IsPlayer($character);
    $addPhysicalDetails = $isPlayer || isset($params['add_npc_physical_details']);
    
    if (!$addPhysicalDetails) {
        return $ret;
    }
    
    // Beauty description using 0-10 scale
    if (isset($beautyScore) && $beautyScore !== "" && !$isWerewolf) {
        // Convert to 0-10 scale if needed
        $beautyStage = min(10, ceil(intval($beautyScore)/10));
        
        $beautyDesc = "";
        switch ($beautyStage) {
            case 0: $beautyDesc = "Extremely Unattractive"; break;
            case 1: $beautyDesc = "Very Unattractive"; break;
            case 2: $beautyDesc = "Unattractive"; break;
            case 3: $beautyDesc = "Below Average"; break;
            case 4: $beautyDesc = "Plain"; break;
            case 5: $beautyDesc = "Average with Charm"; break;
            case 6: $beautyDesc = "Somewhat Alluring"; break;
            case 7: $beautyDesc = "Naturally Sensual"; break;
            case 8: $beautyDesc = "Captivating"; break;
            case 9: $beautyDesc = "Strikingly Beautiful"; break;
            case 10: $beautyDesc = "Exceptionally Gorgeous"; break;
            default: $beautyDesc = "Unknown";
        }
        
        // Add the numerical rating
        $beautyDesc .= " ({$beautyStage}/10)";
        
        $ret .= ucfirst($pronouns['subject']) . " is {$beautyDesc} (appearance). ";
    }
    
    // Breast and butt descriptions (if applicable)
    if((isset($breastsScore) && $breastsScore !== "") && (isset($buttScore) && $buttScore !== "") && !$isWerewolf) {
        // Convert to 0-10 scale
        $breastsStage = min(10, ceil(intval($breastsScore)/10));
        $buttStage = min(10, ceil(intval($buttScore)/10));
        
        // Breast description
        $breastsDesc = "";
        switch ($breastsStage) {
            case 0: $breastsDesc = "Unappealing Breasts"; break;
            case 1: $breastsDesc = "Very Plain Breasts"; break;
            case 2: $breastsDesc = "Plain Breasts"; break;
            case 3: $breastsDesc = "Unremarkable Breasts"; break;
            case 4: $breastsDesc = "Decent Breasts"; break;
            case 5: $breastsDesc = "Pleasing Breast Curves"; break;
            case 6: $breastsDesc = "Attractive Breasts"; break;
            case 7: $breastsDesc = "Very Attractive Breasts"; break;
            case 8: $breastsDesc = "Striking Breast Curves"; break;
            case 9: $breastsDesc = "Exceptionally Attractive Breasts"; break;
            case 10: $breastsDesc = "Remarkably Beautiful Breasts"; break;
            default: $breastsDesc = "Unknown";
        }
        
        // Add the numerical rating
        $breastsDesc .= " ({$breastsStage}/10)";
        
        // Butt description
        $buttDesc = "";
        switch ($buttStage) {
            case 0: $buttDesc = "Unappealing Buttocks"; break;
            case 1: $buttDesc = "Very Plain Buttocks"; break;
            case 2: $buttDesc = "Plain Buttocks"; break;
            case 3: $buttDesc = "Unremarkable Buttocks"; break;
            case 4: $buttDesc = "Decent Buttocks"; break;
            case 5: $buttDesc = "Nicely Formed Buttocks"; break;
            case 6: $buttDesc = "Attractive Buttocks"; break;
            case 7: $buttDesc = "Well-Curved Buttocks"; break;
            case 8: $buttDesc = "Very Attractive Buttocks"; break;
            case 9: $buttDesc = "Exceptionally Shapely Buttocks"; break;
            case 10: $buttDesc = "Remarkably Well-Formed Buttocks"; break;
            default: $buttDesc = "Unknown";
        }
        
        // Add the numerical rating
        $buttDesc .= " ({$buttStage}/10)";
        
        $ret .= ucfirst($pronouns['subject']) . " has {$breastsDesc} (chest) and {$buttDesc} (posterior). ";
    }
    
    if (IsEnabled($character, "isexposed")) {
        $ret .= GetPenisSize($character);
    }
    
    return $ret;
}

/**
 * Get the description of a character's penis size
 * 
 * @param string $name Character name
 * @return string Formatted penis size description
 */
function GetPenisSize($name) {
    $tngsize = GetActorValue($name, "tngsize");
    $gender = strtolower(GetActorValue($name, "gender"));
    // Get the size stage (0-4 scale)
    $sizeStage = 2; // Default to average
    if (!HasKeyword($name, "TNG_Gentlewoman") && $gender == "female") {
        $sizeStage = -1;
    }
    elseif (HasKeyword($name, "TNG_XL") || ($tngsize == 4)) {
        $sizeStage = 4;
    }
    elseif (HasKeyword($name, "TNG_L") || ($tngsize == 3)) {
        $sizeStage = 3;
    }
    elseif (HasKeyword($name, "TNG_M") || HasKeyword($name, "TNG_DefaultSize") || ($tngsize == 2)) {
        $sizeStage = 2;
    }
    elseif (HasKeyword($name, "TNG_S") || ($tngsize == 1)) {
        $sizeStage = 1;
    }        
    elseif (HasKeyword($name, "TNG_XS") || ($tngsize == 0)) {
        $sizeStage = 0;
    }
    
    // Map stage to description
    $sizeDescription = "";
    switch ($sizeStage) {
        case 0: $sizeDescription = "Embarrassingly Tiny Prick"; break;
        case 1: $sizeDescription = "Small Cock"; break;
        case 2: $sizeDescription = "Average Sized Cock"; break;
        case 3: $sizeDescription = "Large Cock"; break;
        case 4: $sizeDescription = "Impressively Huge Cock, one of the biggest you've ever seen"; break;
        default: $sizeDescription = "";
    }
    
    
    if ($sizeDescription != "") {
        return "{$name} has an {$sizeDescription}. ";
    }
    
    return "";
}

/**
 * Build the equipment context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted equipment context
 */
function BuildEquipmentContext($params) {
    // Determine which character we're building context for
    $herika_name = $params['herika_name'];
    $character = $herika_name;
    if ($herika_name == "The Narrator") {
        $character = $params['player_name'];
    }
    // This function calls the existing GetUnifiedEquipmentContext function
    return GetUnifiedEquipmentContext($character);
}

/**
 * Build the tattoo context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted tattoo context
 */
function BuildTattooContext($params) {
    $character = $params['herika_name'];
    $ret = GetTattooContext($character);
    return $ret;
}

/**
 * Build the arousal context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted arousal context
 */
function BuildArousalContext($params) {
    // Determine which character we're building context for
    $character = $params['herika_name'];
    if ($character == "The Narrator") {
        $character = $params['player_name'];
    }
    $arousal = GetActorValue($character, "arousal");
    
    $ret = "";
    if (isset($arousal) && $arousal !== "") {
        // Convert percentage (0-99) to stage (0-10)
        $stage = min(10, floor(floatval($arousal) / 10));
        
        $arousalDesc = "";
        switch ($stage) {
            case 0: $arousalDesc = "Completely Satisfied and Content"; break;
            case 1: $arousalDesc = "Fulfilled with No Desire"; break;
            case 2: $arousalDesc = "Not Aroused"; break;
            case 3: $arousalDesc = "Slightly Aroused and Curious"; break;
            case 4: $arousalDesc = "Moderately Aroused and Interested"; break;
            case 5: $arousalDesc = "Noticeably Aroused and Eager"; break;
            case 6: $arousalDesc = "Quite Aroused and Desiring"; break;
            case 7: $arousalDesc = "Very Horny and Wanting"; break;
            case 8: $arousalDesc = "Intensely Horny and Needing"; break;
            case 9: $arousalDesc = "Burning with Passionate Desire"; break;
            case 10: $arousalDesc = "Overwhelmingly Horny and Desperate"; break;
            default: $arousalDesc = "Unknown";
        }
        
        // Add the numerical rating
        $arousalDesc .= " ({$stage}/10)";
        
        $ret .= "{$character} is {$arousalDesc} (arousal).\n";
    }
    
    return $ret;
}

/**
 * Build the fertility context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted fertility context
 */
function BuildFertilityContext($params) {
    $character = $params['herika_name'];
    if ($character == "The Narrator") {
        $character = $params['player_name'];
    }
    // This function would call the existing GetFertilityContext function
    return GetFertilityContext($character);
}

/**
 * Build the following context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted following context
 */
function BuildFollowingContext($params) {
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    
    if (IsFollowing($character)) {
        return "{$character} is following, walking, or escorting {$player_name}";
    }
    
    return "";
}

/**
 * Build the survival context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted survival context
 */
function BuildSurvivalContext($params) {
    // Determine which character we're building context for
    $herika_name = $params['herika_name'];
    $character = $herika_name;
    $player_name = $params['player_name'];
    if ($character == "The Narrator") {
        $character = $player_name;
    }
    
    // Initialize all stage variables
    $hungerStage = null;
    $thirstStage = null;
    $fatigueStage = null;
    $coldStage = null;

    if (IsModEnabled("Sunhelm")) {
        // Get stage values for Sunhelm (0-5 scale)
        $hungerValue = GetActorValue($character, "hunger");
        $thirstValue = GetActorValue($character, "thirst");
        $fatigueValue = GetActorValue($character, "fatigue");
        $coldValue = GetActorValue($character, "cold");
        
        // Check if the values exist (including zero values)
        if (isset($hungerValue) && $hungerValue !== "") $hungerStage = intval($hungerValue);
        if (isset($thirstValue) && $thirstValue !== "") $thirstStage = intval($thirstValue);
        if (isset($fatigueValue) && $fatigueValue !== "") $fatigueStage = intval($fatigueValue);
        if (isset($coldValue) && $coldValue !== "") $coldStage = intval($coldValue);
    }
    else {
        // For other mods, try to convert percentage values to stages
        $hungerPercent = GetActorValue($character, "hunger");
        $thirstPercent = GetActorValue($character, "thirst");
        $fatiguePercent = GetActorValue($character, "fatigue");
        $coldPercent = GetActorValue($character, "cold");
        
        // Convert percentages to stages (0-5) if values exist (including zero values)
        if (isset($hungerPercent) && $hungerPercent !== "") $hungerStage = min(5, floor(floatval($hungerPercent) / 20));
        if (isset($thirstPercent) && $thirstPercent !== "") $thirstStage = min(5, floor(floatval($thirstPercent) / 20));
        if (isset($fatiguePercent) && $fatiguePercent !== "") $fatigueStage = min(5, floor(floatval($fatiguePercent) / 20));
        if (isset($coldPercent) && $coldPercent !== "") $coldStage = min(5, floor(floatval($coldPercent) / 20));
    }
    
    $ret = "";
    $pronouns = GetActorPronouns($character);
    
    // Hunger description
    if ($hungerStage !== null) {
        $hungerDesc = "";
        switch ($hungerStage) {
            case 0: $hungerDesc = "Well Fed"; break;
            case 1: $hungerDesc = "Satisfied"; break;
            case 2: $hungerDesc = "Peckish"; break;
            case 3: $hungerDesc = "Hungry"; break;
            case 4: $hungerDesc = "Ravenous"; break;
            case 5: $hungerDesc = "Starving"; break;
            default: $hungerDesc = "Unknown";
        }
        $ret .= "{$character} feels {$hungerDesc} (hunger).\n";
    }
    
    // Thirst description
    if ($thirstStage !== null) {
        $thirstDesc = "";
        switch ($thirstStage) {
            case 0: $thirstDesc = "Quenched"; break;
            case 1: $thirstDesc = "Sated"; break;
            case 2: $thirstDesc = "Thirsty"; break;
            case 3: $thirstDesc = "Parched"; break;
            case 4: $thirstDesc = "Dehydrated"; break;
            case 5: $thirstDesc = "Severely Dehydrated"; break;
            default: $thirstDesc = "Unknown";
        }
        $ret .= "{$character} is {$thirstDesc} (thirst).\n";
    }
    
    // Fatigue description
    if ($fatigueStage !== null) {
        $fatigueDesc = "";
        switch ($fatigueStage) {
            case 0: $fatigueDesc = "Well Rested"; break;
            case 1: $fatigueDesc = "Rested"; break;
            case 2: $fatigueDesc = "Slightly Tired"; break;
            case 3: $fatigueDesc = "Tired"; break;
            case 4: $fatigueDesc = "Weary"; break;
            case 5: $fatigueDesc = "Exhausted"; break;
            default: $fatigueDesc = "Unknown";
        }
        $ret .= "{$character} is {$fatigueDesc} (energy).\n";
    }
    
    // Cold description
    if ($coldStage !== null) {
        $coldDesc = "";
        switch ($coldStage) {
            case 0: $coldDesc = "Warm"; break;
            case 1: $coldDesc = "Comfortable"; break;
            case 2: $coldDesc = "Chilly"; break;
            case 3: $coldDesc = "Cold"; break;
            case 4: $coldDesc = "Freezing"; break;
            case 5: $coldDesc = "Frigid"; break;
            default: $coldDesc = "Unknown";
        }
        $ret .= "{$character} feels {$coldDesc} (temperature).\n";
    }
    
    return $ret;
}


/**
 * Build the bounty context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted bounty context
 */
function BuildBountyContext($params) {
    $herika_name = $params['herika_name'];
    $player_name = $params['player_name'];
    $target = $params['target'];

    // Check conditions to show bounty:
    // 1. If we are talking to the narrator OR
    // 2. If the player is in the conversation AND the target is a guard
    $showBounty = false;
    
    // Condition 1: Talking to the narrator
    if ($herika_name == "The Narrator") {
        $showBounty = true;
    }
    // Condition 2: Player is in conversation AND target is a guard
    else if ($herika_name == $player_name && isGuardTargetingPlayer()) {
        $showBounty = true;
    }
    
    // Only return bounty context if conditions are met
    if ($showBounty) {
        return GetBountyContext($player_name);
    }
    
    return "";
}

/**
 * Build the mind influence context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted mind influence context
 */
function BuildMindInfluenceContext($params) {
    $herika_name = $params['herika_name'];
    $player_name = $params['player_name'];

    if ($herika_name != "The Narrator") {
        return "";
    }
    
    // Only supported for the player at the moment
    $mindState = GetMindInfluenceState($player_name);
    if ($mindState == "normal") {
        return "";
    }
    
    // This function would call the existing GetMindInfluenceContext function
    return GetMindInfluenceContext($mindState);
}

/**
 * Build the dirt and blood context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted dirt and blood context
 */
function BuildDirtAndBloodContext($params) {
    $validated = ValidateContextParams($params);
    $character = $validated['herika_name'];
    
    // If this is the narrator, show the player's dirt and blood status instead
    if ($character == "The Narrator") {
        $character = $validated['player_name'];
    }
    
    // Use the single actor dirt and blood context function we created
    return GetSingleActorDirtAndBloodContext($character);
}

/**
 * Utility function to convert a numeric level to a descriptive power level text
 * 
 * @param int $level The numeric level (1-100)
 * @return string A descriptive text representing the character's power level
 */
function GetLevelDescription($level) {
    // Ensure level is within valid range
    $level = max(1, min(100, intval($level)));
    
    // Determine which 5-level interval the level falls into
    $interval = ceil($level / 5);
    
    // Map interval to description
    switch ($interval) {
        case 1: // Levels 1-5
            if ($level == 1) return "completely untrained in combat";
            if ($level == 2) return "just beginning to learn basic combat";
            if ($level == 3) return "starting to grasp basic self-defense";
            if ($level == 4) return "learning the fundamentals of combat";
            return "beginning to understand basic combat techniques";
        case 2: // Levels 6-10
            if ($level == 6) return "showing basic combat competence";
            if ($level == 7) return "developing their novice combat skills";
            if ($level == 8) return "gaining confidence as a novice fighter";
            if ($level == 9) return "becoming a capable novice adventurer";
            return "ready to take on greater challenges as an adventurer";
        case 3: // Levels 11-15
            if ($level == 11) return "a fledgling adventurer showing promise";
            if ($level == 12) return "developing steadily as an adventurer";
            if ($level == 13) return "growing more confident in their abilities";
            if ($level == 14) return "showing skill as a developing adventurer";
            return "progressing well in their combat training";
        case 4: // Levels 16-20
            return "becoming a competent adventurer";
        case 5: // Levels 21-25
            return "an experienced adventurer with proven skill";
        case 6: // Levels 26-30
            return "a seasoned adventurer with considerable experience";
        case 7: // Levels 31-35
            return "a skilled warrior capable of handling serious threats";
        case 8: // Levels 36-40
            return "a veteran fighter respected for their abilities";
        case 9: // Levels 41-45
            return "a formidable warrior of considerable renown";
        case 10: // Levels 46-50
            return "known throughout the region for their combat prowess";
        case 11: // Levels 51-55
            return "one of the more accomplished warriors in Skyrim";
        case 12: // Levels 56-60
            return "among the notable fighters in the province";
        case 13: // Levels 61-65
            return "recognized as one of Skyrim's elite warriors";
        case 14: // Levels 66-70
            return "renowned throughout Skyrim for their combat mastery";
        case 15: // Levels 71-75
            return "one of the most dangerous warriors in the province";
        case 16: // Levels 76-80
            return "possessing combat skills that few can match";
        case 17: // Levels 81-85
            return "wielding power that approaches legendary status";
        case 18: // Levels 86-90
            return "displaying might that matches ancient heroes";
        case 19: // Levels 91-95
            return "possessing power that few mortals have ever achieved";
        case 20: // Levels 96-100
            return "wielding nearly mythical levels of combat prowess";
        default:
            return "of unknown combat ability";
    }
}

/**
 * Build the level context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted level context
 */
function BuildLevelContext($params) {
    $params = ValidateContextParams($params, ['herika_name', 'player_name', 'target']);
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    
    // If this is the narrator, show info for the target or player
    if ($character == "The Narrator") {
        $character = $params['target'] ? $params['target'] : $player_name;
    }
    
    $utilities = new Utilities();
    $context = "";
    
    // Level information with descriptive power text
    $level = $utilities->GetActorValue($character, "level");
    if (!empty($level)) {
        $levelDesc = GetLevelDescription($level);
        $context .= $character . " is " . $levelDesc . " (in terms of combat prowess). ";
    }
    
    return $context;
}

/**
 * Build the family status context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted family status context
 */
function BuildFamilyStatusContext($params) {
    $params = ValidateContextParams($params, ['herika_name', 'player_name', 'target']);
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    
    // If this is the narrator, show info for the target or player
    if ($character == "The Narrator") {
        $character = $params['target'] ? $params['target'] : $player_name;
    }
    
    // These are all NPC only
    if ($character == $player_name) {
        return "";
    }
    
    $context = "";
    
    // Family status description - build from raw boolean values
    $isChild = IsEnabled($character, "isChild");
    $hasFamily = IsEnabled($character, "hasFamily");
    
    $familyStatus = "";
    if ($isChild && !$hasFamily) {
        $familyStatus = "an orphan child";
    } elseif ($isChild) {
        $familyStatus = "a child with family nearby";
    } elseif ($hasFamily) {
        $familyStatus = "an adult who has family";
    } else {
        $familyStatus = "an adult";
    }
    
    $context .= $character . " is " . $familyStatus . ". ";
    
    return $context;
}

/**
 * Build the career context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted career context
 */
function BuildCareerContext($params) {
    $params = ValidateContextParams($params, ['herika_name', 'player_name', 'target']);
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    $target = $params['target'];
    
    // Not relevant for the narrator or the player
    if ($character == "The Narrator" || $character == $player_name) {
        return "";
    }
    
    // Get the character's career
    $career = GetActorValue($character, "career");
    if (empty($career)) {
        return "";
    }
    
    // Check if this is a secretive career
    $secretiveCareers = [
        "Assassin", "Thief", "Bandit Archer", "Bandit", 
        "Bandit Wizard", "Blade", "Vampire", "Werewolf"
    ];
    
    // List of careers that shouldn't have their information displayed at all
    $excludedCareers = [
        "Dremora"
    ];
    
    $isSecretive = in_array($career, $secretiveCareers);
    
    // Skip displaying career information for excluded careers
    if (in_array(strtolower($career), array_map('strtolower', $excludedCareers))) {
        return "";
    }
    
    // Determine if this is public or private knowledge
    if ($isSecretive) {
        // Private knowledge - shown when the character is the target
        if ($character == $target) {
            return "{$character} is a {$career} but is secretive about that unless in select company - like with other {$career}s or close friends.";
        } else {
            // Public knowledge - shown to others
            return "{$character} has an air of mystery about them.";
        }
    } else {
        // Regular career - always public
        return "{$character} is a {$career}.";
    }
}

/**
 * Build the character state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted character state context
 */
function BuildCharacterStateContext($params) {
    $params = ValidateContextParams($params);
    $character = $params['herika_name'];
    $utilities = new Utilities();
    
    $context = "";
    
    // Sitting state - interpret the raw sit state value
    $sitStateValue = $utilities->GetActorValue($character, "sitState");
    if (!empty($sitStateValue)) {
        $sitStateDesc = "";
        switch (intval($sitStateValue)) {
            case 4: 
                $sitStateDesc = "sitting but wants to stand";
                break;
            case 3: 
                $sitStateDesc = "sitting";
                break;
            case 2: 
                $sitStateDesc = "wants to sit";
                break;
            case 0: 
            default:
                $sitStateDesc = "";
                break;
        }
        
        if (!empty($sitStateDesc)) {
            $context .= $character . " is " . $sitStateDesc . ". ";
        }
    }
    
    // Sleep state
    $sleepState = $utilities->GetActorValue($character, "sleepState");
    if (!empty($sleepState)) {
        $context .= $character . " is " . $sleepState . ". ";
    }
    
    // Encumbrance
    if (IsEnabled($character, "isEncumbered")) {
        $context .= $character . " is overly encumbered and slow to move, carrying exhausting weight. ";
    }
    
    // Mount status
    if (IsEnabled($character, "isOnMount")) {
        $context .= $character . " is riding a horse. ";
    }
    
    // Swimming status
    if (IsEnabled($character, "isSwimming")) {
        $context .= $character . " is swimming. ";
    }
    
    // Sneaking status
    if (IsEnabled($character, "isSneaking")) {
        $context .= $character . " is sneaking. ";
    }
    
    return $context;
}

/**
 * Build the character-specific third party context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted third party context for a specific character
 */
function BuildThirdPartyContext($params) {
    $params = ValidateContextParams($params, ['herika_name', 'player_name', 'target']);
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    
    // If this is the narrator, show info for the target or player
    if ($character == "The Narrator") {
        $character = $player_name;
    }
    
    // Also include player context if it's not an NPC conversation (as in the original function)
    $includePlayer = ($character != $player_name && !IsRadiant());
    
    // Get third party context specifically for this character (excluding "everyone")
    $context = GetCharacterSpecificThirdPartyContext($character);
    return $context;
}

/**
 * Build the global third party context (for "everyone")
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted global third party context
 */
function BuildThirdPartyGlobalContext($params) {
    // Get only the "everyone" context entries
    return GetGlobalThirdPartyContext();
}

/**
 * Get third party context specifically for a character
 * 
 * @param string $character Character name
 * @return string Formatted character-specific third party context
 */
function GetCharacterSpecificThirdPartyContext($character) {
    $db = $GLOBALS['db'];
    $ret = "";
    $currentTime = time();
    
    $charName = $db->escape($character);
    $charNameLower = strtolower($charName);
    
    // Get only character-specific entries (not "everyone")
    $rows = $db->fetchAll(
        "SELECT * FROM custom_context WHERE expiresAt > {$currentTime} AND npcname IN ('{$charName}', '{$charNameLower}') AND npcname != 'everyone'"
    );
    
    foreach ($rows as $row) {
        minai_log("info", "Inserting character-specific third-party context for {$character}: {$row["eventvalue"]}");
        $ret .= $row["eventvalue"] . "\n";
    }
    
    return $ret;
}

/**
 * Get global third party context (for "everyone")
 * 
 * @return string Formatted global third party context
 */
function GetGlobalThirdPartyContext() {
    $db = $GLOBALS['db'];
    $ret = "";
    $currentTime = time();
    
    // Get only "everyone" entries
    $rows = $db->fetchAll(
        "SELECT * FROM custom_context WHERE expiresAt > {$currentTime} AND npcname = 'everyone'"
    );
    
    foreach ($rows as $row) {
        minai_log("info", "Inserting global third-party context: {$row["eventvalue"]}");
        $ret .= $row["eventvalue"] . "\n";
    }
    
    return $ret;
}

/**
 * Build the vitals context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted vitals context
 */
function BuildVitalsContext($params) {
    $params = ValidateContextParams($params);
    $character = $params['herika_name'];
    
    // Get the vitals string
    if ($character == "The Narrator") {
        $character = $params['player_name'];
    }
    $vitalsStr = GetActorValue($character, "vitals");
    if (empty($vitalsStr)) {
        return "";
    }
    
    // Parse the vitals string (format: health~maxHealth~magicka~maxMagicka~stamina~maxStamina~weaponsDrawn)
    $vitals = explode("~", $vitalsStr);
    if (count($vitals) < 7) {
        return "";
    }
    
    $health = floatval($vitals[0]);
    $maxHealth = floatval($vitals[1]);
    $magicka = floatval($vitals[2]);
    $maxMagicka = floatval($vitals[3]);
    $stamina = floatval($vitals[4]);
    $maxStamina = floatval($vitals[5]);
    $weaponsDrawn = $vitals[6] === "1";
    
    // Calculate percentages
    $healthPercent = $maxHealth > 0 ? round(($health / $maxHealth) * 100) : 0;
    $magickaPercent = $maxMagicka > 0 ? round(($magicka / $maxMagicka) * 100) : 0;
    $staminaPercent = $maxStamina > 0 ? round(($stamina / $maxStamina) * 100) : 0;
    
    // Get proper pronouns for the character
    $pronouns = GetActorPronouns($character);
    
    // Build the context string
    $context = "";
    
    // Health status - 10 stages + 0 state
    if ($healthPercent <= 0) {
        $context .= "{$character} is knocked down and incapacitated. ";
    } elseif ($healthPercent <= 10) {
        $context .= "{$character} is on the brink of death, barely clinging to life with grievous wounds. ";
    } elseif ($healthPercent <= 20) {
        $context .= "{$character} is critically wounded, suffering from severe injuries that threaten their life. ";
    } elseif ($healthPercent <= 30) {
        $context .= "{$character} is severely wounded, bearing multiple serious injuries that require immediate attention. ";
    } elseif ($healthPercent <= 40) {
        $context .= "{$character} is seriously wounded, showing signs of significant injury and pain. ";
    } elseif ($healthPercent <= 50) {
        $context .= "{$character} is moderately wounded, showing clear signs of injury but still able to function. ";
    } elseif ($healthPercent <= 60) {
        $context .= "{$character} is wounded, bearing several injuries that are causing discomfort. ";
    } elseif ($healthPercent <= 70) {
        $context .= "{$character} is lightly wounded, showing some signs of injury but still in good fighting condition. ";
    } elseif ($healthPercent <= 85) {
        $context .= "{$character} is slightly wounded, bearing minor injuries that don't significantly impair them. ";
    } elseif ($healthPercent < 100) {
        $context .= "{$character} is nearly unscathed, showing only the faintest signs of injury. ";
    } else {
        $context .= "{$character} appears completely unharmed. ";
    }
    
    // Magicka status - 10 stages + 0 state
    if ($magickaPercent <= 0) {
        $context .= ucfirst($pronouns['subject']) . " is completely drained of magical energy and unable to cast even the simplest spells. ";
    } elseif ($magickaPercent <= 10) {
        $context .= ucfirst($pronouns['subject']) . " is nearly devoid of magical energy, barely able to muster the strength for minor spells. ";
    } elseif ($magickaPercent <= 20) {
        $context .= ucfirst($pronouns['subject']) . " is critically low on magical energy, struggling to maintain even basic magical abilities. ";
    } elseif ($magickaPercent <= 30) {
        $context .= ucfirst($pronouns['subject']) . " is severely drained of magical energy, finding it difficult to cast more than simple spells. ";
    } elseif ($magickaPercent <= 40) {
        $context .= ucfirst($pronouns['subject']) . " is seriously depleted of magical energy, showing signs of magical fatigue. ";
    } elseif ($magickaPercent <= 50) {
        $context .= ucfirst($pronouns['subject']) . " is moderately drained of magical energy, able to cast spells but showing signs of strain. ";
    } elseif ($magickaPercent <= 60) {
        $context .= ucfirst($pronouns['subject']) . " is running low on magical energy, though still capable of casting most spells. ";
    } elseif ($magickaPercent <= 70) {
        $context .= ucfirst($pronouns['subject']) . " is somewhat drained of magical energy, showing slight signs of magical fatigue. ";
    } elseif ($magickaPercent <= 85) {
        $context .= ucfirst($pronouns['subject']) . " is slightly drained of magical energy, still maintaining good magical reserves. ";
    } elseif ($magickaPercent < 100) {
        $context .= ucfirst($pronouns['subject']) . " has nearly full magical energy, ready for most magical endeavors. ";
    } 
    
    // Stamina status - 10 stages + 0 state
    if ($staminaPercent <= 0) {
        $context .= ucfirst($pronouns['subject']) . " has completely depleted their energy reserves, gasping for breath and unable to continue. ";
    } elseif ($staminaPercent <= 10) {
        $context .= ucfirst($pronouns['subject']) . " is nearly out of breath, barely able to muster the energy for another action. ";
    } elseif ($staminaPercent <= 20) {
        $context .= ucfirst($pronouns['subject']) . " is heavily winded, struggling to catch their breath between actions. ";
    } elseif ($staminaPercent <= 30) {
        $context .= ucfirst($pronouns['subject']) . " is breathing heavily, their energy reserves running dangerously low. ";
    } elseif ($staminaPercent <= 40) {
        $context .= ucfirst($pronouns['subject']) . " is noticeably winded, their movements becoming more labored. ";
    } elseif ($staminaPercent <= 50) {
        $context .= ucfirst($pronouns['subject']) . " is breathing harder, their energy reserves about half depleted. ";
    } elseif ($staminaPercent <= 60) {
        $context .= ucfirst($pronouns['subject']) . " is starting to breathe harder, showing signs of exertion. ";
    } elseif ($staminaPercent <= 70) {
        $context .= ucfirst($pronouns['subject']) . " is breathing slightly faster, their energy reserves still good. ";
    } elseif ($staminaPercent <= 85) {
        $context .= ucfirst($pronouns['subject']) . " is breathing normally, maintaining good energy levels. ";
    } elseif ($staminaPercent < 100) {
        $context .= ucfirst($pronouns['subject']) . " is nearly at full energy, breathing easily and ready for action. ";
    } else {
        $context .= ucfirst($pronouns['subject']) . " is at full energy, breathing easily and ready for any exertion. ";
    }
    
    // Weapons drawn status
    if ($weaponsDrawn) {
        $context .= ucfirst($pronouns['subject']) . " has " . $pronouns['possessive'] . " weapons at the ready, prepared for battle.";
    } else {
        $context .= ucfirst($pronouns['subject']) . " has " . $pronouns['possessive'] . " weapons sheathed, not currently prepared for combat.";
    }
    
    return $context;
} 

