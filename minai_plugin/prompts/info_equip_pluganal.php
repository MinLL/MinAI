<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class AnalPlugHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("pluganal");
    }
    
    protected function getFormattedType() {
        return "Anal Plug";
    }
    
    protected $equipDescriptions = [
        "is carefully worked into position",
        "slides into place with firm pressure",
        "is methodically inserted and secured",
        "is eased into position with gentle persistence",
        "settles into place with deliberate care"
    ];
    
    protected $removeDescriptions = [
        "is carefully extracted and removed",
        "slides free with gentle attention",
        "is methodically withdrawn",
        "is eased out with careful movements",
        "comes loose with practiced care"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to tense at the unfamiliar intrusion",
                "drawing a sharp intake of breath as they adjust",
                "making them shift uncomfortably at the sensation",
                "prompting them to breathe deeply and relax",
                "eliciting a quiet sound of discomfort"
            ];
        } else {
            return [
                "bringing visible relief to their expression",
                "allowing them to relax as the pressure fades",
                "causing them to exhale slowly in relief",
                "drawing a soft sigh as tension releases",
                "prompting them to shift experimentally"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to squirm at the intense sensation",
                "drawing a muffled moan as it fills them",
                "making their breath catch in their throat",
                "prompting soft sounds of mixed sensation",
                "eliciting a visible shudder"
            ];
        } else {
            return [
                "leaving them with lingering sensations",
                "drawing a whimper as it withdraws",
                "causing them to shift with residual feeling",
                "making them bite their lip as pressure eases",
                "eliciting a mix of relief and loss"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "making them gasp at the intense fullness",
                "causing them to tremble with overwhelming sensation",
                "drawing moans they can't quite suppress",
                "eliciting urgent sounds of arousal",
                "making them writhe as it settles deep"
            ];
        } else {
            return [
                "leaving them quivering with residual sensation",
                "drawing whimpers of mixed relief and need",
                "causing them to squirm with lingering feelings",
                "making them pant softly as it withdraws",
                "eliciting needy sounds as pressure fades"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to cry out at the overwhelming fullness",
                "making them shake with intense stimulation",
                "drawing desperate sounds of arousal",
                "eliciting uncontrolled moans of pleasure",
                "leaving them trembling and gasping"
            ];
        } else {
            return [
                "leaving them desperately sensitive",
                "drawing urgent whimpers of need",
                "causing them to squirm with intense sensation",
                "making them writhe as it withdraws",
                "eliciting pleading sounds of arousal"
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
        
        // Check for belt
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBelt"]) && $this->deviceContext["hasChastityBelt"]) {
            $additionalContext .= " The chastity belt is temporarily loosened to allow insertion, then locked firmly back in place, pressing the plug deeper";
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
        
        // Check for belt
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBelt"]) && $this->deviceContext["hasChastityBelt"]) {
            $additionalContext .= " The chastity belt is temporarily loosened to allow removal, then locked firmly back in place";
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
$handler = new AnalPlugHandler();
RegisterDeviceEvents("pluganal", $handler); 