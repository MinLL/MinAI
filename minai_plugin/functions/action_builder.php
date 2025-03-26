<?php

/**
 * Action Builder - A common pattern for registering actions in MinAI
 * 
 * This builder provides a unified way to define actions that:
 * - Supports SFW/NSFW flags
 * - Provides enable/disable callbacks
 * - Supports different descriptions based on gender combinations
 * - Minimizes code duplication
 */

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
     * Build and register the action
     * 
     * @return void
     */
    public function register() {
        global $GLOBALS;

        // Skip NSFW actions if disabled in config
        if ($this->isNSFW && $GLOBALS["disable_nsfw"]) {
            return;
        }

        // Check enable callback
        if ($this->enableCallback && !call_user_func($this->enableCallback)) {
            return;
        }

        // Set display name in global array
        $GLOBALS["F_NAMES"][$this->actionName] = $this->displayName;

        // Determine the correct description based on gender if available
        $description = $this->description;
        
        $speakerGender = IsFemale($GLOBALS["HERIKA_NAME"]) ? 'female' : 'male';
        $targetGender = IsFemale(GetTargetActor()) ? 'female' : 'male';
        
        $genderKey = "$speakerGender-$targetGender";
        if (isset($this->genderDescriptions[$genderKey])) {
            $description = $this->genderDescriptions[$genderKey];
        }
        
        $description = ExpandPromptVariables($description);
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