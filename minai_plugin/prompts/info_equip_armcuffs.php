<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class ArmCuffsHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("armcuffs");
    }
    
    protected function getFormattedType() {
        return "Arm Restraints";
    }
    
    protected $equipDescriptions = [
        "is securely fastened around their wrists",
        "clicks shut around their arms",
        "is carefully locked into position",
        "binds their arms firmly",
        "is methodically secured in place"
    ];
    
    protected $removeDescriptions = [
        "is carefully unlocked and removed",
        "releases their wrists with a click",
        "is gently unfastened",
        "loosens its grip on their arms",
        "is methodically detached"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test the restraints' hold",
                "drawing a soft intake of breath as they feel their restricted movement",
                "making them flex their wrists experimentally",
                "prompting them to shift their arms in their new bonds",
                "eliciting a quiet sound as they adjust to the restriction"
            ];
        } else {
            return [
                "allowing them to stretch their freed arms",
                "bringing relief as they regain mobility",
                "causing them to rub their wrists gently",
                "drawing a soft sigh of relief",
                "prompting them to test their restored freedom"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath catching as they test their bonds",
                "causing them to shift as they process their helplessness",
                "drawing a soft whimper as they realize their vulnerability",
                "making them tug gently at their restraints",
                "eliciting a mix of nervousness and anticipation"
            ];
        } else {
            return [
                "leaving them with a lingering sense of vulnerability",
                "causing them to shiver slightly as they regain control",
                "drawing shaky breaths as they adjust",
                "making them flex their freed wrists slowly",
                "their expression showing mixed relief and longing"
            ];
        }
    }
}

// Register the events
$handler = new ArmCuffsHandler();
RegisterDeviceEvents("armcuffs", $handler); 