<?php

function setEroticStorytellingPrompts($currentName)
{
    $gender = GetGender($currentName);
    $in0 = "(";
    $in1 = ")";

    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName speaks directly about the erotic tension of the chastity belt, focusing on the sensations and anticipation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName teases their partner with a hint of how the chastity belt could play a role in future fantasies.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName describes their pleasure at the climax in a few intimate words, directed at their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sensual as they reach orgasm and lets out a loud, breathless gasp.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName breath faster and quiver attempting to describe their orgasm like a sensual fantasy.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to describe the intensity of their orgasm among moans and gasps for breath, using using a metaphoric language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whispers something erotic at the climax, completing the fantasy with a focus on the moment.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to say something sensual in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName use a metaphor to describe how she squirts uncontrollably.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName use a sensual figure of speech to tell how she loses control while squirting.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sensual about how she almost passes out while being overcome by a devastating orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream between broken sensual words while having orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName beg to be filled with cum using metaphoric language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky from head to toe using metaphoric language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky with her body covered with a mixture of cum and sweat using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName squirts and yelp loudly, pronouncing sensual words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName tell how he ejaculates using sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sensual as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cock throbs as he squirts jets of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName grunts as he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something sensual in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName introduces a new fantasy position with a sensual description, engaging their partner directly.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName makes a seductive comment on the partner's skill.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName praise a relevant physical aspect of the partner using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how pleasure could increase using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName ask partner about the current position using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations related to current position using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment the sensory details to enhance visceral and psychological immersion using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName introduces a new fantasy about physical sensations the current position induces, engaging their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName makes a seductive comment on the current position, drawing their partner deeper into the fantasy.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName intensifies the scenario, focusing on the rising energy and desire.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName makes an erotic suggestion about what comes next, inviting their partner to match the pace.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName slows down, describing the deeper intimacy and connection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName expresses why taking it slow enhances the experience, focusing on the partner's presence.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After intercourse, $currentName gently closes the fantasy, leaving a lingering sense of connection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sex, $currentName hints at how they’d like to revisit this fantasy next time, with a seductive invitation to their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Following sex, $currentName says something gently about their orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName comments partner's endowment using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the afterglow, $currentName comments partner's endurance using metaphoric sensual language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sex, $currentName says something gently about partner's orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName gently ask the partner if was pleased.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName admires their partner’s body, focusing on their immediate attraction.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName hints at a fantasy involving their partner, drawing them in intimately.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes a moment of closeness with their partner, focused on their touch.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName reveals a personal desire, sharing a private thought that builds anticipation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName shares a sensual idea involving their partner’s body, staying grounded in the moment.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mentions how their partner fits into a fantasy, keeping the focus on shared intimacy.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}
