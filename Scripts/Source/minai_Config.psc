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

int aOIDMap ; Jmap for storing action oid's

int resetActionsOID
int bulkEnabledOID
int bulkIntervalOID
int bulkExponentOID
int bulkMaxIntervalOID
int bulkDecayWindowOID

  
; Legacy globals
GlobalVariable useCBPC
GlobalVariable minai_UseOstim

string currentActionsPage


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

bool Property bulkEnabled = True Auto

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
  Main.Info("Initializing MCM ( " + JMap.Count(aiff.actionRegistry) + " actions in registry).")
  useCBPC = Game.GetFormFromFile(0x0910, "MinAI.esp") as GlobalVariable
  if aOIDMap != 0
    string[] actions = JMap.allKeysPArray(aOIDMap)
    int i = 0
    while i < actions.Length
      JMap.SetObj(aOIDMap, actions[i], JValue.Release(JMap.GetObj(aOIDMap, actions[i])))
      i += 1
    EndWhile
    aOIDMap = JValue.Release(aOIDMap)
  EndIf
  aOIDMap = JMap.Object()
  JValue.Retain(aOIDMap)
  string[] actions = JMap.allKeysPArray(aiff.actionRegistry)
  int i = 0
  while i < actions.Length
    int aOID = JMap.Object()
    JValue.Retain(aOID)
    JMap.SetInt(aOID, "enabled", 0)
    JMap.SetInt(aOID, "interval", 0)
    JMap.SetInt(aOID, "exponent", 0)
    JMap.SetInt(aOID, "maxInterval", 0)
    JMap.SetInt(aOID, "decayWindow", 0)
    Main.Info("Initializing aOID map for " + actions[i] + " : " + aOID)
    JMap.SetObj(aOIDMap, actions[i], aOID)
    i += 1
  EndWhile
  SetupPages()
EndFunction

int Function GetVersion()
  return 8 ; mcm menu version
EndFunction

Function SetupPages()
  Pages = new string[13]
  Pages[0] = "General"
  Pages[1] = "Physics (CBPC)"
  Pages[2] = "Devious Stuff"
  Pages[3] = "Sex Settings"
  Pages[4] = "Action Registry (General)"
  Pages[5] = "Action Registry (Survival)"
  Pages[6] = "Action Registry (Followers)"
  Pages[7] = "Action Registry (Arousal)"
  Pages[8] = "Action Registry (Sex (1))"
  Pages[9] = "Action Registry (Sex (2))"
  Pages[10] = "Action Registry (Sex (3))"
  Pages[11] = "Action Registry (Devious Stuff)"
  Pages[12] = "Action Registry (Devious Followers)"
EndFunction

Event OnVersionUpdate(int newVersion)
  if newVersion != CurrentVersion || aOIDMap == 0 || JMap.Count(aOIDMap) == 0
    InitializeMCM()
  EndIf
EndEvent

Event OnPageReset(string page)
  Main.Info("OnPageReset(" + page + ")")
  if aOIDMap == 0 || JMap.Count(aOIDMap) == 0
    InitializeMCM()
  EndIf
  if page == "" || page == "General"
    RenderGeneralPage()
  elseIf page == "Physics (CBPC)"
    RenderPhysicsPage()
  elseif page == "Devious Stuff"
    RenderDeviousPage()
  elseif page == "Sex Settings"
    RenderSexPage()
  elseif page == "Action Registry (General)"
    RenderActionsPage("General")
  elseif page == "Action Registry (Survival)"
    RenderActionsPage("Survival")
  elseif page == "Action Registry (Followers)"
    RenderActionsPage("Followers")
  elseif page == "Action Registry (Sex (1))"
    RenderActionsPage("Sex1")
  elseif page == "Action Registry (Sex (2))"
    RenderActionsPage("Sex2")
  elseif page == "Action Registry (Sex (3))"
    RenderActionsPage("Sex3")
  elseif page == "Action Registry (Sex (4))"
    RenderActionsPage("Sex4")
  elseif page == "Action Registry (Arousal)"
    RenderActionsPage("Arousal")
  elseif page == "Action Registry (Devious Stuff)"
    RenderActionsPage("Devious Stuff")
  elseif page == "Action Registry (Devious Followers)"
    RenderActionsPage("Devious Followers")
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


Function RenderActionsPage(string mcmPageToRender)
  currentActionsPage=mcmPageToRender
  SetCursorFillMode(TOP_TO_BOTTOM)
  AddHeaderOption("Bulk Manipulation Options")
  bulkEnabledOID = AddToggleOption("Bulk Set Enable/Disable", bulkEnabled)
  bulkIntervalOID = AddSliderOption("Bulk Set Interval", 0, "{1}")
  bulkExponentOID = AddSliderOption("Bulk Set Exponent", 2, "{1}")
  bulkMaxIntervalOID = AddSliderOption("Bulk Set Maximum Interval", 5, "{0}")
  bulkDecayWindowOID = AddSliderOption("Decay Window", 60, "{1}")

  int numActionsForPage = 0
  int i = 0
  string[] actions = JMap.allKeysPArray(aOIDMap)
  while i < actions.Length
    int actionObj = JMap.getObj(aiff.actionRegistry, actions[i])
    string mcmPage = JMap.getStr(actionObj, "mcmPage")
    if mcmPage == mcmPageToRender
      numActionsForPage += 1
    EndIf
    i += 1
  EndWhile
  i = 0
  int numActionsForPageSoFar = 0
  while i < actions.Length
    if (numActionsForPageSoFar  == ( numActionsForPage / 2))
      SetCursorPosition(1) ; Move cursor to top right position
    EndIf
    int actionObj = JMap.getObj(aiff.actionRegistry, actions[i])
    string mcmPage = JMap.getStr(actionObj, "mcmPage")
    if mcmPage == mcmPageToRender
      numActionsForPageSoFar += 1
      string actionName = JMap.getStr(actionObj, "name")
      string mcmName = JMap.getStr(actionObj, "mcmName")
      string mcmDesc = JMap.getStr(actionObj, "mcmDesc")
      
      int enabled = JMap.getInt(actionObj, "enabled")
      float interval = JMap.getFlt(actionObj, "interval")
      float exponent = JMap.getFlt(actionObj, "exponent")
      int maxInterval = JMap.getInt(actionObj, "maxInterval")
      float decayWindow = JMap.getFlt(actionObj, "decayWindow")
      bool hasMod = JMap.GetInt(actionObj, "hasMod") == 1
      int aOID = JMap.GetObj(aOIDMap, actions[i])
      if aOID == 0
        Main.Error("Could not find aOID for " + actions[i] + " total: " + JMap.Count(aOIDMap))
      EndIf
      AddHeaderOption(mcmName + ": " + mcmDesc)
      if !hasMod
        AddTextOption("Disabled - Missing Mod Dependency", "DISABLED")
      Else
        int enabledOID = AddToggleOption("Enable/Disable", enabled == 1)
        JMap.SetInt(aOID, "enabled", enabledOID)
        Main.Info(actions[i] + ": aoid enabled  = " + JMap.GetInt(aOID, "enabled") + " oid = " + enabledOID + " total: " + JMap.Count(aOIDMap))
        JMap.SetInt(aOID, "interval", AddSliderOption("Interval", interval, "{1}"))
        JMap.SetInt(aOID, "exponent", AddSliderOption("Exponent", exponent, "{1}"))
        JMap.SetInt(aOID, "maxInterval", AddSliderOption("Maximum Interval", maxInterval, "{0}"))
        JMap.SetInt(aOID, "decayWindow", AddSliderOption("Decay Window", decayWindow, "{1}"))
        JMap.SetObj(aOIDMap, actions[i], aOID)
      EndIf
    EndIf
    i += 1
  EndWhile
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
  string[] actions = JMap.allKeysPArray(aOIDMap)
  int i = 0
  bool changedAction = False
  if oid == bulkEnabledOID
    bulkEnabled = !bulkEnabled
    SetToggleOptionValue(oid, bulkEnabled)
  EndIf
  while i < actions.Length
    int aOID = JMap.GetObj(aOIDMap, actions[i])
    int actionObj = JMap.getObj(aiff.actionRegistry, actions[i])
    string mcmPage = JMap.getStr(actionObj, "mcmPage")
    if aOID == 0
      Main.Error("Could not find aOID for " + actions[i] + " total: " + JMap.Count(aOIDMap))
    EndIf
    if oid == JMap.GetInt(aOID, "enabled")
      if JMap.getInt(actionObj, "enabled") == 1
        JMap.setInt(actionObj, "enabled", 0)
      else
        JMap.setInt(actionObj, "enabled", 1)
      EndIf
      JMap.SetObj(aiff.actionRegistry, actions[i], actionObj)
      SetToggleOptionValue(oid, JMap.GetInt(actionObj, "enabled") == 1)
    endIf
    if oid == bulkEnabledOID && mcmPage == currentActionsPage
      if bulkEnabled
        JMap.setInt(actionObj, "enabled", 1)
      else
        JMap.setInt(actionObj, "enabled", 0)
      endif
      JMap.SetObj(aiff.actionRegistry, actions[i], actionObj)
      SetToggleOptionValue(oid, JMap.GetInt(actionObj, "enabled") == 1)
      changedAction = True
    EndIf
    i += 1
  EndWhile
  if oid == bulkEnabledOID
    ForcePageReset()
  EndIf
  if changedAction
    aiff.ResetAllActionBackoffs()
  EndIf
EndEvent


Event OnOptionDefault(int oid)
  bool changedAction = False
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
  string[] actions = JMap.allKeysPArray(aOIDMap)
  int i = 0
  while i < actions.Length
    int aOID = JMap.GetObj(aOIDMap, actions[i])
    if aOID == 0
      Main.Error("Could not find aOID for " + actions[i] + " total: " + JMap.Count(aOIDMap))
    EndIf
    if (oid == JMap.getInt(aOID, "enabled") || oid == JMap.getInt(aOID, "interval") || oid == JMap.getInt(aOID, "exponent") || oid == JMap.getInt(aOID, "maxInterval") || oid == JMap.getInt(aOID, "decayWindow"))
      aiff.ResetAction(actions[i])
      changedAction = True
    EndIf
    i += 1
  EndWhile
  if changedAction
    aiff.ResetAllActionBackoffs()
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
  string[] actions = JMap.allKeysPArray(aOIDMap)
  int i = 0
  while i < actions.Length
    int aOID = JMap.GetObj(aOIDMap, actions[i])
    if aOID == 0
      Main.Error("Could not find aOID for " + actions[i] + " total: " + JMap.Count(aOIDMap))
    EndIf
    if oid == JMap.getInt(aOID, "enabled")
      SetInfoText("Enable or disable the action. This will prevent it from being exposed to the LLM")
    elseif oid == JMap.getInt(aOID, "interval")
      SetInfoText("The cooldown (in seconds) inbetween uses of the action")
    elseif oid == JMap.getInt(aOID, "exponent")
      SetInfoText("The exponent applied to the interval's cooldown for uses of this action")
    elseif oid == JMap.getInt(aOID, "maxInterval")
      SetInfoText("The cap on the maximum exponent that will be applied to the interval for uses of this action")
    elseif oid == JMap.getInt(aOID, "decayWindow")
      SetInfoText("The duration of time which must pass without the action being used for the cooldown to return to the base value")
    EndIf
    i += 1
  EndWhile
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
  string[] actions = JMap.allKeysPArray(aOIDMap)
  int i = 0
  while i < actions.Length
    int aOID = JMap.GetObj(aOIDMap, actions[i])
    if aOID == 0
      Main.Error("Could not find aOID for " + actions[i] + " total: " + JMap.Count(aOIDMap))
    EndIf
    int actionObj = JMap.GetObj(aiff.actionRegistry, actions[i])
    if oid == JMap.getInt(aOID, "interval")
      SetSliderDialogStartValue(JMap.getFlt(actionObj, "interval"))
      SetSliderDialogDefaultValue(JMap.getFlt(actionObj, "intervalDefault"))
      SetSliderDialogRange(0, 30)
      SetSliderDialogInterval(0.5)
    elseif oid == bulkIntervalOID
      SetSliderDialogStartValue(0)
      SetSliderDialogDefaultValue(0)
      SetSliderDialogRange(0, 30)
      SetSliderDialogInterval(0.5)
    elseif oid == JMap.getInt(aOID, "exponent") || oid == bulkExponentOID
      SetSliderDialogStartValue(JMap.getFlt(actionObj, "exponent"))
      SetSliderDialogDefaultValue(JMap.getFlt(actionObj, "exponentDefault"))
      SetSliderDialogRange(1, 10)
      SetSliderDialogInterval(0.5)
    elseif oid == bulkExponentOID
      SetSliderDialogStartValue(2)
      SetSliderDialogDefaultValue(2)
      SetSliderDialogRange(1, 10)
      SetSliderDialogInterval(0.5)
    elseif oid == JMap.getInt(aOID, "maxInterval") || oid == bulkMaxIntervalOID
      SetSliderDialogStartValue(JMap.getInt(actionObj, "maxInterval"))
      SetSliderDialogDefaultValue(JMap.getInt(actionObj, "maxIntervalDefault"))
      SetSliderDialogRange(1, 10)
      SetSliderDialogInterval(1)
    elseif oid == bulkMaxIntervalOID
      SetSliderDialogStartValue(5)
      SetSliderDialogDefaultValue(5)
      SetSliderDialogRange(1, 10)
      SetSliderDialogInterval(1)
    elseif oid == JMap.getInt(aOID, "decayWindow") || oid == bulkDecayWindowOID
      SetSliderDialogStartValue(JMap.getFlt(actionObj, "decayWindow"))
      SetSliderDialogDefaultValue(JMap.getFlt(actionObj, "decayWindowDefault"))
      SetSliderDialogRange(0, 600)
      SetSliderDialogInterval(1)
    elseif oid == bulkDecayWindowOID
      SetSliderDialogStartValue(60)
      SetSliderDialogDefaultValue(60)
      SetSliderDialogRange(0, 600)
      SetSliderDialogInterval(1)
    EndIf
    i += 1
  EndWhile
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
    string[] actions = JMap.allKeysPArray(aOIDMap)
  int i = 0
  bool changedAction = False
  while i < actions.Length
    int aOID = JMap.GetObj(aOIDMap, actions[i])
    int actionObj = JMap.GetObj(aiff.actionRegistry, actions[i])
    string mcmPage = JMap.getStr(actionObj, "mcmPage")
    if aOID == 0
      Main.Error("Could not find aOID for " + actions[i] + " total: " + JMap.Count(aOIDMap))
    EndIf
    if oid == JMap.getInt(aOID, "interval") ||  (oid == bulkIntervalOID && mcmPage == currentActionsPage)
      SetSliderOptionValue(oid, value, "{1}")
      JMap.SetInt(actionObj, "interval", value as Int)
      JMap.SetObj(aiff.actionRegistry, actions[i], actionObj)
      changedAction = True
    elseif oid == JMap.getInt(aOID, "exponent") ||  (oid == bulkExponentOID && mcmPage == currentActionsPage)
      SetSliderOptionValue(oid, value, "{1}")
      JMap.SetFlt(actionObj, "exponent", value)
      JMap.SetObj(aiff.actionRegistry, actions[i], actionObj)
      changedAction = True
    elseif oid == JMap.getInt(aOID, "maxInterval") ||  (oid == bulkMaxIntervalOID && mcmPage == currentActionsPage)
      SetSliderOptionValue(oid, value, "{0}")
      JMap.SetFlt(actionObj, "maxInterval", value)
      JMap.SetObj(aiff.actionRegistry, actions[i], actionObj)
      changedAction = True
    elseif oid == JMap.getInt(aOID, "decayWindow") ||  (oid == bulkDecayWindowOID && mcmPage == currentActionsPage)
      SetSliderOptionValue(oid, value, "{1}")
      JMap.setFlt(actionObj, "decayWindow", value)
      JMap.SetObj(aiff.actionRegistry, actions[i], actionObj)
      changedAction = True
    EndIf
    i += 1
  EndWhile
  if oid == bulkIntervalOID || oid == bulkExponentOID || oid == bulkMaxIntervalOID || oid == bulkDecayWindowOID
    ForcePageReset()
  EndIf
  if changedAction
    aiff.ResetAllActionBackoffs()
  EndIf
EndEvent