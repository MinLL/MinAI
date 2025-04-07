<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class VaginalPiercingHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("piercingsvaginal");
    }
    
    protected $equipDescriptions = [
        "is carefully inserted through their sensitive flesh",
        "pierces through with expert precision",
        "slides through the prepared point with delicate care",
        "is meticulously positioned and secured",
        "passes through with practiced skill"
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
                "causing them to gasp at the intimate sensation",
                "drawing a sharp intake of breath",
                "making them tense at the initial touch",
                "prompting them to hold perfectly still",
                "eliciting a quiet sound of surprise"
            ];
        } else {
            return [
                "bringing visible relief to their expression",
                "allowing them to relax as sensitivity fades",
                "causing them to exhale slowly",
                "drawing a soft sigh as pressure releases",
                "leaving the area tingling slightly"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to squirm at the intimate contact",
                "drawing a breathy moan of mixed sensation",
                "making their breath catch as sensitivity peaks",
                "prompting soft sounds of arousal",
                "eliciting a visible shudder of pleasure"
            ];
        } else {
            return [
                "leaving them notably sensitive",
                "drawing a whimper as the metal withdraws",
                "causing them to press their thighs together",
                "making them bite their lip as sensitivity lingers",
                "eliciting mixed sounds of relief and arousal"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "making them moan at the intense stimulation",
                "causing them to tremble with heightened arousal",
                "drawing gasps of pleasure",
                "eliciting urgent sounds of need",
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
                "causing them to cry out with overwhelming pleasure",
                "making them shake with intense arousal",
                "drawing desperate moans of need",
                "eliciting uncontrolled sounds of desire",
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
    function getFormattedType() {
        return "Clit Piercing";
    }

    public function getEquipPrompt() {
        $equipDesc = $this->equipDescriptions[array_rand($this->equipDescriptions)];
        $reactionDesc = $this->getReactionDescriptions(true)[array_rand($this->getReactionDescriptions(true))];
        
        $helplessnessContext = "";
        if (isset($this->deviceContext["helplessness"]) && $this->deviceContext["helplessness"] != "") {
            $helplessnessContext = ", while " . $this->deviceContext["helplessness"];
        }
        
        // Check for belt and plug
        $additionalContext = "";
        if (isset($this->deviceContext["hasChastityBelt"]) && $this->deviceContext["hasChastityBelt"]) {
            $additionalContext .= " The chastity belt is temporarily loosened to allow access, then locked firmly back in place, the shield pressing against the fresh piercing";
        }
        if (isset($this->deviceContext["hasVaginalPlug"]) && $this->deviceContext["hasVaginalPlug"]) {
            $additionalContext .= $additionalContext ? ". The" : " The";
            $additionalContext .= " plug shifts slightly, brushing against the sensitive piercing";
        }
        if ($additionalContext) {
            $additionalContext .= ".";
        }
        
        $promptText = "The {$this->deviceName} is inserted into {$this->target}. ";
        $promptText .= "The device $equipDesc$helplessnessContext, $reactionDesc.";
        if ($additionalContext) {
            $promptText .= $additionalContext;
        }
        
        return "The Narrator: " . $promptText;
    }
}

// Register the events
$handler = new VaginalPiercingHandler();
RegisterDeviceEvents("piercingsvaginal", $handler); 