<?php

function setDominantTalkPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName commands their partner to acknowledge the frustration caused by the chastity belt, saying something authoritative about how it's their fault. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something dominating about how they'll make their partner pay for their pleasure when the chastity belt comes off. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something dominating about their orgasm, using commanding language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something authoritative as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName orders their partner into the current position, using commanding language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something authoritative about the current position. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName demands their partner keep up with the faster pace, using commanding language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something dominating about what they want to do next. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName orders their partner to slow down, using commanding language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something controlling about why they want to take it slow. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName comments on how their partner performed, using commanding language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something dominating about what they expect next time. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their partner's body, using commanding language to exert control. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something authoritative about their partner's posture. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner should move during sex. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's obedience. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something dominating about their partner's submission. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner should look at them during sex. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>