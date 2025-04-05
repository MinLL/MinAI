<?php

require_once("action_builder.php");

$target = $GLOBALS["target"];

// Function specifically for checking spanking actions
function shouldEnableSpankingAction() {
    return IsModEnabled("DeviousFollowers") && IsModEnabled("STA");
}

// Function specifically for checking general perverted actions
function shouldEnableGeneralPervertedAction() {
    return IsModEnabled("Sexlab") || IsModEnabled("Ostim");
}

// Register SpankAss action
directRegisterAction(
    "ExtCmdSpankAss", 
    "SpankAss", 
    "Strike #target_possessive# buttocks firmly - can be playful, disciplinary, or erotic",
    shouldEnableSpankingAction() && !$GLOBALS["disable_nsfw"]
);

// Register SpankTits action (female-only)
directRegisterAction(
    "ExtCmdSpankTits", 
    "SpankTits", 
    "Strike #target_possessive# breasts firmly - an intense erotic act that mixes pain and pleasure",
    shouldEnableSpankingAction() && $GLOBALS["target_gender"] === "female" && !$GLOBALS["disable_nsfw"]
);

// Register Grope action
directRegisterAction(
    "ExtCmdGrope", 
    "Grope", 
    "Touch and fondle #target_possessive# body in a sexual manner - shows desire and dominance",
    shouldEnableGeneralPervertedAction() && !$GLOBALS["disable_nsfw"]
);

// Register PinchNipples action
directRegisterAction(
    "ExtCmdPinchNipples", 
    "PinchNipples", 
    "Firmly pinch and manipulate #target_possessive# nipples - stimulates sensitive nerve endings",
    shouldEnableGeneralPervertedAction() && !$GLOBALS["disable_nsfw"]
);


