<?php

function setSultrySeductionPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName says something smooth and seductive about how the chastity belt is just a minor setback, and that the pleasure will be worth the wait. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way the chastity belt is heightening their anticipation, using a sultry and alluring tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something sultry about their orgasm, using a smooth and seductive tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something enticing as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName says something smooth and seductive about the current position, inviting their partner to join them. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the current position, using a smooth and velvety tone to entice their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName talks about how much they love the faster pace, using a sultry and seductive tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something enticing about what they want to do next, trying to tempt their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName whispers about the slower pace, using a soft and seductive tone to draw their partner in. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something alluring about why they want to take it slow, trying to captivate their partner. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName comments on how good the sex was, using a smooth and seductive tone to describe the experience. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something alluring about how they want to do it again next time. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their partner's body, using a smooth and seductive tone to describe their attraction. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something enticing about their partner's eyes. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner's skin looks in the light. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's gentle caress. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something alluring about their partner's smile. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's voice sounds during sex. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>