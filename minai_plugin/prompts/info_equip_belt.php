<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class BeltHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("belt");
    }
    
    protected function getFormattedType() {
        return "Chastity Belt";
    }
    
    protected $equipDescriptions = [
        "is methodically secured around their hips",
        "is carefully positioned and locked in place",
        "is firmly fastened around their waist",
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
                "prompting them to shift their hips experimentally",
                "eliciting a quiet sound of resignation as it locks into place"
            ];
        } else {
            return [
                "bringing a subtle look of relief to their face",
                "allowing them to relax visibly as the restriction is removed",
                "causing them to stretch carefully, testing their newfound freedom",
                "drawing a soft sigh as they adjust to its absence",
                "prompting them to shift their hips experimentally"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift uncomfortably with muffled sounds of adjustment",
                "drawing stifled sounds as the cold metal makes contact",
                "making them tense slightly with a muffled grunt",
                "prompting them to shift their hips with a quiet, gagged sound",
                "eliciting a muted sound of resignation as it locks into place"
            ];
        } else {
            return [
                "their gagged expression showing visible relief",
                "allowing them to relax visibly with a muffled sigh",
                "causing them to stretch carefully with stifled sounds",
                "drawing muted sounds of relief as they adjust to its absence",
                "prompting soft sounds through their gag as they test their freedom"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath catching as they realize how thoroughly it denies access",
                "causing them to squirm slightly as they process their new predicament",
                "drawing a soft whimper as they test its unyielding security",
                "making them shift restlessly, already feeling its effects",
                "eliciting a mix of anticipation and nervousness in their expression"
            ];
        } else {
            return [
                "their expression mixing relief with lingering arousal",
                "causing them to press their thighs together as they adjust",
                "drawing a shaky breath as they feel suddenly exposed",
                "making them squirm slightly as cool air touches their skin",
                "prompting a complex mix of emotions to cross their face"
            ];
        }
    }
    
    protected function getMediumArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their muffled sounds betraying growing arousal as they test its security",
                "causing them to squirm with stifled whimpers",
                "drawing urgent sounds through their gag as they realize their predicament",
                "making them shift restlessly with muted sounds of protest",
                "eliciting muffled sounds of mixed anticipation and nervousness"
            ];
        } else {
            return [
                "their gagged expression mixing relief with obvious arousal",
                "causing them to press their thighs together with muffled sounds",
                "drawing stifled, shaky breaths as they feel suddenly exposed",
                "making them squirm with soft sounds behind their gag",
                "prompting muffled sounds of conflicted emotion"
            ];
        }
    }

    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to moan softly as they realize how completely they're denied",
                "drawing a whimper of frustrated need as they test its uncompromising security",
                "making them squirm with obvious arousal as they feel its tight control",
                "their breathing becoming uneven as they process their enforced chastity",
                "eliciting a needy sound as they realize their predicament"
            ];
        } else {
            return [
                "drawing a shaky moan as they feel suddenly vulnerable and exposed",
                "causing them to press their thighs together needfully",
                "their aroused state evident in their quick, shallow breathing",
                "making them tremble slightly with pent-up desire",
                "prompting soft sounds of mixed relief and frustrated need"
            ];
        }
    }
    
    protected function getHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing muffled moans as they realize how completely they're denied",
                "drawing stifled whimpers of frustrated need as they test its security",
                "making them squirm with obvious arousal, their sounds trapped behind their gag",
                "their gagged breaths becoming uneven as they process their predicament",
                "eliciting urgent sounds as they realize their complete denial"
            ];
        } else {
            return [
                "drawing muffled moans as they feel suddenly vulnerable",
                "causing them to press their thighs together with stifled sounds of need",
                "their aroused state evident in their quick, muffled breathing",
                "making them tremble with pent-up desire, soft sounds escaping their gag",
                "prompting urgent sounds of mixed relief and frustrated need"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing desperate whimpers as they realize their complete denial",
                "causing them to writhe with obvious need as it secures them in chastity",
                "their ragged breathing betraying their intense arousal as it locks shut",
                "making them tremble with desperate desire as they test its security",
                "eliciting pleading sounds as they process their predicament"
            ];
        } else {
            return [
                "causing them to moan deeply with sudden freedom and intense need",
                "drawing desperate sounds as cool air touches their sensitive skin",
                "making them shake with barely-contained desire",
                "their intense arousal evident in their quick, shallow breaths",
                "prompting them to press their thighs together desperately"
            ];
        }
    }
    
    protected function getVeryHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "drawing urgent, muffled sounds of desperation as they realize their denial",
                "causing them to writhe needfully, their moans stifled by their gag",
                "their ragged breathing through their nose betraying their intense arousal",
                "making them tremble with desperate desire, pleading sounds escaping their gag",
                "eliciting frantic muffled noises as they process their predicament"
            ];
        } else {
            return [
                "causing them to make urgent sounds of need behind their gag",
                "drawing desperate muffled moans as they feel suddenly exposed",
                "making them shake with barely-contained desire, stifled sounds escaping",
                "their intense arousal evident in their quick, shallow breaths",
                "prompting muffled whimpers of desperate need"
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
            $additionalContext .= " The belt presses firmly against the base of the vaginal plug, ensuring it cannot be removed";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", and" : " The belt presses firmly against";
            $additionalContext .= " the base of the anal plug, locking it in place";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " shield of the belt carefully aligns with their clit piercing, the metal cool against their sensitive flesh";
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
            $additionalContext .= " The vaginal plug shifts slightly as pressure from the belt releases";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", and" : " The";
            $additionalContext .= " anal plug moves minutely as the belt's pressure eases";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " clit piercing tingles as the belt's shield pulls away";
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
$handler = new BeltHandler();
RegisterDeviceEvents("belt", $handler); 