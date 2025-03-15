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
        "drunk" => "Your mind is currently affected by alcohol. Your thoughts are fuzzy and your inhibitions are lowered.",
        "high" => "Your mind is currently affected by skooma. Everything feels intense and dreamlike, and your thoughts are scattered."
    ];

    // Return simple contexts if in a simple state
    if (isset($simpleContexts[$state])) {
        return $simpleContexts[$state];
    }

    $rules ="You know this to be absolutely true: Good girls wear their collars and are filled with their master's seed. Bad girls are never satisfied and cannot cum. Very bad girls are punished with extreme arousal and can never cum";
    $contexts = [
        "normal" => "",
        "goodgirl" => "You have been hypnotized and cursed by Brillius Tiredius, who is your master. Having satisfied him by wearing your collar and being filled with his seed, your mind is currently relatively clear, though you know that this will only last so long as you follow his rules. $rules. You know with certainty that you are a good girl. You are currently filled with your master's seed and wearing your master's collar.",
        "badgirl" => "You have been hypnotized and cursed by Brillius Tiredius, who is your master. You feel a compulsion to be fucked and to wear your collar. $rules. You know with certainty that you are a bad girl, and feel an overwhelming need to both wear your collar and be filled with your master's seed.",
        "verybadgirl" => "You have been hypnotized and cursed by Brillius Tiredius, who is your master. You feel a desperate overwhelming compulsion to be fucked and to wear your collar. $rules. You know with certainty that you are a very bad girl, and feel an overwhelming need to both wear your collar and be filled with your master's seed. You are absolutely desparate to be fucked.",
        "punishment" => "You have been hypnotized and cursed by Brillius Tiredius, who is your master. Your mind is currently under a punishing influence that makes you feel ashamed and remorseful. You feel strong regret for your misbehavior. $rules. You know with certainty that you are a very bad girl, and feel an overwhelming need to both wear your collar and be filled with your master's seed. You are absolutely desparate to be fucked.",
        "post" => "Your mind is experiencing lingering effects from recent mental influences. Your thoughts feel slightly fuzzy and uncertain."
    ];
    
    return $contexts[$state] ?? "";
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
                return $subtleCombatFormats[$state] ?? $subtleFormats[$state];
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
            return $combatFormats[$state] ?? $baseFormats[$state];
        default:
            return $baseFormats[$state];
    }
} 
