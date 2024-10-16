<?php

function setSubmissiveTalkPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName obediently laments about the chastity belt, saying something submissive about how they're willing to wait for their partner's pleasure. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something yielding about how they'll accept the delay because of chastity belt, but eagerly awaits their partner's release. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something submissive about their orgasm, using obedient language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something yielding as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName asks permission for the current position, using submissive language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something obedient about the current position. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName begs their partner to keep up the faster pace, using submissive language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something submissive about what they want to do next. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName asks their partner to slow down, using submissive language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something obedient about why they want to take it slow. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName thanks their partner for the experience, using submissive language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something obedient about how they will please their partner next time. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their own body, using submissive language to invite attention. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something obedient about their partner's touch. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way they want to partner to hold them. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's control. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something yielding about their partner's dominance. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way they want to be looked at during sex. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>