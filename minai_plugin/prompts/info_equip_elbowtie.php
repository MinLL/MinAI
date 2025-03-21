<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class ElbowTieHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("elbowtie");
    }
    
    protected $equipDescriptions = [
        "is methodically wrapped around their arms, pulling their elbows together",
        "is carefully secured, drawing their elbows close behind their back",
        "is tightened firmly, forcing their arms into a strict position",
        "binds their elbows together with precise, measured tension",
        "is meticulously tied, pressing their elbows together behind them"
    ];
    
    protected $removeDescriptions = [
        "is carefully unwound from their arms",
        "is gently loosened and removed",
        "is methodically untied, releasing their elbows",
        "is unwrapped with practiced care",
        "releases its strict hold on their arms"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to arch their back slightly from the strict position",
                "making them adjust to the demanding posture",
                "drawing a soft gasp as their elbows are drawn together",
                "prompting them to test the strict bonds carefully",
                "eliciting a quiet sound as they feel their arms secured"
            ];
        } else {
            return [
                "bringing relief as the strain on their arms eases",
                "allowing them to relax their shoulders gratefully",
                "causing them to roll their shoulders with appreciation",
                "drawing a soft sigh as they move their arms freely",
                "prompting them to stretch their freed muscles"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to arch with muffled sounds of adjustment",
                "making them shift with stifled noises as their elbows meet",
                "drawing gagged sounds as they test the strict position",
                "prompting muted sounds as they feel their arms secured",
                "eliciting quiet, gagged sounds as they adjust"
            ];
        } else {
            return [
                "their gagged expression showing relief as the strain eases",
                "allowing them to relax with muffled sounds of relief",
                "causing them to roll their shoulders with stifled sighs",
                "drawing muted sounds as they regain movement",
                "prompting soft sounds through their gag as they stretch"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as they feel the strict bondage",
                "causing them to squirm as their chest is thrust forward",
                "drawing a soft whimper as they test their bonds",
                "making them shift restlessly in the demanding position",
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
                "drawing urgent sounds through their gag as they test the bonds",
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
                "causing them to moan softly at the strict position",
                "drawing whimpers as they feel their chest thrust forward",
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
                "causing muffled moans as they test the strict bondage",
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
                "drawing desperate whimpers as they strain in the strict position",
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
$handler = new ElbowTieHandler();
RegisterDeviceEvents("elbowtie", $handler); 