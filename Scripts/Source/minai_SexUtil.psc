scriptname minai_SexUtil extends Quest

Actor Property PlayerRef auto

minai_Config config
SexLabFramework slf
minai_Util MinaiUtil
minai_SexSexlab sexlab

bool bHasTNG
Keyword TNG_Gentlewoman

function Maintenance(minai_Config localConfig, SexLabFramework localSlf)
  slf = localSlf
  config = localConfig
  MinaiUtil = (self as Quest) as minai_Util
  sexlab = (self as Quest) as minai_SexSexlab
  bHasTNG = Game.GetModByName("TheNewGentleman.esp") != 255
  if bHasTNG
    TNG_Gentlewoman = Game.GetFormFromFile(0xFF8, "TheNewGentleman.esp") as Keyword
  endif
endfunction

int Function GetGender(Actor akActor)
  if !akActor
    return -1 ; Invalid actor
  endif
  if bHasTNG
    if akActor.GetActorBase().GetSex() != 0 && !akActor.HasKeyword(TNG_Gentlewoman)
      return 1 ; Female
    else
      return 0 ; Male
    endif
  elseif slf
    return slf.GetGender(akActor)
  endif
  return akActor.GetLeveledActorBase().GetSex()
EndFunction

int function getPlayerThread(string framework)
  if(framework == "ostim")
    if(OThread.isRunning(0))
      return 0
    else
      return -1
    endif
  elseif(slf != none)
    return slf.FindPlayerController()
  endif
  return -1
endfunction

bool function isPlayerInvolved(int ThreadID, string framework)
  int playerThread = getPlayerThread(framework)

  return ThreadID == playerThread
endfunction

String Function GetActorNameForSex(actor akActor)
  ; Prefer base name, fall back to display name, and then other.
  string ret = MinaiUtil.GetActorName(akActor)
  if ret
    return ret
  EndIf
  return "a monster"
EndFunction

; if it's player only scene it will return none
; assume that actors are only actors from scene
actor Function GetWeightedRandomActorToSpeak(actor[] actors, bool bHasOstim = false)
  actor[] maleActors = PapyrusUtil.ActorArray(0)
  actor[] femaleActors = PapyrusUtil.ActorArray(0)
  int count = actors.Length
  if !actors || count == 0
    return None
  endif
  while count > 0
    count -= 1
    actor currActor = actors[count]
    int currSex = GetGender(currActor)
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
    return MinaiUtil.getRandomActor(maleActors)
  endif

  ; if no male npc, just pick random female. Can be none though if it's player only scene
  if(maleActors.length == 0)
    return MinaiUtil.getRandomActor(femaleActors)
  endif

  ; pick weighted type of npc who will talk
  bool isFemale = Utility.RandomInt(1, 100) <= config.genderWeightComments

  if(isFemale)
    return MinaiUtil.getRandomActor(femaleActors)
  else
    return MinaiUtil.getRandomActor(maleActors)
  endif
EndFunction

bool Function CanAnimate(actor akTarget, bool useOstim = false)
  if !akTarget
    return False
  endif
  if (akTarget.IsOnMount())
    return False
  EndIf
  if (useOstim)
    return True
  EndIf
  if slf && (!slf.IsActorActive(akTarget))
    return True
  EndIf
  return False
EndFunction

string function buildSceneString(string sceneId, string actorString, string eventType, string sceneTagsString, string actionString)
  string result = sceneId+"."
  if(sceneTagsString != "")
    result += " Scene can be described with this tags: " + sceneTagsString + "."
  endif
  if(actionString != "")
    result += " These actions happen in a scene: " + actionString
  endif

  if(eventType == "startthread")
    return actorString + " begin sex scene: " + result
  elseif(eventType == "scenechange")
    return actorString + " changed the scene to " + result
  endif
  return result
endfunction

actor[] function BuildGroup(int size, int jMalesArr, int jFemalesArr)
  MinaiUtil.Debug("BuildGroup: Attempt to build group of "+size+" people")
  Actor[] group
  if(size == 2)
    group = new Actor[2]
  elseif(size == 3)
    group = new Actor[3]
  elseif(size == 4)
    group = new Actor[4]
  elseif(size == 5)
    group = new Actor[5]
  endif
  ; both genders available. Set one of each and after random
  if (JArray.count(jMalesArr) > 0 && JArray.count(jFemalesArr) > 0) 
    MinaiUtil.Debug("BuildGroup: Attempt to build group with both genders")
    actor male = UnshiftActor(jMalesArr)
    actor female = UnshiftActor(jFemalesArr)
    group[0] = male
    group[1] = female
    int i = 2
    while(i < size && 0 < JArray.count(jMalesArr) + JArray.count(jFemalesArr))
      if(JArray.count(jMalesArr) == JArray.count(jFemalesArr))
        if (Utility.RandomInt(0, 1) == 0) 
          group[i] = UnshiftActor(jMalesArr)
        else
          group[i] = UnshiftActor(jFemalesArr)
        endIf
      elseif(JArray.count(jMalesArr) > JArray.count(jFemalesArr))
        group[i] = UnshiftActor(jMalesArr)
      else
        group[i] = UnshiftActor(jFemalesArr)
      endif
      i += 1
    endwhile
    MinaiUtil.Debug("BuildGroup: Succesfully built group of size: "+group.length+" with both genders")
    return group
  endif

  int jActorsArr = 0
  
  ; just take a part of actors from either arrays since there is no other genders left
  if (JArray.count(jMalesArr) > 0) 
    MinaiUtil.Debug("BuildGroup: Attempting to build male only group of size: "+group.length)
    jActorsArr = jMalesArr
  elseif (JArray.count(jFemalesArr) > 0)
    MinaiUtil.Debug("BuildGroup: Attempting to build female only group of size: "+group.length)
    jActorsArr = jFemalesArr
  endif

  int j = 0 
  while(j < size && j < JArray.count(jActorsArr))
    group[j] = UnshiftActor(jActorsArr)
    j += 1
  endwhile

  MinaiUtil.Debug("BuildGroup: Succesfully built single gender group of size: "+group.length)
  return group
endfunction

; Check if user has animations for selected group of actors
bool function CheckGroup(actor[] actors, string framework)
  if(!actors || actors.length < 1)
    return false
  endif
  if(framework == "ostim")
    actors = OActorUtil.Sort(actors, PapyrusUtil.ActorArray(1))
    string sceneId = OLibrary.GetRandomSceneSuperloadCSV(actors, AnyActionType = "sexual")
    Utility.wait(0.2)
    MinaiUtil.Debug("CheckGroup: OStim scene - "+sceneId)
    return sceneId != ""
  else
    return sexlab.FindSexlabAnimations(actors, "").Length != 0
  endif
endfunction

; similar to js unshift function
; picks first item and returns it with removing it from original array
actor function UnshiftActor(int jActorsArr)
  if JArray.count(jActorsArr) == 0
    MinaiUtil.Debug("UnshiftActor: No actors available")
    return None
  endif
  actor currActor = JArray.getForm(jActorsArr, 0) as actor
  JArray.eraseForm(jActorsArr, currActor)
  MinaiUtil.Debug("UnshiftActor: "+currActor.GetDisplayName())
  return currActor
endfunction

; count males actors
; to count females take original actors array minus males count
int function countMales(actor[] actors)
  if !actors
    return 0
  endif
  int i = 0
  int numMales = 0
  while i < actors.Length
    if GetGender(actors[i]) == 0
      numMales += 1
    EndIf
    i += 1
  Endwhile
  return numMales
endfunction
