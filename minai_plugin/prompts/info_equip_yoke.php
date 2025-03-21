<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class YokeHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("yoke");
    }
    
    protected function getFormattedType() {
        return "Yoke";
    }
    
    protected $equipDescriptions = [
        "is carefully positioned around their neck and wrists",
        "is methodically secured, spreading their arms wide",
        "locks into place with their arms held out to the sides",
        "is adjusted to hold their arms away from their body",
        "is fastened securely, forcing their arms outward"
    ];
    
    protected $removeDescriptions = [
        "is carefully unfastened and lifted away",
        "is gently removed from their neck and wrists",
        "is methodically unlocked and taken off",
        "releases its hold on their arms and neck",
        "is lifted away, freeing their arms and neck"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test the rigid restraint cautiously",
                "making them adjust to the awkward position of their arms",
                "drawing a soft gasp as they feel the strain on their shoulders",
                "prompting them to shift their neck experimentally",
                "eliciting a quiet sound as they test their limited range of motion"
            ];
        } else {
            return [
                "bringing relief as the strain on their shoulders eases",
                "allowing them to lower their arms gratefully",
                "causing them to roll their shoulders with appreciation",
                "drawing a soft sigh as they massage their neck",
                "prompting them to stretch their freed arms"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test the restraint with muffled sounds",
                "making them shift with stifled noises of adjustment",
                "drawing gagged sounds as they feel the strain",
                "prompting muted sounds as they test their position",
                "eliciting quiet, gagged sounds as they adjust"
            ];
        } else {
            return [
                "their gagged expression showing relief as the strain eases",
                "allowing them to lower their arms with muffled sounds",
                "causing them to roll their shoulders with stifled sighs",
                "drawing muted sounds of relief as they stretch",
                "prompting soft sounds through their gag as they recover"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as they feel their vulnerability",
                "causing them to squirm in the exposed position",
                "drawing a soft whimper as they realize their predicament",
                "making them shift restlessly in the rigid restraint",
                "eliciting a mix of nervousness and excitement"
            ];
        } else {
            return [
                "their expression mixing relief with lingering excitement",
                "causing them to stretch with evident pleasure",
                "drawing shaky breaths as they regain mobility",
                "making them move with obvious satisfaction",
                "prompting a complex mix of emotions to cross their face"
            ];
        }
    }
    
    protected function getMediumArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their muffled sounds betraying growing excitement",
                "causing them to squirm with stifled whimpers",
                "drawing urgent sounds through their gag as they test the restraint",
                "making them shift restlessly with muted sounds",
                "eliciting gagged sounds of mixed nervousness and arousal"
            ];
        } else {
            return [
                "their gagged expression mixing relief with obvious excitement",
                "causing them to stretch with muffled sounds of pleasure",
                "drawing stifled, shaky breaths as they recover",
                "making them move with soft sounds behind their gag",
                "prompting muffled sounds of conflicted emotion"
            ];
        }
    }

    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to moan softly at their exposed helplessness",
                "drawing whimpers as they feel completely vulnerable",
                "making them squirm with obvious excitement",
                "their breathing becoming uneven as they strain",
                "eliciting needy sounds as they test their bonds"
            ];
        } else {
            return [
                "drawing a shaky moan as they regain movement",
                "causing them to stretch with evident arousal",
                "their excited state evident in their quick breathing",
                "making them tremble slightly as they recover",
                "prompting soft sounds of mixed relief and arousal"
            ];
        }
    }
    
    protected function getHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing muffled moans as they test the rigid restraint",
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
                "making them tremble as they recover their mobility",
                "prompting urgent sounds of mixed relief and arousal"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing desperate whimpers as they strain helplessly",
                "causing them to writhe with obvious need",
                "their ragged breathing betraying their intense arousal",
                "making them tremble with desperate excitement",
                "eliciting pleading sounds as they test their bonds"
            ];
        } else {
            return [
                "causing them to moan deeply with sudden freedom",
                "drawing desperate sounds as they stretch",
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
$handler = new YokeHandler();
RegisterDeviceEvents("yoke", $handler); 