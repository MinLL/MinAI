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
string[] Property ActorList Auto 

; other attributes track with the actor to a certain degree so needs to be updated regularly

minai_Util  MinaiUtil
minai_MainQuestController main
minai_AIFF aiff
Actor playerRef
minai_Followers followers

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

string[] function lengthenArray(string name, string[] nameList)
  string[] newNameList = Utility.CreateStringArray(nameList.Length + 1)
  int countNames = 0
  while (countNames < nameList.Length)
    newNameList[countNames] = name
    countNames += 1
  endwhile
  newNameList[newNameList.Length - 1] = name
  return newNameList
EndFunction


function Maintenance(minai_MainQuestController _main)
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  MinaiUtil = (self as Quest) as minai_Util
  bHasFrostfall = False
  MinaiUtil.Info("Environmental Awareness maintenance")
  playerRef = Game.GetPlayer()
  If Game.GetModByName("Frostfall.esp") != 255
    MinaiUtil.Info("Environmental Awareness - Frostfall.esp found")
    GlobalVariable FrostfallRunning = Game.GetFormFromFile(0x06DCFB, "Frostfall.esp") as GlobalVariable
    if(FrostfallRunning.GetValueInt() == 2) 
      MinaiUtil.Info("Environmental Awareness - Frostfall Enabled")
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
 
    if(bHasFrostfall)
      Weather akWeather = Weather.GetCurrentWeather()
      bool IsWeatherOvercast = _Frost_OvercastWeatherList.HasForm(akWeather)
      if(IsWeatherOvercast)
        envDescription += "The sky is overcast. "
      endif

      float currentTemp = _Frost_CurrentTemperature.GetValueInt()
      ; describe the temprature
      string airTemprature = ""
      if currentTemp <= -15
        airTemprature = "lethally cold"
      elseif currentTemp < 0
        airTemprature = "dangerously cold"
      elseif currentTemp < 10
        airTemprature = "cold"
      elseif currentTemp < 20
        airTemprature = "mild"
      elseif currentTemp < 23
        airTemprature = "room temprature"
      elseif currentTemp < 34
        airTemprature = "warm"
      elseif currentTemp < 40
        airTemprature = "hot"
      else 
        airTemprature = "extremely hot"
      endif
      envDescription += "The environment is " + airTemprature + ". "
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
  int r = Utility.RandomInt(0,20)
  bool notInList = ActorList.Find(an) < 0

  ; the player's data can change pretty often, and so can a follower's
  ; even player's height/race/sex/gender, so run it half the time rather than 1 in 20
  bool bIsPlayerOrFollower = playerRef == akActor || followers.IsFollower(akActor)
  if bIsPlayerOrFollower
    r += 9
  endif 

  if(notInList||r>19)
    if(notInList)
      ActorList = lengthenArray(an, ActorList)
    EndIf

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
    else 
      torso = " nothing"  
    endIf
    if(helmetArmor)
      helmet = " " + helmetArmor.GetName()
    endIf
    if(shoesArmor)
      shoes = " " + shoesArmor.GetName()
    else
      shoes = " no shoes"
    endIf
    if(shieldArmor)
      shield = " " + shieldArmor.GetName()
    endIf


    string wealthText = ""

    if(richClothes) 
      wealthText = " expensive"
    elseif(poorClothes)
      wealthText = " cheap"
    EndIf

    string armorWeight = ""
    if(IsLightArmor)
      armorWeight = " light"
    elseif(IsHeavyArmor)
      armorWeight = " heavy"
    endif

    string clothes = ", wearing" + wealthText + armorWeight + torso + ", " + shoes
    if(helmet)
      clothes += ", a " + helmet 
    endif
    if(shield)
      clothes +=  " and a " + shield
    endif
    staticData += clothes

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
    string playerName = main.GetActorName(playerRef)
    if(akActor!=playerRef)
      if(bIsBribed)
        privateKnowledge += an + " has accepted bribes from " + playerName + ". "
        staticData += an + " seems smug around " + playerName + ". "
      endif
      if(bIsIntimidated)
        staticData += " " + an + " seems anxious and a little frightend around " + playerName + ". "
        privateKnowledge = playerName + " has used threats against me in the past, I do what " + playerName + " wants because I am frightend of them. "
      endif
      if(bWouldBeIntimidated && !bIsIntimidated)
        staticData += " " + an + " finds " + playerName + " potentially intimidating, though " + playerName + " has never been aggressive with them. " 
      endif
      
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
    ; supposedly 140 is max for orc and nord who get +10 to cold benefit
    if(actorsWarmth>=130)
      dynamicData += actorName + " is dressed very warmly, like for a winter evening."
    elseif(actorsWarmth>=110)
      dynamicData += actorName + " is dressed warmly, like for a winter day."
    elseif(actorsWarmth>=90)
      dynamicData += actorName + " is dressed lukewarmly, like for a brisk breeze."
    elseif(actorsWarmth>=70)
      dynamicData += actorName + " is dressed for moderate weather."
    elseif(actorsWarmth>=50)
      dynamicData += actorName + " attire clearly has warmth as an afterthought. "
    elseif(actorsWarmth>=30)
      dynamicData += actorName + " is as dressed as a summertime swimmer."
    else 
      dynamicData += actorName + " is lacking any clothes that would keep them warm."
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
  if(playerCoverage>=130)
    dynamicData += actorName + " has great protection from the rain and wind by some combination of their clothes or environment. "
  elseif(playerCoverage>=100)
    dynamicData += actorName + " has good protection from the rain and wind by some combination of their clothes or environment. "
  elseif(playerCoverage>=60)
    dynamicData += actorName + " has some pretty nice protection from the rain and wind because of their clothes or the things around them. "
  elseif(playerCoverage>=30)
    dynamicData += actorName + " has some protection from the rain and wind because of their clothes or the things around them. "
  elseif(playerCoverage>=10)
    dynamicData += actorName + " has little protection from the rain and wind. "
  else
    dynamicData += actorName + " is completely exposed to any rain and wind. "
  endif
  Endif
  if(dynamicData != "") 
    dynamicData =  " " + an + " is " + dynamicData 
  endif
  aiff.SetActorVariable(akActor, "EnvironmentalAwarenessDynamicData", dynamicData)
EndFunction