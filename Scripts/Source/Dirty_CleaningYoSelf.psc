Scriptname Dirty_CleaningYoSelf extends activemagiceffect  

GlobalVariable Property Dirty_SoapRequired Auto 
Actor Property PlayerRef Auto
;MiscObject Property Dirty_Soap Auto
Spell Property Dirty_SoapEffectSpell Auto
Spell Property Dirty_Spell_Dirt1 Auto
Spell Property Dirty_Spell_Dirt2 Auto
Spell Property Dirty_Spell_Dirt3 Auto
Spell Property Dirty_Spell_Dirt4 Auto
Spell Property Dirty_Spell_Blood1 Auto
Spell Property Dirty_Spell_Blood2 Auto
Spell Property Dirty_Spell_Blood3 Auto
Spell Property Dirty_Spell_Blood4 Auto
Spell Property Dirty_Spell_Clean Auto
Sound Property Dirty_WashingMarkerSound auto
Message Property Dirty_MessageBathWeapon Auto
Message Property Dirty_MessageBathCombat Auto
Message Property Dirty_MessageBathSoap Auto
Form[] Clothing
Formlist property Dirty_ListofSoaps Auto
GlobalVariable Property Dirty_WashingAutomaticClothes Auto
GlobalVariable Property Dirty_SoundEffect Auto
Dirty_BathingQuest Property Dirty_BatheQuest Auto
GlobalVariable Property Dirty_WaterRequired Auto
Message Property Dirty_MessageWater Auto

formlist property Dirty_ListofWaterfalls256 auto
formlist property Dirty_ListofWaterfalls512 auto
formlist property Dirty_ListofWaterfalls2048 auto
formlist property Dirty_ListofWaterfalls4096 auto

import PO3_SKSEFunctions

Event OnEffectStart(Actor akTarget, Actor akCaster)
	If (!Dirty_BatheQuest) 
		Dirty_BatheQuest = Game.GetFormFromFile(0x00000DC3, "Dirt and Blood - Dynamic Visuals.esp") as Dirty_BathingQuest
        Debug.trace("DIRTY quest is now " + Dirty_BatheQuest)
	endIf
	If PlayerRef.IsInCombat()
		Dirty_MessageBathCombat.Show()
	elseif PlayerRef.IsWeaponDrawn()
		Dirty_MessageBathWeapon.Show()

	Elseif Dirty_WaterRequired.GetValue() == 1
			Spell WadeInWater1 = Game.GetFormFromFile(0x00000D64, "WadeInWater.esp") as Spell
			Spell WadeInWater2 = Game.GetFormFromFile(0x00000D65, "WadeInWater.esp") as Spell
			ObjectReference StaticItem

			StaticItem = Game.FindClosestReferenceOfAnyTypeInList(Dirty_ListofWaterfalls256, PlayerRef.GetPositionX(), PlayerRef.GetPositionY(), (PlayerRef.GetPositionZ() + 256), 312.0)
					If StaticItem == None
						StaticItem = Game.FindClosestReferenceOfAnyTypeInList(Dirty_ListofWaterfalls512, PlayerRef.GetPositionX(), PlayerRef.GetPositionY(), (PlayerRef.GetPositionZ() + 512), 512.0)
					If StaticItem == None
						StaticItem = Game.FindClosestReferenceOfAnyTypeInList(Dirty_ListofWaterfalls2048, PlayerRef.GetPositionX(), PlayerRef.GetPositionY(), (PlayerRef.GetPositionZ() + 2048), 1000.0)
					If StaticItem == None
						StaticItem = Game.FindClosestReferenceOfAnyTypeInList(Dirty_ListofWaterfalls4096, PlayerRef.GetPositionX(), PlayerRef.GetPositionY(), (PlayerRef.GetPositionZ() + 4096), 1000.0)
					endif
					endif
					endif

		If PlayerRef.HasSpell(WadeInWater1) || PlayerRef.HasSpell(WadeInWater2) || StaticItem != None
				int handle = ModEvent.Create("MinAI_RequestResponse")
				if (handle)
					ModEvent.PushString(handle, PlayerRef.GetDisplayName() + " stripped down naked and started bathing.")
					ModEvent.PushString(handle, "minai_bathing")
					ModEvent.PushString(handle, "everyone")
					ModEvent.Send(handle)
				endIf
    	   	 Dirty_BatheQuest.PlayBatheAnimation(PlayerRef, true, true)
		else
		Dirty_MessageWater.Show()
		endif

	Else
    	   	 Dirty_BatheQuest.PlayBatheAnimation(PlayerRef, true, true)
	EndIf
EndEvent
