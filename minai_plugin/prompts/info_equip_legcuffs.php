<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class LegCuffsHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("legcuffs");
    }
    
    protected function getFormattedType() {
        return "Leg Restraints";
    }
    
    protected $equipDescriptions = [
        "is securely fastened around their ankles",
        "clicks shut around their legs",
        "is carefully locked into position",
        "binds their legs firmly",
        "is methodically secured in place"
    ];
    
    protected $removeDescriptions = [
        "is carefully unlocked and removed",
        "releases their ankles with a click",
        "is gently unfastened",
        "loosens its grip on their legs",
        "is methodically detached"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test their limited stride",
                "drawing a soft sound as they feel their restricted movement",
                "making them shift their weight carefully",
                "prompting them to test their new bounds",
                "eliciting a quiet response as they adjust to the limitation"
            ];
        } else {
            return [
                "allowing them to stretch their legs freely",
                "bringing relief as they regain their full stride",
                "causing them to shift their weight naturally",
                "drawing a soft sigh as they test their mobility",
                "prompting them to take a few experimental steps"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as they test their restricted movement",
                "causing them to shift unsteadily as they adjust",
                "drawing soft sounds as they realize their vulnerability",
                "making them sway slightly in their bonds",
                "eliciting a mix of uncertainty and submission"
            ];
        } else {
            return [
                "leaving them with trembling legs",
                "causing them to steady themselves carefully",
                "drawing shaky breaths as they regain their balance",
                "making them take a moment to find their footing",
                "their relief evident as they regain mobility"
            ];
        }
    }
}

// Register the events
$handler = new LegCuffsHandler();
RegisterDeviceEvents("legcuffs", $handler); 