scriptname minai_Survival extends Quest

bool bHasSunhelm = False
bool bUseVanilla = True

_shweathersystem sunhelmWeather
_SunHelmMain property sunhelmMain auto
Sound sunhelmFoodEatSound
Sound property sunhelmFillBottlesSound auto

Form Gold
Quest DialogueGeneric
Faction JobInnKeeper
Faction JobInnServer


minai_MainQuestController main
minai_Mantella minMantella

function Maintenance(minai_MainQuestController _main)
  main = _main
  minMantella = (Self as Quest) as minai_Mantella
  Debug.Trace("[minai] Initializing Survival Module")
  if Game.GetModByName("SunhelmSurvival.esp") != 255
    bHasSunhelm = True
    Debug.Trace("[minai] Found Sunhelm")
    sunhelmMain = Game.GetFormFromFile(0x000D61, "SunhelmSurvival.esp") as _sunhelmmain
    sunhelmWeather = Game.GetFormFromFile(0x989760, "SunhelmSurvival.esp") as _shweathersystem
    sunhelmFoodEatSound = Game.GetFormFromFile(0x5674E1, "SunhelmSurvival.esp") as Sound
    sunhelmFillBottlesSound = Game.GetFormFromFile(0x4BB249, "SunhelmSurvival.esp") as Sound

    if !sunhelmMain || !sunhelmWeather|| !sunhelmFoodEatSound
      Debug.Trace("[minai] Could not load all sunhelm references")
    EndIf    
  EndIf

  ; Vanilla Integrations
  gold = Game.GetFormFromFile(0x0000000F, "Skyrim.esm")
  if !gold
    Debug.Trace("[minai] - Could not get reference to gold?")
  EndIf

  DialogueGeneric = Game.GetFormFromFile(0x13EB3, "Skyrim.esm") as Quest
  if !DialogueGeneric
    Debug.Trace("[minai] - Could not get handle to DialogueGeneric.")
  EndIf

  JobInnKeeper = Game.GetFormFromFile(0x5091B, "Skyrim.esm") as Faction
  JobInnServer = Game.GetFormFromFile(0xDEE93, "Skyrim.esm") as Faction

  if !JobInnKeeper || !JobInnServer
    Debug.Trace("[minai] - Failed to fetch vanilla factions")
  EndIf

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
      main.RegisterEvent(main.GetActorName(player) + " began to trade with " + akSpeaker.GetActorBase().GetName())
    EndIf
    if stringutil.Find(sayLine, "-gift-") != -1
      akSpeaker.ShowGiftMenu(true)
    EndIf
    if stringutil.Find(sayLine, "-undress-") != -1
      akSpeaker.UnequipAll()
    endif
EndFunction