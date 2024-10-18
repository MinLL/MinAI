<?php

function setSensualWhisperingPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName whispers a sensual complaint about the chastity belt, saying something erotic about how it's teasing them. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about how they'll savor the pleasure when the chastity belt is finally off. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName whispers something sensual about their orgasm, using a soft tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName moans softly as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName whispers something sensual about the current position, using a soft tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something erotic about the current position, in a gentle whisper. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName whispers about how much they love the faster pace, using a soft tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about what they want to do next, in a gentle whisper. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName whispers about the slower pace, using a soft tone to describe the sensations. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something sensual about why they want to take it slow, in a gentle whisper. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName whispers something sensual about the experience, using a soft tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something erotic about the afterglow, in a gentle whisper. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName whispers something sensual about their partner's body, using a soft tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something erotic about their partner's lips. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner's skin tastes. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's gentle touch. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about their partner's fingers. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's breath feels on their skin. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>