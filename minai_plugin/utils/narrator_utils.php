<?php
Function SetNarratorPrompts($isFirstPerson = false) {
    // Get the player's input if any
    $playerInput = isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"] != "" ? $GLOBALS["gameRequest"][3] : "";
    
    if ($isFirstPerson) {
        if (IsExplicitScene()) {
            $narratorPrompt = [
                "cue" => [
                    "write a first-person erotic narrative response as {$GLOBALS["PLAYER_NAME"]} in response to the #SEX_SCENARIO, focusing entirely on your immediate physical sensations and emotional state. Describe in vivid detail exactly what you are feeling in this moment, both physically and mentally."
                ],
                "player_request"=>[    
                    "{$GLOBALS["PLAYER_NAME"]} thinks to herself about the current situation.",
                ]
            ];
            
            $templateDialog = "Respond in first-person perspective as {$GLOBALS["PLAYER_NAME"]}, describing only your current physical and emotional state. Focus purely on the present moment - what you're feeling, how your body is responding, and your immediate emotional reactions. Don't reflect on the past or future, stay completely in the now.";
        } else {
            $narratorPrompt = [
                "cue" => [
                    "write a first-person narrative response as {$GLOBALS["PLAYER_NAME"]}, describing your thoughts, feelings, and experiences in this moment. Speak introspectively about your journey and current situation."
                ],
                "player_request"=>[    
                    "{$GLOBALS["PLAYER_NAME"]} thinks to herself about the current situation.",
                ]
            ];
            
            $templateDialog = "Respond in first-person perspective as {$GLOBALS["PLAYER_NAME"]}, sharing your personal thoughts and feelings.";
        }
    } else {
        if (IsExplicitScene()) {
            $narratorPrompt = [
                "cue" => [
                    "write a response as The Narrator, describing {$GLOBALS["PLAYER_NAME"]}'s immediate physical and emotional experiences in vivid sensual detail. Focus entirely on what she is feeling in this exact moment."
                ]
            ];
            
            $templateDialog = "You are The Narrator. Describe the intense sensations and emotions being experienced right now, focusing purely on the present moment.";
        } else {
            $narratorPrompt = [
                "cue" => [
                    "write a response as The Narrator, speaking from an omniscient perspective about the world and the player's journey."
                ]
            ];
            
            $templateDialog = "You are The Narrator. Respond in an omniscient, storyteller-like manner.";
        }
    }

    // Add player_request only if there was actual input
    if (!empty($playerInput)) {
        $narratorPrompt["player_request"] = [
            $playerInput
        ];
    }

    // Set the base prompts
    $GLOBALS["PROMPTS"]["minai_narrator_talk"] = $narratorPrompt;
    $GLOBALS["TEMPLATE_DIALOG"] = $templateDialog;

    // If Herika is The Narrator, set additional prompts for player input types
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        $inputTypes = ["inputtext", "inputtext_s", "ginputtext", "ginputtext_s", "instruction", "init"];
        foreach ($inputTypes as $type) {
            $GLOBALS["PROMPTS"][$type] = $narratorPrompt;
        }
    }
}