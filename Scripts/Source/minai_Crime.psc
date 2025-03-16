scriptname minai_Crime extends Quest

minai_MainQuestController main
minai_AIFF aiff
Actor PlayerRef
minai_Config config

; Crime faction handling
FormList CrimeFactionsList      ; FormList containing all crime factions

function Maintenance(minai_MainQuestController _main)
  PlayerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  main.Info("Initializing Crime Module")
  
  ; Get the crime factions FormList
  CrimeFactionsList = Game.GetFormFromFile(0x026953, "Skyrim.esm") as FormList
  
  if !CrimeFactionsList
    main.Error("Failed to get CrimeFactionsList - crime functionality may be limited")
    return
  endif
  
  ; Log the found crime factions
  int count = CrimeFactionsList.GetSize()
  main.Info("Found " + count + " crime factions in CrimeFactionsList")
  
  int i = 0
  while i < count
    Faction crimeFaction = CrimeFactionsList.GetAt(i) as Faction
    
    if crimeFaction
      main.Info("Initialized crime faction: " + crimeFaction.GetName())
    else
      main.Warn("Failed to cast crime faction at index " + i)
    endif
    
    i += 1
  endwhile
  StoreCrimeVariables()
  ; Register our crime actions
  RegisterActions()
endFunction

; Command dispatcher event - receives commands from AI Agent
Event CommandDispatcher(String speakerName, String command, String parameter)
  main.Info("Crime CommandDispatcher received: " + speakerName + ", " + command + ", " + parameter)
  
  ; Extract the faction name from the speaker name (format: "Name [Faction]")
  string guardFaction = ""
  int bracketStart = StringUtil.Find(speakerName, "[")
  int bracketEnd = StringUtil.Find(speakerName, "]")
  
  if bracketStart > 0 && bracketEnd > bracketStart
    guardFaction = StringUtil.Substring(speakerName, bracketStart + 1, bracketEnd - bracketStart - 1)
    main.Info("Extracted guard faction: " + guardFaction)
  else
    main.Warn("Could not extract faction from speaker name: " + speakerName)
  endif
  
  ; Determine which command to execute
  if command == "ExtCmdAddBountySmall"
    AddBounty(config.SmallBountyAmount, speakerName, guardFaction)
    SetContext(PlayerRef)
  elseif command == "ExtCmdAddBountyMedium"
    AddBounty(config.MediumBountyAmount, speakerName, guardFaction)
    SetContext(PlayerRef)
  elseif command == "ExtCmdAddBountyLarge"
    AddBounty(config.LargeBountyAmount, speakerName, guardFaction)
    SetContext(PlayerRef)
  elseif command == "ExtCmdArrest"
    Arrest(speakerName, guardFaction)
    SetContext(PlayerRef)
  elseif command == "ExtCmdClearBounty"
    ClearBounty(speakerName, guardFaction)
    SetContext(PlayerRef)
  endif
EndEvent

function AddBounty(int amount, string speakerName, string guardFaction)
  main.Info("Adding bounty of " + amount + " by " + speakerName + " of faction " + guardFaction)
  
  ; Get the crime faction for this guard
  Faction crimeFaction = GetFactionByGuard(guardFaction)
  
  if !crimeFaction
    main.Warn("No crime faction found for guard: " + guardFaction)
    main.RequestLLMResponseFromActor("The guard attempts to report the crime, but appears confused about which hold's laws apply here. Without proper jurisdiction, the crime goes unrecorded.", "chatnf_minai_narrate", speakerName, "player")
    return
  endif
  
  ; Add bounty to the player
  crimeFaction.ModCrimeGold(amount, false)
  
  int newBounty = crimeFaction.GetCrimeGold()
  string playerName = main.GetActorName(PlayerRef)
  string factionName = crimeFaction.GetName()
  
  ; Create a more detailed context about the bounty situation
  string bountyReason = ""
  if amount == config.SmallBountyAmount
    bountyReason = "minor offense"
  elseif amount == config.MediumBountyAmount
    bountyReason = "significant criminal activity"
  elseif amount == config.LargeBountyAmount
    bountyReason = "serious crime"
  else
    bountyReason = "criminal behavior"
  endif
  
  main.RequestLLMResponseFromActor(speakerName + " writes in the official ledger, documenting the " + bountyReason + ". A bounty of " + amount + " gold is placed on " + playerName + "'s head. The total bounty in " + factionName + " is now " + newBounty + " gold. Word of this crime will spread to other guards in the hold.", "chatnf_minai_narrate", speakerName, "player")
endFunction

function Arrest(string speakerName, string guardFaction)
  main.Info("Arresting player by " + speakerName + " of faction " + guardFaction)
  
  ; Get the crime faction for this guard
  Faction crimeFaction = GetFactionByGuard(guardFaction)
  
  if !crimeFaction
    main.Warn("No crime faction found for guard: " + guardFaction)
    main.RequestLLMResponseFromActor("The guard seems uncertain about their authority in this location. After a moment of confusion, they abandon the arrest attempt.", "chatnf_minai_narrate", speakerName, "player")
    return
  endif
  
  ; Check if player has a bounty in this hold
  if crimeFaction.GetCrimeGold() <= 0
    string factionName = crimeFaction.GetName()
    main.RequestLLMResponseFromActor(speakerName + " consults a small notebook, checking for outstanding bounties. After scanning the pages, the guard looks up with slight embarrassment. \"Hmm, seems there's been a misunderstanding. You have no bounty in " + factionName + ". You're free to go.\"", "chatnf_minai_narrate", speakerName, "player")
    return
  endif
  
  ; Send player to jail immediately
  string factionName = crimeFaction.GetName()
  int bounty = crimeFaction.GetCrimeGold()
  string playerName = main.GetActorName(PlayerRef)
  crimeFaction.SendPlayerToJail()
  
  main.RequestLLMResponseFromActor(speakerName + " confiscates any stolen items and escorts " + playerName +" to the local prison, where they will serve their sentence.", "chatnf_minai_narrate", speakerName, "player")
endFunction

function ClearBounty(string speakerName, string guardFaction)
  main.Info("Clearing player bounty by " + speakerName + " of faction " + guardFaction)
  
  ; Get the crime faction for this guard
  Faction crimeFaction = GetFactionByGuard(guardFaction)
  
  if !crimeFaction
    main.Warn("No crime faction found for guard: " + guardFaction)
    main.RequestLLMResponseFromActor("The official appears confused, shuffling through documents with increasing frustration. \"I don't have the authority to clear bounties from other holds. You'll need to speak with the proper authorities.\"", "chatnf_minai_narrate", speakerName, "player")
    return
  endif
  
  ; Get the original bounty amount for reporting
  int originalBounty = crimeFaction.GetCrimeGold()
  
  if originalBounty <= 0
    string factionName = crimeFaction.GetName()
    main.RequestLLMResponseFromActor("The guard carefully examines the legal documents and ledgers for " + factionName + ". After flipping through several pages, they look up. \"There are no outstanding bounties or criminal charges against you in our records. Your name is clean here.\"", "chatnf_minai_narrate", speakerName, "player")
    return
  endif
  
  ; Clear the player's bounty (set to 0)
  crimeFaction.SetCrimeGold(0)
  crimeFaction.SetCrimeGoldViolent(0)
  
  string factionName = crimeFaction.GetName()
  string playerName = main.GetActorName(PlayerRef)
  
  main.RequestLLMResponseFromActor(speakerName + " reviews several official documents, comparing them with the bounty ledger. After careful consideration, the official stamps the papers with the hold's seal. \"Your debt to " + factionName + " has been settled. The bounty of " + originalBounty + " gold has been cleared from our records, " + playerName + ". You're once again welcome in our cities.\"", "chatnf_minai_narrate", speakerName, "player")
endFunction

; Helper function to get the crime faction from a guard's faction
Faction function GetFactionByGuard(string guardFaction)
  ; If guardFaction is empty, try to use the player's current location as fallback
  if guardFaction == ""
    return GetCurrentCrimeFaction()
  endif
  
  ; Look for a matching crime faction based on the guard faction name
  
  ; Try to match against known guard types
  if StringUtil.Find(guardFaction, "whiterun") >= 0
    return GetFactionByHoldName("Whiterun")
  elseif StringUtil.Find(guardFaction, "riften") >= 0 || StringUtil.Find(guardFaction, "rift") >= 0
    return GetFactionByHoldName("Rift")
  elseif StringUtil.Find(guardFaction, "windhelm") >= 0 || StringUtil.Find(guardFaction, "eastmarch") >= 0
    return GetFactionByHoldName("Eastmarch")
  elseif StringUtil.Find(guardFaction, "solitude") >= 0 || StringUtil.Find(guardFaction, "haafingar") >= 0
    return GetFactionByHoldName("Haafingar")
  elseif StringUtil.Find(guardFaction, "markarth") >= 0 || StringUtil.Find(guardFaction, "reach") >= 0
    return GetFactionByHoldName("Reach")
  elseif StringUtil.Find(guardFaction, "falkreath") >= 0
    return GetFactionByHoldName("Falkreath")
  elseif StringUtil.Find(guardFaction, "morthal") >= 0 || StringUtil.Find(guardFaction, "hjaalmarch") >= 0
    return GetFactionByHoldName("Hjaalmarch") 
  elseif StringUtil.Find(guardFaction, "dawnstar") >= 0 || StringUtil.Find(guardFaction, "pale") >= 0
    return GetFactionByHoldName("Pale")
  elseif StringUtil.Find(guardFaction, "winterhold") >= 0
    return GetFactionByHoldName("Winterhold")
  endif
  
  ; If we can't determine the faction from the guard faction, fall back to the player's current location
  main.Warn("Could not determine faction from guard faction: " + guardFaction + ", falling back to location")
  return GetCurrentCrimeFaction()
endFunction

; Helper function to find a crime faction by hold name
Faction function GetFactionByHoldName(string holdName)
  ; If CrimeFactionsList isn't initialized, we can't continue
  if !CrimeFactionsList 
    main.Error("Crime factions list not initialized")
    return None
  endif
  
  int count = CrimeFactionsList.GetSize()
  int i = 0
  while i < count
    Faction crimeFaction = CrimeFactionsList.GetAt(i) as Faction
    if crimeFaction
      string factionName = crimeFaction.GetName()
      
      if StringUtil.Find(factionName, holdName) >= 0
        return crimeFaction
      endif
    endif
    i += 1
  endWhile
  
  ; If we can't find by hold name, fall back to the player's current location
  main.Warn("Could not find faction for hold: " + holdName + ", falling back to location")
  return GetCurrentCrimeFaction()
endFunction

; Helper function to get the current crime faction based on player location (fallback method)
Faction function GetCurrentCrimeFaction()
  ; If CrimeFactionsList isn't initialized, we can't continue
  if !CrimeFactionsList
    main.Error("Crime factions list not initialized")
    return None
  endif
  
  Location currentLoc = PlayerRef.GetCurrentLocation()
  
  ; First try determining based on location name
  if currentLoc
    string locName = currentLoc.GetName()
    
    ; Check location name against known patterns
    if StringUtil.Find(locName, "whiterun") >= 0
      return GetFactionByHoldName("Whiterun")
    elseif StringUtil.Find(locName, "riften") >= 0 || StringUtil.Find(locName, "rift") >= 0
      return GetFactionByHoldName("Rift")
    elseif StringUtil.Find(locName, "windhelm") >= 0 || StringUtil.Find(locName, "eastmarch") >= 0
      return GetFactionByHoldName("Eastmarch")
    elseif StringUtil.Find(locName, "solitude") >= 0 || StringUtil.Find(locName, "haafingar") >= 0
      return GetFactionByHoldName("Haafingar")
    elseif StringUtil.Find(locName, "markarth") >= 0 || StringUtil.Find(locName, "reach") >= 0
      return GetFactionByHoldName("Reach")
    elseif StringUtil.Find(locName, "falkreath") >= 0
      return GetFactionByHoldName("Falkreath")
    elseif StringUtil.Find(locName, "morthal") >= 0 || StringUtil.Find(locName, "hjaalmarch") >= 0
      return GetFactionByHoldName("Hjaalmarch")
    elseif StringUtil.Find(locName, "dawnstar") >= 0 || StringUtil.Find(locName, "pale") >= 0
      return GetFactionByHoldName("Pale")
    elseif StringUtil.Find(locName, "winterhold") >= 0
      return GetFactionByHoldName("Winterhold")
    endif
  endif
  
  ; Next try to determine based on worldspace
  Worldspace currentWorldspace = PlayerRef.GetWorldspace()
  if currentWorldspace
    string worldspaceName = currentWorldspace.GetName()
    
    if StringUtil.Find(worldspaceName, "whiterun") >= 0
      return GetFactionByHoldName("Whiterun")
    elseif StringUtil.Find(worldspaceName, "riften") >= 0 || StringUtil.Find(worldspaceName, "rift") >= 0
      return GetFactionByHoldName("Rift")
    elseif StringUtil.Find(worldspaceName, "windhelm") >= 0 || StringUtil.Find(worldspaceName, "eastmarch") >= 0
      return GetFactionByHoldName("Eastmarch")
    elseif StringUtil.Find(worldspaceName, "solitude") >= 0 || StringUtil.Find(worldspaceName, "haafingar") >= 0
      return GetFactionByHoldName("Haafingar")
    elseif StringUtil.Find(worldspaceName, "markarth") >= 0 || StringUtil.Find(worldspaceName, "reach") >= 0
      return GetFactionByHoldName("Reach")
    elseif StringUtil.Find(worldspaceName, "falkreath") >= 0
      return GetFactionByHoldName("Falkreath")
    elseif StringUtil.Find(worldspaceName, "morthal") >= 0 || StringUtil.Find(worldspaceName, "hjaalmarch") >= 0
      return GetFactionByHoldName("Hjaalmarch")
    elseif StringUtil.Find(worldspaceName, "dawnstar") >= 0 || StringUtil.Find(worldspaceName, "pale") >= 0
      return GetFactionByHoldName("Pale")
    elseif StringUtil.Find(worldspaceName, "winterhold") >= 0
      return GetFactionByHoldName("Winterhold")
    endif
  endif
  
  ; Finally, as a fallback, try to determine by checking each faction's crime gold
  ; This works because only the current hold will have crime data for the player
  Faction factionWithBounty = None
  int highestBounty = 0
  
  int factionCount = CrimeFactionsList.GetSize()
  int i = 0
  while i < factionCount
    Faction crimeFaction = CrimeFactionsList.GetAt(i) as Faction
    if crimeFaction
      int bounty = crimeFaction.GetCrimeGold()
      if bounty > highestBounty
        highestBounty = bounty
        factionWithBounty = crimeFaction
      endif
    endif
    i += 1
  endWhile
  
  if factionWithBounty
    main.Warn("Fallback: Determined current jurisdiction by crime gold: " + factionWithBounty.GetName())
    return factionWithBounty
  endif
  
  ; If all else fails, try to find Whiterun faction as a default
  Faction defaultFaction = GetFactionByHoldName("Whiterun")
  if defaultFaction
    main.Warn("Could not determine current jurisdiction, defaulting to Whiterun")
    return defaultFaction
  endif
  
  ; If we can't even find Whiterun, just use the first valid faction
  i = 0
  int firstFactionCount = CrimeFactionsList.GetSize()
  while i < firstFactionCount
    Faction crimeFaction = CrimeFactionsList.GetAt(i) as Faction
    if crimeFaction
      main.Warn("Could not determine current jurisdiction, using first available faction")
      return crimeFaction
    endif
    i += 1
  endWhile
  
  ; If we get here, we're really out of options
  main.Error("No valid crime factions found")
  return None
endFunction

; Register our actions with the action registry
function RegisterActions()
  main.Info("Registering Crime actions with action registry")
  
  ; Register crime-related actions
  aiff.RegisterAction("ExtCmdAddBountySmall", "AddBountySmall", "Place a small bounty on the player", "Crime", 1, 5, 2, 5, 60, true)
  aiff.RegisterAction("ExtCmdAddBountyMedium", "AddBountyMedium", "Place a medium bounty on the player", "Crime", 1, 5, 2, 5, 60, true)
  aiff.RegisterAction("ExtCmdAddBountyLarge", "AddBountyLarge", "Place a large bounty on the player", "Crime", 1, 5, 2, 5, 60, true)
  aiff.RegisterAction("ExtCmdArrest", "Arrest", "Arrest the player for their crimes", "Crime", 1, 5, 2, 5, 60, true)
  aiff.RegisterAction("ExtCmdClearBounty", "ClearBounty", "Clear the player's bounty", "Crime", 1, 5, 2, 5, 60, true)
endFunction

Function SetContext(actor akTarget)
  if akTarget != PlayerRef
    return
  endif
  main.Info("Setting Crime context for actor: " + akTarget.GetName())
  string bountyContext = ""
  
  ; If CrimeFactionsList isn't initialized, we can't continue
  if !CrimeFactionsList
    main.Error("Crime factions list not initialized for bounty context")
    return
  endif
  
  ; Check each faction for bounties
  int factionCount = CrimeFactionsList.GetSize()
  int i = 0
  int bountyCount = 0
  
  while i < factionCount
    Faction crimeFaction = CrimeFactionsList.GetAt(i) as Faction
    if crimeFaction
      int bounty = crimeFaction.GetCrimeGold()
      string factionName = crimeFaction.GetName()
      if bounty > 0 && factionName != ""
        bountyContext += factionName + ":" + bounty + ";"
        bountyCount += 1
      endif
    endif
    i += 1
  endWhile
  
  ; If there are bounties, format the context string
  if bountyCount > 0
    main.Info("Found " + bountyCount + " active bounties for context")
    aiff.SetActorVariable(akTarget, "bountyContext", bountyContext)
  else
    main.Debug("No active bounties found for context")
    aiff.SetActorVariable(akTarget, "bountyContext", "")
  endif
endFunction 

Function StoreCrimeVariables()
    Main.Info("Storing crime variables: " + config.SmallBountyAmount + ", " + config.MediumBountyAmount + ", " + config.LargeBountyAmount)
    aiff.SetActorVariable(playerRef, "CrimeSmallBountyAmount", config.SmallBountyAmount)
    aiff.SetActorVariable(playerRef, "CrimeMediumBountyAmount", config.MediumBountyAmount)
    aiff.SetActorVariable(playerRef, "CrimeLargeBountyAmount", config.LargeBountyAmount)
EndFunction 