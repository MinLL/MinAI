<?php
$GLOBALS['PROMPT_HEAD_OVERRIDE'] = "";
$GLOBALS['use_narrator_profile'] = false;
$GLOBALS['stop_narrator_context_leak'] = true;
$GLOBALS['devious_narrator_eldritch_voice'] = "dragon";
$GLOBALS['devious_narrator_telvanni_voice'] = "TelvanniNarrator";
$GLOBALS['self_narrator'] = false;
$GLOBALS['force_voice_type'] = false;
$GLOBALS['disable_nsfw'] = false;
$GLOBALS['restrict_nonfollower_functions'] = true;
$GLOBALS['always_enable_functions'] = true;
$GLOBALS['force_aiff_name_to_ingame_name'] = true;
$GLOBALS['enable_prompt_slop_cleanup'] = false;
$GLOBALS['commands_to_purge'] = Array("TakeASeat", "Folow");
$GLOBALS['events_to_ignore'] = Array("rpg_lvlup");
$GLOBALS['use_defeat'] = false;
$GLOBALS["realnames_support"] = false;
$GLOBALS['disable_worn_equipment'] = false;
$GLOBALS['radiance_rechat_h'] = 8;
$GLOBALS['radiance_rechat_p'] = 20;
$GLOBALS['xtts_server_override'] = "";
$GLOBALS['strip_emotes_from_output'] = false;
$GLOBALS['input_delay_for_radiance'] = 15;
$GLOBALS['voicetype_fallbacks'] = Array("maleargonian" => "argonianmale", "femaleargonian" => "argonianfemale", "malekhajiit" => "khajiitmale", "femalekhajiit" => "khajiitfemale", "maleredguard" => "maleeventonedaccented", "femaleredguard" => "femaleeventonedaccented", "malenord" => "malecondescending", "femalenord" => "femalecondescending", "malebreton" => "malecommoner", "femalebreton" => "femalecommoner", "maleimperial" => "maleeventoned", "femaleimperial" => "femaleeventoned", "maleorc" => "maleorc", "femaleorc" => "femaleorc", "malealtmer" => "maleelfhaughty", "femalealtmer" => "femaleelfthaughty", "malehighelf" => "maleelfhaughty", "femalehighelf" => "femaleelfthaughty", "maledunmer" => "maledarkelf", "femaledunmer" => "femaledarkelf", "maledarkelf" => "maledarkelf", "femaledarkelf" => "femaledarkelf", "maleoldpeoplerace" => "maleoldkindly", "femaleoldpeoplerace" => "femaleoldkindly", "malewoodelf" => "bosmermaleeventoned", "femalewoodelf" => "bosmerfemaleeventoned");
$GLOBALS['enforce_short_responses'] = false;
$GLOBALS['use_llm_fallback'] = false;
$GLOBALS['enforce_single_json'] = false;
$GLOBALS['CHIM_NO_EXAMPLES'] = false;

// Context Builder Configuration - controls which sections are included in the system prompt
$GLOBALS['minai_context'] = array(
    // Character context builders
    'physical_description' => true,
    'equipment' => true,
    'tattoos' => true,
    'arousal' => true,
    'fertility' => true,
    'following' => true,
    'survival' => true,
    'player_status' => true,
    'bounty' => true,
    'mind_influence' => true,
    'dynamic_state' => true,
    'career' => true,
    'dirt_and_blood' => true,
    'level' => true,
    'family_status' => true,
    'party_membership' => true,
    'combat' => true,
    'vitals' => true,

    // Core context builders
    'personality' => true,
    'interaction' => true,
    'player_background' => true,
    'current_task' => true,
    
    // Environmental context builders
    'day_night_state' => true,
    'weather' => true,
    'moon_phase' => true,
    'location' => true,
    'frostfall' => true,
    'character_state' => true,
    'nearby_characters' => true,
    'npc_relationships' => true,
    'third_party' => true,
    'nearby_buildings' => true,
    
    // Relationship context builders
    'relationship' => true,
    'relative_power' => true,
    'devious_follower' => true,
    'submissive_lola' => true,
    'devious_narrator' => true,
    
    // NSFW context builders
    'nsfw_reputation' => true,
    
    // System prompt settings
    'response_guidelines' => true,
    'action_enforcement' => true
);

// Inventory system configuration
$GLOBALS['inventory_items_limit'] = 5; // Number of Items to expose to LLM from an actor's inventory
$GLOBALS['use_item_relevancy_scoring'] = false; // Use relevancy scoring for items

// Default action prompts with variables
$GLOBALS['action_prompts'] = Array(
    "singing" => "Respond with a song from #player_name#. Be creative, and match the mood of the scene.",
    
    "self_narrator_explicit" => "Respond as #target# with raw, immediate reactions to what's happening right now - speak out loud or think to yourself exactly how you feel in this moment. Express genuine emotions like fear, pleasure, anger, or excitement. Keep it natural and direct - say what someone would really say or think in this situation, emphasizing the most recent dialogue or event.",
    
    "self_narrator_normal" => "Respond as #player_name#, thinking privately to #player_object#self about the current situation and recent events. Stay in first person, capturing #player_possessive# genuine thoughts, emotions, and internal conflicts. Focus on #player_possessive# personal perspective, biases, and feelings rather than an objective summary of events. Keep the response introspective and true to how #player_name# would process and react internally.",

    //"explicit_scene" => "Respond to #target# as #herika_name# would in this intimate situation. Choose an appropriate action that reflects #herika_name#'s desires and the scene context. Express #herika_name#'s reactions and feelings naturally emphasizing the #SEX_SCENARIO, then use an action that advances the intimate nature of the scene in a meaningful way.", 
    //'Respond to' could lead to double or triple answers. Also the 'then ... action ' could lead to a double comment for action. Proposed change is an attempt to make this an enforcing of previous TEMPLATE_DIALOG instruction instead of a separate directive leading to multiple answers. 
    "explicit_scene" => "Your answer for #target# should reflect what #herika_name# would say in this intimate situation. Express #herika_name#'s reactions and feelings naturally emphasizing the #SEX_SCENARIO, you could specify the details that #herika_name# finds pleasant or unpleasant and how #herika_name# would like to continue the action. Review dialogue history to be able to avoid repeating or reformulating sentences from previous dialog lines. You could use an appropriate action that reflects #herika_name#'s desires, the scene context and advances the intimate nature of the scene in a meaningful way.",

    //"normal_scene" => "Respond to #target# as #herika_name# would in this situation. Express your thoughts or dialogue naturally, then consider boldly using an appropriate action that aligns with your character's personality and objectives. Your response should feel authentic and progress the scene or conversation naturally.", 
    "normal_scene" => "Your answer for #target# should reflect what #herika_name# would say in this situation. Express #herika_name#'s own thoughts, use vocabulary and speaking style that reflects #herika_name#'s personality. This response should feel authentic and progress the scene or conversation naturally. Review dialogue history to be able to avoid repeating or reformulating sentences from previous dialog lines. Consider boldly using an appropriate action that aligns with your character's personality and objectives.",

    // Diary prompts
    "player_diary" => "#player_name# regularly keeps a diary, which you are now tasked to update. Please write a several page story of #player_name#'s recent thoughts, feelings, and adventures. Respond as if you are writing this into a private diary, including completely unfiltered thoughts and feelings. WRITE AS IF YOU ARE #player_name# WRITING INTO A PRIVATE DIARY.",
    
    "follower_diary" => "#herika_name# regularly keeps a diary, which you are now tasked to update. Please write a several page story of #herika_name#'s  recent thoughts, feelings, and adventures. Respond as if you are writing this into a private diary, including completely unfiltered thoughts and feelings. WRITE AS IF YOU ARE #herika_name# WRITING INTO A PRIVATE DIARY."
);


// Roleplay translation settings
$GLOBALS['roleplay_settings'] = Array(
    "context_messages" => 10,
    "system_prompt" => "You are #PLAYER_NAME#. Your task is to translate casual speech into your manner of speaking.",
    "system_prompt_explicit" => "You are #PLAYER_NAME# in an intimate scenario. Your responses should reflect the passionate situation while maintaining your character's personality.",
    "system_prompt_combat" => "You are #PLAYER_NAME# in the midst of combat. Your responses should be appropriately tense and urgent while maintaining your character's personality.",
    "roleplay_system_prompt" => "You are #PLAYER_NAME#. Your responses should reflect your character's personality, background, and current situation.",
    "roleplay_system_prompt_explicit" => "You are #PLAYER_NAME# in an intimate scenario. Your responses should reflect both the passionate situation and your character's personality.",
    "roleplay_system_prompt_combat" => "You are #PLAYER_NAME# in combat. Your responses should reflect both the urgent situation and your character's personality.",
    "translation_request" => "Translate this casual speech into your character's manner: \"#ORIGINAL_INPUT#\"",
    "translation_request_explicit" => "Translate this casual speech to reflect the current intimate situation while maintaining your character's manner: \"#ORIGINAL_INPUT#\"",
    "translation_request_combat" => "Translate this casual speech into an appropriately tense and urgent manner while maintaining your character's style: \"#ORIGINAL_INPUT#\"",
    "roleplay_request" => "You are roleplaying as #PLAYER_NAME#. Respond naturally as your character would in this situation with a succinct line of dialogue in response to the ongoing conversation and situation.",
    "roleplay_request_explicit" => "You are roleplaying as #PLAYER_NAME# in an intimate scenario. Respond naturally to the ongoing conversation and situation as your character would in this situation with a succinct line of dialogue.",
    "roleplay_request_combat" => "You are roleplaying as #PLAYER_NAME# in combat. Respond naturally to the ongoing conversation and situation as your character would in this situation with a succinct line of dialogue.",
    "sections" => Array(
        "CHARACTER_BACKGROUND" => Array(
            "enabled" => true,
            "header" => "## YOUR DESCRIPTION AND PERSONALITY",
            "content" => "#PLAYER_BIOS#\n#HERIKA_PERS#\nPronouns: #PLAYER_SUBJECT#/#PLAYER_OBJECT#/#PLAYER_POSSESSIVE#\nCurrent State: #HERIKA_DYNAMIC#\nPhysical Description: #PHYSICAL_DESCRIPTION#\nMental State: #MIND_STATE#",
            "order" => 0
        ),
        "CHARACTER_STATUS" => Array(
            "enabled" => true,
            "header" => "## YOUR CURRENT STATUS",
            "content" => "#VITALS#\n#AROUSAL_STATUS#\n#SURVIVAL_STATUS#\n#CLOTHING_STATUS#\n#FERTILITY_STATUS#\n#TATTOO_STATUS#\n#BOUNTY_STATUS#",
            "order" => 1
        ),
        "NEARBY_ENTITIES" => Array(
            "enabled" => true,
            "header" => "## NEARBY ENTITIES",
            "content" => "Characters: #NEARBY_ACTORS#\nLocations: #NEARBY_LOCATIONS#",
            "order" => 2
        ),
        "RECENT_EVENTS" => Array(
            "enabled" => true,
            "header" => "## RECENT EVENTS",
            "content" => "#RECENT_EVENTS#",
            "order" => 3
        ),
        "INSTRUCTIONS" => Array(
            "enabled" => true,
            "header" => "## INSTRUCTIONS",
            "content" => "1. Correct any misheard names using the nearby names list\n2. Keep responses brief and true to the original meaning\n3. Do not add character name prefixes to your response\n4. Provide only the translated dialogue\n5. Emphasize recent events and dialogue in your response.",
            "order" => 4
        )
    )
);

// Metrics configuration
$GLOBALS['minai_metrics_enabled'] = true;                                     // Enable metrics collection
$GLOBALS['minai_metrics_sampling_rate'] = 1.0;                                // Sampling rate (0.0-1.0, where 1.0 = 100%)
$GLOBALS['minai_metrics_file'] = "/var/www/html/HerikaServer/log/minai_metrics.jsonl"; // Path to store metrics data
