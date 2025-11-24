<?php

function setSultrySeductionPrompts($currentName)
{
    $gender = GetGender($currentName);
    $in0 = "(";
    $in1 = ")";
    
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName says something smooth and seductive about how the chastity belt is just a minor setback, and that the pleasure will be worth the wait.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes the way the chastity belt is heightening their anticipation, using a sultry and alluring tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName exclaims something sultry about their orgasm, using a smooth and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sultry and seductive as they reach orgasm and lets out a loud, breathless gasp.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName breath faster and quiver attempting to describe their orgasm using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to describe the intensity of their orgasm among moans and gasps for breath, using a seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something enticing as they climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to say something seductive in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she squirts using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she loses control while squirting using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something about how she almost passes out while being overcome by a devastating orgasm using smooth and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream between broken smooth and seductive words while having orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName beg to be filled with cum using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky from head to toe using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky with her body covered with a mixture of cum and sweat using smooth and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName squirts and yelp loudly, pronouncing seductive words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName tell how he ejaculates using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sultry as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cock throbs as he squirts jets of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName grunts as he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something seductive in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName says something smooth and seductive about the current position, inviting their partner to join them.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how happy is to try the current position, using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how happy is to give pleasure, using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment the partner's skill, using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment a relevant physical aspect of the partner, using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how exciting current position is, using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment what physical sensations the current position induces, using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment the sensory details to enhance visceral and psychological immersion using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pressure, fluids, lubrication/friction, discomfort or tearing using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like fluids, lubrication, discomfort or tearing or pressure using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction, discomfort or tearing or pressure using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like discomfort or tearing or pressure using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction, discomfort or tearing using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort, tearing, pressure, fluids or lubrication/friction using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like body arching, trembling, spasms, breathing or heart rate using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName describe physical sensations like pleasure or friction using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or pressure using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or tearing using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or heart rate using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or breathing using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or spasms using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or muscle tension using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment physical responses like body arching, trembling, spasms, breathing or heart rate using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or spasms using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like breathing or heart rate using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms, breathing or heart rate using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like body arching or spasms using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment on the current position, using a smooth and velvety tone to entice their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, swollen nipples or vaginal lubrication using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate, body arching, spasms or swollen clitoris using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like muscle tension, spasms or vaginal lubrication using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling, swollen nipples or vaginal lubrication using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erected nipples, swollen clitoris, breathing or heart rate using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like engorged clitoris, vaginal lubrication, breathing, heart rate, muscle tension, trembling using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like vaginal lubrication, breathing, heart rate or engorged clitoris using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, penis erection, swelling of the testicles, scrotum tensing using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate, body arching, spasms, erection using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like muscle tension, spasms, swelling of the testicles using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling, scrotum tensing, penis erection using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erection, breathing, heart rate, body arching using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like swelling of the testicles, breathing, heart rate or trembling using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like penis erection using a smooth and velvety tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName talks about how much they love the faster pace, using a sultry and seductive tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something enticing about what they want to do next, trying to tempt their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName whispers about the slower pace, using a soft and seductive tone to draw their partner in.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something alluring about why they want to take it slow, trying to captivate their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After sex, $currentName comment on how good the sex was, using a smooth and seductive tone to describe the experience.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After intercourse, $currentName says something enticing about their orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the afterglow, $currentName comments partner's endowment using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Post-release, $currentName says something seductive about partner's orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Following sex, $currentName says something alluring about how they want to do it again next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName says something seductive about what they would like to improve next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the aftermath of sex, $currentName comments partner's endurance using sultry and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName comment on their partner's body, using a smooth and seductive tone to describe their attraction.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something enticing about their partner's eyes.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Breathlessly, $currentName expresses gratitude for the pleasure with a whispered, suggestive phrase.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName talks about how much they love their partner's gentle caress.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In a moment of passion, $currentName murmurs a seductive sentence, expressing how $currentName is losing control.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}With a soft laugh, $currentName uses a playful phrase to encourage partner to explore further, hinting at the pleasures to come.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

