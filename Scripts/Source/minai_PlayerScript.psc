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
Form gold
bool trackingEnabled = False

Function StartTrackingPlayer()
  if playerRef.HasSpell(minai_PlayerStateTracker)
    playerRef.RemoveSpell(minai_PlayerStateTracker)
  EndIf
  aiff.SetActorVariable(playerRef, "inCombat", playerRef.GetCombatState() >= 1)
  minai_DynamicSapienceToggleStealth = Game.GetFormFromFile(0x0E97, "MinAI.esp") as GlobalVariable
  if (!minai_DynamicSapienceToggleStealth)
    MainQuestController.Error("Could not retrieve minai_DynamicSapienceToggleStealth from esp")
    Return
  EndIf

  ; Always start with sapience enabled
  minai_DynamicSapienceToggleStealth.SetValue(1.0)

  MainQuestController.Info("Starting player state tracking")
  playerRef.AddSpell(minai_PlayerStateTracker, false)

  ; Only register for animation events if the feature is enabled
  if config.disableSapienceInStealth
    RegisterStealthAnimEvents()
  EndIf
EndFunction

Function RegisterStealthAnimEvents()
  MainQuestController.Info("Registering stealth animation events")
  RegisterForAnimationEvent(playerRef, "tailSneakIdle")
  RegisterForAnimationEvent(playerRef, "tailSneakLocomotion") 
  RegisterForAnimationEvent(playerRef, "tailMTIdle")
  RegisterForAnimationEvent(playerRef, "tailMTLocomotion")
  RegisterForAnimationEvent(playerRef, "tailCombatIdle")
  RegisterForAnimationEvent(playerRef, "tailCombatLocomotion")
EndFunction

Function UnregisterStealthAnimEvents()
  MainQuestController.Info("Unregistering stealth animation events")
  UnregisterForAnimationEvent(playerRef, "tailSneakIdle")
  UnregisterForAnimationEvent(playerRef, "tailSneakLocomotion") 
  UnregisterForAnimationEvent(playerRef, "tailMTIdle")
  UnregisterForAnimationEvent(playerRef, "tailMTLocomotion")
  UnregisterForAnimationEvent(playerRef, "tailCombatIdle")
  UnregisterForAnimationEvent(playerRef, "tailCombatLocomotion")
EndFunction



Function UpdateStealthFeatureState(bool enabled)
  ; Called when the feature is toggled in MCM
  if enabled
    RegisterStealthAnimEvents()
    ; Set initial state based on current sneaking status
    if playerRef.IsSneaking()
      minai_DynamicSapienceToggleStealth.SetValue(0.0)
    else
      minai_DynamicSapienceToggleStealth.SetValue(1.0)
    endIf
  else
    UnregisterStealthAnimEvents()
    ; Re-enable sapience when feature is disabled
    minai_DynamicSapienceToggleStealth.SetValue(1.0)
  endif
EndFunction

Event OnPlayerLoadGame()
  playerRef = game.GetPlayer()
  RegisterForSleep()
  gold = Game.GetFormFromFile(0x00000F, "Skyrim.esm") as Form
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  if (bHasAIFF)
    aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  endif
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  minai_PlayerStateTracker = Game.GetFormFromFile(0x0921, "MinAI.esp") as Spell
  
  ; Ensure sapience isn't incorrectly disabled on load
  minai_DynamicSapienceToggleStealth = Game.GetFormFromFile(0x0E97, "MinAI.esp") as GlobalVariable
  if minai_DynamicSapienceToggleStealth && !config.disableSapienceInStealth && minai_DynamicSapienceToggleStealth.GetValueInt() == 0
    MainQuestController.Info("Re-enabling sapience on load as stealth feature is disabled")
    minai_DynamicSapienceToggleStealth.SetValue(1.0)
  EndIf
  StartTrackingPlayer()
  MainQuestController.Maintenance()
  EnableInventoryTracking()
EndEvent

Event OnLocationChange(Location akOldLoc, Location akNewLoc)
  if (bHasAIFF)
    aiff.CleanupSapientActors()
  EndIf
  EnableInventoryTracking()
endEvent


Event OnSleepStart(float afSleepStartTime, float afDesiredSleepEndTime)
  MainQuestController.Info("OnSleepStart()")
  if config.autoUpdateDiary
    followers.UpdateFollowerDiaries()
  EndIf
  if config.updateNarratorProfile && bHasAIFF
    aiff.UpdateProfile("The Narrator")
  EndIf
  EnableInventoryTracking()
EndEvent

; Add new event handler for animation events
Event OnAnimationEvent(ObjectReference akSource, string asEventName)
  MainQuestController.Debug("OnAnimationEvent() - akSource: " + akSource + " asEventName: " + asEventName)
  if akSource == playerRef && minai_DynamicSapienceToggleStealth && config.disableSapienceInStealth
    bool isStealthAnim = (asEventName == "tailSneakIdle" || asEventName == "tailSneakLocomotion")
    bool isNonStealthAnim = (asEventName == "tailMTIdle" || asEventName == "tailMTLocomotion" || asEventName == "tailCombatIdle" || asEventName == "tailCombatLocomotion")
    
    if isStealthAnim
      minai_DynamicSapienceToggleStealth.SetValue(0.0)
      MainQuestController.Info("Disabling sapience due to stealth mode")
    elseif isNonStealthAnim
      MainQuestController.Info("Re-enabling sapience (leaving stealth mode)")
      minai_DynamicSapienceToggleStealth.SetValue(1.0)
    endif
  endif
EndEvent

; Add event handler for item added
Event OnItemAdded(Form akBaseItem, int aiItemCount, ObjectReference akItemReference, ObjectReference akSourceContainer)
  if !bHasAIFF || !aiff
    return
  EndIf
  if !trackingEnabled
    return
  endif
  MainQuestController.Debug("Player OnItemAdded - Form: " + akBaseItem + ", Count: " + aiItemCount)
  if aiff.OnInventoryChanged(playerRef, akBaseItem, aiItemCount, true)
    DisableInventoryTracking()
  endif
EndEvent

; Add event handler for item removed
Event OnItemRemoved(Form akBaseItem, int aiItemCount, ObjectReference akItemReference, ObjectReference akDestContainer)
  if !bHasAIFF || !aiff
    return
  EndIf
  if !trackingEnabled
    return
  endif
  
  MainQuestController.Debug("Player OnItemRemoved - Form: " + akBaseItem + ", Count: " + aiItemCount)
  if aiff.OnInventoryChanged(playerRef, akBaseItem, aiItemCount, false)
    DisableInventoryTracking()
  endif
EndEvent


event DisableInventoryTracking()
  ; Filter out everything except gold to neuter the item change events
  MainQuestController.Info("Disabling inventory tracking for player")
  if (!gold)
    gold = Game.GetFormFromFile(0x00000F, "Skyrim.esm") as Form
  endif
  trackingEnabled = False
  AddInventoryEventFilter(gold)
  
endEvent


event EnableInventoryTracking()
  ; Remove the filter to allow all item changes again
  MainQuestController.Info("Enabling inventory tracking for player")
  trackingEnabled = True
  if (!gold)
    gold = Game.GetFormFromFile(0x00000F, "Skyrim.esm") as Form
  endif
  RemoveInventoryEventFilter(gold)
  aiff.TrackActorInventory(playerRef)
endEvent