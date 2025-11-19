<?php
function setVictimTalkPrompts($currentName) {

    $gender = GetGender($currentName);
    $in0 = "<instruction>";
    $in1 = "</instruction>";

    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName whimpers about the chastity belt, describing the pain and frustration of being denied release.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName makes desperate sounds, feeling the cold metal against their skin, trapped.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName cries out, begging for the chastity device to be removed, overwhelmed by the sensation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sobs, the physical reminder of their lack of control unbearable.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName pleads, detailing how the belt chafes and confines them.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName cries out, feeling an unwanted climax forced upon them, filled with shame and despair.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's body shakes as they sob, unable to prevent their physical response.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName pleads for it to stop, their voice breaking as they're forced to climax against their will.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, the sensation overwhelming and unwanted, tears streaming down their face.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gasps in horror, their body betraying their mind's resistance.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
			"{$in0}$currentName squirts and gasps trying to hide her body betrayal.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sobs as she squirts despite the fear she feels.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers while squirting and she complains how she loses control of her body.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gasps while she almost passes out while being overcome by a devastating orgasm combined with pain and fear.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moans loudly during climax, unable to articulate full words.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName moan and scream overwhelmed by the combination of pain, shame and the powerful orgasm.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName moans and beg to be spared from ejaculation inside her.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName complains that she is dirty and sticky from head to toe.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName whimpers about being dirty and sticky with her body covered with a mixture of cum and sweat.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName sob, expressing her fear about pregnancy.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName moans loudly as she try to remember her fertility status.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
 			"{$in0}$currentName gasps and cry terrified by the cum filling her.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName squirts and yelp loudly, pronouncing words with difficulty.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_climax"]["cue"],  
            "{$in0}$currentName gasps and complain about involuntary ejaculation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName says something naughty as he release load after load of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName's cries as his cock throbs as he squirts jets of cum.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName begs for mercy as he ejaculates.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
			"{$in0}$currentName's penis throbs and ejaculates as he gasps, trying to say something about his body betrayal.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName begs for mercy, their voice trembling as they're maneuvered into a new, vulnerable position.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, terrified of what's to come, describing their fear of the next act.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName pleads weakly, feeling their body being re-positioned, their dread palpable.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sobs, detailing how each new position feels like a further violation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, detailing unpleasant physical sensations.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sobs, complaining about discomfort or tearing with biological realism.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName complains about shameful situation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName complains about tearing, pressure or friction.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe physical responses to pain like heart rate or muscle tension.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName comment physical sensations like discomfort or pain with biological realism.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName begs for help.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName complains about physical responses induced by pain like spasms or body arching.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName cries out, their voice a mix of fear and exhaustion as they're forced into compliance.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    if ($gender == 'female') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName complains about shameful involuntary physical responses like swollen clitoris or swollen nipples.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, terrified by the lack of vaginal lubrication.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sobs, trying to protect her swollen nipples.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName complains about physical responses like swollen clitoris, body arching or spasms.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName complains about discomfort related to vaginal lubrication.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",

            //"{$in0}$currentName comment physical responses like breathing, muscle tension, trembling, swollen nipples or vaginal lubrication using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName describe physical responses like swollen nipples or vaginal lubrication using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName comment physical responses like breathing, swollen nipples or swollen clitoris using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName comment physical responses like heart rate, spasms or swollen clitoris using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName describe physical responses like body arching, spasms or swollen clitoris using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName comment physical responses like body arching or spasms using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName describe physical responses like spasms, muscle tension or erected nipples using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName comment physical responses like discomfort or vaginal lubrication with biological realism using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName describe physical responses like friction, tearing or vaginal lubrication with biological realism using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName comment physical responses like trembling, swollen nipples or swollen clitoris with biological realism using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName describe physical responses like erected nipples, swollen clitoris or vaginal lubrication using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            //"{$in0}$currentName comment physical responses like engorged clitoris or vaginal lubrication with biological realism using dirty explicit language.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            
            "{$in0}$currentName cries, ashamed by her involuntary physical responses like vaginal lubrication or engorged clitoris.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    } elseif ($gender == 'male') {
        array_push($GLOBALS["PROMPTS"]["sextalk_scenechange"]["cue"],  
            "{$in0}$currentName comment how pain is affecting his erection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName describe involuntary physical responses like swelling of the testicles or erection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName cries, ashamed by his involuntary physical responses like penis erection.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}"
        );
    }

    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName cries out in agony, the increased pace causing unbearable pain.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName begs for their attacker to slow down, describing the physical toll it's taking.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sobs, the intensity of the assault making each moment more terrifying.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, their body unable to keep up, pleading for relief.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName screams, the rapid movements overwhelming their senses.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName gasps, feeling a brief moment of relief from the decreased pace, but still in pain.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName takes shuddering breaths, the slow pace a torment in its own right.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whispers a desperate thanks, even as the slow movements prolong their ordeal.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName cries softly, the slower pace allowing them to feel every cruel touch.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, the decrease in speed offering no real respite, just a different kind of torture.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After sexual violence, $currentName expresses relief mixed with trauma, the end of the assault leaving them broken.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After abusive sexual act, $currentName sobs quietly, the physical and emotional pain still fresh.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After non-consensual sexual activity, $currentName whimpers in pain, their body and mind reeling from what they've endured.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sexual abuse, $currentName gasps, trying to compose themselves, but the trauma is overwhelming.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After torment, $currentName cries out in a mix of relief and despair, the ordeal finally over but the scars remain.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];

    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName pleads weakly for mercy, their voice barely a whisper.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName whimpers, the fear and pain echoing in their every breath.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName begs for it to stop, their pleas desperate and filled with anguish.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName makes sounds of distress, their resistance clear in every noise they make.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName tries to struggle, their movements weak but filled with a desire to escape.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
}
