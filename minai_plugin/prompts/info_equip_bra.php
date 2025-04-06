<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class BraHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("bra");
    }
    
    protected function getFormattedType() {
        return "Chastity Bra";
    }
    
    protected $equipDescriptions = [
        "is methodically secured around their chest",
        "is carefully positioned and locked in place",
        "is firmly fastened over their breasts",
        "clicks shut with a final, decisive sound",
        "is meticulously adjusted before being locked"
    ];
    
    protected $removeDescriptions = [
        "is carefully unlocked and removed",
        "is gently unfastened and taken away",
        "is methodically loosened and removed",
        "clicks open and is lifted away",
        "is released with a metallic click"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift uncomfortably as they adjust to its restrictive presence",
                "drawing a soft gasp as the cold metal makes contact",
                "making them tense slightly as they test its secure fit",
                "prompting them to adjust their posture",
                "eliciting a quiet sound of resignation"
            ];
        } else {
            return [
                "bringing a subtle look of relief to their face",
                "allowing them to relax visibly as the restriction is removed",
                "causing them to stretch carefully, testing their newfound freedom",
                "drawing a soft sigh as they adjust to its absence",
                "prompting them to roll their shoulders experimentally"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath catching as they realize how thoroughly it denies access",
                "causing them to squirm slightly as they process their predicament",
                "drawing a soft whimper as they test its unyielding security",
                "making them shift restlessly as they feel its tight embrace",
                "eliciting a mix of anticipation and nervousness"
            ];
        } else {
            return [
                "their expression mixing relief with lingering arousal",
                "causing them to press their arms against their chest",
                "drawing a shaky breath as they feel suddenly exposed",
                "making them squirm slightly as cool air touches their skin",
                "prompting a complex mix of emotions to cross their face"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to moan softly as they realize how completely they're denied",
                "drawing a whimper of frustrated need",
                "making them squirm with obvious arousal",
                "their breathing becoming uneven as they process their predicament",
                "eliciting a needy sound as they test its security"
            ];
        } else {
            return [
                "drawing a shaky moan as they feel suddenly vulnerable",
                "causing them to cover themselves needfully",
                "their aroused state evident in their quick, shallow breathing",
                "making them tremble slightly with pent-up desire",
                "prompting soft sounds of mixed relief and frustrated need"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing desperate whimpers as they realize their complete denial",
                "causing them to writhe with obvious need",
                "their ragged breathing betraying their intense arousal",
                "making them tremble with desperate desire",
                "eliciting pleading sounds as they test its security"
            ];
        } else {
            return [
                "causing them to moan deeply with sudden freedom",
                "drawing desperate sounds as cool air touches their sensitive skin",
                "making them shake with barely-contained desire",
                "their intense arousal evident in their quick, shallow breaths",
                "prompting them to arch their back needfully"
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
        
        // Check for piercings and clamps
        $additionalContext = "";
        if (isset($this->deviceContext["hasNipplePiercing"]) && $this->deviceContext["hasNipplePiercing"]) {
            $additionalContext .= " The metal shield presses firmly against their nipple piercings, the contact heightening their sensitivity";
        }
        if (isset($this->deviceContext["hasClamps"]) && $this->deviceContext["hasClamps"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " bra's pressure increases the bite of the clamps, intensifying their grip";
        }
        if ($additionalContext) {
            $additionalContext .= ".";
        }
        
        $formattedType = $this->getFormattedType();
        $formattedType = $formattedType ? " ({$formattedType})" : "";
        $promptText = "The {$this->deviceName}{$formattedType} is locked onto {$this->target}. ";
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
        
        // Check for piercings and clamps
        $additionalContext = "";
        if (isset($this->deviceContext["hasNipplePiercing"]) && $this->deviceContext["hasNipplePiercing"]) {
            $additionalContext .= " Their nipple piercings tingle as the metal shield pulls away";
        }
        if (isset($this->deviceContext["hasClamps"]) && $this->deviceContext["hasClamps"]) {
            $additionalContext .= $additionalContext ? ", and the" : " The";
            $additionalContext .= " clamps' bite lessens as the bra's pressure releases";
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
$handler = new BraHandler();
RegisterDeviceEvents("bra", $handler); 