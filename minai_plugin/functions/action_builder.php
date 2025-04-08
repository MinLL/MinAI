<?php

/**
 * Action Builder - A common pattern for registering actions in MinAI
 * 
 * This builder provides a unified way to define actions that:
 * - Supports SFW/NSFW flags
 * - Provides enable/disable callbacks
 * - Supports different descriptions based on gender combinations
 * - Minimizes code duplication
 * - Supports batch registration for performance
 */

// Direct action registration function for optimal performance
// This bypasses the builder pattern for significant speed improvements
function directRegisterAction($actionName, $displayName, $description, $enableCondition, $genderDescriptions = [], $required = [], $customDescription = null) {
    global $GLOBALS;
    
    // Skip registration if the enable condition is false
    if ($enableCondition === false) {
        return;
    }
    
    // Cache gender values once if not already cached
    static $speakerGender = null;
    static $targetGender = null;
    static $genderKey = null;
    static $nearby = null;
    
    if ($speakerGender === null) {
        $speakerGender = $GLOBALS["herika_gender"];
        $targetGender = $GLOBALS["target_gender"];
        $genderKey = "$speakerGender-$targetGender";
        $nearby = isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [];
    }
    
    // Use gender-specific description if available
    $finalDescription = $description;
    if (isset($genderDescriptions[$genderKey])) {
        $finalDescription = $genderDescriptions[$genderKey];
    } elseif ($customDescription !== null) {
        $finalDescription = $customDescription;
    }
    
    // Expand variables in the description
    $finalDescription = ExpandPromptVariables($finalDescription);
    
    // Set up global arrays directly
    $GLOBALS["F_NAMES"][$actionName] = $displayName;
    $GLOBALS["F_TRANSLATIONS"][$actionName] = $finalDescription;
    
    // Add function parameter definition
    $functionParams = [
        "type" => "object",
        "properties" => [
            "target" => [
                "type" => "string",
                "description" => "Target NPC, Actor, or being",
                "enum" => $nearby
            ]
        ]
    ];
    
    // Add function to globals
    $GLOBALS["FUNCTIONS"][] = [
        "name" => $displayName,
        "description" => $finalDescription,
        "parameters" => $functionParams,
    ];
    
    // Set return function
    $GLOBALS["FUNCRET"][$actionName] = $GLOBALS["GenericFuncRet"];
    
    // Register the action
    RegisterAction($actionName);
}

class ActionBuilder {
    private $actionName;
    private $displayName;
    private $description;
    private $parameters = [];
    private $required = [];
    private $isNSFW = false;
    private $enableCallback = null;
    private $genderDescriptions = [];
    private $returnFunction = null;
    
    // Cached values to avoid recalculation
    private static $nearby = null;
    private static $cachedSpeakerGender = null;
    private static $cachedTargetGender = null;
    private static $enabledActionsCache = [];
    private static $batchMode = false;
    private static $batchActions = [];

    /**
     * Create a new action builder
     * 
     * @param string $actionName The name of the action (e.g., ExtCmdStartLooting)
     * @param string $displayName The display name of the action (e.g., StartLooting)
     * @return ActionBuilder
     */
    public static function create($actionName, $displayName = null) {
        return new self($actionName, $displayName);
    }

    /**
     * Constructor
     */
    private function __construct($actionName, $displayName = null) {
        $this->actionName = $actionName;
        $this->displayName = $displayName ?: $actionName;
        
        // Initialize cached values if needed
        if (self::$nearby === null && isset($GLOBALS["nearby"])) {
            self::$nearby = $GLOBALS["nearby"];
        }
    }

    /**
     * Set the description for the action
     * 
     * @param string $description The description text
     * @return ActionBuilder
     */
    public function withDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Add a parameter to the action
     * 
     * @param string $name Parameter name
     * @param string $type Parameter type (string, integer, etc)
     * @param string $description Parameter description
     * @param array $enum Optional enum values
     * @param bool $required Whether the parameter is required
     * @return ActionBuilder
     */
    public function withParameter($name, $type, $description, $enum = [], $required = false) {
        $this->parameters[$name] = [
            'type' => $type,
            'description' => $description,
            'enum' => $enum
        ];

        if ($required) {
            $this->required[] = $name;
        }

        return $this;
    }

    /**
     * Mark the action as NSFW
     * 
     * @param bool $isNSFW Whether the action is NSFW
     * @return ActionBuilder
     */
    public function isNSFW($isNSFW = true) {
        $this->isNSFW = $isNSFW;
        return $this;
    }

    /**
     * Set a callback to determine if the action should be enabled
     * 
     * @param callable $callback Function to call to determine if action should be enabled
     * @return ActionBuilder
     */
    public function withEnableCondition($callback) {
        $this->enableCallback = $callback;
        return $this;
    }

    /**
     * Add a gender-specific description
     * 
     * @param string $speakerGender 'male', 'female', or 'other'
     * @param string $targetGender 'male', 'female', or 'other'
     * @param string $description The description for this gender combination
     * @return ActionBuilder
     */
    public function withGenderDescription($speakerGender, $targetGender, $description) {
        $this->genderDescriptions["$speakerGender-$targetGender"] = $description;
        return $this;
    }

    /**
     * Set the return function for this action
     * 
     * @param mixed $returnFunction The function to return
     * @return ActionBuilder
     */
    public function withReturnFunction($returnFunction) {
        $this->returnFunction = $returnFunction;
        return $this;
    }

    /**
     * Check if an action should be enabled based on its callback
     * 
     * @return bool Whether the action should be enabled
     */
    private function shouldEnable() {
        // Check cache first
        if (isset(self::$enabledActionsCache[$this->actionName])) {
            return self::$enabledActionsCache[$this->actionName];
        }
        
        // Skip NSFW actions if disabled in config
        if ($this->isNSFW && $GLOBALS["disable_nsfw"]) {
            self::$enabledActionsCache[$this->actionName] = false;
            return false;
        }

        // Check enable callback
        if ($this->enableCallback) {
            $result = is_callable($this->enableCallback) ? 
                call_user_func($this->enableCallback) : false;
            self::$enabledActionsCache[$this->actionName] = $result;
            return $result;
        }
        
        // No callback means enabled
        self::$enabledActionsCache[$this->actionName] = true;
        return true;
    }
    
    /**
     * Get gender-specific description
     * 
     * @return string The description text for the current gender combination
     */
    private function getGenderSpecificDescription() {
        // Use cached values if available
        if (self::$cachedSpeakerGender === null) {
            self::$cachedSpeakerGender = $GLOBALS["herika_gender"];
        }
        
        if (self::$cachedTargetGender === null) {
            self::$cachedTargetGender = $GLOBALS["target_gender"];
        }
        
        $genderKey = self::$cachedSpeakerGender . "-" . self::$cachedTargetGender;
        
        if (isset($this->genderDescriptions[$genderKey])) {
            return $this->genderDescriptions[$genderKey];
        }
        
        return $this->description;
    }

    /**
     * Start batch registration mode
     * 
     * @return void
     */
    public static function startBatchRegistration() {
        self::$batchMode = true;
        self::$batchActions = [];
    }
    
    /**
     * Complete batch registration
     * 
     * @return void
     */
    public static function completeBatchRegistration() {
        if (!self::$batchMode || empty(self::$batchActions)) {
            return;
        }
        
        global $GLOBALS;
        
        // Process all batched actions
        foreach (self::$batchActions as $action) {
            // Skip if not enabled
            if (!$action->shouldEnable()) {
                continue;
            }
            
            // Get description with gender specifics
            $description = $action->getGenderSpecificDescription();
            $description = ExpandPromptVariables($description);
            
            // Register all the properties in global arrays
            $GLOBALS["F_NAMES"][$action->actionName] = $action->displayName;
            $GLOBALS["F_TRANSLATIONS"][$action->actionName] = $description;
            
            // Build function parameters
            $functionParams = [
                "type" => "object",
                "properties" => [],
                "required" => $action->required,
            ];
            
            foreach ($action->parameters as $name => $param) {
                $functionParams["properties"][$name] = [
                    "type" => $param["type"],
                    "description" => $param["description"]
                ];
                
                if (!empty($param["enum"])) {
                    $functionParams["properties"][$name]["enum"] = $param["enum"];
                }
            }
            
            // Add function to globals
            $GLOBALS["FUNCTIONS"][] = [
                "name" => $action->displayName,
                "description" => $description,
                "parameters" => $functionParams,
            ];
            
            // Set return function if provided
            if ($action->returnFunction !== null) {
                $GLOBALS["FUNCRET"][$action->actionName] = $action->returnFunction;
            } elseif (isset($GLOBALS["GenericFuncRet"])) {
                $GLOBALS["FUNCRET"][$action->actionName] = $GLOBALS["GenericFuncRet"];
            }
            
            // Actually register the action
            RegisterAction($action->actionName);
        }
        
        // Reset batch mode
        self::$batchMode = false;
        self::$batchActions = [];
    }
    
    /**
     * Clear all caches
     * 
     * @return void
     */
    public static function clearCaches() {
        self::$nearby = isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [];
        self::$cachedSpeakerGender = null;
        self::$cachedTargetGender = null;
        self::$enabledActionsCache = [];
    }

    /**
     * Build and register the action
     * 
     * @return void
     */
    public function register() {
        // Add to batch if in batch mode
        if (self::$batchMode) {
            self::$batchActions[] = $this;
            return;
        }
        
        global $GLOBALS;

        // Skip if not enabled
        if (!$this->shouldEnable()) {
            return;
        }

        // Get description with gender specifics
        $description = $this->getGenderSpecificDescription();
        $description = ExpandPromptVariables($description);
        
        // Register all the properties in global arrays
        $GLOBALS["F_NAMES"][$this->actionName] = $this->displayName;
        $GLOBALS["F_TRANSLATIONS"][$this->actionName] = $description;

        // Build function parameters
        $functionParams = [
            "type" => "object",
            "properties" => [],
            "required" => $this->required,
        ];

        foreach ($this->parameters as $name => $param) {
            $functionParams["properties"][$name] = [
                "type" => $param["type"],
                "description" => $param["description"]
            ];
            
            if (!empty($param["enum"])) {
                $functionParams["properties"][$name]["enum"] = $param["enum"];
            }
        }

        // Add function to globals
        $GLOBALS["FUNCTIONS"][] = [
            "name" => $this->displayName,
            "description" => $description,
            "parameters" => $functionParams,
        ];

        // Set return function if provided
        if ($this->returnFunction !== null) {
            $GLOBALS["FUNCRET"][$this->actionName] = $this->returnFunction;
        } elseif (isset($GLOBALS["GenericFuncRet"])) {
            $GLOBALS["FUNCRET"][$this->actionName] = $GLOBALS["GenericFuncRet"];
        }

        // Actually register the action
        RegisterAction($this->actionName);
    }
}

// Helper function to quickly create and register an action
function registerMinAIAction($actionName, $displayName = null) {
    return ActionBuilder::create($actionName, $displayName);
}

// Helper function to register actions in batch for performance
function registerActionsBatch($setupCallback = null) {
    ActionBuilder::startBatchRegistration();
    
    if (is_callable($setupCallback)) {
        call_user_func($setupCallback);
    }
    
    ActionBuilder::completeBatchRegistration();
}