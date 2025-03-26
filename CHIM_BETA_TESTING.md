# CHIM Beta Testing Guide

## About Debug Builds

The debug build of CHIM is designed to provide enhanced diagnostic information when crashes or other issues occur. Unlike the regular release version, debug builds include additional debugging symbols and logging capabilities that help identify the root causes of problems. This is why the Visual C++ Debug Redistributables are required - they provide the necessary runtime support for these debugging features.

## Prerequisites



1. **Install Visual C++ Debug Redistributables**
   - Download the Visual Studio Build Tools from [Microsoft's website](https://visualstudio.microsoft.com/downloads/#remote-tools-for-visual-studio-2022)
   - ![1](https://github.com/user-attachments/assets/12b2f8bc-8664-40a1-876e-13b6bdbd376e)
   - During installation, select and install the "C++ Build Tools" component
   - ![2](https://github.com/user-attachments/assets/be8f4597-bf00-4b92-92e5-4e24abec7136)
   - This step is **mandatory** - the debug version will not work without these redistributables

## Installation Steps

1. **Install the Debug Version**
   - Download the `AIAgent-Debug-version.zip` file provided to you
   - Install it as a separate mod in your mod manager
   - Make sure it overwrites the existing CHIM files
   - ⚠️ **IMPORTANT**: Remember to remove this debug version before updating CHIM in the future!

2. **MinAI Compatibility (Optional)**
   - If you use MinAI with CHIM, you must use the latest version
   - MinAI is not required for beta testing, but if installed, it must be up to date

## Verification

1. **Start the Game**
   - Launch Skyrim through your mod manager
   - If CHIM doesn't work at all, check SKSE logs for errors
   - If you see "AIAgent.dll failed to load", this indicates the debug redistributables were not installed correctly

## Reporting Issues

If you encounter any problems, please include the following log files in your report:
- `Documents/My Games/Skyrim Special Edition/SKSE/AIagent.log`
- `Documents/My Games/Skyrim Special Edition/Logs/Scripts/Papyrus.0.log`

These logs are essential for diagnosing any issues you might encounter during beta testing.

## Cleanup

Remember to remove the debug version and reinstall the regular CHIM version when:
1. You're done beta testing
2. You want to update CHIM to a newer version

This will prevent any conflicts with future updates. 
