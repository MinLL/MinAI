<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class StraitjacketHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("straitjacket");
    }
    
    protected function getFormattedType() {
        return "Straitjacket";
    }
    
    protected $equipDescriptions = [
        "is methodically wrapped around their torso",
        "is carefully secured with multiple straps and buckles",
        "is firmly fastened, binding their arms against their body",
        "is tightened with practiced efficiency",
        "envelops them in its restrictive embrace"
    ];
    
    protected $removeDescriptions = [
        "is carefully unbuckled and unwrapped",
        "is methodically loosened and removed",
        "is unfastened strap by strap",
        "is gently pulled away from their body",
        "releases its tight hold on them"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test the severe restriction of their arms",
                "drawing a soft gasp as they realize how thoroughly they're bound",
                "making them shift uncomfortably as they adjust to their confinement",
                "prompting them to flex against the unyielding canvas",
                "eliciting a quiet sound of resignation as they feel its tight embrace"
            ];
        } else {
            return [
                "bringing visible relief as they regain mobility",
                "allowing them to stretch their arms carefully",
                "causing them to roll their shoulders experimentally",
                "drawing a soft sigh as they test their recovered freedom",
                "prompting them to flex their arms gratefully"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test the bonds with muffled sounds",
                "drawing stifled gasps as they realize their helplessness",
                "making them shift with quiet, gagged noises",
                "prompting muted sounds as they test the restraint",
                "eliciting soft sounds through their gag as they adjust"
            ];
        } else {
            return [
                "their gagged expression showing clear relief",
                "allowing them to stretch with muffled sighs",
                "causing them to make soft sounds of relief",
                "drawing quiet sounds through their gag",
                "prompting stifled sounds of comfort as they move freely"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as they test their complete helplessness",
                "causing them to squirm within its tight confines",
                "drawing soft whimpers as they realize how thoroughly they're bound",
                "making them shift restlessly against the restrictive canvas",
                "eliciting a mix of nervousness and excitement"
            ];
        } else {
            return [
                "their expression mixing relief with lingering excitement",
                "causing them to move their arms with obvious pleasure",
                "drawing shaky breaths as they readjust to freedom",
                "making them stretch with visible satisfaction",
                "prompting a complex mix of emotions to cross their face"
            ];
        }
    }
    
    protected function getMediumArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their muffled sounds betraying growing excitement",
                "causing them to squirm with stifled whimpers",
                "drawing urgent sounds through their gag",
                "making them shift restlessly with muted noises",
                "eliciting muffled sounds of mixed anticipation"
            ];
        } else {
            return [
                "their gagged expression showing mixed relief and excitement",
                "causing them to stretch with muffled sounds",
                "drawing stifled, shaky breaths",
                "making them move with soft sounds behind their gag",
                "prompting muffled sounds of satisfaction"
            ];
        }
    }

    public function getEquipPrompt() {
        $equipDesc = $this->equipDescriptions[array_rand($this->equipDescriptions)];
        $reactionDesc = $this->getReactionDescriptions(true)[array_rand($this->getReactionDescriptions(true))];
        
        $helplessnessContext = "";
        if (isset($this->deviceContext["helplessness"]) && $this->deviceContext["helplessness"] != "") {
            $helplessnessContext = ", while " . $this->deviceContext["helplessness"];
        }
        
        // Check for other devices that might interact
        $additionalContext = "";
        if (isset($this->deviceContext["hasGag"]) && $this->deviceContext["hasGag"]) {
            $additionalContext .= " The high collar of the straitjacket presses against the straps of their gag, reinforcing their predicament";
        }
        if (isset($this->deviceContext["hasCollar"]) && $this->deviceContext["hasCollar"]) {
            $additionalContext .= $additionalContext ? ", while" : " The high collar of the straitjacket";
            $additionalContext .= " carefully aligns with their existing collar";
        }
        if ($additionalContext) {
            $additionalContext .= ".";
        }
        
        $formattedType = $this->getFormattedType();
        $formattedType = $formattedType ? " ({$formattedType})" : "";
        $promptText = "The {$this->deviceName}{$formattedType} is brought to {$this->target}. ";
        $promptText .= "The device $equipDesc$helplessnessContext, $reactionDesc.";
        if ($additionalContext) {
            $promptText .= $additionalContext;
        }
        
        return "The Narrator: " . $promptText;
    }
    
    public function getUnequipPrompt() {
        $removeDesc = $this->removeDescriptions[array_rand($this->removeDescriptions)];
        $reactionDesc = $this->getReactionDescriptions(false)[array_rand($this->getReactionDescriptions(false))];
        
        $helplessnessContext = "";
        if (isset($this->deviceContext["helplessness"]) && $this->deviceContext["helplessness"] != "") {
            $helplessnessContext = ", while " . $this->deviceContext["helplessness"];
        }
        
        // Check for other devices that might interact
        $additionalContext = "";
        if (isset($this->deviceContext["hasGag"]) && $this->deviceContext["hasGag"]) {
            $additionalContext .= " The pressure against their gag's straps eases";
        }
        if (isset($this->deviceContext["hasCollar"]) && $this->deviceContext["hasCollar"]) {
            $additionalContext .= $additionalContext ? ", and their" : " Their";
            $additionalContext .= " collar becomes more noticeable without the jacket's high neck";
        }
        if ($additionalContext) {
            $additionalContext .= ".";
        }
        
        $promptText = "The {$this->deviceName} $removeDesc from {$this->target}$helplessnessContext, $reactionDesc.";
        if ($additionalContext) {
            $promptText .= $additionalContext;
        }
        
        return "The Narrator: " . $promptText;
    }
}

// Register the events
$handler = new StraitjacketHandler();
RegisterDeviceEvents("straitjacket", $handler); 