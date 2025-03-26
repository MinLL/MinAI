<?php

// Base class for device equip/unequip events
class DeviceEventHandler {
    protected $cleanedMessage;
    protected $deviceName;
    protected $target;
    protected $deviceContext;
    protected $arousal;
    protected $arousalIntensity;
    protected $deviceType;
    
    // Device-specific descriptions that should be overridden by child classes
    protected $equipDescriptions = [
        "is carefully secured",
        "is firmly locked",
        "is methodically fastened",
        "is deliberately attached"
    ];
    
    protected $removeDescriptions = [
        "is carefully unlocked",
        "is gently removed",
        "is methodically unfastened",
        "is deliberately detached"
    ];
    
    public function __construct($deviceType) {
        $this->deviceType = $deviceType;
        $this->cleanedMessage = GetCleanedMessage();
        
        // Extract device name from game request
        $gameRequest = $GLOBALS["gameRequest"][3];
        if (preg_match('/(?:Equipped|Removed) Device: (.*?) (?:on|from)/', $gameRequest, $matches)) {
            $this->deviceName = $matches[1];
        } else {
            $this->deviceName = "device"; // Fallback
        }
        
        $this->target = $GLOBALS["target"];
        $this->deviceContext = GetDeviceContext($this->target);
        $this->arousal = isset($this->deviceContext["arousal"]) ? $this->deviceContext["arousal"] : 50;
        $this->arousalIntensity = GetReactionIntensity($this->arousal);
    }
    
    // Get reaction descriptions based on arousal and device type
    protected function getReactionDescriptions($isEquip = true) {
        $reactions = [];
        $hasGag = $this->deviceContext["hasGag"];
        
        switch ($this->arousalIntensity) {
            case "low":
                if ($hasGag) {
                    $reactions = $this->getLowArousalGaggedReactions($isEquip);
                } else {
                    $reactions = $this->getLowArousalReactions($isEquip);
                }
                break;
            case "medium":
                if ($hasGag) {
                    $reactions = $this->getMediumArousalGaggedReactions($isEquip);
                } else {
                    $reactions = $this->getMediumArousalReactions($isEquip);
                }
                break;
            case "high":
                if ($hasGag) {
                    $reactions = $this->getHighArousalGaggedReactions($isEquip);
                } else {
                    $reactions = $this->getHighArousalReactions($isEquip);
                }
                break;
            default: // very high
                if ($hasGag) {
                    $reactions = $this->getVeryHighArousalGaggedReactions($isEquip);
                } else {
                    $reactions = $this->getVeryHighArousalReactions($isEquip);
                }
                break;
        }
        
        return $reactions;
    }
    
    // These methods should be overridden by device-specific classes
    protected function getLowArousalReactions($isEquip) { return []; }
    protected function getLowArousalGaggedReactions($isEquip) { return []; }
    protected function getMediumArousalReactions($isEquip) { return []; }
    protected function getMediumArousalGaggedReactions($isEquip) { return []; }
    protected function getHighArousalReactions($isEquip) { return []; }
    protected function getHighArousalGaggedReactions($isEquip) { return []; }
    protected function getVeryHighArousalReactions($isEquip) { return []; }
    protected function getVeryHighArousalGaggedReactions($isEquip) { return []; }
    
    public function getEquipPrompt() {
        $equipDesc = $this->equipDescriptions[array_rand($this->equipDescriptions)];
        $reactionDesc = $this->getReactionDescriptions(true)[array_rand($this->getReactionDescriptions(true))];
        
        $helplessnessContext = "";
        if (isset($this->deviceContext["helplessness"]) && $this->deviceContext["helplessness"] != "") {
            $helplessnessContext = ", while " . $this->deviceContext["helplessness"];
        }
        
        $promptText = "The {$this->deviceName} is brought to {$this->target}. ";
        $promptText .= "The device $equipDesc$helplessnessContext, $reactionDesc.";
        
        return "The Narrator: " . $promptText;
    }
    
    public function getUnequipPrompt() {
        $removeDesc = $this->removeDescriptions[array_rand($this->removeDescriptions)];
        $reactionDesc = $this->getReactionDescriptions(false)[array_rand($this->getReactionDescriptions(false))];
        
        $helplessnessContext = "";
        if (isset($this->deviceContext["helplessness"]) && $this->deviceContext["helplessness"] != "") {
            $helplessnessContext = ", while " . $this->deviceContext["helplessness"];
        }
        
        $promptText = "The {$this->deviceName} $removeDesc from {$this->target}$helplessnessContext, $reactionDesc.";
        
        return "The Narrator: " . $promptText;
    }
}

// Function to register device events
function RegisterDeviceEvents($deviceType, $handler) {
    if ($GLOBALS["gameRequest"][0] == "minai_equip_" . $deviceType) {
        $prompt = $handler->getEquipPrompt();
        $GLOBALS["PROMPTS"]["minai_equip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
        // register info variant
        $GLOBALS["PROMPTS"]["info_equip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
        // register info variant
        $GLOBALS["PROMPTS"]["info_minai_equip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
    } elseif ($GLOBALS["gameRequest"][0] == "minai_unequip_" . $deviceType) {
        $prompt = $handler->getUnequipPrompt();
        $GLOBALS["PROMPTS"]["minai_unequip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
        // register info variant
        $GLOBALS["PROMPTS"]["info_unequip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
        // register info variant
        $GLOBALS["PROMPTS"]["info_minai_unequip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
    }
} 