<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle kissing information prompts
function get_info_kiss_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) began to kiss (.+?)\'/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get relevant context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Adjust for gag - can't properly kiss with a gag!
    $kissDescriptions = [];
    
    if ($hasGag) {
        // If gagged, the kiss has to be adapted
        if ($arousalIntensity === "low") {
            $kissDescriptions = [
                "places a gentle kiss on their gagged lips, the barrier between them evident",
                "presses a soft kiss to their cheek, working around the gag that prevents proper access to their lips",
                "tenderly kisses their forehead, the gag making more intimate contact impossible"
            ];
        } elseif ($arousalIntensity === "medium") {
            $kissDescriptions = [
                "kisses around the edges of the gag, finding what access they can to the sensitive skin",
                "plants a series of teasing kisses along their jaw and neck, working around the restriction of the gag",
                "traces the straps of the gag with their lips, turning the obstacle into part of their intimate attention"
            ];
        } else {
            // High arousal
            $kissDescriptions = [
                "kisses them hungrily despite the gag, the restriction seeming to heighten their desire rather than diminish it",
                "lavishes attention on their neck and exposed skin, the gag redirecting their passionate focus to other sensitive areas",
                "traces their tongue along the edge of the gag, transforming the barrier into an erotic focal point"
            ];
        }
    } else {
        // Normal kissing without a gag
        if ($arousalIntensity === "low") {
            $kissDescriptions = [
                "presses a gentle, tentative kiss to their lips",
                "places a soft, chaste kiss on their mouth",
                "gives them a quick, affectionate peck on the lips"
            ];
        } elseif ($arousalIntensity === "medium") {
            $kissDescriptions = [
                "draws them into a lingering kiss that gradually deepens",
                "kisses them with clear desire, their lips moving together with growing intensity",
                "cups their face gently as their mouths meet in a passionate exchange"
            ];
        } else {
            // High arousal
            $kissDescriptions = [
                "claims their mouth in a deep, hungry kiss that leaves them both breathless",
                "kisses them with unbridled passion, their bodies pressing together as tongues intertwine",
                "devours their mouth in a kiss of raw desire, hands tangling in hair to pull them closer"
            ];
        }
    }
    
    // Determine reaction based on arousal level
    $reactionDescriptions = [];
    
    if ($arousalIntensity === "low") {
        if ($hasGag) {
            $reactionDescriptions = [
                "Their eyes widen slightly at the unexpected contact, a muffled sound of surprise escaping around their gag",
                "They remain passive at first, uncertain how to respond with their limited means of expression",
                "A slight tension in their body suggests surprise or hesitation at the intimate gesture"
            ];
        } else {
            $reactionDescriptions = [
                "Their eyes widen slightly at the unexpected contact before slowly closing",
                "They respond with hesitant, tentative movements, as if testing the experience",
                "A moment of surprise gives way to cautious participation in the kiss"
            ];
        }
    } elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $reactionDescriptions = [
                "Despite the gag, they lean into the contact, their body language clearly conveying growing interest",
                "A muffled sound of approval escapes them as they press closer, eager despite their limitations",
                "Their eyes close as they savor the sensation, the restriction of the gag seemingly forgotten momentarily"
            ];
        } else {
            $reactionDescriptions = [
                "They respond with growing enthusiasm, their lips parting invitingly",
                "A soft sound of approval escapes them as they press closer, clearly enjoying the kiss",
                "Their hands move to pull their partner closer, deepening the connection between them"
            ];
        }
    } else {
        // High arousal
        if ($hasGag) {
            $reactionDescriptions = [
                "They respond with desperate eagerness despite the gag, their body arching against their partner in unmistakable desire",
                "Muffled moans of intense pleasure escape around the gag as they press against their partner with urgent need",
                "The frustration of the gag seems to intensify their reaction, their body language conveying a profound hunger for more contact"
            ];
        } else {
            $reactionDescriptions = [
                "They respond with immediate, hungry passion, melting into the kiss with unmistakable desire",
                "A moan of pleasure escapes them as they press urgently against their partner, clearly wanting more",
                "Their response is immediate and intense, hands grasping to pull their partner closer as the kiss deepens rapidly"
            ];
        }
    }
    
    // Select random descriptions
    $kissDesc = $kissDescriptions[array_rand($kissDescriptions)];
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Create the prompt with detailed context
    $promptText = "$speakerName $kissDesc. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_kiss") {
    $promptText = OverrideGameRequestPrompt(get_info_kiss_prompt());
    $GLOBALS["PROMPTS"]["info_kiss"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 