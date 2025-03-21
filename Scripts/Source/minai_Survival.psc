scriptname minai_Survival extends Quest

bool bHasSunhelm = False
bool bUseVanilla = True
bool bHasBFT = False
bool bHasCampfire = False
bool bHasSurvivalMode = False
bool bHasRequiem = False
bool bHasGourmet = False

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

GlobalVariable property Survival_ModeEnabled auto
GlobalVariable property Survival_HungerNeedValue auto
GlobalVariable property Survival_ColdNeedValue auto 
GlobalVariable property Survival_ExhaustionNeedValue auto
GlobalVariable property Survival_HungerNeedMaxValue auto
GlobalVariable property Survival_ColdNeedMaxValue auto
GlobalVariable property Survival_ExhaustionNeedMaxValue auto

MagicEffect property REQ_Effect_Alcohol auto
MagicEffect property REQ_Effect_Drug_Skooma01_FX auto

; Gourmet Alcohol Effects
MagicEffect property MAG_AlcoholDamageMagicka auto
MagicEffect property MAG_AlcoholDamageStamina auto
MagicEffect property MAG_AlcoholFortifyMagicka auto
MagicEffect property MAG_AlcoholFortifyStamina auto
MagicEffect property MAG_AlcoholUpgradeEffect01 auto
MagicEffect property MAG_AlcoholUpgradeEffect02 auto
MagicEffect property MAG_AlcoholUpgradePerkEffect01 auto
MagicEffect property MAG_AlcoholUpgradePerkEffect02 auto

; Gourmet Drug Effects
MagicEffect property MAG_DrugsAddictionEffect auto
MagicEffect property MAG_DrugsDamageHealth auto
MagicEffect property MAG_DrugsDamageMagicka auto
MagicEffect property MAG_DrugsDamageStamina auto
MagicEffect property MAG_DrugsFortifyHealth auto
MagicEffect property MAG_DrugsFortifyHealthAlt auto
MagicEffect property MAG_DrugsFortifyHealthRegen auto
MagicEffect property MAG_DrugsFortifyMagicka auto
MagicEffect property MAG_DrugsFortifyMagickaRegen auto
MagicEffect property MAG_DrugsFortifyStamina auto
MagicEffect property MAG_DrugsFortifyStaminaRegen auto
MagicEffect property MAG_DrugsVisualEffectEversnow auto
MagicEffect property MAG_DrugsVisualEffectSkooma auto
MagicEffect property MAG_DrugsVisualEffectSkoomaRed auto
MagicEffect property MAG_DrugsVisualEffectSleepingTreeSap auto

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

  aiff.RegisterAction("ExtCmdServeFood", "ServeFood", "Survival", "Receive food from an inn keeper/server", 1, 5, 2, 5, 60, bHasSunhelm)
  aiff.RegisterAction("ExtCmdRentRoom", "RentRoom", "Rent a room from an inn keeper", "Survival", 1, 30, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdTrade", "Trade", "Open the buy/sell menu", "Survival", 1, 5, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdCarriageRide", "CarriageRide", "Request a carriage ride to a destination", "Survival", 1, 5, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdTrainSkill", "TrainSkill", "Receive training in a skill", "Survival", 1, 5, 2, 5, 60, True)
  
  if Game.GetModByName("ccQDRSSE001-SurvivalMode.esl") != 255
    bHasSurvivalMode = True
    Main.Info("Found Survival Mode")
    
    ; Get essential global variables
    Survival_ModeEnabled = Game.GetFormFromFile(0x0826, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    Survival_HungerNeedValue = Game.GetFormFromFile(0x081A, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    Survival_ColdNeedValue = Game.GetFormFromFile(0x081B, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    Survival_ExhaustionNeedValue = Game.GetFormFromFile(0x0816, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    Survival_HungerNeedMaxValue = Game.GetFormFromFile(0x080C, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    Survival_ColdNeedMaxValue = Game.GetFormFromFile(0x084B, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    Survival_ExhaustionNeedMaxValue = Game.GetFormFromFile(0x084A, "ccQDRSSE001-SurvivalMode.esl") as GlobalVariable
    
    if !Survival_ModeEnabled || !Survival_HungerNeedValue || !Survival_ColdNeedValue || !Survival_ExhaustionNeedValue
        Main.Error("Could not load all Survival Mode references")
        Main.Debug("Survival_ModeEnabled: " + Survival_ModeEnabled)
        Main.Debug("Survival_HungerNeedValue: " + Survival_HungerNeedValue)
        Main.Debug("Survival_ColdNeedValue: " + Survival_ColdNeedValue)
        Main.Debug("Survival_ExhaustionNeedValue: " + Survival_ExhaustionNeedValue)
        bHasSurvivalMode = False
    EndIf
    Main.Debug("Survival Mode Values - Hunger: " + Survival_HungerNeedValue.GetValue() + "/" + Survival_HungerNeedMaxValue.GetValue() + ", Cold: " + Survival_ColdNeedValue.GetValue() + "/" + Survival_ColdNeedMaxValue.GetValue() + ", Exhaustion: " + Survival_ExhaustionNeedValue.GetValue() + "/" + Survival_ExhaustionNeedMaxValue.GetValue())
  EndIf
  aiff.SetModAvailable("SurvivalMode", bHasSurvivalMode)
  aiff.SetModAvailable("Sunhelm", bHasSunhelm)
  aiff.SetModAvailable("BetterFastTravel", bHasBFT)

  if Game.GetModByName("Requiem.esp") != 255
    bHasRequiem = True
    Main.Info("Found Requiem")
    REQ_Effect_Alcohol = Game.GetFormFromFile(0x18ED7C, "Requiem.esp") as MagicEffect
    REQ_Effect_Drug_Skooma01_FX = Game.GetFormFromFile(0x8191518, "Requiem.esp") as MagicEffect
    if !REQ_Effect_Alcohol || !REQ_Effect_Drug_Skooma01_FX
      Main.Error("Could not load Requiem effects - Intoxication state tracking will be disabled")
      bHasRequiem = False
    EndIf
  EndIf

  ; Load Gourmet effects
  if Game.GetModByName("Gourmet.esp") != 255
    bHasGourmet = True
    Main.Info("Found Gourmet")
    
    ; Load alcohol effects
    MAG_AlcoholDamageMagicka = Game.GetFormFromFile(0x01B806, "Gourmet.esp") as MagicEffect
    MAG_AlcoholDamageStamina = Game.GetFormFromFile(0x01B803, "Gourmet.esp") as MagicEffect
    MAG_AlcoholFortifyMagicka = Game.GetFormFromFile(0x01B804, "Gourmet.esp") as MagicEffect
    MAG_AlcoholFortifyStamina = Game.GetFormFromFile(0x01B805, "Gourmet.esp") as MagicEffect
    MAG_AlcoholUpgradeEffect01 = Game.GetFormFromFile(0x01B927, "Gourmet.esp") as MagicEffect
    MAG_AlcoholUpgradeEffect02 = Game.GetFormFromFile(0x01B928, "Gourmet.esp") as MagicEffect
    MAG_AlcoholUpgradePerkEffect01 = Game.GetFormFromFile(0x01B925, "Gourmet.esp") as MagicEffect
    MAG_AlcoholUpgradePerkEffect02 = Game.GetFormFromFile(0x01B926, "Gourmet.esp") as MagicEffect
    
    ; Load drug effects
    MAG_DrugsAddictionEffect = Game.GetFormFromFile(0x01B95F, "Gourmet.esp") as MagicEffect
    MAG_DrugsDamageHealth = Game.GetFormFromFile(0x01B93F, "Gourmet.esp") as MagicEffect
    MAG_DrugsDamageMagicka = Game.GetFormFromFile(0x01B823, "Gourmet.esp") as MagicEffect
    MAG_DrugsDamageStamina = Game.GetFormFromFile(0x01B824, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyHealth = Game.GetFormFromFile(0x01B82E, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyHealthAlt = Game.GetFormFromFile(0x01B942, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyHealthRegen = Game.GetFormFromFile(0x01B941, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyMagicka = Game.GetFormFromFile(0x01B825, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyMagickaRegen = Game.GetFormFromFile(0x01B940, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyStamina = Game.GetFormFromFile(0x01B826, "Gourmet.esp") as MagicEffect
    MAG_DrugsFortifyStaminaRegen = Game.GetFormFromFile(0x01B93E, "Gourmet.esp") as MagicEffect
    MAG_DrugsVisualEffectEversnow = Game.GetFormFromFile(0x01B973, "Gourmet.esp") as MagicEffect
    MAG_DrugsVisualEffectSkooma = Game.GetFormFromFile(0x01B827, "Gourmet.esp") as MagicEffect
    MAG_DrugsVisualEffectSkoomaRed = Game.GetFormFromFile(0x01B82D, "Gourmet.esp") as MagicEffect
    MAG_DrugsVisualEffectSleepingTreeSap = Game.GetFormFromFile(0x0E3CBB, "Gourmet.esp") as MagicEffect

    ; Verify all effects loaded correctly
    if !MAG_AlcoholDamageMagicka || !MAG_AlcoholDamageStamina || !MAG_AlcoholFortifyMagicka || !MAG_AlcoholFortifyStamina || \
       !MAG_DrugsAddictionEffect || !MAG_DrugsDamageHealth || !MAG_DrugsDamageMagicka || !MAG_DrugsDamageStamina || \
       !MAG_DrugsFortifyHealth || !MAG_DrugsFortifyHealthAlt || !MAG_DrugsFortifyHealthRegen || !MAG_DrugsFortifyMagicka || \
       !MAG_DrugsFortifyStaminaRegen || !MAG_DrugsVisualEffectEversnow || !MAG_DrugsVisualEffectSkooma || !MAG_DrugsVisualEffectSkoomaRed
      Main.Error("Could not load all Gourmet effects - Drug/Alcohol state tracking will be disabled")
      bHasGourmet = False
    EndIf
  EndIf

  aiff.SetModAvailable("Requiem", bHasRequiem)
  aiff.SetModAvailable("Gourmet", bHasGourmet)
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
      main.RegisterEvent(main.GetActorName(player) + " began to trade with " + Main.GetActorName(akSpeaker), "info_trade")
    EndIf
    if stringutil.Find(sayLine, "-gift-") != -1
      akSpeaker.ShowGiftMenu(true)
    EndIf
    if stringutil.Find(sayLine, "-undress-") != -1
      akSpeaker.UnequipAll()
    endif
EndFunction











Event CommandDispatcher(String speakerName,String  command, String parameter)
  Actor akSpeaker=aiff.AIGetAgentByName(speakerName)
  actor akTarget= aiff.AIGetAgentByName(parameter)
  if !akTarget
    akTarget = PlayerRef
  EndIf
  Main.Debug("Survival - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  string targetName = main.GetActorName(akTarget)
  ; Sunhelm
  if command == "ExtCmdServeFood"
    Main.Debug("Feeding Player")
    FeedPlayer(akSpeaker, PlayerRef)
    Main.RegisterEvent("" + targetName + " paid for a meal, which "+speakerName+" then served to them.", "info_meal_served")
  EndIf
  ; Vanilla functionality
  if command == "ExtCmdRentRoom"
    Main.Debug("Renting Room")
    if playerRef.GetItemCount(Gold) < (DialogueGeneric as DialogueGenericScript).RoomRentalCost.GetValue() as Int
      Debug.Notification("AI: Player does not have enough gold to rent room.")
      Main.RegisterEvent("" + targetName + " did not have enough gold for the room.", "info_room_denied")
    Else
      (akSpeaker as RentRoomScript).RentRoom(DialogueGeneric as DialogueGenericScript)
      Main.RegisterEvent(""+targetName + " paid for a room, which "+speakerName+" then provided to them.", "info_room_rented")
    EndIf
  EndIf
  if command == "ExtCmdTrade"
    akSpeaker.showbartermenu()
    Main.RegisterEvent(""+speakerName+" started trading goods with " + targetName + ".", "info_trade")
  EndIf
  if command == "ExtCmdCarriageRide"
    ; Parameter has destination
    int destination = GetDestination(parameter)
    carriageScript.Travel(destination, akSpeaker)
    Main.RegisterEvent(""+speakerName+" gave " + targetName + " a ride in a carriage to " + destination + ".", "info_carriage_ride")
  EndIf
    if command == "ExtCmdTrainSkill"
    Main.Debug(speakerName + " is training the player")
    Game.ShowTrainingMenu(akSpeaker)
    Main.RegisterEvent(""+speakerName+" gave " + targetName + " some training.", "info_training")
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
  Main.Debug("SetContext Survival(" + main.GetActorName(akTarget) + ")")
  if !aiff
    return
  EndIf
  if akTarget == playerRef
    if bHasSunhelm
      aiff.SetActorVariable(playerRef, "hunger", sunhelmMain.Hunger.CurrentHungerStage)
      aiff.SetActorVariable(playerRef, "thirst", sunhelmMain.Thirst.CurrentThirstStage)
      aiff.SetActorVariable(playerRef, "fatigue", sunhelmMain.Fatigue.CurrentFatigueStage)
      aiff.SetActorVariable(playerRef, "cold", sunhelmMain.Cold.CurrentColdStage)
    ElseIf bHasSurvivalMode && Survival_ModeEnabled.GetValueInt() == 1
      ; Convert needs to percentage values for consistency
      float hungerPercent = ((Survival_HungerNeedValue.GetValue() / Survival_HungerNeedMaxValue.GetValue())) * 100
      float coldPercent = (Survival_ColdNeedValue.GetValue() / Survival_ColdNeedMaxValue.GetValue()) * 100
      float exhaustionPercent = (Survival_ExhaustionNeedValue.GetValue() / Survival_ExhaustionNeedMaxValue.GetValue()) * 100
      
      ; Store values as integers 0-100 for consistency with Sunhelm
      aiff.SetActorVariable(playerRef, "hunger", hungerPercent as int)
      aiff.SetActorVariable(playerRef, "cold", coldPercent as int)
      aiff.SetActorVariable(playerRef, "fatigue", exhaustionPercent as int)
    EndIf
    ; Track intoxication effects
    if bHasRequiem
      if REQ_Effect_Alcohol
        aiff.SetActorVariable(playerRef, "isDrunk", playerRef.HasMagicEffect(REQ_Effect_Alcohol))
      EndIf
      if REQ_Effect_Drug_Skooma01_FX
        aiff.SetActorVariable(playerRef, "isOnSkooma", playerRef.HasMagicEffect(REQ_Effect_Drug_Skooma01_FX))
      EndIf
    EndIf
    
    ; Track Gourmet effects if mod is present
    if bHasGourmet
      ; Track alcohol effects and set isDrunk if any alcohol effect is active
      bool hasAnyAlcoholEffect = playerRef.HasMagicEffect(MAG_AlcoholDamageMagicka) || \
                                playerRef.HasMagicEffect(MAG_AlcoholDamageStamina) || \
                                playerRef.HasMagicEffect(MAG_AlcoholFortifyMagicka) || \
                                playerRef.HasMagicEffect(MAG_AlcoholFortifyStamina) || \
                                playerRef.HasMagicEffect(MAG_AlcoholUpgradeEffect01) || \
                                playerRef.HasMagicEffect(MAG_AlcoholUpgradeEffect02) || \
                                playerRef.HasMagicEffect(MAG_AlcoholUpgradePerkEffect01) || \
                                playerRef.HasMagicEffect(MAG_AlcoholUpgradePerkEffect02)
      
      if hasAnyAlcoholEffect
        aiff.SetActorVariable(playerRef, "isDrunk", true)
      ElseIf !bHasRequiem 
        aiff.SetActorVariable(playerRef, "isDrunk", false)
      EndIf
      
      ; Track drug effects and set isOnSkooma if any drug effect is active
      bool hasAnyDrugEffect = playerRef.HasMagicEffect(MAG_DrugsAddictionEffect) || \
                             playerRef.HasMagicEffect(MAG_DrugsDamageHealth) || \
                             playerRef.HasMagicEffect(MAG_DrugsDamageMagicka) || \
                             playerRef.HasMagicEffect(MAG_DrugsDamageStamina) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyHealth) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyHealthAlt) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyHealthRegen) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyMagicka) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyMagickaRegen) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyStamina) || \
                             playerRef.HasMagicEffect(MAG_DrugsFortifyStaminaRegen) || \
                             playerRef.HasMagicEffect(MAG_DrugsVisualEffectEversnow) || \
                             playerRef.HasMagicEffect(MAG_DrugsVisualEffectSkooma) || \
                             playerRef.HasMagicEffect(MAG_DrugsVisualEffectSkoomaRed)
      
      if hasAnyDrugEffect
        aiff.SetActorVariable(playerRef, "isOnSkooma", true)
      ElseIf !bHasRequiem 
        aiff.SetActorVariable(playerRef, "isOnSkooma", false)
      EndIf
    EndIf
    
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
    Main.RequestLLMResponseFromActor(playerName + " set up a tent.", "chatnf_minai_narrate", "everyone", "player")
  EndIf
EndEvent


Event Campfire_OnObjectRemoved(Form akBaseObject, float afPositionX, float afPositionY, float afPositionZ, float afAngleX, float afAngleY, float afAngleZ, bool abIsTent)
  string playerName = Main.GetActorName(playerRef)
  if abIsTent
    Main.RequestLLMResponseFromActor(playerName + " took down a tent.", "chatnf_minai_narrate", "everyone", "player")
  EndIf
EndEvent

Event Campfire_OnBedrollSitLay(Form akTent, bool abGettingUp)
  string playerName = Main.GetActorName(playerRef)
  if !abGettingUp
    Main.RequestLLMResponseFromActor(playerName + " laid down on a bedroll.", "chatnf_minai_narrate", "everyone", "player")
  else
    ; This might be too spammy if it's chat, since they'll also get the "goodmorning" message at the same time
    Main.RequestLLMResponseFromActor(playerName + " got up from a bedroll.", "chatnf_minai_narrate", "everyone", "player")
  endif
endEvent


Event Campfire_OnTentEnter(Form akTent, bool abHasShelter)
  string playerName = Main.GetActorName(playerRef)
  if abHasShelter
    Main.RequestLLMResponseFromActor(playerName + " entered their tent, which has adequate shelter.", "chatnf_minai_narrate", "everyone", "player")
  else
    Main.RequestLLMResponseFromActor(playerName + " entered their tent, which is unsheltered from the elements.", "chatnf_minai_narrate", "everyone", "player")
  endif
endEvent

Event Campfire_OnTentLeave()
  ; This seems to fire at inappropriate times
  ; string playerName = Main.GetActorName(playerRef)
  ; Main.RequestLLMResponse(playerName + " left their tent.", "chatnf_survival_1", playerName)
endEvent

bool Function HasActiveSurvivalMod()
    return (bHasSunhelm || (bHasSurvivalMode && Survival_ModeEnabled.GetValueInt() == 1))
EndFunction
