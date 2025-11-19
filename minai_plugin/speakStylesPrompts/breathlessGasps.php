<?php

function setBreathlessGaspsPrompts($currentName)
{
    $in0 = "<instruction>";
    $in1 = "</instruction>";

    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName makes frustrated gasps and moans about the chastity belt, saying something brief but intense about how it's killing them.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something short but sensual about the anticipation of finally being able to climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName lets out a loud, breathless gasp as they reach orgasm, unable to speak in full sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName moans repeatedly as they climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName gasps breathlessly about the current position, unable to form coherent sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gasps breathlessly about the partner's skill.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName try to describe physical sensations related to current position, gasping.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName moans incoherently as they adjust to the current position.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName gasps for air as they try to keep up with the faster pace, making short exclamations.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something brief but intense about what they want to do next.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName sighs contentedly as they slow down, making softer gasps.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something short but sensual about why they want to take it slow.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After intercourse, $currentName lies there, catching their breath and making soft gasps as they recover.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Post-orgasmic, $currentName says something brief but satisfied as they come down from the high.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName makes gasps and moans as they comment on their partner's body.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something brief but intense about their partner's touch.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes the way their partner's movements feel, gasping for air.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName talks about how much they love their partner's kisses, making short gasps.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something short but sensual about their partner's fingers.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comments on the way their partner's intimacy is overwhelming, moaning softly.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

