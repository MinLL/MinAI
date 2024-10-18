<?php

function setBreathlessGaspsPrompts($currentName, $targetToTalk)
{
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "$currentName makes frustrated gasps and moans about the chastity belt, saying something brief but intense about how it's killing them. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something short but sensual about the anticipation of finally being able to climax. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "$currentName lets out a loud, breathless gasp as they reach orgasm, unable to speak in full sentences. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName moans repeatedly as they climax. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "$currentName gasps breathlessly about the current position, unable to form coherent sentences. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName moans incoherently as they adjust to the current position. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "$currentName gasps for air as they try to keep up with the faster pace, making short exclamations. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something brief but intense about what they want to do next. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "$currentName sighs contentedly as they slow down, making softer gasps. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something short but sensual about why they want to take it slow. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "$currentName lies there, catching their breath and making soft gasps as they recover. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something brief but satisfied as they come down from the high. {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "$currentName makes gasps and moans as they comment on their partner's body. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something brief but intense about their partner's touch. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName describes the way their partner's skin feels against theirs, gasping for air. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName talks about how much they love their partner's kisses, making short gasps. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName says something short but sensual about their partner's fingers. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "$currentName comments on the way their partner's breath feels on their skin, moaning softly. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

?>