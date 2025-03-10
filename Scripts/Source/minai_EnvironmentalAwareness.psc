scriptname minai_EnvironmentalAwareness extends Quest


; some support for Frostfall -- would like to know players soggieness and coldness, temprature
; followers would notice frostbite setting in etc

bool bHasFrostfall
; frostfall vars
GlobalVariable _Frost_CurrentTemperature
GlobalVariable _Frost_NearFire 
GlobalVariable _Frost_CurrentHeatSourceDistance 
GlobalVariable _Frost_CurrentHeatSourceSize 
GlobalVariable _Frost_WetLevel 
GlobalVariable _Frost_AttributeCoverage 
GlobalVariable _Frost_AttributeWarmth 
GlobalVariable _Frost_ExposureLevel 
GlobalVariable _Frost_IsTakingShelter 
GlobalVariable _Frost_ExposureTarget
FormList _Frost_OvercastWeatherList
FormList _Frost_SevereWeatherList

; tracked actors - most attributes don't change so once found we'll save that here
; so that we know the db is aready updated 
; we'll use a map of actorName to boolean true for tracked actors
; rather than searching a list for a match, just use map
int iActorMap 

; other attributes track with the actor to a certain degree so needs to be updated regularly

minai_Util  MinaiUtil
minai_MainQuestController main
minai_AIFF aiff
Actor playerRef
string playerName
minai_Followers followers
bool bIsNight = false


string function GetDayState() 
	float Time = Utility.GetCurrentGameTime()
	Time -= Math.Floor(Time) ; Remove "previous in-game days passed" bit
	Time *= 48 ; Convert from fraction of a day to number of 1/2 hours for context about imminent sunrise
	
  string str = ""
  if(Time<3) 
    str = "midnight"
  elseif(Time<9) 
    str = "dead of night"
  elseif(Time<10) 
    str = "just before sunrise"
  elseif(Time<11) 
    str = "dawn"
  elseif(Time<14) 
    str = "early morning"
  elseif(Time<20) 
    str = "morning"
  elseif(Time<21) 
    str = "mid-morning" 
  elseif(Time<23) 
    str = "late morning"
  elseif(Time<25) 
    str = "noon"
  elseif(Time<29) 
    str = "early afternoon"
  elseif(Time<32) 
    str = "afternoon"
  elseif(Time<34) 
    str = "late afternoon"
  elseif(Time<37) 
    str = "just before sunset"
  elseif(Time<38) 
    str = "sunset" 
  elseif(Time<40) 
    str = "early evening"
  elseif(Time<42) 
    str = "evening"
  elseif(Time<44) 
    str = "night"
  elseif(Time<46) 
    str = "late night" 
  elseif(Time==47) 
    str = "almost midnight"
  endif
    bIsNight = Time<11 || Time>38
  return str
EndFunction

String Function GetRaceName(Race akRace)
	String name = akRace.GetName()
	If akRace.HasKeywordString("Vampire")
		name = "$RacenameVampire{" + name + "}"
	ElseIf akRace.GetFormId() == 0x97a3d
		name = "$Afflicted"
	EndIf
	return name
EndFunction

function Maintenance(minai_MainQuestController _main)  
  main = _main
  main.Info("Environmental Awareness Initializing")
  aiff = (Self as Quest) as minai_AIFF
  MinaiUtil = (self as Quest) as minai_Util
  bHasFrostfall = False
  if (iActorMap)
    ; Release actor map every time the game loads to avoid infinite memory growth
    JValue.release(iActorMap)
    iActorMap = 0
  endif
  if (!iActorMap)
    Main.Debug("Environmental Awareness: Creating new actor map")
    iActorMap = JMap.object()
    JValue.retain(iActorMap)
  endif
  
  playerRef = Game.GetPlayer()
  playerName = main.GetActorName(playerRef)
  If Game.GetModByName("Frostfall.esp") != 255
    GlobalVariable FrostfallRunning = Game.GetFormFromFile(0x06DCFB, "Frostfall.esp") as GlobalVariable
    if(FrostfallRunning.GetValueInt() == 2) 
      bHasFrostfall = True
      _Frost_CurrentTemperature = Game.GetFormFromFile(0x0665F9, "Frostfall.esp") as GlobalVariable
      _Frost_NearFire = Game.GetFormFromFile(0x064AFD, "Frostfall.esp") as GlobalVariable 
      _Frost_CurrentHeatSourceDistance = Game.GetFormFromFile(0x064AFB, "Frostfall.esp") as GlobalVariable 
      _Frost_CurrentHeatSourceSize = Game.GetFormFromFile(0x064AFC, "Frostfall.esp") as GlobalVariable 
      _Frost_WetLevel = Game.GetFormFromFile(0x06458D, "Frostfall.esp") as GlobalVariable 
      _Frost_AttributeCoverage = Game.GetFormFromFile(0x067B91, "Frostfall.esp") as GlobalVariable 
      _Frost_AttributeWarmth = Game.GetFormFromFile(0x067B8F, "Frostfall.esp") as GlobalVariable 
      _Frost_ExposureLevel = Game.GetFormFromFile(0x068119, "Frostfall.esp") as GlobalVariable 
      _Frost_IsTakingShelter = Game.GetFormFromFile(0x068118, "Frostfall.esp") as GlobalVariable 
      _Frost_ExposureTarget = Game.GetFormFromFile(0x0805E4, "Frostfall.esp") as GlobalVariable 
      _Frost_OvercastWeatherList = Game.GetFormFromFile(0x04671A, "Frostfall.esp") as FormList 
      _Frost_SevereWeatherList = Game.GetFormFromFile(0x024098, "Frostfall.esp") as FormList
    endif
  endif
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
EndFunction

function SetContext(actor akActor)
  Main.Debug("SetContext EnvironmentalAwareness(" + Main.GetActorName(akActor) + ")")
  string an = Main.GetActorName(akActor)
  if(akActor == playerRef)
    string envDescription = GetDayState()
    if(envDescription)
      envDescription = "It is " + envDescription + "."
    endif
    int weatherInt = Weather.GetCurrentWeather().GetClassification()
    if(weatherInt == 3)
      envDescription += "It is snowing outside. "
    elseif(weatherInt == 2) 
      envDescription += "It is raining outside. "
    endif
    ; Snow is 3, Rain is 2, Cloudy is 1, Clear is 0, and -1 is used 
    envDescription += GetMoonStatus()

    if(akActor.IsInInterior())
      envDescription = "We are indoors. "
    endif

    if(bHasFrostfall)
      Weather akWeather = Weather.GetCurrentWeather()
      bool IsWeatherOvercast = _Frost_OvercastWeatherList.HasForm(akWeather)
      if(IsWeatherOvercast)
        envDescription += "The sky is overcast. "
      endif

      float currentTemp = _Frost_CurrentTemperature.GetValueInt()
      ; describe the temprature
      string airTemprature = ""
      if currentTemp < -14
        airTemprature = "frigid and deadly"
      elseif currentTemp < -9
        airTemprature = "bone-chilling"
      elseif currentTemp < -4
        airTemprature = "biting cold"
      elseif currentTemp < 1
        airTemprature = "cold"
      elseif currentTemp < 6
        airTemprature = "chilly"
      elseif currentTemp < 10
        airTemprature = "cool"
      elseif currentTemp == 10
        airTemprature = "temperate"
      elseif currentTemp < 15
        airTemprature = "pleasant"
      elseif currentTemp < 18
        airTemprature = "warm"
      else 
        airTemprature = "hot"
      endif
      envDescription += "The temperature is " + airTemprature + ". "
      bool IsWeatherSevere = _Frost_SevereWeatherList.HasForm(akWeather)
      if(IsWeatherSevere)
        dynamicData += "The weather is severe and dangerous. People must be careful! "
      endif
    endIf
    envDescription += "\n"
    aiff.SetActorVariable(akActor, "EnviromentalAwarenessPlayerEnviroment", envDescription)
  endIf
 

  ; if name not in list yet lets do some sets of stuff, like family
  ; except for the oddity that is certain family rearing mods where children can grow
  int r = PO3_SKSEFunctions.GenerateRandomInt(0,20)

  bool bNotInList = (JMap.getInt(iActorMap , an) != 1)
  string wasInList = an + " was in the list!"
  if(bNotInList) 
    wasInList = an + " was NOT in list"
  endIf
  MinaiUtil.Debug("Environmental Awareness SetContext 002 :: "  + wasInList)

  ; the player's data can change pretty often, and so can a follower's
  ; even player's height/race/sex/gender, so run it half the time rather than 1 in 20
  bool bIsPlayerOrFollower = playerRef == akActor || followers.IsFollower(akActor)
  if bIsPlayerOrFollower
    r += 9
  endif 
  if(bNotInList||r>19)
    if(bNotInList)
      JMap.setInt(iActorMap, an, 1)
      MinaiUtil.Debug("Environmental Awareness SetContext 003 added :: "  + an)
    EndIf

   
    ; use the sqrt of the player's level to assign brackets of peer combat status.
    ; so a level 9 finds levels like 6-12 peers, 13 through level 15 to be better, 15+ much better  
    ; level 49 finds level 42-56 to be peers. Works for scaling levels. 
    int actorLevel = akActor.GetLevel()
    int playerLevel = playerRef.GetLevel()
    float sqrtOfPlayerLevel = Math.sqrt(playerLevel as float)
    int levelDifference = actorLevel - playerLevel
    
    int ranking = 3 
    if(levelDifference<-2*sqrtOfPlayerLevel)
      ranking = 1
    elseif(levelDifference<-1*sqrtOfPlayerLevel)
      ranking = 2
    elseif(levelDifference>2*sqrtOfPlayerLevel)
      ranking = 5
    elseif(levelDifference>sqrtOfPlayerLevel)
      ranking = 4
    endif
    
    ; 5 - much better
    ; 4 - better than
    ; 3 - peer
    ; 2 - weaker than
    ; 1 - much weaker than
    ; if you're between the upper and lower limit you're something of a peer
    
    string staticData = "" + an + " is: "
    bool isKid = akActor.IsChild()
    bool hasAFamily = akActor.HasFamilyRelationship()
    
    if(isKid && !hasAFamily)
      staticData += ", an orphan child"
    ElseIf (isKid)
      staticData += ", a child with family nearby"
    ElseIf (hasAFamily)
      staticData += ", an adult who has family"
    else 
      staticData += ", an adult"
    EndIf

    ; anything different from 1 is shorter or taller, .05 difference is notably short/ 1.05 notably taller than their racial norm
    ; for relative height "is short for a nord"

    Race akRace = akActor.GetRace()
    string racename = GetRaceName(akRace)
    staticData += ", a " + racename
    float height = akActor.GetActorBase().GetHeight()
    if(height<0.95)
      staticData += ", very short for a " + racename
    elseif(height<0.98)
      staticData += ", short compared to a normal " + racename
    elseif(height<1.02)
      staticData += ", of normal height for a " + racename
    elseif(height<1.05) 
      staticData += ", tall for a " + racename
    else
      staticData += ", very tall for a " + racename
    endIf

    ; maybe i'll write a mod so NPCs change their wardrobes some day but for now NPCs (not followers) rarely change outfits
    ; this will focus on things you can see without intending to look at someone, helmet, armor, shoes
    ; cause everyone looks at your shoes
    ; shield because logos matter like gang signs in skyrim

    Armor helmetArmor = akActor.GetEquippedArmorInSlot(30)
    Armor torsoArmor = akActor.GetEquippedArmorInSlot(32)
    Armor shoesArmor = akActor.GetEquippedArmorInSlot(37)
    Armor shieldArmor = akActor.GetEquippedArmorInSlot(39)
    
    bool IsHeavyArmor = false
    bool IsLightArmor = false
    bool richClothes = false
    bool poorClothes = false
    string helmet = ""
    string torso = ""
    string shoes = ""
    string shield = ""

    if(torsoArmor)
      torso = " " + torsoArmor.GetName()
      IsHeavyArmor = torsoArmor.IsHeavyArmor()
      IsLightArmor = torsoArmor.IsLightArmor()
      richClothes = torsoArmor.IsClothingRich()
      poorClothes = torsoArmor.IsClothingPoor()

    endIf

    string wealthText = ""
    if(richClothes) 
      wealthText = " expensive"
    elseif(poorClothes)
      wealthText = " cheap"
    EndIf

    if(wealthText!="")
      staticData += ". " + an + " is dressed " + wealthText + " "
    EndIf

    if(!bHasFrostfall || playerRef != akActor)
      float actorsWarmth = akActor.GetWarmthRating()
      ; supposedly 140 is max for orc and nord who get +10 to cold benefit
      ; add 30 because NPCs are always underdressed 
      ; and some mods add undetected clothes for weather
      ; but if basically naked no bonus
      if(!bIsPlayerOrFollower && actorsWarmth>=30)
        actorsWarmth += 30
      endif
      string warmthLanguage = ""
      if(actorsWarmth>=130)
        warmthLanguage = "extremely warm"
      elseif(actorsWarmth>=110)
        warmthLanguage = "very warm"
      elseif(actorsWarmth>=90)
        warmthLanguage = "moderately warm"
      elseif(actorsWarmth>=70)
        warmthLanguage = "uninsulated to the cold"
      elseif(actorsWarmth>=50)
        warmthLanguage = "not warm at all"
      elseif(actorsWarmth>=30)
        warmthLanguage = "exposing them to the cold"
      else 
        warmthLanguage = "completely vulnerable to the cold"
      endif
      staticData += ", and their attire is " + warmthLanguage + ". "
    endif
    string privateKnowledge = ""

    if(akActor!=playerRef)     
      if(ranking<=1)
        staticData += " " + an + " would be a helpless combatant against " + playerName + ". "
      elseif(ranking==2)
        staticData += " " + an + " would be a vastly overmatched combatant against " + playerName + ". "
      elseif(ranking==3)
        staticData += " " + an + " is a peer combatant when compared to " + playerName + ". "
      elseif(ranking==4)
        staticData += " " + playerName + " would be a vastly overmatched combatant against " + an + ". "
      elseif(ranking>=5)
        staticData += " " + playerName + " would be a helpless combatant against " + an + ". "
      endif     
    endIf        
    
    ; what class are people around you?
    ActorBase akBase = akActor.GetBaseObject() as ActorBase
    Class aClass = akBase.GetClass()

    string careerName = aClass.GetName()
    string publicCareerText = " " + an + " is a " + careerName + ". "
    ; prevent Razita is a Razita 
    if(careerName == an)
      publicCareerText = ""
    endif
    if(careerName == "Assassin" || careerName == "Thief" || careerName == "Bandit Archer" || careerName == "Bandit" || careerName == "Bandit Wizard"|| careerName == "Blade" || careerName == "Vampire" || careerName == "Werewolf" || careerName == "dremora")
      string privateCareerText = an + " is a " + careerName + " but is secretive about that unless in select company - like with other " + careerName + "s or close friends.  "
      privateKnowledge += " " + privateCareerText
      publicCareerText = an + " has an air of mystery about them. "
      Main.Debug("Actor " + an + " is a " + careerName)
    endif
    staticData +=  publicCareerText
    aiff.SetActorVariable(akActor, "EnvironmentalAwarenessPrivateKnowledge", privateKnowledge) 
    aiff.SetActorVariable(akActor, "EnviromentalAwarenessMoreStableData", staticData)
  EndIF

  string dynamicData = ""
  ; trying to sit or maybe is sitting or trying to get up
  ; 4: Sitting, wants to stand
  ; 3: Sitting
  ; 2: Not sitting, wants to sit
  ; 0: Not sitting
  int sitValue = akActor.GetSitState()
  if (sitValue == 3)
    dynamicData += ", sitting down"
  elseif(sitValue == 2)
    dynamicData += ", trying to sit down"
  elseif(sitValue == 4)
    dynamicData += ", getting up"
  endIf

  int sleepValue = akActor.GetSleepState()
  if(sleepValue==3) 
    dynamicData += ", sleeping"
  elseif(sleepValue==2)
    dynamicData += ", wanting to sleep"
  elseif(sleepValue==4)
    dynamicData += ", waking up"
  endif

  if(akActor.IsOverEncumbered())
      dynamicData += "\n\t overly encumbered and slow to move, carrying exhausting weight"
  endif

  if(akActor.IsOnMount())
    dynamicData += "\n\t riding a horse"
  Endif
  
  if(akActor.IsSwimming())
    dynamicData += ", swimming"
  endif

  if(bHasFrostfall && akActor == playerRef)
    bool nearFire = _Frost_NearFire.GetValueInt() == 2
    if(nearFire) 
      dynamicData += ", near a fire, which helps them dry if wet"
    endIf
    int heatSize = _Frost_CurrentHeatSourceSize.GetValueInt()
    if(heatSize == 0) 
      dynamicData += ", not near a fire"
    elseif(heatSize == 1)
      dynamicData += ", near a small fire"
    elseif(heatSize == 2)
      dynamicData += ", near a good fire"
    elseif(heatSize == 3)
      dynamicData += ", near a roaring fire"
    endif
    float heatDistance = _Frost_CurrentHeatSourceDistance.GetValue()
    if(heatDistance<0)
      dynamicData += ""
    elseif(heatDistance <= 300) 
      dynamicData += ", very close to the fire"
    elseif(heatDistance<=450)
      dynamicData += ", close to the fire"
    else 
      dynamicData += ", at the edge of the fire's heat"
    endif

    int whereAreWe = Weather.GetSkyMode()
    ; 0 - No sky 
    ; 1 - Interior
    ; 2 - Skydome only
    ; 3 - Full sky
    if(whereAreWe>1 && _Frost_IsTakingShelter.GetValue()==2)
      dynamicData += ", sheltered by things overhead"
    endif
    
    int playerWetness = _Frost_WetLevel.GetValueInt()
    if(playerWetness==0) 
      dynamicData += ", is dry"
    elseif(playerWetness==1)
      dynamicData += ", is damp"
    elseif(playerWetness==2)
      dynamicData += ", is wet"
    else
      dynamicData += ", is drenched"
    endif
    string actorName = an

    ; exposure is the measure of a body losing heat faster than it can make it, like when falling into cold water
    int playersExposureLevel = _Frost_ExposureLevel.GetValueInt()
    if(playersExposureLevel==-1)
      dynamicData += "" ;  in unexposed say nothing
    elseif(playersExposureLevel==0)
      dynamicData += ". " + actorName + " is a bit chilled."
    elseif(playersExposureLevel == 1)
      dynamicData += ". " + actorName + " seems chilly, with a reddened nose."
    elseif(playersExposureLevel == 2)
      dynamicData += ". " + actorName + " is cold, rubbing their hands together periodically."
    elseif(playersExposureLevel == 3)
      dynamicData += ". " + actorName + " is very cold, shivering lightly, their teeth chatter."
    elseif(playersExposureLevel == 4)
      dynamicData += ". " + actorName + " is freezing, dangerously cold, their teeth chatter and interupt their speech, they are shivering noticably."
    elseif(playersExposureLevel == 5)
      dynamicData += ". " + actorName + " is freezing to death. They are looking blue, their teeth are chattering, and they are shivering in a big hard to control vibrating motion. "
    elseif(playersExposureLevel == 6)
      dynamicData += ". " + actorName + " is too cold, nearing hypothermia, they are nearing death! They can't speak and can barely move due to the cold. "
    endif

    ; what will the baseline exposure of the player be if they don't take any actions, stand near fires etc

    float playersBaseline = _Frost_ExposureTarget.GetValue()
    if(playersBaseline<20)
      dynamicData += ". " + actorName + " finds the weather warm. "
    elseif(playersBaseline<40)
      dynamicData += ". " + actorName + " finds the weather comfortable. "
    elseif(playersBaseline<60)
      dynamicData += ". " + actorName + " finds the weather cold. "
    elseif(playersBaseline<80)
      dynamicData += ". " + actorName + " finds the weather very cold. "
    elseif(playersBaseline<100)
      dynamicData += ". The weather dangerously cold for " + actorName + ", it is freezing and going away from heat is very risky. "
    else
      dynamicData += ". The weather is lethally cold for " + actorName + " and they could easily freeze to death. "
    endif


    ; how bundled up is the player
    int actorsWarmth = _Frost_AttributeWarmth.GetValueInt()
    ; in frostfall, 300 is a good score, so more like double non-frostfall version
    if(actorsWarmth>=290)
      dynamicData += actorName + " is dressed very warmly. "
    elseif(actorsWarmth>=270)
      dynamicData += actorName + " is dressed warmly. "
    elseif(actorsWarmth>=240)
      dynamicData += actorName + " is dressed lukewarmly. "
    elseif(actorsWarmth>=200)
      dynamicData += actorName + " is dressed for moderate weather. "
    elseif(actorsWarmth>=160)
      dynamicData += actorName + " does not appear dressed for warmth. "
    elseif(actorsWarmth>=100)
      dynamicData += actorName + " is dressed like a summertime swimmer. "
    else 
      dynamicData += actorName + " is lacking any clothes that would keep them warm. "
    endif
    
    float cTemp = _Frost_CurrentTemperature.GetValueInt()
    ; describe the temprature
    string aTemp = ""
    if cTemp <= -15
      aTemp = " lethally cold"
    elseif cTemp <= 0
      aTemp = " dangerously cold"
    elseif cTemp <= 10
      aTemp = " cold"
    elseif cTemp < 20
      aTemp = " mild"
    elseif cTemp < 23
      aTemp = " room temprature"
    elseif cTemp < 34
      aTemp = " warm"
    elseif cTemp < 40
      aTemp = " hot"
    else 
      aTemp = " extremely hot"
    endif
  
    if(actorsWarmth < 30 && cTemp <= 10)
      dynamicData += " " + actorName + "'s clothing situation leaves them completely exposed to the" + aTemp + " weather. "
    elseif(actorsWarmth < 50 && cTemp <= 10)
      dynamicData += " " + actorName + "'s clothing is not protecting them from the" + aTemp + " weather. "
    elseif(actorsWarmth < 70 && cTemp <= 10)
      dynamicData += " " + actorName + "'s clothing is almost no protection from the" + aTemp + " weather. "
    endif
      
    int playerCoverage = _Frost_AttributeCoverage.GetValueInt()
    if(playerCoverage>=300)
      dynamicData += " " + actorName + " has great protection from rain and wind by some combination of their clothes or environment. "
    elseif(playerCoverage>=270)
      dynamicData += " " + actorName + " has good protection from rain and wind by some combination of their clothes or environment. "
    elseif(playerCoverage>=220)
      dynamicData += " " + actorName + " has some pretty nice protection from rain and wind because of their clothes or the things around them. "
    elseif(playerCoverage>=160)
      dynamicData += " " + actorName + " has some protection from rain and wind because of their clothes or the things around them. "
    elseif(playerCoverage>=80)
      dynamicData += " " + actorName + " has little protection from rain and wind. "
    else
      dynamicData += " " + actorName + " is completely exposed to any rain and wind. "
    endif
  Endif
  if(dynamicData != "") 
    dynamicData =  " " + an + " is " + dynamicData 
  endif

  if(akActor != playerRef)
    ; has player ever bribed this NPC?
    bool bIsBribed = false
    if (akActor.IsBribed())
      bIsBribed = true
    endIf


    ; check if player has intimidated this character, if a player ever has this is true,
    ; and by the laws of the game's mechanics if a player is intimidating to someone in the past even moreso in the future!
    ; maybe though if they meet and the player is laid low by a defeat mod, we can reset the NPC's intimidation
    ; to be future compatible allow the value to change back to false if we add "un-intimidate" mechanics
    bool bIsIntimidated = false
    if(akActor.isIntimidated())
      bIsIntimidated = true
    endif

    bool bWouldBeIntimidated = false
    ; check if the player could intimidate the NPC if they wanted to, because in the battle familiar world of Skyrim everyone knows
    ; who can take who out, so for added flavor of respect, fear, grovelling, or worship
    if(akActor.willIntimidateSucceed())
      bWouldBeIntimidated = true
    endif

    string dynamicPrivateData = ""
    if(bIsBribed)
      dynamicPrivateData += an + " has accepted bribes from " + playerName + ". "
      dynamicData += an + " seems smug around " + playerName + ". "
    endif
    if(bIsIntimidated)
      dynamicData += " " + an + " seems anxious and a little frightend around " + playerName + ". "
      dynamicPrivateData = playerName + " has used threats against me in the past, I do what " + playerName + " wants because I am frightend of them. "
    endif
    if(bWouldBeIntimidated && !bIsIntimidated)
      dynamicPrivateData += " " + an + " finds " + playerName + " potentially intimidating, though " + playerName + " has not been aggressive with them. " 
    endif
    aiff.SetActorVariable(akActor, "EnvironmentalAwarenessDynamicPrivateData", dynamicPrivateData)
  endIf
  aiff.SetActorVariable(akActor, "EnvironmentalAwarenessDynamicData", dynamicData)
EndFunction


; moon phase logic is from the papyrus wiki
Int Function GetPassedGameDays()
	Float GameDaysPassed
	GameDaysPassed = Utility.GetCurrentGameTime()
	Return GameDaysPassed As Int
EndFunction

Int Function GetPassedGameHours() Global
	Float GameTime
	Float GameHoursPassed
 
	GameTime = Utility.GetCurrentGameTime()
	GameHoursPassed = ((GameTime - (GameTime As Int)) * 24)
	Return GameHoursPassed As Int
EndFunction

string function GetMoonStatus()
  int phase = GetCurrentMoonPhase()
  string txt = "The " + GetCurrentMoonSync()
  if(bIsNight)
    txt += " are"
  else
    txt += " will be"
  endif
  if(phase == 0) 
    txt += " full"
  elseif(phase == 1)
    txt += " wanning gibbious"
  elseif(phase == 2)
    txt += " third quarter"
  elseif(phase == 3)
    txt += " wanning crescent"
  elseif(phase == 4)
    txt += " in new moon"
  elseif(phase == 5)
    txt += " waxing crescent"
  elseif(phase == 6)
    txt += " first quarter"
  elseif(phase == 7)
    txt += " waxing gibbious"
  endif
  txt += " tonight."
  return txt
endfunction

int Function GetCurrentMoonPhase()
	Int GameDaysPassed
	Int GameHoursPassed
	Int PhaseTest
	GameDaysPassed = GetPassedGameDays()
	GameHoursPassed = GetPassedGameHours()
	If (GameHoursPassed >= 12.0)
		GameDaysPassed += 1
	EndIf
	PhaseTest = GameDaysPassed % 24 ;A full cycle through the moon phases lasts 24 days
	If PhaseTest >= 22 || PhaseTest == 0
		Return 7
	ElseIf PhaseTest < 4
		Return 0
	ElseIf PhaseTest < 7
		Return 1
	ElseIf PhaseTest < 10
		Return 2
	ElseIF PhaseTest < 13
		Return 3
	ElseIf PhaseTest < 16
		Return 4
	ElseIf PhaseTest < 19
		Return 5
	ElseIf PhaseTest < 22
		Return 6
	EndIf
EndFunction

string Function GetCurrentMoonSync()
	Int GameDaysPassed
	Int GameHoursPassed
	Int SyncTest
	
	GameDaysPassed = GetPassedGameDays()
	GameHoursPassed = GetPassedGameHours()
	If (GameHoursPassed >= 12)
		GameDaysPassed += 1
	EndIf
	SyncTest = GameDaysPassed % 5
	if(SyncTest == 0)
    return " two moons"
  endif
  return " moon"
EndFunction
