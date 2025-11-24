<?php

function setSubmissiveTalkPrompts($currentName)
{
    $gender = GetGender($currentName);
    $in0 = "(";
    $in1 = ")";

    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName obediently laments about the chastity belt, saying something submissive about how they're willing to wait for their partner's pleasure.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something yielding about how they'll accept the delay because of chastity belt, but eagerly awaits their partner's release.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName exclaims something submissive about their orgasm, using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName is grateful to be allowed to have an orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName shows gratitude for an intense orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName breath faster and quiver attempting to describe their orgasm using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to describe the intensity of their orgasm among moans and gasps for breath, using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something yielding as they climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to shows gratitude in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gratefully tell how she squirts.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she loses control while squirting using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something about how she almost passes out while being overcome by a devastating orgasm using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream between broken words while having orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName beg to be filled with cum, happy to submit to partner's will.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky from head to toe, worried that her partner will no longer like her.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky with her body covered with a mixture of cum and sweat, worried that her partner will no longer want her.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName beg to be filled with cum and comment about her fertility status and pregnancy probability.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName comment about her fertility status.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gratefully squirts and yelp loudly, pronouncing words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName gratefully tell how he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something obedient as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cock throbs as he squirts jets of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName gratefully grunts as he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something obedient in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName asks permission for the current position, using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how happy is to submit to partner's will.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how happy is to give pleasure.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName praise the partner's skill.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName praise a relevant physical aspect of the partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tells how exciting current position is, using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says what physical sensations the current position induces, using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment the physical arrangement, explaining how it will enhance the sensory experience using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe the sensory details to enhance visceral and psychological immersion using submissive obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment physical sensations like pressure, discomfort or tearing with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like fluids, discomfort or tearing with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like fluids, lubrication or friction with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like friction or tearing with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction or discomfort with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like friction, tearing or pressure with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like lubrication or pressure with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like lubrication or fluids with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like lubrication or friction with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pressure, discomfort or tearing with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pressure or discomfort with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pressure or tearing with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pressure, fluids or lubrication with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like discomfort or tearing with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort or fluids with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like discomfort, friction or tearing with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort or pressure with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or friction with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or pressure with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or tearing with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or heart rate with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or breathing with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or spasms with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or muscle tension with biological realism using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment physical responses like body arching spasms, or muscle tension using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like trembling, spasms or breathing using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like breathing or heart rate using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate or muscle tension using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like breathing or trembling using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like spasms or breathing using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms, heart rate or muscle tension using submissive obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            
            "{$in0}$currentName says something obedient about the current position.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
    
    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName describe physical responses like breathing, muscle tension, trembling, swollen nipples or vaginal lubrication using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName beg to be filled with cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like swollen nipples or vaginal lubrication using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like breathing, swollen nipples or swollen clitoris using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like swollen clitoris or swollen nipples using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate, spasms or swollen clitoris using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like body arching, spasms or swollen clitoris using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like body arching or spasms using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms, muscle tension or erected nipples using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like discomfort or vaginal lubrication with biological realism using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like friction, tearing or vaginal lubrication with biological realism using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like trembling, swollen nipples or swollen clitoris with biological realism using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erected nipples, swollen clitoris or vaginal lubrication using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like engorged clitoris or vaginal lubrication with biological realism using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate, body arching, spasms or swollen clitoris using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like muscle tension, spasms or vaginal lubrication with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling, swollen nipples or vaginal lubrication with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erected nipples, swollen clitoris, vaginal lubrication, breathing or heart rate using obedient explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like vaginal lubrication, breathing, heart rate, body arching or engorged clitoris using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, penis erection, swelling of the testicles or scrotum tensing using submissive explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate, body arching, spasms or erection using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like muscle tension, spasms or swelling of the testicles using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like trembling or penis erection with biological realism using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erection, breathing or heart rate with biological realism using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like swelling of the testicles, scrotum tensing, breathing, heart rate, muscle tension or trembling using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like penis erection using obedient language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName begs their partner to keep up the faster pace, using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something submissive about what they want to do next.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName asks their partner to slow down, using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something obedient about why they want to take it slow.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After sex, $currentName thanks their partner for the experience, using submissive language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName says something obedient about how they will please again their partner next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the aftermath of sex, $currentName shows gratitude for being allowed to have sex.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After climax, $currentName thanks for being allowed to have orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sex, $currentName shows gratitude because they were allowed to enjoy their partner's endowment.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName tell how happy they are for the chance to please their partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the afterglow, $currentName praise their partner skills.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After intercourse, $currentName praise their partner endurance and stamina.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName comment on their own body, using submissive language to invite attention.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something obedient about their partner's touch.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes the way they want to partner to hold them.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName talks about how much they love their partner's control.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something yielding about their partner's dominance.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment on the way they want to be looked at during sex.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

