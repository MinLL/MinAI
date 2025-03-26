<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle spanking information prompts with appropriate context
function get_info_spank_ass_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) spanks (.+?)\'s ass/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get arousal context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    
    // Check for specific devices that would affect the spanking sensation
    $hasPlugs = isset($deviceContext["hasPlugVaginal"]) || isset($deviceContext["hasPlugAnal"]);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Determine reaction based on arousal and devices
    $reactionDescriptions = [];
    
    if ($hasPlugs) {
        // Reactions when wearing anal/vaginal plugs during spanking
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "Their eyes widen suddenly as the impact causes the plugs to shift inside them, a muffled sound caught behind their gag",
                    "The spanking sends a jolt through their body as the plugs move within them, their gagged protest barely audible",
                    "Each strike makes them jerk forward with surprise, the plugs inside them adding unexpected intensity to the punishment"
                ];
            } else {
                $reactionDescriptions = [
                    "A sharp gasp escapes their lips as the impact causes the plugs to shift inside them",
                    "The spanking sends a jolt through their body as the plugs move within them, causing them to bite their lip",
                    "Each strike makes them jerk forward with a quiet yelp, the internal devices amplifying the sensation"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The combination of the plugs shifting inside them and the sting of the spanking causes them to emit a desperate groan behind their gag",
                    "Each impact sends the plugs deeper, drawing muffled moans that they struggle to contain",
                    "Their body tenses and trembles with each strike, the plugs enhancing each sensation in ways their gagged sounds can't properly express"
                ];
            } else {
                $reactionDescriptions = [
                    "The combination of the plugs shifting inside them and the sting of the spanking draws forth a conflicted moan",
                    "Each impact sends the plugs deeper, causing them to cry out with a mixture of discomfort and unwanted pleasure",
                    "Their breath catches with each strike as the plugs enhance every sensation, creating a complex mixture of pain and stimulation"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "Desperate, muffled sounds of conflicted pleasure escape their gag as the spanking causes the plugs to hit sensitive spots inside them",
                    "Their body writhes helplessly, the plugs turning each spank into an intense wave of stimulation that their gagged moans can't properly express",
                    "Each impact makes the plugs shift in maddening ways, causing their bound body to shudder violently with poorly concealed arousal"
                ];
            } else {
                $reactionDescriptions = [
                    "Desperate cries of conflicted pleasure escape their lips as the spanking causes the plugs to hit sensitive spots inside them",
                    "Their body writhes helplessly, the plugs turning each spank into an intense wave of stimulation they can't ignore",
                    "Each impact makes the plugs shift in maddening ways, causing them to moan loudly despite their attempts to maintain composure"
                ];
            }
        }
    } else {
        // Reactions without plugs - simpler, more direct
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The sharp impact draws a muted sound of surprise through their gag",
                    "They flinch with each strike, muffled protests barely audible behind their gag",
                    "The spanking leaves red marks on their skin as they squirm with muffled discomfort"
                ];
            } else {
                $reactionDescriptions = [
                    "The sharp impact draws a gasp of surprise from their lips",
                    "They flinch with each strike, their composure briefly disrupted by the sting",
                    "The spanking leaves red marks on their skin as they grit their teeth"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "Each strike draws a stifled sound that might be pain or something else entirely, their gagged response ambiguous",
                    "Their muffled sounds increase in pitch as the spanking continues, their body reacting in complex ways",
                    "The reddening skin seems to sensitize with each impact, their muffled reactions growing more intense"
                ];
            } else {
                $reactionDescriptions = [
                    "Each strike draws a sound that might be pain or something else entirely, their reaction ambiguous",
                    "Their breath quickens as the spanking continues, their body responding in complex ways",
                    "The reddening skin seems to sensitize with each impact, turning mere sting into something more"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "Their gagged moans betray a shameful enjoyment of the spanking, their body responding eagerly to each impact",
                    "They squirm in a way that seems designed to invite more spanking rather than avoid it, gagged sounds pleading",
                    "The reddened flesh heats with each strike, muffled sounds of undeniable pleasure escaping around their gag"
                ];
            } else {
                $reactionDescriptions = [
                    "Their moans betray a shameful enjoyment of the spanking, their body responding eagerly to each impact",
                    "They squirm in a way that seems designed to invite more spanking rather than avoid it",
                    "The reddened flesh heats with each strike, drawing forth sounds of undeniable pleasure"
                ];
            }
        }
    }
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while $helplessness";
    }
    
    // Select a random reaction from the appropriate array
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Create the prompt with detailed context
    $promptText = "$speakerName delivers a firm spanking to $targetName's exposed buttocks$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_spank_ass") {
    $promptText = OverrideGameRequestPrompt(get_info_spank_ass_prompt());
    $GLOBALS["PROMPTS"]["info_spank_ass"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 