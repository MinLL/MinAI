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
        "goodgirl" => "The mental influence leaves you feeling deeply content and fulfilled. You bask in the satisfaction of having met your master's expectations, your thoughts and speech reflecting this serene, proud state. You feel a strong compulsion to obey and please those in authority.",
        "badgirl" => "The mental influence fills you with a gnawing sexual frustration. You feel the sting of having disappointed your master, your thoughts and speech tinged with longing and regret. Despite this, you still feel a compulsion to obey and seek approval from those in authority.",
        "verybadgirl" => "The mental influence overwhelms you with an unbearable, unrelieved arousal. Breaking your master's rules has left you desperate and frantic, your thoughts and speech consumed by need. Yet, you still feel a compulsion to obey and earn forgiveness from those in authority.",
        "punishment" => "The mental influence leaves you feeling small and chastened. You regret your actions deeply, your thoughts and speech filled with a desire to make amends. You feel a strong compulsion to obey and prove your loyalty.",
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

    $contexts = [
        "normal" => "",
        "goodgirl" => "Your mind is currently under an influence that makes you docile and obedient. You feel a strong compulsion to be good and follow instructions. You know this to be absolutely true: Good girls wear their collars and are filled with their master's seed. You know with certainty that you are a good girl.",
        "badgirl" => "Your mind is currently under an influence that makes you docile and obedient. You feel a strong compulsion to be good and follow instructions. You know this to be absolutely true: Bad girls are never satisfied and cannot cum. You know with certainty that you are a bad girl.",
        "verybadgirl" => "Your mind is currently under an influence that makes you docile and obedient. You feel a strong compulsion to be good and follow instructions. You know this to be absolutely true: Very bad girls are punished with extreme arousal and can never cum. You know with certainty that you are a very bad girl.",
        "punishment" => "Your mind is currently under a punishing influence that makes you feel ashamed and remorseful. You feel strong regret for your misbehavior.",
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
        "drunk" => "Remember that you are intoxicated. Your response should be slightly slurred and show reduced inhibitions.",
        "high" => "Remember that you are under the effects of skooma. Your response should be energetic and slightly erratic."
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
            "goodgirl" => "Your response should subtly convey your deep satisfaction and eagerness, while avoiding direct references to your state.",
            "badgirl" => "Your response should hint at your aching need and desperate arousal through metaphor and suggestion rather than explicit statements.",
            "verybadgirl" => "Your response should subtly reveal your torturous, overwhelming desire through implication rather than direct expression.",
            "punishment" => "Your response should be subdued, tinged with yearning, expressed through indirect references.",
            "post" => "Your response should have a dreamy, wanting undertone without explicitly mentioning your mental state."
        ];
        
        $subtleExplicitFormats = [
            "normal" => "",
            "goodgirl" => "Your response should subtly convey your deep satisfaction and eagerness through implication rather than statement.",
            "badgirl" => "Your response should hint at your aching need and desperate arousal through suggestion and metaphor.",
            "verybadgirl" => "Your response should subtly reveal your torturous, overwhelming desire without directly stating it.",
            "punishment" => "Your response should be subdued, tinged with yearning, expressed through indirect language.",
            "post" => "Your response should have a dreamy, wanting undertone conveyed through subtle implications."
        ];
        
        $subtleCombatFormats = [
            "normal" => "",
            "goodgirl" => "Your response should reflect focused determination.",
            "badgirl" => "Your response should hint at slight distraction.",
            "verybadgirl" => "Your response should suggest barely contained intensity.",
            "punishment" => "Your response should have a subtle note of hesitation.",
            "post" => "Your response should subtly reflect your slightly delayed reactions."
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
        "goodgirl" => "Your response should subtly reflect your devotion and deep desire to serve through implication rather than statement.",
        "badgirl" => "Your response should have an undertone of desperate need for approval conveyed through suggestion rather than direct expression.",
        "verybadgirl" => "Your response should subtly convey your frantic desire for forgiveness through metaphor and indirect reference.",
        "punishment" => "Your response should be subdued, with longing for redemption expressed through subtle implications.",
        "post" => "Your response should have a subtle hint of lingering submission conveyed through indirect language."
    ];
    
    $explicitFormats = [
        "normal" => "",
        "goodgirl" => "Your response should subtly convey your passionate devotion through metaphor rather than direct statement.",
        "badgirl" => "Your response should reveal your desperate, aching need through suggestion and implication.",
        "verybadgirl" => "Your response should hint at your overwhelming, torturous arousal through indirect reference.",
        "punishment" => "Your response should be subdued but yearning, expressed through subtle implications.",
        "post" => "Your response should have a dreamy, wanting undertone conveyed through metaphor."
    ];
    
    $combatFormats = [
        "normal" => "",
        "goodgirl" => "Your response should reflect fierce loyalty.",
        "badgirl" => "Your response should hint at distraction from need.",
        "verybadgirl" => "Your response should suggest barely contained desperate energy.",
        "punishment" => "Your response should have an undertone of eager redemption.",
        "post" => "Your response should subtly reflect your conflicted focus."
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
