scriptname minai_Sex extends Quest

SexLabFramework slf


bool bHasOstim = False
bool bHasSexlab = False
GlobalVariable minai_UseOstim
int clothingMap = 0
int descriptionsMap
bool bHasAIFF

minai_AIFF aiff
minai_MainQuestController main
minai_DeviousStuff devious
Actor PlayerRef
minai_Config config

float lastDirtyTalk

Message minai_ConfirmSexMsg
string lastTag

; what are chances that random speaker will be female sex. 0 - female actors won't talk; 100 - only female actors will talk
; todo make it mcm slider
int commentFemaleWeight = 100

int threadsPrevSpeedsMap

Function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  devious = (Self as Quest) as minai_DeviousStuff
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  Main.Info("Initializing Sex Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  minai_ConfirmSexMsg = Game.GetFormFromFile(0x0914, "MinAI.esp") as Message

  RegisterForModEvent("HookStageStart", "OnStageStart")
  RegisterForModEvent("HookOrgasmStart", "PostSexScene")
  RegisterForModEvent("HookAnimationEnd", "EndSexScene")
  RegisterForModEvent("HookAnimationStart", "OnAnimationStart")
  RegisterForModEvent("SexLabOrgasmSeparate", "SLSOOrgasm")
  
  RegisterForModEvent("ostim_thread_start", "OStimManager")
  RegisterForModEvent("ostim_thread_scenechanged", "OStimManager")
  RegisterForModEvent("ostim_thread_speedchanged", "OStimManager")
  RegisterForModEvent("ostim_actor_orgasm", "OStimManager")
  RegisterForModEvent("ostim_thread_end", "OStimManager")

  threadsPrevSpeedsMap = JValue.releaseAndRetain(threadsPrevSpeedsMap, JMap.object())
    
  slf = Game.GetFormFromFile(0xD62, "SexLab.esm") as SexLabFramework
  if slf != None
    Main.Info("Found Sexlab")
    bHasSexlab = True
  EndIf
  if Game.GetModByName("OStim.esp") != 255
    Main.Info("Found OStim")
    bHasOstim = True
  EndIf

  minai_UseOStim = Game.GetFormFromFile(0x0906, "MinAI.esp") as GlobalVariable
  if !minai_UseOStim
    Main.Error("Could not find ostim toggle")
  EndIf
  
  ; clean any threads from table
  UpdateThreadTable("clean")
  InitializeSexDescriptions()
  lastDirtyTalk = 0.0
  if (clothingMap == 0)
    Main.Debug("Initializing clothing map")
    clothingMap = JMap.object()
    JValue.retain(clothingMap)
  else
    Main.Debug("Clothing map already initialized, id=" + clothingMap)
  EndIf
  lastTag = ""
  aiff.SetAISexEnabled(config.enableAISex)
  aiff.SetModAvailable("Ostim", bHasOstim)
  aiff.SetModAvailable("Sexlab", slf != None)
  aiff.RegisterAction("ExtCmdRemoveClothes", "RemoveClothes", "Take off all clothing", "Sex1", 1, 5, 2, 5, 300, True)
  aiff.RegisterAction("ExtCmdPutOnClothes", "PutOnClothes", "Put all clothing back on", "Sex1", 1, 5, 2, 5, 300, True)
  aiff.RegisterAction("ExtCmdMasturbate", "Masturbate", "Begin Masturbating", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartOrgy", "Orgy", "Start Sex with all nearby AI Actors", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdEndSex", "EndSex", "Finish the Current Sex Scene", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  ; aiff.RegisterAction("ExtCmdStartSexScene", "StartSexScene", "ExtCmdStartSexScene", "Sex", 1, 5, 2, 5, 300)
  aiff.RegisterAction("ExtCmdStartBlowjob", "StartBlowjob", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartAnal", "StartAnal", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartVaginal", "StartVaginal", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartHandjob", "StartHandjob", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFootjob", "StartFootjob", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartBoobjob", "StartBoobjob", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCunnilingus", "StartCunnilingus", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFacial", "StartFacial", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCumonchest", "StartCumonchest", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartRubbingclitoris", "StartRubbingclitoris", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartDeepthroat", "StartDeepthroat", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartRimjob", "StartRimjob", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFingering", "StartFingering", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartMissionarySex", "StartMissionarySex", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCowgirlSex", "StartCowgirlSex", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartReverseCowgirl", "StartReverseCowgirl", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartDoggystyle", "StartDoggystyle", "Sex Position", "Sex2", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFacesitting", "StartFacesitting", "Sex Position", "Sex3", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStart69Sex", "Start69Sex", "Sex Position", "Sex3", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartGrindingSex", "StartGrindingSex", "Sex Position", "Sex3", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartThighjob", "StartThighjob", "Sex Position", "Sex3", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCuddleSex", "StartCuddleSex", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartKissingSex", "StartKissingSex", "Sex Position", "Sex1", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  
  aiff.RegisterAction("ExtCmdSpeedUpSex", "SpeedUpSex", "Sex Intensity", "Sex3", 1, 3, 1, 1, 300, (bHasOstim))
  aiff.RegisterAction("ExtCmdSlowDownSex", "SlowDownSex", "Sex Intensity", "Sex3", 1, 3, 1, 1, 300, (bHasOstim))

  ; Temporarily disabled until bugs can be addressed
  aiff.RegisterAction("ExtCmdComeWithMe", "ComeWithMe", "Start Following Player", "General", 1, 0, 2, 5, 300, false, true)
  aiff.RegisterAction("ExtCmdEndComeWithMe", "EndComeWithMe", "End Following Player", "General", 1, 0, 2, 5, 300, false, true)
EndFunction



bool Function CanAnimate(actor akTarget)
  if (akTarget.IsOnMount())
    return False
  EndIf
  if bHasOstim && (minai_UseOStim.GetValue() == 1.0 && !OActor.IsInOStim(akTarget))
    return True
  EndIf
  if slf && (!slf.IsActorActive(akTarget))
    return True
  EndIf
  return False
EndFunction



Function Start1pSex(actor akSpeaker)
  if CanAnimate(akSpeaker)
    if bHasOstim && minai_UseOStim.GetValue() == 1.0
      OThread.QuickStart(OActorUtil.ToArray(akSpeaker))
    else
      if devious.HasDD()
        devious.libs.Masturbate(akSpeaker)
      else	
        slf.Quickstart(akSpeaker)
      EndIf
    EndIf
  EndIf
EndFunction



Function StartSexScene(actor[] actors, bool bPlayerInScene, string tags="")
  if config.confirmSex && bPlayerInScene
    int result = minai_confirmSexMsg.Show()
    if result == 1
      Main.Info("User declined sex scene, aborting")
      return
    EndIf
  EndIf
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    tags = ConvertTagsOstim(tags)
    actors = OActorUtil.Sort(actors, OActorUtil.EmptyArray())
    string newScene = OLibrary.GetRandomSceneWithAnyActionCSV(actors, tags)
    Utility.Wait(0.5)
    if newScene == ""
      newScene = OLibrary.GetRandomSceneWithAnySceneTagCSV(actors, tags)
      Utility.Wait(1)
      if newScene == ""
        Main.Debug("No OStim scene found for: " + tags)
      EndIf
    EndIf
    int ActiveOstimThreadID = OThread.Quickstart(actors, newScene)
    Main.Debug("Found " + tags + " scene: " + newScene + " for OStim Thread [" + ActiveOstimThreadID + "].")
  else
    StartSexlabScene(bPlayerInScene, actors, tags)
  EndIf
  
  if actors.Length == 1
    if devious.HasDD() && actors[0].WornHasKeyword(devious.libs.zad_DeviousBelt)
      main.RegisterEvent(Main.GetActorName(actors[0]) + " started unsuccessfully trying to masturbate through the chastity belt. (" + tags +") ", "info_sexscene")
    Else
      main.RegisterEvent(Main.GetActorName(actors[0]) + " started masturbating. (" + tags +") ", "info_sexscene")
    EndIf
  elseif actors.Length == 2
    main.RegisterEvent(Main.GetActorName(actors[0]) + " and " + Main.GetActorName(actors[1]) + " started having sex. (" + tags +") ", "info_sexscene")
  elseif actors.Length > 2
    string sexStr = ""
    int i = 0
    while i < actors.Length
      sexStr += actors[i]
      if i != actors.Length - 1
         sexStr += " and "
      EndIf
      i += 1
    EndWhile
    ; sexStr += " started having sex. (" + tags +") "
    Main.RegisterEvent(sexStr, "info_sexscene")
  EndIf
EndFunction



Function StartSexOrSwitchToGroup(actor[] actors, actor akSpeaker, string tags="")
  Main.Info("Sex: Starting/switching for " + actors.Length + " actors (tags: " + tags + ")")
  bool bSpeakerInScene = False
  bool bPlayerInScene = False
  bool bCanAnimate = True
  bool isNewScene = False
  int actor_idx = 0
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    Main.Debug("OStim detected - processing scene request")
    ; Ostim
    Actor[] ostimActors = actors
    while actor_idx < ostimActors.Length
      if ostimActors[actor_idx] == PlayerRef
        bPlayerInScene = True
      EndIf
      actor_idx += 1
    EndWhile
    tags = ConvertTagsOstim(tags)
    int ActiveOstimThreadID = OActor.GetSceneID(akSpeaker)
    if ActiveOstimThreadID < 0
    Main.Debug("OStim detects akSpeaker is not in thread: " + ActiveOstimThreadID)
      ; akSpeaker is not in an OStim thread
      if ostimActors.Length == 1
        OThread.QuickStart(OActorUtil.ToArray(akSpeaker))
      elseif ostimActors.Length > 1
        ActiveOstimThreadID = OActor.GetSceneID(ostimActors[1])
        if ActiveOstimThreadID < 0
          Main.Debug("OStim detects target 1 is not in thread: " + ActiveOstimThreadID)
          ; Target 1 is not in an OStim thread
          if OActor.VerifyActors(ostimActors)
          StartSexScene(ostimActors, bPlayerInScene, tags)
          EndIf
        else
          ; Target 1 is already in an OStim thread
          Main.Debug("OStim detects target 1 is already in thread: " + ActiveOstimThreadID)
          ostimActors = OThread.GetActors(ActiveOstimThreadID)
          ; add akSpeaker to OStim actor array
          ostimActors = PapyrusUtil.PushActor(ostimActors,akSpeaker)
          ostimActors = OActorUtil.Sort(ostimActors, OActorUtil.EmptyArray())
          Main.Debug("OStim added akSpeaker to array and sorted: " + ostimActors)
          OThread.Stop(ActiveOstimThreadID)
          Utility.Wait(2)
          StartSexScene(ostimActors, bPlayerInScene, tags)
        EndIf
      EndIf
    else
      ; akSpeaker is already in an OStim thread
      ostimActors = OThread.GetActors(ActiveOstimThreadID)
      Main.Debug("Searching for random " + tags + " scene.")
      string newScene = OLibrary.GetRandomSceneWithAnyActionCSV(ostimActors, tags)
      Utility.Wait(0.5)
      if newScene == ""
        newScene = OLibrary.GetRandomSceneWithAnySceneTagCSV(ostimActors, tags)
        Utility.Wait(1)
      EndIf
      Main.Debug("Ostim scene transition to: " + newScene + " for OStim Thread [" + ActiveOstimThreadID + "].")
      if OThread.IsRunning(ActiveOstimThreadID)
        OThread.NavigateTo(ActiveOstimThreadID, newScene)
        if OThread.IsInAutoMode(ActiveOstimThreadID)
          OThread.StopAutoMode(ActiveOstimThreadID)
          Utility.Wait(5)
          OThread.StartAutoMode(ActiveOstimThreadID)
        EndIf
      EndIf
      ; main.RegisterEvent(Main.GetActorName(akSpeaker) + " changed the sex scene to " + tags + " instead: " + newScene, "info_sexscene")
      Return
    EndIf
  else
    ; Sexlab
    Actor[] actorsInScene
    int i = 0
    int numMales = 0
    int numFemales = 0
    int threadID = slf.FindPlayerController()
    sslThreadController Controller
    Controller = slf.ThreadSlots.GetController(threadID)
    if (!Controller)
      isNewScene = true
      actorsInScene = actors
    else
      actorsInScene = Controller.Positions
    EndIf
    while i < actorsInScene.Length
      if actorsInScene[i] == akSpeaker && !isNewScene
        bSpeakerInScene = True
      EndIf
      if actorsInScene[i] == PlayerRef
        bPlayerInScene = True
      EndIf
      if slf.GetGender(actorsInScene[i]) == 0
        numMales += 1
      Else
        NumFemales += 1
      EndIf
      if !CanAnimate(actorsInScene[i])
        bCanAnimate = False
      EndIf
      i += 1
    EndWhile

    if lastTag != "" && lastTag == tags && bSpeakerInScene
      Main.Warn("Aborting StartSexOrSwitchTo: Tag '" + tags + "' is the same as previous tag '" + lastTag +"'")
      return
    EndIf
    lastTag = tags
    if isNewScene
      StartSexScene(actors, bPlayerInScene, tags)
    else
      if !bSpeakerInScene ; Speaker not in scene, add them to it
        Main.Info("Sex: Speaker was not in scene, adding them to it.")
	      actorsInScene = PapyrusUtil.PushActor(actorsInScene,akSpeaker)
        actorsInScene = slf.SortActors(actorsInScene)
        if slf.GetGender(akSpeaker) == 0
          numMales += 1
        else
          numFemales += 1
        endIf
      EndIf
      sslBaseAnimation[] animations = FindSexlabAnimations(actorsInScene, numMales, numFemales, tags)
      if actorsInScene.Length != actors.Length || !bSpeakerInScene
        Main.Info("Sex: Number of actors changed, forcing update")
        Controller.ChangeActors(actorsInScene)
	      Controller.SetAnimations(animations)
        Utility.Wait(1)
      EndIf
      ; If this isn't set, it means that a new actor is joining from the orgy command.
      ; Prefer to reuse the existing tag for the added actor
      if tags == ""
        tags == lastTag
      EndIf
      Controller.SetForcedAnimations(animations)
      Controller.SetAnimation()
      if actors.Length == 1
        main.RegisterEvent(Main.GetActorName(actors[0]) + " changed up their masturbation to " + tags + " instead.", "info_sexscene")
      elseif actors.Length == 2
        main.RegisterEvent(Main.GetActorName(actors[0]) + " and " + Main.GetActorName(actors[1]) + " changed up their sex to " + tags + " instead.", "info_sexscene")
      elseif actors.Length > 2
        string sexStr = ""
        i = 0
        while i < actors.Length
          sexStr += actors[i]
          if i != actors.Length - 1
             sexStr += " and "
          EndIf
          i += 1
        EndWhile
        sexStr += " changed up their sex to " + tags + " instead."
        Main.RegisterEvent(sexStr, "info_sexscene")
      EndIf
    EndIf
  EndIf
EndFunction



Function StartSexOrSwitchTo(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene, string tags)
  Main.Info("StartSexOrSwitchTo( " + Main.GetActorName(akSpeaker) + ", " + Main.GetActorName(akTarget) + ", " + tags + " )")
  Actor[] actors = new Actor[2]
  actors[0] = akSpeaker
  actors[1] = akTarget
  StartSexOrSwitchToGroup(actors, akSpeaker, tags)
EndFunction



Function SpeedUpSex(actor akActor)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    int ostimTid = OActor.GetSceneID(akActor)
    if OThread.IsRunning(ostimTid)
      int ostimSpeed = OThread.GetSpeed(ostimTid)
      ostimSpeed += 1
      OThread.SetSpeed(ostimTid, ostimSpeed)
    EndIf
  EndIf
EndFunction



Function SlowDownSex(actor akActor)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    int ostimTid = OActor.GetSceneID(akActor)
    if OThread.IsRunning(ostimTid)
      int ostimSpeed = OThread.GetSpeed(ostimTid)
      ostimSpeed -= 1
      OThread.SetSpeed(ostimTid, ostimSpeed)
    EndIf
  EndIf
EndFunction



Function EndSex(actor akSpeaker)
  if OActor.IsInOStim(akSpeaker)
    int ActiveOstimThreadID = OActor.GetSceneID(akSpeaker)
    if OThread.IsRunning(ActiveOstimThreadID)
      OThread.Stop(ActiveOstimThreadID)
    EndIf
  else
    ; add SL Stop
    int threadID = slf.FindPlayerController()
    EndSexScene(threadID, True)
    ;;; WILL THIS WORK?
  EndIf
EndFunction



bool Function CompareActorSex(actor actor1, actor actor2)
  ; We want to sort males to the end, and females to the front.
  ; 0 = male
  ; 1 = female
  ; 2 = other
  return slf.GetGender(actor1) < slf.GetGender(actor2)
EndFunction



Function StartSexlabScene(bool bPlayerInScene, actor[] actorsToSort, string tags)
  Main.Debug("SortActorsForSex(" + bPlayerInScene +")")
  Main.Debug("Sorting actors: " + actorsToSort)
  ; Basic insertion sort implmentation to sort female actors to start of list
  int index = 1
  actor currentActor
  while index < actorsToSort.Length
    currentActor = actorsToSort[index]
    int position = index
    while (position > 0 && CompareActorSex(actorsToSort[position - 1], currentActor))
      actorsToSort[position] = actorsToSort[position - 1]
      position = position - 1
    Endwhile
    actorsToSort[position] = currentActor
    index += 1
  Endwhile

  int i = 0
  int numMales = 0
  int numFemales = 0
  while i < actorsToSort.Length
    if slf.GetGender(actorsToSort[i]) == 0
      numMales += 1
    else
      numFemales += 1
    EndIf
    i += 1
  Endwhile
  
  Main.Debug("Done Sorting actors (" + numMales + " males, " + numFemales + " females): " + actorsToSort)
  slf.StartSex(actorsToSort, FindSexlabAnimations(actorsToSort, numMales, numFemales, tags))
EndFunction

Function StartFollow(actor akSpeaker, actor akTarget)
  if (!bHasAIFF)
    Main.Warn("StartFollow: AIFF not available")
    return
  EndIf

  ; only follow if the target is the player
  if (akTarget != PlayerRef)
    if (akTarget)
      Main.Error("StartFollowPlayer: Target is not the player: " + akTarget.GetName())
    else
      Main.Error("StartFollowPlayer: Target is None")
    EndIf
    return
  EndIf
  aiff.StartFollowTarget(akSpeaker, akTarget)
EndFunction

Function EndFollow(actor akSpeaker)
  if (!bHasAIFF)
    Main.Warn("EndFollow: AIFF not available")
    return
  EndIf

  aiff.EndFollowTarget(akSpeaker)
EndFunction


sslBaseAnimation[] Function FindSexlabAnimations(actor[] actors, int numMales, int numFemales, string tags, bool forceaggressive = false)
  sslBaseAnimation[] ret
  string suppressTag = "forced,"
  if numMales == 0
    suppressTag += "MM,MF"
  EndIf
  if numFemales == 0
    suppressTag += "FF,MF,"
  EndIf
  if numMales == 1
    suppressTag += "MM,"
  EndIf
  if numFemales == 1
    suppressTag += "FF,"
  EndIf
  if devious.HasDD()
    main.Debug("FindValidSexlabAnimations: Using DD's SelectValidDDAnimations")
    ret = devious.libs.SelectValidDDAnimations(actors, numMales + numFemales, forceaggressive, tags, suppressTag)
  EndIf
  if !ret
    Main.Debug("FindValidSexlabAnimations: Falling back to slf getanimation functions")
    if tags == ""
      return slf.GetAnimationsByDefault(numMales, numFemales)
    else
      return slf.GetAnimationsByTags(numMales + numFemales, tags, suppressTag)
    EndIf
  EndIf
  return ret
EndFunction



bool Function UseSex()
  return slf != None || bHasOstim
EndFunction



Function ActionResponse(actor akTarget, actor akSpeaker, string sayLine, actor[] actorsFromFormList, bool bPlayerInScene)
  actor Player = game.GetPlayer()
  ; Mutually Exclusive keywords
  if stringutil.Find(sayLine, "-masturbate-") != -1
    Start1pSex(akSpeaker)
  elseif stringutil.Find(sayLine, "-startsex-") != -1 || stringUtil.Find(sayLine, "-sex-") != -1 || stringUtil.Find(sayLine, "-fuck-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "vaginal")
  elseif stringutil.Find(sayLine, "-vaginalsex-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "vaginal")
  elseif stringutil.Find(sayLine, "-analsex-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "anal")
  elseif stringutil.Find(sayLine, "-missionary-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "missionary")
  elseif stringutil.Find(sayLine, "-cowgirl-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "cowgirl")
  elseif stringutil.Find(sayLine, "-reversecowgirl-") != -1 || stringutil.Find(sayLine, "-reversedcowgirl-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "reversecowgirl")
  elseif stringutil.Find(sayLine, "-doggystyle-") != -1 || stringutil.Find(sayLine, "-kittystyle-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "doggystyle")
  elseif stringutil.Find(sayLine, "-sixtynine-") != -1 || stringutil.Find(sayLine, "-69-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "sixtynine,69")
  elseif stringutil.Find(sayLine, "-blowjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "blowjob")
  elseif stringutil.Find(sayLine, "-handjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "handjob")
  elseif stringutil.Find(sayLine, "-footjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "footjob")
  elseif stringutil.Find(sayLine, "-boobjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "boobjob")
  elseif stringutil.Find(sayLine, "-cunnilingus-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "cunnilingus")
  elseif stringutil.Find(sayLine, "-facesitting-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "facesitting")
  elseif stringutil.Find(sayLine, "-facial-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "facial")
  elseif stringutil.Find(sayLine, "-cumonchest-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "chestcum")
  elseif stringutil.Find(sayLine, "-rubbingclitoris-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "pussy")
  elseif stringutil.Find(sayLine, "-deepthroat-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "oral")
  elseif stringutil.Find(sayLine, "-rimjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "rimjob")
  elseif stringutil.Find(sayLine, "-fingering-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "fingering")
  elseif stringutil.Find(sayLine, "-vampirebite-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "vampire")
  elseif stringutil.Find(sayLine, "-suckingnipple-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "breastfeeding")
  ; Could not find SL equivalent for these
  elseif stringutil.Find(sayLine, "-grinding-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "grindingpenis,buttjob")
  elseif stringutil.Find(sayLine, "-thighjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "thighjob")
  ; experimental kiss animation for OStim
  elseif stringutil.Find(sayLine, "-smooch-") != -1
    if bHasOstim && minai_UseOStim.GetValue() == 1.0
      OThread.Quickstart(OActorUtil.ToArray(akSpeaker, akTarget), "OARE_HoldingChinKiss")
    EndIf
  ; Kissing/Cuddling experimental alternates - leads to sex
  elseif stringutil.Find(sayLine, "-cuddling-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "hugging")
  elseif stringutil.Find(sayLine, "-frenchkissing-") != -1 || stringutil.Find(sayLine, "-frenchkiss-") != -1 || stringutil.Find(sayLine, "-french kissing-") != -1 || stringutil.Find(sayLine, "-french kiss-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "kissing")
  elseIf stringutil.Find(sayLine, "-groupsex-") != -1 || stringUtil.Find(sayLine, "-orgy-") != -1 || stringUtil.Find(sayLine, "-threesome-") != -1
    StartSexOrSwitchToGroup(actorsFromFormList, akSpeaker)
  elseif stringutil.Find(sayLine, "-endsex-") != -1 || stringutil.Find(sayLine, "-end sex-") != -1 || stringutil.Find(sayLine, "-stopsex-") != -1 || stringutil.Find(sayLine, "-stop sex-") != -1 || stringutil.Find(sayLine, "-red-") != -1
    EndSex(akSpeaker)
    
  elseif stringutil.Find(sayLine, "-speedup-") != -1 || stringutil.Find(sayLine, "-speed up-") != -1 || stringutil.Find(sayLine, "-faster-") != -1 || stringutil.Find(sayLine, "-gofaster-") != -1 || stringutil.Find(sayLine, "-go faster-") != -1
    SpeedUpSex(akSpeaker)
  elseif stringutil.Find(sayLine, "-slowdown-") != -1 || stringutil.Find(sayLine, "-slow down-") != -1 || stringutil.Find(sayLine, "-slower-") != -1 || stringutil.Find(sayLine, "-goslower-") != -1 || stringutil.Find(sayLine, "-go slower-") != -1
    SlowDownSex(akSpeaker)
  EndIf
EndFunction



Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf
  Main.Debug("Sex - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  actor akSpeaker = AIAgentFunctions.getAgentByName(speakerName)
  actor akTarget = AIAgentFunctions.getAgentByName(parameter)
  actor[] actorsFromFormList = AIAgentFunctions.findAllNearbyAgents()
  if !akTarget
    akTarget = PlayerRef
  EndIf
  if (akTarget.IsChild())
    Main.Warn(Main.GetActorName(akTarget) + " is a child actor. Not processing actions.")
    return
  EndIf
  bool bPlayerInScene = (akTarget == PlayerRef || akSpeaker == PlayerRef)

  string targetName = main.GetActorName(akTarget)
  If command == "ExtCmdMasturbate"
    Start1pSex(akSpeaker)
  elseif command == "ExtCmdStartSexScene"
    ; not sure why it was here but it didn't start actual scene, only set in conf_opts to true
  elseif command == "ExtCmdStartBlowjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "blowjob")
  elseif command == "ExtCmdStartAnal"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "anal")
  elseif command == "ExtCmdStartVaginal"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "vaginal")
  elseif command == "ExtCmdStartHandjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "handjob")
  elseif command == "ExtCmdStartFootjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "footjob")
  elseif command == "ExtCmdStartBoobjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "boobjob")
  elseif command == "ExtCmdStartCunnilingus"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "cunnilingus")
  elseif command == "ExtCmdStartFacial"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "facial")
  elseif command == "ExtCmdStartCumonchest"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "chestcum")
  elseif command == "ExtCmdStartRubbingclitoris"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "pussy")
  elseif command == "ExtCmdStartDeepthroat"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "oral")
  elseif command == "ExtCmdStartRimjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "rimjob")
  elseif command == "ExtCmdStartFingering"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "fingering")
  elseif command == "ExtCmdStartMissionarySex"  
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "missionary")
  elseif command == "ExtCmdStartCowgirlSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "cowgirl")
  elseif command == "ExtCmdStartReverseCowgirl"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "reversecowgirl")
  elseif command == "ExtCmdStartDoggystyle"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "doggystyle")
  elseif command == "ExtCmdStartFacesitting"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "facesitting")
  elseif command == "ExtCmdStart69Sex"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "sixtynine,69")
  elseif command == "ExtCmdStartGrindingSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "grindingpenis,buttjob")
  elseif command == "ExtCmdStartThighjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "thighjob")
  elseif command == "ExtCmdStartCuddleSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "hugging")
  elseif command == "ExtCmdStartKissingSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, PlayerRef, bPlayerInScene, "kissing")
  elseIf command == "ExtCmdStartOrgy"
    actor[] actors = aiff.GetNearbyAI()
    actors = PapyrusUtil.PushActor(actors,playerRef)
    if (actors.Length > 5)
      actor[] newActors = new actor[5];
      int i = 0;
      while i  < 5
        newActors[i] = actors[i]
        i += 1
      EndWhile
      actors = newActors
    EndIf
    if bHasOstim && minai_UseOStim.GetValue() == 1.0
      actors = OActorUtil.Sort(actors, OActorUtil.EmptyArray()) ; 2nd param is array of dominant actors
    else
      actors = slf.SortActors(actors)
    EndIf
    StartSexOrSwitchToGroup(actors, akSpeaker, "")
  elseif command == "ExtCmdEndSex"
    EndSex(akSpeaker)
  elseif command == "ExtCmdSpeedUpSex"
    SpeedUpSex(akSpeaker)
  elseif command == "ExtCmdSlowDownSex"
    SlowDownSex(akSpeaker)

  elseif (command=="ExtCmdRemoveClothes")
    Form[] equippedItems=PO3_SKSEFunctions.AddAllEquippedItemsToArray(akSpeaker);
    int equippedArmor = JArray.Object()
    JValue.retain(equippedArmor)
    Int iElement = equippedItems.Length
    Main.Debug("Removing clothes (clothingMap=" + clothingMap + ",id=" + equippedArmor + " + " + JValue.Count(equippedArmor) + " ) for " + speakerName)
    Int iIndex = 0
    while iIndex < iElement
      if devious.HasDD() && equippedItems[iIndex].HasKeyword(devious.libs.zad_Lockable)
        Main.Debug("Not removing " + equippedItems[iIndex] + " - Lockable DD")
      else
        Main.Debug("Removing " + equippedItems[iIndex].GetName())
        JArray.AddForm(equippedArmor, equippedItems[iIndex])
        akSpeaker.UnequipItem(equippedItems[iIndex])
      EndIf
      iIndex += 1
    Endwhile
    JMap.setObj(clothingMap, speakerName, equippedArmor)
    AIAgentFunctions.logMessageForActor("command@ExtCmdRemoveClothes@@"+speakerName+" removes clothes and armor","funcret",speakerName)
  elseif (command=="ExtCmdPutOnClothes")
    int equippedItems=JMap.getObj(clothingMap,speakerName) as Int;
    Int iElement = JValue.count(equippedItems)
    Main.Debug("Equipping clothes (clothingMap=" + clothingMap + ",id=" + equippedItems + " + " + JValue.Count(equippedItems) + " ) for " + speakerName)
    Int iIndex = 0
    while iIndex < iElement
      Form item=JArray.GetForm(equippedItems, iIndex)
      main.Debug("Equipping " + item.GetName())
      akSpeaker.EquipItem(item);
      iIndex += 1
    Endwhile
    equippedItems = JValue.release(equippedItems)
    AIAgentFunctions.logMessageForActor("command@ExtCmdPutOnClothes@@"+speakerName+" puts on clothes and armor","funcret",speakerName)
  elseif (command=="ExtCmdComeWithMe")
    Main.Debug("ExtCmdComeWithMe: is called")
    StartFollow(akSpeaker, akTarget)
  elseif (command=="ExtCmdEndComeWithMe")
    Main.Debug("ExtCmdEndComeWithMe: is called")
    EndFollow(akSpeaker)
  EndIf
EndEvent

string Function ConvertTagsOstim(string tags)
  ; Convert sexlab tags to ostim tags
  if tags == "anal"
    tags = "analsex"
  elseif tags == "vaginal"
    tags = "vaginalsex"
  elseif tags == "oral"
    tags = "deepthroat"
  elseif tags == "fingering"
    tags = "vaginalfingering"
  elseif tags == "cunnilingus"
    tags = "cunnilingus,lickingvagina,oralfingering"
  elseif tags == "breastfeeding"
    tags = "suckingnipple"
  elseif tags == "chestcum"
    tags = "cumonchest"
  elseif tags == "pussy"
    tags = "rubbingclitoris"
  elseif tags == "vampire"
    tags = "vampirebite"
  elseif tags == "hugging"
    tags = "cuddling"
  elseif tags == "kissing"
    tags = "frenchkissing"
  EndIf
  return tags
EndFunction



Event OStimManager(string eventName, string strArg, float numArg, Form sender)
  int ostimTid = numArg as int
  ; ostim thread with index 0 is reserved for player scenes
  bool playerInvolved = ostimTid == 0
  bool isRunning = OThread.IsRunning(ostimTid)
  string sceneId = strArg
  Main.Debug("oStim eventName: "+eventName+", strArg: "+strArg+", numArg: "+numArg as int+" sender: "+sender as actor )
  if (eventName == "ostim_thread_start")
    resetPrevSpeed(ostimTid)
    if isRunning
      if playerInvolved
        AIFF.ChillOut()
      EndIf
      UpdateThreadTable("startthread", "ostim", ostimTid)
      Main.Info("OStim scene startthread")
    else
      Main.Debug("OStim thread start failed")
    EndIf
  
  elseif (eventName == "ostim_thread_scenechanged")
    Actor[] actors = OThread.GetActors(ostimTid)
    ; reset previous speed on scene change, so if different scene has different default speed it won't count as speed change
    resetPrevSpeed(ostimTid)
    if isRunning
      if(playerInvolved)
        AIFF.ChillOut()
      endif
      Main.Info("Ostim Scene changed to: " + sceneId)
      ; we don't want to catch transition scenes they usually couple of seconds which isn't enough to have conversation
      if(!OMetadata.isTransition(sceneId))
        UpdateThreadTable("scenechange", "ostim", ostimTid)
        sexTalkSceneChage(GetWeightedRandomActorToSpeak(actors))
      endif
    else
      Main.Debug("OStim scene change failed")
    EndIf
    
  elseif (eventName == "ostim_thread_speedchanged")
    int newSpeed = strArg as int
    int prevSpeed = getPrevSpeed(ostimTid)
    
    ; when thread starts it fires this event, also on scene change if scenes have different default speed it will fire this event, but in this case scene change is more critical for us
    if(!prevSpeed || prevSpeed == -1 || prevSpeed == newSpeed)
      setPrevSpeed(ostimTid, newSpeed)
      return 
    endif

    Actor[] actors = OThread.GetActors(ostimTid)
    bool increase = newSpeed > prevSpeed
    if isRunning
      sexTalkSpeedChange(GetWeightedRandomActorToSpeak(actors), increase)
      Main.Info("Ostim speed change")
      setPrevSpeed(ostimTid, newSpeed)
    else
      Main.Debug("OStim speed change failed")
    EndIf
  elseif (eventName == "ostim_actor_orgasm")   
    Actor OrgasmedActor = sender as Actor
    sexTalkClimax(OrgasmedActor)
    Main.Info("Ostim actor orgasm: " + OrgasmedActor)

  elseif (eventName == "ostim_thread_end")  
    Main.Info("OStim scene ended")
    UpdateThreadTable("end", "ostim", ostimTid)
    removePrevSpeed(ostimTid)
  EndIf
EndEvent



Function LoadSexlabDescriptions()
  if (descriptionsMap==0)
    Main.Info("Loading Sexlab Descriptions")
    descriptionsMap=JValue.readFromFile( "Data/Data/minai/sexlab_descriptions.json")
    JValue.retain(descriptionsMap)
    Main.Info("Descriptions set: "+JMap.count(descriptionsMap)+" using map: "+descriptionsMap+ " Data/Data/minai/sexlab_descriptions.json")
  EndIf
EndFunction


Event OnAnimationStart(int tid, bool HasPlayer)
  LoadSexlabDescriptions()
  Actor[] actorList = slf.GetController(tid).Positions
  int i = actorList.Length
  bool bPlayerInScene=false
  while(i > 0)
    i -= 1
    if (actorList[i]==playerRef) 
      bPlayerInScene=true;
    EndIf
  Endwhile
  if (bPlayerInScene)
    AIFF.ChillOut()
  EndIf
  Main.Info("Started Sex Scene")
  UpdateThreadTable("startthread", "sexlab", tid)
EndEvent



String Function GetActorNameForSex(actor akActor)
  ; Prefer base name, fall back to display name, and then other.
  string ret = Main.GetActorName(akActor)
  if ret
    return ret
  EndIf
  ret = Main.GetActorName(akActor)
  if ret
    return ret
  EndIf
  return "a monster"
EndFunction



Event OnStageStart(int tid, bool HasPlayer)
  sslThreadController controller = slf.GetController(tid)
  
  if (controller.Stage==1) 
    LoadSexlabDescriptions()
  EndIf
  
  Actor[] sortedActorList = slf.GetController(tid).Positions

  if (sortedActorList.length < 1)
    return
  EndIf

  int i = 0
  while i < sortedActorList.Length
    if sortedActorList[i] != PlayerRef
      aiff.setAnimationBusy(1, Main.GetActorName(sortedActorList[i]))
    EndIf
    i += 1
  EndWhile

  UpdateThreadTable("scenechange", "sexlab", tid)
  
  if (controller.Stage < (controller.Animation.StageCount()))
    if bHasAIFF && AiAgentFunctions.isGameVR() 
     ; VR users will have dirty talk through physics integration instead
     ; Reenabled this temporarily while figuring out female player character collisions during sex.
     ; Works much better for male atm, need to add different colliders
     sexTalkSceneChage(GetWeightedRandomActorToSpeak(sortedActorList))
    else
      sexTalkSceneChage(GetWeightedRandomActorToSpeak(sortedActorList))
    EndIf
  EndIf
EndEvent

Event SLSOOrgasm(Form actorRef, Int tid)
  ; Make sure that the actor is an Actor, this should always that case but just in case
  Actor actorInAction = actorRef as Actor
  If (actorInAction == None)
    Main.Debug("[SLSOOrgasm] No actor in action")
    return
  EndIf

  Main.Debug("[SLSOOrgasm] name = " + actorInAction.GetName() + " tid: " + tid)
  sslThreadController controller = slf.GetController(tid)
  Actor[] actorList = slf.HookActors(tid)

  if (actorList.length < 1)
    return
  EndIf
  
  sexTalkClimax(actorInAction)
EndEvent

Event PostSexScene(int tid, bool HasPlayer)
  Main.Debug("[PostSexScene] tid = " + tid)

  sslThreadController controller = slf.GetController(tid)
  Actor[] actorList = slf.HookActors(tid)
  if (actorList.length < 1)
    return
  EndIf
 
  Actor[] sortedActorList = slf.SortActors(actorList,true)
  ; Select an actor that's not the player, and have them talk.
  actor actorWithBelt = none

  int i = 0
  while(i < sortedActorList.length)
    actor currentActor = sortedActorList[i]
    if(actorWithBelt == none && currentActor.WornHasKeyword(devious.libs.zad_DeviousBelt))
      actorWithBelt = currentActor
    endif

    i += 1
  endwhile
  
  if actorWithBelt
    sexTalkClimax(actorWithBelt, true)
  else
    sexTalkClimax(GetWeightedRandomActorToSpeak(sortedActorList))
  EndIf
  lastTag = ""
EndEvent

Event EndSexScene(int tid, bool HasPlayer)
    JValue.release(descriptionsMap)
    Main.Info("Ended Sex scene")
    sslThreadController controller = slf.GetController(tid)

    Actor[] actorList = slf.HookActors(tid)
    
    ; Send event, AI can be aware SEX is happening here
    Actor[] sortedActorList = slf.SortActors(actorList,true)
    
    if bHasAIFF
      ; TODO make weighted random selection of actor to speak
      actor speaker = GetWeightedRandomActorToSpeak(sortedActorList)
      sexTalkOnEnd(speaker)
      AIFF.SetAnimationBusy(0, Main.GetActorName(speaker))
    EndIf
    UpdateThreadTable("end", "sexlab", tid)
EndEvent

string Function GetSexStageDescription(string animationStageName) 
  Main.Info("Obtaining description for: <"+animationStageName+"> using map: "+descriptionsMap)
  return JMap.getStr(descriptionsMap,animationStageName)
EndFunction

Function InitializeSexDescriptions()
  if (JMap.count(descriptionsMap) != 0 || descriptionsMap != 0)
    Main.Info("Not reinitializing sexlab descriptions - data already exists.")
    return
  EndIf
  descriptionsMap = JMap.object()
  LoadSexlabDescriptions()

  if (JMap.count(descriptionsMap) != 0 || descriptionsMap != 0)
    Main.Info("Not reinitializing sexlab descriptions - data already exists.")
    return
  EndIf
  
  Main.Info("Initializing sex descriptions");
  JMap.clear(descriptionsMap)
  JMap.setStr(descriptionsMap,"Mitos_Laplove_A1_S1","Stands over partner.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A1_S1","staying atop partner with gentle movements.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A2_S1","lying down in a passive stance.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A1_S2","still staying atop partner, sitting on, moving with gentle movements.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A2_S2","lying down in a passive stance.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A1_S3","still staying atop partner, sitting over, moving now with stronger movements.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A2_S3","lying down in a passive stance.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A1_S4","still staying atop partner, almost hugging, moving now with quick movements.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A2_S4","lying down in a passive stance.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A1_S5","still staying atop partner, almost hugging, moving now slowly. Trembling with Pleasure.")
  JMap.setStr(descriptionsMap,"Leito_Cowgirl_A2_S5","lying down in a passive stance. Trembling with Pleasure.")
  Main.Info("Descriptions set: "+JMap.count(descriptionsMap)+" using map: "+descriptionsMap)
  JValue.writeToFile(descriptionsMap, "Data/Data/minai/sexlab_descriptions.json")
EndFunction



string Function GetKeywordsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction



string Function GetFactionsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction

String Function JoinActorArray(Actor[] actorArray, string separator = ",")
  String result = ""
  int index = 0
  int size = actorArray.Length

  ; Loop through the array using a while loop
  while (index < size)
      Actor currentActor = actorArray[index]
      if (currentActor)
          ; Append the actor's name to the result string
          result += Main.GetActorName(currentActor)

          ; If it's not the last actor, append a comma
          if (index < size - 1)
              result += separator
          endif
      endif
      
      index += 1
  endwhile
  
  return result
EndFunction

String Function JoinStringArray(string[] strArr, string separator = ",")
  String result = ""
  int index = 0
  int size = strArr.Length

  ; Loop through the array using a while loop
  while (index < size)
      string str = strArr[index]
      if (str)
          ; Append the actor's name to the result string
          result += str

          ; If it's not the last actor, append a comma
          if (index < size - 1)
              result += separator
          endif
      endif
      
      index += 1
  endwhile
  
  return result
EndFunction

; type - is db operation: startthread | scenechange | speedchange | end | clean
;  startthread - when new thread started
;  scenechange - when scene changed
;  speedchange - when speed changed
;  end - on thread end
;  clean - clean db of all threads recorded in table
; framework - ostim or sexlab
function UpdateThreadTable(string type, string framework = "ostim", int ThreadID = -1)
  actor[] actors
  string sceneId
  
  ; !!!Min to review sexlab implementation
  if(framework == "sexlab")
    sslThreadController controller = slf.GetController(ThreadID)
  
    if (controller.Stage==1) 
      LoadSexlabDescriptions()
    EndIf
    
    actors = slf.GetController(ThreadID).Positions
    sceneId = controller.Animation.FetchStage(controller.Stage)[0]
  else
    actors = OThread.GetActors(ThreadID)
    sceneId = OThread.GetScene(ThreadID)
  endif

  actor[] maleActors = PapyrusUtil.ActorArray(0)
  actor[] femaleActors = PapyrusUtil.ActorArray(0)
  int i = 0
  int count = actors.Length
  while i < count
    actor currActor = actors[i]
    int currSex = currActor.GetActorBase().GetSex()
    
    if(currSex == 0)
      maleActors = PapyrusUtil.PushActor(maleActors, currActor)
    elseif(currSex == 1)
      femaleActors = PapyrusUtil.PushActor(femaleActors, currActor)
    endif

    i += 1
  endwhile

  string maleActorsString = JoinActorArray(maleActors)
  string femaleActorsString = JoinActorArray(femaleActors)

  string fallback = buildSceneFallbackDescription(ThreadID, framework, type)

  string jsonToSend = "{ \"type\": \""+type+"\", \"framework\": \""+framework+"\", \"threadId\": "+ThreadID+", \"maleActors\": \""+maleActorsString+"\", \"femaleActors\": \""+femaleActorsString+"\", \"scene\": \""+sceneId+"\""

  if(fallback != "")
    jsonToSend += ", \"fallback\": \""+fallback+"\""
  endif

  jsonToSend += "}"

  AIAgentFunctions.logMessage("command@ExtCmdUpdateThreadsTable@"+ jsonToSend +"@", "updateThreadsDB")
endfunction

function sexTalkClimax(actor speaker, bool chastity = false)
  if(chastity)
    SexTalk(speaker, "sextalk_climaxchastity")
  else
    SexTalk(speaker, "sextalk_climax")
  endif
endfunction

function sexTalkSceneChage(actor speaker)
  SexTalk(speaker, "sextalk_scenechange")
endfunction

function sexTalkSpeedChange(actor speaker, bool increase)
  if(increase)
    SexTalk(speaker, "sextalk_speedincrease")
  else
    SexTalk(speaker, "sextalk_speeddecrease")
  endif
  
endfunction

function sexTalkOnEnd(actor speaker)
  SexTalk(speaker, "sextalk_end")
endfunction

function sexTalkCollision(actor speaker, string promptToSay)
  SexTalk(speaker, "chatnf_vr_1")
endfunction

; speaker is who actually will say llm lines, can be none if scene is player only
; chatType different AIFF custom chat topics see php files
Function SexTalk(actor speaker, string chatType)
  if !bHasAIFF || !speaker
    return
  EndIf

  ; Throttle on how often we should dirty talk incase people are switching animations
  float currentTime = Utility.GetCurrentRealTime()
  if currentTime - lastDirtyTalk > 5
    lastDirtyTalk = currentTime
    string speakerName = Main.GetActorName(speaker)
    Main.Debug("SexTalk() => " + speakerName + ": " + chatType)
    Main.RequestLLMResponseNPC("", "", speakerName, chatType)
  else
    Main.Debug("SexTalk - THROTTLED")
  EndIf
EndFunction

actor function getRandomActor(actor[] actors)
  int index = PO3_SKSEFunctions.GenerateRandomInt(0, actors.length - 1)

  return actors[index]
endfunction

; if it's player only scene it will return none
; assume that actors are only actors from scene
actor Function GetWeightedRandomActorToSpeak(actor[] actors)
  actor[] maleActors = PapyrusUtil.ActorArray(0)
  actor[] femaleActors = PapyrusUtil.ActorArray(0)
  int count = actors.Length
  while count > 0
    count -= 1
    actor currActor = actors[count]
    int currSex = currActor.GetActorBase().GetSex()
    bool isMuted = false

    ; if ostim actor is muted
    if(bHasOstim)
      if(OActor.IsMuted(currActor))
        isMuted = true
      endif
    endif

    if(currActor == PlayerRef || isMuted)
      ; player doesn't participate in llm talking :)
      ; some actions in ostim prevents actors from talking, which makes sense
    elseif(currSex == 0)
      maleActors = PapyrusUtil.PushActor(maleActors, currActor)
    elseif(currSex == 1)
      femaleActors = PapyrusUtil.PushActor(femaleActors, currActor)
    endif
  endwhile

  ; if no female npc, just pick random male. Can be none though if it's player only scene
  if(femaleActors.length == 0)
    return getRandomActor(maleActors)
  endif

  ; if no male npc, just pick random female. Can be none though if it's player only scene
  if(maleActors.length == 0)
    return getRandomActor(femaleActors)
  endif

  ; pick weighted type of npc who will talk
  bool isFemale = Utility.RandomInt(1, 100) <= commentFemaleWeight

  if(isFemale)
    return getRandomActor(femaleActors)
  else
    return getRandomActor(maleActors)
  endif
EndFunction

function setPrevSpeed(int ThreadID, int prevSpeed)
  JMap.setInt(threadsPrevSpeedsMap, ThreadID, prevSpeed)
endfunction

function resetPrevSpeed(int ThreadID)
  JMap.setInt(threadsPrevSpeedsMap, ThreadID, -1)
endfunction

int function getPrevSpeed(int ThreadID)
  return JMap.getInt(threadsPrevSpeedsMap, ThreadID)
endfunction

function removePrevSpeed(int ThreadID)
  JMap.removeKey(threadsPrevSpeedsMap, ThreadID)
endfunction

string function buildSceneString(string sceneId, string sceneTagsString, string actionString)
  string result = sceneId+"."
  if(sceneTagsString != "")
    result += " Scene can be described with this tags: " + sceneTagsString + "."
  endif
  if(actionString != "")
    result += " These actions happen in a scene: " + actionString
  endif
  return result
endfunction

string function buildSceneFallbackDescription(int ThreadID, string framework, string eventType)
  string sceneId = ""
  string actorString = ""
  string actionString = ""
  string sceneTagsString = ""
  if(framework == "ostim")
    sceneId = OThread.GetScene(ThreadID)
    string[] actionTypes = OMetadata.GetActionTypes(sceneId)
    string[] sceneTags = OMetadata.GetSceneTags(sceneId)
    Actor[] actors = OThread.GetActors(ThreadID)
    actorString = JoinActorArray(actors, ", ")
    actionString = JoinStringArray(actionTypes, ", ")
    sceneTagsString = JoinStringArray(sceneTags, ", ")
  else
    sslThreadController controller = slf.GetController(ThreadID)
    string[] sceneTags= controller.Animation.GetRawTags()
    sceneId = controller.Animation.Name
    Actor[] actors = slf.GetController(ThreadID).Positions
    actorString = JoinActorArray(actors, ", ")
    sceneTagsString = JoinStringArray(sceneTags, ", ")
    int count = actors.length
    while(count > 0)
      count -= 1
      string stageDescription = GetSexStageDescription(controller.Animation.FetchStage(controller.Stage)[0])

      if(stageDescription != "")
        actionString += GetActorNameForSex(actors[count])+" is "+ stageDescription
      endif
    endwhile
  endif

  if(eventType == "startthread")
    return actorString + " begin sex scene: " + buildSceneString(sceneId, sceneTagsString, actionString)
  elseif(eventType == "scenechange")
    return actorString + " changed the scene to " + buildSceneString(sceneId, sceneTagsString, actionString)
  endif
endfunction
