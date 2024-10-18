<?php

function setTeasingTalkPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName playfully taunts their partner about the chastity belt, saying something cheeky about how it's not their fault they can't climax. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something saucy about how they'll tease them even more when the chastity belt is finally off. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName exclaims something playful about their orgasm, using flirtatious language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something teasing as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName teases their partner about the current position, using playful language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something flirtatious about the current position. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName taunts their partner about keeping up with the faster pace, using playful language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something cheeky about what they want to do next. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName teases their partner about slowing down, using playful language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something saucy about why they want to take it slow. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName teases their partner about their performance, using playful language. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something cheeky about what they expect next time. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName comments on their partner's body, using playful language to keep the mood light. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something cheeky about their partner's attempts to seduce them. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner's teasing makes them feel. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's playful touch. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something saucy about their partner's flirting. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's jokes make them laugh during sex. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>