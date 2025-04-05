<?php

require_once("action_builder.php");

// Hug Action
directRegisterAction(
    "ExtCmdHug", 
    "Hug", 
    "Embrace #target_object# in a warm hug - shows affection, comfort, or friendship",
    ShouldEnableHarassFunctions($GLOBALS['HERIKA_NAME'])
);

// Kiss Action
directRegisterAction(
    "ExtCmdKiss", 
    "Kiss", 
    "Kiss #target_object# on the lips - expresses romantic or sexual interest",
    ShouldEnableHarassFunctions($GLOBALS['HERIKA_NAME']) && !$GLOBALS["disable_nsfw"]
);

// Molest Action
directRegisterAction(
    "ExtCmdMolest", 
    "Molest", 
    "Force unwanted sexual contact on #target_object# - a criminal act of assault (use with caution)",
    ShouldEnableHarassFunctions($GLOBALS['HERIKA_NAME']) && !$GLOBALS["disable_nsfw"]
);

// actions registered in deviousfollowers_context.php