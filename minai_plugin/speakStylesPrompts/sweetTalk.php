<?php

function setSweeTalkPrompts($currentName)
{
    $gender = GetGender($currentName);
    $in0 = "<instruction>";
    $in1 = "</instruction>";

    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName affectionately laments about the chastity belt, saying something sweet about how much they wish they could climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something loving about how they'll make it worth the wait when the chastity belt is removed.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName exclaims something sweet about their orgasm, using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something warm and tender as they reach orgasm and lets out a loud, breathless gasp.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName breath faster and quiver attempting to describe their orgasm using sweet and affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to describe the intensity of their orgasm among moans and gasps for breath, using loving and affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something loving as they climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to say something affectionate in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she squirts using tender language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she loses control while squirting using warm and tender language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something about how she almost passes out while being overcome by a devastating orgasm using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream between broken loving and tender words while having orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName ask to be filled with cum using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky from head to toe using sweet words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky with her body covered with a mixture of cum and sweat using loving and tender words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName ask to be filled with cum and comment about her fertility status and pregnancy probability.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName comment about her fertility status using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName squirts and yelp loudly, pronouncing sweet and tender words words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName tell how he ejaculates using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sweet as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cock throbs as he squirts jets of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName grunts as he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something tender in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName says something affectionate about the current position, expressing their love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how happy is to give pleasure, expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment the partner's skill, expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment about a relevant physical aspect of the partner, expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how exciting current position is, using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment about physical sensations the current position induces, using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment the sensory details to enhance visceral and psychological immersion using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pressure, fluids, lubrication/friction, discomfort or tearing expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like fluids, lubrication, discomfort or tearing or pressure using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like friction, discomfort or tearing or pressure expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort or tearing or pressure expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction, discomfort or tearing using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort, tearing, pressure, fluids or lubrication/friction expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like body arching, trembling, spasms, breathing or heart rate using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment physical sensations like pleasure or friction using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or pressure expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or tearing expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or heart rate expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or breathing using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or spasms expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or muscle tension expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName describe physical responses like body arching, trembling, spasms, breathing or heart rate using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or spasms expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like breathing or heart rate expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms, breathing or heart rate expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like body arching or spasms expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment on how this current position makes them feel closer to their partner using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, swollen nipples or vaginal lubrication expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate or swollen clitoris using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like body arching or swollen clitoris expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or swollen clitoris expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like muscle tension or vaginal lubrication with biological realism expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or vaginal lubrication with biological realism using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate or vaginal lubrication with biological realism expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or vaginal lubrication with biological realism expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or swollen nipples with biological realism expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like swollen nipples or vaginal lubrication with biological realism expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erected nipples or swollen clitoris using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erected nipples or vaginal lubrication expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erected nipples or heart rate expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erected nipples or breathing expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like engorged clitoris, vaginal lubrication, breathing, heart rate or muscle tension expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like vaginal lubrication, breathing, heart rate, body arching or engorged clitoris using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, penis erection, swelling of the testicles or scrotum tensing using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like breathing, penis erection or scrotum tensing using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate, body arching, spasms or erection expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like muscle tension or swelling of the testicles expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or swelling of the testicles expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or penis erection of the testicles using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or penis erection with biological realism expressing care and love.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erection, breathing or heart rate with biological realism expressing warm and tenderness.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like swelling of the testicles, scrotum tensing, breathing, heart rate, muscle tension or trembling using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like penis erection expressing love and affection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName talks about how much they love the passion, using affectionate language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sweet about how this pace makes them feel.",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName comment on the slower pace, using affectionate language to describe the intimacy.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something loving about why they want to take it slow.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After sex, $currentName comment on how good the sex was, using affectionate language to describe the experience.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName says something sweet about how they want to do it again next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Following sex, $currentName says something loving about what they would like to improve next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After climax, $currentName says something sweet about their orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Post-orgasmic, $currentName comments partner's endowment using affectionate and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After intercourse, $currentName comments partner's endurance using affectionate and seductive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the aftermath of sex, $currentName says something sweet about partner's orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Post-release, $currentName says something sweet about their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName comment on their partner's body, using affectionate language to describe their attraction.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something sweet about how much they're enjoying the intimacy.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes the way their partner's touch makes them feel.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName talks about how turned on they are by their partner's movements.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whispers something sensual about how good it feels to be inside/with their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment on the way their partner's eyes sparkle during sex.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

