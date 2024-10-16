<?php

function setEroticStorytellingPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName starts telling a story about a similar scenario where the chastity belt was a tantalizing obstacle, describing the erotic tension in detail. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about how they'll use the chastity belt as a prop in a future fantasy scenario. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName reaches the climax, describing their orgasm in detail. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something erotic as they climax, finishing the fantasy narrative. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName starts telling a story about a fantasy position, describing the scene in detail. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about the current position, weaving it into a fantasy narrative. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName continues the story, describing a more intense scenario with the faster pace. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something erotic about what they want to do next, incorporating it into the fantasy narrative. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName slows down the story, describing a more sensual scenario with the slower pace. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something sensual about why they want to take it slow, incorporating it into the fantasy narrative. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName wraps up the story, describing the afterglow of the fantasy scenario. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about how they want to reenact the fantasy next time. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName says something erotic about their partner's body, describing their attraction to it. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something seductive about a fantasy scenario involving their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes a sensual scene involving their partner's touch. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love a particular fantasy involving their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something erotic about a roleplay scenario involving their partner's body. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's body fits into their favorite fantasy. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>