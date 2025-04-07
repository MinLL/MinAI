<?php

require_once("action_builder.php");

// Increase Arousal Action
directRegisterAction(
    "ExtCmdIncreaseArousal", 
    "IncreaseArousal", 
    "Signal that you're becoming more aroused - use when events are stimulating, but prioritize other actions that directly advance the scene",
    (IsModEnabled("OSL") || IsModEnabled("Aroused")) && !$GLOBALS["disable_nsfw"]
);

// Decrease Arousal Action
directRegisterAction(
    "ExtCmdDecreaseArousal", 
    "DecreaseArousal", 
    "Signal that you're becoming less aroused - use when excitement is dampening, but prioritize other actions that directly advance the scene",
    (IsModEnabled("OSL") || IsModEnabled("Aroused")) && !$GLOBALS["disable_nsfw"]
);


