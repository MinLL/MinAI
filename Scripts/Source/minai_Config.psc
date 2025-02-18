Scriptname minai_Config extends SKI_ConfigBase Conditional

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
bool Property ActionRegistryIsDirty = false Auto
minai_SapienceController sapience

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
int disableAIAnimationsOID
int useSapienceOID
int radiantDialogueFrequencyOID
int radiantDialogueChanceOID
int autoUpdateDiaryOID
int updateNarratorDiaryOID
int enableAISexOID
int genderWeightCommentsOID
int commentsRateOID
int forceOrgasmCommentOID
int forcePostSceneCommentOID
int prioritizePlayerThreadOID
int enableAmbientCommentsOID
int minRadianceRechatsOID
int maxRadianceRechatsOID
int maxThreadsOID
int allowSexTransitionsOID
int allowActorsToJoinSexOID
int aOIDMap ; Jmap for storing action oid's
int aCategoryMap ; Jmap for storing action categories

int actionEnabledOID
int actionIntervalOID
int actionExponentOID
int actionMaxIntervalOID
int actionDecayWindowOID

int testActionsOID
int addSpellsOID
int removeSpellsOID
int toggleCombatDialogueOID
int toggleSapienceOID

int singKeyOID          ; New OID for sing keybind
int narratorKeyOID      ; New OID for narrator keybind
int narratorTextKeyOID  ; New OID for narrator text input keybind

; Add new OID definitions near other key OIDs
int roleplayKeyOID      ; New OID for roleplay voice keybind
int roleplayTextKeyOID  ; New OID for roleplay text input keybind

; Key properties
int Property toggleSapienceKey = -1 Auto
int Property singKey = -1 Auto          ; New property for sing key
int Property narratorKey = -1 Auto      ; New property for narrator key
int Property narratorTextKey = -1 Auto  ; New property for narrator text input key

; Add new key properties
int Property roleplayKey = -1 Auto          ; Property for roleplay voice key
int Property roleplayTextKey = -1 Auto      ; Property for roleplay text key

; Legacy globals
GlobalVariable useCBPC
GlobalVariable minai_UseOstim
GlobalVariable minai_SapienceEnabled

string currentAction
string currentCategory

; New configs
Bool cbpcDisableSelfAssTouchDefault = True
Bool Property cbpcDisableSelfAssTouch = True Auto

Bool cbpcDisableSelfTouchDefault = False 
Bool Property cbpcDisableSelfTouch = False Auto

float cbpcOtherTouchThresholdDefault = 4.0
float Property cbpcOtherTouchThreshold = 4.0 Auto

float cbpcSelfTouchThresholdDefault = 4.0 
float Property cbpcSelfTouchThreshold = 4.0 Auto

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

Bool disableAIAnimationsDefault = False
Bool Property disableAIAnimations = False Auto

Float radiantDialogueFrequencyDefault = 30.0
Float Property radiantDialogueFrequency = 30.0 Auto

Float radiantDialogueChanceDefault = 50.0
Float Property radiantDialogueChance = 50.0 Auto

bool Property bulkEnabled = True Auto

bool autoUpdateDiaryDefault = False
bool Property autoUpdateDiary = False Auto

bool updateNarratorDiaryDefault = False
bool Property updateNarratorDiary = False Auto

bool enableAISexDefault = true
bool Property enableAISex = False Auto

float genderWeightCommentsDefault = 50.0
float Property genderWeightComments = 50.0 Auto

float commentsRateDefault = 15.0
float Property commentsRate = 15.0 Auto

bool forceOrgasmCommentDefault = true
bool Property forceOrgasmComment = true Auto

bool forcePostSceneCommentDefault = true
bool Property forcePostSceneComment = true Auto

bool prioritizePlayerThreadDefault = true
bool Property prioritizePlayerThread = true Auto

bool enableAmbientCommentsDefault = true
bool Property enableAmbientComments = true Auto

int minRadianceRechatsDefault = 3
int Property minRadianceRechats = 3 Auto

int maxRadianceRechatsDefault = 5
int Property maxRadianceRechats = 5 Auto

float maxThreadsDefault = 5.0
float Property maxThreads = 5.0 Auto

bool allowSexTransitionsDefault = False
bool Property allowSexTransitions = False Auto

bool allowActorsToJoinSexDefault = False
bool Property allowActorsToJoinSex = False Auto

bool toggleCombatDialogueDefault = True
bool property toggleCombatDialogue = True Auto

bool updateNarratorProfileDefault = False
bool Property updateNarratorProfile = False Auto

int updateNarratorProfileOID

bool preserveQueueDefault = True
bool Property preserveQueue = True Auto

int preserveQueueOID

bool trackVictimAwarenessDefault = True
bool Property trackVictimAwareness = True Auto

int trackVictimAwarenessOID

bool enableConsoleLoggingDefault = True
bool Property enableConsoleLogging = True Auto

int enableConsoleLoggingOID

; Add near other property declarations
bool disableSapienceInStealthDefault = False
bool Property disableSapienceInStealth = False Auto

int disableSapienceInStealthOID

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
  sapience = Game.GetFormFromFile(0x091D, "MinAI.esp") as minai_SapienceController
  minai_SapienceEnabled = Game.GetFormFromFile(0x091A, "MinAI.esp") as GlobalVariable
  Main.Info("Initializing MCM ( " + JMap.Count(aiff.actionRegistry) + " actions in registry).")
  useCBPC = Game.GetFormFromFile(0x0910, "MinAI.esp") as GlobalVariable
  ActionRegistryIsDirty = False
  SetupPages()
EndFunction

int Function GetVersion()
  return 15 ; mcm menu version
EndFunction

Function SetupPages()
  if sex.IsNSFW()
    Pages = new string[5]
    Pages[0] = "General"
    Pages[1] = "Physics (CBPC)"
    Pages[2] = "Devious Stuff"
    Pages[3] = "Sex Settings"
    Pages[4] = "Action Registry"
  Else
    Pages = new string[3]
    Pages[0] = "General"
    Pages[1] = "Physics (CBPC)"
    Pages[2] = "Action Registry"
  EndIf
EndFunction

Event OnVersionUpdate(int newVersion)
  InitializeMCM()
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
  elseif page == "Action Registry"
    RenderActionsPage();
  else
    RenderPlaceholderPage()
  EndIf
EndEvent

Function RenderGeneralPage()
  SetCursorFillMode(TOP_TO_BOTTOM)		
  AddHeaderOption("LLM Settings")
  autoUpdateDiaryOID = AddToggleOption("Automatically Update Follower Diaries", autoUpdateDiary)
  updateNarratorDiaryOID = AddToggleOption("Update Narrator Diary on Sleep", updateNarratorDiary)
  updateNarratorProfileOID = AddToggleOption("Update Narrator Dynamic Profile on Sleep", updateNarratorProfile)
  requestResponseCooldownOID = AddSliderOption("LLM Response Request Cooldown", requestResponseCooldown, "{1}")
  AddHeaderOption("Sapience Settings")
  useSapienceOID = AddToggleOption("Enable Sapience", minai_SapienceEnabled.GetValueInt() == 1)
  disableSapienceInStealthOID = AddToggleOption("Disable Sapience While Sneaking", disableSapienceInStealth)
  radiantDialogueFrequencyOID = AddSliderOption("Radiant Dialogue (NPC -> NPC) Frequency", radiantDialogueFrequency, "{1}")
  radiantDialogueChanceOID = AddSliderOption("Radiant Dialogue (NPC -> NPC) Chance", radiantDialogueChance, "{1}")
  minRadianceRechatsOID = AddSliderOption("Minimum Radiance Rechats", minRadianceRechats, "{0}")
  maxRadianceRechatsOID = AddSliderOption("Maximum Radiance Rechats", maxRadianceRechats, "{0}")
  SetCursorPosition(1) ; Move cursor to top right position
  AddHeaderOption("General Settings")
  toggleCombatDialogueOID = AddToggleOption("CHIM Config - Allow Dialogue during Combat", toggleCombatDialogue)
  preserveQueueOID = AddToggleOption("CHIM Config - Preserve Dialogue Queue", preserveQueue)
  addSpellsOID = AddTextOption("General", "Add Spells to Player")
  removeSpellsOID = AddTextOption("General", "Remove Spells from Player")
  toggleSapienceOID = AddKeyMapOption("Toggle Sapience", toggleSapienceKey)
  roleplayKeyOID = AddKeyMapOption("Roleplay Voice", roleplayKey)
  roleplayTextKeyOID = AddKeyMapOption("Roleplay Text", roleplayTextKey)
  ; Disable for now until I finish implementing this
  ; singKeyOID = AddKeyMapOption("Sing", singKey)              ; New keybind option
  narratorKeyOID = AddKeyMapOption("Talk to Narrator", narratorKey)  ; New keybind option
  narratorTextKeyOID = AddKeyMapOption("Type to Narrator", narratorTextKey)
  disableAIAnimationsOID = AddToggleOption("Disable AI-FF Animations", disableAIAnimations)
  AddHeaderOption("Debug")
  enableConsoleLoggingOID = AddToggleOption("Enable Console Logging", enableConsoleLogging)
  testActionsOID = AddTextOption("Debug", "Test Mod Events")
EndFunction

Function RenderPhysicsPage()
  SetCursorFillMode(TOP_TO_BOTTOM)		
  AddHeaderOption("CBPC Settings")
  UseCBPCOID = AddToggleOption("Enable CBPC", useCBPC.GetValueInt() == 1)
  cbpcDisableSelfTouchOID = AddToggleOption("Disable Self Touch", cbpcDisableSelfTouch)
  cbpcDisableSelfAssTouchOID = AddToggleOption("Disable Self Ass Touch", cbpcDisableSelfAssTouch)
  collisionSpeechCooldownOID = AddSliderOption("Physics Speech Comment Rate", collisionSpeechCooldown, "{1}")
  collisionSexCooldownOID = AddSliderOption("Physics Speech Comment Rate (Sex)", collisionSexCooldown, "{1}")
  cbpcSelfTouchThresholdOID = AddSliderOption("Self Touch Threshold", cbpcSelfTouchThreshold, "{1}")
  cbpcOtherTouchThresholdOID = AddSliderOption("NPC Touch Threshold", cbpcOtherTouchThreshold, "{1}")
  collisionCooldownOID = AddSliderOption("Physics Calculation Rate", collisionCooldown, "{1}")
EndFunction


Function RenderSexPage()
  ; left column
  SetCursorFillMode(TOP_TO_BOTTOM)
  AddHeaderOption("Arousal Settings ")
  arousalForSexOID = AddSliderOption("Arousal Threshold for Sex", arousalForSex, "{0}")
  arousalForHarassOID = AddSliderOption("Arousal Threshold for Flirting/Harassment", arousalForHarass, "{0}")
  confirmSexOID = AddToggleOption("Ask before a sex scene is initiated", confirmSex)
  allowSexTransitionsOID = AddToggleOption("Allow Sex Scene Transitions", allowSexTransitions)
  allowActorsToJoinSexOID = AddToggleOption("Allow NPC's to join Ongoing Sex Scenes", allowActorsToJoinSex)
  trackVictimAwarenessOID = AddToggleOption("Track Victim Actor Awareness", trackVictimAwareness)
  AddHeaderOption("NPC Sex Settings")
  enableAISexOID = AddToggleOption("Enable NPC -> NPC Sex", enableAISex)
  ; right column
  SetCursorPosition(1)
  AddHeaderOption("Comments during sex")
  genderWeightCommentsOID = AddSliderOption("Gender weight", genderWeightComments, "{0}")
  commentsRateOID = AddSliderOption("Comments rate", commentsRate)
  prioritizePlayerThreadOID = AddToggleOption("Prioritize comments in player's scene", prioritizePlayerThread)
  forceOrgasmCommentOID = AddToggleOption("Force orgasm comment", forceOrgasmComment)
  forcePostSceneCommentOID = AddToggleOption("Force post scene comment", forcePostSceneComment)
  enableAmbientCommentsOID = AddToggleOption("Enable ambient comments between events", enableAmbientComments)
  maxThreadsOID = AddSliderOption("Max threads", maxThreads)
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


Function RenderAction(int actionObj)
  SetCursorPosition(1)
  string name = JMap.getStr(actionObj, "name")
  string mcmName = JMap.getStr(actionObj, "mcmName")
  string mcmDesc = JMap.getStr(actionObj, "mcmDesc")
  AddHeaderOption(mcmName + ": " + mcmDesc)
  int enabled = JMap.getInt(actionObj, "enabled")
  float interval = JMap.getFlt(actionObj, "interval")
  float exponent = JMap.getFlt(actionObj, "exponent")
  float maxInterval = JMap.getFlt(actionObj, "maxInterval")
  float decayWindow = JMap.getFlt(actionObj, "decayWindow")
  Main.Debug("Rendering action " + name + " with mcmName " + mcmName)
  actionEnabledOID  = AddToggleOption("Enable/Disable", enabled == 1)
  actionIntervalOID = AddSliderOption("Interval", interval, "{1}")
  actionExponentOID = AddSliderOption("Exponent", exponent, "{1}")
  actionMaxIntervalOID = AddSliderOption("Maximum Interval", maxInterval, "{0}")
  actionDecayWindowOID = AddSliderOption("Decay Window", decayWindow, "{0}")

EndFunction

Function RenderActionCategory(string category)
  AddHeaderOption(">>> " + category + " Actions")
  if category != currentCategory
    int categoryOID = AddTextOption("Expand " + category + " Actions", "")
    JMap.setInt(aCategoryMap, category, categoryOID)
    return
  EndIf
  Main.Debug("Rendering category " + category)
  int i = 0
  string[] actions = JMap.allKeysPArray(aiff.actionRegistry)
  while i < actions.Length
    int actionObj = JMap.getObj(aiff.actionRegistry, actions[i])
    string mcmPage = JMap.getStr(actionObj, "mcmPage")
    if mcmPage == category
      string mcmName = JMap.getStr(actionObj, "mcmName")
      string name = JMap.getStr(actionObj, "name")
      int oid = AddTextOption(mcmName, "Edit Action")
      Main.Debug("Adding action " + name + " to aOIDMap with oid " + oid + " and name " + mcmName + " and page " + mcmPage)
      JMap.setInt(aOIDMap, name, oid)
    EndIf
    i += 1
  EndWhile
EndFunction

Function RenderActionsPage()
  if aOIDMap != 0
    aOIDMap = JValue.Release(aOIDMap)
  EndIf
  aOIDMap = JMap.Object()
  JValue.Retain(aOIDMap)
  if aCategoryMap != 0
    aCategoryMap = JValue.Release(aCategoryMap)
  EndIf
  aCategoryMap = JMap.Object()
  JValue.Retain(aCategoryMap)

  SetCursorFillMode(TOP_TO_BOTTOM)
  SetCursorPosition(0)
  string[] actionCategories
  if sex.IsNSFW()
    actionCategories = new String[9];
    actionCategories[0] = "General";
    actionCategories[1] = "Survival";
    actionCategories[2] = "External";
    actionCategories[3] = "Followers";
    actionCategories[4] = "Arousal";
    actionCategories[5] = "Sex";
    actionCategories[6] = "Devious Stuff"; 
    actionCategories[7] = "Devious Followers";
    actionCategories[8] = "Submissive Lola";
  else
    actionCategories = new String[4];
    actionCategories[0] = "General";
    actionCategories[1] = "Survival";
    actionCategories[2] = "External";
    actionCategories[3] = "Followers";
  EndIf
  int i = 0
  while i < actionCategories.Length
    RenderActionCategory(actionCategories[i])
    i += 1
  EndWhile
  if currentAction != ""
    RenderAction(JMap.getObj(aiff.actionRegistry, currentAction))
  EndIf
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
  string playerName = Main.GetActorName(player)
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

Function SetActionEnabled(string actionName, bool value)
  int actionObj = JMap.getObj(aiff.actionRegistry, actionName)
  int tmp = 0
  if value
    tmp = 1
  EndIf
  JMap.setInt(actionObj, "enabled", tmp)
  JMap.SetObj(aiff.actionRegistry, actionName, actionObj)
  aiff.ResetAllActionBackoffs()
EndFunction

Function ToggleActionEnabled(string actionName)
  int actionObj = JMap.getObj(aiff.actionRegistry, actionName)
  int tmp = JMap.getInt(actionObj, "enabled")
  if tmp == 1
    tmp = 0
  else
    tmp = 1
  EndIf
  JMap.setInt(actionObj, "enabled", tmp)
  JMap.SetObj(aiff.actionRegistry, actionName, actionObj)
  aiff.ResetAllActionBackoffs()
EndFunction

Function SetActionInterval(string actionName, float value)
  Main.Debug("Setting action interval for " + actionName + " to " + value)
  int actionObj = JMap.getObj(aiff.actionRegistry, actionName)
  JMap.setFlt(actionObj, "interval", value)
  JMap.SetObj(aiff.actionRegistry, actionName, actionObj)
  aiff.ResetAllActionBackoffs()
EndFunction

Function SetActionExponent(string actionName, float value)
  Main.Debug("Setting action exponent for " + actionName + " to " + value)
  int actionObj = JMap.getObj(aiff.actionRegistry, actionName)
  JMap.setFlt(actionObj, "exponent", value)
  JMap.SetObj(aiff.actionRegistry, actionName, actionObj)
  aiff.ResetAllActionBackoffs()
EndFunction

Function SetActionMaxInterval(string actionName, float value)
  Main.Debug("Setting action max interval for " + actionName + " to " + value)
  int actionObj = JMap.getObj(aiff.actionRegistry, actionName)
  JMap.setFlt(actionObj, "maxInterval", value)
  JMap.SetObj(aiff.actionRegistry, actionName, actionObj)
  aiff.ResetAllActionBackoffs()
EndFunction

Function SetActionDecayWindow(string actionName, float value)
  Main.Debug("Setting action decay window for " + actionName + " to " + value)
  int actionObj = JMap.getObj(aiff.actionRegistry, actionName)
  JMap.setFlt(actionObj, "decayWindow", value)
  JMap.SetObj(aiff.actionRegistry, actionName, actionObj)
  aiff.ResetAllActionBackoffs()
EndFunction

Event OnOptionSelect(int oid)
  Main.Debug("OnOptionSelect(" + oid + ")")
  if oid == enableConsoleLoggingOID
    enableConsoleLogging = !enableConsoleLogging
    SetToggleOptionValue(oid, enableConsoleLogging)
  elseif oid == UseCBPCOID
    toggleGlobal(oid, useCBPC)
    Debug.Notification("CBPC setting changed. Save/Reload to take effect")
  elseif oid == autoUpdateDiaryOID
    autoUpdateDiary = !autoUpdateDiary
    SetToggleOptionValue(oid, autoUpdateDiary)
  elseif oid == updateNarratorDiaryOID
    updateNarratorDiary = !updateNarratorDiary
    SetToggleOptionValue(oid, updateNarratorDiary)
  elseif oid == updateNarratorProfileOID
    updateNarratorProfile = !updateNarratorProfile
    SetToggleOptionValue(oid, updateNarratorProfile)
  elseif oid == enableAISexOID
    enableAISex = !enableAISex
    aiff.SetAISexEnabled(enableAISex)
    SetToggleOptionValue(oid, enableAISex)
  elseif oid == useSapienceOID
    toggleGlobal(oid, minai_SapienceEnabled)
    if minai_SapienceEnabled.GetValueInt() == 1.0
      sapience.StartRadiantDialogue()
    else
      sapience.StopRadiantDialogue()
    EndIf
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
  elseif oid == allowSexTransitionsOID
    allowSexTransitions = !allowSexTransitions
    SetToggleOptionValue(oid, allowSexTransitions)
    aiff.SetActorVariable(game.GetPlayer(), "allowSexTransitions", allowSexTransitions)
  elseif oid == allowActorsToJoinSexOID
    allowActorsToJoinSex = !allowActorsToJoinSex
    SetToggleOptionValue(oid, allowActorsToJoinSex)
  elseif oid == disableAIAnimationsOID
    disableAIAnimations = !disableAIAnimations
    SetToggleOptionValue(oid, disableAIAnimations)
  elseif oid == toggleCombatDialogueOID
    toggleCombatDialogue = !toggleCombatDialogue
    if toggleCombatDialogue
      sapience.EnableCombatDialogue()
    else
      sapience.DisableCombatDialogue()
    EndIf
    SetToggleOptionValue(oid, toggleCombatDialogue)
  elseif oid == forceOrgasmCommentOID
    forceOrgasmComment = !forceOrgasmComment
    SetToggleOptionValue(oid, forceOrgasmComment)
  elseif oid == forcePostSceneCommentOID
    forcePostSceneComment = !forcePostSceneComment
    SetToggleOptionValue(oid, forcePostSceneComment)
  elseif oid == prioritizePlayerThreadOID
    prioritizePlayerThread = !prioritizePlayerThread
    SetToggleOptionValue(oid, prioritizePlayerThread)
  elseif oid == actionEnabledOID
    ToggleActionEnabled(currentAction)
    SetToggleOptionValue(oid, JMap.getInt(JMap.getObj(aiff.actionRegistry, currentAction), "enabled") == 1)
  elseif oid == addSpellsOID
    main.AddSpellsToPlayer()
  elseif oid == removeSpellsOID
    main.RemoveSpellsFromPlayer()
  elseif oid == testActionsOID
    main.TestModEvents()
    Debug.MessageBox("Testing mod events...")
  elseif oid == enableAmbientCommentsOID
    enableAmbientComments = !enableAmbientComments
    SetToggleOptionValue(oid, enableAmbientComments)
  elseif oid == updateNarratorProfileOID
    updateNarratorProfile = !updateNarratorProfile
    SetToggleOptionValue(oid, updateNarratorProfile)
  elseif oid == preserveQueueOID
    preserveQueue = !preserveQueue
    if preserveQueue
      aiff.EnablePreserveQueue()
    else
      aiff.DisablePreserveQueue() 
    EndIf
    SetToggleOptionValue(oid, preserveQueue)
  elseif oid == trackVictimAwarenessOID
    trackVictimAwareness = !trackVictimAwareness
    SetToggleOptionValue(oid, trackVictimAwareness)
  elseif oid == disableSapienceInStealthOID
    disableSapienceInStealth = !disableSapienceInStealth
    SetToggleOptionValue(oid, disableSapienceInStealth)
  EndIf
  int i = 0
  string[] categories = JMap.allKeysPArray(aCategoryMap)
  while i < categories.Length
    int categoryOID = JMap.getInt(aCategoryMap, categories[i])
    if oid == categoryOID
      currentCategory = categories[i]
      ForcePageReset()
      return
    EndIf
    i += 1
  EndWhile
  i = 0
  string[] actions = JMap.allKeysPArray(aOIDMap)
  while i < actions.Length
    int aOID = JMap.getInt(aOIDMap, actions[i])
    if (oid == aoid)
      currentAction = actions[i]
      ForcePageReset()
      return
    EndIf
    i += 1
  EndWhile
EndEvent


Event OnOptionDefault(int oid)
  bool changedAction = False
  if oid == enableConsoleLoggingOID
    enableConsoleLogging = enableConsoleLoggingDefault
    SetToggleOptionValue(oid, enableConsoleLogging)
  elseif oid == UseCBPCOID
    SetGlobalToggle(oid, UseCBPC, true)
    Debug.Notification("CBPC setting changed. Save/Reload to take effect")
  elseif oid == autoUpdateDiaryOID
    autoUpdateDiary = autoUpdateDiaryDefault
    SetToggleOptionValue(oid, autoUpdateDiary)
  elseif oid == updateNarratorDiaryOID
    updateNarratorDiary = updateNarratorDiaryDefault
    SetToggleOptionValue(oid, updateNarratorDiary)
  elseif oid == updateNarratorProfileOID
    updateNarratorProfile = updateNarratorProfileDefault
    SetToggleOptionValue(oid, updateNarratorProfile)
  elseif oid == enableAISexOID
    enableAISex = enableAISexDefault
    aiff.SetAISexEnabled(enableAISex)
    SetToggleOptionValue(oid, enableAISex)
  elseif oid == useSapienceOID
    SetGlobalToggle(oid, minai_SapienceEnabled, false)
    if minai_SapienceEnabled.GetValueInt() == 1
      sapience.StartRadiantDialogue()
    else
      sapience.StopRadiantDialogue()
    EndIf
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
  elseif oid ==  minRadianceRechatsOID
    minRadianceRechats = minRadianceRechatsDefault
    SetSliderOptionValue(minRadianceRechatsOID, minRadianceRechatsDefault, "{0}")
  elseif oid ==  maxRadianceRechatsOID
    maxRadianceRechats = maxRadianceRechatsDefault
    SetSliderOptionValue(maxRadianceRechatsOID, maxRadianceRechatsDefault, "{0}")
  elseif oid ==  arousalForHarassOID
    arousalForHarass = arousalForHarassDefault
    SetSliderOptionValue(arousalForHarassOID, arousalForHarassDefault, "{0}")
  elseif oid ==  confirmSexOID
    confirmSex = confirmSexDefault
    SetToggleOptionValue(oid, confirmSex)
  elseif oid == allowSexTransitionsOID
    allowSexTransitions = allowSexTransitionsDefault
    SetToggleOptionValue(oid, allowSexTransitions)
    aiff.SetActorVariable(game.GetPlayer(), "allowSexTransitions", allowSexTransitions)
  elseif oid == toggleCombatDialogueOID
    toggleCombatDialogue = toggleCombatDialogueDefault
    if toggleCombatDialogue
      sapience.EnableCombatDialogue()
    else
      sapience.DisableCombatDialogue()
    EndIf
    SetToggleOptionValue(oid, toggleCombatDialogue)
  elseif oid == disableAIAnimationsOID
    disableAIAnimations = disableAIAnimationsDefault
    SetToggleOptionValue(oid, disableAIAnimationsDefault)
  elseif oid ==  radiantDialogueFrequencyOID
    radiantDialogueFrequency = radiantDialogueFrequencyDefault
    SetSliderOptionValue(radiantDialogueFrequencyOID, radiantDialogueFrequencyDefault, "{1}")
  elseif oid ==  radiantDialogueChanceOID
    radiantDialogueChance = radiantDialogueChanceDefault
    SetSliderOptionValue(radiantDialogueChanceOID, radiantDialogueChanceDefault, "{1}")
  elseif oid == genderWeightCommentsOID
    genderWeightComments = genderWeightCommentsDefault
    SetSliderOptionValue(oid, genderWeightCommentsDefault, "{0}")
  elseif oid == commentsRateOID
    commentsRate = commentsRateDefault
    SetSliderOptionValue(oid, commentsRateDefault, "{0}")
  elseif oid == forceOrgasmCommentOID
    forceOrgasmComment = forceOrgasmCommentDefault
    SetToggleOptionValue(oid, forceOrgasmCommentDefault)
  elseif oid == forcePostSceneCommentOID
    forcePostSceneComment = forcePostSceneCommentDefault
    SetToggleOptionValue(oid, forcePostSceneCommentDefault)
  elseif oid == prioritizePlayerThreadOID
    prioritizePlayerThread = prioritizePlayerThreadDefault
    SetToggleOptionValue(oid, prioritizePlayerThreadDefault)
  elseif oid == enableAmbientCommentsOID
    enableAmbientComments = enableAmbientCommentsDefault
    SetToggleOptionValue(oid, enableAmbientCommentsDefault)
  elseif oid == maxThreadsOID
    maxThreads = maxThreadsDefault
    SetSliderOptionValue(oid, maxThreadsDefault, "{0}")
  elseif oid == updateNarratorProfileOID
    updateNarratorProfile = updateNarratorProfileDefault
    SetToggleOptionValue(oid, updateNarratorProfile)
  elseif oid == preserveQueueOID
    preserveQueue = preserveQueueDefault
    if preserveQueue
      aiff.EnablePreserveQueue()
    else
      aiff.DisablePreserveQueue()
    EndIf
    SetToggleOptionValue(oid, preserveQueueDefault)
  elseif oid == trackVictimAwarenessOID
    trackVictimAwareness = trackVictimAwarenessDefault
    SetToggleOptionValue(oid, trackVictimAwareness)
  elseif oid == disableSapienceInStealthOID
    disableSapienceInStealth = disableSapienceInStealthDefault
    SetToggleOptionValue(oid, disableSapienceInStealth)
  EndIf
EndEvent


Event OnOptionHighlight(int oid)
  Main.Debug("OnOptionHighlight(" + oid + ")")
  if oid == enableConsoleLoggingOID
    SetInfoText("Controls whether log messages are printed to the console in addition to the Papyrus log")
  elseif oid == UseCBPCOID
    SetInfoText("Enables or disables CBPC globally. Requires save/reload to take effect")
  elseif oid == autoUpdateDiaryOID
    SetInfoText("Automatically update the diary for all followers upon sleeping.")
  elseif oid == updateNarratorDiaryOID
    SetInfoText("Controls whether the narrator maintains a diary that is updated when sleeping.")
  elseif oid == updateNarratorProfileOID
    SetInfoText("Controls whether the narrator's dynamic profile is updated when sleeping.")
  elseif oid == enableAISexOID
    SetInfoText("Allow NPC's to decide to have sex with eachother.")
  elseif  oid == useSapienceOID
    SetInfoText("The Sapience System enables and disables AI dynamically in a radius around the player using SPID, and allows NPC's to radiantly interact with eachother without direct player involvement.")
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
  elseif oid == radiantDialogueFrequencyOID
    SetInfoText("How often the radiant dialogue chance is checked for nearby AI")
  elseif oid == radiantDialogueChanceOID
    SetInfoText("How likely the radiant dialogue system is to be invoked each time it is checked")
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
  elseif oid == minRadianceRechatsOID
    SetInfoText("Minimum number of times a radiant dialogue can be rechatted")
  elseif oid == maxRadianceRechatsOID
    SetInfoText("Maximum number of times a radiant dialogue can be rechatted")
  elseif oid == arousalForHarassOID
    SetInfoText("Minimum Arousal level required for actions like spanking, groping, kissing to be exposed to the LLM")
  elseif oid == confirmSexOID
    SetInfoText("Show a confirmation message before sex scenes start")
  elseif oid == allowActorsToJoinSexOID
    SetInfoText("Allow actors to join ongoing sex scenes")
  elseif oid == allowSexTransitionsOID
    SetInfoText("Allow actors to transition between different sex scene types mid-scene")
  elseif oid == toggleCombatDialogueOID
    SetInfoText("Allow dialogue to be spoken during combat. Facilitates actor to actor dialogue, trash talking, taunts, etc.")
  elseif oid == disableAIAnimationsOID
    SetInfoText("Forces AI-FF animations to be disabled. There seems to be a CTD in the AIAgent DLL while resetting idle state sometimes, this avoids it.")
  elseif oid == genderWeightCommentsOID
    SetInfoText("Chances how often either gender npcs will talk. 0 - males only will talk. 100 - females only wil talk")
  elseif oid == commentsRateOID
    SetInfoText("Comments during sex scene cooldown in seconds. Example: If average AI response - 10 seconds, set this option to >10. Don't set too low it can consume a lot of resources and characters will talk non-stop...")
  elseif oid == forceOrgasmCommentOID
    SetInfoText("Ignore comments during sex cooldown, and request message on orgasm event immediately.")
  elseif oid == forcePostSceneCommentOID
    SetInfoText("Ignore comments during sex cooldown, and request message after sex scene ends.")
  elseif oid == prioritizePlayerThreadOID
    SetInfoText("If there are scenes with player involve, all comments will be within this scenes.")
  elseif oid == actionEnabledOID
    SetInfoText("Enable or disable the action. This will prevent it from being exposed to the LLM")
  elseif oid == actionIntervalOID
    SetInfoText("The base cooldown (in seconds) inbetween uses of the action. Increases by the exponent every time this is triggered. Set to 0 to disable backoff entirely")
  elseif oid == actionExponentOID
    SetInfoText("The exponent applied to the interval's cooldown for uses of this action")
  elseif oid == actionMaxIntervalOID
    SetInfoText("The cap on the maximum value that the interval will rise to from repeated uses of the action. Useful if you want to have a cap on how long the cooldown will become")
  elseif oid == actionDecayWindowOID
    SetInfoText("The duration of time which must pass without the action being used for the cooldown to return to the base value")
  elseif oid == testActionsOID
    SetInfoText("For debugging purposes. Send test mod events to the backend")
  elseif oid == addSpellsOID
    SetInfoText("Add spells such as Toggle Sapience, and other mod utility spells to the player")
  elseif oid == toggleSapienceOID
    SetInfoText("Hotkey to toggle Sapience on or off")
  elseif oid == removeSpellsOID
    SetInfoText("Remove the spells that this mod adds from the player")
  elseif oid == enableAmbientCommentsOID
    SetInfoText("Enable ambient comments between events. Follows comments during sex scene cooldown. Polling mechanism checking each time if there is no cooldown on comments and fires ambient talking.")
  elseif oid == maxThreadsOID
    SetInfoText("Maximum concurrent threads for adult frameworks. Ostim usually crashes at 6+, try yourself and set to the number you game can handle.")
  elseif oid == singKeyOID
    SetInfoText("Hotkey to make your character sing")
  elseif oid == narratorKeyOID
    SetInfoText("Hotkey to initiate a private conversation with just the narrator")
  elseif oid == narratorTextKeyOID
    SetInfoText("Hotkey to type to the narrator")
  elseif oid == preserveQueueOID
    SetInfoText("When enabled, the dialogue queue will be preserved when actions are enabled. This allows for more natural conversation flow.")
  elseif oid == trackVictimAwarenessOID
    SetInfoText("When enabled, tracks whether actors in sex scenes are victims or aggressors. This may not be completely accurate, and is mod-dependent.")
  elseif oid == roleplayKeyOID
    SetInfoText("Hotkey to roleplay as your character using voice")
  elseif oid == roleplayTextKeyOID
    SetInfoText("Hotkey to roleplay as your character using text")
  elseif oid == disableSapienceInStealthOID
    SetInfoText("When enabled, sapience will be automatically disabled while the player is sneaking (Allowing for private conversations with followers and such)")
  EndIf
  int i = 0
  string[] actions = JMap.allKeysPArray(aiff.actionRegistry)
  while i < actions.Length
    if oid == JMap.getInt(aOIDMap, actions[i])
      int actionObj = JMap.getObj(aiff.actionRegistry, actions[i])
      string mcmDesc = JMap.getStr(actionObj, "mcmDesc")
      Main.Debug("Highlighting action " + actions[i] + " with description " + mcmDesc)
      SetInfoText(mcmDesc)
      return
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
  elseif oid == radiantDialogueFrequencyOID
    SetSliderDialogStartValue(radiantDialogueFrequency)
    SetSliderDialogDefaultValue(radiantDialogueFrequencyDefault)
    SetSliderDialogRange(5, 300)
    SetSliderDialogInterval(0.5)
  elseif oid == radiantDialogueChanceOID
    SetSliderDialogStartValue(radiantDialogueChance)
    SetSliderDialogDefaultValue(radiantDialogueChanceDefault)
    SetSliderDialogRange(0, 100)
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
    SetSliderDialogRange(0, 100)
    SetSliderDialogInterval(1.0)
  elseif oid == minRadianceRechatsOID
    SetSliderDialogStartValue(minRadianceRechats)
    SetSliderDialogDefaultValue(minRadianceRechatsDefault)
    SetSliderDialogRange(0, 30)
    SetSliderDialogInterval(1.0)
  elseif oid == maxRadianceRechatsOID
    SetSliderDialogStartValue(maxRadianceRechats)
    SetSliderDialogDefaultValue(maxRadianceRechatsDefault)
    SetSliderDialogRange(0, 30)
    SetSliderDialogInterval(1.0)
  elseif oid == arousalForHarassOID
    SetSliderDialogStartValue(arousalForHarass)
    SetSliderDialogDefaultValue(arousalForHarassDefault)
    SetSliderDialogRange(0, 100)
    SetSliderDialogInterval(1.0)
  elseif oid == genderWeightCommentsOID
    SetSliderDialogStartValue(genderWeightComments)
    SetSliderDialogDefaultValue(genderWeightCommentsDefault)
    SetSliderDialogRange(0, 100)
    SetSliderDialogInterval(1.0)
  elseif oid == commentsRateOID
    SetSliderDialogStartValue(commentsRate)
    SetSliderDialogDefaultValue(commentsRateDefault)
    SetSliderDialogRange(0, 120)
    SetSliderDialogInterval(1.0)
  elseIf oid == actionIntervalOID
    int actionObj = JMap.getObj(aiff.actionRegistry, currentAction)
    float value = JMap.getFlt(actionObj, "interval")
    float defaultValue = JMap.getFlt(actionObj, "intervalDefault")
    SetSliderDialogStartValue(value)
    SetSliderDialogDefaultValue(defaultValue)
    SetSliderDialogRange(0, 100)
    SetSliderDialogInterval(0.5)
  elseif oid == actionExponentOID
    int actionObj = JMap.getObj(aiff.actionRegistry, currentAction)
    float value = JMap.getFlt(actionObj, "exponent")
    float defaultValue = JMap.getFlt(actionObj, "exponentDefault")
    SetSliderDialogStartValue(value)
    SetSliderDialogDefaultValue(defaultValue)
    SetSliderDialogRange(0, 100)
    SetSliderDialogInterval(0.5)
  elseif oid == actionMaxIntervalOID
    int actionObj = JMap.getObj(aiff.actionRegistry, currentAction)
    float value = JMap.getFlt(actionObj, "maxInterval")
    float defaultValue = JMap.getFlt(actionObj, "maxIntervalDefault")
    SetSliderDialogStartValue(value)
    SetSliderDialogDefaultValue(defaultValue)
    SetSliderDialogRange(0, 100)
    SetSliderDialogInterval(1)
  elseif oid == actionDecayWindowOID
    int actionObj = JMap.getObj(aiff.actionRegistry, currentAction)
    float value = JMap.getFlt(actionObj, "decayWindow")
    float defaultValue = JMap.getFlt(actionObj, "decayWindowDefault")
    SetSliderDialogStartValue(value)
    SetSliderDialogDefaultValue(defaultValue)
    SetSliderDialogRange(0, 1200)
    SetSliderDialogInterval(5)
  elseif oid == maxThreadsOID
    SetSliderDialogStartValue(maxThreads)
    SetSliderDialogDefaultValue(maxThreadsDefault)
    SetSliderDialogRange(0, 20)
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
  elseif oid == radiantDialogueFrequencyOID
    sapience.StartRadiantDialogue()
    radiantDialogueFrequency = value
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == radiantDialogueChanceOID
    sapience.StartRadiantDialogue()
    radiantDialogueChance = value
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
  elseif oid == minRadianceRechatsOID
    minRadianceRechats = value as Int
    SetSliderOptionValue(oid, value, "{0}")
  elseif oid == maxRadianceRechatsOID
    maxRadianceRechats = value as Int
    SetSliderOptionValue(oid, value, "{0}")
  elseif oid == arousalForHarassOID
    arousalForHarass = value
    SetSliderOptionValue(oid, value, "{0}")
    StoreConfig("arousalForHarass", arousalForHarass)
  elseif oid == genderWeightCommentsOID
    genderWeightComments = value
    SetSliderOptionValue(oid, value, "{0}")
    StoreConfig("genderWeightComments", genderWeightComments)
  elseif oid == commentsRateOID
    commentsRate = value
    SetSliderOptionValue(oid, value, "{0}")
    StoreConfig("commentsRate", commentsRate)
  elseIf oid == actionIntervalOID
    SetActionInterval(currentAction, value)
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == actionExponentOID
    SetActionExponent(currentAction, value)
    SetSliderOptionValue(oid, value, "{1}")
  elseif oid == actionMaxIntervalOID
    SetActionMaxInterval(currentAction, value)
    SetSliderOptionValue(oid, value, "{0}")
  elseif oid == actionDecayWindowOID
    SetActionDecayWindow(currentAction, value)
    SetSliderOptionValue(oid, value, "{0}")
  elseif oid == maxThreadsOID
    maxThreads = value
    SetSliderOptionValue(oid, value, "{0}")
    StoreConfig("maxThreads", commentsRate)
  EndIf
EndEvent


event OnOptionKeyMapChange(int a_option, int a_keyCode, string a_conflictControl, string a_conflictName)
    {Called when a key has been remapped}
    bool continue = true
    if (a_conflictControl != "")
        string msg
        if (a_conflictName != "")
            msg = "This key is already mapped to:\n'" + a_conflictControl + "'\n(" + a_conflictName + ")\n\nAre you sure you want to continue?"
        else
            msg = "This key is already mapped to:\n'" + a_conflictControl + "'\n\nAre you sure you want to continue?"
        endIf
        continue = ShowMessage(msg, true, "$Yes", "$No")
    endIf

    if (continue)
        if (a_option == toggleSapienceOID)
            toggleSapienceKey = a_keyCode
            SetKeymapOptionValue(a_option, a_keyCode)
            main.SetSapienceKey(true)
        elseif (a_option == singKeyOID)
            singKey = a_keyCode
            SetKeymapOptionValue(a_option, a_keyCode)
            main.SetSingKey(true)
        elseif (a_option == narratorKeyOID)
            narratorKey = a_keyCode 
            SetKeymapOptionValue(a_option, a_keyCode)
            main.SetNarratorKey(true)
        elseif (a_option == narratorTextKeyOID)
            narratorTextKey = a_keyCode
            SetKeymapOptionValue(a_option, a_keyCode)
            main.SetNarratorTextKey(true)
        elseif (a_option == roleplayKeyOID)
            roleplayKey = a_keyCode
            SetKeymapOptionValue(a_option, a_keyCode)
            main.SetRoleplayKey(true)
        elseif (a_option == roleplayTextKeyOID)
            roleplayTextKey = a_keyCode
            SetKeymapOptionValue(a_option, a_keyCode)
            main.SetRoleplayTextKey(true)
        endIf
    endIf
EndEvent