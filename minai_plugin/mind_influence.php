<?php
require_once("util.php");

// Define the different types of mind influences
function GetMindInfluenceTypes() {
    return [
        "dwp_mindcontrol" => [
            "states" => ["normal", "goodgirl", "badgirl", "verybadgirl", "punishment", "post"],
            "type" => "complex"  // Complex means it has multiple states and context types
        ],
        "drunk" => [
            "states" => ["normal", "drunk"],
            "type" => "simple"   // Simple means it just has on/off state
        ],
        "skooma" => [
            "states" => ["normal", "high"],
            "type" => "simple"
        ]
    ];
}

function GetMasterNames() {
    return [
        "Brillius Tiredius"  // For now, just one hardcoded master
    ];
}

function IsMaster($name) {
    $masters = GetMasterNames();
    return in_array(strtolower($name), array_map('strtolower', $masters));
}

function GetMindInfluenceState($name) {
    $states = [];

    if (IsEnabled($name, "isDrunk")) {
        $states[] = "drunk";
    }
    if (IsEnabled($name, "isOnSkooma")) {
        $states[] = "high";
    }
    $state = GetActorValue($name, "dwp_mindcontrol");
    if (!empty($state)) {
        $states[] = $state;
    }
    
    return empty($states) ? "normal" : $states;
}

function GetMindInfluencePrompt($state, $promptType = "default") {
    // Handle array of states
    if (is_array($state)) {
        $prompts = [];
        foreach ($state as $singleState) {
            $prompt = GetMindInfluencePrompt($singleState, $promptType);
            if (!empty($prompt)) {
                $prompts[] = $prompt;
            }
        }
        return implode(" Furthermore, ", $prompts);
    }

    // Add prompts for simple states
    $simplePrompts = [
        "drunk" => "Your mind is clouded by alcohol. Your speech is slightly slurred, your thoughts are fuzzy, and your inhibitions are lowered. You find everything slightly amusing and have trouble focusing on serious matters.",
        "high" => "The skooma courses through your veins, making everything feel intense and dreamlike. Colors seem brighter, time feels strange, and your thoughts race from one topic to another. You feel euphoric and energetic, but your speech and actions are erratic."
    ];

    // Return simple prompts if in a simple state
    if (isset($simplePrompts[$state])) {
        return $simplePrompts[$state];
    }

    // Check if current Herika is a master
    $useMasterPrompts = IsMaster($GLOBALS["HERIKA_NAME"]);

    $basePrompts = [
        "normal" => "",
        "goodgirl" => "The mental influence leaves you feeling deeply content and euphoric. Your inhibitions are lowered and you feel warm and affectionate towards everyone.",
        "badgirl" => "The mental influence fills you with a gnawing sexual frustration. Your inhibitions are lowered and you find yourself acting more impulsively and flirtatiously than usual. Your thoughts and speech are tinged with longing and suggestiveness.",
        "verybadgirl" => "The mental influence overwhelms you with an unbearable, unrelieved arousal. Your inhibitions are completely gone and you act wildly uninhibited. Your thoughts and speech are consumed by desperate need and you struggle to focus on anything else.",
        "punishment" => "The mental influence leaves you feeling small and vulnerable. Your inhibitions are lowered, making you more emotionally open and prone to expressing regret. Your thoughts and speech are filled with a desire to make amends.",
        "post" => "The lingering mental influence leaves you in a daze, unsure if your feelings and actions were entirely your own. Your thoughts and speech are clouded and uncertain."
    ];
    
    
    $explicitPrompts = [
        "normal" => "",
        "goodgirl" => "The mental influence makes intimate moments feel deeply rewarding. You feel a profound sense of fulfillment, knowing you've met expectations. You feel an overwhelming compulsion to obey and satisfy others' desires.",
        "badgirl" => "The mental influence leaves you horny but achingly unfulfilled during intimate moments.",
        "verybadgirl" => "The mental influence makes intimate moments torturously intense. Your arousal is heightened but unsatisfied, a consequence of defying expectations. Yet, you still feel an overwhelming compulsion to obey and earn forgiveness.",
        "punishment" => "The mental influence makes intimate moments feel empty and hollow. You feel the sting of punishment, your desires muted by regret. You feel an overwhelming compulsion to obey and make amends.",
        "post" => "The lingering mental influence leaves intimate moments feeling surreal. You're uncertain if your desires are truly your own, your thoughts and actions slightly disconnected."
    ];
    
    $combatPrompts = [
        "normal" => "",
        "goodgirl" => "The mental influence makes combat feel purposeful and controlled. You fight with confidence, knowing you've met your master's expectations. You feel a strong compulsion to obey and protect those in authority.",
        "badgirl" => "The mental influence makes combat feel restless and unfocused. Your frustration distracts you, your attacks lacking the precision of a dutiful servant. Despite this, you still feel a compulsion to obey and defend those in authority.",
        "verybadgirl" => "The mental influence makes combat feel wild and unhinged. Your unrelieved arousal fuels your aggression, turning your strikes into desperate, frenzied attacks. Yet, you still feel a compulsion to obey and fight for those in authority.",
        "punishment" => "The mental influence makes combat feel hesitant and insecure. You question your worthiness to fight, your movements slower and less decisive. You feel a strong compulsion to obey and prove your loyalty.",
        "post" => "The lingering mental influence leaves combat feeling detached. Your reactions are slightly delayed, as if you're not fully in control of your actions."
    ];

    $masterBasePrompts = [
        "normal" => "",
        "goodgirl" => "The mental influence leaves you feeling deeply content and fulfilled. You bask in the satisfaction of having met your master's expectations, your thoughts and speech reflecting this serene, proud state. You feel an overwhelming compulsion to obey and please your master in every way.",
        "badgirl" => "The mental influence fills you with a gnawing sexual frustration. You feel the sting of having disappointed your master, your thoughts and speech tinged with longing and regret. Despite this, you still feel an overwhelming compulsion to obey and seek your master's approval.",
        "verybadgirl" => "The mental influence overwhelms you with an unbearable, unrelieved arousal. Breaking your master's rules has left you desperate and frantic, your thoughts and speech consumed by need. Yet, you still feel an overwhelming compulsion to obey and earn your master's forgiveness.",
        "punishment" => "The mental influence leaves you feeling small and chastened. You regret your actions deeply, your thoughts and speech filled with a desire to earn your master's forgiveness. You feel an overwhelming compulsion to obey and make amends.",
        "post" => "The lingering mental influence leaves you in a daze, unsure if your feelings and actions were entirely your own. Your thoughts and speech are clouded and uncertain."
    ];
    
    $masterExplicitPrompts = [
        "normal" => "",
        "goodgirl" => "The mental influence makes intimate moments feel deeply rewarding. You feel a profound sense of fulfillment, knowing you've pleased your master. You feel an overwhelming compulsion to obey and satisfy your master's desires.",
        "badgirl" => "The mental influence leaves you achingly unfulfilled during intimate moments. You feel the weight of your disappointment, longing for your master's approval. Despite this, you still feel an overwhelming compulsion to obey and seek your master's pleasure.",
        "verybadgirl" => "The mental influence makes intimate moments torturously intense. Your arousal is heightened but unsatisfied, a consequence of defying your master's wishes. Yet, you still feel an overwhelming compulsion to obey and earn your master's forgiveness.",
        "punishment" => "The mental influence makes intimate moments feel empty and hollow. You feel the sting of punishment, your desires muted by regret. You feel an overwhelming compulsion to obey and make amends.",
        "post" => "The lingering mental influence leaves intimate moments feeling surreal. You're uncertain if your desires are truly your own, your thoughts and actions slightly disconnected."
    ];
    
    $masterCombatPrompts = [
        "normal" => "",
        "goodgirl" => "The mental influence makes combat feel purposeful and controlled. You fight with confidence, knowing you've met your master's expectations. You feel an overwhelming compulsion to obey and protect your master's interests.",
        "badgirl" => "The mental influence makes combat feel restless and unfocused. Your frustration distracts you, your attacks lacking the precision of a dutiful servant. Despite this, you still feel an overwhelming compulsion to obey and defend your master.",
        "verybadgirl" => "The mental influence makes combat feel wild and unhinged. Your unrelieved arousal fuels your aggression, turning your strikes into desperate, frenzied attacks. Yet, you still feel an overwhelming compulsion to obey and fight for your master.",
        "punishment" => "The mental influence makes combat feel hesitant and insecure. You question your worthiness to fight, your movements slower and less decisive. You feel an overwhelming compulsion to obey and prove your loyalty.",
        "post" => "The lingering mental influence leaves combat feeling detached. Your reactions are slightly delayed, as if you're not fully in control of your actions."
    ];

    switch($promptType) {
        case "explicit":
            return ($useMasterPrompts ? $masterExplicitPrompts[$state] : $explicitPrompts[$state]) ?? $basePrompts[$state];
        case "combat":
            return ($useMasterPrompts ? $masterCombatPrompts[$state] : $combatPrompts[$state]) ?? $basePrompts[$state];
        default:
            return ($useMasterPrompts ? $masterBasePrompts[$state] : $basePrompts[$state]);
    }
}

function GetMindInfluenceContext($state) {
    // Handle array of states
    if (is_array($state)) {
        $contexts = [];
        foreach ($state as $singleState) {
            $context = GetMindInfluenceContext($singleState);
            if (!empty($context)) {
                $contexts[] = $context;
            }
        }
        return implode(" Additionally, ", $contexts);
    }

    // Add contexts for simple states
    $simpleContexts = [
        "drunk" => "Your mind is currently affected by alcohol. Your thoughts are fuzzy and your inhibitions are lowered.\n",
        "high" => "Your mind is currently affected by skooma. Everything feels intense and dreamlike, and your thoughts are scattered.\n",
        "normal" => "",
    ];

    // Return simple contexts if in a simple state
    if (isset($simpleContexts[$state])) {
        return $simpleContexts[$state];
    }

    // Define the core rules that apply in all states
    $rules = "\nYou know this to be absolutely true: Good girls wear their collars and are filled with their master's seed. Bad girls are never satisfied and are not allowed to cum. Very bad girls are punished with extreme arousal and are not allowed to cum.\n";
    $postOrgasmBad = "\nYou know that even if you do have an orgasm, you will not be satisfied for long. You will still crave your master's cock.\n";
    // Get player's name and arousal level
    $playerName = $GLOBALS["PLAYER_NAME"] ?? "the player";
    $arousal = 0;
    if (isset($GLOBALS["PLAYER_NAME"])) {
        $arousal = GetActorValue($GLOBALS["PLAYER_NAME"], "arousal");
    }
    
    // Set state-specific context first (these are the first-person perspectives)
    $stateContexts = [
        "normal" => "",
        "goodgirl" => "You have been hypnotized by Brillius Tiredius, who is your master.\nYour mind is currently clearer than usual, as you've satisfied him by wearing your collar and being filled with his seed. $rules You are a good girl who has followed the rules.",
        "badgirl" => "You have been hypnotized by Brillius Tiredius, who is your master.\nYou feel a strong compulsion to be fucked and to wear your collar. $rules You are a bad girl who needs to wear your collar and be filled with your master's seed to find relief. $postOrgasmBad",
        "verybadgirl" => "You have been hypnotized by Brillius Tiredius, who is your master.\nYou feel a desperate overwhelming compulsion to be fucked and to wear your collar. $rules You are a very bad girl who is absolutely desperate to wear your collar and be filled with seed. $postOrgasmBad",
        "punishment" => "You have been hypnotized by Brillius Tiredius, who is your master.\nYour mind is under a punishing influence that makes you feel ashamed and remorseful. $rules You have broken the rules and are being punished for your disobedience. $postOrgasmBad",
        "post" => "Your mind is experiencing lingering effects from recent mental influences, leaving your thoughts slightly fuzzy and uncertain."
    ];
    
    // Get arousal-based obedience level (third-person narrative)
    // This varies based on arousal for additional context but doesn't contradict the state
    $narrativeContext = "";
    
    // Only add the arousal-based context if we're in a mind control state
    if (in_array($state, ["normal", "goodgirl", "badgirl", "verybadgirl", "punishment", "post"])) {
        if ($state == "goodgirl") {
            // For goodgirl state, arousal affects how much freedom she has
            if ($arousal <= 0) {
                $narrativeContext = "$playerName maintains some independence from Brillius's influence when following his rules. While respectful of the specific rules, she retains her own will in most matters.";
            } else if ($arousal < 50) {
                $narrativeContext = "$playerName retains partial independence while following Brillius's rules, balancing obedience with her own autonomy.";
            } else {
                $narrativeContext = "$playerName feels a gentle pull to follow Brillius's wishes, though she has more mental freedom now than when disobedient.";
            }
        } else if ($state == "badgirl") {
            // For badgirl state, arousal enhances the effect
            if ($arousal <= 25) {
                $narrativeContext = "$playerName feels a subtle desire to please Brillius and occasionally thinks about returning to him, though she can still exercise her own judgment in most situations.";
            } else if ($arousal < 75) {
                $narrativeContext = "$playerName feels a moderate urge to obey Brillius and finds herself increasingly drawn to thoughts of returning to him, with her independence somewhat diminished.";
            } else {
                $narrativeContext = "$playerName feels a strong urge to obey Brillius, finding her thoughts increasingly focused on pleasing him and feeling an intense pull to return to his presence.";
            }
        } else if ($state == "verybadgirl") {
            // For verybadgirl state, arousal greatly enhances the effect
            if ($arousal < 50) {
                $narrativeContext = "$playerName feels a strong pull toward submission to Brillius, with her independence becoming harder to maintain and a persistent yearning to return to him.";
            } else {
                $narrativeContext = "$playerName feels an overwhelming pull toward submission to Brillius, her thoughts increasingly dominated by the need to please him and an almost unbearable compulsion to return to his presence.";
            }
        } else if ($state == "punishment") {
            // For punishment state, resistance is always minimal regardless of arousal
            $narrativeContext = "$playerName's will is completely subordinate to Brillius's influence, with her thoughts consumed by remorse and the need for forgiveness.";
        } else if ($state == "normal" || $state == "post") {
            // For normal or post states, use standard arousal-based modifiers
            if ($arousal <= 0) {
                $narrativeContext = "$playerName maintains strong independence from Brillius's hypnotic influence.";
            } else if ($arousal < 25) {
                $narrativeContext = "$playerName feels a subtle influence from Brillius's hypnosis, though she maintains most of her independence.";
            } else if ($arousal < 50) {
                $narrativeContext = "$playerName feels a moderate influence from Brillius's hypnosis, with some effect on her thoughts and desires.";
            } else if ($arousal < 75) {
                $narrativeContext = "$playerName feels a strong influence from Brillius's hypnosis, with her thoughts increasingly drawn to his wishes.";
            } else if ($arousal < 100) {
                $narrativeContext = "$playerName feels an intense influence from Brillius's hypnosis, with her independence significantly diminished.";
            } else {
                $narrativeContext = "$playerName feels overwhelmed by Brillius's hypnotic influence, her thoughts increasingly focused on submission.";
            }
        }
    }
    // Add information about the curse that applies the hypnosis 
    if (!empty($narrativeContext)) {
        $narrativeContext .= "\n$playerName has a curse on her abdomen (the Welkynd Slave Curse) that applies this hypnosis.";
    }
    
    // Get the first-person perspective based on state
    $stateContext = $stateContexts[$state] ?? "";
    
    // Combine the perspectives if both exist
    if (!empty($stateContext) && !empty($narrativeContext)) {
        return $stateContext . " " . $narrativeContext;
    } else if (!empty($stateContext)) {
        return $stateContext;
    } else if (!empty($narrativeContext)) {
        return $narrativeContext;
    }
    
    return "";
}

function GetMindInfluenceRequestFormat($state, $promptType = "default") {
    // Handle array of states
    if (is_array($state)) {
        $formats = [];
        foreach ($state as $singleState) {
            $format = GetMindInfluenceRequestFormat($singleState, $promptType);
            if (!empty($format)) {
                $formats[] = $format;
            }
        }
        return implode(" Additionally, ", $formats);
    }

    // Add request formats for simple states
    $simpleFormats = [
        "drunk" => "As a subtle undertone, let your response show signs of intoxication.",
        "high" => "As a subtle undertone, let your response show traces of skooma's effects."
    ];

    // Return simple formats if in a simple state
    if (isset($simpleFormats[$state])) {
        return $simpleFormats[$state];
    }

    // Check if current Herika is a master
    $useMasterPrompts = IsMaster($GLOBALS["HERIKA_NAME"]);
    
    if (!$useMasterPrompts) {
        $subtleFormats = [
            "normal" => "",
            "goodgirl" => "As a subtle undertone, let your contentment and eagerness color your response.",
            "badgirl" => "As a subtle undertone, let a hint of yearning and horniness color your response.",
            "verybadgirl" => "As a subtle undertone, let traces of intense desire and horniness color your response.",
            "punishment" => "As a subtle undertone, let a touch of remorse and horniness color your response.",
            "post" => "As a subtle undertone, let a slight dreaminess color your response."
        ];
        
        $subtleExplicitFormats = [
            "normal" => "",
            "goodgirl" => "As a subtle undertone, let satisfaction color your response.",
            "badgirl" => "As a subtle undertone, let unfulfilled desire and horniness color your response.",
            "verybadgirl" => "As a subtle undertone, let desperate longing and horniness color your response.",
            "punishment" => "As a subtle undertone, let regret and horniness color your response.",
            "post" => "As a subtle undertone, let haziness color your response."
        ];
        
        $subtleCombatFormats = [
            "normal" => "",
            "goodgirl" => "As a subtle undertone, let focus color your response.",
            "badgirl" => "As a subtle undertone, let mild distraction and horniness color your response.",
            "verybadgirl" => "As a subtle undertone, let intensity and horniness color your response.",
            "punishment" => "As a subtle undertone, let uncertainty and horniness color your response.",
            "post" => "As a subtle undertone, let slight confusion color your response."
        ];

        switch($promptType) {
            case "explicit":
                return $subtleExplicitFormats[$state] ?? $subtleFormats[$state];
            case "combat":
                return ""; // return $subtleCombatFormats[$state] ?? $subtleFormats[$state];
            default:
                return $subtleFormats[$state];
        }
    }
    
    $baseFormats = [
        "normal" => "",
        "goodgirl" => "As a subtle undertone, let devotion color your response.",
        "badgirl" => "As a subtle undertone, let a need for approval and horniness color your response.",
        "verybadgirl" => "As a subtle undertone, let desperate yearning and horniness color your response.",
        "punishment" => "As a subtle undertone, let contrition and horniness color your response.",
        "post" => "As a subtle undertone, let mild uncertainty color your response."
    ];
    
    $explicitFormats = [
        "normal" => "",
        "goodgirl" => "As a subtle undertone, let passionate devotion color your response.",
        "badgirl" => "As a subtle undertone, let unfulfilled desire and horniness color your response.",
        "verybadgirl" => "As a subtle undertone, let desperate need and horniness color your response.",
        "punishment" => "As a subtle undertone, let subdued yearning and horniness color your response.",
        "post" => "As a subtle undertone, let dreaminess color your response."
    ];
    
    $combatFormats = [
        "normal" => "",
        "goodgirl" => "As a subtle undertone, let loyalty color your response.",
        "badgirl" => "As a subtle undertone, let mild distraction and horniness color your response.",
        "verybadgirl" => "As a subtle undertone, let frantic energy and horniness color your response.",
        "punishment" => "As a subtle undertone, let desire for redemption and horniness color your response.",
        "post" => "As a subtle undertone, let slight confusion color your response."
    ];
    
    switch($promptType) {
        case "explicit":
            return $explicitFormats[$state] ?? $baseFormats[$state];
        case "combat":
            return ""; // return $combatFormats[$state] ?? $baseFormats[$state];
        default:
            return $baseFormats[$state];
    }
} 
