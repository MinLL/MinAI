scriptname minai_Sex extends Quest

SexLabFramework slf


bool bHasOstim = False
GlobalVariable minai_UseOstim
int clothingMap = 0
int descriptionsMap
bool bHasAIFF

minai_AIFF aiff
minai_MainQuestController main
minai_DeviousStuff devious
Actor PlayerRef

float lastDirtyTalk



Function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  devious = (Self as Quest) as minai_DeviousStuff
  Main.Info("Initializing Sex Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  
  RegisterForModEvent("HookStageStart", "OnStageStart")
  RegisterForModEvent("HookOrgasmStart", "PostSexScene")
  RegisterForModEvent("HookAnimationEnd", "EndSexScene")
  RegisterForModEvent("HookAnimationStart", "OnAnimationStart")
  
  ; RegisterForModEvent("ostim_event", "OnOstimEvent")
  RegisterForModEvent("ostim_thread_start", "OStimManager")
  RegisterForModEvent("ostim_thread_scenechanged", "OStimManager")
  RegisterForModEvent("ostim_thread_speedchanged", "OStimManager")
  RegisterForModEvent("ostim_actor_orgasm", "OStimManager")
  RegisterForModEvent("ostim_thread_end", "OStimManager")
    
  slf = Game.GetFormFromFile(0xD62, "SexLab.esm") as SexLabFramework
  if Game.GetModByName("OStim.esp") != 255
    Main.Info("Found OStim")
    bHasOstim = True
  EndIf

  minai_UseOStim = Game.GetFormFromFile(0x0906, "MinAI.esp") as GlobalVariable
  if !minai_UseOStim
    Main.Error("Could not find ostim toggle")
  EndIf
  
  ; Reset incase the player quit during a sex scene or this got stuck
  SetSexSceneState("off")
  InitializeSexDescriptions()
  lastDirtyTalk = 0.0
  if (clothingMap == 0)
    Main.Debug("Initializing clothing map")
    clothingMap = JMap.object()
    JValue.retain(clothingMap)
  else
    Main.Debug("Clothing map already initialized, id=" + clothingMap)
  EndIf
  aiff.SetModAvailable("Ostim", bHasOstim)
  aiff.SetModAvailable("Sexlab", slf != None)
EndFunction



bool Function CanAnimate(actor akTarget, actor akSpeaker)
  if (akTarget.IsOnMount() || akSpeaker.IsOnMount())
    return False
  EndIf
  if bHasOstim && (minai_UseOStim.GetValue() == 1.0 && !OActor.IsInOStim(akTarget) && !OActor.IsInOStim(akSpeaker))
    return True
  EndIf
  if slf && (!slf.IsActorActive(akTarget) && !slf.IsActorActive(akSpeaker))
    return True
  EndIf
  return False
EndFunction



Function Start1pSex(actor akSpeaker)
  if CanAnimate(akSpeaker, akSpeaker)
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



Function Start2pSex(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene, string tags="")
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    tags = ConvertTagsOstim(tags)
    Actor[] ostimActors = new Actor[2]
    if bPlayerInScene
      ostimActors = OActorUtil.ToArray(Player, akSpeaker) as Actor[]
    else
      ostimActors = OActorUtil.ToArray(akTarget, akSpeaker) as Actor[]
    EndIf
    ostimActors = OActorUtil.Sort(ostimActors, OActorUtil.EmptyArray())
    string newScene = OLibrary.GetRandomSceneWithAnyActionCSV(ostimActors, tags)
    Utility.Wait(0.5)
    if newScene == ""
      newScene = OLibrary.GetRandomSceneWithAnySceneTagCSV(ostimActors, tags)
      Utility.Wait(1)
      if newScene == ""
        Main.Debug("No OStim scene found for: " + tags)
      EndIf
    EndIf
    int ActiveOstimThreadID = OThread.Quickstart(ostimActors, newScene)
    Main.Debug("Found " + tags + " scene: " + newScene + " for OStim Thread [" + ActiveOstimThreadID + "].")
  else
    actor[] actors = new actor[2]
    actors[0] = akTarget
    actors[1] = akSpeaker
    StartSexSmart(bPlayerInScene, actors, tags)
  EndIf
  main.RegisterEvent(akSpeaker.GetActorBase().GetName() + " and " + akTarget.GetActorBase().GetName() + " started having sex. (" + tags +") ", "info_sexscene")
EndFunction



Function StartSexOrSwitchTo(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene, string tags)
  AIFF.ChillOut()
  if CanAnimate(akTarget, akSpeaker)
    Start2pSex(akSpeaker, akTarget, PlayerRef, bPlayerInScene, tags)
  elseif bHasOstim && minai_UseOStim.GetValue() == 1.0 && OActor.IsInOStim(akSpeaker)
    Main.Debug(akSpeaker.GetActorBase().GetName() + " is switching Ostim scene.")
    tags = ConvertTagsOstim(tags)
    Main.Debug("Searching for random " + tags + " scene.")
    int ActiveOstimThreadID = OActor.GetSceneID(akSpeaker)
    Actor[] ostimActors = new Actor[5]
    ostimActors = OThread.GetActors(ActiveOstimThreadID)
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
    main.RegisterEvent(akSpeaker.GetActorBase().GetName() + " attempted to change the scene to " + tags + " instead: " + newScene, "info_sexscene")
    Return
  elseif akSpeaker != playerRef && akTarget != playerRef
    Return
  else
    int threadID = slf.FindPlayerController()
    sslThreadController Controller = slf.ThreadSlots.GetController(threadID)
    sslBaseAnimation[] animations = slf.GetAnimationsByTags(2, tags)
    Controller.SetForcedAnimations(animations)
    Controller.SetAnimation()
    main.RegisterEvent(akSpeaker.GetActorBase().GetName() + " and " + akTarget.GetActorBase().GetName() + " changed up the sex to " + tags + " instead.", "info_sexscene")
  EndIf
EndFunction



Function StartGroupSex(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene, Actor[] actorsFromFormList, string tags="")
  if CanAnimate(akTarget, akSpeaker)
    if bHasOstim && minai_UseOStim.GetValue() == 1.0
      Main.Debug("OStim Orgy: Processing for akSpeaker: " + akSpeaker + "at akTarget: " + akTarget)
      Main.Debug("OStim Orgy: actorsFromFormList: " + actorsFromFormList)
      Actor[] ostimActors = new Actor[10]
      ostimActors = OActorUtil.ToArray(actorsFromFormList[0],actorsFromFormList[1],actorsFromFormList[2],actorsFromFormList[3],actorsFromFormList[4],actorsFromFormList[5],actorsFromFormList[6],actorsFromFormList[7],actorsFromFormList[8],actorsFromFormList[9])
      Main.Debug("OStim Orgy: ostimActors pre-sort: " + ostimActors)
      ostimActors = OActorUtil.Sort(ostimActors, OActorUtil.EmptyArray()) ; 2nd param is list of Dominant Actors
      Main.Debug("OStim Orgy: ostimActors sorted: " + ostimActors)
      int ActiveOstimThreadID
      ActiveOstimThreadID = OThread.QuickStart(ostimActors, tags)
    else
      StartSexSmart(bPlayerInScene, actorsFromFormList, tags)
    EndIf
  EndIf
EndFunction



Function StopSex(actor akSpeaker)
  if OActor.IsInOStim(akSpeaker)
    int ActiveOstimThreadID = OActor.GetSceneID(akSpeaker)
    if OThread.IsRunning(ActiveOstimThreadID)
      OThread.Stop(ActiveOstimThreadID)
    EndIf
  else
    ; add SL Stop
  EndIf
EndFunction



bool Function CompareActorSex(actor actor1, actor actor2)
  ; We want to sort males to the end, and females to the front.
  ; 0 = male
  ; 1 = female
  ; 2 = other
  return actor1.GetActorBase().GetSex() < actor2.GetActorBase().GetSex()
EndFunction



Function StartSexSmart(bool bPlayerInScene, actor[] actorsToSort, string tags)
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
    if actorsToSort[i].GetActorBase().GetSex() == 0
      numMales += 1
    else
      numFemales += 1
    EndIf
    i += 1
  Endwhile
  
  Main.Debug("Done Sorting actors (" + numMales + " males, " + numFemales + " females): " + actorsToSort)
  slf.StartSex(actorsToSort, FindSexlabAnimations(actorsToSort, numMales, numFemales, tags))
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
    Start2pSex(akSpeaker, akTarget, Player, bPlayerInScene)
    
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
  ; Kissing/Cuddling experimental alternates - leads to sex
  elseif stringutil.Find(sayLine, "-cuddling-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "hugging")
  elseif stringutil.Find(sayLine, "-frenchkissing-") != -1 || stringutil.Find(sayLine, "-frenchkiss-") != -1 || stringutil.Find(sayLine, "-french kissing-") != -1 || stringutil.Find(sayLine, "-french kiss-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, Player, bPlayerInScene, "kissing")
    
  elseIf stringutil.Find(sayLine, "-groupsex-") != -1 || stringUtil.Find(sayLine, "-orgy-") != -1 || stringUtil.Find(sayLine, "-threesome-") != -1
    StartGroupSex(akSpeaker, akTarget, Player, bPlayerInScene, actorsFromFormList)
  elseif stringutil.Find(sayLine, "-endsex-") != -1 || stringutil.Find(sayLine, "-end sex-") != -1 || stringutil.Find(sayLine, "-stopsex-") != -1 || stringutil.Find(sayLine, "-stop sex-") != -1 || stringutil.Find(sayLine, "-red-") != -1
    StopSex(akSpeaker)
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

  bool bPlayerInScene = (akTarget == PlayerRef || akSpeaker == PlayerRef)

  string targetName = main.GetActorName(akTarget)
  If command == "ExtCmdMasturbate"
    Start1pSex(akSpeaker)
  elseif command == "ExtCmdStartSexScene"
    Main.RegisterEvent(akSpeaker.GetActorBase().GetName() + " and " + akTarget.GetActorBase().GetName() + " started having an intimate encounter together.")
    SetSexSceneState("on")
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
  elseIf command == "ExtCmdOrgy"
    StartGroupSex(akSpeaker, akTarget, PlayerRef, bPlayerInScene, actorsFromFormList)
  elseif command == "ExtCmdStopSex"
    StopSex(akSpeaker)
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
  EndIf
EndEvent



Function SetContext(actor akTarget)
  if !aiff
    return
  elseif OActor.IsInOStim(akTarget)
    String actorName = main.GetActorName(akTarget)
    aiff.SetActorVariable(akTarget, "excitement", OActor.GetExcitement(akTarget))
    aiff.SetActorVariable(akTarget, "orgasmcount", OActor.GetTimesClimaxed(akTarget))
  EndIf
EndFunction



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



Event OnOstimOrgasm(string eventName, string strArg, float numArg, Form sender)
  actor akActor = sender as actor
EndEvent



Event OStimManager(string eventName, string strArg, float numArg, Form sender)
  int ostimTid = numArg as int
  Main.Info("oStim eventName: "+eventName+", strArg: "+strArg);
  if (eventName=="ostim_thread_start")
    string sceneName=OThread.GetScene(ostimTid);
    bool isRunning=OThread.IsRunning(ostimTid);
    Actor[] actors =  OThread.GetActors(ostimTid);
    string actorString;
    int i = actors.Length
    bool playerInvolved=false
    while(i > 0)
        i -= 1
        actorString=actorString+actors[i].GetActorBase().GetName()+",";
        if (actors[i] == playerRef) 
          playerInvolved=true;
        EndIf
    Endwhile
    
    if (playerInvolved)
      AIFF.ChillOut()
    EndIf
    SetSexSceneState("on")
    if bHasAIFF
      AIAgentFunctions.logMessage("ostim@"+sceneName+" "+isRunning+" "+actorString,"setconf")
    EndIf
    Main.Info("Started intimate scene")
  
  elseif (eventName=="ostim_thread_scenechanged")
    string sceneId = strArg 
    string sceneName=OThread.GetScene(ostimTid);
    bool isRunning=OThread.IsRunning(ostimTid);
    Actor[] actors =  OThread.GetActors(ostimTid);
    string actorString;
    int i = actors.Length
    bool playerInvolved=false
    while(i > 0)
        i -= 1
        actorString=actorString+actors[i].GetActorBase().GetName()+",";
        if (actors[i] == playerRef)
          playerInvolved=true;
        EndIf
    Endwhile
    
    if (playerInvolved)
      AIFF.ChillOut()
    EndIf
    main.RegisterEvent(""+sceneName+" id:"+sceneId+" isRunning:"+isRunning+" Actors:"+actorString,"info_sexscene")
    Main.Info("Ostim Scene changed")

  elseif (eventName=="ostim_actor_orgasm")    
    Actor OrgasmedActor = Sender as Actor
    main.RegisterEvent(OrgasmedActor.GetActorBase().GetName() + " had an Orgasm")
    DirtyTalk("ohh... yes.","chatnf_sl_2",OrgasmedActor.GetActorBase().GetName())
    Main.Info("Ostim Actor orgasm")

  elseif (eventName=="ostim_thread_end")    
    string sceneName=OThread.GetScene(ostimTid);
    bool isRunning=OThread.IsRunning(ostimTid);
    Actor[] actors =  OThread.GetActors(ostimTid);
    string actorString;
    int i = actors.Length
    bool playerInvolved=false
    while(i > 0)
      i -= 1
      actorString=actorString+actors[i].GetActorBase().GetName()+",";
      if (actors[i] == playerRef) 
        playerInvolved=true;
      EndIf
    endwhile
    
    if (playerInvolved)
      AIFF.ChillOut()
    EndIf
    SetSexSceneState("off")
    Main.Info("Ended intimate scene")
  EndIf
EndEvent



Function LoadSexlabDescriptions()
  if (descriptionsMap==0)
    Main.Info("Loading Sexlab Descriptions")
    descriptionsMap=JValue.readFromFile( "Data/Data/minai/sexlab_descriptions.json");
    JValue.retain(descriptionsMap)
    Main.Info("Descriptions set: "+JMap.count(descriptionsMap)+" using map: "+descriptionsMap+ " Data/Data/minai/sexlab_descriptions.json")
  EndIf
EndFunction



Function SetSexSceneState(string sexState)
  if bHasAIFF
    AIAgentFunctions.logMessage("sexscene@" + sexState,"setconf")
  EndIf
EndFunction



Event OnAnimationStart(int tid, bool HasPlayer)
  LoadSexlabDescriptions()
  Actor[] actorList = slf.GetController(tid).Positions
  Actor[] sortedActorList = slf.SortActors(actorList,true)
  int i = sortedActorList.Length
  bool bPlayerInScene=false
  while(i > 0)
    i -= 1
    if (sortedActorList[i]==playerRef) 
      bPlayerInScene=true;
    EndIf
  Endwhile
  if (bPlayerInScene)
    AIFF.ChillOut()
  EndIf
  SetSexSceneState("on")
  Main.Info("Started Sex Scene")
EndEvent



String Function GetActorNameForSex(actor akActor)
  ; Prefer base name, fall back to display name, and then other.
  string ret = akActor.GetActorBase().GetName()
  if ret
    return ret
  EndIf
  ret = akActor.GetDisplayName()
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
  
  Actor[] actorList = slf.GetController(tid).Positions
  Actor[] targetactorList = actorList

  if (actorList.length < 1)
    return
  EndIf
  
  string pleasure=""
  Actor[] sortedActorList = slf.SortActors(actorList,true)
  
  int i = sortedActorList.Length
  while(i > 0)
    i -= 1
    pleasure=pleasure+GetActorNameForSex(sortedActorList[i])+" pleasure score "+slf.GetEnjoyment(tid,sortedActorList[i])+","
  endwhile
  string sceneTags="'. Scene tags: "+controller.Animation.GetRawTags()+"."
  if (controller.Animation.GetRawTags()=="")
    sceneTags="";
  EndIf
  
  ;Animations[StageIndex(Position, Stage)]
  string sexPos="#SEX_SCENARIO: Position '" +controller.Animation.Name+"'. ";
  string pleasureFull=pleasure

  string stageDesc1 = GetSexStageDescription(controller.Animation.FetchStage(controller.Stage)[0])
  string stageDesc2 = GetSexStageDescription(controller.Animation.FetchStage(controller.Stage)[1])
  
  string description=GetActorNameForSex(sortedActorList[0])+" is "+ stageDesc1
  string description2=GetActorNameForSex(sortedActorList[1])+" is "+ stageDesc2

  if (stageDesc1 == "")
    description="";
  EndIf
  
  if (stageDesc2 == "")
    description2="";
  EndIf

  ; Select an actor that's not the player, and have them talk.
  actor otherActor = sortedActorList[0]
  if otherActor == playerRef && sortedActorList.Length > 1
    otherActor = sortedActorList[1]
  EndIf

  string[] Tags = controller.Animation.GetRawTags()
  ; Send event, AI can be aware SEX is happening here
  if (Tags.Find("forced")!= -1)
    main.RegisterEvent(sexPos+sceneTags+GetActorNameForSex(sortedActorList[0])+ " is being raped by  "+GetActorNameForSex(sortedActorList[1])+ ", ("+GetActorNameForSex(sortedActorList[0])+" feels a mix of pain and pleasure) ."+description+description2+"("+pleasureFull+")","info_sexscene")
  else
    main.RegisterEvent(sexPos+sceneTags+GetActorNameForSex(sortedActorList[0])+ " and "+GetActorNameForSex(sortedActorList[1])+ " are having sex. "+description+description2+"("+pleasureFull+")","info_sexscene")
  EndIf

  ; main.RegisterEvent(controller.Animation.FetchStage(controller.Stage)[0]+"@"+sceneTags,"info_sexscenelog")

  aiff.setAnimationBusy(1, otherActor.GetActorBase().GetName())
  if (!slf.isMouthOpen(otherActor) && otherActor != playerRef)
    if (controller.Stage < (controller.Animation.StageCount()))
      if bHasAIFF && AiAgentFunctions.isGameVR() 
       ; VR users will have dirty talk through physics integration instead
       ; Reenabled this temporarily while figuring out female player character collisions during sex.
       ; Works much better for male atm, need to add different colliders
        DirtyTalk("ohh... yes.","chatnf_sl",GetActorNameForSex(otherActor))
      else
        DirtyTalk("ohh... yes.","chatnf_sl",GetActorNameForSex(otherActor))
      EndIf
    EndIf
  else
    main.RegisterEvent(GetActorNameForSex(otherActor)+ " is now using mouth with "+sortedActorList[1].GetActorBase().GetName(),"info_sexscene")
  EndIf
EndEvent



Function DirtyTalk(string lineToSay, string lineType, string name)
  if !bHasAIFF
    return
  EndIf
  if name != PlayerRef.GetActorBase().GetName() && !AIAgentFunctions.getAgentByName(name)
    Main.Info("Not sending dirty talk: Actor is not ai controlled and is not the player")
    return
  EndIf
  ; Throttle on how often we should dirty talk incase people are switching animations
  float currentTime = Utility.GetCurrentRealTime()
  if currentTime - lastDirtyTalk > 5
    lastDirtyTalk = currentTime
    Main.Debug("DirtyTalk(" + lineToSay + ", " + lineType +", " + name +")")
    Main.RequestLLMResponse(lineToSay, lineType, name)
  else
    Main.Debug("DirtyTalk - THROTTLED")
  EndIf
EndFunction



Event PostSexScene(int tid, bool HasPlayer)
  sslThreadController controller = slf.GetController(tid)
  Actor[] actorList = slf.HookActors(tid)
  Actor[] targetactorList = actorList
  if (actorList.length < 1)
    return
  EndIf
  
  string pleasure=""
    int i = actorList.Length
  while(i > 0)
    i -= 1
    pleasure=pleasure+actorList[i].GetActorBase().GetName()+" is reaching orgasm,"
  Endwhile
  string pleasureFull="Pleasure:"+pleasure
  ; Send event, AI can be aware SEX is happening here
  main.RegisterEvent(pleasureFull,"info_sexscene")
  
  Actor[] sortedActorList = slf.SortActors(actorList,true)
  ; Select an actor that's not the player, and have them talk.
  actor otherActor = sortedActorList[0]
  if otherActor == playerRef && sortedActorList.Length > 1
    otherActor = sortedActorList[1]
  EndIf
  
  main.RegisterEvent(otherActor.GetActorBase().GetName()+ ": Oh yeah! I'm having an orgasm!.","chat")
  if (!slf.isMouthOpen(otherActor))
    DirtyTalk("I'm cumming!","chatnf_sl_2",GetActorNameForSex(otherActor))
  EndIf
EndEvent



Event EndSexScene(int tid, bool HasPlayer)
    JValue.release(descriptionsMap)
    Main.Info("Ended Sex scene")
    sslThreadController controller = slf.GetController(tid)

    Actor[] actorList = slf.HookActors(tid)
    Actor[] targetactorList = actorList

    ; Send event, AI can be aware SEX is happening here
    Actor[] sortedActorList = slf.SortActors(actorList,true)
    
    ; Select an actor that's not the player, and have them talk.
    actor otherActor = sortedActorList[0]
    if otherActor == playerRef && sortedActorList.Length > 1
      otherActor = sortedActorList[1]
    EndIf

    main.RegisterEvent(sortedActorList[0].GetActorBase().GetName()+ " and "+sortedActorList[1].GetActorBase().GetName()+ " ended the intimate moment","info_sexscene")
    if bHasAIFF
      DirtyTalk("That was awesome, what do you think?","inputtext",GetActorNameForSex(otherActor))
      AIFF.SetAnimationBusy(0, otherActor.GetActorBase().GetName())
    EndIf
    SetSexSceneState("off")
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