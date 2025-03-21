<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class VaginalPlugHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("plugvaginal");
    }
    
    protected function getFormattedType() {
        return "Vaginal Plug";
    }
    
    protected $equipDescriptions = [
        "is carefully inserted and secured in place",
        "slides into position and locks firmly",
        "is methodically worked into place",
        "is gently pushed in and locked securely",
        "settles into place with a final click"
    ];
    
    protected $removeDescriptions = [
        "is carefully extracted and removed",
        "slides free with gentle care",
        "is methodically withdrawn",
        "is released and eased out",
        "comes loose with a soft click"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift uncomfortably at the intrusive sensation",
                "drawing a soft gasp as they adjust to its presence",
                "making them tense slightly at the unfamiliar fullness",
                "prompting a quiet intake of breath",
                "eliciting a small shiver as it settles into place"
            ];
        } else {
            return [
                "bringing a subtle look of relief to their face",
                "allowing them to relax as the sensation fades",
                "causing them to exhale slowly as it withdraws",
                "drawing a soft sigh of relief",
                "leaving them feeling oddly empty"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to squirm at the pleasurable intrusion",
                "drawing a breathy moan as it fills them",
                "making their breath catch as they feel it settle",
                "prompting them to press their thighs together",
                "eliciting a visible shudder of arousal"
            ];
        } else {
            return [
                "leaving them squirming with lingering arousal",
                "drawing a whimper as the stimulation ends",
                "causing them to press their thighs together needfully",
                "making them bite their lip as it withdraws",
                "eliciting a soft moan of loss"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "making them moan deeply at the intense sensation",
                "causing them to tremble with obvious need",
                "drawing gasps of pleasure as it fills them",
                "eliciting desperate sounds of arousal",
                "making them writhe with barely contained desire"
            ];
        } else {
            return [
                "leaving them whimpering with desperate need",
                "drawing moans of frustrated arousal",
                "causing them to squirm with unfulfilled desire",
                "making them pant with lingering excitement",
                "eliciting needy sounds as the sensation fades"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to cry out with overwhelming sensation",
                "making them shake with intense arousal",
                "drawing desperate moans of pleasure",
                "eliciting uncontrolled sounds of need",
                "leaving them trembling and gasping"
            ];
        } else {
            return [
                "leaving them desperately aroused and wanting",
                "drawing urgent whimpers of need",
                "causing them to beg for more stimulation",
                "making them writhe with desperate desire",
                "eliciting pleading sounds of frustration"
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
        
        // Check for belt and piercings
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBelt"]) && $this->deviceContext["hasChastityBelt"]) {
            $additionalContext .= " The chastity belt is temporarily loosened to allow insertion, then locked firmly back in place, pressing the plug deeper";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " plug brushes teasingly against their clit piercing";
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
        
        // Check for belt and piercings
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBelt"]) && $this->deviceContext["hasChastityBelt"]) {
            $additionalContext .= " The chastity belt is temporarily loosened to allow removal, then locked firmly back in place";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " clit piercing tingles from the lingering contact";
        }
        if ($additionalContext) {
            $additionalContext .= ".";
        }
        
        $formattedType = $this->getFormattedType();
        $formattedType = $formattedType ? " ({$formattedType})" : "";
        $promptText = "The {$this->deviceName}{$formattedType} $removeDesc from {$this->target}$helplessnessContext, $reactionDesc.";
        if ($additionalContext) {
            $promptText .= $additionalContext;
        }
        
        return "The Narrator: " . $promptText;
    }
}

// Register the events
$handler = new VaginalPlugHandler();
RegisterDeviceEvents("plugvaginal", $handler); 