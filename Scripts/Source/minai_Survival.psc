scriptname minai_Survival extends Quest

bool bHasSunhelm = False
bool bUseVanilla = True
bool bHasBFT = False
bool bHasCampfire = False

_shweathersystem sunhelmWeather
_SunHelmMain property sunhelmMain auto
Sound sunhelmFoodEatSound
Sound property sunhelmFillBottlesSound auto

Form Gold
Quest DialogueGeneric
Faction JobInnKeeper
Faction JobInnServer

CarriageSystemScript carriageScript

minai_MainQuestController main
minai_Mantella minMantella
minai_AIFF aiff

actor playerRef

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  minMantella = (Self as Quest) as minai_Mantella
  aiff = (Self as Quest) as minai_AIFF
  
  main.Info("Initializing Survival Module")
  if Game.GetModByName("SunhelmSurvival.esp") != 255
    bHasSunhelm = True
    Main.Info("Found Sunhelm")
    sunhelmMain = Game.GetFormFromFile(0x000D61, "SunhelmSurvival.esp") as _sunhelmmain
    sunhelmWeather = Game.GetFormFromFile(0x989760, "SunhelmSurvival.esp") as _shweathersystem
    sunhelmFoodEatSound = Game.GetFormFromFile(0x5674E1, "SunhelmSurvival.esp") as Sound
    sunhelmFillBottlesSound = Game.GetFormFromFile(0x4BB249, "SunhelmSurvival.esp") as Sound

    if !sunhelmMain || !sunhelmWeather|| !sunhelmFoodEatSound
      Main.Error("Could not load all sunhelm references")
    EndIf    
  EndIf
  if Game.GetModByName("Campfire.esm") != 255
    bHasCampfire = True
    RegisterForModEvent("Campfire_OnObjectPlaced", "Campfire_OnObjectPlaced")
    RegisterForModEvent("Campfire_OnObjectRemoved", "Campfire_OnObjectRemoved")
    RegisterForModEvent("Campfire_OnBedrollSitLay", "Campfire_OnBedrollSitLay")
    RegisterForModEvent("Campfire_OnTentEnter", "Campfire_OnTentEnter")
    RegisterForModEvent("Campfire_OnTentLeave", "Campfire_OnTentLeave")
  EndIf
  carriageScript = Game.GetFormFromFile(0x17F01, "Skyrim.esm") as CarriageSystemScript
  if !carriageScript
    Main.Error("Could not get reference to carriageScript")
  EndIf
  if Game.GetModByName("BFT Ships and Carriages.esp") != 255
    bHasBFT = true
  EndIf
  ; Vanilla Integrations
  gold = Game.GetFormFromFile(0x0000000F, "Skyrim.esm")
  if !gold
    Main.Error("- Could not get reference to gold?")
  EndIf

  DialogueGeneric = Game.GetFormFromFile(0x13EB3, "Skyrim.esm") as Quest
  if !DialogueGeneric
    Main.Error("- Could not get handle to DialogueGeneric.")
  EndIf

  JobInnKeeper = Game.GetFormFromFile(0x5091B, "Skyrim.esm") as Faction
  JobInnServer = Game.GetFormFromFile(0xDEE93, "Skyrim.esm") as Faction

  if !JobInnKeeper || !JobInnServer
    Main.Error("- Failed to fetch vanilla factions")
  EndIf
  aiff.SetModAvailable("Sunhelm", bHasSunhelm)
  aiff.SetModAvailable("BetterFastTravel", bHasBFT)
  aiff.RegisterAction("ExtCmdServeFood", "ServeFood", "Survival", "Receive food from an inn keeper/server", 1, 5, 2, 5, 60, bHasSunhelm)
  aiff.RegisterAction("ExtCmdRentRoom", "RentRoom", "Rent a room from an inn keeper", "Survival", 1, 30, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdTrade", "Trade", "Open the buy/sell menu", "Survival", 1, 5, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdCarriageRide", "CarriageRide", "Request a carriage ride to a destination", "Survival", 1, 5, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdTrainSkill", "TrainSkill", "Receive training in a skill", "Survival", 1, 5, 2, 5, 60, True)
  
EndFunction


Function FeedPlayer(Actor akSpeaker, Actor player)
  if player.GetItemCount(Gold) < 20
    Debug.Notification("AI: Player has insufficient gold for meal.")
    return
  EndIf
  player.RemoveItem(Gold, 20)

  int thirstVal = 100
  float perkModifier = 0.0 ; Depricated
  if(sunhelmMain.Thirst.IsRunning())
      sunhelmMain.Thirst.DecreaseThirstLevel(thirstVal)
  endif
  if(sunhelmMain.Hunger.IsRunning())
      sunhelmMain.Hunger.DecreaseHungerLevel(165 + (165 * perkModifier))
  endif
  sunhelmFoodEatSound.Play(Game.GetPlayer())
   If Player.GetAnimationVariableInt("i1stPerson") as Int == 1
      if(Player.GetSitState() == 0)
          ;    Debug.SendAnimationEvent(Player, "idleEatingStandingStart")
          ;    Utility.Wait(7.0)
          ;    Player.PlayIdle(IdleStop_Loose)
      elseif(Player.GetSitState() == 3)
          Game.ForceThirdPerson()
          Utility.Wait(1.0)
          Debug.SendAnimationEvent(Player, "ChairEatingStart")
          Utility.Wait(1.0)
          Game.ForceFirstPerson()
      endif
  else
      if(Player.GetSitState() == 0)
          Debug.SendAnimationEvent(Player, "idleEatingStandingStart")
      elseif(Player.GetSitState() == 3)
          Debug.SendAnimationEvent(Player, "ChairEatingStart")
      endif
  endif
EndFunction






Function UpdateEvents(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList, bool bPlayerInScene, string targetName, string speakerName, string playerName)
  ; Sunhelm Integrations
  if bHasSunhelm && bPlayerInScene && (minMantella.FactionInScene(JobInnServer, actorsFromFormList) || minMantella.FactionInScene(JobInnKeeper, actorsFromFormList))
    main.RegisterAction("!" + speakerName + " is a server at an inn. If " + speakerName + " wants to serve " + playerName + " any kind of food or meal, include the keyword '-servefood-' keyword in your response.!")
  EndIf

  ; Vanilla Integrations
  if minMantella.FactionInScene(JobInnKeeper, actorsFromFormList) && bPlayerInScene
    main.RegisterAction("!" + speakerName + " is an innkeeper at an inn. If " + speakerName + " wants to allow " + playerName + " to rent a room for the night at the inn, include the keyword '-rentroom-' in your response.!")
  EndIf
EndFunction

bool Function UseVanilla()
  return bUseVanilla
EndFunction

bool Function UseSunhelm()
  return bHasSunhelm
EndFunction




Function ActionResponse(actor akTarget, actor akSpeaker, string sayLine, actor[] actorsFromFormList)
  actor player = Game.GetPlayer()
    ; Sunhelm
    if stringUtil.Find(sayLine, "-servefood-") != -1
      FeedPlayer(akSpeaker, Player)
    EndIf
    ; Vanilla functionality
    if stringUtil.Find(sayLine, "-rentroom-") != -1
      if player.GetItemCount(Gold) < (DialogueGeneric as DialogueGenericScript).RoomRentalCost.GetValue() as Int
        Debug.Notification("AI: Player does not have enough gold to rent room.")
      Else
        (akSpeaker as RentRoomScript).RentRoom(DialogueGeneric as DialogueGenericScript)
      EndIf
    EndIf  
    ; Replicated the functions from MGO's NSFW plugin, as they're handy
    if stringutil.Find(sayLine, "-gear-") != -1
      akSpeaker.OpenInventory(true)
    EndIf
    if stringutil.Find(sayLine, "-trade-") != -1
      akSpeaker.showbartermenu()
      main.RegisterEvent(main.GetActorName(player) + " began to trade with " + Main.GetActorName(akSpeaker))
    EndIf
    if stringutil.Find(sayLine, "-gift-") != -1
      akSpeaker.ShowGiftMenu(true)
    EndIf
    if stringutil.Find(sayLine, "-undress-") != -1
      akSpeaker.UnequipAll()
    endif
EndFunction











Event CommandDispatcher(String speakerName,String  command, String parameter)
  Actor akSpeaker=AIAgentFunctions.getAgentByName(speakerName)
  actor akTarget= AIAgentFunctions.getAgentByName(parameter)
  if !akTarget
    akTarget = PlayerRef
  EndIf
  Main.Debug("Survival - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  string targetName = main.GetActorName(akTarget)
  ; Sunhelm
  if command == "ExtCmdServeFood"
    Main.Debug("Feeding Player")
    FeedPlayer(akSpeaker, PlayerRef)
    Main.RegisterEvent(""+speakerName+" served " + targetName + " a meal.")
  EndIf
  ; Vanilla functionality
  if command == "ExtCmdRentRoom"
    Main.Debug("Renting Room")
    if playerRef.GetItemCount(Gold) < (DialogueGeneric as DialogueGenericScript).RoomRentalCost.GetValue() as Int
      Debug.Notification("AI: Player does not have enough gold to rent room.")
      Main.RegisterEvent("" + targetName + " did not have enough gold for the room.")
    Else
      (akSpeaker as RentRoomScript).RentRoom(DialogueGeneric as DialogueGenericScript)
      Main.RegisterEvent(""+speakerName+" provided " + targetName + " a room for the night.")
    EndIf
  EndIf
  if command == "ExtCmdTrade"
    akSpeaker.showbartermenu()
    Main.RegisterEvent(""+speakerName+" started trading goods with " + targetName + ".")
  EndIf
  if command == "ExtCmdCarriageRide"
    ; Parameter has destination
    int destination = GetDestination(parameter)
    carriageScript.Travel(destination, akSpeaker)
    Main.RegisterEvent(""+speakerName+" gave " + targetName + " a ride in a carriage to " + destination + ".")
  EndIf
    if command == "ExtCmdTrainSkill"
    Main.Debug(speakerName + " is training the player")
    Game.ShowTrainingMenu(akSpeaker)
    Main.RegisterEvent(""+speakerName+" gave " + targetName + " some training.")
  EndIf
EndEvent


int Function GetDestination(string destination)
  if destination == "Whiterun"
    return 1
  elseif destination == "Solitude"
    return 2
  elseif destination == "Markarth"
    return 3
  elseif destination == "Riften"
    return 4
  elseif destination == "Windhelm"
    return 5
  elseif destination == "Morthal"
    return 6
  elseif destination == "Dawnstar"
    return 7
  elseif destination == "Falkreath"
    return 8
  elseif destination == "Winterhold"
    return 9
  ;; BYOH locations
  elseif destination == "Darkwater Crossing"
    return 10
  elseif destination == "Dragon Bridge"
    return 11
  elseif destination == "Ivarstead"
    return 12
  elseif destination == "Karthwasten"
    return 13
  elseif destination == "Kynesgrove"
    return 14
  elseif destination == "Old Hroldan"
    return 15
  elseif destination == "Riverwood"
    return 16
  elseif destination == "Rorikstead"
    return 17
  elseif destination == "Shor's Stone"
    return 18
  elseif destination == "Stonehills"
    return 19
  elseif destination == "HalfMoonMill"
    return 120
  elseif destination == "HeartwoodMill"
    return 121
  elseif destination == "AngasMill"
    return 122
  elseif destination == "LakeviewManor"
    return 123
  elseif destination == "WindstadManor"
    return 124
  elseif destination == "HeljarchenHall"
    return 125
  elseif destination == "DayspringCanyon"
    return 126
  elseif destination == "Helgen"
    return 127
  EndIf
  return 0
EndFunction


float Function GetCurrentHourOfDay() 
	float Time = Utility.GetCurrentGameTime()
	Time -= Math.Floor(Time) ; Remove "previous in-game days passed" bit
	Time *= 24 ; Convert from fraction of a day to number of hours
	Return Time

EndFunction

Function SetContext(actor akTarget)
  if !aiff
    return
  EndIf
  if bHasSunhelm
    aiff.SetActorVariable(playerRef, "hunger", sunhelmMain.Hunger.CurrentHungerStage)
    aiff.SetActorVariable(playerRef, "thirst", sunhelmMain.Thirst.CurrentThirstStage)
    aiff.SetActorVariable(playerRef, "fatigue", sunhelmMain.Fatigue.CurrentFatigueStage)
  EndIf
  if akTarget == playerRef
    aiff.SetActorVariable(playerRef, "weather", Weather.GetCurrentWeather())
    aiff.SetActorVariable(playerRef, "skyMode", Weather.GetSkyMode())
    aiff.SetActorVariable(playerRef, "currentGameHour", GetCurrentHourOfDay())
  EndIf
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction

string Function GetFactionsForActor(actor akTarget)
  string ret = ""
  ret += aiff.GetFactionIfExists(akTarget, "JobInnServer", JobInnServer)
  ret += aiff.GetFactionIfExists(akTarget, "JobInnKeeper", JobInnKeeper)
  aiff.SetActorVariable(akTarget, "isChild", akTarget.IsChild())
  return ret
EndFunction


Event Campfire_OnObjectPlaced(Form akPlacedObject, float afPositionX, float afPositionY, float afPositionZ, float afAngleX, float afAngleY, float afAngleZ, bool abIsTent)
  string playerName = Main.GetActorName(playerRef)
  if abIsTent
    Main.RequestLLMResponse(playerName + " set up a tent.", "chatnf_survival_1")
  EndIf
EndEvent


Event Campfire_OnObjectRemoved(Form akBaseObject, float afPositionX, float afPositionY, float afPositionZ, float afAngleX, float afAngleY, float afAngleZ, bool abIsTent)
  string playerName = Main.GetActorName(playerRef)
  if abIsTent
    Main.RequestLLMResponse(playerName + " took down a tent.", "chatnf_survival_1")
  EndIf
EndEvent

Event Campfire_OnBedrollSitLay(Form akTent, bool abGettingUp)
  string playerName = Main.GetActorName(playerRef)
  if !abGettingUp
    Main.RequestLLMResponse(playerName + " laid down on a bedroll.", "chatnf_survival_1")
  else
    ; This might be too spammy if it's chat, since they'll also get the "goodmorning" message at the same time
    Main.RequestLLMResponse(playerName + " got up from a bedroll.", "chatnf_survival_1")
  endif
endEvent


Event Campfire_OnTentEnter(Form akTent, bool abHasShelter)
  string playerName = Main.GetActorName(playerRef)
  if abHasShelter
    Main.RequestLLMResponse(playerName + " entered their tent, which has adequate shelter.", "chatnf_survival_1")
  else
    Main.RequestLLMResponse(playerName + " entered their tent, which is unsheltered from the elements.", "chatnf_survival_1")
  endif
endEvent

Event Campfire_OnTentLeave()
  ; This seems to fire at inappropriate times
  ; string playerName = Main.GetActorName(playerRef)
  ; Main.RequestLLMResponse(playerName + " left their tent.", "chatnf_survival_1", playerName)
endEvent
