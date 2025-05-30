<?php
/**
 * System Prompt Context Builder
 * 
 * This file contains the main framework for building and managing the system prompt
 * using a modular, configurable approach to context builders.
 */

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../util.php");
require_once(__DIR__ . "/../utils/format_util.php");
require_once(__DIR__ . "/../utils/metrics_util.php");

// Include all context builder modules
require_once(__DIR__ . "/context_modules/core_context.php");
require_once(__DIR__ . "/context_modules/character_context.php");
require_once(__DIR__ . "/context_modules/relationship_context.php");
require_once(__DIR__ . "/context_modules/environmental_context.php");
require_once(__DIR__ . "/context_modules/nsfw_context.php");

/**
 * Context builder registry to store all available context builders
 */
class ContextBuilderRegistry {
    private static $instance = null;
    private $builders = [];
    private $decorators = [];
    private $visitedDecorators = [];
    
    /**
     * Get the singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ContextBuilderRegistry();
        }
        return self::$instance;
    }
    
    /**
     * Reset the singleton instance
     * Use this when you need a completely fresh registry
     */
    public static function resetInstance() {
        self::$instance = null;
    }
    
    /**
     * Register a new context builder
     * 
     * @param string $id Unique identifier for the builder
     * @param array $config Configuration for the builder
     */
    public function register($id, $config) {
        $this->builders[$id] = array_merge([
            'enabled' => true,          // Default to enabled
            'is_nsfw' => false,         // Default to SFW
            'priority' => 100,          // Default priority (lower numbers = higher priority)
            'header' => null,           // Section header displayed to LLM
            'section' => 'misc',        // Section this builder belongs to
            'description' => '',        // Description of what this builder provides
            'builder_callback' => null  // Function to call to build context
        ], $config);
        
        // Check for config override to enable/disable
        $config_var = "MINAI_CONTEXT_ENABLE_" . strtoupper($id);
        if (isset($GLOBALS[$config_var])) {
            $this->builders[$id]['enabled'] = (bool)$GLOBALS[$config_var];
        }
    }
    
    /**
     * Register a decorator function for expanding hashtag objects
     * 
     * @param string $tag Hashtag to register (without # symbols)
     * @param callable $callback Function to call to expand the tag
     */
    public function registerDecorator($tag, $callback) {
        if (is_callable($callback)) {
            $this->decorators[$tag] = $callback;
        } else {
            minai_log("warning", "Attempted to register non-callable decorator for '{$tag}'");
        }
    }
    
    /**
     * Get all registered decorators
     * 
     * @return array Array of registered decorators
     */
    public function getDecorators() {
        return $this->decorators;
    }
    
    /**
     * Get a specific decorator by tag
     * 
     * @param string $tag The tag to get decorator for
     * @return callable|null The decorator callback or null if not found
     */
    public function getDecorator($tag) {
        return isset($this->decorators[$tag]) ? $this->decorators[$tag] : null;
    }
    
    /**
     * Expand a hashtag in text with its decorated value
     * 
     * @param string $tag Tag to expand (without # symbols)
     * @param array $params Parameters to pass to decorator
     * @param int $maxDepth Maximum recursion depth to prevent infinite loops
     * @return string The expanded value or original hashtag if not found
     */
    public function expandDecorator($tag, $params = [], $maxDepth = 10) {
        // Prevent infinite recursion
        if (in_array($tag, $this->visitedDecorators) || $maxDepth <= 0) {
            minai_log("warning", "Possible recursive decoration detected for tag '{$tag}' or max depth reached");
            return "#{$tag}#";
        }
        
        $decorator = $this->getDecorator($tag);
        if ($decorator) {
            // Mark this tag as visited for recursion detection
            $this->visitedDecorators[] = $tag;
            
            try {
                $result = call_user_func($decorator, $params);
                
                // Further expand any hashtags in the result
                $result = $this->expandAllDecorators($result, $params, $maxDepth - 1);
                
                // Remove this tag from visited list when done
                array_pop($this->visitedDecorators);
                
                return $result;
            } catch (Exception $e) {
                minai_log("error", "Error expanding decorator '{$tag}': " . $e->getMessage());
                
                // Remove this tag from visited list on error
                array_pop($this->visitedDecorators);
                
                return "#{$tag}#";
            }
        }
        
        return "#{$tag}#";
    }
    
    /**
     * Expand all hashtags in a text string
     * 
     * @param string $text Text to expand hashtags in
     * @param array $params Parameters to pass to decorators
     * @param int $maxDepth Maximum recursion depth to prevent infinite loops
     * @return string Text with all hashtags expanded
     */
    public function expandAllDecorators($text, $params = [], $maxDepth = 10) {
        if (empty($text) || $maxDepth <= 0) {
            return $text;
        }
        
        // Reset visited decorators if this is the top-level call
        if (empty($this->visitedDecorators)) {
            $this->visitedDecorators = [];
        }
        
        // Find all hashtags in the text
        preg_match_all('/#([a-zA-Z0-9_]+)#/', $text, $matches);
        
        if (empty($matches[1])) {
            return $text;
        }
        
        // Expand each hashtag
        foreach ($matches[1] as $tag) {
            $expanded = $this->expandDecorator($tag, $params, $maxDepth);
            $text = str_replace("#{$tag}#", $expanded, $text);
        }
        
        return $text;
    }
    
    /**
     * Get all registered builders, optionally filtered by section and SFW/NSFW
     * 
     * @param string $section Optional section to filter by
     * @param bool $include_nsfw Whether to include NSFW builders
     * @return array Filtered list of builders
     */
    public function getBuilders($section = null, $include_nsfw = true) {
        $filtered = [];
        
        foreach ($this->builders as $id => $builder) {
            // Skip disabled builders
            if (!$builder['enabled']) {
                continue;
            }
            
            // Skip NSFW builders if not including them
            if ($builder['is_nsfw'] && !$include_nsfw) {
                continue;
            }
            
            // Filter by section if provided
            if ($section !== null && $builder['section'] !== $section) {
                continue;
            }
            
            $filtered[$id] = $builder;
        }
        
        // Sort by priority
        uasort($filtered, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        return $filtered;
    }
    
    /**
     * Get a specific builder by ID
     * 
     * @param string $id The unique identifier of the builder to retrieve
     * @return array|null The builder configuration or null if not found
     */
    public function getBuilder($id) {
        return isset($this->builders[$id]) ? $this->builders[$id] : null;
    }
    
    /**
     * Format context content according to formatting rules
     * 
     * @param string $context The context string to format
     * @return string Formatted context
     */
    public function formatContext($context) {
        return FormatUtil::formatContext($context);
    }
    
    /**
     * Get all hashtags used in the system prompt
     * 
     * @return array List of all unique hashtags used
     */
    public function getAllHashtags() {
        $hashtags = [];
        
        // Build a sample prompt to identify hashtags
        $prompt = $this->buildSamplePrompt();
        
        // Extract all hashtags
        preg_match_all('/#([a-zA-Z0-9_]+)#/', $prompt, $matches);
        
        if (!empty($matches[1])) {
            $hashtags = array_unique($matches[1]);
        }
        
        return $hashtags;
    }
    
    /**
     * Get a mapping of hashtags to their resolved values
     * 
     * @param array $params Parameters to pass to decorators
     * @return array Associative array of hashtags and their resolved values
     */
    public function getHashtagMap($params = []) {
        $hashtags = $this->getAllHashtags();
        $map = [];
        
        foreach ($hashtags as $tag) {
            // Reset visited decorators for each expansion
            $this->visitedDecorators = [];
            
            try {
                $map[$tag] = $this->expandDecorator($tag, $params);
            } catch (Exception $e) {
                $map[$tag] = "Error: " . $e->getMessage();
            }
        }
        
        return $map;
    }
    
    /**
     * Builds a sample system prompt to extract hashtags
     * This is a lightweight version of BuildSystemPrompt without formatting
     * 
     * @return string Sample system prompt content
     */
    private function buildSamplePrompt() {
        $sample = "";
        $params = [
            'herika_name' => isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "Character",
            'player_name' => isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "Player",
            'target' => isset($GLOBALS["target"]) ? $GLOBALS["target"] : "Player",
            'is_self_narrator' => false
        ];
        
        // Call each builder to get content
        foreach ($this->builders as $id => $builder) {
            if ($builder['enabled'] && is_callable($builder['builder_callback'])) {
                try {
                    $content = call_user_func($builder['builder_callback'], $params);
                    if (!empty($content)) {
                        $sample .= $content . "\n";
                    }
                } catch (Exception $e) {
                    // Skip on error
                }
            }
        }
        
        return $sample;
    }
}

/**
 * Builds the complete system prompt using all registered context builders
 * 
 * @return array System prompt content in the format expected for contextDataFull
 */
function BuildSystemPrompt() {
    // Start metrics collection for system prompt builder
    minai_start_timer('system_prompt_builder', 'context_php');
    
    // Access global variables needed for the system prompt
    $prompt_head = isset($GLOBALS["PROMPT_HEAD"]) ? $GLOBALS["PROMPT_HEAD"] : "";
    $herika_name = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "";
    $player_name = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "";
    $target = isset($GLOBALS["target"]) ? $GLOBALS["target"] : 
             (isset($GLOBALS["HERIKA_TARGET"]) ? $GLOBALS["HERIKA_TARGET"] : $player_name);
    
    // Determine if NSFW context should be included
    $include_nsfw = !isset($GLOBALS["disable_nsfw"]) || !$GLOBALS["disable_nsfw"];
    
    // Check for self_narrator mode (Narrator as player's subconscious)
    $is_self_narrator = false;
    if ($herika_name === "The Narrator" && isset($GLOBALS['self_narrator']) && $GLOBALS['self_narrator'] === true) {
        $is_self_narrator = true;
    }
    
    // Check for diary request type
    $is_diary_request = isset($GLOBALS["gameRequest"]) && 
                        is_array($GLOBALS["gameRequest"]) && 
                        !empty($GLOBALS["gameRequest"][0]) &&
                        $GLOBALS["gameRequest"][0] === "diary";
    
    // Set the display name based on self_narrator mode and request type
    $display_name = $herika_name;
    if ($is_self_narrator) {
        if ($is_diary_request) {
            // For diary entries in self-narrator mode, just use "Min" instead of "Min's subconscious"
            $display_name = $player_name;
        } else {
            // Normal self-narrator mode
            $display_name = "{$player_name}'s subconscious";
        }
    }
    
    // Initialize the system prompt
    $system_prompt = "";
    
    // Build the core instruction section
    $system_prompt .= "# Instructions\n";
    if (!empty($prompt_head)) {
        $system_prompt .= trim($prompt_head) . "\n\n";
    }
    $system_prompt .= "You are roleplaying as {$display_name} in a Skyrim adventure.\n";
    $system_prompt .= "Respond in character as {$display_name} at all times.\n\n";
    
    // Get registry instance
    $registry = ContextBuilderRegistry::getInstance();

    // Set up actors based on self_narrator mode
    $actors = array();
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        // Only include player in narrator mode (self narrator or not)
        $actors['primary'] = "The Narrator";
        
    } else {
        // Include both primary and target in normal mode
        $actors['primary'] = $herika_name;
        $actors['target'] = $target;
    }

    // Common parameters to pass to all builders
    $params = array(
        'herika_name' => $herika_name,
        'player_name' => $player_name,
        'target' => $target,
        'is_self_narrator' => $is_self_narrator
    );

    // Define the per-actor sections and their headers
    $actor_sections = array(
        'character' => "Character",
        'status' => "Current Status",
        'interaction' => "Interaction"
    );

    // Define the shared sections
    $shared_sections = array(
        'environment' => "# Environmental Context",
        'misc' => "# Additional Information"
    );

    // Process each actor
    foreach ($actors as $actor_role => $actor_name) {
        // Skip if actor name is empty
        if (empty($actor_name)) {
            continue;
        }
        
        $actor_content = "";
        
        // Set up the actor parameters based on role
        $actor_params = $params;
        if ($actor_role === 'primary') {
            // For primary character in narrator mode, focus on player's variables
            if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
                $actor_params['herika_name'] = "The Narrator";
            }
        } else {
            // For target (usually player), swap roles
            $actor_params['herika_name'] = $target;
            $actor_params['is_target'] = true;
        }
        
        // Process each section for this actor
        foreach ($actor_sections as $section_id => $section_title) {
            // Start timer for this section, with system_prompt_builder as parent
            minai_start_timer('section_build_' . $section_id, 'system_prompt_builder');
            
            $section_content = "";
            $builders = $registry->getBuilders($section_id, $include_nsfw);
            
            foreach ($builders as $id => $builder) {
                // Skip if no builder callback
                if (!is_callable($builder['builder_callback'])) {
                    minai_log("warning", "Builder callback '{$builder['builder_callback']}' is not callable");
                    continue;
                }
                
                // Start timer for this context builder, with section_build_X as parent
                minai_start_timer('context_builder_' . $id, 'section_build_' . $section_id);
                
                try {
                    // Call the builder function with actor-specific params
                    $context = call_user_func($builder['builder_callback'], $actor_params);
                    
                    // Skip if no content returned
                    if (empty($context)) {
                        minai_stop_timer('context_builder_' . $id, [
                            'builder_id' => $id,
                            'section' => $section_id,
                            'actor' => $actor_name,
                            'success' => false,
                            'content_length' => 0
                        ]);
                        continue;
                    }
                    
                    // Format the context according to formatting rules
                    $context = $registry->formatContext($context);
                    
                    // Add the sub-header if provided
                    if (!empty($builder['header'])) {
                        $section_content .= "### " . $builder['header'] . "\n";
                    }
                    
                    // Add the content
                    $section_content .= trim($context) . "\n\n";
                    
                    // Record metrics for this builder
                    minai_stop_timer('context_builder_' . $id, [
                        'builder_id' => $id,
                        'section' => $section_id,
                        'actor' => $actor_name,
                        'success' => true,
                        'content_length' => strlen($context)
                    ]);
                    
                } catch (Exception $e) {
                    minai_log("error", "Error in builder '{$id}' for actor '{$actor_name}': " . $e->getMessage());
                    
                    // Record error metrics
                    minai_stop_timer('context_builder_' . $id, [
                        'builder_id' => $id,
                        'section' => $section_id,
                        'actor' => $actor_name,
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Add the section to the actor content if it has content
            if (!empty($section_content)) {
                $actor_content .= "## " . $section_title . "\n";
                $actor_content .= $section_content;
            }
            
            // Record metrics for section build
            minai_stop_timer('section_build_' . $section_id, [
                'section' => $section_id,
                'actor' => $actor_name,
                'content_length' => strlen($section_content)
            ]);
        }
        
        // Add the actor section to the system prompt if it has content
        if (!empty($actor_content)) {
            // Use appropriate display name for the header
            $header_name = $actor_name;
            if ($is_self_narrator && $actor_role === 'primary') {
                $header_name = $display_name;
            }
            $system_prompt .= "# " . $header_name . "\n";
            $system_prompt .= $actor_content . "\n";
        }
    }
    
    // Add shared sections (environment, misc, etc.)
    foreach ($shared_sections as $section_id => $section_header) {
        // Start timer for this section, with system_prompt_builder as parent
        minai_start_timer('section_build_' . $section_id, 'system_prompt_builder');
        
        $section_content = "";
        $builders = $registry->getBuilders($section_id, $include_nsfw);
        
        foreach ($builders as $id => $builder) {
            // Skip if no builder callback
            if (!is_callable($builder['builder_callback'])) {
                minai_log("warning", "Builder callback '{$builder['builder_callback']}' is not callable");
                continue;
            }
            
            // Start timer for this context builder, with section_build_X as parent
            minai_start_timer('context_builder_' . $id, 'section_build_' . $section_id);
            
            try {
                // Call the builder function with error handling
                $context = call_user_func($builder['builder_callback'], $params);
                
                // Skip if no content returned
                if (empty($context)) {
                    minai_stop_timer('context_builder_' . $id, [
                        'builder_id' => $id,
                        'section' => $section_id,
                        'success' => false,
                        'content_length' => 0
                    ]);
                    continue;
                }
                
                // Format the context according to formatting rules
                $context = $registry->formatContext($context);
                
                // Add the sub-header if provided
                if (!empty($builder['header'])) {
                    $section_content .= "## " . $builder['header'] . "\n";
                }
                
                // Add the content
                $section_content .= trim($context) . "\n\n";
                
                // Record metrics for this builder
                minai_stop_timer('context_builder_' . $id, [
                    'builder_id' => $id,
                    'section' => $section_id,
                    'success' => true,
                    'content_length' => strlen($context)
                ]);
                
            } catch (Exception $e) {
                minai_log("error", "Error in builder '{$id}': " . $e->getMessage());
                
                // Record error metrics
                minai_stop_timer('context_builder_' . $id, [
                    'builder_id' => $id,
                    'section' => $section_id,
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Add the section to the system prompt if it has content
        if (!empty($section_content)) {
            $system_prompt .= $section_header . "\n";
            $system_prompt .= $section_content;
        }
        
        // Record metrics for section build
        minai_stop_timer('section_build_' . $section_id, [
            'section' => $section_id,
            'content_length' => strlen($section_content)
        ]);
    }
    
    // Add guidance for the LLM on how to format responses
    if (!isset($GLOBALS['minai_context']['response_guidelines'])) {
        $GLOBALS['minai_context']['response_guidelines'] = true;
    }
    if ($GLOBALS['minai_context']['response_guidelines']) {
    $system_prompt .= "# Response Guidelines\n";        
        if ($is_diary_request) {
            // Special guidelines for diary entries
            if ($is_self_narrator) {
                $system_prompt .= "- You are writing a diary entry as {$display_name}\n";
                $system_prompt .= "- Write in first person perspective as {$display_name}, recording your personal reflections\n";
            } else {
                $system_prompt .= "- You are writing a diary entry as {$display_name}\n";
                $system_prompt .= "- Write in first person perspective, recording your thoughts and experiences\n";
            }
            $system_prompt .= "- Include personal reflections on recent events and feelings\n";
            $system_prompt .= "- The tone should be introspective and authentic to your character\n";
            $system_prompt .= "- Reference recent experiences, observations, and emotions\n";
        } else {
            // Standard response guidelines
            $system_prompt .= "- Stay in character as {$display_name} at all times\n";
            $system_prompt .= "- Respond appropriately to the context of the conversation\n";
            if ($GLOBALS['enforce_short_responses']) {
                $system_prompt .= "- Be concise and direct in your responses\n";
            }
           
            $system_prompt .= "- Your response should reflect your personality\n";
            $system_prompt .= "- Prioritize responding to the most recent dialogue and events\n";
            $system_prompt .= "- Include variety in your responses, and avoid repeating yourself\n";
        }
    }
    if (!isset($GLOBALS['minai_context']['action_enforcement'])) {
        $GLOBALS['minai_context']['action_enforcement'] = true;
    }
    if ($GLOBALS['minai_context']['action_enforcement']) {
        $GLOBALS["COMMAND_PROMPT"] = ""; # Kill don't narrate
        $GLOBALS["COMMAND_PROMPT_FUNCTIONS"]=""; # Handled by the system prompt
        if (isset($GLOBALS["FUNCTIONS_ARE_ENABLED"]) && $GLOBALS["FUNCTIONS_ARE_ENABLED"]) {
            $system_prompt .= "\n# AVAILABLE ACTIONS\n";
            $system_prompt .= " - This section defines actions that {$display_name} can perform to interact with the world.\n";
            $system_prompt .= " - Use these actions when they align with your character's intentions and the current situation.\n";
            $system_prompt .= " - While Talk is an available action for dialogue, prioritize other contextually appropriate actions when possible.\n";
        }
    }
    else {
        // Use defaults
        // no change required
    }
    
    // Process decorators
    minai_start_timer('expand_decorators', 'system_prompt_builder');
    
    // Expand decorators for all hashtags in the system prompt
    $system_prompt = $registry->expandAllDecorators($system_prompt, $params);
    
    // Then expand any remaining variables (for backward compatibility)
    $system_prompt = ExpandPromptVariables($system_prompt);
    
    // Replace escaped quotes with regular quotes
    $system_prompt = str_replace("\\'", "'", $system_prompt);
    
    minai_stop_timer('expand_decorators', [
        'content_length' => strlen($system_prompt)
    ]);
    
    // Record metrics for system prompt builder
    minai_stop_timer('system_prompt_builder', [
        'content_length' => strlen($system_prompt)
    ]);
    
    return array(
        'role' => 'system',
        'content' => $system_prompt
    );
}

/**
 * Updates the system prompt in contextDataFull
 * 
 * This function replaces the 0th entry in contextDataFull with a newly generated
 * system prompt, or inserts it if the array is empty
 */
function UpdateSystemPrompt() {
    try {
        $newSystemPrompt = BuildSystemPrompt();
        
        if (empty($GLOBALS["head"]) || !is_array($GLOBALS["head"])) {
            $GLOBALS["head"] = [$newSystemPrompt];
        } else {
            // Replace the first element (system prompt)
            $GLOBALS["head"][0] = $newSystemPrompt;
            
            // Log the updated system prompt for debugging
            minai_log("info", "Updated system prompt successfully");
        }
    } catch (Exception $e) {
        minai_log("error", "Error updating system prompt: " . $e->getMessage());
    }
}

/**
 * Helper function to call a specific context builder and get its output
 * 
 * @param string $builderId The ID of the builder to call
 * @param array $params Parameters to pass to the builder
 * @return string The context string from the builder or empty string if not found
 */
function callContextBuilder($builderId, $params = []) {
    $registry = ContextBuilderRegistry::getInstance();
    $builder = $registry->getBuilder($builderId);
    // Set default values for missing parameters using PLAYER_NAME
    if (!isset($params['herika_name']) || !isset($params['target']) || !isset($params['player_name'])) {
        $defaultName = $GLOBALS["PLAYER_NAME"];
        $params = array_merge([
            'herika_name' => $defaultName,
            'target' => $defaultName,
            'player_name' => $defaultName
        ], $params);
    }
    if ($builder && is_callable($builder['builder_callback'])) {
        try {
            return call_user_func($builder['builder_callback'], $params);
        } catch (Exception $e) {
            minai_log("error", "Error calling context builder '{$builderId}': " . $e->getMessage());
        }
    }
    return '';
}

/**
 * Helper function to register a decorator
 * 
 * @param string $tag Hashtag to register (without # symbols)
 * @param callable $callback Function to call to expand the tag
 */
function registerDecorator($tag, $callback) {
    $registry = ContextBuilderRegistry::getInstance();
    $registry->registerDecorator($tag, $callback);
}

/**
 * Helper function to get all hashtags used in the system prompt
 * 
 * @return array List of all unique hashtags used
 */
function getAllHashtags() {
    $registry = ContextBuilderRegistry::getInstance();
    return $registry->getAllHashtags();
}

/**
 * Helper function to get a mapping of hashtags to their resolved values
 * 
 * @param array $params Optional parameters to pass to decorators
 * @return array Associative array of hashtags and their resolved values
 */
function getHashtagMap($params = []) {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Set default values for missing parameters
    if (!isset($params['herika_name']) || !isset($params['target']) || !isset($params['player_name'])) {
        $defaultName = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "Player";
        $params = array_merge([
            'herika_name' => isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "Character",
            'target' => isset($GLOBALS["target"]) ? $GLOBALS["target"] : $defaultName,
            'player_name' => $defaultName
        ], $params);
    }
    
    return $registry->getHashtagMap($params);
}

/**
 * Helper function to expand a specific hashtag
 * 
 * @param string $tag Tag to expand (without # symbols)
 * @param array $params Optional parameters to pass to the decorator
 * @return string The expanded value
 */
function expandDecorator($tag, $params = []) {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Set default values for missing parameters
    if (!isset($params['herika_name']) || !isset($params['target']) || !isset($params['player_name'])) {
        $defaultName = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "Player";
        $params = array_merge([
            'herika_name' => isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "Character",
            'target' => isset($GLOBALS["target"]) ? $GLOBALS["target"] : $defaultName,
            'player_name' => $defaultName
        ], $params);
    }
    
    // No need to reset visited decorators - the expandDecorator method handles this
    
    return $registry->expandDecorator($tag, $params);
}

// Initialize the registry with core builders
function InitializeContextBuilders() {
    // This function is called to register all context builders
    // Individual modules will register their builders
    InitializeCoreContextBuilders();
    InitializeCharacterContextBuilders();
    InitializeRelationshipContextBuilders();
    InitializeEnvironmentalContextBuilders();
    InitializeNSFWContextBuilders();
}

// Call the initialization function
InitializeContextBuilders();