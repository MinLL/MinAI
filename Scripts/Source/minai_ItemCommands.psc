Scriptname minai_ItemCommands extends Quest

minai_MainQuestController Property Main Auto
minai_AIFF Property aiff Auto
Actor Property PlayerRef Auto
minai_Util Property MinaiUtil Auto

; Initialization flag
bool Property Initialized Auto

; Initialization function called from MainQuestController
Function Maintenance(minai_MainQuestController MainController)
  Main = MainController
  aiff = (Main as Quest) as minai_AIFF
  MinaiUtil = (Main as Quest) as minai_Util
  PlayerRef = Game.GetPlayer()
  string playerName = Main.GetActorName(PlayerRef)
  ; Register the item commands with the AIFF system
  aiff.RegisterAction("ExtCmdGiveItem", "GiveItem", "Give an item to " + playerName, "General", 1, 0, 2, 5, 60, true, true)
  aiff.RegisterAction("ExtCmdTakeItem", "TakeItem", "Take an item from " + playerName, "General", 1, 0, 2, 5, 60, true, true)
  aiff.RegisterAction("ExtCmdTradeItem", "TradeItem", "Trade items with " + playerName, "General", 1, 0, 2, 5, 60, true, true)
  
  Main.Info("minai_ItemCommands.Maintenance() - Initialized item commands")
  Initialized = true
EndFunction

; Event listeners
Event OnInit()
  ; Don't register here - let Maintenance handle it
  if !Initialized && Main
    Main.Info("minai_ItemCommands initialized but waiting for Maintenance call")
  EndIf
EndEvent

; Main command handler
Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !Initialized
    return
  EndIf

  Actor akSpeaker = aiff.AIGetAgentByName(speakerName)
  
  if !akSpeaker
    Main.Warn("OnCommandReceived - No speaker found for command: " + command)
    return
  EndIf
  
  string playerName = Main.GetActorName(PlayerRef)
  
  Main.Debug("ItemCommands - Received command: " + command + " with parameter: " + parameter)
  
  if command == "ExtCmdGiveItem"
    GiveItemToPlayer(akSpeaker, parameter)
  elseIf command == "ExtCmdTakeItem"
    TakeItemFromPlayer(akSpeaker, parameter)
  elseIf command == "ExtCmdTradeItem"
    TradeItemWithPlayer(akSpeaker, parameter)
  EndIf
EndEvent



; New helper function to parse form ID from the new format "0x00000F:Skyrim.esm:5"
; Returns the Form object
Form Function GetFormFromLLMFormat(string parameter)
  if !parameter || parameter == ""
    return None
  EndIf
  
  ; Parse the parameter (formId:modName:count)
  string[] parts = StringUtil.Split(parameter, ":")
  if parts.Length < 2
    Main.Warn("GetFormFromLLMFormat - Invalid format, expected formId:modName[:count]: " + parameter)
    return None
  EndIf
  
  string formIdStr = parts[0]
  string modName = parts[1]
  
  ; Process form ID string - strip 0x prefix if present
  if StringUtil.Find(formIdStr, "0x") == 0
    formIdStr = StringUtil.SubString(formIdStr, 2)
  EndIf
  
  ; Use the utility function to convert hex to integer
  int formId = minai_Util.HexToInt(formIdStr)
  if formId == 0
    Main.Warn("GetFormFromLLMFormat - Failed to parse form ID: " + formIdStr)
    return None
  EndIf
  
  ; Get the form from the given mod
  Form itemForm = Game.GetFormFromFile(formId, modName)
  if !itemForm
    Main.Warn("GetFormFromLLMFormat - Form not found: " + formIdStr + " in mod " + modName)
    return None
  EndIf
  
  return itemForm
EndFunction

; Helper function to extract the count from the parameter
int Function GetCountFromLLMFormat(string parameter)
  if !parameter || parameter == ""
    return 1
  EndIf
  
  ; Parse the parameter (formId:modName:count)
  string[] parts = StringUtil.Split(parameter, ":")
  
  ; Default count is 1
  int count = 1
  
  ; Parse count if provided
  if parts.Length >= 3 && parts[2] != ""
    count = parts[2] as int
    if count <= 0
      count = 1
    EndIf
  EndIf
  
  return count
EndFunction

; Give an item from NPC to player
Function GiveItemToPlayer(Actor akSpeaker, String parameter)
  if !akSpeaker
    Main.Warn("GiveItemToPlayer - Invalid speaker")
    return
  EndIf
  
  string speakerName = Main.GetActorName(akSpeaker)
  string playerName = Main.GetActorName(PlayerRef)
  
  ; Parse the item information and count
  Form itemForm = GetFormFromLLMFormat(parameter)
  int count = GetCountFromLLMFormat(parameter)
  
  if !itemForm
    Main.Warn("GiveItemToPlayer - Invalid item format or item not found: " + parameter)
    Debug.Notification("Invalid item format or item not found: " + parameter)
    return
  EndIf
  
  ; Get the actual item name as it appears in game
  string actualItemName = itemForm.GetName()
  
  ; Check if NPC has the item
  int hasCount = akSpeaker.GetItemCount(itemForm)
  if hasCount <= 0
    Main.Warn("GiveItemToPlayer - " + speakerName + " doesn't have any " + actualItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to give " + playerName + " " + actualItemName + ", but doesn't have any.", "chatnf_minai_narrate", speakerName, "npc")
    return
  EndIf
  
  ; Check if NPC has enough of the item
  bool hasEnough = true
  int originalCount = count
  if hasCount < count
    hasEnough = false
    Main.Debug("GiveItemToPlayer - Adjusting count from " + count + " to " + hasCount)
    count = hasCount
  EndIf
  
  ; Give item to player
  akSpeaker.RemoveItem(itemForm, count, true, PlayerRef)
  Main.Info("GiveItemToPlayer - " + speakerName + " gave " + count + "x " + actualItemName + " to " + playerName)
  
  ; Update inventories after transfer
  aiff.TrackActorInventory(akSpeaker)
  aiff.TrackActorInventory(PlayerRef)
  
  ; Format count string for readability
  string countStr = ""
  if count > 1
    countStr = count + "x "
  EndIf
  
  ; Return result to LLM - different message based on whether NPC had enough items
  if hasEnough
    main.RequestLLMResponseFromActor(speakerName + " gave " + playerName + " " + countStr + actualItemName + ".", "chatnf_minai_narrate", speakerName, "npc")
  else
    string originalCountStr = ""
    if originalCount > 1
      originalCountStr = originalCount + "x "
    EndIf
    main.RequestLLMResponseFromActor(speakerName + " only had " + countStr + actualItemName + " to give " + playerName + ", so they gave what they had.", "chatnf_minai_narrate", speakerName, "npc")
  EndIf
EndFunction

; Take an item from player to NPC
Function TakeItemFromPlayer(Actor akSpeaker, String parameter)
  if !akSpeaker
    Main.Warn("TakeItemFromPlayer - Invalid speaker")
    return
  EndIf
  
  string speakerName = Main.GetActorName(akSpeaker)
  string playerName = Main.GetActorName(PlayerRef)
  
  ; Parse the item information and count
  Form itemForm = GetFormFromLLMFormat(parameter)
  int count = GetCountFromLLMFormat(parameter)
  
  if !itemForm
    Main.Warn("TakeItemFromPlayer - Invalid item format or item not found: " + parameter)
    main.RequestLLMResponseFromActor(speakerName + " tried to take an item from " + playerName + ", but couldn't find it.", "chatnf_minai_narrate", speakerName, "npc")
    return
  EndIf
  
  ; Get the actual item name as it appears in game
  string actualItemName = itemForm.GetName()
  
  ; Check if player has the item
  int hasCount = PlayerRef.GetItemCount(itemForm)
  if hasCount <= 0
    Main.Warn("TakeItemFromPlayer - " + playerName + " doesn't have any " + actualItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to take " + actualItemName + " from " + playerName + ", but " + playerName + " doesn't have any.", "chatnf_minai_narrate", speakerName, "npc")
    return
  EndIf
  
  ; Check if player has enough of the item
  bool hasEnough = true
  int originalCount = count
  if hasCount < count
    hasEnough = false
    Main.Debug("TakeItemFromPlayer - Adjusting count from " + count + " to " + hasCount)
    count = hasCount
  EndIf
  
  ; Take item from player
  PlayerRef.RemoveItem(itemForm, count, true, akSpeaker)
  Main.Info("TakeItemFromPlayer - " + speakerName + " took " + count + "x " + actualItemName + " from " + playerName)
  
  ; Update inventories after transfer
  aiff.TrackActorInventory(akSpeaker)
  aiff.TrackActorInventory(PlayerRef)
  
  ; Format count string for readability
  string countStr = ""
  if count > 1
    countStr = count + "x "
  EndIf
  
  ; Return result to LLM - different message based on whether player had enough items
  if hasEnough
    main.RequestLLMResponseFromActor(speakerName + " took " + countStr + actualItemName + " from " + playerName + ".", "chatnf_minai_narrate", speakerName, "npc")
  else
    string originalCountStr = ""
    if originalCount > 1
      originalCountStr = originalCount + "x "
    EndIf
    main.RequestLLMResponseFromActor(speakerName + " took " + countStr + actualItemName + " from " + playerName + ", which was all " + playerName + " had.", "chatnf_minai_narrate", speakerName, "npc")
  EndIf
  
  ; Register the event for event tracking
  Main.RegisterEvent(speakerName + " took " + countStr + actualItemName + " from " + playerName + ".", "info_item_taken")
EndFunction

; Trade items between NPC and player
Function TradeItemWithPlayer(Actor akSpeaker, String parameter)
  if !akSpeaker
    Main.Warn("TradeItemWithPlayer - Invalid speaker")
    return
  EndIf
  
  string speakerName = Main.GetActorName(akSpeaker)
  string playerName = Main.GetActorName(PlayerRef)
  string[] params = StringUtil.Split(parameter, "|")
  
  if params.Length < 2
    Main.Warn("TradeItemWithPlayer - Missing required parameters, expected format: formId:modName:count|formId:modName:count")
    return
  EndIf
  
  ; Parse the items to give and take with their counts
  Form giveItemForm = GetFormFromLLMFormat(params[0])
  int giveCount = GetCountFromLLMFormat(params[0])
  
  Form takeItemForm = GetFormFromLLMFormat(params[1])
  int takeCount = GetCountFromLLMFormat(params[1])
  
  if !giveItemForm || !takeItemForm
    Main.Warn("TradeItemWithPlayer - One or more items not found or invalid format")
    main.RequestLLMResponseFromActor(speakerName + " tried to trade items with " + playerName + ", but one or more items couldn't be found.", "chatnf_minai_narrate", speakerName, "npc")
    return
  EndIf
  
  ; Get the actual item names as they appear in game
  string actualGiveItemName = giveItemForm.GetName()
  string actualTakeItemName = takeItemForm.GetName()
  
  ; Check if NPC has the item to give
  int npcHasCount = akSpeaker.GetItemCount(giveItemForm)
  if npcHasCount <= 0
    Main.Warn("TradeItemWithPlayer - " + speakerName + " doesn't have any " + actualGiveItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to trade " + actualGiveItemName + " with " + playerName + ", but doesn't have any.", "chatnf_minai_narrate", speakerName, "npc")
    return
  EndIf
  
  ; Adjust count if NPC doesn't have enough
  if npcHasCount < giveCount
    Main.Debug("TradeItemWithPlayer - Adjusting give count from " + giveCount + " to " + npcHasCount)
    giveCount = npcHasCount
  EndIf
  
  ; Check if player has the item to give
  int playerHasCount = PlayerRef.GetItemCount(takeItemForm)
  if playerHasCount <= 0
    Main.Warn("TradeItemWithPlayer - " + playerName + " doesn't have any " + actualTakeItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to trade for " + playerName + "'s " + actualTakeItemName + ", but " + playerName + " doesn't have any.", "chatnf_minai_narrate", speakerName, "npc")
    return
  EndIf
  
  ; Adjust count if player doesn't have enough
  if playerHasCount < takeCount
    Main.Debug("TradeItemWithPlayer - Adjusting take count from " + takeCount + " to " + playerHasCount)
    takeCount = playerHasCount
  EndIf
  
  ; Perform the trade (both operations at once)
  akSpeaker.RemoveItem(giveItemForm, giveCount, true, PlayerRef)
  PlayerRef.RemoveItem(takeItemForm, takeCount, true, akSpeaker)
  
  Main.Debug("TradeItemWithPlayer - " + speakerName + " gave " + giveCount + "x " + actualGiveItemName + " and received " + takeCount + "x " + actualTakeItemName + " from " + playerName)
  
  ; Update inventories after transfer
  aiff.TrackActorInventory(akSpeaker)
  aiff.TrackActorInventory(PlayerRef)
  
  ; Format count strings for readability
  string giveCountStr = ""
  if giveCount > 1
    giveCountStr = giveCount + "x "
  EndIf
  
  string takeCountStr = ""
  if takeCount > 1
    takeCountStr = takeCount + "x "
  EndIf
  
  ; Return result to LLM
  main.RequestLLMResponseFromActor(speakerName + " traded " + giveCountStr + actualGiveItemName + " for " + playerName + "'s " + takeCountStr + actualTakeItemName + ".", "chatnf_minai_narrate", speakerName, "npc")
  
  ; Register the event for event tracking
  Main.RegisterEvent(speakerName + " traded " + giveCountStr + actualGiveItemName + " for " + takeCountStr + actualTakeItemName + " with " + playerName + ".", "info_item_traded")
EndFunction 