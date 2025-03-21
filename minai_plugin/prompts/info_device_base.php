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
        // Only construct if the game request is for an equip/unequip event
        $validPrefixes = ["minai_equip_", "minai_unequip_", "info_equip_", "info_unequip_"];
        $matchFound = false;
        foreach ($validPrefixes as $prefix) {
            if (strpos($GLOBALS["gameRequest"][0], $prefix) !== false) {
                $matchFound = true;
                break;
            }
        }
        if (!$matchFound) {
            return;
        }
        
        $this->deviceType = $deviceType;
        $this->cleanedMessage = GetCleanedMessage();
        
        // Extract device name from game request
        $gameRequest = $GLOBALS["gameRequest"][3];
        if (preg_match('/(?:Equipped|Removed) Device: (.*?) (?:on|from) (.+)$/', $gameRequest, $matches)) {
            $this->deviceName = $matches[1];
            $this->target = $matches[2];
        } else {
            $this->deviceName = "device"; // Fallback
            $this->target = $GLOBALS["target"];
        }
        $this->deviceContext = GetInfoDeviceContext($this->target);
        $this->arousal = isset($this->deviceContext["arousal"]) ? $this->deviceContext["arousal"] : 50;
        $this->arousalIntensity = GetReactionIntensity($this->arousal);
    }
    
    // Get reaction descriptions based on arousal and device type
    protected function getReactionDescriptions($isEquip = true) {
        $reactions = [];
        $hasGag = $this->deviceContext["hasGag"];
        $arousalLevels = ["very_high", "high", "medium", "low"];
        
        // Try each arousal level from highest to lowest until we find reactions
        foreach ($arousalLevels as $level) {
            if ($hasGag) {
                switch ($level) {
                    case "very_high":
                        $reactions = $this->getVeryHighArousalGaggedReactions($isEquip);
                        break;
                    case "high":
                        $reactions = $this->getHighArousalGaggedReactions($isEquip);
                        break;
                    case "medium":
                        $reactions = $this->getMediumArousalGaggedReactions($isEquip);
                        break;
                    case "low":
                        $reactions = $this->getLowArousalGaggedReactions($isEquip);
                        break;
                }
            } else {
                switch ($level) {
                    case "very_high":
                        $reactions = $this->getVeryHighArousalReactions($isEquip);
                        break;
                    case "high":
                        $reactions = $this->getHighArousalReactions($isEquip);
                        break;
                    case "medium":
                        $reactions = $this->getMediumArousalReactions($isEquip);
                        break;
                    case "low":
                        $reactions = $this->getLowArousalReactions($isEquip);
                        break;
                }
            }

            // If we found reactions at this level, use them
            if (!empty($reactions)) {
                break;
            }
        }

        // If still no reactions found for gagged state, try ungagged
        if (empty($reactions) && $hasGag) {
            foreach ($arousalLevels as $level) {
                switch ($level) {
                    case "very_high":
                        $reactions = $this->getVeryHighArousalReactions($isEquip);
                        break;
                    case "high":
                        $reactions = $this->getHighArousalReactions($isEquip);
                        break;
                    case "medium":
                        $reactions = $this->getMediumArousalReactions($isEquip);
                        break;
                    case "low":
                        $reactions = $this->getLowArousalReactions($isEquip);
                        break;
                }
                if (!empty($reactions)) {
                    break;
                }
            }
        }

        // Fallback to a basic reaction if nothing else is available
        if (empty($reactions)) {
            $reactions = ["remains stoic"];
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
        
        $formattedType = $this->getFormattedType();
        $formattedType = $formattedType ? " ({$formattedType})" : "";
        $promptText = "The {$this->deviceName}{$formattedType} is brought to {$this->target}. ";
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
        
        $formattedType = $this->getFormattedType();
        $formattedType = $formattedType ? " ({$formattedType})" : "";
        $promptText = "The {$this->deviceName}{$formattedType} $removeDesc from {$this->target}$helplessnessContext, $reactionDesc.";
        
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
        OverrideGameRequestPrompt($prompt);
    } elseif ($GLOBALS["gameRequest"][0] == "minai_unequip_" . $deviceType) {
        $prompt = $handler->getUnequipPrompt();
        $GLOBALS["PROMPTS"]["minai_unequip_" . $deviceType] = [
            "cue" => [],
            "player_request" => [$prompt]
        ];
        OverrideGameRequestPrompt($prompt);
    }
} 