<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class NipplePiercingHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("piercingsnipple");
    }
    
    protected function getFormattedType() {
        return "Nipple Piercings";
    }
    
    protected $equipDescriptions = [
        "is carefully threaded through their sensitive flesh",
        "pierces through with practiced precision",
        "slides through the prepared points with care",
        "is meticulously inserted and secured",
        "passes through with expert attention"
    ];
    
    protected $removeDescriptions = [
        "is carefully withdrawn and removed",
        "slides free with gentle attention",
        "is methodically extracted",
        "is released with delicate care",
        "comes loose with practiced precision"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to inhale sharply at the sensation",
                "drawing a soft gasp of surprise",
                "making them tense at the initial sting",
                "prompting them to hold their breath briefly",
                "eliciting a quiet sound of discomfort"
            ];
        } else {
            return [
                "bringing relief to their expression",
                "allowing them to relax as the sensitivity fades",
                "causing them to exhale slowly",
                "drawing a soft sigh as pressure releases",
                "leaving their flesh tingling slightly"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to squirm at the intense sensation",
                "drawing a breathy moan of mixed pain and pleasure",
                "making their breath catch as sensitivity heightens",
                "prompting soft sounds of arousal",
                "eliciting a visible shudder"
            ];
        } else {
            return [
                "leaving their nipples notably sensitive",
                "drawing a whimper as the metal withdraws",
                "causing them to press their arms against their chest",
                "making them bite their lip as sensitivity lingers",
                "eliciting mixed sounds of relief and arousal"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "making them moan at the intense stimulation",
                "causing them to tremble with heightened sensitivity",
                "drawing gasps of aroused response",
                "eliciting urgent sounds of pleasure",
                "making them writhe as sensation builds"
            ];
        } else {
            return [
                "leaving them quivering with arousal",
                "drawing whimpers of need as sensitivity peaks",
                "causing them to squirm with lingering sensation",
                "making them pant softly as metal withdraws",
                "eliciting needy sounds as stimulation fades"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to cry out with overwhelming sensation",
                "making them shake with intense stimulation",
                "drawing desperate moans of pleasure",
                "eliciting uncontrolled sounds of arousal",
                "leaving them trembling and gasping"
            ];
        } else {
            return [
                "leaving them desperately aroused and sensitive",
                "drawing urgent whimpers of need",
                "causing them to beg for more stimulation",
                "making them writhe with desperate arousal",
                "eliciting pleading sounds of desire"
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
        
        // Check for bra
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBra"]) && $this->deviceContext["hasChastityBra"]) {
            $additionalContext .= " The chastity bra is temporarily loosened to allow access, then locked firmly back in place, the metal pressing against the fresh piercings";
        }
        if ($additionalContext) {
            $additionalContext .= ".";
        }
        
        $formattedType = $this->getFormattedType();
        $formattedType = $formattedType ? " ({$formattedType})" : "";
        $promptText = "The {$this->deviceName}{$formattedType} is inserted into {$this->target}. ";
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
        
        // Check for bra
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBra"]) && $this->deviceContext["hasChastityBra"]) {
            $additionalContext .= " The chastity bra is temporarily loosened to allow access, then locked firmly back in place";
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
$handler = new NipplePiercingHandler();
RegisterDeviceEvents("piercingsnipple", $handler); 