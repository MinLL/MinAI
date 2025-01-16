scriptname minai_EnvironmentalAwareness extends Quest

; some support for Frost Fall -- need to know players soggieness and coldness, temprature
; followers would notice frostbite setting in etc

bool bHasFrostFall
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
; \ [03] Frostfall.esp \ [3] GRUP Top "GLOB" \ [143]  [GLOB:] \ [0] Record Header
; \ [03] Frostfall.esp \ [3] GRUP Top "GLOB" \ [16] _Frost_Setting_FrigidWaterIsLethal [GLOB:0300E5B7] \ [0] Record Header



; tracked actors - most attributes don't change so once found we'll save that here
; so that we know the db is aready updated 
string[] Property ActorList Auto 

; other attributes track with the actor to a certain degree so needs to be updated regularly

int actorLevel
int actorRelationRankToPlayer
bool isIntimidated ; if user intimidates someone, by the mechanics of the game they always will so this is permanent flag
bool willIntimidateSucceed ; they might be your friend but if they got aggressive would you be intimidated? in the war culture of Skyrim people know

; Bool IsGhost()

;     Is this actor flagged as a ghost?

; Bool IsGuard()

;     Obtains whether this actor is a guard or not.

; Bool IsHostileToActor-(Actor akActor) 


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
  MinaiUtil = (self as Quest) as minai_Util
  If Game.GetModByName("Frostfall.esp") != 255
    bHasFrostFall = True
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
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
EndFunction

function SetContext(actor akActor)
  if(akActor == playerRef)
    string envDescription = "It is " + GetDayState() + "."
    if(bHasFrostFall)
      Weather akWeather = Weather.GetCurrentWeather()
      bool IsWeatherOvercast = _Frost_OvercastWeatherList.HasForm(akWeather)
      if(IsWeatherOvercast)
        envDescription += " The sky is overcast."
      endif

      float currentTemp = _Frost_CurrentTemperature.GetValueInt()
      ; float currentTemp = FrostUtil.GetCurrentTemperature()
      ; describe the temprature
      string airTemprature = ""
      if currentTemp <= -15
        airTemprature = "lethally cold"
      elseif currentTemp <= 0
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
      envDescription += "The environment is " + currentTemp + "."
      bool IsWeatherSevere = _Frost_SevereWeatherList.HasForm(akWeather)
      if(IsWeatherSevere)
        dynamicData += " The weather is severe and dangerous. People must be careful!"
      endif
    endIf
    envDescription += "\n"
    aiff.SetActorVariable(akActor, "EnviromentalAwarenessPlayerEnviroment", envDescription)
  endIf
  string name = MinaiUtil.GetActorName(akActor)
  ; if name not in list yet lets do some sets of stuff, like family
  ; except for the oddity that is certain family rearing mods where children can grow
  int r = Utility.RandomInt(0,20)
  bool notInList = ActorList.Find(name) < 0

  ; the player's data can change pretty often, and so can a follower's
  ; even player's height/race/sex/gender, so run it half the time rather than 1 in 20
  bool isPlayerOrFollower = playerRef == akActor || followers.IsFollower(akActor)
  if isPlayerOrFollower
    r += 9
  endif 

  if(notInList||r>19)
    if(notInList)
      ActorList = lengthenArray(name, ActorList)
    EndIf
    string staticData = "About " + name + ":"
    bool isKid = akActor.IsChild()
    bool hasAFamily = akActor.HasFamilyRelationship()
    if(isKid && !hasAFamily)
      staticData += "\n\t* is an orphan child."
    ElseIf (isKid)
      staticData += "\n\t* is a child with family nearby"
    ElseIf (hasAFamily)
      staticData += "\n\t* an adult who has family"
    else 
      staticData += "\n\t* an adult"
    EndIf


    ; anything different from 1 is shorter or taller, .05 difference is notably short/ 1.05 notably taller than their racial norm
    ; for relative height "is short for a nord"

    Race akRace = akActor.GetRace()
    string racename = GetRaceName(akRace)
    staticData += "\n\t* is a " + racename
    float height = akActor.GetActorBase().GetHeight()
    if(height<0.95)
      staticData += "\n\t* is very short for a " + racename
    elseif(height<0.98)
      staticData += "\n\t* is short compared to a normal " + racename
    elseif(height<1.02)
      staticData += "\n\t* is of normal height for a " + racename
    elseif(height<1.05) 
      staticData += "\n\t* is tall for a " + racename
    else
      staticData += "\n\t* is very tall for a " + racename
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
    string helmet = ""
    string torso = ""
    string shoes = ""
    string shield = ""

    if(torsoArmor)
      torso = " " + torsoArmor.GetName()
      IsHeavyArmor = torsoArmor.IsHeavyArmor()
      IsLightArmor = torsoArmor.IsLightArmor()
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

    bool richClothes = torsoArmor.IsClothingRich()
    bool poorClothes = torsoArmor.IsClothingPoor()
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

    string clothes = "\n\t* is wearing" + wealthText + armorWeight + torso + ", " + shoes
    if(helmet)
      clothes += ", a " + helmet 
    endif
    if(shield)
      clothes +=  " and a " + shield
    endif
    staticData += clothes

    if(!bHasFrostFall || playerRef != akActor)
      float actorsWarmth = akActor.GetWarmthRating()
      ; supposedly 140 is max for orc and nord who get +10 to cold benefit
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
      staticData += "\n\t* their attire is " + warmthLanguage
    endif
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
    dynamicData += "\n\t* is sitting down"
  elseif(sitValue == 2)
    dynamicData += "\n\t* is trying to sit down"
  elseif(sitValue == 4)
    dynamicData += "\n\t* is getting up"
  endIf

  int sleepValue = akActor.GetSleepState()
  if(sleepValue==3) 
    dynamicData += "\n\t* is sleeping"
  elseif(sleepValue==2)
    dynamicData += "\n\t* wants to sleep"
  elseif(sleepValue==4)
    dynamicData += "\n\t* is waking up"
  endif

  if(akActor.IsOverEncumbered())
      dynamicData += "\n\t is overly encumbered and slow to move, carrying exhausting weight"
  endif

  if(akActor.IsOnMount())
    dynamicData += "\n\t is riding a horse"
  Endif
  
  if(akActor.IsSwimming())
    dynamicData += "\n\t* is swimming"
  endif

  if(bHasFrostFall && akActor == playerRef)
    bool nearFire = _Frost_NearFire.GetValueInt() == 2
    if(nearFire) 
      dynamicData += "\n\t* is near a fire, which helps them dry if wet"
    endIf
    int heatSize = _Frost_CurrentHeatSourceSize.GetValueInt()
    if(heatSize == 0) 
      dynamicData += "\n\t* is not near a fire"
    elseif(heatSize == 1)
      dynamicData += "\n\t* near a small fire"
    elseif(heatSize == 2)
      dynamicData += "\n\t* near a good fire"
    elseif(heatSize == 3)
      dynamicData += "\n\t* near a roaring fire"
    endif
    float heatDistance = _Frost_CurrentHeatSourceDistance.GetValue()
    if(heatDistance<0)
      dynamicData += ""
    elseif(heatDistance <= 300) 
      dynamicData += "\n\t* is very close to the fire"
    elseif(heatDistance<=450)
      dynamicData += "\n\t* is close to the fire"
    else 
      dynamicData += "\n\t* is at the edge of the fire's heat"
    endif

    int whereAreWe = Weather.GetSkyMode()
    ; 0 - No sky 
    ; 1 - Interior
    ; 2 - Skydome only
    ; 3 - Full sky
    if(whereAreWe>1 && _Frost_IsTakingShelter.GetValue()==2)
      dynamicData += "\n\t* is sheltered by things overhead"
    endif
    
    int playerWetness = _Frost_WetLevel.GetValueInt()
    if(playerWetness==0) 
      dynamicData += "\n\t* is dry"
    elseif(playerWetness==1)
      dynamicData += "\n\t* is damp"
    elseif(playerWetness==2)
      dynamicData += "\n\t* is wet"
    else
      dynamicData += "\n\t* is drenched"
    endif
    string actorName = main.GetActorName(akActor)

    int playersExposureLevel = _Frost_ExposureLevel.GetValueInt()
    if(playersExposureLevel==-1)
      dynamicData += "\n\t* " + actorName + " is completely warm"
    elseif(playersExposureLevel==0)
      dynamicData += "\n\t* " + actorName + " is warm"
    elseif(playersExposureLevel == 1)
      dynamicData += "\n\t* " + actorName + " is comfortable with the temprature"
    elseif(playersExposureLevel == 2)
      dynamicData += "\n\t* " + actorName + " is cold"
    elseif(playersExposureLevel == 3)
      dynamicData += "\n\t* " + actorName + " is very cold"
    elseif(playersExposureLevel == 4)
      dynamicData += "\n\t* " + actorName + " is freezing, dangerously cold"
    elseif(playersExposureLevel == 5)
      dynamicData += "\n\t* " + actorName + " is freezing to death"
    elseif(playersExposureLevel == 6)
      dynamicData += "\n\t* " + actorName + " is too cold, nearing hypothermia, they are nearing death!"
    endif

    ; what will the baseline exposure of the player be if they don't take any actions, stand near fires etc

    float playersBaseline = _Frost_ExposureTarget.GetValue()
    if(playersBaseline<20)
      dynamicData += "\n\t* " + actorName + " finds the weather warm"
    elseif(playersBaseline<40)
      dynamicData += "\n\t* " + actorName + " finds the weather comfortable"
    elseif(playersBaseline<60)
      dynamicData += "\n\t* " + actorName + " finds the weather cold"
    elseif(playersBaseline<80)
      dynamicData += "\n\t* " + actorName + " finds the weather very cold"
    elseif(playersBaseline<100)
      dynamicData += "\n\t* the weather dangerously cold for " + actorName + ", its freezing and going away from heat is very risky"
    else
      dynamicData += "\n\t* the weather is lethally cold for " + actorName + " and they could easily freeze to death"
    endif

    int actorsWarmth = _Frost_AttributeWarmth.GetValueInt()
    ; supposedly 140 is max for orc and nord who get +10 to cold benefit
    string warmthLanguage = ""
    if(actorsWarmth>=130)
      warmthLanguage = "completely warm"
    elseif(actorsWarmth>=110)
      warmthLanguage = "warm"
    elseif(actorsWarmth>=90)
      warmthLanguage = "mild"
    elseif(actorsWarmth>=70)
      warmthLanguage = "chilly"
    elseif(actorsWarmth>=50)
      warmthLanguage = "cold"
    elseif(actorsWarmth>=30)
      warmthLanguage = "very cold"
    else 
      warmthLanguage = "frightenly cold"
    endif
    dynamicData += "\n\t* they feel " + warmthLanguage
    
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
    float clothesWarmth = akActor.GetWarmthRating()
    ; int clothesWarmth = FrostUtil.GetPlayerArmorWarmth()
    if(clothesWarmth>=130)
      dynamicData += "\n\t* their clothing is extremely warm"
    elseif(clothesWarmth>=110)
      dynamicData += "\n\t* their clothing is very warm"
    elseif(clothesWarmth>=90)
      dynamicData += "\n\t* their clothing is kind of warm"
    elseif(clothesWarmth>=70 || cTemp>10)
      dynamicData += "\n\t* their clothing is not warm"
    elseif(clothesWarmth>=50)
      dynamicData += "\n\t* their clothing is almost no protection from the" + aTemp + " weather"
    elseif(clothesWarmth>=30)
      dynamicData += "\n\t* their clothing is not protecting them from the" + aTemp + " weather"
    else 
      dynamicData += "\n\t* their clothing situation leaves them completely exposed to the" + aTemp + " weather"
    endif
    
    ; int playerArmorCoverage = FrostUtil.GetPlayerArmorCoverage()
    ; if(playerArmorCoverage>=130)
    ;   dynamicData += "\n\t* their clothing has great protection from the wind and rain"
    ; elseif(playerArmorCoverage>=110)
    ;   dynamicData += "\n\t* their clothing has good protection from the wind and rain"
    ; elseif(playerArmorCoverage>=90)
    ;   dynamicData += "\n\t* their clothing has some protection from the wind and rain"
    ; elseif(playerArmorCoverage>=70 || cTemp>10)
    ;   dynamicData += "\n\t* their clothing has little protection from the wind and rain"
    ; elseif(playerArmorCoverage>=50)
    ;   dynamicData += "\n\t* their clothing offers miniscule protection from the wind and rain"
    ; elseif(playerArmorCoverage>=30)
    ;   dynamicData += "\n\t* their clothing is basically no protection from the wind and rain"
    ; else 
    ;   dynamicData += "\n\t* their clothing situation is is completely vulnerable to the wind and rain"
    ; endif

    int playerCoverage = _Frost_AttributeCoverage.GetValueInt()
    if(playerCoverage>=130)
      dynamicData += "\n\t* they have great protection from the wind and rain by some combination of their clothes or environment"
    elseif(playerCoverage>=100)
      dynamicData += "\n\t* they are have good protection from the wind and rain by some combination of their clothes or environment"
    elseif(playerCoverage>=60)
      dynamicData += "\n\t* they have some pretty nice protection from the wind and rain because of their clothes or the things around them"
    elseif(playerCoverage>=30)
      dynamicData += "\n\t* they have some protection from the wind and rain because of their clothes or the things around them"
    elseif(playerCoverage>=10)
      dynamicData += "\n\t* they have little protection from the wind and rain"
    else
      dynamicData += "\n\t* they are completely exposed to any wind and rain"
    endif
  Endif

  aiff.SetActorVariable(akActor, "EnvironmentalAwarenessDynamicData", dynamicData)
EndFunction