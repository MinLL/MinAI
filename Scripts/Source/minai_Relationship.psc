scriptname minai_Relationship extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Util MinaiUtil

actor playerRef


function Maintenance(minai_MainQuestController _main)
  main = _main
  MinaiUtil = (self as Quest) as minai_Util
  MinaiUtil.Info("Initializing Relationship minAI Module.")
  playerRef = Game.GetPlayer()
  aiff = (Self as Quest) as minai_AIFF
 
EndFunction


Function UpdateEventsForMantella(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList)
  MinaiUtil.Log("UpdateEventsForMantella - Relationship","INFO")
  if !aiff
    MinaiUtil.Log("No AIFF in minai_Relationship","INFO")
    return
  EndIf

	int numActors = actorsFromFormList.Length
	int i = 0
	While (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			string msg = GetStringForActor(currentActor)
      main.RegisterEvent(msg, "info_context")
    Endif
		i += 1
	EndWhile
EndFunction

; for CHIM, use tags to pass to the PHP where lists of characters are built 
; ie tom dick and harry smell like roses
Function SetContext(actor akActor)
  Main.Debug("SetContext Relationship(" + main.GetActorName(akActor) + ")")
  int msg = akActor.GetRelationshipRank(playerRef)
  aiff.SetActorVariable(akActor, "relationshipRank", msg) 
EndFunction

; for mantella, the whole string
string Function GetStringForActor(actor currentActor)
  String actorName = main.GetActorName(currentActor)
  String playerName = main.GetActorName(playerRef)
  int rrank = currentActor.GetRelationshipRank(playerRef)
  string msg = ""
  If (rrank == -4)
    msg = playerName + " is an archnemesis of " + actorName + ". "
  ElseIf (rrank == -3)
    msg = playerName + " is an enemy of " + actorName + ". "
  ElseIf (rrank == -2)
    msg = playerName + " is a foe of " + actorName + ". "
  ElseIf (rrank == -1)
    msg = playerName + " is a rival of " + actorName + ". "
  ElseIf (rrank == -0)
    msg = playerName + " is an acquaintance of " + actorName + ". "
  ElseIf (rrank == 1)
    msg = playerName + " is a friend of " + actorName + ". "
  ElseIf (rrank == 2)
    msg = playerName + " is a confidant of " + actorName + ". "
  ElseIf (rrank == 3)
    msg = playerName + " is an ally of " + actorName + ". "
  ElseIf (rrank == 4)
    msg = playerName + " is a lover of " + actorName + ". "
  EndIf
  return msg
EndFunction