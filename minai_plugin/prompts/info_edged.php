<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/../util.php");

// Function to handle edging information prompts
function get_info_edged_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message if possible
    // Extract actor name from message like "X was brought right to the edge of orgasm..."
    $targetName = $GLOBALS["target"];
    $target = $GLOBALS["target"];
    if (preg_match('/^(.*?) was brought right to the edge/', $cleanedMessage, $matches)) {
        $target = $matches[1];
        $targetName = $target;
    }

    
    // Check if actor can orgasm
    $canOrgasm = ActorCanOrgasm($target);
    
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get specific device description
    $deviceDescription = GetDeviceDescription($target);
    
    // Get additional context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 90;
    $intensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;

    // Enhance the denial aspect if orgasm is not allowed
    if (!$canOrgasm) {
        $denialIntros = [
            "The cruel programming of $targetName's $deviceDescription knows they're forbidden release",
            "$targetName's $deviceDescription seems to delight in their inability to climax",
            "Despite their desperate state, $targetName's $deviceDescription maintains perfect control",
            "The merciless intelligence controlling $targetName's $deviceDescription knows exactly how far to push"
        ];
        
        $denialBuildups = [
            "methodically building the pleasure far beyond what they could normally endure",
            "pushing their arousal to heights that would normally trigger an immediate climax",
            "forcing them to experience sensations that dance on the razor's edge of release",
            "driving their need to levels that would usually guarantee an explosive orgasm"
        ];
        
        $denialReactions = [];
        if ($hasGag) {
            $denialReactions = [
                "their muffled screams of frustration betray their knowledge that relief will never come",
                "their gagged pleas grow increasingly desperate as they realize release is impossible",
                "tears of desperation leak from their eyes as their gag stifles their begging",
                "their body writhes helplessly as gagged sobs of denial fill the air"
            ];
        } else {
            $denialReactions = [
                "they wail in anguish as they realize their peak will remain forever out of reach",
                "broken promises and desperate bargaining spill from their lips",
                "they alternate between begging for mercy and pleading for the impossible release",
                "their voice cracks with need as they acknowledge their complete helplessness"
            ];
        }
        
        $denialFinales = [
            "Their current state ensures this torment can continue indefinitely, each edge more devastating than the last.",
            "The perfect denial serves as an exquisite reminder of their inability to achieve release.",
            "Their punishment is made sweeter by knowing this desperate edge could last forever.",
            "The endless cycle of denial becomes both heaven and hell, exactly as designed."
        ];
        
        $promptText = $denialIntros[array_rand($denialIntros)] . ", " . $denialBuildups[array_rand($denialBuildups)] . ". ";
        $promptText .= $denialReactions[array_rand($denialReactions)];
        
        if (!empty($helplessness)) {
            $promptText .= ", while remaining completely $helplessness";
        }
        
        $promptText .= ". " . $denialFinales[array_rand($denialFinales)];
        
        return "The Narrator: " . $promptText;
    }
    
    // Original edging text continues below for when orgasm is allowed
    // Create intro variations for edging - reworded to work without a speaker
    $edgingIntros = [
        "The intensity of $targetName's $deviceDescription rises and falls with perfect control, bringing them to the very edge",
        "$targetName's $deviceDescription pulses with expertly calculated patterns to build unbearable tension",
        "The settings on $targetName's $deviceDescription teasingly increase to near-climax levels",
        "Precisely timed surges of sensation from $targetName's $deviceDescription push them to their limit"
    ];
    
    $edgingIntro = $edgingIntros[array_rand($edgingIntros)];
    
    // Create buildup variations
    $buildupPhrases = [
        "gradually building the pleasure until they're teetering on the brink",
        "steadily intensifying the stimulation until release seems inevitable",
        "carefully escalating the sensations until they're desperate for climax",
        "meticulously raising the pleasure to an almost unbearable peak"
    ];
    
    $buildupPhrase = $buildupPhrases[array_rand($buildupPhrases)];
    
    // Create denial variations
    $denialPhrases = [
        "then abruptly stops, leaving them quivering at the precipice of release",
        "only to suddenly cease all stimulation, abandoning them on the knife-edge of climax",
        "before cruelly withdrawing, stranding them at the very threshold of orgasm",
        "then unexpectedly halts, trapping them in an exquisite state of unfulfilled need"
    ];
    
    $denialPhrase = $denialPhrases[array_rand($denialPhrases)];
    
    // Create variations for reactions based on context and arousal intensity
    $reactionDescriptions = [];
    
    if ($hasGag) {
        if ($intensity === "very_high") {
            $reactionDescriptions = [
                "their entire body trembling violently as muffled desperate pleas escape past their gag",
                "their hips bucking frantically seeking the denied release, gagged whimpers filling the air",
                "their eyes wide with frustrated need as they strain against their restraints, guttural sounds barely contained by the gag",
                "convulsing with unfulfilled desire, desperate begging sounds muffled by their gag"
            ];
        } else {
            $reactionDescriptions = [
                "their body shuddering with need as stifled moans escape through their gag",
                "their limbs twitching with unfulfilled desire, muffled whimpers barely audible",
                "their eyes pleading for mercy as they squirm helplessly, gagged sounds of frustration filling the air",
                "their muscles tensing and releasing as they're left in a state of suspended pleasure"
            ];
        }
    } else {
        if ($intensity === "very_high") {
            $reactionDescriptions = [
                "their entire body quaking with frustration as they desperately beg for release",
                "their voice breaking as they plead, hips writhing involuntarily seeking the denied stimulation",
                "gasping raggedly between increasingly desperate demands for completion",
                "tears forming at the corners of their eyes as they hover in an exquisite agony of denial"
            ];
        } else {
            $reactionDescriptions = [
                "their breath coming in short, frustrated gasps as they whimper in need",
                "soft groans of disappointment escaping their lips as they squirm with unfulfilled desire",
                "their body tense with anticipation that now has nowhere to go",
                "their expression a mixture of pleasure and torment as they're left wanting"
            ];
        }
    }
    
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while remaining completely $helplessness";
    }
    
    // Create aftermath variations
    $aftermathDescriptions = [
        "They hover in a delicious state of denial, the promise of release tantalizingly out of reach.",
        "Their body remains suspended in a heightened state of arousal, craving the completion it was denied.",
        "The cruel interruption leaves them trapped in an exquisite purgatory between pleasure and frustration.",
        "Every nerve ending remains electrified with need, their entire being focused on the release withheld from them."
    ];
    
    $aftermathDesc = $aftermathDescriptions[array_rand($aftermathDescriptions)];
    
    // Create the prompt with detailed context - removed speakerName dependency
    $promptText = "$edgingIntro, $buildupPhrase, $denialPhrase. ";
    $promptText .= "$reactionDesc$helplessnessContext. ";
    $promptText .= "$aftermathDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_minai_edged" || $GLOBALS["gameRequest"][0] == "minai_edged" ) {
    $promptText = OverrideGameRequestPrompt(get_info_edged_prompt());
    $GLOBALS["PROMPTS"]["info_minai_edged"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["minai_edged"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 