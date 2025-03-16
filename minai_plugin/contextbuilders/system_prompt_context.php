<?php
/**
 * System Prompt Context Builder
 * 
 * This file contains the main framework for building and managing the system prompt
 * using a modular, configurable approach to context builders.
 */

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../util.php");

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
}

/**
 * Builds the complete system prompt using all registered context builders
 * 
 * @return array System prompt content in the format expected for contextDataFull
 */
function BuildSystemPrompt() {
 
    // Access global variables needed for the system prompt
    $prompt_head = isset($GLOBALS["PROMPT_HEAD"]) ? $GLOBALS["PROMPT_HEAD"] : "";
    $herika_name = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "";
    $player_name = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "";
    $target = isset($GLOBALS["target"]) ? $GLOBALS["target"] : 
             (isset($GLOBALS["HERIKA_TARGET"]) ? $GLOBALS["HERIKA_TARGET"] : $player_name);
    
    // Determine if NSFW context should be included
    $include_nsfw = !isset($GLOBALS["disable_nsfw"]) || !$GLOBALS["disable_nsfw"];
    
    // Initialize the system prompt
    $system_prompt = "";
    
    // Build the core instruction section
    $system_prompt .= "# Instructions\n";
    if (!empty($prompt_head)) {
        $system_prompt .= trim($prompt_head) . "\n\n";
    }
    $system_prompt .= "You are roleplaying as {$herika_name} in a Skyrim adventure.\n";
    $system_prompt .= "Respond in character as {$herika_name} at all times.\n\n";
    
    // Get registry instance
    $registry = ContextBuilderRegistry::getInstance();

    // Define the actors sections (primary character and target)
    $actors = [
        'primary' => $herika_name,
        'target' => $target
    ];

    // Common parameters to pass to all builders
    $params = [
        'herika_name' => $herika_name,
        'player_name' => $player_name,
        'target' => $target
    ];

    // Define the per-actor sections and their headers
    $actor_sections = [
        'character' => "Character",
        'status' => "Current Status"
    ];

    // Define the shared sections (apply to both/all actors or environment)
    $shared_sections = [
        'environment' => "# Environmental Context",
        'misc' => "# Additional Information"
    ];

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
            // Primary character -is Herika/NPC - params already set up correctly
        } else {
            // Target character (usually player) - swap the parameter roles
            $actor_params['herika_name'] = $target;
            $actor_params['is_target'] = true;
        }
        
        // Process each section for this actor
        foreach ($actor_sections as $section_id => $section_title) {
            $section_content = "";
            $builders = $registry->getBuilders($section_id, $include_nsfw);
            
            foreach ($builders as $id => $builder) {
                // Skip if no builder callback
                if (!is_callable($builder['builder_callback'])) {
                    minai_log("warning", "Builder callback '{$builder['builder_callback']}' is not callable");
                    continue;
                }
                
                try {
                    // Call the builder function with actor-specific params
                    $context = call_user_func($builder['builder_callback'], $actor_params);
                    
                    // Skip if no content returned
                    if (empty($context)) {
                        continue;
                    }
                    
                    // Add the sub-header if provided
                    if (!empty($builder['header'])) {
                        $section_content .= "### " . $builder['header'] . "\n";
                    }
                    
                    // Add the content
                    $section_content .= trim($context) . "\n\n";
                } catch (Exception $e) {
                    minai_log("error", "Error in builder '{$id}' for actor '{$actor_name}': " . $e->getMessage());
                }
            }
            
            // Add the section to the actor content if it has content
            if (!empty($section_content)) {
                $actor_content .= "## " . $section_title . "\n";
                $actor_content .= $section_content;
            }
        }
        
        // Add relationship information for this actor
        $relationship_content = "";
        $relationship_builders = $registry->getBuilders('interaction', $include_nsfw);
        
        foreach ($relationship_builders as $id => $builder) {
            if ($id === 'relationship' && is_callable($builder['builder_callback'])) {
                try {
                    $context = call_user_func($builder['builder_callback'], $actor_params);
                    
                    if (!empty($context)) {
                        $relationship_content .= "## Relationship\n";
                        $relationship_content .= trim($context) . "\n\n";
                    }
                } catch (Exception $e) {
                    minai_log("error", "Error in relationship builder for actor '{$actor_name}': " . $e->getMessage());
                }
            }
        }
        
        // Add relationship to actor content if available
        if (!empty($relationship_content)) {
            $actor_content .= $relationship_content;
        }
        
        // Add the actor section to the system prompt if it has content
        if (!empty($actor_content)) {
            $system_prompt .= "# " . $actor_name . "\n";
            $system_prompt .= $actor_content . "\n";
        }
    }
    
    // Add shared sections (environment, misc, etc.)
    foreach ($shared_sections as $section_id => $section_header) {
        $section_content = "";
        $builders = $registry->getBuilders($section_id, $include_nsfw);
        
        foreach ($builders as $id => $builder) {
            // Skip if no builder callback
            if (!is_callable($builder['builder_callback'])) {
                minai_log("warning", "Builder callback '{$builder['builder_callback']}' is not callable");
                continue;
            }
            
            try {
                // Call the builder function with error handling
                $context = call_user_func($builder['builder_callback'], $params);
                
                // Skip if no content returned
                if (empty($context)) {
                    continue;
                }
                
                // Add the sub-header if provided
                if (!empty($builder['header'])) {
                    $section_content .= "## " . $builder['header'] . "\n";
                }
                
                // Add the content
                $section_content .= trim($context) . "\n\n";
            } catch (Exception $e) {
                minai_log("error", "Error in builder '{$id}': " . $e->getMessage());
            }
        }
        
        // Add the section to the system prompt if it has content
        if (!empty($section_content)) {
            $system_prompt .= $section_header . "\n";
            $system_prompt .= $section_content;
        }
    }
    
    // Add guidance for the LLM on how to format responses
    $system_prompt .= "# Response Guidelines\n";
    $system_prompt .= "- Stay in character as {$herika_name} at all times\n";
    $system_prompt .= "- Respond appropriately to the context of the conversation\n";
    $system_prompt .= "- Be concise and direct in your responses\n";
    $system_prompt .= "- Your response should reflect your personality and relationship with {$target}\n";
    $system_prompt .= "- Never break the fourth wall or reference that you are an AI\n";
    
    return [
        'role' => 'system',
        'content' => $system_prompt
    ];
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