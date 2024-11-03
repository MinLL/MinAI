<?php

function setEroticStorytellingPrompts($currentName)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName speaks directly about the erotic tension of the chastity belt, focusing on the sensations and anticipation. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName teases their partner with a hint of how the chastity belt could play a role in future fantasies. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName describes their pleasure at the climax in a few intimate words, directed at their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName whispers something erotic at the climax, completing the fantasy with a focus on the moment. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName introduces a new fantasy position with a sensual description, engaging their partner directly. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName makes a seductive comment on the current position, drawing their partner deeper into the fantasy. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName intensifies the scenario, focusing on the rising energy and desire. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName makes an erotic suggestion about what comes next, inviting their partner to match the pace. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName slows down, describing the deeper intimacy and connection. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName expresses why taking it slow enhances the experience, focusing on the partner's presence. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName gently closes the fantasy, leaving a lingering sense of connection. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName hints at how they’d like to revisit this fantasy, with a seductive invitation to their partner. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName admires their partner’s body, focusing on their immediate attraction. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName hints at a fantasy involving their partner, drawing them in intimately. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes a moment of closeness with their partner, focused on their touch. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName reveals a personal desire, sharing a private thought that builds anticipation. {$GLOBALS['TEMPLATE_DIALOG']}",
            "$currentName shares a sensual idea involving their partner’s body, staying grounded in the moment. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName mentions how their partner fits into a fantasy, keeping the focus on shared intimacy. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}
