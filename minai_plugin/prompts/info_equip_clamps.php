<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class ClampsHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("clamps");
    }
    
    protected function getFormattedType() {
        return "Clamps";
    }
    
    protected $equipDescriptions = [
        "is carefully positioned and tightened",
        "closes with precise pressure",
        "grips firmly into place",
        "pinches securely with measured force",
        "clamps down with deliberate pressure"
    ];
    
    protected $removeDescriptions = [
        "is carefully loosened and removed",
        "releases its grip gradually",
        "is gently opened and taken away",
        "eases its pressure slowly",
        "lets go with careful attention"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to gasp at the sharp sensation",
                "drawing a hiss of discomfort",
                "making them tense at the initial bite",
                "prompting them to breathe carefully",
                "eliciting a quiet sound of pain"
            ];
        } else {
            return [
                "bringing immediate relief to their expression",
                "allowing them to relax as the pressure fades",
                "causing them to exhale shakily",
                "drawing a soft sigh as blood flow returns",
                "leaving their flesh throbbing gently"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to squirm at the intense pressure",
                "drawing a moan of mixed pain and pleasure",
                "making their breath catch as sensation builds",
                "prompting soft sounds of arousal",
                "eliciting a visible shudder"
            ];
        } else {
            return [
                "leaving their nipples achingly sensitive",
                "drawing a whimper as blood rushes back",
                "causing them to arch slightly",
                "making them bite their lip as feeling returns",
                "eliciting mixed sounds of relief and need"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "making them moan deeply at the sharp pleasure",
                "causing them to tremble with intense sensation",
                "drawing gasps of aroused response",
                "eliciting urgent sounds of need",
                "making them writhe as pressure builds"
            ];
        } else {
            return [
                "leaving them quivering with arousal",
                "drawing whimpers of need as sensitivity peaks",
                "causing them to squirm with renewed sensation",
                "making them pant as feeling floods back",
                "eliciting needy sounds as pressure releases"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to cry out with overwhelming sensation",
                "making them shake with intense stimulation",
                "drawing desperate moans of pleasure",
                "eliciting uncontrolled sounds of need",
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
        
        // Check for bra and piercings
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBra"]) && $this->deviceContext["hasChastityBra"]) {
            $additionalContext .= " The chastity bra is temporarily loosened to allow access, then locked firmly back in place, pressing the clamps harder";
        }
        if (isset($this->deviceContext["hasNipplePiercing"]) && $this->deviceContext["hasNipplePiercing"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " clamps grip carefully around their nipple piercings, the metal-on-metal contact adding to the sensation";
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
        
        // Check for bra and piercings
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBra"]) && $this->deviceContext["hasChastityBra"]) {
            $additionalContext .= " The chastity bra's pressure eases as the clamps are removed";
        }
        if (isset($this->deviceContext["hasNipplePiercing"]) && $this->deviceContext["hasNipplePiercing"]) {
            $additionalContext .= $additionalContext ? ", and their" : " Their";
            $additionalContext .= " nipple piercings remain sensitive from the metal-on-metal contact";
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
$handler = new ClampsHandler();
RegisterDeviceEvents("clamps", $handler); 