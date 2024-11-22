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
Keyword EroticArmor
Keyword SLA_PiercingVulva
Keyword SLA_PiercingBelly
Keyword SLA_PiercingNipple
Keyword SLA_PiercingClit
Keyword TNG_XS
Keyword TNG_S
Keyword TNG_M
Keyword TNG_L
Keyword TNG_XL
Keyword TNG_DefaultSize
Keyword TNG_Gentlewoman
Keyword TNG_Revealing


bool bHasAroused = False
bool bHasArousedKeywords = False
bool bHasBabo = False
bool bHasTNG = False
int cuirassSlot = 0x00000004


minai_MainQuestController main
minai_AIFF aiff

actor playerRef

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  
  Main.Info("Initializing Arousal Module.")
  if Game.GetModByName("SexlabAroused.esm") != 255
    Main.Info("Found Sexlab Aroused")
    bHasAroused = True
    Aroused = Game.GetFormFromFile(0x04290F, "SexlabAroused.esm") as slaUtilScr
    SLA_HalfNakedBikini = Game.GetFormFromFile(0x08E854, "SexlabAroused.esm") as Keyword
    SLA_ArmorHalfNaked = Game.GetFormFromFile(0x08E855, "SexlabAroused.esm") as Keyword
    SLA_Brabikini = Game.GetFormFromFile(0x08E856, "SexlabAroused.esm") as Keyword
    SLA_ThongT = Game.GetFormFromFile(0x08E857, "SexlabAroused.esm") as Keyword
    SLA_ArmorSpendex = Game.GetFormFromFile(0x08E858, "SexlabAroused.esm") as Keyword
    SLA_PantiesNormal = Game.GetFormFromFile(0x08EDC1, "SexlabAroused.esm") as Keyword
    SLA_ThongLowLeg = Game.GetFormFromFile(0x08EDC2, "SexlabAroused.esm") as Keyword
    SLA_ThongCString = Game.GetFormFromFile(0x08EDC3, "SexlabAroused.esm") as Keyword
    SLA_KillerHeels = Game.GetFormFromFile(0x08F326, "SexlabAroused.esm") as Keyword
    SLA_PantsNormal = Game.GetFormFromFile(0x08F3F3, "SexlabAroused.esm") as Keyword
    SLA_MicroHotPants = Game.GetFormFromFile(0x08F3F4, "SexlabAroused.esm") as Keyword
    SLA_ThongGstring = Game.GetFormFromFile(0x08F3F5, "SexlabAroused.esm") as Keyword
    SLA_ArmorHarness = Game.GetFormFromFile(0x08F3FC, "SexlabAroused.esm") as Keyword
    SLA_ArmorTransparent = Game.GetFormFromFile(0x08F3FD, "SexlabAroused.esm") as Keyword
    SLA_ArmorLewdLeotard = Game.GetFormFromFile(0x08F401, "SexlabAroused.esm") as Keyword
    SLA_PelvicCurtain = Game.GetFormFromFile(0x08F402, "SexlabAroused.esm") as Keyword
    SLA_FullSkirt = Game.GetFormFromFile(0x08F40D, "SexlabAroused.esm") as Keyword
    SLA_MiniSkirt = Game.GetFormFromFile(0x08F40E, "SexlabAroused.esm") as Keyword
    SLA_MicroSkirt = Game.GetFormFromFile(0x08F40F, "SexlabAroused.esm") as Keyword
    SLA_BootsHeels = Game.GetFormFromFile(0x08F410, "SexlabAroused.esm") as Keyword
    SLA_HasLeggings = Game.GetFormFromFile(0x08FE9F, "SexlabAroused.esm") as Keyword
    SLA_ArmorRubber = Game.GetFormFromFile(0x08FEA4, "SexlabAroused.esm") as Keyword
    EroticArmor = Game.GetFormFromFile(0x08C7F6, "SexlabAroused.esm") as Keyword
    SLA_PiercingVulva = Game.GetFormFromFile(0x08F3F6, "SexlabAroused.esm") as Keyword
    SLA_PiercingBelly = Game.GetFormFromFile(0x088F3F7, "SexlabAroused.esm") as Keyword
    SLA_PiercingNipple = Game.GetFormFromFile(0x08F3F8, "SexlabAroused.esm") as Keyword
    SLA_PiercingClit = Game.GetFormFromFile(0x08F40B, "SexlabAroused.esm") as Keyword
    
    ; Check a couple keywords to see if it's a stripped down SexlabAroused
    if SLA_HalfNakedBikini && SLA_ArmorHalfNaked
      bHasArousedKeywords = True
    EndIf
    Main.Info("Sexlab Aroused Keywords=" + bHasArousedKeywords)
  EndIf

  if Game.GetModByName("TheNewGentleman.esp") != 255
    bHasTNG = True
	Main.Info("Found TNG")
	TNG_XS = Game.GetFormFromFile(0x03BFE1, "TheNewGentleman.esp") as Keyword
	TNG_S = Game.GetFormFromFile(0x03BFE2, "TheNewGentleman.esp") as Keyword
	TNG_M = Game.GetFormFromFile(0x03BFE3, "TheNewGentleman.esp") as Keyword
	TNG_L = Game.GetFormFromFile(0x03BFE4, "TheNewGentleman.esp") as Keyword
	TNG_XL = Game.GetFormFromFile(0x03BFE5, "TheNewGentleman.esp") as Keyword
	TNG_DefaultSize = Game.GetFormFromFile(0x03BFE0, "TheNewGentleman.esp") as Keyword
	TNG_Gentlewoman = Game.GetFormFromFile(0x03BFF8, "TheNewGentleman.esp") as Keyword
	TNG_Revealing = Game.GetFormFromFile(0x03BFFF, "TheNewGentleman.esp") as Keyword
	if TNG_XS != None && TNG_S != None && TNG_M != None && TNG_L != None && TNG_XL != None && TNG_DefaultSize != None && TNG_Gentlewoman != None && TNG_Revealing != None
		Main.Debug("TNG size keywords retrieved successfully.")
	else
		Main.Error("Failed to retrieve one or more TNG size keywords.")
	EndIf
  EndIf

  if Game.GetModByName("BaboInteractiveDia.esp") != 255
    Main.Info("Found BaboDialogue")
    bHasBabo = True
    baboConfigs = (Game.GetFormFromFile(0x2FEA1B, "BaboInteractiveDia.esp") as BaboDialogueConfigMenu)
    if !baboConfigs
      bHasBabo = False
      Main.Error("Could not fetch baboConfigs")
    EndIf
  EndIf
  aiff.SetModAvailable("Aroused", bHasAroused)
  aiff.SetModAvailable("ArousedKeywords", bHasArousedKeywords)
  aiff.SetModAvailable("Babo", bHasBabo)
  aiff.SetModAvailable("TNG", bHasTNG)
  aiff.RegisterAction("ExtCmdIncreaseArousal", "IncreaseArousal", "AI Arousal Increase", "Arousal", 1, 0, 2, 5, 60, (bHasAroused))
  aiff.RegisterAction("ExtCmdDecreaseArousal", "DecreaseArousal", "AI Arousal Decrease", "Arousal", 1, 0, 2, 5, 60, (bHasAroused))
EndFunction


Function UpdateArousal(actor akTarget, int Arousal)
  If bHasAroused
    Aroused.UpdateActorExposure(akTarget, Arousal)
  EndIf
EndFunction


int Function GetActorArousal(actor akActor)
  int exposure = 0
  if bHasAroused
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
      main.RegisterAction(actorName + "'s sexual arousal level is " + arousal + "%.")
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
			if cuirass == None
				main.RegisterAction(actorName + " is naked.")
			else
				main.RegisterAction(actorName + " is wearing " + cuirass.GetName())
			EndIf
			if bHasTNG
				bool exposed = IsTNGExposed(currentActor)
				if exposed && ((currentActor.GetActorBase().GetSex() == 0) || currentActor.HasKeyword(TNG_Gentlewoman))
					main.RegisterAction(actorName + "'s genitals are exposed.")
          string sizeDescription = ""
          Main.Debug("TNG Dick Check on "+ actorName)
          ; Check for auto-assigned first
          if currentActor.HasKeywordString("TNG_ActorAddnAuto:05")
            sizeDescription = "one of the biggest cocks you've ever seen"
          elseif currentActor.HasKeywordString("TNG_ActorAddnAuto:04")
            sizeDescription = "a large cock"
          elseif currentActor.HasKeywordString("TNG_ActorAddnAuto:03")
            sizeDescription = "an average sized cock"
          elseif currentActor.HasKeywordString("TNG_ActorAddnAuto:02")
            sizeDescription = "a very small cock"
          elseif currentActor.HasKeywordString("TNG_ActorAddnAuto:01")
            sizeDescription = "an embarrassingly tiny prick"
          EndIf
          if sizeDescription == ""
            Main.Debug("TNG_ActorAddnAuto:0x not found on " + actorName + ", checking manual size assignment.")
          EndIf
          ; Supersede if manually set
          if currentActor.HasKeyword(TNG_XL)
            sizeDescription = "one of the biggest cocks you've ever seen"
          elseif currentActor.HasKeyword(TNG_L)
            sizeDescription = "a large cock"
          elseif currentActor.HasKeyword(TNG_M) || currentActor.HasKeyword(TNG_DefaultSize)
            sizeDescription = "an average sized cock"
          elseif currentActor.HasKeyword(TNG_S)
            sizeDescription = "a very small cock"
          elseif currentActor.HasKeyword(TNG_XS)
            sizeDescription = "an embarrassingly tiny prick"
          EndIf
          if sizeDescription != ""
            main.RegisterAction("You can see that " + actorName + " has " + sizeDescription + ".")
          else
            Main.Info("TNG Dick Check Failed on " + actorName)
          EndIf
				EndIf
			EndIf
			if !bHasArousedKeywords
				return
			EndIf
			if SLA_HalfNakedBikini != None
        if currentActor.WornHasKeyword(SLA_HalfNakedBikini)
          main.RegisterAction(actorName + " is wearing a set of revealing bikini armor.")
        EndIf
      EndIf
      if SLA_ArmorHalfNaked != None
        if currentActor.WornHasKeyword(SLA_ArmorHalfNaked)
          main.RegisterAction(actorName + " is wearing very revealing attire, leaving them half naked.")
        EndIf
      EndIf
      if SLA_Brabikini != None
        if currentActor.WornHasKeyword(SLA_Brabikini)
          main.RegisterAction(actorName + " is wearing a bra underneath her other equipment.")
        EndIf
      EndIf
      if SLA_ThongT != None
        if currentActor.WornHasKeyword(SLA_ThongT)
          main.RegisterAction(actorName + " is wearing a thong underneath her other equipment.")
        EndIf
      EndIf
      if SLA_ThongLowLeg != None
        if currentActor.WornHasKeyword(SLA_ThongLowLeg)
          main.RegisterAction(actorName + " is wearing a thong underneath her other equipment.")
        EndIf
      EndIf
      if SLA_ThongCString != None
        if currentActor.WornHasKeyword(SLA_ThongCString)
          main.RegisterAction(actorName + " is wearing a thong underneath her other equipment.")
        EndIf
      EndIf
      if SLA_ThongGstring != None
        if currentActor.WornHasKeyword(SLA_ThongGstring)
          main.RegisterAction(actorName + " is wearing a thong underneath her other equipment.")
        EndIf
      EndIf
      if SLA_PantiesNormal != None
        if currentActor.WornHasKeyword(SLA_PantiesNormal)
          main.RegisterAction(actorName + " is wearing plain panties underneath her other equipment.")
        EndIf
      EndIf
			if SLA_KillerHeels != None
        if currentActor.WornHasKeyword(SLA_KillerHeels)
          main.RegisterAction(actorName + " is wearing a set of high-heels.")
        EndIf
      EndIf
      if SLA_BootsHeels != None
        if currentActor.WornHasKeyword(SLA_BootsHeels)
          main.RegisterAction(actorName + " is wearing a set of high-heels.")
        EndIf
      EndIf
      if SLA_PantsNormal
        if currentActor.WornHasKeyword(SLA_PantsNormal)
          main.RegisterAction(actorName + " is wearing a set of ordinary pants.")
        EndIf
      EndIf
			if SLA_MicroHotPants != None
        if currentActor.WornHasKeyword(SLA_MicroHotPants)
          main.RegisterAction(actorName + " is wearing a set of short hot-pants that accentuate her ass.")
        EndIf
      EndIf
      if SLA_ArmorHarness != None
        if currentActor.WornHasKeyword(SLA_ArmorHarness)
          main.RegisterAction(actorName + " is wearing a form-fitting body harness.")
        EndIf
      EndIf
      if SLA_ArmorSpendex != None
        if currentActor.WornHasKeyword(SLA_ArmorSpendex)
          main.RegisterAction(actorName + "'s outfit is made out of latex (Referred to as Ebonite).")
        EndIf
      EndIf
			if SLA_ArmorTransparent != None
        if currentActor.WornHasKeyword(SLA_ArmorTransparent)
          main.RegisterAction(actorName + "'s outfit is transparent, leaving nothing to the imagination.")
        EndIf
      EndIf
			if SLA_ArmorLewdLeotard != None
        if currentActor.WornHasKeyword(SLA_ArmorLewdLeotard)
          main.RegisterAction(actorName + " is wearing a sheer, revealing leotard leaving very little to the imagination.")
        EndIf
      EndIf
      if SLA_PelvicCurtain != None
        if currentActor.WornHasKeyword(SLA_PelvicCurtain)
          main.RegisterAction(actorName + "'s pussy is covered only by a sheer curtain of fabric.")
        EndIf
      EndIf
			if SLA_FullSkirt != None
        if currentActor.WornHasKeyword(SLA_FullSkirt)
          main.RegisterAction(actorName + " is wearing a full length skirt that goes down to her knees.")
        EndIf
			EndIf
      if SLA_MiniSkirt != None
        if currentActor.WornHasKeyword(SLA_MiniSkirt)
          main.RegisterAction(actorName + " is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.")
        EndIf
      EndIf
      if SLA_MicroSkirt != None
        if currentActor.WornHasKeyword(SLA_MicroSkirt)
          main.RegisterAction(actorName + " is wearing a micro mini-skirt exposes her ass. Her underwear or panties are visible underneath when she moves.")
        EndIf
      EndIf
      if SLA_ArmorRubber != None
        if currentActor.WornHasKeyword(SLA_ArmorRubber)
          main.RegisterAction(actorName + "'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).")
        EndIf
      EndIf
      if EroticArmor != None
        if currentActor.WornHasKeyword(EroticArmor)
          main.RegisterAction(actorName + "'s outfit is sexy and revealing.")
        EndIf
      EndIf
			if SLA_PiercingNipple != None
        if currentActor.WornHasKeyword(SLA_PiercingNipple)
          main.RegisterAction(actorName + " has nipple piercings.")
        EndIf
      EndIf
      if SLA_PiercingBelly != None
        if currentActor.WornHasKeyword(SLA_PiercingBelly)
          main.RegisterAction(actorName + " has a navel piercing.")
        EndIf
      EndIf
			if SLA_PiercingVulva != None
        if currentActor.WornHasKeyword(SLA_PiercingVulva)
          main.RegisterAction(actorName + " has labia piercings.")
        EndIf
      EndIf
      if SLA_PiercingClit != None
        if currentActor.WornHasKeyword(SLA_PiercingClit)
          main.RegisterAction(actorName + " has a clitoris piercing.")
        EndIf
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
    string appearanceStr = Main.GetActorName(Player) + " is a " + gender + " " + actorRace + "." 
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
      Debug.Notification(Main.GetActorName(akSpeaker) + " is getting more turned on.")
    EndIf
    If stringutil.Find(sayLine, "-eww-") != -1
      UpdateArousal(akSpeaker, -12)
      Debug.Notification(Main.GetActorName(akSpeaker) + " is getting less turned on.")
    EndIf
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  Actor akSpeaker=AIAgentFunctions.getAgentByName(speakerName)
  actor akTarget= AIAgentFunctions.getAgentByName(parameter)
  if !akTarget
    akTarget = PlayerRef
  EndIf
  if (akTarget.IsChild())
    Main.Warn(Main.GetActorName(akTarget) + " is a child actor. Not processing actions.")
    return
  EndIf
  Main.Debug("Arousal - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  string targetName = main.GetActorName(akTarget)
  if command == "ExtCmdIncreaseArousal"
    UpdateArousal(akSpeaker, 6)
    Debug.Notification(Main.GetActorName(akSpeaker) + " is getting more turned on.")
    Main.RegisterEvent(""+speakerName+"'s arousal level increased.")
  EndIf
  if command == "ExtCmdDecreaseArousal"
    UpdateArousal(akSpeaker, -12)
    Debug.Notification(Main.GetActorName(akSpeaker) + " is getting less turned on.")
    Main.RegisterEvent(""+speakerName+"'s arousal level decreased.")
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
  aiff.SetActorVariable(akTarget, "isexposed", IsTNGExposed(akTarget))
  if cuirass == None
    aiff.SetActorVariable(akTarget, "cuirass", "")
  Else
    aiff.SetActorVariable(akTarget, "cuirass", cuirass.GetName())
  EndIf

  string wornEquipments = GetWornEquipments(akTarget)
  aiff.SetActorVariable(akTarget, "AllWornEquipment", wornEquipments)
  
  if !bHasArousedKeywords
  	return
  EndIf
  if bHasBabo && akTarget == PlayerRef
    aiff.SetActorVariable(akTarget, "beautyScore", baboConfigs.BeautyValue.GetValueInt())
    aiff.SetActorVariable(akTarget, "breastsScore", baboConfigs.BreastsValue.GetValueInt())
    aiff.SetActorVariable(akTarget, "buttScore", baboConfigs.ButtocksValue.GetValueInt())
  EndIf
  string gender = "male";
  if akTarget.GetActorBase().GetSex() != 0
    gender = "female"
  EndIf
  aiff.SetActorVariable(akTarget, "gender", gender)
  string actorRace = (akTarget.GetActorBase().GetRace() as Form).GetName()
  int cotrIndex = StringUtil.Find(actorRace, " DZ")
  if cotrIndex != -1
    actorRace = StringUtil.Substring(actorRace, 0, cotrIndex)
  EndIf
  if actorRace == "fox"
    actorRace = "human"
  EndIf
  aiff.SetActorVariable(akTarget, "race", actorRace)
EndFunction

; Because escaping characters can be expensive, perform length encoding
; instead of escaping the string
string Function LengthEncodedString(string str)
  if (!str || str == "") 
    ; use empty string instead to save space
    return ""
  EndIf

  int len = StringUtil.GetLength(str)
  ; using integer instead of hex, most of the name should be less than 99 characters (so 2 digits)
  ; the IntToString hex will return 4 characters 0xFF
  return PO3_SKSEFunctions.IntToString(len, false) + "#" + str
EndFunction

string Function GetWornEquipments(Actor target)
  ; Encoding format: base form id:esp:slotMasks:keywords:name
  string wornEquipments = ""
  int index
  int slotsChecked
  slotsChecked += 0x00100000
  slotsChecked += 0x00200000
  slotsChecked += 0x80000000

  int currentSlot = 0x01
  while (currentSlot < 0x80000000)
    if (Math.LogicalAnd(slotsChecked, currentSlot) != currentSlot) ;only check slots we haven't found anything equipped on already
      Armor wornArmor = target.GetWornForm(currentSlot) as Armor
      if (wornArmor)
        int slotMask = wornArmor.GetSlotMask()
        string wornArmorName = wornArmor.GetName()
        ; Need both ID and mod name to ensure uniqueness
        string baseFormIdHex = PO3_SKSEFunctions.IntToString(Math.LogicalAnd(wornArmor.GetFormID(), 0x00FFFFFF), true)
        string modName = PO3_SKSEFunctions.GetFormModName(wornArmor, false)
        ; only need to escape "name" as others can't contain any colon
        wornEquipments += baseFormIdHex + ":" + modName + ":" + PO3_SKSEFunctions.IntToString(slotMask, true) + ":" + GetKeywordsForEquipments(wornArmor) + ":" + LengthEncodedString(wornArmorName)  + ":"
        slotsChecked += slotMask
      else ;no armor was found on this slot
        slotsChecked += currentSlot
      endif
    endif
    currentSlot *= 2 ;double the number to move on to the next slot
  endWhile
  Main.Debug("GetWornEquipments: " + wornEquipments)
  return wornEquipments
EndFunction

string Function GetEquipmentKeywordWithComma(Armor equipment, string keywordStr, Keyword theKeyword)
  if equipment.HasKeyword(theKeyword)
    return keywordStr + ","
  EndIf
  return ""
EndFunction

string Function GetKeywordsForEquipments(Armor theArmor)
  string ret = ""
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_HalfNakedBikini", SLA_HalfNakedBikini)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ArmorHalfNaked", SLA_ArmorHalfNaked)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_Brabikini", SLA_Brabikini)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ThongT", SLA_ThongT)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ThongLowLeg", SLA_ThongLowLeg)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ThongCString", SLA_ThongCString)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ThongGstring", SLA_ThongGstring)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PantiesNormal", SLA_PantiesNormal)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_KillerHeels", SLA_KillerHeels)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_BootsHeels", SLA_BootsHeels)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PantsNormal", SLA_PantsNormal)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_MicroHotPants", SLA_MicroHotPants)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ArmorHarness", SLA_ArmorHarness)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ArmorSpendex", SLA_ArmorSpendex)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ArmorTransparent", SLA_ArmorTransparent)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_ArmorLewdLeotard", SLA_ArmorLewdLeotard)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PelvicCurtain", SLA_PelvicCurtain)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_FullSkirt", SLA_FullSkirt)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_MiniSkirt", SLA_MiniSkirt)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_MicroSkirt", SLA_MicroSkirt)
  ret += GetEquipmentKeywordWithComma(theArmor, "EroticArmor", EroticArmor)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PiercingVulva", SLA_PiercingVulva)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PiercingBelly", SLA_PiercingBelly)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PiercingNipple", SLA_PiercingNipple)
  ret += GetEquipmentKeywordWithComma(theArmor, "SLA_PiercingClit", SLA_PiercingClit)
  return ret
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
    ret += aiff.GetKeywordIfExists(akTarget, "EroticArmor", EroticArmor)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PiercingVulva", SLA_PiercingVulva)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PiercingBelly", SLA_PiercingBelly)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PiercingNipple", SLA_PiercingNipple)
    ret += aiff.GetKeywordIfExists(akTarget, "SLA_PiercingClit", SLA_PiercingClit)
  EndIf
  if bHasTNG
    if akTarget.HasKeyword(TNG_Gentlewoman)
      ret += "TNG_Gentlewoman,"
    EndIf
    if akTarget.HasKeyword(TNG_XS)
      ret += "TNG_XS,"
    elseif akTarget.HasKeyword(TNG_S)
   	    ret += "TNG_S,"
    elseif akTarget.HasKeyword(TNG_M)
      ret += "TNG_M,"
    elseif akTarget.HasKeyword(TNG_L)
      ret += "TNG_L,"
    elseif akTarget.HasKeyword(TNG_XL)
      ret += "TNG_XL,"
    elseif akTarget.HasKeyword(TNG_DefaultSize)
      ret += "TNG_DefaultSize,"
    elseif akTarget.HasKeywordString("TNG_ActorAddnAuto:01")
      ret += "TNG_ActorAddnAuto:01,"
    elseif akTarget.HasKeywordString("TNG_ActorAddnAuto:02")
      ret += "TNG_ActorAddnAuto:02,"
    elseif akTarget.HasKeywordString("TNG_ActorAddnAuto:03")
      ret += "TNG_ActorAddnAuto:03,"
    elseif akTarget.HasKeywordString("TNG_ActorAddnAuto:04")
      ret += "TNG_ActorAddnAuto:04,"
    elseif akTarget.HasKeywordString("TNG_ActorAddnAuto:05")
      ret += "TNG_ActorAddnAuto:05,"
    EndIf
  EndIf
  int actorSex = akTarget.GetActorBase().GetSex()
  if (actorSex == 0)
    ret += "ActorSexMale,"
  elseif (actorSex == 1)
    ret += "ActorSexFemale,"
  else
    ret += "ActorSexOther,"
  EndIf
  ret += akTarget.GetActorBase().GetRace() + ","
  return ret
EndFunction

string Function GetFactionsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction


bool Function IsTNGExposed(Actor akTarget)
	Armor armorItem = akTarget.GetWornForm(cuirassSlot) as Armor
	if armorItem != None
		if !armorItem.HasKeyword(TNG_Revealing) && !armorItem.HasKeywordString("TNG_Revealing")
			Main.Debug(main.GetActorName(akTarget) + " is wearing concealing armor")
			return False
		EndIf
	EndIf
	Main.Debug(main.GetActorName(akTarget) + " is exposed.")
	return True
EndFunction
