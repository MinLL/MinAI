scriptname minai_Arousal extends Quest

BaboDialogueConfigMenu baboConfigs
slaUtilScr Aroused

Keyword SLA_HalfNakedBikini
Keyword SLA_ArmorHalfNaked
Keyword SLA_Brabikini
Keyword SLA_ThongT
Keyword SLA_ArmorSpendex
Keyword SLA_PantiesNormal
Keyword SLA_ThongLowLeg
Keyword SLA_ThongCString
Keyword SLA_KillerHeels
Keyword SLA_PantsNormal
Keyword SLA_MicroHotPants
Keyword SLA_ThongGstring
Keyword SLA_ArmorHarness
Keyword SLA_ArmorTransparent
Keyword SLA_ArmorLewdLeotard
Keyword SLA_PelvicCurtain
Keyword SLA_FullSkirt
Keyword SLA_MiniSkirt
Keyword SLA_MicroSkirt
Keyword SLA_BootsHeels
Keyword SLA_HasLeggings
Keyword SLA_ArmorRubber


bool bHasAroused = False
bool bHasArousedKeywords = False
bool bHasOSL = False
bool bHasBabo = False
int cuirassSlot = 0x00000004


minai_MainQuestController main
minai_AIFF aiff

actor playerRef

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  
  Main.Info("- Initializing Arousal Module.")
  if Game.GetModByName("SexlabAroused.esm") != 255
    Main.Info("Found Sexlab Aroused")
    bHasAroused = True
    Aroused = Game.GetFormFromFile(0x04290F, "SexlabAroused.esm") as slaUtilScr
    SLA_HalfNakedBikini = Game.GetFormFromFile(0x08E854, "SexlabAroused.esm") as Keyword
    SLA_ArmorHalfNaked = Game.GetFormFromFile(0x08E855, "SexlabAroused.esm") as Keyword
    SLA_Brabikini = Game.GetFormFromFile(0x0308E856, "SexlabAroused.esm") as Keyword
    SLA_ThongT = Game.GetFormFromFile(0x0308E857, "SexlabAroused.esm") as Keyword
    SLA_ArmorSpendex = Game.GetFormFromFile(0x0308E858, "SexlabAroused.esm") as Keyword
    SLA_PantiesNormal = Game.GetFormFromFile(0x0308EDC1, "SexlabAroused.esm") as Keyword
    SLA_ThongLowLeg = Game.GetFormFromFile(0x0308EDC2, "SexlabAroused.esm") as Keyword
    SLA_ThongCString = Game.GetFormFromFile(0x0308EDC3, "SexlabAroused.esm") as Keyword
    SLA_KillerHeels = Game.GetFormFromFile(0x0308F326, "SexlabAroused.esm") as Keyword
    SLA_PantsNormal = Game.GetFormFromFile(0x0308F3F3, "SexlabAroused.esm") as Keyword
    SLA_MicroHotPants = Game.GetFormFromFile(0x0308F3F4, "SexlabAroused.esm") as Keyword
    SLA_ThongGstring = Game.GetFormFromFile(0x0308F3F5, "SexlabAroused.esm") as Keyword
    SLA_ArmorHarness = Game.GetFormFromFile(0x0308F3FC, "SexlabAroused.esm") as Keyword
    SLA_ArmorTransparent = Game.GetFormFromFile(0x0308F3FD, "SexlabAroused.esm") as Keyword
    SLA_ArmorLewdLeotard = Game.GetFormFromFile(0x0308F401, "SexlabAroused.esm") as Keyword
    SLA_PelvicCurtain = Game.GetFormFromFile(0x0308F402, "SexlabAroused.esm") as Keyword
    SLA_FullSkirt = Game.GetFormFromFile(0x0308F40D, "SexlabAroused.esm") as Keyword
    SLA_MiniSkirt = Game.GetFormFromFile(0x0308F40E, "SexlabAroused.esm") as Keyword
    SLA_MicroSkirt = Game.GetFormFromFile(0x0308F40F, "SexlabAroused.esm") as Keyword
    SLA_BootsHeels = Game.GetFormFromFile(0x0308F410, "SexlabAroused.esm") as Keyword
    SLA_HasLeggings = Game.GetFormFromFile(0x0308FE9F, "SexlabAroused.esm") as Keyword
    SLA_ArmorRubber = Game.GetFormFromFile(0x0308FEA4, "SexlabAroused.esm") as Keyword
    ; Check a couple keywords to see if it's a stripped down SexlabAroused
    if SLA_HalfNakedBikini && SLA_ArmorHalfNaked
      bHasArousedKeywords = True
    EndIf
    Main.Info("Sexlab Aroused Keywords=" + bHasArousedKeywords)
  EndIf
  if Game.GetModByName("OSLAroused.esp") != 255
    Main.Info("Found OSL Aroused")
    bHasOSL = True
  EndIf
  if Game.GetModByName("BaboInteractiveDia.esp") != 255
    Main.Info("Found BaboDialogue")
    bHasBabo = True
    baboConfigs = (Game.GetFormFromFile(0x2FEA1B, "BaboInteractiveDia.esp") as BaboDialogueConfigMenu)
    if !baboConfigs
      bHasBabo = False
      Debug.Notification("Incompatible version of BaboDialogue. AI integrations disabled.")
      Main.Error("Could not fetch baboConfigs")
    EndIf
  EndIf
  aiff.SetModAvailable("Aroused", bHasAroused)
  aiff.SetModAvailable("ArousedKeywords", bHasArousedKeywords)
  aiff.SetModAvailable("OSL", bHasOSL)
  aiff.SetModAvailable("Babo", bHasBabo)
EndFunction





Function UpdateArousal(actor akTarget, int Arousal)
  if bHasOSL
    OSLArousedNative.ModifyArousal(akTarget, Arousal)
  elseIf bHasAroused
    Aroused.UpdateActorExposure(akTarget, Arousal)
  EndIf
EndFunction




int Function GetActorArousal(actor akActor)
  int exposure = 0
  if bHasOSL
    exposure = OSLArousedNative.GetArousal(akActor) as Int
  Else
   exposure = aroused.GetActorArousal(akActor)
  EndIf
  return exposure
EndFunction


Function WriteArousedString(bool bPlayerInScene, actor Player, actor[] actorsFromFormList)
	int numActors = actorsFromFormList.Length
	int i = 0
	while (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			String actorName = main.GetActorName(currentActor)
			int arousal = GetActorArousal(currentActor)
			if currentActor != Player
			  main.RegisterAction(actorName + "'s sexual arousal level is " + arousal + " on a scale of 0-100, where 0 is \"not horny\", and 100 is \"desperate for sex\". They should behave accordingly.")
			Else
			  main.RegisterAction(actorName + "'s sexual arousal level is " + arousal + " on a scale of 0-100, where 0 is \"not horny\", and 100 is \"desperate for sex\".")
			EndIf
		EndIf
		i += 1
	EndWhile
	if bPlayerInScene
	  String actorName = main.GetActorName(player)
	  int exposure = GetActorArousal(player)
	  if player.getActorBase().getSex() == 0 ; Male
            If exposure >= 99
              main.RegisterAction(actorName + " appears to have a raging erection that is difficult to hide. " + actorName + " appears to be absolutely desperate for sex.")
            ElseIf exposure >= 85
              main.RegisterAction(actorName + " appears to have a raging erection that is difficult to hide.")
            ElseIf exposure >= 70
              main.RegisterAction(actorName + " appears to be aroused, and has flushed cheeks. ")
            Elseif exposure >= 50
              main.RegisterAction(actorName + " appears to be mildly turned on, and is blushing slightly.")
            EndIf
          else ; Female, or other
            If exposure >= 99
              main.RegisterAction(actorName + " appears to be extremely aroused, and looks to be absolutely desperate for sex. She has heavy breathing, pointy nipples, and flushed cheeks.")
            ElseIf exposure >= 85
              main.RegisterAction(actorName + " appears to be very aroused, with pointy nipples and heavy breathing.")
            ElseIf exposure >= 70
              main.RegisterAction(actorName + " appears to be aroused, and has flushed cheeks.")
            Elseif exposure >= 50
              main.RegisterAction(actorName + " appears to be mildly turned on, and is blushing slightly.")
            EndIf
          EndIf
	EndIf
EndFunction


function WriteClothingString(actor akActor, actor player, bool isYou=false, actor[] actorsFromFormList)
	int numActors = actorsFromFormList.Length
	int i = 0
	While (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			String actorName = main.GetActorName(currentActor)
			Armor cuirass = currentActor.GetWornForm(cuirassSlot) as Armor
			if !cuirass
				main.RegisterAction(actorName + " is naked.")
			else
				main.RegisterAction(actorName + " is wearing " + cuirass.GetName())
			EndIf
			if !bHasArousedKeywords
				return
			endif
			if currentActor.WornHasKeyword(SLA_HalfNakedBikini)
			  main.RegisterAction(actorName + " is wearing a set of revealing bikini armor.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorHalfNaked)
			  main.RegisterAction(actorName + " is wearing very revealing attire, leaving them half naked.")
			EndIf
			if currentActor.WornHasKeyword(SLA_Brabikini)
			  main.RegisterAction(actorName + " is wearing a bra underneath her other equipment.")
			EndIf
			  if currentActor.WornHasKeyword(SLA_ThongT) || currentActor.WornHasKeyword(SLA_ThongLowLeg) || currentActor.WornHasKeyword(SLA_ThongCString) || currentActor.WornHasKeyword(SLA_ThongGstring)
			  main.RegisterAction(actorName + " is wearing a thong underneath her other equipment.")
			EndIf
			if currentActor.WornHasKeyword(SLA_PantiesNormal)
			  main.RegisterAction(actorName + " is wearing plain panties underneath her other equipment.")
			EndIf
			if currentActor.WornHasKeyword(SLA_KillerHeels) || currentActor.WornHasKeyword(SLA_BootsHeels)
			  main.RegisterAction(actorName + " is wearing a set of high-heels.")
			EndIf
			if currentActor.WornHasKeyword(SLA_PantsNormal)
			  main.RegisterAction(actorName + " is wearing a set of ordinary pants.")
			EndIf
			if currentActor.WornHasKeyword(SLA_MicroHotPants)
			  main.RegisterAction(actorName + " is wearing a set of short hot-pants that accentuate her ass.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorHarness)
			  main.RegisterAction(actorName + " is wearing a form-fitting body harness.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorSpendex)
			  main.RegisterAction(actorName + "'s outfit is made out of latex (Referred to as Ebonite).")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorTransparent)
			  main.RegisterAction(actorName + "'s outfit is transparent, leaving nothing to the imagination.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorLewdLeotard)
			  main.RegisterAction(actorName + " is wearing a sheer, revealing leotard leaving very little to the imagination.")
			EndIf
			if currentActor.WornHasKeyword(SLA_PelvicCurtain)
			  main.RegisterAction(actorName + "'s pussy is covered only by a sheer curtain of fabric.")
			EndIf
			if currentActor.WornHasKeyword(SLA_FullSkirt)
			  main.RegisterAction(actorName + " is wearing a full length skirt that goes down to her knees.")
			EndIf
			if currentActor.WornHasKeyword(SLA_MiniSkirt) || currentActor.WornHasKeyword(SLA_MicroSkirt)
			  main.RegisterAction(actorName + " is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorRubber)
			  main.RegisterAction(actorName + "'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).")
			EndIf
		EndIf
		i += 1
	EndWhile
EndFunction



Function WritePlayerAppearance(Actor player)
  ;; Appearance
  string actorRace = (player.GetActorBase().GetRace() as Form).GetName()
  int cotrIndex = StringUtil.Find(actorRace, " DZ")
  if cotrIndex != -1
    actorRace = StringUtil.Substring(actorRace, 0, cotrIndex)
  endif
  string gender = ""
  int sexInt = player.GetActorBase().GetSex()
  if sexInt == 0
    gender = "male"
  elseif sexInt == 1
    gender = "female"
  else
    gender = "transgender"
  endif
  if bHasBabo
    String appearance = ""
    string breasts = ""
    string butt = ""
    int beautyInt = baboConfigs.BeautyValue.GetValueInt()
    if beautyInt < 20
      appearance = "rather ugly"
    elseif beautyInt < 40
      appearance = "below average (In appearance)"
    elseif beautyInt < 60
      appearance = "average (In appearance)"
    elseif beautyInt < 80
      appearance = "rather attractive"
    else
      appearance = "absolutely gorgeous"
    endif
    
    int breastsInt = baboConfigs.BreastsValue.GetValueInt()
    if breastsInt < 20
      breasts = "flat breasts"
    elseif breastsInt < 40
      breasts = "small breasts"
    elseif breastsInt < 60
      breasts = "average breasts"
    elseif breastsInt < 80
      breasts = "large boobs"
    else
      breasts = "enormous tits"
    endif
    int buttInt = baboConfigs.ButtocksValue.GetValueInt()
    if buttInt < 20
      butt = "flat ass"
    elseif buttInt < 40
      butt = "small muscular ass"
    elseif buttInt < 60
      butt = "average typical ass"
    elseif buttInt < 80
      butt = "large thick ass"
    else
      butt = "enormous beautiful ass"
    endif
    string appearanceStr = main.GetActorName(player) + " is an " + appearance + " " + actorRace + " " + gender + " with " + breasts + " and a " + butt + "."
    Main.Info("Set player description (Babo): " + appearanceStr)
    main.RegisterAction(appearanceStr)
  else
    string appearanceStr = Player.GetActorBase().GetName() + " is a " + gender + " " + actorRace + "." 
    Main.Info("Set player description: " + appearanceStr)
    main.RegisterAction(appearanceStr)
  EndIf
EndFunction


Function UpdateEvents(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList, bool bPlayerInScene, string targetName, string speakerName, string playerName)
  if bPlayerInScene
    WritePlayerAppearance(playerRef)
  EndIf

  if bHasAroused
    WriteArousedString(bPlayerInScene, playerRef, actorsFromFormList)
    WriteClothingString(actorSpeaking, playerRef, True, actorsFromFormList)
  EndIf

EndFunction

Function ActionResponse(actor akTarget, actor akSpeaker, string sayLine, actor[] actorsFromFormList)
    If stringutil.Find(sayLine, "-thats hot-") != -1
      UpdateArousal(akSpeaker, 6)
      Debug.Notification(akSpeaker.GetActorBase().GetName() + " is getting more turned on.")
    EndIf
    If stringutil.Find(sayLine, "-eww-") != -1
      UpdateArousal(akSpeaker, -12)
      Debug.Notification(akSpeaker.GetActorBase().GetName() + " is getting less turned on.")
    EndIf
EndFunction




Event CommandDispatcher(String speakerName,String  command, String parameter)
  Actor akSpeaker=AIAgentFunctions.getAgentByName(speakerName)
  actor akTarget= AIAgentFunctions.getAgentByName(parameter)
  if !akTarget
    akTarget = PlayerRef
  EndIf
  string targetName = main.GetActorName(akTarget)
  if command == "ExtCmdIncreaseArousal"
    UpdateArousal(akSpeaker, 6)
    Debug.Notification(akSpeaker.GetActorBase().GetName() + " is getting more turned on.")
    AIAgentFunctions.logMessageForActor("command@ExtCmdIncreaseArousal@@"+speakerName+"'s arousal level increased.","funcret",speakerName)
  EndIf
  if command == "ExtCmdDecreaseArousal"
    UpdateArousal(akSpeaker, -12)
    Debug.Notification(akSpeaker.GetActorBase().GetName() + " is getting less turned on.")
    AIAgentFunctions.logMessageForActor("command@ExtCmdDecreaseArousal@@"+speakerName+"'s arousal level decreased.","funcret",speakerName)
  EndIf
EndEvent


Function SetContext(actor akTarget)
  if !aiff
    return
  EndIf
  String actorName = main.GetActorName(akTarget)
  Armor cuirass = akTarget.GetWornForm(cuirassSlot) as Armor
  aiff.SetActorVariable(akTarget, "isnaked", !cuirass)
  aiff.SetActorVariable(akTarget, "arousal", GetActorArousal(akTarget))
  
  if !bHasArousedKeywords
  	return
  endif
  aiff.SetActorVariable(akTarget, "beautyScore", baboConfigs.BeautyValue.GetValueInt())
  aiff.SetActorVariable(akTarget, "breastsScore", baboConfigs.BreastsValue.GetValueInt())
  aiff.SetActorVariable(akTarget, "buttScore", baboConfigs.ButtocksValue.GetValueInt())
  string gender = "male";
  if akTarget.GetActorBase().GetSex() != 0
    gender = "female"
  endif
  aiff.SetActorVariable(akTarget, "gender", gender)
  string actorRace = (playerRef.GetActorBase().GetRace() as Form).GetName()
  int cotrIndex = StringUtil.Find(actorRace, " DZ")
  if cotrIndex != -1
    actorRace = StringUtil.Substring(actorRace, 0, cotrIndex)
  endif
  aiff.SetActorVariable(akTarget, "race", actorRace)
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  string ret = ""
  if bHasArousedKeywords
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_HalfNakedBikini", SLA_HalfNakedBikini)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_ArmorHalfNaked", SLA_ArmorHalfNaked)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Brabikini", SLA_Brabikini)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Thong", SLA_ThongT)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Thong", SLA_ThongLowLeg)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Thong", SLA_ThongCString)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Thong", SLA_ThongGstring)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PantiesNormal", SLA_PantiesNormal)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Heels", SLA_KillerHeels)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_Heels", SLA_BootsHeels)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PantsNormal", SLA_PantsNormal)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_MicroHotPants", SLA_MicroHotPants)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_ArmorHarness", SLA_ArmorHarness)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_ArmorSpendex", SLA_ArmorSpendex)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_ArmorTransparent", SLA_ArmorTransparent)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_ArmorLewdLeotard", SLA_ArmorLewdLeotard)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PelvicCurtain", SLA_PelvicCurtain)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_FullSkirt", SLA_FullSkirt)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_MiniSkirt", SLA_MiniSkirt)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_MiniSkirt", SLA_MicroSkirt)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_ArmorRubber", SLA_ArmorRubber)
  EndIf
  return ret
EndFunction

string Function GetFactionsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction

