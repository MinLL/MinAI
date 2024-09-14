scriptname minai_Sex extends Quest

SexLabFramework slf


bool bHasOstim = False
GlobalVariable minai_UseOstim


minai_MainQuestController main
Actor PlayerRef

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  Debug.Trace("[minai] - Initializing Sex Module.")
  RegisterForModEvent("HookAnimationStart", "OnSexlabAnimationStart")
  RegisterForModEvent("ostim_orgasm", "OnOstimorgasm")
  
  slf = Game.GetFormFromFile(0xD62, "SexLab.esm") as SexLabFramework
  if Game.GetModByName("OStim.esp") != 255
    Debug.Trace("[minai] Found OStim")
    bHasOstim = True
  EndIf

  minai_UseOStim = Game.GetFormFromFile(0x0906, "MinAI.esp") as GlobalVariable
  if !minai_UseOStim
    Debug.Trace("[minai] Could not find ostim toggle")
  EndIf
EndFunction



bool Function CanAnimate(actor akTarget, actor akSpeaker)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0 && !OActor.IsInOStim(akTarget) && !OActor.IsInOStim(akSpeaker)
    return True
  EndIf
  return !slf.IsActorActive(akTarget) && !slf.IsActorActive(akSpeaker)
EndFunction

Function Start1pSex(actor akSpeaker)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    OThread.QuickStart(OActorUtil.ToArray(akSpeaker))
  else
    slf.Quickstart(akSpeaker)
  EndIf
EndFunction


Function Start2pSex(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    int ActiveOstimThreadID
    if bPlayerInScene
    	ActiveOstimThreadID = OThread.QuickStart(OActorUtil.ToArray(Player, akSpeaker))
    else
    	ActiveOstimThreadID = OThread.QuickStart(OActorUtil.ToArray(akTarget, akSpeaker))
    EndIf
    Utility.Wait(2)
    bool AutoMode = OThread.IsInAutoMode(ActiveOstimThreadID)
    if AutoMode == False
    	OThreadBuilder.NoPlayerControl(ActiveOstimThreadID)
    	OThread.StartAutoMode(ActiveOstimThreadID)
    EndIf
  Else
    slf.Quickstart(akTarget,akSpeaker)
  EndIf
EndFunction


Function StartGroupSex(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene, Actor[] actorsFromFormList)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    int ActiveOstimThreadID
    ActiveOstimThreadID = OThread.QuickStart(OActorUtil.ToArray(actorsFromFormList[0],actorsFromFormList[1],actorsFromFormList[2],actorsFromFormList[3],actorsFromFormList[4],actorsFromFormList[5],actorsFromFormList[6],actorsFromFormList[7],actorsFromFormList[8],actorsFromFormList[9]))
    Utility.Wait(2)
    bool AutoMode = OThread.IsInAutoMode(ActiveOstimThreadID)
    if AutoMode == False
      OThreadBuilder.NoPlayerControl(ActiveOstimThreadID)
      OThread.StartAutoMode(ActiveOstimThreadID)
    EndIf
  Else
    int numMales = 0
    int numFemales = 0
    Actor[] sortedActors = new Actor[12]
    int i = 0
    ; If the player is a female actor and is in the scene, put them in slot 0
    if bPlayerInScene && player.GetActorBase().GetSex() != 0
      sortedActors[0] = Player
    EndIf
    while i < actorsFromFormList.Length
      if actorsFromFormList[i].GetActorBase().GetSex() == 0
        numMales += 1
      else
        numFemales += 1
        if sortedActors[0] == None
        ; If there's a female actor in the scene, put them in slot 0
          sortedActors[0] = actorsFromFormList[i]
        EndIf
      EndIf
      if i != 0
        sortedActors[i] = actorsFromFormList[i]
      EndIf
      i += 1
    EndWhile
    if sortedActors[0] == None
      ; No female actors in scene, just use the first one that we skipped before
      sortedActors[0] = actorsFromFormList[0]
    EndIf
    slf.StartSex(actorsFromFormList, slf.GetAnimationsByDefault(numMales, numFemales))
  EndIf
EndFunction


bool function UseSex()
  return slf != None || bHasOstim
EndFunction



Function ActionResponse(actor akTarget, actor akSpeaker, string sayLine, actor[] actorsFromFormList, bool bPlayerInScene)
  actor Player = game.GetPlayer()
    ; Mutually Exclusive keywords
    if CanAnimate(akTarget, akSpeaker)
      If stringutil.Find(sayLine, "-masturbate-") != -1
        Start1pSex(akSpeaker)
      elseif stringutil.Find(sayLine, "-startsex-") != -1 || stringUtil.Find(sayLine, "-have sex-") != -1 || stringUtil.Find(sayLine, "-sex-") != -1 || stringUtil.Find(sayLine, "-having sex-") != -1
        Start2pSex(akSpeaker, akTarget, Player, bPlayerInScene)
      elseIf stringutil.Find(sayLine, "-groupsex-") != -1 || stringUtil.Find(sayLine, "-orgy-") != -1 || stringUtil.Find(sayLine, "-threesome-") != -1 || stringUtil.Find(sayLine, "-fuck-") != -1
        StartGroupSex(akSpeaker, akTarget, Player, bPlayerInScene, actorsFromFormList)
      EndIf
    Else
      Debug.Trace("[minai] Not processing keywords for exclusive scene - Conflicting scene is running")
    EndIf
    
EndFunction


Event OnSexlabAnimationStart(int threadID, bool HasPlayer)
      sslThreadController Controller = SLF.ThreadSlots.GetController(threadID)
      sslBaseAnimation anim = Controller.Animation
      String sexDesc = ""
      If HasPlayer
        if anim.HasTag("Boobjob")
	  sexDesc = "giving a blowjob"
	elseif anim.HasTag("Vaginal")
	  sexDesc = "having vaginal sex"
	elseif anim.hasTag("Fisting")
	  sexDesc = "having having her pussy fisted"
	elseif anim.hasTag("Anal")
	  sexDesc="having anal sex"
	elseif anim.HasTag("Oral")
	  sexDesc = "giving a blowjob"
	elseif anim.HasTag("Spanking")
	  sexDesc = "being spanked"
	elseif anim.HasTag("Masturbation")
	  sexDesc = "masturbating furiously"
	endif
      EndIf
      Actor[] actors = Controller.Positions
      int i = 0
      while i < actors.Length
        main.RegisterEvent(actors[i].GetActorBase().GetName() + " started " + sexDesc + ".")
        i += 1
      EndWhile
EndEvent



Event CommandDispatcher(String speakerName,String  command, String parameter)
  Actor akSpeaker=AIAgentFunctions.getAgentByName(speakerName)
  actor akTarget= AIAgentFunctions.getAgentByName(parameter)
  if !akTarget
    akTarget = PlayerRef
  EndIf

  bool bPlayerInScene = (akTarget == PlayerRef || akSpeaker == PlayerRef)

  string targetName = main.GetActorName(akTarget)
  if CanAnimate(akTarget, akSpeaker)
    If command == "ExtCmdMasturbate"
      Start1pSex(akSpeaker)
    elseif command == "ExtCmdStartSexScene"
      Start2pSex(akSpeaker, akTarget, PlayerRef, bPlayerInScene)
    elseIf command == "ExtCmdOrgy"
      Debug.Notification("Orgy is broken until I figure out how to get all AI actors")
      ; StartGroupSex(akSpeaker, akTarget, PlayerRef, bPlayerInScene, actorsFromFormList)
    EndIf
  Else
    Debug.Trace("[minai] Not processing keywords for exclusive scene - Conflicting scene is running")
  EndIf
EndEvent