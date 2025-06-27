# MinAI Minimal

A stripped-down version of MinAI containing only the essential features:

## Features

### 1. Self Narrator
- Generates internal player thoughts and reactions
- Triggered by `minai_narrator` or `self_narrator` requests
- Uses dedicated narrator voice (configurable)

### 2. Translation
- Converts casual player input to character-appropriate speech
- Triggered by `minai_translate` or `minai_roleplay` requests
- Auto-detects casual speech patterns and offers translation

## Usage

### API Endpoints

**POST /api/main.php**
```json
{
    "0": "minai_translate",
    "3": "hey what's up",
    "player_name": "Lydia"
}
```

**GET /api/main.php**
- Returns status and enabled features

### Integration

Include in your HerikaServer plugin:
```php
require_once("/path/to/minai_minimal/main.php");
```

## Configuration

Edit `config.php`:
- `$GLOBALS['narrator_voice']` - Voice for narrator
- `$GLOBALS['player_voice_model']` - Default player voice
- `$GLOBALS['translation_enabled']` - Enable/disable translation
- `$GLOBALS['self_narrator']` - Enable/disable narrator

## Files

- `main.php` - Entry point and request processor
- `config.php` - Configuration settings
- `translation.php` - Translation feature implementation
- `narrator.php` - Self narrator feature implementation
- `utils/llm_utils.php` - LLM communication utilities
- `api/main.php` - HTTP API endpoint

## Logging

Logs are written to `/var/www/html/HerikaServer/log/minai_minimal.log`