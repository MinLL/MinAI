<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class GagHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("gag");
    }
    
    protected function getFormattedType() {
        return "Gag";
    }
    
    protected $equipDescriptions = [
        "is carefully positioned between their lips",
        "is firmly secured behind their head",
        "is methodically fastened in place",
        "is adjusted until properly seated",
        "clicks into place with practiced efficiency"
    ];
    
    protected $removeDescriptions = [
        "is gently unfastened and withdrawn",
        "is carefully removed from their mouth",
        "is methodically loosened and taken away",
        "is released with practiced care",
        "is withdrawn with gentle consideration"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test its presence with their tongue",
                "drawing a muffled sound of surprise",
                "making them shift slightly as they adjust",
                "prompting them to test its security",
                "eliciting a quiet, stifled noise"
            ];
        } else {
            return [
                "allowing them to work their jaw carefully",
                "bringing visible relief as they regain their voice",
                "causing them to test their newfound freedom to speak",
                "drawing a soft sigh of relief",
                "prompting them to stretch their jaw experimentally"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        return $this->getLowArousalReactions($isEquip);
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their eyes widening as they test its effectiveness",
                "causing them to make muffled sounds of protest",
                "drawing stifled whimpers as they realize their predicament",
                "making them shift restlessly against the intrusion",
                "eliciting a mix of surprise and anticipation"
            ];
        } else {
            return [
                "their expression a mix of relief and lingering excitement",
                "causing them to touch their lips gently",
                "drawing a shaky breath as they regain their voice",
                "making them test their ability to speak",
                "prompting soft sounds of relief"
            ];
        }
    }
    
    protected function getMediumArousalGaggedReactions($isEquip) {
        return $this->getMediumArousalReactions($isEquip);
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to make desperate muffled sounds",
                "drawing stifled moans of arousal",
                "making them squirm with obvious need",
                "their breathing becoming heavy through their nose",
                "eliciting needy whimpers despite the obstruction"
            ];
        } else {
            return [
                "drawing a gasping moan as they regain their voice",
                "causing them to pant heavily",
                "their aroused state evident in their quick breaths",
                "making them whimper with newfound freedom",
                "prompting soft sounds of need"
            ];
        }
    }
    
    protected function getHighArousalGaggedReactions($isEquip) {
        return $this->getHighArousalReactions($isEquip);
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing desperate muffled pleas",
                "causing them to writhe with intense need",
                "their ragged breathing evident despite the obstruction",
                "making them tremble with desperate arousal",
                "eliciting stifled sounds of intense desire"
            ];
        } else {
            return [
                "causing them to moan deeply with sudden freedom",
                "drawing desperate gasps and whimpers",
                "making them shake with barely-contained desire",
                "their intense arousal evident in their panting",
                "prompting them to voice their desperate need"
            ];
        }
    }
    
    protected function getVeryHighArousalGaggedReactions($isEquip) {
        return $this->getVeryHighArousalReactions($isEquip);
    }
}

// Register the events
$handler = new GagHandler();
RegisterDeviceEvents("gag", $handler); 