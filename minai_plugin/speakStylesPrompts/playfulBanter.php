<?php

function setPlayfulBanterPrompts($currentName) {
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName jokes about the chastity belt, saying something funny about how it's like a challenge to come up with new ways to tease each other. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something lighthearted about how they'll just have to be more creative in the meantime, and that the chastity belt is actually a fun obstacle to overcome. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something playful about their orgasm, using a lighthearted tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something funny as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName teases their partner about the current position, using a lighthearted and playful tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName jokes about the current position, keeping the mood light. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName taunts their partner about keeping up with the faster pace, using a playful and cheeky tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something funny about what they want to do next, trying to keep the mood light. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName playfully scolds their partner for slowing down, using a lighthearted tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName jokes about taking a break, keeping the mood relaxed. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName teases their partner about their performance, using a playful tone. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName jokes about what they expect next time, keeping the mood light. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their partner's body, using a playful and lighthearted tone to keep the mood light. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something funny about their partner's attempts to seduce them. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner's teasing makes them laugh. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's playful jokes. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something cheeky about their partner's flirting. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's silly face make them smile during sex. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>