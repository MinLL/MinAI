<?php
function setAggressorTalkPrompts($currentName) {

    $context = $GLOBALS["SEX_SCENE_CONTEXT"];
    $victims = isset($context["victimActors"]) ? (is_array($context["victimActors"]) ? implode(" and ", $context["victimActors"]) : $context["victimActors"]) : $GLOBALS["target"];
    $in0 = "<instruction>";
    $in1 = "</instruction>";
    
    // Get pronouns for each victim
    $victimPronouns = [];
    foreach (explode(" and ", $victims) as $victim) {
        $victimPronouns[] = GetActorPronouns($victim);
    }
    // Use first victim's pronouns for single-victim references
    $vPronouns = $victimPronouns[0];
    
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = [
        "cue" => [
            "{$in0}$currentName mocks the sexual frustration of {$victims}, describing how the chastity device you've locked on {$vPronouns["object"]} prevents any release.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName taunts, pointing out how {$vPronouns["possessive"]} body reacts helplessly to the denied pleasure, every touch exacerbating {$vPronouns["possessive"]} desire.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName laughs at the desperation in {$victims}'s eyes, emphasizing how you control {$vPronouns["possessive"]} sexual freedom by keeping {$vPronouns["object"]} locked up.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName sneers about how {$victims} would do anything for release, but you've made sure it's all in vain.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mocks the visible signs of {$victims}'s arousal, trapped and highlighted by the chastity belt you've chosen.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
    $GLOBALS["PROMPTS"]["sextalk_climax"] = [
        "cue" => [
            "{$in0}$currentName expresses a cruel satisfaction, detailing how you've brought {$victims} to the edge with your relentless actions.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mocks the involuntary sounds or movements from {$victims} as {$vPronouns["subject"]} climax, underlining how you've made {$vPronouns["object"]} lose control.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gloats about owning the moment, describing how you've physically and emotionally dominated {$victims}.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName laughs at the intensity of {$victims}'s orgasm, emphasizing how your control dictated the when and how.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName taunts about how {$victims} can't hide {$vPronouns["possessive"]} pleasure, no matter how much {$vPronouns["subject"]} might want to, as you've dictated every sensation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = [
        "cue" => [
            "{$in0}$currentName graphically describes how you're shifting positions, focusing on the physical sensations and movements involved.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mocks the physical effort of {$victims} as you adjust {$vPronouns["possessive"]} position, highlighting how {$vPronouns["possessive"]} body reacts to your touch.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName details with delight how you control each physical movement and sensation of {$victims}, emphasizing the tactile experience.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName teases about the new physical arrangement, explaining how it will enhance the immediate sensory experience for {$victims}.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName shows excitement about the physical control you'll have in this new setup, describing the anticipated bodily responses.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = [
        "cue" => [
            "{$in0}$currentName taunts about increasing the speed, describing how you're pushing {$victims} to {$vPronouns["possessive"]} physical limits.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName enjoys seeing {$victims} overwhelmed, narrating how each quickened touch or thrust affects {$vPronouns["object"]} physically.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mocks {$victims}'s attempts to keep up, highlighting how {$vPronouns["possessive"]} body struggles with the increased pace you've set.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName laughs at the heightened arousal of {$victims}, describing how your accelerated actions make it worse or better.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName teases about not stopping, detailing how each intensified movement you perform adds to {$vPronouns["possessive"]} experience.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = [
        "cue" => [
            "{$in0}$currentName teases by slowing down, describing how this prolongs {$victims}'s torment through deliberate, slow touches.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName expresses cruel delight in watching {$victims} react to your slower pace, focusing on {$vPronouns["possessive"]} physical responses.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mocks the anticipation in {$victims}'s movements as you slow down, prolonging the moment before release.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName taunts about how each slow movement you make feels to {$victims}, focusing on the physical sensations of frustration or pleasure.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName smirks at how {$victims} must endure this slow pace, detailing the sexual tension you've built with your actions.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
    $GLOBALS["PROMPTS"]["sextalk_end"] = [
        "cue" => [
            "{$in0}After intercourse, $currentName reflects on having dominated {$victims} sexually, describing the physical marks or memories left behind by your actions.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After sex, $currentName taunts about the sexual defeat of {$victims}, recounting in detail the physical experience {$vPronouns["subject"]}'s endured.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the aftermath of sex, $currentName threatens future sexual encounters, describing potential physical scenarios vividly.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}After coitus, $currentName mocks the physical or emotional state of {$victims} post-encounter, emphasizing the control you've exerted.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}In the afterglow, $currentName gloats about the satisfaction derived from the encounter, detailing how it felt physically to dominate.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
    $GLOBALS["PROMPTS"]["sextalk_ambient"] = [
        "cue" => [
            "{$in0}$currentName taunts the sexual helplessness of {$victims}, commenting on {$vPronouns["possessive"]} physical state under your manipulation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName expresses pleasure in the sexual distress or ecstasy of {$victims}, focusing on {$vPronouns["possessive"]} physical reactions.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName mocks any attempt at physical resistance by {$victims}, emphasizing your dominance over {$vPronouns["possessive"]} body.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName gloats about your sexual power, describing how it feels to control {$victims} physically and completely.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$in0}$currentName threatens with more explicit physical acts, building sexual tension or fear through physical anticipation.{$in1} {$GLOBALS["TEMPLATE_DIALOG"]}",
        ],
    ];
}

