<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle increased arousal information prompts
function get_info_arousal_increase_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract name from the message
    $speakerName = "";
    
    if (preg_match('/^(.+?)\'s arousal level increased/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get arousal context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    $hasChastityBelt = isset($deviceContext["hasChastityBelt"]) ? $deviceContext["hasChastityBelt"] : false;
    $hasPlugs = isset($deviceContext["hasPlugVaginal"]) || isset($deviceContext["hasPlugAnal"]);
    
    // Create different descriptions based on the current arousal level
    $arousalDescriptions = [];
    
    if ($arousal < 30) {
        // Low arousal becoming noticeable
        if ($hasGag) {
            $arousalDescriptions = [
                "$speakerName's breathing quickens slightly, a hint of desire showing in their eyes despite their gagged state.",
                "A subtle flush spreads across $speakerName's skin as their body begins to respond, their gagged state adding to their vulnerability.",
                "Despite their gagged mouth, $speakerName's body language begins to shift as the first signs of arousal take hold."
            ];
        } else {
            $arousalDescriptions = [
                "$speakerName's breathing quickens slightly, a hint of desire showing in their eyes.",
                "A subtle flush spreads across $speakerName's skin as their body begins to respond to stimulation.",
                "$speakerName's posture shifts almost imperceptibly as the first signs of arousal take hold."
            ];
        }
    } elseif ($arousal < 70) {
        // Medium arousal intensifying
        if ($hasChastityBelt || $hasPlugs) {
            $arousalDescriptions = [
                "$speakerName's growing arousal becomes difficult to hide, made all the more intense by the devices restraining them.",
                "A visible tremor runs through $speakerName as their desire builds, the restrictive devices heightening every sensation.",
                "$speakerName shifts uncomfortably as their arousal increases, the devices they wear creating a maddening combination of stimulation and denial."
            ];
        } elseif ($hasGag) {
            $arousalDescriptions = [
                "Muffled sounds of increasing desire escape $speakerName's gag as their arousal builds noticeably.",
                "$speakerName's eyes take on a glazed quality as their arousal deepens, their gagged state adding to their struggle for composure.",
                "Despite the restriction of the gag, $speakerName's growing excitement is evident in their quickened breathing and restless movements."
            ];
        } else {
            $arousalDescriptions = [
                "$speakerName's lips part slightly as their breathing becomes more labored, clear signs of increasing desire.",
                "A soft, involuntary sound escapes $speakerName as their arousal builds to a more noticeable level.",
                "$speakerName's focus visibly wavers as their body responds with increasing intensity to stimulation."
            ];
        }
    } else {
        // High arousal becoming desperate
        if ($hasChastityBelt) {
            $arousalDescriptions = [
                "$speakerName's frustration becomes painfully evident as their intense arousal meets the unyielding restriction of their chastity belt.",
                "The chastity belt becomes an instrument of torment as $speakerName's arousal reaches desperate heights with no possibility of relief.",
                "$speakerName's entire body trembles with need, the chastity belt cruelly preventing any possibility of satisfaction."
            ];
        } elseif ($hasPlugs) {
            $arousalDescriptions = [
                "The plugs inside $speakerName seem to intensify their already overwhelming arousal, drawing desperate sounds from them.",
                "$speakerName's body quivers as the internal devices amplify their state of extreme excitement.",
                "A visible shudder runs through $speakerName as the plugs shift within them, heightening their already desperate state of arousal."
            ];
        } elseif ($hasGag) {
            $arousalDescriptions = [
                "Urgent, muffled sounds of need escape around $speakerName's gag as their arousal reaches a fever pitch.",
                "$speakerName's entire body radiates desperate desire, their gagged moans carrying unmistakable urgency.",
                "The gag does little to hide the sounds of $speakerName's desperate arousal as they struggle to maintain any semblance of composure."
            ];
        } else {
            $arousalDescriptions = [
                "$speakerName can no longer hide their state of intense arousal, their entire being focused on the need for release.",
                "A desperate, needy sound escapes $speakerName as their arousal reaches a critical point.",
                "$speakerName's self-control visibly crumbles as their arousal becomes overwhelming and all-consuming."
            ];
        }
    }
    
    // Select a random description
    $arousalDesc = $arousalDescriptions[array_rand($arousalDescriptions)];
    
    // Create the prompt
    $promptText = "$arousalDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_arousal_increase") {
    $promptText = OverrideGameRequestPrompt(get_info_arousal_increase_prompt());
    $GLOBALS["PROMPTS"]["info_arousal_increase"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 