Scriptname minai_Config extends SKI_ConfigBase Conditional

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious

; OID definitions
int logLevelOID
int useCBPCOID
int cbpcDisableSelfAssTouchOID
int cbpcDisableSelfTouchOID
int cbpcSelfTouchThresholdOID
int cbpcOtherTouchThresholdOID
int collisionCooldownOID
int collisionSpeechCooldownOID
int collisionSexCooldownOID
int allowDeviceLockOID
int allowDeviceUnlockOID
int requestResponseCooldownOID
int arousalForSexOID
int arousalForHarassOID
int confirmSexOID

; Legacy globals
GlobalVariable useCBPC
GlobalVariable minai_UseOstim


; New configs
Bool cbpcDisableSelfAssTouchDefault = True
Bool Property cbpcDisableSelfAssTouch = True Auto

Bool cbpcDisableSelfTouchDefault = False 
Bool Property cbpcDisableSelfTouch = False Auto

float cbpcOtherTouchThresholdDefault = 10.0
float Property cbpcOtherTouchThreshold = 10.0 Auto

float cbpcSelfTouchThresholdDefault = 10.0 
float Property cbpcSelfTouchThreshold = 10.0 Auto

float collisionCooldownDefault = 2.0
float Property collisionCooldown = 2.0 Auto

float collisionSpeechCooldownDefault = 8.0
float Property collisionSpeechCooldown = 8.0 Auto

float collisionSexCooldownDefault = 14.0
float Property collisionSexCooldown = 14.0 Auto

bool allowDeviceLockDefault = False
bool Property allowDeviceLock = False Auto

bool allowDeviceUnlockDefault = False
bool Property allowDeviceUnlock = False Auto

float requestResponseCooldownDefault = 10.0
Float Property requestResponseCooldown = 10.0 Auto

float arousalForSexDefault = 0.0
Float Property arousalForSex = 0.0 Auto

float arousalForHarassDefault = 0.0
Float Property arousalForHarass = 0.0 Auto

bool confirmSexDefault = False
bool Property confirmSex = False Auto

Event OnConfigInit()
  main.Info("Building mcm menu.")
  InitializeMCM()
EndEvent

Function InitializeMCM()
  minai_UseOStim = Game.GetFormFromFile(0x0906, "MinAI.esp") as GlobalVariable
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  Main.Info("Initializing VR Module.")
  useCBPC = Game.GetFormFromFile(0x0910, "MinAI.esp") as GlobalVariable
  SetupPages()
EndFunction

int Function GetVersion()
	return 7 ; mcm menu version
EndFunction

Function SetupPages()
  Pages = new string[4]
  Pages[0] = "General"
  Pages[1] = "Physics (CBPC)"
  Pages[2] = "Devious Stuff"
  Pages[3] = "Sex Settings"
EndFunction

Event OnVersionUpdate(int newVersion)
  if newVersion != CurrentVersion
    InitializeMCM()
  EndIf
EndEvent

Event OnPageReset(string page)
  Main.Info("OnPageReset(" + page + ")")
  if page == "" || page == "General"
    RenderGeneralPage()
  elseIf page == "Physics (CBPC)"
    RenderPhysicsPage()
  elseif page == "Devious Stuff"
    RenderDeviousPage()
  elseif page == "Sex Settings"
    RenderSexPage()
  Else
    RenderPlaceholderPage()
  EndIf
EndEvent

Function RenderGeneralPage()
  SetCursorFillMode(TOP_TO_BOTTOM)		
  AddHeaderOption("LLM Settings")
  requestResponseCooldownOID = AddSliderOption("LLM Response Request Cooldown", requestResponseCooldown, "{1}")
EndFunction

Function RenderPhysicsPage()
  SetCursorFillMode(TOP_TO_BOTTOM)		
  AddHeaderOption("CBPC Settings")
  UseCBPCOID = AddToggleOption("Enable CBPC", useCBPC.GetValueInt() == 1)
  cbpcDisableSelfTouchOID = AddToggleOption("Disable Self Touch", cbpcDisableSelfTouch)
  cbpcDisableSelfAssTouchOID = AddToggleOption("Disable Self Ass Touch", cbpcDisableSelfAssTouch)
  collisionCooldownOID = AddSliderOption("Physics Calculation Rate", collisionCooldown, "{1}")
  collisionSpeechCooldownOID = AddSliderOption("Physics Speech Comment Rate", collisionSpeechCooldown, "{1}")
  collisionSexCooldownOID = AddSliderOption("Physics Speech Comment Rate (Sex)", collisionSexCooldown, "{1}")
  cbpcSelfTouchThresholdOID = AddSliderOption("Self Touch Threshold", cbpcSelfTouchThreshold, "{1}")
  cbpcOtherTouchThresholdOID = AddSliderOption("NPC Touch Threshold", cbpcOtherTouchThreshold, "{1}")
  collisionCooldownOID = AddSliderOption("Physics Calculation Rate", collisionCooldown, "{1}")
EndFunction


Function RenderSexPage()
  SetCursorFillMode(TOP_TO_BOTTOM)
  AddHeaderOption("Arousal Settings ")
  arousalForSexOID = AddSliderOption("Arousal Threshold for Sex", arousalForSex, "{0}")
  arousalForHarassOID = AddSliderOption("Arousal Threshold for Flirting/Harassment", arousalForHarass, "{0}")
  confirmSexOID = AddToggleOption("Ask before a sex scene is initiated", confirmSex)
EndFunction


Function RenderDeviousPage()
  SetCursorFillMode(TOP_TO_BOTTOM)		
  AddHeaderOption("DD Settings")
  if (!devious.HasDD())
    AddHeaderOption("Devious Devices not detected")
    return
  EndIf
  AllowDeviceLockOID = AddToggleOption("Allow the LLM to lock devices on actors", allowDeviceLock)
  AllowDeviceUnlockOID = AddToggleOption("Allow the LLM to unlock devices on actors", allowDeviceUnlock)
EndFunction

Function RenderPlaceholderPage()
  AddHeaderOption("Not Yet Implemented") 
EndFunction


Function SetGlobalToggle(int oid, GlobalVariable var, bool value)
  if value
    var.SetValue(1)
  else
    var.SetValue(0)
  EndIf
  SetToggleOptionValue(oid, var.GetValueInt() == 1)
EndFunction

Function ToggleGlobal(int oid, GlobalVariable var)
  if var.GetValueInt() == 1
    var.SetValue(0)
  else
    var.SetValue(1)
  EndIf
  SetToggleOptionValue(oid, var.GetValueInt() == 1)
EndFunction


Function StoreConfig(string var, string value)
  actor Player = Game.GetPlayer()
  string playerName = player.GetActorBase().GetName()
  if aiff.HasAIFF()
    aiff.SetActorVariable(Player, var, value)
  EndIf
EndFunction

Function StoreAllConfigs()
  StoreConfig("allowDeviceLock", allowDeviceLock)
  StoreConfig("allowDeviceUnlock", allowDeviceUnlock)
  StoreConfig("arousalForSex", arousalForSex)
  StoreConfig("arousalForHarass", arousalForHarass)
EndFunction

Event OnOptionSelect(int oid)
  if oid == UseCBPCOID
    toggleGlobal(oid, useCBPC)
    Debug.Notification("CBPC setting changed. Save/Reload to take effect")
  elseif oid == cbpcDisableSelfTouchOID
    cbpcDisableSelfTouch = !cbpcDisableSelfTouch
    SetToggleOptionValue(oid, cbpcDisableSelfTouch)
  elseif oid == cbpcDisableSelfAssTouchOID
    cbpcDisableSelfAssTouch = !cbpcDisableSelfAssTouch
    SetToggleOptionValue(oid, cbpcDisableSelfAssTouch)
  elseif oid == allowDeviceLockOID
    allowDeviceLock = !allowDeviceLock
    SetToggleOptionValue(oid, allowDeviceLock)
    StoreConfig("allowDeviceLock", allowDeviceLock)
  elseif oid == allowDeviceUnlockOID
    allowDeviceUnlock = !allowDeviceUnlock
    SetToggleOptionValue(oid, allowDeviceUnlock)
    StoreConfig("allowDeviceUnlock", allowDeviceUnlock)
  elseif oid == confirmSexOID
    confirmSex = !confirmSex
    SetToggleOptionValue(oid, confirmSex)
  EndIf
EndEvent


Event OnOptionDefault(int oid)
  if oid == UseCBPCOID
    SetGlobalToggle(oid, UseCBPC, true)
    Debug.Notification("CBPC setting changed. Save/Reload to take effect")
  elseif oid == cbpcDisableSelfTouchOID
    cbpcDisableSelfTouch = cbpcDisableSelfTouchDefault
    SetToggleOptionValue(oid, cbpcDisableSelfTouchDefault)
  elseif oid == cbpcDisableSelfAssTouchOID
    cbpcDisableSelfAssTouch = cbpcDisableSelfAssTouchDefault
    SetToggleOptionValue(oid, cbpcDisableSelfAssTouchDefault)
  elseif oid == cbpcSelfTouchThresholdOID
    cbpcSelfTouchThreshold = cbpcSelfTouchThresholdDefault
    SetSliderOptionValue(cbpcSelfTouchThresholdOID, cbpcSelfTouchThresholdDefault, "{1}")
  elseif oid == cbpcOtherTouchThresholdOID
    cbpcOtherTouchThreshold = cbpcOtherTouchThresholdDefault
    SetSliderOptionValue(cbpcOtherTouchThresholdOID, cbpcOtherTouchThresholdDefault, "{1}")
  elseif oid ==  collisionCooldownOID
    collisionCooldown = collisionCooldownDefault
    SetSliderOptionValue(collisionCooldownOID, collisionCooldownDefault, "{1}")
  elseif oid ==  collisionSpeechCooldownOID
    collisionSpeechCooldown = collisionSpeechCooldownDefault
    SetSliderOptionValue(collisionSpeechCooldownOID, collisionSpeechCooldownDefault, "{1}")
  elseif oid ==  collisionSexCooldownOID
    collisionSexCooldown = collisionSexCooldownDefault
    SetSliderOptionValue(collisionSexCooldownOID, collisionSexCooldownDefault, "{1}")
  elseif oid ==  allowDeviceLockOID
    allowDeviceLock = allowDeviceLockDefault
    SetToggleOptionValue(oid, allowDeviceLock)
    StoreConfig("allowDeviceLock", allowDeviceLock)
  elseif oid ==  allowDeviceUnlockOID
    allowDeviceUnlock = allowDeviceUnlockDefault
    SetToggleOptionValue(oid, allowDeviceUnlock)
    StoreConfig("allowDeviceUnlock", allowDeviceUnlock)
  elseif oid == requestResponseCooldownOID
    requestResponseCooldown = requestResponseCooldownDefault
    SetSliderOptionValue(requestResponseCooldownOID, requestResponseCooldownDefault, "{1}")
  elseif oid ==  arousalForSexOID
    arousalForSex = arousalForSexDefault
    SetSliderOptionValue(arousalForSexOID, arousalForSexDefault, "{0}")
  elseif oid ==  arousalForHarassOID
    arousalForHarass = arousalForHarassDefault
    SetSliderOptionValue(arousalForHarassOID, arousalForHarassDefault, "{0}")
  elseif oid ==  confirmSexOID
    confirmSex = confirmSexDefault
    SetToggleOptionValue(oid, confirmSex)
  EndIf
EndEvent


Event OnOptionHighlight(int oid)
  if oid == UseCBPCOID
    SetInfoText("Enables or disables CBPC globally. Requires save/reload to take effect")
  elseif oid == cbpcDisableSelfTouchOID
    SetInfoText("Enables or disables collision detection on self")
  elseif oid == cbpcDisableSelfAssTouchOID
    SetInfoText("Enables or disables collision detection on one's own ass. Useful to avoid detection events in VR")
  elseif oid == cbpcOtherTouchThresholdOID
    SetInfoText("How long (Cumulatively) within a given period a part must be touched for collision to register for touching others")
  elseif oid == cbpcSelfTouchThresholdOID
    SetInfoText("How long (Cumulatively) within a given period a part must be touched for collision to register for touching oneself")
  elseif oid == collisionCooldownOID
    SetInfoText("How often physics are calculated in seconds. Lower = more responsive, higher = less script load")
  elseif oid == collisionSpeechCooldownOID
    SetInfoText("How often the AI should be prompted to react to physics in seconds (outside of sex)")
  elseif oid == collisionSexCooldownOID
    SetInfoText("How often the AI should be prompted to react to physics in seconds (during sex)")
  elseif oid == allowDeviceLockOID
    SetInfoText("Should the AI be allowed to lock devices on actors?")
  elseif oid == allowDeviceUnlockOID
    SetInfoText("Should the AI be allowed to unlock devices from actors?")
  elseif oid == requestResponseCooldownOID
    SetInfoText("The minimum time in seconds inbetween requests for the LLM to react to an in-game event")
  elseif oid == arousalForSexOID
    SetInfoText("Minimum Arousal level required for the Sex related actions to be exposed to the LLM")
  elseif oid == arousalForHarassOID
    SetInfoText("Minimum Arousal level required for actions like spanking, groping, kissing to be exposed to the LLM")
  elseif oid == confirmSexOID
    SetInfoText("Show a confirmation message before sex scenes start")
  EndIf
EndEvent

Event OnOptionSliderOpen(int oid)
  if oid == cbpcSelfTouchThresholdOID
    SetSliderDialogStartValue(cbpcSelfTouchThreshold)
    SetSliderDialogDefaultValue(cbpcSelfTouchThresholdDefault)
    SetSliderDialogRange(1,100)
    SetSliderDialogInterval(1)
  elseif oid == cbpcOtherTouchThresholdOID
    SetSliderDialogStartValue(cbpcOtherTouchThreshold)
    SetSliderDialogDefaultValue(cbpcOtherTouchThresholdDefault)
    SetSliderDialogRange(1,100)
    SetSliderDialogInterval(1)
  elseif oid == collisionCooldownOID
    SetSliderDialogStartValue(collisionCooldown)
    SetSliderDialogDefaultValue(collisionCooldownDefault)
    SetSliderDialogRange(1, 100)
    SetSliderDialogInterval(0.5)
  elseif oid == collisionSpeechCooldownOID
    SetSliderDialogStartValue(collisionSpeechCooldown)
    SetSliderDialogDefaultValue(collisionSpeechCooldownDefault)
    SetSliderDialogRange(1, 100)
    SetSliderDialogInterval(0.5)
  elseif oid == collisionSexCooldownOID
    SetSliderDialogStartValue(collisionSexCooldown)
    SetSliderDialogDefaultValue(collisionSexCooldownDefault)
    SetSliderDialogRange(1, 100)
    SetSliderDialogInterval(0.5)
  elseif oid == requestResponseCooldownOID
    SetSliderDialogStartValue(requestResponseCooldown)
    SetSliderDialogDefaultValue(requestResponseCooldownDefault)
    SetSliderDialogRange(4, 60)
    SetSliderDialogInterval(0.5)
  elseif oid == arousalForSexOID
    SetSliderDialogStartValue(arousalForSex)
    SetSliderDialogDefaultValue(arousalForSexDefault)
    SetSliderDialogRange(1, 100)
    SetSliderDialogInterval(1.0)
  elseif oid == arousalForHarassOID
    SetSliderDialogStartValue(arousalForHarass)
    SetSliderDialogDefaultValue(arousalForHarassDefault)
    SetSliderDialogRange(1, 100)
    SetSliderDialogInterval(1.0)
  EndIf
EndEvent



Event OnOptionSliderAccept(int oid, float value)
  if oid == cbpcSelfTouchThresholdOID
    cbpcSelfTouchThreshold = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == cbpcOtherTouchThresholdOID
    cbpcOtherTouchThreshold = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == collisionCooldownOID
    collisionCooldown = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == collisionSpeechCooldownOID
    collisionSpeechCooldown = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == collisionSexCooldownOID
    collisionSexCooldown = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == requestResponseCooldownOID
    requestResponseCooldown = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == arousalForSexOID
    arousalForSex = value
    SetSliderOptionValue(oid, value, "{0}")
    StoreConfig("arousalForSex", arousalForSex)
  elseif oid == arousalForHarassOID
    arousalForHarass = value
    SetSliderOptionValue(oid, value, "{0}")
    StoreConfig("arousalForHarass", arousalForHarass)
  EndIf  
EndEvent