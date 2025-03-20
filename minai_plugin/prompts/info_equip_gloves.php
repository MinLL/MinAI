<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class GlovesHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("gloves");
    }
    
    protected function getFormattedType() {
        return "Restrictive Gloves";
    }
    
    protected $equipDescriptions = [
        "is carefully pulled over their hands",
        "is methodically fitted to their fingers",
        "slides snugly onto their hands",
        "encases their fingers and palms",
        "is meticulously secured around their hands"
    ];
    
    protected $removeDescriptions = [
        "is carefully peeled from their hands",
        "is gently worked off their fingers",
        "releases their hands from confinement",
        "is methodically removed",
        "slides off their restricted hands"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to flex their fingers experimentally",
                "drawing a soft sound as they test their limited dexterity",
                "making them curl and uncurl their hands",
                "prompting them to adjust to the restriction",
                "eliciting a quiet response as they feel their hands confined"
            ];
        } else {
            return [
                "allowing them to stretch their freed fingers",
                "bringing relief as they regain dexterity",
                "causing them to flex their hands carefully",
                "drawing a soft sigh as they test their mobility",
                "prompting them to massage their freed hands"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath catching as they realize their limited control",
                "causing them to test the extent of their restriction",
                "drawing soft sounds as they adjust to their helplessness",
                "making them shift nervously at their reduced capability",
                "eliciting a mix of anticipation and vulnerability"
            ];
        } else {
            return [
                "their hands tingling as sensation returns",
                "causing them to rub their sensitive palms",
                "drawing shaky breaths as they regain control",
                "making them flex their fingers with relief",
                "leaving them acutely aware of their restored freedom"
            ];
        }
    }
}

// Register the events
$handler = new GlovesHandler();
RegisterDeviceEvents("gloves", $handler); 