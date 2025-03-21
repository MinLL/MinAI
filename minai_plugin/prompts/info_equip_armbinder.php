<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class ArmbinderHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("armbinder");
    }
    
    protected $equipDescriptions = [
        "is methodically secured around their arms",
        "is carefully laced up their arms",
        "is firmly pulled tight and fastened",
        "is meticulously adjusted and secured",
        "binds their arms together behind their back"
    ];
    
    protected $removeDescriptions = [
        "is carefully unlaced and removed",
        "is gently loosened and taken away",
        "is methodically unfastened",
        "is released with practiced care",
        "is removed, freeing their arms"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test their bonds with careful movements",
                "making them shift as they adjust to their restricted mobility",
                "drawing a soft gasp as they realize how helpless their arms are",
                "prompting them to roll their shoulders experimentally",
                "eliciting a quiet sound of resignation as they test their bonds"
            ];
        } else {
            return [
                "bringing visible relief as they regain movement",
                "allowing them to stretch their arms gratefully",
                "causing them to roll their shoulders with appreciation",
                "drawing a soft sigh as they flex their freed arms",
                "prompting them to test their restored mobility"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test their bonds with muffled sounds",
                "making them shift with stifled noises of adjustment",
                "drawing gagged sounds as they realize their helplessness",
                "prompting muted sounds as they test their restraints",
                "eliciting quiet, gagged sounds of resignation"
            ];
        } else {
            return [
                "their gagged expression showing relief as they regain movement",
                "allowing them to stretch with muffled sounds of appreciation",
                "causing them to roll their shoulders with stifled sighs",
                "drawing muted sounds of relief as they flex their arms",
                "prompting soft sounds through their gag as they test their freedom"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as they test their strict bondage",
                "causing them to squirm as they feel their vulnerability",
                "drawing a soft whimper as they realize how helpless they are",
                "making them shift restlessly in their bonds",
                "eliciting a mix of nervousness and excitement"
            ];
        } else {
            return [
                "their expression mixing relief with lingering excitement",
                "causing them to stretch with obvious pleasure",
                "drawing shaky breaths as they regain control",
                "making them move their arms with evident satisfaction",
                "prompting a complex mix of emotions to cross their face"
            ];
        }
    }
    
    protected function getMediumArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their muffled sounds betraying growing excitement",
                "causing them to squirm with stifled whimpers",
                "drawing urgent sounds through their gag as they test their bonds",
                "making them shift restlessly with muted sounds",
                "eliciting gagged sounds of mixed nervousness and arousal"
            ];
        } else {
            return [
                "their gagged expression mixing relief with obvious excitement",
                "causing them to stretch with muffled sounds of pleasure",
                "drawing stifled, shaky breaths as they regain control",
                "making them move with soft sounds behind their gag",
                "prompting muffled sounds of conflicted emotion"
            ];
        }
    }

    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to moan softly at their helplessness",
                "drawing whimpers of aroused vulnerability",
                "making them squirm with obvious excitement",
                "their breathing becoming uneven as they test their bonds",
                "eliciting needy sounds as they realize their predicament"
            ];
        } else {
            return [
                "drawing a shaky moan as they regain movement",
                "causing them to stretch with evident arousal",
                "their excited state evident in their quick breathing",
                "making them tremble slightly as they flex their arms",
                "prompting soft sounds of mixed relief and arousal"
            ];
        }
    }
    
    protected function getHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing muffled moans as they test their strict bondage",
                "drawing stifled whimpers of aroused vulnerability",
                "making them squirm with obvious excitement behind their gag",
                "their gagged breaths becoming uneven as they strain",
                "eliciting urgent sounds as they realize their helplessness"
            ];
        } else {
            return [
                "drawing muffled moans as they regain movement",
                "causing them to stretch with stifled sounds of arousal",
                "their excited state evident in their quick, muffled breathing",
                "making them tremble as they flex their freed arms",
                "prompting urgent sounds of mixed relief and arousal"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing desperate whimpers as they strain against the bonds",
                "causing them to writhe with obvious need",
                "their ragged breathing betraying their intense arousal",
                "making them tremble with desperate excitement",
                "eliciting pleading sounds as they test their helplessness"
            ];
        } else {
            return [
                "causing them to moan deeply with sudden freedom",
                "drawing desperate sounds as they stretch their arms",
                "making them shake with barely-contained excitement",
                "their intense arousal evident in their quick breaths",
                "prompting them to move with obvious need"
            ];
        }
    }
    
    protected function getVeryHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing urgent, muffled sounds of desperation",
                "causing them to writhe needfully, their moans stifled",
                "their ragged breathing through their nose betraying their excitement",
                "making them tremble with desperate need behind their gag",
                "eliciting frantic muffled noises as they strain"
            ];
        } else {
            return [
                "causing them to make urgent sounds of need",
                "drawing desperate muffled moans as they stretch",
                "making them shake with barely-contained excitement",
                "their intense arousal evident in their quick breaths",
                "prompting muffled whimpers as they test their freedom"
            ];
        }
    }
}

// Register the events
$handler = new ArmbinderHandler();
RegisterDeviceEvents("armbinder", $handler); 