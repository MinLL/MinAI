<?php

function setDominantTalkPrompts($currentName)
{
    $gender = GetGender($currentName);
    $in0 = "(";
    $in1 = ")";

    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName commands their partner to acknowledge the frustration caused by the chastity belt, saying something authoritative about how it's their fault.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something dominating about how they'll make their partner pay for their pleasure when the chastity belt comes off.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName exclaims something dominating about their orgasm, using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe the intensity of their orgasm in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName orders their partner to stay still as they reach orgasm and lets out a loud, breathless gasp.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName breath faster and quiver attempting to describe their orgasm using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to describe the intensity of their orgasm among moans and gasps for breath, in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pressure their orgasm and partner's behavior in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something authoritative as they climax.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to command her partner what to do in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she squirts using filthy language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tell how she loses control while squirting using filthy language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something about how she almost passes out while being overcome by a devastating orgasm using dirty language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream between broken filthy words while having orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName demand to be filled with cum using dirty language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName is scolding her partner because she is dirty and sticky from head to toe, using filthy language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName is scolding her partner because she is dirty and sticky with her body covered with a mixture of cum and sweat, using filthy language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName squirts and yelp loudly, dominating her partner and pronouncing dirty words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName tell how he ejaculates in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something authoritative as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cock throbs as he squirts jets of cum, in a manner that expresses dominance over the partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName grunts as he ejaculates, in complete domination of the partner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something dominating in broken sentences.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName orders their partner into the current position, using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName orders their partner to change their behavior in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName orders their partner to adjust position using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName ask partner about the current position in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations related to current position from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pressure the sensory details to enhance visceral and psychological immersion from a dominant egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName describe physical sensations like pressure, fluids, lubrication/friction, discomfort or tearing with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like fluids, lubrication or pressure with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like fluids, friction or tearing with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like fluids, friction or pressure with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like lubrication, friction or tearing with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction or discomfort with biological realism from an egoistic authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like friction or tearing with biological realism from an egoistic authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like friction or pressure with biological realism from an egoistic authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like discomfort, tearing or fluids with biological realism from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort or pressure with biological realism from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like discomfort or fluids with biological realism from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort or friction with biological realism from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like tearing or friction with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pressure or tearing with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName comment physical sensations like pleasure or friction with biological realism using dominant language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or pressure with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or tearing with biological realism using authoritative egoistic language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or heart rate with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical sensations like pleasure or breathing with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or spasms with biological realism using authoritative language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like pleasure or muscle tension with biological realism from a egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            "{$in0}$currentName describe physical responses like body arching, trembling, spasms, breathing or muscle tension from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or breathing from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like breathing or heart rate from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate or muscle tension from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or muscle tension from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like spasms or breathing from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or heart rate from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like muscle tension or trembling from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            
            "{$in0}$currentName says something authoritative about the current position.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, swollen nipples or vaginal lubrication from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like heart rate or swollen clitoris from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like body arching or swollen clitoris from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or swollen clitoris from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like muscle tension or vaginal lubrication with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or vaginal lubrication with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate or vaginal lubrication with biological realism from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or vaginal lubrication with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or swollen nipples with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like swollen nipples or vaginal lubrication with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erected nipples or swollen clitoris from an authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erected nipples or vaginal lubrication from an authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like erected nipples or heart rate from an authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erected nipples or breathing from an authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like engorged clitoris, vaginal lubrication, breathing, heart rate or muscle tension with biological realism from an egoistic dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like vaginal lubrication, breathing, heart rate, body arching or engorged clitoris from an egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, penis erection, swelling of the testicles or scrotum tensing from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like breathing, penis erection or scrotum tensing from a selfish dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like heart rate, body arching, spasms or erection from an egoistic dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like muscle tension or swelling of the testicles from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like spasms or swelling of the testicles from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like spasms or penis erection of the testicles from a dominant perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like trembling or penis erection with biological realism from an authoritative perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses like erection, breathing or heart rate with biological realism from a selfish perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like swelling of the testicles, scrotum tensing, breathing, heart rate, muscle tension or trembling from an authoritative egoistic perspective.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical responses like penis erection in authoritative manner.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName demands their partner keep up with the faster pace, using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something dominating about what they want to do next.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName orders their partner to slow down, using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something controlling about why they want to take it slow.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After intercourse, $currentName comment on how their partner performed, using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After climax, $currentName explain what they dislikes about partner's performance .{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Following sex, $currentName relax and demand improvements from partner for next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sex, $currentName comments partner's endurance using dominant language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After intercourse, $currentName comments partner's orgasm using dominant language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the aftermath of sex, $currentName comments partner's skill using dominant language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Post-orgasmic, $currentName comments partner's endowment using dominant language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Following sex, $currentName says something about what they expect next time, using commanding language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}Post-release, $currentName says something in authoritative manner about what they want the partner to improve next time.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName teases their partner with a playful command.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName demands their partner look at them in a certain way.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describes the way their partner should move during sex.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName talks about how much they love their partner's obedience.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something dominating about their partner's submission.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment on the way their partner should look at them during sex.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
    ];
}

