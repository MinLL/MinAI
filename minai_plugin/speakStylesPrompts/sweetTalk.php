<?php

function setSweeTalkPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName affectionately laments about the chastity belt, saying something sweet about how much they wish they could climax. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something loving about how they'll make it worth the wait when the chastity belt is removed. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something sweet about their orgasm, using affectionate language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something loving as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName says something affectionate about the current position, expressing their love. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on how this current position makes them feel closer to their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName talks about how much they love the passion, using affectionate language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something sweet about how this pace makes them feel.",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName comments on the slower pace, using affectionate language to describe the intimacy. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something loving about why they want to take it slow. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName comments on how good the sex was, using affectionate language to describe the experience. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something sweet about their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their partner's body, using affectionate language to describe their attraction. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something sweet about their partner's smile. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner's touch makes them feel. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's laugh. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something loving about their partner's voice. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's eyes sparkle during sex. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>