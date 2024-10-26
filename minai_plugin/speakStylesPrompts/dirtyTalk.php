<?php

function setDirtyTalkPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName curses the chastity belt, saying something filthy about how it's ruining their pleasure. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something naughty about how they're going to make up for it when the chastity belt comes off. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something filthy about their orgasm, using explicit language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something naughty as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName comments on the current position, getting down and dirty about the specifics. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something filthy about the current position. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName talks about how much they love the faster pace, using explicit language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something naughty about what they want to do next. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName comments on the slower pace, using explicit language to describe the sensations. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something dirty about why they want to take it slow. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName comments on how good the sex was, using explicit language to describe the experience. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something dirty about what they want to do next time. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their partner's body, using explicit language to describe their attraction. {$GLOBALS["TEMPLATE_DIALOG"]}",
            " $currentName expresses their delight with every move, promising sexy surprises. {$GLOBALS['TEMPLATE_DIALOG']} Don't hold back!",
            "$currentName teases, detailing naughty rewards for good behavior in a playful tone: {$GLOBALS['TEMPLATE_DIALOG']}",
            "$currentName talks about how much they love their partner's moans. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something filthy about the way they want to explore their partner's body. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName moans their desire for more, asking for deeper pleasure with a filthy whisper: {$GLOBALS['TEMPLATE_DIALOG']}"
        ],
    ];
}

?>