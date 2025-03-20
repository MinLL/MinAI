<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class BlindfoldHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("blindfold");
    }
    
    protected function getFormattedType() {
        return "Blindfold";
    }
    
    protected $equipDescriptions = [
        "is gently secured over their eyes",
        "is carefully positioned to block their vision",
        "is adjusted to ensure complete darkness",
        "wraps snugly around their head",
        "settles into place over their eyes"
    ];
    
    protected $removeDescriptions = [
        "is carefully lifted from their eyes",
        "is gently untied and removed",
        "is slowly pulled away",
        "releases its hold on their vision",
        "is delicately unfastened"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to blink as darkness descends",
                "drawing a soft gasp as their sight is taken",
                "making them tilt their head as they adjust",
                "prompting them to reach up tentatively",
                "eliciting a quiet sound as they process the darkness"
            ];
        } else {
            return [
                "allowing them to blink as light returns",
                "bringing relief as their vision is restored",
                "causing them to adjust to the light gradually",
                "drawing a soft sigh as they regain their sight",
                "prompting them to look around carefully"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as darkness envelops them",
                "causing them to shift nervously in their newfound blindness",
                "drawing soft sounds as they lose their sight",
                "making them reach out uncertainly",
                "eliciting a mix of anticipation and vulnerability"
            ];
        } else {
            return [
                "leaving them blinking in the sudden light",
                "causing them to shield their sensitive eyes",
                "drawing shaky breaths as their vision returns",
                "making them pause as they readjust to sight",
                "their expression showing mixed relief and lingering tension"
            ];
        }
    }
}

// Register the events
$handler = new BlindfoldHandler();
RegisterDeviceEvents("blindfold", $handler); 