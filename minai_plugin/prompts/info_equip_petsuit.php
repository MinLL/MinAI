<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class PetSuitHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("petsuit");
    }
    
    protected function getFormattedType() {
        return "Pet Suit";
    }
    
    protected $equipDescriptions = [
        "is carefully fitted over their body",
        "is methodically secured around their form",
        "is snugly fastened to their frame",
        "envelops their body in its confining embrace",
        "is meticulously adjusted before being sealed"
    ];
    
    protected $removeDescriptions = [
        "is carefully peeled away",
        "is methodically unfastened and removed",
        "is gently loosened and taken off",
        "releases its grip on their form",
        "is systematically removed piece by piece"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift as they adjust to its snug fit",
                "drawing a soft gasp as the material hugs their skin",
                "making them test their limited range of movement",
                "prompting them to flex experimentally in their new confines",
                "eliciting a quiet sound as they feel its restrictive nature"
            ];
        } else {
            return [
                "bringing visible relief as they regain freedom of movement",
                "allowing them to stretch their newly freed limbs",
                "causing them to roll their shoulders in relief",
                "drawing a soft sigh as they feel the air on their skin",
                "prompting them to test their restored mobility"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as the suit molds to their form",
                "causing them to squirm as they feel its intimate embrace",
                "drawing soft sounds as they test its restrictive nature",
                "making them shift restlessly in their new confinement",
                "eliciting a mix of anticipation and submission"
            ];
        } else {
            return [
                "their skin flushed and sensitive from its embrace",
                "causing them to shiver as cool air touches their form",
                "drawing shaky breaths as they regain their freedom",
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
            $additionalContext .= " The suit's tight material presses firmly against the vaginal plug, intensifying its presence";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", while" : " The suit";
            $additionalContext .= " ensures the anal plug is held securely in place";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " snug material teases their clit piercing with each movement";
        }
        if (isset($this->deviceContext["hasNipplePiercings"]) && $this->deviceContext["hasNipplePiercings"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " nipple piercings are teased by the pet suit's snug embrace";
        }
        if (isset($this->deviceContext["hasNippleClamps"]) && $this->deviceContext["hasNippleClamps"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " suit's tight fit presses the nipple clamps more firmly against their sensitive flesh";
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
            $additionalContext .= " The pressure around their vaginal plug gradually subsides";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", and" : " The";
            $additionalContext .= " anal plug's presence becomes less intense as the suit releases";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " intimate piercing tingles as the tight material finally pulls away";
        }
        if (isset($this->deviceContext["hasNipplePiercings"]) && $this->deviceContext["hasNipplePiercings"]) {
            $additionalContext .= $additionalContext ? ", while their" : " Their";
            $additionalContext .= " nipple piercings are released from the pet suit's confining pressure";
        }
        if (isset($this->deviceContext["hasNippleClamps"]) && $this->deviceContext["hasNippleClamps"]) {
            $additionalContext .= $additionalContext ? ", and their" : " Their";
            $additionalContext .= " nipple clamps return to their original intensity as the suit's pressure eases";
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
$handler = new PetSuitHandler();
RegisterDeviceEvents("petsuit", $handler); 