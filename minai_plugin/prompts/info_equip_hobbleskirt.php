<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class HobbleSkirtHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("hobbleskirt");
    }
    
    protected function getFormattedType() {
        return "Hobble Skirt";
    }
    
    protected $equipDescriptions = [
        "is carefully fitted around their hips",
        "is methodically secured in place",
        "wraps tightly around their lower body",
        "is fastened snugly around their thighs",
        "is meticulously adjusted for proper restriction"
    ];
    
    protected $removeDescriptions = [
        "is carefully unfastened and removed",
        "is methodically loosened and taken off",
        "releases its restrictive hold",
        "is gently worked down their legs",
        "is systematically unfastened"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to test their limited stride",
                "drawing a soft sound as they feel their restricted movement",
                "making them shift carefully within its confines",
                "prompting them to adjust to their limited mobility",
                "eliciting a quiet response as they feel their legs bound"
            ];
        } else {
            return [
                "allowing them to stretch their legs freely",
                "bringing relief as they regain their stride",
                "causing them to test their restored mobility",
                "drawing a soft sigh as they move unhindered",
                "prompting them to take a few experimental steps"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as they feel thoroughly restricted",
                "causing them to shift within their new confines",
                "drawing soft sounds as they test their bonds",
                "making them sway slightly in their limited mobility",
                "eliciting a mix of vulnerability and anticipation"
            ];
        } else {
            return [
                "their legs trembling slightly from the release",
                "causing them to steady themselves carefully",
                "drawing shaky breaths as they regain freedom",
                "making them pause to find their balance",
                "leaving them acutely aware of their restored mobility"
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
        
        // Check for piercings and plugs
        $additionalContext = "";
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= " The tight skirt presses against the base of the vaginal plug with each restricted step";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", while" : " The skirt's movement";
            $additionalContext .= " causes the anal plug to shift subtly as they walk";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " clit piercing is teased by the skirt's restrictive embrace";
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
        
        // Check for piercings and plugs
        $additionalContext = "";
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= " The pressure against their vaginal plug eases";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", and" : " The";
            $additionalContext .= " anal plug's movement settles as the skirt releases";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " clit piercing tingles as the restrictive pressure releases";
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
$handler = new HobbleSkirtHandler();
RegisterDeviceEvents("hobbleskirt", $handler); 