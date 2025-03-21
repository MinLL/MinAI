<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class SuitHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("suit");
    }
    
    protected function getFormattedType() {
        return "Full Body Suit";
    }
    
    protected $equipDescriptions = [
        "is methodically pulled over their form",
        "is carefully fitted to their body",
        "slides snugly into place",
        "envelops their body in its tight embrace",
        "is meticulously adjusted for a perfect fit"
    ];
    
    protected $removeDescriptions = [
        "is carefully peeled away from their skin",
        "is methodically loosened and removed",
        "slides off their form",
        "releases its hold on their body",
        "is systematically removed"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift as the material clings to their skin",
                "drawing a soft gasp as it conforms to their body",
                "making them test the suit's restrictive nature",
                "prompting them to adjust to its intimate fit",
                "eliciting a quiet sound as they feel its embrace"
            ];
        } else {
            return [
                "bringing relief as their skin is exposed to air",
                "allowing them to move more freely",
                "causing them to stretch their newly freed body",
                "drawing a soft sigh as the pressure releases",
                "prompting them to test their restored mobility"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as the suit hugs every curve",
                "causing them to squirm as they feel its tight embrace",
                "drawing soft sounds as they test its restrictive hold",
                "making them shift restlessly in its intimate grip",
                "eliciting a mix of anticipation and arousal"
            ];
        } else {
            return [
                "their skin flushed and sensitive from its tight embrace",
                "causing them to shiver as cool air touches their form",
                "drawing shaky breaths as pressure releases",
                "making them sway slightly as they readjust",
                "leaving them visibly affected by its removal"
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
        
        // Check for piercings, plugs, and clamps
        $additionalContext = "";
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= " The suit's material presses firmly against the vaginal plug, adding to its presence";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", while" : " The suit";
            $additionalContext .= " applying extra pressure to the anal plug";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " tight material rubs against their clit piercing with every movement";
        }
        if (isset($this->deviceContext["hasNipplePiercings"]) && $this->deviceContext["hasNipplePiercings"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " nipple piercings tingle as the suit's fabric slides over them";
        }
        if (isset($this->deviceContext["hasNippleClamps"]) && $this->deviceContext["hasNippleClamps"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " suit's pressure intensifies the grip of their nipple clamps";
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
        
        // Check for piercings, plugs, and clamps
        $additionalContext = "";
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= " The pressure on their vaginal plug eases";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", along with" : " The pressure on";
            $additionalContext .= " their anal plug";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " clit piercing tingles as the tight material releases";
        }
        if (isset($this->deviceContext["hasNipplePiercings"]) && $this->deviceContext["hasNipplePiercings"]) {
            $additionalContext .= $additionalContext ? ", while their" : " Their";
            $additionalContext .= " nipple piercings are finally freed from the fabric's embrace";
        }
        if (isset($this->deviceContext["hasNippleClamps"]) && $this->deviceContext["hasNippleClamps"]) {
            $additionalContext .= $additionalContext ? ", and the" : " The";
            $additionalContext .= " pressure on their nipple clamps diminishes";
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
$handler = new SuitHandler();
RegisterDeviceEvents("suit", $handler); 