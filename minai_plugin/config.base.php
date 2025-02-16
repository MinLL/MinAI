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

// Default action prompts with variables
$GLOBALS['action_prompts'] = Array(
    "singing" => "Respond with a song from #player_name#. Be creative, and match the mood of the scene.",
    
    "self_narrator_explicit" => "Respond with #target#'s immediate thoughts, emotions, and internal reactions to the physical and emotional sensations #target_subject# is experiencing right now. Focus particularly on any erotic elements happening within the scene. Respond in first person as #target#, staying fully in the present moment and focusing on #target_possessive# personal, subjective experience rather than describing the situation itself. Keep the response deeply personal and reflective of how #target# would genuinely react.",
    
    "self_narrator_normal" => "Respond as #player_name#, thinking privately to #player_object#self about the current situation and recent events. Stay in first person, capturing #player_possessive# genuine thoughts, emotions, and internal conflicts. Focus on #player_possessive# personal perspective, biases, and feelings rather than an objective summary of events. Keep the response introspective and true to how #player_name# would process and react internally.",

    "explicit_scene" => "Choose the ACTION or TALK that best conveys #herika_name#'s immediate physical and emotional responses. Focus heavily on explicit dialogue and verbal expressions to enhance the scene, but also utilize ACTIONS to vividly depict #herika_name#'s sensations and feelings when interacting with #target#. Avoid narration and emoting.",
    
    "normal_scene" => "Choose the ACTION that best fits the current context and #herika_name#'s mood when interacting with #target#. Prioritize dialogue for expressing thoughts and intentions. Use ACTIONS for tasks like interacting with items, trading, or showing physical needs. Avoid narration and emoting."
);

// Roleplay translation settings
$GLOBALS['roleplay_settings'] = Array(
    "context_messages" => 10,
    "system_prompt" => "You are #PLAYER_NAME#. Your task is to translate casual speech into your manner of speaking.",
    "roleplay_system_prompt" => "You are #PLAYER_NAME#. Your responses should reflect your character's personality, background, and current situation.",
    "translation_request" => "Translate this casual speech into your character's manner: \"#ORIGINAL_INPUT#\"",
    "roleplay_request" => "You are roleplaying as #PLAYER_NAME#. Respond naturally as your character would in this situation with a succinct line of dialogue in response to the ongoing conversation and situation.",
    "sections" => Array(
        "CHARACTER_BACKGROUND" => Array(
            "enabled" => true,
            "header" => "=== YOUR BACKGROUND ===",
            "content" => "#PLAYER_BIOS#\nPronouns: #PLAYER_SUBJECT#/#PLAYER_OBJECT#/#PLAYER_POSSESSIVE#\n\nCurrent State: #HERIKA_DYNAMIC#\n\nPhysical Description: #PHYSICAL_DESCRIPTION#",
            "order" => 0
        ),
        "CHARACTER_STATUS" => Array(
            "enabled" => true,
            "header" => "=== YOUR CURRENT STATUS ===",
            "content" => "#AROUSAL_STATUS#\n#SURVIVAL_STATUS#\n#CLOTHING_STATUS#\n#DEVICES_STATUS#\n#FERTILITY_STATUS#",
            "order" => 1
        ),
        "NEARBY_ENTITIES" => Array(
            "enabled" => true,
            "header" => "=== NEARBY ENTITIES ===",
            "content" => "Characters: #NEARBY_ACTORS#\nLocations: #NEARBY_LOCATIONS#",
            "order" => 2
        ),
        "RECENT_EVENTS" => Array(
            "enabled" => true,
            "header" => "=== RECENT EVENTS ===",
            "content" => "#RECENT_EVENTS#",
            "order" => 3
        ),
        "INSTRUCTIONS" => Array(
            "enabled" => true,
            "header" => "=== INSTRUCTIONS ===",
            "content" => "1. Correct any misheard names using the nearby names list\n2. Keep responses brief and true to the original meaning\n3. Do not add character name prefixes to your response\n4. Provide only the translated dialogue\n5. Emphasize recent events and dialogue in your response.",
            "order" => 4
        )
    )
);
