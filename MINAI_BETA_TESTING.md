# MinAI Beta Testing Guide

## Prerequisites

1. **CHIM DEV Launcher**
   - Download the DEV version of the launcher from the #minai-testing-downloads channel
   - This version allows switching to the Dev branch of the CHIM server via the debugging menu
   - [Screenshot placeholder for launcher dev branch selection]
   - After switching to the Dev branch, update the server.

2. **CHIM DEV Skyrim Mod**
   - Install the DEV version of the CHIM Skyrim mod

3. **AIAgent Debug Version**
   - Follow the installation instructions in the [CHIM Beta Testing Guide](CHIM_BETA_TESTING.md)
   - ⚠️ **IMPORTANT**: Remove this debug version before future CHIM updates!

4. **MinAI DEV Version**
   - Download the latest MinAI dev version from [MinAI Releases](https://github.com/MinLL/MinAI/releases)
   - Look for the most recent version marked as "Pre-release"

## Load Order

The correct load order is crucial for beta testing:

1. CHIM (DEV)
2. AIAgent-Debug
3. MinAI DEV

[Screenshot placeholder for load order]

## Issue Reporting

When reporting issues, you MUST include these log files:
1. `Documents/My Games/Skyrim Special Edition/SKSE/AIagent.log`
2. `Documents/My Games/Skyrim Special Edition/Logs/Scripts/Papyrus.0.log`

These logs are essential for diagnosing issues during beta testing.

## Cleanup

After beta testing:
1. Remove the debug version of AIAgent
2. Reinstall the regular CHIM version
3. Switch back to the stable branch in the CHIM launcher

This will prevent conflicts with future updates. 