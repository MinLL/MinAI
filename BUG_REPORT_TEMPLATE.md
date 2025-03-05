# MinAI Bug Report Template

When submitting a bug report, please follow this template carefully. This will help us diagnose and fix your issue more quickly. 

## Before Submitting a Bug Report

### Use the Troubleshooter First
Before submitting a bug report, please use the MinAI Troubleshooter (Server Plugins -> Plugin Manager -> MinAI -> Diagnostics -> Troubleshooter). The troubleshooter can:
- Identify common configuration issues
- Help explain unexpected AI behavior
- Validate your setup and requirements
- Explain action execution
- Verify LLM responses

Many common issues can be resolved using the troubleshooter without needing to submit a bug report.

## Required Information

### System Information
- Skyrim Version:
- CHIM Version:
- MinAI Version:
- List of other relevant mods installed:

### Bug Description
**What happened:**
[Describe what actually occurred]

**What you expected:**
[Describe what you expected to happen]

**Steps to reproduce:**
1. [First step]
2. [Second step]
3. [etc...]

### Required Log Files

**Important:** Different issues require different logs. Please provide the appropriate logs based on your issue type:

| Issue Type | Required Logs |
|------------|--------------|
| In-game behavior/mechanics | Papyrus.0.log |
| Action execution problems | Papyrus.0.log + context_sent_to_llm.log + output_from_llm.log |
| Sapience/NPC behavior | Papyrus.0.log |
| CHIM crashes/errors | AIAgent.log + apache_error.log |
| Strange LLM responses | context_sent_to_llm.log + output_from_llm.log |
| CHIM not working/connection issues | apache_error.log |
| MinAI roleplay/context issues | minai.log + minai_context_sent_to_llm.log + minai_output_from_llm.log |

#### 1. Papyrus Log
**Required for: In-game behavior, action execution, and Sapience issues**

You must enable Papyrus logging first:
1. Navigate to `Documents/My Games/Skyrim Special Edition`
2. Open (or create) `Skyrim.ini`
3. Add these lines under the `[Papyrus]` section:
```ini
[Papyrus]
bEnableLogging=1
bEnableTrace=1
bLoadDebugInformation=1
```
4. Start your game, reproduce the issue
5. Find the log at: `Documents/My Games/Skyrim Special Edition/Logs/Script/Papyrus.0.log`
6. Copy the contents and paste them between the triple backticks below:

```
[Paste your Papyrus.0.log here]
```

#### 2. AIAgent Log
**Required for: CHIM crashes and errors**

1. Navigate to `Documents/My Games/SKSE`
2. Find `AIAgent.log`
3. Copy the contents and paste them between the triple backticks below:

```
[Paste your AIAgent.log here]
```

#### 3. MinAI Diagnostics Logs
**Required for: Action execution problems and strange LLM behavior**

Access the MinAI Diagnostics page through:
1. Open CHIM's UI via your browser
2. Navigate to Server Plugins -> Plugin Manager -> MinAI -> Diagnostics
3. Provide the following logs:

**context_sent_to_llm.log:**
```
[Paste the contents of context_sent_to_llm.log here]
```

**output_from_llm.log:**
```
[Paste the contents of output_from_llm.log here]
```

These logs are crucial for debugging as they show:
- `context_sent_to_llm.log`: The context and available actions being sent to the AI model
- `output_from_llm.log`: The AI model's responses and chosen actions

If you're reporting an issue with actions not working, these logs will help determine if:
- Actions are being properly exposed to the AI (visible in context_sent_to_llm.log)
- The AI is attempting to use actions (visible in output_from_llm.log)
- There's a disconnect between the AI's commands and in-game execution

#### 4. Apache Error Log
**Required for: CHIM connection issues and server errors**

1. Navigate to the MinAI Diagnostics page
2. Find the `apache_error.log` file.
3. Copy the contents and paste them between the triple backticks below:

```
[Paste your apache_error.log here]
```

#### 5. MinAI Specific Logs
**Required for: MinAI roleplay and context-related issues**

These logs can be viewed via the Diagnostics page:

**minai.log:**
```
[Paste your minai.log here]
```

**minai_context_sent_to_llm.log:**
```
[Paste your minai_context_sent_to_llm.log here]
```

**minai_output_from_llm.log:**
```
[Paste your minai_output_from_llm.log here]
```

These logs are particularly helpful for debugging:
- Character personality and roleplay issues
- Context generation problems
- Memory and relationship tracking
- Custom prompt template behavior

### Additional Context
- Were you in a specific location?
- Which NPC were you interacting with?
- Any other relevant details about what was happening in-game?

### Screenshots/Videos
If applicable, add screenshots or videos to help explain your problem.

---
**Note:** Please make sure to remove any sensitive information before submitting your logs. While these logs typically don't contain personal information, it's good practice to review them first. 