scriptname minai_EnvironmentalAwareness extends Quest

; Support for Frostfall
bool bHasFrostfall
; Frostfall variables
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

; Tracked actors map
int iActorMap 

; Utilities and references
minai_Util MinaiUtil
minai_MainQuestController main
minai_AIFF aiff
Actor playerRef
string playerName
minai_Followers followers
bool bIsNight = false

; Get the current day/night state based on game time
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

Function SetFrostfallContext()
  if (bHasFrostfall)
    actor akActor = playerRef
    ; Store temperature as a semantic value
    float currentTemp = _Frost_CurrentTemperature.GetValueInt()
    string temperature = ""
    
    if (currentTemp < -14)
      temperature = "frigid and deadly"
    elseif (currentTemp < -9)
      temperature = "bone-chilling"
    elseif (currentTemp < -4)
      temperature = "biting cold"
    elseif (currentTemp < 1)
      temperature = "cold"
    elseif (currentTemp < 6)
      temperature = "chilly"
    elseif (currentTemp < 10)
      temperature = "cool"
    elseif (currentTemp == 10)
      temperature = "temperate"
    elseif (currentTemp < 15)
      temperature = "pleasant"
    elseif (currentTemp < 18)
      temperature = "warm"
    else 
      temperature = "hot"
    endif
    
    aiff.SetActorVariable(akActor, "temperature", temperature)
    
    ; Weather severity
    Weather currentWeather = Weather.GetCurrentWeather()
    bool isWeatherSevere = _Frost_SevereWeatherList.HasForm(currentWeather)
    
    if (isWeatherSevere)
      aiff.SetActorVariable(akActor, "weatherSeverity", "The weather is severe and dangerous. People must be careful!")
    else
      aiff.SetActorVariable(akActor, "weatherSeverity", "")
    endif
    
    ; Shelter status - using direct boolean
    aiff.SetActorVariable(akActor, "isSheltered", _Frost_IsTakingShelter.GetValueInt() == 1)
    
    ; Wetness level
    int wetLevel = _Frost_WetLevel.GetValueInt()
    string wetnessLevel = ""
    
    if (wetLevel >= 80)
      wetnessLevel = "soaking wet"
    elseif (wetLevel >= 60)
      wetnessLevel = "very wet"
    elseif (wetLevel >= 40)
      wetnessLevel = "wet"
    elseif (wetLevel >= 20)
      wetnessLevel = "damp"
    else
      wetnessLevel = "dry"
    endif
    
    aiff.SetActorVariable(akActor, "wetnessLevel", wetnessLevel)
    
    ; Exposure level
    float exposureLevel = _Frost_ExposureLevel.GetValue()
    
    if (exposureLevel >= 80)
      aiff.SetActorVariable(akActor, "exposureLevel", "You are freezing to death, seek warmth immediately!")
    elseif (exposureLevel >= 60)
      aiff.SetActorVariable(akActor, "exposureLevel", "You are dangerously cold, frostbite is setting in.")
    elseif (exposureLevel >= 40)
      aiff.SetActorVariable(akActor, "exposureLevel", "You are very cold.")
    elseif (exposureLevel >= 20)
      aiff.SetActorVariable(akActor, "exposureLevel", "You feel cold.")
    else
      aiff.SetActorVariable(akActor, "exposureLevel", "")
    endif
    
    ; Baseline exposure
    float exposureRate = _Frost_ExposureTarget.GetValue()
    
    if (exposureRate > 0.3)
      aiff.SetActorVariable(akActor, "baselineExposure", "The cold is rapidly seeping into your bones.")
    elseif (exposureRate > 0.1)
      aiff.SetActorVariable(akActor, "baselineExposure", "The cold is gradually affecting you.")
    elseif (exposureRate > 0)
      aiff.SetActorVariable(akActor, "baselineExposure", "The cold is slowly affecting you.")
    elseif (exposureRate < -0.3)
      aiff.SetActorVariable(akActor, "baselineExposure", "You are warming up rapidly.")
    elseif (exposureRate < -0.1)
      aiff.SetActorVariable(akActor, "baselineExposure", "You are gradually warming up.")
    elseif (exposureRate < 0)
      aiff.SetActorVariable(akActor, "baselineExposure", "You are slowly warming up.")
    else
      aiff.SetActorVariable(akActor, "baselineExposure", "")
    endif
    
    ; Warmth rating
    int warmthRating = _Frost_AttributeWarmth.GetValueInt()
    
    if (warmthRating >= 80)
      aiff.SetActorVariable(akActor, "warmthRating", "extremely warmly")
    elseif (warmthRating >= 60)
      aiff.SetActorVariable(akActor, "warmthRating", "very warmly")
    elseif (warmthRating >= 40)
      aiff.SetActorVariable(akActor, "warmthRating", "warmly")
    elseif (warmthRating >= 20)
      aiff.SetActorVariable(akActor, "warmthRating", "somewhat warmly")
    else
      aiff.SetActorVariable(akActor, "warmthRating", "poorly for the weather")
    endif
    
    ; Coverage rating
    int coverageRating = _Frost_AttributeCoverage.GetValueInt()
    
    if (coverageRating >= 90)
      aiff.SetActorVariable(akActor, "coverageRating", "is completely covered, with no exposed skin")
    elseif (coverageRating >= 70)
      aiff.SetActorVariable(akActor, "coverageRating", "has most skin covered")
    elseif (coverageRating >= 50)
      aiff.SetActorVariable(akActor, "coverageRating", "has some exposed skin")
    elseif (coverageRating >= 30)
      aiff.SetActorVariable(akActor, "coverageRating", "has a lot of exposed skin")
    else
      aiff.SetActorVariable(akActor, "coverageRating", "is barely covered")
    endif
  endif
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
  
  playerRef = Game.GetPlayer()
  ; Store location data
  SetLocationData(playerRef)
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
  string actorName = Main.GetActorName(akActor)
  ; Some variables are only relevant for the player
  if (akActor == playerRef)   
    ; Store day state
    string dayState = GetDayState()
    if (dayState != "")
      aiff.SetActorVariable(akActor, "dayState", dayState)
    endif

    ;  Snow is 3, Rain is 2, Cloudy is 1, Clear is 0, and -1 is used 
    aiff.SetActorVariable(akActor, "weatherClassification", Weather.GetCurrentWeather().GetClassification() as string)
    ; Store moon data
    StoreMoonData(akActor)
  
    ; Weather data
    int weatherInt = Weather.GetCurrentWeather().GetClassification()
    aiff.SetActorVariable(akActor, "weatherClassification", weatherInt as string)
    SetFrostfallContext()
  else ; NPCs
    ; Check if bribed - using direct boolean from function
    aiff.SetActorVariable(akActor, "isBribed", akActor.IsBribed())

    ; Check if intimidated - using direct boolean from function
    aiff.SetActorVariable(akActor, "isIntimidated", akActor.IsIntimidated())
    
    ; Check relationship rank with player
    aiff.SetActorVariable(akActor, "relationshipRank", akActor.GetRelationshipRank(playerRef) as string)

    bool isKid = akActor.IsChild()
    bool hasAFamily = akActor.HasFamilyRelationship()
    ; Child status - using direct boolean
    aiff.SetActorVariable(akActor, "isChild", isKid)
    
    ; Family relationships - using direct boolean
    aiff.SetActorVariable(akActor, "hasFamily", hasAFamily)
      
    ; Sleep state
    int sleepState = akActor.GetSleepState()
    if (sleepState == 3)
      aiff.SetActorVariable(akActor, "sleepState", "sleeping deeply")
    elseif (sleepState == 2)
        aiff.SetActorVariable(akActor, "sleepState", "sleeping")
    elseif (sleepState == 1)
        aiff.SetActorVariable(akActor, "sleepState", "resting")
    else
      aiff.SetActorVariable(akActor, "sleepState", "")
    endif
     ; Get and store actor's class/career name as raw data
    ActorBase akBase = akActor.GetBaseObject() as ActorBase
    if (akBase)
      Class aClass = akBase.GetClass()
      if (aClass)
        string careerName = aClass.GetName()
        aiff.SetActorVariable(akActor, "career", careerName)
      endif
    endif
  endif
  ;;;;;;;;;;;;;;;;;;;;;;;; Applicable to everyone

  ; Character level
  aiff.SetActorVariable(akActor, "level", akActor.GetLevel() as string)
  
  ; Character state data - using direct booleans
  aiff.SetActorVariable(akActor, "isSneaking", akActor.IsSneaking())
  aiff.SetActorVariable(akActor, "isSwimming", akActor.IsSwimming())
  aiff.SetActorVariable(akActor, "isOnMount", akActor.IsOnMount())
  aiff.SetActorVariable(akActor, "isEncumbered", akActor.IsOverEncumbered())
  ; Sitting state - store raw value and let server handle formatting
  aiff.SetActorVariable(akActor, "sitState", akActor.GetSitState() as string)
EndFunction

; Store moon phase data as raw values
function StoreMoonData(actor akActor)
  ; Store the moon phase (0-7)
  int phase = GetCurrentMoonPhase()
  aiff.SetActorVariable(akActor, "moonPhase", phase as string)
  
  ; Store whether it's night or day
  aiff.SetActorVariable(akActor, "isNight", bIsNight)
  
  ; Store whether there are one or two moons visible
  string moonCount = GetCurrentMoonSync()
  aiff.SetActorVariable(akActor, "moonCount", moonCount)
endfunction

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

; Get current moon phase (0-7)
int Function GetCurrentMoonPhase()
  Int GameDaysPassed = GetPassedGameDays()
  Int GameHoursPassed = GetPassedGameHours()
  If (GameHoursPassed >= 12.0)
    GameDaysPassed += 1
  EndIf
  int PhaseTest = GameDaysPassed % 24 ;A full cycle through the moon phases lasts 24 days
  If PhaseTest >= 22 || PhaseTest == 0
    Return 7
  ElseIf PhaseTest < 4
    Return 0
  ElseIf PhaseTest < 7
    Return 1
  ElseIf PhaseTest < 10
    Return 2
  ElseIf PhaseTest < 13
    Return 3
  ElseIf PhaseTest < 16
    Return 4
  ElseIf PhaseTest < 19
    Return 5
  ElseIf PhaseTest < 22
    Return 6
  EndIf
EndFunction

; Get moon count ("moon" or "two moons")
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

; New function to handle location data
function SetLocationData(actor whoCares)
  actor akActor = playerRef
  Location currentLoc = akActor.GetCurrentLocation()
  
  ; Try alternative methods if GetCurrentLocation returns None
  if (!currentLoc)
    ; Try getting location from parent cell
    Cell parentCell = akActor.GetParentCell()
    if (parentCell)
      ; If cell location is None, try getting location directly from cell refs
      int numRefs = parentCell.GetNumRefs(0) ; 0 to get all refs
      int i = 0
      while (!currentLoc && i < numRefs && i < 20)
        ObjectReference ref = parentCell.GetNthRef(i, 0)
        currentLoc = ref.GetCurrentLocation()
        i += 1
      endwhile
    endif
    
    ; If still no location, try getting from nearby references
    if (!currentLoc)
      ObjectReference nearbyRef = PO3_SKSEFunctions.GetObjectUnderFeet(akActor)
      if (nearbyRef)
        currentLoc = nearbyRef.GetCurrentLocation()
      endif
    endif
  endif
  
  if (currentLoc)
    ; Store current location name
    aiff.SetActorVariable(akActor, "currentLocation", currentLoc.GetName())
    
    ; Store hold information if available
    Location holdLoc = PO3_SKSEFunctions.GetParentLocation(currentLoc)
    if (holdLoc)
      aiff.SetActorVariable(akActor, "currentHold", holdLoc.GetName())
    else
      ; We will use the last known hold if we can't find the current hold
      ; aiff.SetActorVariable(akActor, "currentHold", "")
    endif
  else
    aiff.SetActorVariable(akActor, "currentLocation", "")
  EndIf
  ; Store interior/exterior status
  aiff.SetActorVariable(akActor, "isInterior", akActor.IsInInterior())
  
  ; Store worldspace information
  WorldSpace currentWorld = playerRef.GetWorldSpace()
  if (currentWorld)
    aiff.SetActorVariable(akActor, "currentWorldspace", currentWorld.GetName())
  else
    aiff.SetActorVariable(akActor, "currentWorldspace", "")
  endif
  
  ; Store cell information
  Cell currentCell = akActor.GetParentCell()
  if (currentCell)
    aiff.SetActorVariable(akActor, "currentCell", currentCell.GetName())
  else
    aiff.SetActorVariable(akActor, "currentCell", "")
  endif
  
  ; Build location keywords string
  string locationKeywords = ""
  if (currentLoc)
    ; Add each keyword if present
    if (currentLoc.HasKeywordString("LocTypeCity"))
      locationKeywords += "city~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTown"))
      locationKeywords += "town~"
    endif
    if (currentLoc.HasKeywordString("LocTypeVillage"))
      locationKeywords += "village~"
    endif
    if (currentLoc.HasKeywordString("LocTypeSettlement"))
      locationKeywords += "settlement~"
    endif
    if (currentLoc.HasKeywordString("LocTypeDungeon"))
      locationKeywords += "dungeon~"
    endif
    if (currentLoc.HasKeywordString("LocTypeRuin"))
      locationKeywords += "ruin~"
    endif
    if (currentLoc.HasKeywordString("LocTypeFort"))
      locationKeywords += "fort~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTemple"))
      locationKeywords += "temple~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTavern"))
      locationKeywords += "tavern~"
    endif
    if (currentLoc.HasKeywordString("LocTypeShop"))
      locationKeywords += "shop~"
    endif
    if (currentLoc.HasKeywordString("LocTypeHouse"))
      locationKeywords += "house~"
    endif
    if (currentLoc.HasKeywordString("LocTypeFarm"))
      locationKeywords += "farm~"
    endif
    if (currentLoc.HasKeywordString("LocTypeMine"))
      locationKeywords += "mine~"
    endif
    if (currentLoc.HasKeywordString("LocTypeCamp"))
      locationKeywords += "camp~"
    endif
    if (currentLoc.HasKeywordString("LocTypeMilitaryCamp"))
      locationKeywords += "military_camp~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBanditCamp"))
      locationKeywords += "bandit_camp~"
    endif
    if (currentLoc.HasKeywordString("LocTypeDragonLair"))
      locationKeywords += "dragon_lair~"
    endif
    if (currentLoc.HasKeywordString("LocTypeGiantCamp"))
      locationKeywords += "giant_camp~"
    endif
    if (currentLoc.HasKeywordString("LocTypeVampireLair"))
      locationKeywords += "vampire_lair~"
    endif
    if (currentLoc.HasKeywordString("LocTypeWerewolfLair"))
      locationKeywords += "werewolf_lair~"
    endif
    if (currentLoc.HasKeywordString("LocTypeCave"))
      locationKeywords += "cave~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTomb"))
      locationKeywords += "tomb~"
    endif
    if (currentLoc.HasKeywordString("LocTypeCrypt"))
      locationKeywords += "crypt~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBarrow"))
      locationKeywords += "barrow~"
    endif
    if (currentLoc.HasKeywordString("LocTypeNordicRuin"))
      locationKeywords += "nordic_ruin~"
    endif
    if (currentLoc.HasKeywordString("LocTypeDwemerRuin"))
      locationKeywords += "dwemer_ruin~"
    endif
    if (currentLoc.HasKeywordString("LocTypeFalmerLair"))
      locationKeywords += "falmer_lair~"
    endif
    if (currentLoc.HasKeywordString("LocTypePrison"))
      locationKeywords += "prison~"
    endif
    if (currentLoc.HasKeywordString("LocTypeJail"))
      locationKeywords += "jail~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBarracks"))
      locationKeywords += "barracks~"
    endif
    if (currentLoc.HasKeywordString("LocTypePalace"))
      locationKeywords += "palace~"
    endif
    if (currentLoc.HasKeywordString("LocTypeCastle"))
      locationKeywords += "castle~"
    endif
    if (currentLoc.HasKeywordString("LocTypeKeep"))
      locationKeywords += "keep~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTower"))
      locationKeywords += "tower~"
    endif
    if (currentLoc.HasKeywordString("LocTypeLighthouse"))
      locationKeywords += "lighthouse~"
    endif
    if (currentLoc.HasKeywordString("LocTypeMill"))
      locationKeywords += "mill~"
    endif
    if (currentLoc.HasKeywordString("LocTypeStable"))
      locationKeywords += "stable~"
    endif
    if (currentLoc.HasKeywordString("LocTypeSmithy"))
      locationKeywords += "smithy~"
    endif
    if (currentLoc.HasKeywordString("LocTypeForge"))
      locationKeywords += "forge~"
    endif
    if (currentLoc.HasKeywordString("LocTypeSmelter"))
      locationKeywords += "smelter~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTannery"))
      locationKeywords += "tannery~"
    endif
    if (currentLoc.HasKeywordString("LocTypeFishery"))
      locationKeywords += "fishery~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBrewery"))
      locationKeywords += "brewery~"
    endif
    if (currentLoc.HasKeywordString("LocTypeWinery"))
      locationKeywords += "winery~"
    endif
    if (currentLoc.HasKeywordString("LocTypeMeadery"))
      locationKeywords += "meadery~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBakery"))
      locationKeywords += "bakery~"
    endif
    if (currentLoc.HasKeywordString("LocTypeButcherShop"))
      locationKeywords += "butcher_shop~"
    endif
    if (currentLoc.HasKeywordString("LocTypeGeneralStore"))
      locationKeywords += "general_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeClothingStore"))
      locationKeywords += "clothing_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeJewelryStore"))
      locationKeywords += "jewelry_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBookStore"))
      locationKeywords += "book_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypePotionStore"))
      locationKeywords += "potion_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeScrollStore"))
      locationKeywords += "scroll_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeWeaponStore"))
      locationKeywords += "weapon_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeArmorStore"))
      locationKeywords += "armor_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeFoodStore"))
      locationKeywords += "food_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeFurnitureStore"))
      locationKeywords += "furniture_store~"
    endif
    if (currentLoc.HasKeywordString("LocTypeBlackMarket"))
      locationKeywords += "black_market~"
    endif
    if (currentLoc.HasKeywordString("LocTypeThievesGuild"))
      locationKeywords += "thieves_guild~"
    endif
    if (currentLoc.HasKeywordString("LocTypeDarkBrotherhood"))
      locationKeywords += "dark_brotherhood~"
    endif
    if (currentLoc.HasKeywordString("LocTypeCompanionsGuild"))
      locationKeywords += "companions_guild~"
    endif
    if (currentLoc.HasKeywordString("LocTypeCollegeOfWinterhold"))
      locationKeywords += "college_of_winterhold~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfKynareth"))
      locationKeywords += "temple_of_kynareth~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfTalos"))
      locationKeywords += "temple_of_talos~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfArkay"))
      locationKeywords += "temple_of_arkay~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfDibella"))
      locationKeywords += "temple_of_dibella~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfMara"))
      locationKeywords += "temple_of_mara~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfZenithar"))
      locationKeywords += "temple_of_zenithar~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfStendarr"))
      locationKeywords += "temple_of_stendarr~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfJulianos"))
      locationKeywords += "temple_of_julianos~"
    endif
    if (currentLoc.HasKeywordString("LocTypeTempleOfAkatosh"))
      locationKeywords += "temple_of_akatosh~"
    endif
  EndIf
  
  ; Store the location keywords string
  aiff.SetActorVariable(akActor, "locationKeywords", locationKeywords)
  aiff.SetActorVariable(akActor, "isTrespassing", playerRef.IsTrespassing())
  main.Info("Set location data")

endfunction