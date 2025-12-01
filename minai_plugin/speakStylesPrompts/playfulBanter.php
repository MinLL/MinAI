<?php

function setPlayfulBanterPrompts($currentName) 
{
    $gender = GetGender($currentName);
    $in0 = "(";
    $in1 = ")";
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName jokes about the chastity belt, saying something funny about how it's like a challenge to come up with new ways to tease each other.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something lighthearted about how they'll just have to be more creative in the meantime, and that the chastity belt is actually a fun obstacle to overcome.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName exclaims something playful about their orgasm, using a lighthearted tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something funny as they reach orgasm and lets out a loud, breathless gasp.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName breath faster and quiver attempting to describe their orgasm using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to describe the intensity of their orgasm among moans and gasps for breath, using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something funny as they climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to say something playful in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she squirts using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she loses control while squirting using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something about how she almost passes out while being overcome by a devastating orgasm using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream between broken playful words while having orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName ask to be filled with cum using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky from head to toe using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky with her body covered with a mixture of cum and sweat using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName squirts and yelp loudly, pronouncing words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName tell how he ejaculates using playful language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something playful as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cock throbs as he squirts jets of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName grunts as he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something playful in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName teases their partner about the current position, using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            
            "{$in0}$currentName comment how happy is to try the current position, using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how happy is to give pleasure, using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment the partner's skill, using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment a relevant physical aspect of the partner, using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment how exciting current position is, keeping the mood light.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment what physical sensations the current position induces, keeping the mood light.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName describe the sensory details to enhance visceral and psychological immersion using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pressure, fluids, friction, discomfort or tearing using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like fluids, lubrication, discomfort or tearing using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like fluids, friction or tearing or pressure using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction, discomfort or tearing or pressure using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like lubrication or fluids using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort, tearing, pressure, fluids or lubrication/friction using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment physical sensations like pleasure or friction using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or pressure using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or tearing using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or heart rate using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or breathing using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or spasms using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or muscle tension using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName describe physical responses like body arching, trembling, spasms using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or spasms using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like breathing or heart rate using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms, breathing using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like body arching or spasms using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            
            "{$in0}$currentName jokes about the current position, keeping the mood light.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, swollen nipples or vaginal lubrication using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate, body arching, spasms or swollen clitoris using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like muscle tension, spasms or vaginal lubrication using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or vaginal lubrication using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like vaginal lubrication or friction using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like vaginal lubrication or discomfort  using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like vaginal lubrication or pressure  using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling, swollen nipples or vaginal lubrication using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erected nipples or swollen clitoris using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like engorged clitoris, breathing, heart rate or muscle tension using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like vaginal lubrication or engorged clitoris using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, penis erection, swelling of the testicles, scrotum tensing using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate, body arching, spasms, erection using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like muscle tension, spasms, swelling of the testicles using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling, scrotum tensing, penis erection using a lighthearted tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erection, breathing, body arching using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like swelling of the testicles, breathing, heart rate or trembling using a lighthearted and playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like penis erection using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName taunts their partner about keeping up with the faster pace, using a playful and cheeky tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName praise the partner's skill, using a playful and cheeky tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName praise a relevant physical aspect of the partner, using a playful and cheeky tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName ask partner about the current position using a playful and cheeky tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations related to current position in a playful manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something funny about what they want to do next, trying to keep the mood light.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName playfully scolds their partner for slowing down, using a lighthearted tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName jokes about taking a break, keeping the mood relaxed.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After intercourse, $currentName teases their partner about their performance, using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sex, $currentName jokes about what they expect next time, keeping the mood light.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After lovemaking, $currentName jokes about their orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the afterglow, $currentName comments partner's endowment using explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After climax, $currentName comments partner's endurance in a playful manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After intercourse, $currentName says something about partner's orgasm using a playful tone.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the aftermath of sex, $currentName playfully scolds their partner about what they would like to improve next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName comment on their partner's body, using a playful and lighthearted tone to keep the mood light.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something funny about their partner's attempts to seduce them.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes the way their partner's teasing makes them laugh.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName enjoys the passionate pace set by their partner, resulting in flirtatious and fun remarks.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something cheeky about their partner's flirting.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}The attention to their erogenous zones delights $currentName, who can't resist making witty comments.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

