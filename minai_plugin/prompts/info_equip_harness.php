<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class HarnessHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("harness");
    }
    
    protected function getFormattedType() {
        return "Body Harness";
    }
    
    protected $equipDescriptions = [
        "is methodically strapped around their body",
        "is carefully adjusted and secured",
        "is fitted snugly into position",
        "wraps firmly around their form",
        "is meticulously fastened in place"
    ];
    
    protected $removeDescriptions = [
        "is carefully unbuckled and removed",
        "is methodically loosened and taken off",
        "releases its secure hold",
        "is gently unfastened",
        "is systematically detached"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to adjust to the straps' embrace",
                "drawing a soft gasp as the bindings tighten",
                "making them test the harness's hold",
                "prompting them to shift as it settles into place",
                "eliciting a quiet sound as they feel its grip"
            ];
        } else {
            return [
                "bringing relief as the pressure releases",
                "allowing them to move more freely",
                "causing them to stretch their freed body",
                "drawing a soft sigh as the straps loosen",
                "prompting them to test their restored mobility"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath quickening as the straps tighten",
                "causing them to squirm as they feel its firm hold",
                "drawing soft sounds as they test its grip",
                "making them shift restlessly in its embrace",
                "eliciting a mix of anticipation and vulnerability"
            ];
        } else {
            return [
                "their skin sensitive where the straps pressed",
                "causing them to shiver as pressure releases",
                "drawing shaky breaths as they adjust",
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
        
        // Check for piercings and plugs
        $additionalContext = "";
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= " The harness straps press firmly against the vaginal plug, securing it deeply";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", while" : " The straps";
            $additionalContext .= " ensuring the anal plug remains firmly seated";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " harness straps brush against their clit piercing with each movement";
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
        
        // Check for piercings and plugs
        $additionalContext = "";
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= " The vaginal plug shifts as the harness's pressure releases";
        }
        if (isset($this->deviceContext["hasAnalPlug"]) && $this->deviceContext["hasAnalPlug"]) {
            $additionalContext .= $additionalContext ? ", and" : " The";
            $additionalContext .= " anal plug moves slightly as the straps loosen";
        }
        if (isset($this->deviceContext["hasVaginalPiercing"]) && $this->deviceContext["hasVaginalPiercing"]) {
            $additionalContext .= $additionalContext ? ". Their" : " Their";
            $additionalContext .= " clit piercing tingles as the harness straps pull away";
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
$handler = new HarnessHandler();
RegisterDeviceEvents("harness", $handler); 