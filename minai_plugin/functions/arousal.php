<?php

require_once("action_builder.php");

// Increase Arousal Action
registerMinAIAction("ExtCmdIncreaseArousal", "IncreaseArousal")
    ->withDescription("Signal that you're becoming more aroused - use when events are stimulating, but prioritize other actions that directly advance the scene")
    ->withParameter("target", "integer", "How much to increase arousal (0-100 scale)", range(1, 20))
    ->isNSFW()
    ->withEnableCondition(function() {
        return IsModEnabled("OSL") || IsModEnabled("Aroused");
    })
    ->register();

// Decrease Arousal Action
registerMinAIAction("ExtCmdDecreaseArousal", "DecreaseArousal")
    ->withDescription("Signal that you're becoming less aroused - use when excitement is dampening, but prioritize other actions that directly advance the scene")
    ->withParameter("target", "integer", "How much to decrease arousal (0-100 scale)", range(1, 20))
    ->isNSFW()
    ->withEnableCondition(function() {
        return IsModEnabled("OSL") || IsModEnabled("Aroused");
    })
    ->register();


