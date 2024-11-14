scriptname minai_SexSexlab extends Quest

Actor Property PlayerRef auto

SexLabFramework slf
minai_DeviousStuff devious
minai_SexUtil sexUtil
minai_Util MinaiUtil
minai_MainQuestController main
minai_Config config
int jDescriptionsMap

function Maintenance(SexLabFramework localSlf, minai_DeviousStuff localDevious)
  slf = localSlf
  devious = localDevious
  sexUtil = (self as Quest) as minai_SexUtil
  MinaiUtil = (self as Quest) as minai_Util
  main = (self as Quest) as minai_MainQuestController
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
endfunction


Function StartSexlabScene(bool bPlayerInScene, actor[] actorsToSort, string tags) 
  MinaiUtil.Debug("SortActorsForSex(" + bPlayerInScene +")")
  MinaiUtil.Debug("Sorting actors: " + actorsToSort)
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

  MinaiUtil.Debug("Done Sorting actors")
  slf.StartSex(actorsToSort, FindSexlabAnimations(actorsToSort, tags))
EndFunction

function StartSexOrSwitchToGroup(actor[] actors, actor akSpeaker, string tags = "", string lastTag)
  bool bSpeakerInScene = False
  bool bPlayerInScene = False
  bool bCanAnimate = True
  bool isNewScene = False
  int actor_idx = 0
  Actor[] actorsInScene
  int i = 0
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
    if !sexUtil.CanAnimate(actorsInScene[i])
      bCanAnimate = False
    EndIf
    i += 1
  EndWhile

  if lastTag != "" && lastTag == tags && bSpeakerInScene
    MinaiUtil.Warn("Aborting StartSexOrSwitchTo: Tag '" + tags + "' is the same as previous tag '" + lastTag +"'")
    return
  EndIf
  if isNewScene
    StartSexlabScene(bPlayerInScene, actors, tags)
  elseif !config.allowSexTransitions
    MinaiUtil.Warn("Aborting StartSexOrSwitchTo: Scene already active, and allowSexTransitions is disabled")
    return
  else
    if !bSpeakerInScene ; Speaker not in scene, add them to it
      ; Abort if the speaker is not already in the scene and the option is disabled
      if !config.allowActorsToJoinSex
        MinaiUtil.Warn("Aborting StartSexOrSwitchTo: Speaker not in scene and config.allowActorsToJoinSexScene is disabled")
        return
      EndIf
      actorsInScene = PapyrusUtil.PushActor(actorsInScene,akSpeaker)
      actorsInScene = slf.SortActors(actorsInScene)
    EndIf
    sslBaseAnimation[] animations = FindSexlabAnimations(actorsInScene, tags)
    if actorsInScene.Length != actors.Length || !bSpeakerInScene
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
      main.RegisterEvent(MinaiUtil.GetActorName(actors[0]) + " changed up their masturbation to " + tags + " instead.", "info_sexscene")
    elseif actors.Length == 2
      main.RegisterEvent(MinaiUtil.GetActorName(actors[0]) + " and " + MinaiUtil.GetActorName(actors[1]) + " changed up their sex to " + tags + " instead.", "info_sexscene")
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
endfunction

bool Function CompareActorSex(actor actor1, actor actor2)
  ; We want to sort males to the end, and females to the front.
  ; 0 = male
  ; 1 = female
  ; 2 = other
  return slf.GetGender(actor1) < slf.GetGender(actor2)
EndFunction

sslBaseAnimation[] Function FindSexlabAnimations(actor[] actors, string tags, bool forceaggressive = false)
  int numMales = sexUtil.countMales(actors)
  int numFemales = actors.length - numMales
  MinaiUtil.Debug("FindSexlabAnimations (" + numMales + " males, " + numFemales + " females): " + actors)
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
    MinaiUtil.Debug("FindValidSexlabAnimations: Using DD's SelectValidDDAnimations")
    ret = devious.libs.SelectValidDDAnimations(actors, numMales + numFemales, forceaggressive, tags, suppressTag)
  EndIf
  if !ret
    MinaiUtil.Debug("FindValidSexlabAnimations: Falling back to slf getanimation functions")
    if tags == ""
      return slf.GetAnimationsByDefault(numMales, numFemales)
    else
      return slf.GetAnimationsByTags(numMales + numFemales, tags, suppressTag)
    EndIf
  EndIf
  return ret
EndFunction

Function InitializeSexDescriptions()
  if (JMap.count(jDescriptionsMap) != 0 || jDescriptionsMap != 0)
    MinaiUtil.Info("Not reinitializing sexlab descriptions - data already exists.")
    return
  EndIf
  jDescriptionsMap = JMap.object()
  LoadSexlabDescriptions()

  if (JMap.count(jDescriptionsMap) != 0 || jDescriptionsMap != 0)
    MinaiUtil.Info("Not reinitializing sexlab descriptions - data already exists.")
    return
  EndIf
  
  MinaiUtil.Info("Initializing sex descriptions");
  JMap.clear(jDescriptionsMap)
  JMap.setStr(jDescriptionsMap,"Mitos_Laplove_A1_S1","Stands over partner.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A1_S1","staying atop partner with gentle movements.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A2_S1","lying down in a passive stance.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A1_S2","still staying atop partner, sitting on, moving with gentle movements.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A2_S2","lying down in a passive stance.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A1_S3","still staying atop partner, sitting over, moving now with stronger movements.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A2_S3","lying down in a passive stance.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A1_S4","still staying atop partner, almost hugging, moving now with quick movements.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A2_S4","lying down in a passive stance.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A1_S5","still staying atop partner, almost hugging, moving now slowly. Trembling with Pleasure.")
  JMap.setStr(jDescriptionsMap,"Leito_Cowgirl_A2_S5","lying down in a passive stance. Trembling with Pleasure.")
  JValue.writeToFile(jDescriptionsMap, "Data/Data/minai/sexlab_descriptions.json")
EndFunction

Function LoadSexlabDescriptions()
  if (jDescriptionsMap==0)
    MinaiUtil.Info("Loading Sexlab Descriptions")
    jDescriptionsMap=JValue.readFromFile( "Data/Data/minai/sexlab_descriptions.json")
    JValue.retain(jDescriptionsMap)
    MinaiUtil.Info("Descriptions set: "+JMap.count(jDescriptionsMap)+" using map: "+jDescriptionsMap+ " Data/Data/minai/sexlab_descriptions.json")
  EndIf
EndFunction

string Function GetSexStageDescription(string animationStageName) 
  MinaiUtil.Info("Obtaining description for: <"+animationStageName+"> using map: "+jDescriptionsMap)
  return JMap.getStr(jDescriptionsMap,animationStageName)
EndFunction

function releaseJDescriptionsMap()
  JValue.release(jDescriptionsMap)
endfunction

string function buildSceneFallbackDescription(int ThreadID, string eventType)
  sslThreadController controller = slf.GetController(ThreadID)
  if (!controller)
    return ""
  EndIf
  string[] sceneTags= controller.Animation.GetRawTags()
  string sceneId = controller.Animation.Name
  Actor[] actors = slf.GetController(ThreadID).Positions
  string actorString = MinaiUtil.JoinActorArray(actors, ", ")
  string sceneTagsString = MinaiUtil.JoinStringArray(sceneTags, ", ")
  string actionString = ""
  int count = actors.length
  while(count > 0)
    count -= 1
    string stageDescription = GetSexStageDescription(controller.Animation.FetchStage(controller.Stage)[count])

    if(stageDescription != "")
      actionString += sexUtil.GetActorNameForSex(actors[count])+" is "+ stageDescription
    endif
  endwhile

  return sexUtil.buildSceneString(sceneId, actorString, eventType, sceneTagsString, actionString)
endfunction
