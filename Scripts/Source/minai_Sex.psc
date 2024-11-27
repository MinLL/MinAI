scriptname minai_Sex extends Quest

SexLabFramework slf

bool bHasOstim = False
bool bHasSexlab = False
bool bHasSexlabPPlus = False
GlobalVariable minai_UseOstim
int clothingMap = 0
bool bHasAIFF

minai_AIFF aiff
minai_MainQuestController main
minai_DeviousStuff devious
Actor PlayerRef
minai_Config config
minai_AmbientSexTalk ambientSexTalk
minai_SexOstim ostim
minai_SexSexlab sexlab
minai_SexUtil sexUtil
minai_Util MinaiUtil

float lastSexTalk

Message minai_ConfirmSexMsg
string lastTag

; {threadId: Actor} to track actors who will say something on after ostim scene. Need this because there is no guarantee the on ostim end event there will be running thread with actors
int actorToSayOnEndMap

string property ostimType = "ostim" auto
string property sexlabType = "sexlab" auto

Function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  devious = (Self as Quest) as minai_DeviousStuff
  ostim = (Self as Quest) as minai_SexOstim
  sexlab = (Self as Quest) as minai_SexSexlab
  sexUtil = (Self as Quest) as minai_SexUtil
  MinaiUtil = (Self as Quest) as minai_Util
  ambientSexTalk = Game.GetFormFromFile(0x0E88, "MinAI.esp") as minai_AmbientSexTalk
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

  actorToSayOnEndMap = JValue.releaseAndRetain(actorToSayOnEndMap, JMap.object())
    
  slf = Game.GetFormFromFile(0xD62, "SexLab.esm") as SexLabFramework

  ; Only SexLab P+ includes matchmaker, so check for that
  bHasSexlabPPlus = Game.GetFormFromFile(0xB3302, "SexLab.esm")

  ambientSexTalk.Maintenance(self, slf)
  ostim.Maintenance()
  sexlab.Maintenance(slf, devious)
  sexUtil.Maintenance(config, slf)
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
  
  ; Reset incase the player quit during a sex scene or this got stuck
  SetSexSceneState("off")
  ; clean any threads from table
  UpdateThreadTable("clean")
  sexlab.InitializeSexDescriptions()
  lastSexTalk = 0.0
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
  aiff.RegisterAction("ExtCmdRemoveClothes", "RemoveClothes", "Take off all clothing", "Sex", 1, 5, 2, 5, 300, True)
  aiff.RegisterAction("ExtCmdPutOnClothes", "PutOnClothes", "Put all clothing back on", "Sex", 1, 5, 2, 5, 300, True)
  aiff.RegisterAction("ExtCmdMasturbate", "Masturbate", "Begin Masturbating", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartThreesome", "Threesome", "Start threesome sex with target", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartOrgy", "Orgy", "Start sex with all nearby AI Actors", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdEndSex", "EndSex", "Finish the Current Sex Scene", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  ; aiff.RegisterAction("ExtCmdStartSexScene", "StartSexScene", "ExtCmdStartSexScene", "Sex", 1, 5, 2, 5, 300)
  aiff.RegisterAction("ExtCmdStartBlowjob", "StartBlowjob", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartAnal", "StartAnal", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartVaginal", "StartVaginal", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartHandjob", "StartHandjob", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFootjob", "StartFootjob", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartBoobjob", "StartBoobjob", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCunnilingus", "StartCunnilingus", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFacial", "StartFacial", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCumonchest", "StartCumonchest", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartRubbingclitoris", "StartRubbingclitoris", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartDeepthroat", "StartDeepthroat", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartRimjob", "StartRimjob", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFingering", "StartFingering", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartMissionarySex", "StartMissionarySex", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCowgirlSex", "StartCowgirlSex", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartReverseCowgirl", "StartReverseCowgirl", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartDoggystyle", "StartDoggystyle", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartFacesitting", "StartFacesitting", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStart69Sex", "Start69Sex", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartGrindingSex", "StartGrindingSex", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartThighjob", "StartThighjob", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartCuddleSex", "StartCuddleSex", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  aiff.RegisterAction("ExtCmdStartKissingSex", "StartKissingSex", "Sex Position", "Sex", 1, 5, 2, 5, 300, (bHasSexlab || bHasOstim))
  
  aiff.RegisterAction("ExtCmdSpeedUpSex", "SpeedUpSex", "Sex Intensity", "Sex", 1, 3, 1, 1, 300, (bHasOstim))
  aiff.RegisterAction("ExtCmdSlowDownSex", "SlowDownSex", "Sex Intensity", "Sex", 1, 3, 1, 1, 300, (bHasOstim))

  aiff.RegisterAction("ExtCmdFollow", "FollowTarget", "Start Following Player", "General", 1, 0, 2, 5, 300, true)
  aiff.RegisterAction("ExtCmdStopFollowing", "StopFollowing", "Stop Following Player", "General", 1, 0, 2, 5, 300, true)
EndFunction

; Event onUpdate()
; EndEvent

bool Function CanAnimate(actor akTarget)
  return sexUtil.CanAnimate(akTarget, useOstim() && !OActor.IsInOStim(akTarget))
EndFunction



Function Start1pSex(actor akSpeaker)
  if CanAnimate(akSpeaker)
    if useOstim()
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
  if useOstim()
    ostim.StartOstim(actors, tags)
  else
    sexlab.StartSexlabScene(bPlayerInScene, actors, tags)
  EndIf
  
  if actors.Length == 1
    if devious.HasDD() && actors[0].WornHasKeyword(devious.libs.zad_DeviousBelt)
      main.RegisterEvent(MinaiUtil.GetActorName(actors[0]) + " started unsuccessfully trying to masturbate through the chastity belt. (" + tags +") ", "info_sexscene")
    Else
      main.RegisterEvent(MinaiUtil.GetActorName(actors[0]) + " started masturbating. (" + tags +") ", "info_sexscene")
    EndIf
    return
  endif

  main.RegisterEvent(MinaiUtil.JoinActorArray(actors, ", ") + " started having sex. (" + tags +") ", "info_sexscene")
EndFunction

; process actors into smaller groups to start multiple threads
; it separates males/females into separate arrays
; if there are males and females try to get one of each gender for each group and then randomly pick 3rd, 4th, 5th actors.
; check if user has animation to support selected actors if no -> downsize group and try again. Assumption that user has at least 3ppl scenes.
Function ProcessActorsAndStartScenes(Actor[] actors)
  int jMalesArr = JValue.releaseAndRetain(jMalesArr, JArray.object())
  int jFemalesArr = JValue.releaseAndRetain(jFemalesArr, JArray.object())
  int countThreads = 0
  int i = 0
  int count = actors.Length

  ; add player as first actor based on gender
  if(sexUtil.GetGender(PlayerRef) == 0)
    JArray.addForm(jMalesArr, PlayerRef)
  else
    JArray.addForm(jFemalesArr, PlayerRef)
  endif

  ; add all other actors to gender specific arrays
  while i < count
    actor currActor = actors[i]
    int currSex = sexUtil.GetGender(currActor)
    
    if(currSex == 0)
      JArray.addForm(jMalesArr, currActor)
    else
      JArray.addForm(jFemalesArr, currActor)
    endif

    i += 1
  endwhile

  MinaiUtil.Debug("Try to form orgy with: "+JArray.count(jMalesArr)+" males and "+JArray.count(jFemalesArr)+" females")

  string framework = "sexlab"
  if(useOstim())
    framework = "ostim"
  endif

  while((JArray.count(jMalesArr) > 0 || JArray.count(jFemalesArr) > 0) && countThreads < config.maxThreads)
    MinaiUtil.Debug("Currently running threads: "+countThreads)
    int groupSize = Utility.RandomInt(3, 5)
    MinaiUtil.Debug("Try to form group with size "+groupSize)
    int remainingActors = JArray.count(jMalesArr) + JArray.count(jFemalesArr)
    if (remainingActors <= 8) 
      
      groupSize = remainingActors - 3
      MinaiUtil.Debug("There are less than 8 actors change group size to "+groupSize)
    endif

    if(remainingActors <= 5) 
      MinaiUtil.Debug("There are less than 5 actors change group size to "+groupSize)
      groupSize = remainingActors
    endif

    actor[] group = sexUtil.BuildGroup(groupSize, jMalesArr, jFemalesArr)

    ; if user has not valid animations for selected set of actors try to downsize and build group again
    while(!sexUtil.CheckGroup(group, framework) && groupSize > 2)
      ; try to downsize group and build it again
      groupSize -= 1
      MinaiUtil.Debug("User doesn't have animations for this group, try to downsize to "+groupSize)
      ; add actors from group back to gender specific arrays
      int j = 0
      while(j < group.length)
        int currSex = sexUtil.GetGender(group[j])
        if(currSex == 0)
          JArray.addForm(jMalesArr, group[j])
        elseif(currSex == 1)
          JArray.addForm(jFemalesArr, group[j])
        endif
        j += 1
      endwhile
      
      group = sexUtil.BuildGroup(groupSize, jMalesArr, jFemalesArr)
    endwhile

    ; if group exists and it contains actors and user has animations for this group -> then start thread
    if(group && group.Length > 0 && sexUtil.CheckGroup(group, framework))
      MinaiUtil.Debug("Group of "+groupSize+" was formed and initiated scene")
      StartSexOrSwitchToGroup(group, group[0])
      countThreads += 1
      Utility.Wait(3.0)
    endif
  endwhile
EndFunction

Function StartSexOrSwitchToGroup(actor[] actors, actor akSpeaker, string tags="")
  MinaiUtil.Info("Sex: Starting/switching for " + actors.Length + " actors (tags: " + tags + ")")
  bool bSpeakerInScene = False
  bool bPlayerInScene = False
  bool bCanAnimate = True
  bool isNewScene = False
  int actor_idx = 0
  if CanAnimate(playerRef) && actors.Find(playerRef) >= 0 && config.confirmSex
    int result = minai_confirmSexMsg.Show()
    if result == 1
      Main.Info("User declined sex scene, aborting")
      return
    EndIf
  EndIf
  if useOstim()
    MinaiUtil.Debug("OStim detected - processing scene request")
    ostim.StartSexOrSwitchToGroup(actors, akSpeaker, tags)
  else
    sexlab.StartSexOrSwitchToGroup(actors, akSpeaker, tags, lastTag)
    lastTag = tags
  EndIf
EndFunction

Function StartSexOrSwitchTo(actor akSpeaker, actor akTarget, string tags)
  MinaiUtil.Info("StartSexOrSwitchTo( " + MinaiUtil.GetActorName(akSpeaker) + ", " + MinaiUtil.GetActorName(akTarget) + ", " + tags + " )")
  Actor[] actors = new Actor[2]
  actors[0] = akSpeaker
  actors[1] = akTarget
  StartSexOrSwitchToGroup(actors, akSpeaker, tags)
EndFunction



Function SpeedUpSex(actor akActor)
  if useOstim()
    ostim.SpeedUp(akActor)
  EndIf
EndFunction



Function SlowDownSex(actor akActor)
  if (useOstim())
    ostim.SlowDown(akActor)
  EndIf
EndFunction



Function EndSex(actor akSpeaker)
  if OActor.IsInOStim(akSpeaker)
    ostim.StopThread(akSpeaker)
  else
    ; add SL Stop
    int threadID = slf.FindPlayerController()
    EndSexScene(threadID, True)
    ;;; WILL THIS WORK?
  EndIf
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


; Mantella legacy
bool Function UseSex()
  return slf != None || bHasOstim
EndFunction


; Mantella legacy
Function ActionResponse(actor akTarget, actor akSpeaker, string sayLine, actor[] actorsFromFormList, bool bPlayerInScene)
  actor Player = game.GetPlayer()
  ; Mutually Exclusive keywords
  if stringutil.Find(sayLine, "-masturbate-") != -1
    Start1pSex(akSpeaker)
  elseif stringutil.Find(sayLine, "-startsex-") != -1 || stringUtil.Find(sayLine, "-sex-") != -1 || stringUtil.Find(sayLine, "-fuck-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "vaginal")
  elseif stringutil.Find(sayLine, "-vaginalsex-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "vaginal")
  elseif stringutil.Find(sayLine, "-analsex-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "anal")
  elseif stringutil.Find(sayLine, "-missionary-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "missionary")
  elseif stringutil.Find(sayLine, "-cowgirl-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "cowgirl")
  elseif stringutil.Find(sayLine, "-reversecowgirl-") != -1 || stringutil.Find(sayLine, "-reversedcowgirl-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "reversecowgirl")
  elseif stringutil.Find(sayLine, "-doggystyle-") != -1 || stringutil.Find(sayLine, "-kittystyle-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "doggystyle")
  elseif stringutil.Find(sayLine, "-sixtynine-") != -1 || stringutil.Find(sayLine, "-69-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "sixtynine,69")
  elseif stringutil.Find(sayLine, "-blowjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "blowjob")
  elseif stringutil.Find(sayLine, "-handjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "handjob")
  elseif stringutil.Find(sayLine, "-footjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "footjob")
  elseif stringutil.Find(sayLine, "-boobjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "boobjob")
  elseif stringutil.Find(sayLine, "-cunnilingus-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "cunnilingus")
  elseif stringutil.Find(sayLine, "-facesitting-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "facesitting")
  elseif stringutil.Find(sayLine, "-facial-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "facial")
  elseif stringutil.Find(sayLine, "-cumonchest-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "chestcum")
  elseif stringutil.Find(sayLine, "-rubbingclitoris-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "pussy")
  elseif stringutil.Find(sayLine, "-deepthroat-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "oral")
  elseif stringutil.Find(sayLine, "-rimjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "rimjob")
  elseif stringutil.Find(sayLine, "-fingering-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "fingering")
  elseif stringutil.Find(sayLine, "-vampirebite-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "vampire")
  elseif stringutil.Find(sayLine, "-suckingnipple-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "breastfeeding")
  ; Could not find SL equivalent for these
  elseif stringutil.Find(sayLine, "-grinding-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "grindingpenis,buttjob")
  elseif stringutil.Find(sayLine, "-thighjob-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "thighjob")
  ; experimental kiss animation for OStim
  elseif stringutil.Find(sayLine, "-smooch-") != -1
    if useOstim()
      OThread.Quickstart(OActorUtil.ToArray(akSpeaker, akTarget), "OARE_HoldingChinKiss")
    EndIf
  ; Kissing/Cuddling experimental alternates - leads to sex
  elseif stringutil.Find(sayLine, "-cuddling-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "hugging")
  elseif stringutil.Find(sayLine, "-frenchkissing-") != -1 || stringutil.Find(sayLine, "-frenchkiss-") != -1 || stringutil.Find(sayLine, "-french kissing-") != -1 || stringutil.Find(sayLine, "-french kiss-") != -1
    StartSexOrSwitchTo(akSpeaker, akTarget, "kissing")
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
  if !akTarget
    akTarget = PlayerRef
  EndIf
  if (akTarget.IsChild())
    Main.Warn(MinaiUtil.GetActorName(akTarget) + " is a child actor. Not processing actions.")
    return
  EndIf
  bool bPlayerInScene = (akTarget == PlayerRef || akSpeaker == PlayerRef)

  string targetName = MinaiUtil.GetActorName(akTarget)
  If command == "ExtCmdMasturbate"
    Start1pSex(akSpeaker)
  elseif command == "ExtCmdStartBlowjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, "blowjob")
  elseif command == "ExtCmdStartAnal"
    StartSexOrSwitchTo(akSpeaker, akTarget, "anal")
  elseif command == "ExtCmdStartVaginal"
    StartSexOrSwitchTo(akSpeaker, akTarget, "vaginal")
  elseif command == "ExtCmdStartHandjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, "handjob")
  elseif command == "ExtCmdStartFootjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, "footjob")
  elseif command == "ExtCmdStartBoobjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, "boobjob")
  elseif command == "ExtCmdStartCunnilingus"
    StartSexOrSwitchTo(akSpeaker, akTarget, "cunnilingus")
  elseif command == "ExtCmdStartFacial"
    StartSexOrSwitchTo(akSpeaker, akTarget, "facial")
  elseif command == "ExtCmdStartCumonchest"
    StartSexOrSwitchTo(akSpeaker, akTarget, "chestcum")
  elseif command == "ExtCmdStartRubbingclitoris"
    StartSexOrSwitchTo(akSpeaker, akTarget, "pussy")
  elseif command == "ExtCmdStartDeepthroat"
    StartSexOrSwitchTo(akSpeaker, akTarget, "oral")
  elseif command == "ExtCmdStartRimjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, "rimjob")
  elseif command == "ExtCmdStartFingering"
    StartSexOrSwitchTo(akSpeaker, akTarget, "fingering")
  elseif command == "ExtCmdStartMissionarySex"  
    StartSexOrSwitchTo(akSpeaker, akTarget, "missionary")
  elseif command == "ExtCmdStartCowgirlSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, "cowgirl")
  elseif command == "ExtCmdStartReverseCowgirl"
    StartSexOrSwitchTo(akSpeaker, akTarget, "reversecowgirl")
  elseif command == "ExtCmdStartDoggystyle"
    StartSexOrSwitchTo(akSpeaker, akTarget, "doggystyle")
  elseif command == "ExtCmdStartFacesitting"
    StartSexOrSwitchTo(akSpeaker, akTarget, "facesitting")
  elseif command == "ExtCmdStart69Sex"
    StartSexOrSwitchTo(akSpeaker, akTarget, "sixtynine,69")
  elseif command == "ExtCmdStartGrindingSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, "grindingpenis,buttjob")
  elseif command == "ExtCmdStartThighjob"
    StartSexOrSwitchTo(akSpeaker, akTarget, "thighjob")
  elseif command == "ExtCmdStartCuddleSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, "hugging")
  elseif command == "ExtCmdStartKissingSex"
    StartSexOrSwitchTo(akSpeaker, akTarget, "kissing")
  elseif command == "ExtCmdStartThreesome"
    ; will be replaced by invite/join functions
    actor[] actors = new Actor[3]
    actors[0] = akSpeaker
    actors[1] = akTarget
    actors[2] = PlayerRef
    if actors[1] == actors[2]
      actors = PapyrusUtil.SliceActorArray(actors, 0, 1)
      Main.Debug("Threesome attempt - Must target another NPC")
      Main.RequestLLMResponseNPC(speakerName, "Please join us for sex!", parameter, "chatnf_invite")
    EndIf
    StartSexOrSwitchToGroup(actors, akSpeaker)
  elseIf command == "ExtCmdStartOrgy"
    actor[] actors = aiff.GetNearbyAI()
    ProcessActorsAndStartScenes(actors)
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
  elseif (command=="ExtCmdFollow")
    StartFollow(akSpeaker, akTarget)
  elseif (command=="ExtCmdStopFollowing")
    EndFollow(akSpeaker)
  EndIf
EndEvent


Event OStimManager(string eventName, string strArg, float numArg, Form sender)
  int ostimTid = numArg as int
  ; ostim thread with index 0 is reserved for player scenes
  bool playerInvolved = sexUtil.isPlayerInvolved(ostimTid, ostimType)
  bool isRunning = OThread.IsRunning(ostimTid)
  string sceneId = strArg
  Main.Debug("oStim eventName: "+eventName+", strArg: "+strArg+", numArg: "+numArg as int+" sender: "+sender as actor )
  if (eventName == "ostim_thread_start")
    ostim.resetPrevSpeed(ostimTid)
    if isRunning
      Main.Info("OStim scene startthread")
      onSexStart(ostimTid, ostimType)
    else
      Main.Debug("OStim thread start failed")
    EndIf
  
  elseif (eventName == "ostim_thread_scenechanged")
    ; reset previous speed on scene change, so if different scene has different default speed it won't count as speed change
    ostim.resetPrevSpeed(ostimTid)
    if isRunning
      Main.Info("Ostim Scene changed to: " + sceneId)
      ; we don't want to catch transition scenes they usually couple of seconds which isn't enough to have conversation
      if(OMetadata.isTransition(sceneId))
        return
      endif
      onSceneChange(ostimTid, ostimType)
    else
      Main.Debug("OStim scene change failed")
    EndIf
    
  elseif (eventName == "ostim_thread_speedchanged")
    int newSpeed = strArg as int
    int prevSpeed = ostim.getPrevSpeed(ostimTid)
    
    ; when thread starts it fires this event, also on scene change if scenes have different default speed it will fire this event, but in this case scene change is more critical for us
    if(!prevSpeed || prevSpeed == -1 || prevSpeed == newSpeed)
      ostim.setPrevSpeed(ostimTid, newSpeed)
      return 
    endif

    Actor[] actors = OThread.GetActors(ostimTid)
    bool increase = newSpeed > prevSpeed
    if isRunning
      sexTalkSpeedChange(sexUtil.GetWeightedRandomActorToSpeak(actors, bHasOstim), playerInvolved, ostimType, increase)
      Main.Info("Ostim speed change")
      ostim.setPrevSpeed(ostimTid, newSpeed)
    else
      Main.Debug("OStim speed change failed")
    EndIf
  elseif (eventName == "ostim_actor_orgasm")   
    Actor OrgasmedActor = sender as Actor
    if(OrgasmedActor != PlayerRef)
      sexTalkClimax(OrgasmedActor, playerInvolved, ostimType)
    endif
    Main.Info("Ostim actor orgasm: " + OrgasmedActor)

  elseif (eventName == "ostim_thread_end")  
    Main.Info("OStim scene ended")
    ostim.removePrevSpeed(ostimTid)
    onSexEnd(ostimTid, ostimType)
  EndIf
EndEvent


Event OnAnimationStart(int tid, bool HasPlayer)
  sexlab.LoadSexlabDescriptions()
  Main.Info("Started Sex Scene")
  onSexStart(tid, sexlabType)
EndEvent


; we need it for some native logic of AIFF doesn't participate in MinAI php logic
Function SetSexSceneState(string sexState)
  if bHasAIFF
    AIAgentFunctions.logMessage("sexscene@" + sexState,"setconf")
  EndIf
  if sexState == "off"
    lastTag = ""
  EndIf
EndFunction

Event OnStageStart(int tid, bool HasPlayer)
  sslThreadController controller = slf.GetController(tid)
  
  if (controller.Stage==1) 
    sexlab.LoadSexlabDescriptions()
  EndIf
  onSceneChange(tid, sexlabType)
EndEvent

Event SLSOOrgasm(Form actorRef, Int tid)
  ; Make sure that the actor is an Actor, this should always that case but just in case
  Actor actorInAction = actorRef as Actor
  If (actorInAction == None)
    Main.Debug("[SLSOOrgasm] No actor in action")
    return
  EndIf

  ; if using sexlab p+ and climaxtype = scene, then handle climax in scenechange
  If isSLSBClimaxType()
    return
  EndIf

  Main.Debug("[SLSOOrgasm] name = " + actorInAction.GetName() + " tid: " + tid)
  sslThreadController controller = slf.GetController(tid)
  Actor[] actorList = slf.HookActors(tid)

  if (actorList.length < 1)
    return
  EndIf

  bool hasPlayer = sexUtil.isPlayerInvolved(tid, sexlabType)
  
  sexTalkClimax(actorInAction, hasPlayer, sexlabType)
EndEvent

Event PostSexScene(int tid, bool HasPlayer)
  Main.Debug("[PostSexScene] tid = " + tid)

  ; if using sexlab p+ and climaxtype = scene, then handle climax in scenechange
  If isSLSBClimaxType()
    return
  EndIf

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
    sexTalkClimax(actorWithBelt, HasPlayer, true)
  else
    sexTalkClimax(sexUtil.GetWeightedRandomActorToSpeak(sortedActorList, bHasOstim), HasPlayer, sexlabType)
  EndIf
  lastTag = ""
EndEvent

Event EndSexScene(int tid, bool HasPlayer)
    sexlab.releaseJDescriptionsMap()
    Main.Info("Ended Sex scene")
    onSexEnd(tid, sexlabType)
EndEvent


string Function GetKeywordsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction



string Function GetFactionsForActor(actor akTarget)
  string ret = ""

  return ret
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
  
  if(framework == sexlabType) && bHasSexlab
    if bHasSexlabPPlus
      SexLabThread thread = slf.GetThread(ThreadID)
      string sceneHash = thread.GetActiveScene()
      string stageHash = thread.GetActiveStage()
      string firstStageHash = SexlabRegistry.GetAllstages(sceneHash)[0]
  
      if (stageHash == firstStageHash)
        sexlab.LoadSexlabDescriptions()
      EndIf
      actors = thread.GetPositions()

      string sceneIdWithPrefix = SexlabRegistry.GetAnimationEvent(sceneHash, stageHash, 0)
      sceneId = StringUtil.Substring(sceneIdWithPrefix, 4, -1)
    else
      sslThreadController controller = slf.GetController(ThreadID)
    
      if (controller.Stage==1) 
        sexlab.LoadSexlabDescriptions()
      EndIf
      actors = slf.GetController(ThreadID).Positions

      sceneId = controller.Animation.FetchStage(controller.Stage)[0]
    endif
  elseif (framework == ostimType) && bHasOstim
    actors = OThread.GetActors(ThreadID)
    sceneId = OThread.GetScene(ThreadID)
  endif

  actor[] maleActors = PapyrusUtil.ActorArray(0)
  actor[] femaleActors = PapyrusUtil.ActorArray(0)
  int i = 0
  int count = actors.Length
  while i < count
    actor currActor = actors[i]
    int currSex = sexUtil.GetGender(currActor)
    
    if(currSex == 0)
      maleActors = PapyrusUtil.PushActor(maleActors, currActor)
    elseif(currSex == 1)
      femaleActors = PapyrusUtil.PushActor(femaleActors, currActor)
    endif

    i += 1
  endwhile

  string maleActorsString = MinaiUtil.JoinActorArray(maleActors)
  string femaleActorsString = MinaiUtil.JoinActorArray(femaleActors)

  string fallback = buildSceneFallbackDescription(ThreadID, framework, type)

  string jsonToSend = "{ \"type\": \""+type+"\", \"framework\": \""+framework+"\", \"threadId\": "+ThreadID+", \"maleActors\": \""+maleActorsString+"\", \"femaleActors\": \""+femaleActorsString+"\", \"scene\": \""+sceneId+"\""

  if(fallback != "")
    jsonToSend += ", \"fallback\": \""+fallback+"\""
  endif

  jsonToSend += "}"

  AIAgentFunctions.logMessage("command@ExtCmdUpdateThreadsTable@"+ jsonToSend +"@", "updateThreadsDB")
endfunction

function sexTalkClimax(actor speaker, bool hasPlayer, string framework, bool chastity = false)
  if(chastity)
    SexTalk(speaker, "sextalk_climaxchastity", hasPlayer, framework, config.forceOrgasmComment)
  else
    SexTalk(speaker, "sextalk_climax", hasPlayer, framework, config.forceOrgasmComment)
  endif
endfunction

function sexTalkSceneChage(actor speaker, bool hasPlayer, string framework)
  SexTalk(speaker, "sextalk_scenechange", hasPlayer, framework)
endfunction

function sexTalkSpeedChange(actor speaker, bool hasPlayer, string framework, bool increase)
  if(increase)
    SexTalk(speaker, "sextalk_speedincrease", hasPlayer, framework)
  else
    SexTalk(speaker, "sextalk_speeddecrease", hasPlayer, framework)
  endif
  
endfunction

function sexTalkOnEnd(actor speaker, bool hasPlayer, string framework)
  SexTalk(speaker, "sextalk_end", hasPlayer, framework, config.forcePostSceneComment)
endfunction

function sexTalkCollision(actor speaker, bool hasPlayer, string framework, string promptToSay)
  SexTalk(speaker, "chatnf_vr_1", hasPlayer, framework)
endfunction

function sexTalkAmbient(actor speaker, bool hasPlayer, string framework)
  SexTalk(speaker, "sextalk_ambient", hasPlayer, framework)
endfunction

; speaker is who actually will say llm lines, can be none if scene is player only
; chatType different AIFF custom chat topics see php files
Function SexTalk(actor speaker, string chatType, bool hasPlayer, string framework, bool ignoreSexTalkCooldown = false)
  if !bHasAIFF || !speaker
    return
  EndIf

  bool anyThreadWithPlayer = sexUtil.getPlayerThread(framework) != -1

  ; don't request if there any thread with player involved and current thread doesn't have player and config enabled player prioritazation
  if(anyThreadWithPlayer && !hasPlayer && config.prioritizePlayerThread)
    return
  endif

  ; Throttle on how often we should sex talk incase people are switching animations
  float currentTime = Utility.GetCurrentRealTime()
  if currentTime - lastSexTalk > config.commentsRate || ignoreSexTalkCooldown
    lastSexTalk = currentTime
    string speakerName = MinaiUtil.GetActorName(speaker)
    Main.Debug("SexTalk() => " + speakerName + ": " + chatType)
    Main.RequestLLMResponseNPC("", "", speakerName, chatType)
  else
    Main.Debug("SexTalk - THROTTLED")
  EndIf
EndFunction

string function buildSceneFallbackDescription(int ThreadID, string framework, string eventType)
  if(framework == ostimType && bHasOstim)
    return ostim.buildSceneFallbackDescription(ThreadID, eventType)
  elseif(framework == sexlabType && bHasSexlab)
    return sexlab.buildSceneFallbackDescription(ThreadID, eventType)
  endif
endfunction

function onSexStart(int ThreadID, string framework)
  bool playerInvolved = sexUtil.isPlayerInvolved(ThreadID, framework)

  if playerInvolved
    AIFF.ChillOut()
  EndIf

  ; enable sex-mode for php
  SetSexSceneState("on")
  UpdateThreadTable("startthread", framework, ThreadID)
  ambientSexTalk.OnSexStart(ThreadID, framework)

  ; get actors to store random actor who will talk at the end. Doing it at start ensure that on sex end events we won't end up where in threads there are no actors anymore
  Actor[] actors
  if(framework == ostimType)
    actors = OThread.GetActors(ThreadID)
  elseif(framework == sexlabType)
    actors = slf.GetController(ThreadID).Positions
  endif

  JMap.setForm(actorToSayOnEndMap, ThreadID, sexUtil.GetWeightedRandomActorToSpeak(actors, bHasOstim))
endfunction

function onSexEnd(int ThreadID, string framework)
  ; disable sex-mode for php. AIFF implementation is a little incorrect, since there are still
  SetSexSceneState("off")
  UpdateThreadTable("end", framework, ThreadID)
  ambientSexTalk.OnSexEnd(ThreadID, framework)

  ; get actor to say something after sex
  bool playerInvolved = sexUtil.isPlayerInvolved(ThreadID, framework)
  actor actorToSayOnEnd = JMap.getForm(actorToSayOnEndMap, ThreadID) as Actor
  if bHasAIFF
    AIFF.SetAnimationBusy(0, MinaiUtil.GetActorName(actorToSayOnEnd))
  EndIf
  sexTalkOnEnd(actorToSayOnEnd, playerInvolved, framework)
  ; clean actor to say from finished thread
  JMap.removeKey(actorToSayOnEndMap, ThreadID)
endfunction

function onSceneChange(int ThreadID, string framework)
  UpdateThreadTable("scenechange", framework, ThreadID)
  bool playerInvolved = sexUtil.isPlayerInvolved(ThreadID, framework)
  ; in case if any framework need to block sex talk during scene change
  bool skipSexTalk = false
  ; in case the scene change says which actors climax (sexlab p+ with SLSB packs)
  bool useClimaxTalk = false
  if(playerInvolved)
    AIFF.ChillOut()
  endif

  actor[] actors
  actor[] climaxingActors
  if(framework == ostimType)
    actors = OThread.GetActors(ThreadID)
  elseif(framework == sexlabType)
    sslThreadController controller = slf.GetController(ThreadID)
    actors = controller.Positions

    ; if using sexlab p+ and climaxtype = scene, then handle climax in scenechange
    If isSLSBClimaxType()
      ; will be empty if only climax was by the player
      climaxingActors = getClimaxingActorsArray(ThreadID)
      If climaxingActors.Length > 0
        useClimaxTalk = true
      EndIf

    ; we don't want to fire scene change sex talk on last sexlab stage since it will be orgasm stage for actors, where orgasm sex talk will be fired
    ; elseif to avoid skipping talk in final scene in case SLSB climax took place in an earlier scene
    elseif (controller.Stage == controller.Animation.StageCount())
      skipSexTalk
    endif
  endif

  int i = 0
  while i < actors.Length
    if actors[i] != PlayerRef
      aiff.setAnimationBusy(1, MinaiUtil.GetActorName(actors[i]))
    EndIf
    i += 1
  EndWhile
  if(!skipSexTalk)
    If useClimaxTalk
      sexTalkClimax(sexUtil.GetWeightedRandomActorToSpeak(climaxingActors, bHasOstim), playerInvolved, framework)
    Else
      sexTalkSceneChage(sexUtil.GetWeightedRandomActorToSpeak(actors, bHasOstim), playerInvolved, framework)
    EndIf
  endif
endfunction

bool Function IsNSFW()
  return bHasSexlab || bHasOstim
EndFunction

bool function useOstim()
  return bHasOstim && minai_UseOStim.GetValue() == 1.0
endfunction

; check if sexlab p+ is active and ClimaxType is set to Scene in its MCM
bool Function isSLSBClimaxType()
  If bHasSexlabPPlus
    int climaxType = sslSystemConfig.GetSettingInt("iClimaxType")
    ; 0 = from SLSB data, 1 = last scene in animation, 2 = sexlab p+ version of SLSO
    return climaxType == 0
  EndIf
  return false;
EndFunction

; get which actors are climaxing in a sexlab p+ scene (excluding the player)
actor[] Function getClimaxingActorsArray(int ThreadID)
  SexLabThread thread = slf.GetThread(ThreadID)
  string sceneHash = thread.GetActiveScene()
  string stageHash = thread.GetActiveStage()
  actor[] climaxingActors = PapyrusUtil.ActorArray(0)

  ; get the climaxing positions in the scene (0, 1, 2, etc)
  int[] climaxingPositions = SexlabRegistry.GetClimaxingActors(sceneHash, stageHash)
  If climaxingPositions.Length == 0
    return climaxingActors
  EndIf

  actor[] actorsInScene = thread.GetPositions()
  int i = 0
  while i < actorsInScene.Length
    actor currActor = actorsInScene[i]
    ; don't add the player
    If currActor != PlayerRef && thread.IsOrgasmAllowed(currActor)
      ; check if the actor is in one of the climaxing positions
      int actorPosition = thread.GetPositionIdx(currActor)
      If climaxingPositions.Find(actorPosition) >= 0
        climaxingActors = PapyrusUtil.PushActor(climaxingActors, currActor)
      EndIf
    EndIf
    i += 1
  EndWhile

  return climaxingActors
EndFunction
