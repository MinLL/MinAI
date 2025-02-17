Scriptname minai_PlayerScript extends ReferenceAlias

minai_MainQuestController Property  MainQuestController  Auto
minai_AIFF aiff
minai_Followers followers
minai_Config config
bool bHasAIFF
Spell minai_PlayerStateTracker
actor playerRef
GlobalVariable minai_DynamicSapienceToggleStealth
minai_SapienceController Property sapience Auto

Function StartTrackingPlayer()
  if playerRef.HasSpell(minai_PlayerStateTracker)
    playerRef.RemoveSpell(minai_PlayerStateTracker)
  EndIf
  if (playerRef.IsSneaking())
    minai_DynamicSapienceToggleStealth.SetValue(0.0)
  Else
    minai_DynamicSapienceToggleStealth.SetValue(1.0)
  EndIf
  MainQuestController.Info("Starting player state tracking")
  playerRef.AddSpell(minai_PlayerStateTracker, false)
  minai_DynamicSapienceToggleStealth = Game.GetFormFromFile(0x0E97, "MinAI.esp") as GlobalVariable
  if (!minai_DynamicSapienceToggleStealth)
    MainQuestController.Error("Could not retrieve minai_DynamicSapienceToggleStealth from esp")
  EndIf
  ; Register for sneak animation events
  RegisterForAnimationEvent(playerRef, "tailSneakIdle")
  RegisterForAnimationEvent(playerRef, "tailSneakLocomotion") 
  RegisterForAnimationEvent(playerRef, "tailMTIdle")
  RegisterForAnimationEvent(playerRef, "tailMTLocomotion")
  RegisterForAnimationEvent(playerRef, "tailCombatIdle")
  RegisterForAnimationEvent(playerRef, "tailCombatLocomotion")
EndFunction

Event OnPlayerLoadGame()
  playerRef = game.GetPlayer()
  RegisterForSleep()
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  if (bHasAIFF)
    aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  endif
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  minai_PlayerStateTracker = Game.GetFormFromFile(0x0921, "MinAI.esp") as Spell
  StartTrackingPlayer()
  MainQuestController.Maintenance()
  
EndEvent

Event OnLocationChange(Location akOldLoc, Location akNewLoc)
  if (bHasAIFF)
    aiff.CleanupSapientActors()
  EndIf
endEvent


Event OnSleepStart(float afSleepStartTime, float afDesiredSleepEndTime)
  MainQuestController.Info("OnSleepStart()")
  if config.autoUpdateDiary
    followers.UpdateFollowerDiaries()
  EndIf
  if config.updateNarratorProfile && bHasAIFF
    aiff.UpdateProfile("The Narrator")
  EndIf
EndEvent

; Add new event handler for animation events
Event OnAnimationEvent(ObjectReference akSource, string asEventName)
  MainQuestController.Debug("OnAnimationEvent() - akSource: " + akSource + " asEventName: " + asEventName)
  if akSource == playerRef
    if asEventName == "tailSneakIdle" || asEventName == "tailSneakLocomotion"
      if config.disableSapienceInStealth
        minai_DynamicSapienceToggleStealth.SetValue(0.0)
        MainQuestController.Info("Disabling sapience due to stealth")
      EndIf
    elseif asEventName == "tailMTIdle" || asEventName == "tailMTLocomotion" || asEventName == "tailCombatIdle" || asEventName == "tailCombatLocomotion"
      if config.disableSapienceInStealth
        MainQuestController.Info("Re-enabling sapience after leaving stealth")
        minai_DynamicSapienceToggleStealth.SetValue(1.0)
      EndIf
    endif
  endif
EndEvent
