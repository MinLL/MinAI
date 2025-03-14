Scriptname minai_ItemCommands extends Quest

; Main quest controller script
minai_MainQuestController Property Main Auto
; Access to the AIFF framework
minai_AIFF Property aiff Auto
; Player reference
Actor Property PlayerRef Auto
; Utility script for common functions
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

; Helper function to get a Form from the item registry
Form Function GetItemFormFromRegistry(string itemId)
  if !itemId || itemId == ""
    return None
  EndIf
  
  ; If itemId already has 0x prefix, use it as is, otherwise add it
  if StringUtil.Find(itemId, "0x") != 0
    itemId = "0x" + itemId
  EndIf
  
  ; Try to get item data from registry
  int itemData = JMap.getObj(aiff.itemRegistry, itemId)
  if itemData != 0
    return JMap.getForm(itemData, "form")
  EndIf
  
  ; If not found, log warning and return None
  Main.Warn("GetItemFormFromRegistry - Item ID not found in registry: " + itemId)
  return None
EndFunction

; Helper function to find an item by name in the registry
Form Function GetItemFormFromName(string itemName)
  if !itemName || itemName == ""
    return None
  EndIf
  
  Main.Debug("GetItemFormFromName - Searching for item: " + itemName)
  
  ; Get all item IDs from registry
  string[] itemIds = JMap.allKeysPArray(aiff.itemRegistry)
  
  if !itemIds || itemIds.Length == 0
    Main.Warn("GetItemFormFromName - Item registry is empty")
    return None
  EndIf
  
  ; Search through registry for matching names
  int i = 0
  while i < itemIds.Length
    int itemData = JMap.getObj(aiff.itemRegistry, itemIds[i])
    if itemData != 0
      Form itemForm = JMap.getForm(itemData, "form")
      if itemForm
        string registryItemName = itemForm.GetName()
        if registryItemName == itemName
          Main.Debug("GetItemFormFromName - Found match: " + registryItemName + " (ID: " + itemIds[i] + ")")
          return itemForm
        EndIf
      EndIf
    EndIf
    i += 1
  EndWhile
  
  ; Try partial matching if exact match not found
  i = 0
  while i < itemIds.Length
    int itemData = JMap.getObj(aiff.itemRegistry, itemIds[i])
    if itemData != 0
      Form itemForm = JMap.getForm(itemData, "form")
      if itemForm
        string registryItemName = itemForm.GetName()
        ; Check if the registry item name contains the search string
        if StringUtil.Find(registryItemName, itemName) >= 0
          Main.Debug("GetItemFormFromName - Found partial match: " + registryItemName + " (ID: " + itemIds[i] + ")")
          return itemForm
        EndIf
      EndIf
    EndIf
    i += 1
  EndWhile
  
  ; If still not found, log warning and return None
  Main.Warn("GetItemFormFromName - Item name not found in registry: " + itemName)
  return None
EndFunction

; Give an item from NPC to player
Function GiveItemToPlayer(Actor akSpeaker, String parameter)
  if !akSpeaker
    Main.Warn("GiveItemToPlayer - Invalid speaker")
    return
  EndIf
  
  string speakerName = Main.GetActorName(akSpeaker)
  string playerName = Main.GetActorName(PlayerRef)
  string[] params = StringUtil.Split(parameter, ":")
  
  if params.Length < 1
    Main.Warn("GiveItemToPlayer - Missing item name parameter")
    return
  EndIf
  
  string itemName = params[0]
  int count = 1
  
  ; Parse count if provided
  if params.Length >= 2 && params[1] != ""
    count = params[1] as int
    if count <= 0
      count = 1
    EndIf
  EndIf
  
  ; Get item from registry by name
  Form itemForm = GetItemFormFromName(itemName)
  if !itemForm
    Main.Warn("GiveItemToPlayer - Item not found in registry: " + itemName)
    Debug.Notification("Item not found in registry: " + itemName)
    ; aiff.AIRequestMessageForActor(speakerName + " tried to give " + playerName + " " + itemName + ", but couldn't find it.", "chat_minai_giveitem", speakerName)
    return
  EndIf
  
  ; Get the actual item name as it appears in game
  string actualItemName = itemForm.GetName()
  
  ; Check if NPC has the item
  int hasCount = akSpeaker.GetItemCount(itemForm)
  if hasCount <= 0
    Main.Warn("GiveItemToPlayer - " + speakerName + " doesn't have any " + actualItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to give " + playerName + " " + actualItemName + ", but doesn't have any.", "chatnf_minai_narrate", speakerName)
    return
  EndIf
  
  ; Adjust count if NPC doesn't have enough
  if hasCount < count
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
  
  ; Return result to LLM
  main.RequestLLMResponseFromActor(speakerName + " gave " + playerName + " " + countStr + actualItemName + ".", "chatnf_minai_narrate", speakerName)
EndFunction

; Take an item from player to NPC
Function TakeItemFromPlayer(Actor akSpeaker, String parameter)
  if !akSpeaker
    Main.Warn("TakeItemFromPlayer - Invalid speaker")
    return
  EndIf
  
  string speakerName = Main.GetActorName(akSpeaker)
  string playerName = Main.GetActorName(PlayerRef)
  string[] params = StringUtil.Split(parameter, ":")
  
  if params.Length < 1
    Main.Warn("TakeItemFromPlayer - Missing item name parameter")
    return
  EndIf
  
  string itemName = params[0]
  int count = 1
  
  ; Parse count if provided
  if params.Length >= 2 && params[1] != ""
    count = params[1] as int
    if count <= 0
      count = 1
    EndIf
  EndIf
  
  ; Get item from registry by name
  Form itemForm = GetItemFormFromName(itemName)
  if !itemForm
    Main.Warn("TakeItemFromPlayer - Item not found in registry: " + itemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to take " + itemName + " from " + playerName + ", but couldn't find it.", "chatnf_minai_narrate", speakerName)
    return
  EndIf
  
  ; Get the actual item name as it appears in game
  string actualItemName = itemForm.GetName()
  
  ; Check if player has the item
  int hasCount = PlayerRef.GetItemCount(itemForm)
  if hasCount <= 0
    Main.Warn("TakeItemFromPlayer - " + playerName + " doesn't have any " + actualItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to take " + actualItemName + " from " + playerName + ", but " + playerName + " doesn't have any.", "chatnf_minai_narrate", speakerName)
    return
  EndIf
  
  ; Adjust count if player doesn't have enough
  if hasCount < count
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
  
  ; Return result to LLM
  main.RequestLLMResponseFromActor(speakerName + " took " + countStr + actualItemName + " from " + playerName + ".", "chatnf_minai_narrate", speakerName)
  
  ; Register the event for event tracking
  Main.RegisterEvent(speakerName + " took " + countStr + actualItemName + " from " + playerName + ".")
EndFunction

; Trade items between NPC and player
Function TradeItemWithPlayer(Actor akSpeaker, String parameter)
  if !akSpeaker
    Main.Warn("TradeItemWithPlayer - Invalid speaker")
    return
  EndIf
  
  string speakerName = Main.GetActorName(akSpeaker)
  string playerName = Main.GetActorName(PlayerRef)
  string[] params = StringUtil.Split(parameter, ":")
  
  if params.Length < 2
    Main.Warn("TradeItemWithPlayer - Missing required parameters")
    return
  EndIf
  
  string giveItemName = params[0]
  string takeItemName = params[1]
  int giveCount = 1
  int takeCount = 1
  
  ; Parse counts if provided
  if params.Length >= 3 && params[2] != ""
    giveCount = params[2] as int
    if giveCount <= 0
      giveCount = 1
    EndIf
  EndIf
  
  if params.Length >= 4 && params[3] != ""
    takeCount = params[3] as int
    if takeCount <= 0
      takeCount = 1
    EndIf
  EndIf
  
  ; Get items from registry by name
  Form giveItemForm = GetItemFormFromName(giveItemName)
  Form takeItemForm = GetItemFormFromName(takeItemName)
  
  if !giveItemForm || !takeItemForm
    Main.Warn("TradeItemWithPlayer - One or more items not found in registry")
    main.RequestLLMResponseFromActor(speakerName + " tried to trade items with " + playerName + ", but one or more items couldn't be found.", "chatnf_minai_narrate", speakerName)
    return
  EndIf
  
  ; Get the actual item names as they appear in game
  string actualGiveItemName = giveItemForm.GetName()
  string actualTakeItemName = takeItemForm.GetName()
  
  ; Check if NPC has the item to give
  int npcHasCount = akSpeaker.GetItemCount(giveItemForm)
  if npcHasCount <= 0
    Main.Warn("TradeItemWithPlayer - " + speakerName + " doesn't have any " + actualGiveItemName)
    main.RequestLLMResponseFromActor(speakerName + " tried to trade " + actualGiveItemName + " with " + playerName + ", but doesn't have any.", "chatnf_minai_narrate", speakerName)
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
    main.RequestLLMResponseFromActor(speakerName + " tried to trade for " + playerName + "'s " + actualTakeItemName + ", but " + playerName + " doesn't have any.", "chatnf_minai_narrate", speakerName)
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
  main.RequestLLMResponseFromActor(speakerName + " traded " + giveCountStr + actualGiveItemName + " for " + playerName + "'s " + takeCountStr + actualTakeItemName + ".", "chatnf_minai_narrate", speakerName)
  
  ; Register the event for event tracking
  Main.RegisterEvent(speakerName + " traded " + giveCountStr + actualGiveItemName + " for " + takeCountStr + actualTakeItemName + " with " + playerName + ".")
EndFunction 