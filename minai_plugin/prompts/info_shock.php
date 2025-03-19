<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle shock information prompts
function get_info_shock_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message if possible
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) remotely shocks (.+?)\./', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get specific device description
    $deviceDescription = GetDeviceDescription($target);
    
    // Get additional context
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Create intro variations for shocking
    $shockIntros = [
        "activates the punishment runes on $targetName's $deviceDescription",
        "sends a pulse of painful arcane energy through $targetName's $deviceDescription",
        "triggers the disciplinary enchantment on $targetName's $deviceDescription",
        "channels disruptive magic into the soulgems within $targetName's $deviceDescription"
    ];
    
    $shockIntro = $shockIntros[array_rand($shockIntros)];
    
    // Create shock reaction descriptions
    $shockDescriptions = [];
    if ($hasGag) {
        $shockDescriptions = [
            "causing their body to jerk violently as they emit a muffled scream that penetrates their gag",
            "sending them into convulsions, their desperate cries stifled but still audible through the gag",
            "making their muscles seize instantly as they make urgent, desperate sounds behind their gag",
            "forcing sharp, stifled cries of pain through their gag as painful magic courses through sensitive flesh"
        ];
    } else {
        $shockDescriptions = [
            "causing their body to jerk violently as they let out a raw scream of pain",
            "sending them into convulsions, a sharp cry of shock escaping their lips",
            "making their muscles seize instantly as they gasp in surprised agony",
            "forcing cries of pain from their lips as disruptive arcane energy pulses through their most sensitive areas"
        ];
    }
    
    $shockDesc = $shockDescriptions[array_rand($shockDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", helplessly $helplessness and unable to escape,";
    }
    
    // Create aftermath variations
    $aftermathDescriptions = [
        "The magical punishment is brief but effective, leaving them trembling from the aftereffects.",
        "The arcane jolt lasts only moments but leaves a lingering reminder of who controls their pleasure and pain.",
        "Though quick, the magical shock leaves them shaking and compliant, a clear reminder of the consequences of disobedience.",
        "Even after the disruptive energy dissipates, their muscles continue to twitch involuntarily as they recover from the sudden pain."
    ];
    
    $aftermathDesc = $aftermathDescriptions[array_rand($aftermathDescriptions)];
    
    // Create the prompt with detailed context
    $promptText = "$speakerName $shockIntro$helplessnessContext, $shockDesc. ";
    $promptText .= "$aftermathDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_shock") {
    $promptText = OverrideGameRequestPrompt(get_info_shock_prompt());
    $GLOBALS["PROMPTS"]["info_shock"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 