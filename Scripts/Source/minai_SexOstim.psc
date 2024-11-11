scriptname minai_SexOstim extends Quest

Actor Property PlayerRef auto

minai_SexUtil sexUtil
minai_Util MinaiUtil
minai_Config config
; map to store ostim previous speeds per thread. Need to compare if thread sped up or slowed down
; {[threadID]: integer}
int jThreadsPrevSpeedsMap

function Maintenance()
  sexUtil = (self as Quest) as minai_SexUtil
  MinaiUtil = (self as Quest) as minai_Util
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  jThreadsPrevSpeedsMap = JValue.releaseAndRetain(jThreadsPrevSpeedsMap, JMap.object())
endfunction

int function StartOstim(actor[] actors, string tags = "")
  MinaiUtil.Debug("StartSexScene processing for OStim: " + tags)
  int builderID = OThreadBuilder.create(actors)

  string newScene = getSceneByActionsOrTags(actors, tags, true)

  OThreadBuilder.SetStartingAnimation(builderID, newScene)

  int newThreadID = OThreadBuilder.Start(builderID)
  MinaiUtil.Debug("OStim Thread [" + newThreadID + "] Initialized")

  return newThreadID
endfunction

Function StartSexOrSwitchToGroup(actor[] actors, actor akSpeaker, string tags="")
  bool bSpeakerInScene = False
  bool bPlayerInScene = False
  bool bCanAnimate = True
  bool isNewScene = False
  int actor_idx = 0
  ; Ostim
  Actor[] ostimActors = actors
  bPlayerInScene = sexUtil.getPlayerThread("ostim") != -1
  int ActiveOstimThreadID = OActor.GetSceneID(akSpeaker)

  
  if(ActiveOstimThreadID < 0)
    ; akSpeaker is not in an OStim thread
    MinaiUtil.Debug("OStim detects akSpeaker is not in thread: " + ActiveOstimThreadID)

    ; search if any of actors is in ostim scene
    ActiveOstimThreadID = FindThread(actors)

    if(ActiveOstimThreadID < 0)
      ; there is no ostim scene let's start new one
      MinaiUtil.Debug("OStim detects none of actors are in the thread: " + ActiveOstimThreadID)
      StartOstim(actors, tags)
    elseif !config.allowActorsToJoinSex
      MinaiUtil.Warn("Aborting StartSexOrSwitchTo: Speaker not in scene and config.allowActorsToJoinSexScene is disabled")
      return 
    else
      ; there is active ostim scene let's add akSpeaker to that scene
      MinaiUtil.Debug("OStim detects someone from actors is already in thread: " + ActiveOstimThreadID)
      AddActorsToActiveThread(ActiveOstimThreadID, OActorUtil.ToArray(akSpeaker))
    endif
  elseif !config.allowSexTransitions
    MinaiUtil.Warn("Aborting StartSexOrSwitchTo: Scene already active, and allowSexTransitions is disabled")
    return
  else
    ; akSpeaker is already in an OStim thread
    ostimActors = OThread.GetActors(ActiveOstimThreadID)
    string newScene = getSceneByActionsOrTags(ostimActors, tags)
    MinaiUtil.Debug("Ostim scene transition to: " + newScene + " for OStim Thread [" + ActiveOstimThreadID + "].")
    Navigate(ActiveOstimThreadID, newScene)
  EndIf
EndFunction

; function AnimatedUndress(actor akSpeaker)
;     float handsDuration = 2.9
;     float headDuration = 2
;     float feetDuration = 6
;     float bottomDuration = 5
;     float bodyDuration = 3.433333

;     ;hands
;     If akSpeaker.GetWornForm(0x00000008)
;         GoToScene("OStim2PUndressGloves1MF", handsDuration, true)
;     EndIf

;     ;head
;     If akSpeaker.GetWornForm(0x00000001)
;         GoToScene("OStim2PUndressHead1MF", headDuration, true)
; 	EndIf

;     ;feet
;     If akSpeaker.GetWornForm(0x00000080)
;         GoToScene("OStim2PUndressBoots1MF", feetDuration, true)
; 	EndIf

;     ;bottom
;     If akSpeaker.GetWornForm(0x00000100)
;         GoToScene("OStim2PUndressBottom1MF", bottomDuration, true)
; 	EndIf

;     ;top
;     if akSpeaker.GetWornForm(0x00000004)
;         GoToScene("OStim2PUndressTop1MF", bodyDuration, true)
;     endif
; endfunction

Function GoToScene(int ThreadID, string SceneID, float await = 0.0, bool isWarp = true)
  ; if(interrupted)
  ;     return
  ; endif
  if(isWarp)
      OThread.WarpTo(ThreadID, SceneID)
  else
      OThread.NavigateTo(ThreadID, SceneID)
  endif
  if(await != 0)
      Utility.Wait(await)
  endif
EndFunction

string function getSceneByActionsOrTags(actor[] actors, string tags, bool useRandom = false)
  string newScene
  actors = OActorUtil.sort(actors, PapyrusUtil.ActorArray(1))
  if tags != ""
    MinaiUtil.Debug("Searching for OStim scene with Actions: " + tags)
    tags = ConvertTagsOstim(tags)
    newScene = OLibrary.GetRandomSceneWithAnyActionCSV(actors, tags)
    MinaiUtil.Debug("Ostim post Action search: " + newScene)
    if(newScene == "")
      MinaiUtil.Debug("Searching for OStim scene with Tags: " + tags)
      newScene = OLibrary.GetRandomSceneWithAnySceneTagCSV(actors, tags)
      MinaiUtil.Debug("Ostim post Tag search: " + newScene)
    endif
  else
    MinaiUtil.Debug("No OStim tags provided")
  endif

  if(newScene == "")
    if(useRandom)
      newScene = OLibrary.GetRandomScene(actors)
      MinaiUtil.Debug("Found random OStim scene: " + newScene)
    else
      MinaiUtil.Debug("No OStim scene found for: " + tags)
    endif
  else
    MinaiUtil.Debug("Found " + tags + " scene: " + newScene)
  endif

  return newScene
endfunction

int Function FindThread(actor[] actors)
  int threadID = -1
  int i = 0
  while(i < actors.Length)
    threadID = OActor.GetSceneID(actors[i])
    if(threadID != -1)
      return threadID
    endif
    i += 1
  endwhile

  return threadID
endfunction

int function AddActorsToActiveThread(int ThreadID, actor[] newActors)
  actor[] currentActors = OThread.GetActors(ThreadID)
  int i = 0
  while(i < newActors.Length)
    currentActors = PapyrusUtil.PushActor(currentActors, newActors[i])
    i += 1
  endwhile

  OThread.Stop(ThreadID)
  while(OThread.isRunning(ThreadID))
    Utility.Wait(0.2)
  endwhile

  StartOstim(currentActors)
endfunction

function Navigate(int ThreadID, string newScene)
  OThread.NavigateTo(ThreadID, newScene)
  if OThread.IsInAutoMode(ThreadID)
    OThread.StopAutoMode(ThreadID)
    Utility.Wait(5)
    OThread.StartAutoMode(ThreadID)
  EndIf
endfunction

function SpeedUp(actor akActor)
  int ThreadID = OActor.GetSceneID(akActor)
  string sceneId = OThread.GetScene(ThreadID)
  float maxSpeed = OMetadata.GetMaxSpeed(sceneId)
  int newOstimSpeed = OThread.GetSpeed(ThreadID) + 1
  if(newOstimSpeed <= maxSpeed)
    OThread.SetSpeed(ThreadID, newOstimSpeed)
  endif
endfunction

function SlowDown(actor akActor)
  int ThreadID = OActor.GetSceneID(akActor)
  int newOstimSpeed = OThread.GetSpeed(ThreadID) - 1
  if(newOstimSpeed >= 0)
    OThread.SetSpeed(ThreadID, newOstimSpeed)
  endif
endfunction

function StopThread(actor akSpeaker)
  int ActiveOstimThreadID = OActor.GetSceneID(akSpeaker)
  if (ActiveOstimThreadID != -1 && OThread.IsRunning(ActiveOstimThreadID))
    OThread.Stop(ActiveOstimThreadID)
  EndIf
endfunction

string function buildSceneFallbackDescription(int ThreadID, string eventType)
  string sceneId = OThread.GetScene(ThreadID)
  string[] actionTypes = OMetadata.GetActionTypes(sceneId)
  string[] sceneTags = OMetadata.GetSceneTags(sceneId)
  Actor[] actors = OThread.GetActors(ThreadID)
  string actorString = MinaiUtil.JoinActorArray(actors, ", ")
  string actionString = MinaiUtil.JoinStringArray(actionTypes, ", ")
  string sceneTagsString = MinaiUtil.JoinStringArray(sceneTags, ", ")

  return sexUtil.buildSceneString(sceneId, actorString, eventType, sceneTagsString, actionString)
endfunction

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

function setPrevSpeed(int ThreadID, int prevSpeed)
  JMap.setInt(jThreadsPrevSpeedsMap, ThreadID, prevSpeed)
endfunction

function resetPrevSpeed(int ThreadID)
  JMap.setInt(jThreadsPrevSpeedsMap, ThreadID, -1)
endfunction

int function getPrevSpeed(int ThreadID)
  return JMap.getInt(jThreadsPrevSpeedsMap, ThreadID)
endfunction

function removePrevSpeed(int ThreadID)
  JMap.removeKey(jThreadsPrevSpeedsMap, ThreadID)
endfunction
