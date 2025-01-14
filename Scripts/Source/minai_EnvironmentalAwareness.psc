scriptname minai_EnvironmentalAwareness extends Quest


minai_Util  MinaiUtil
minai_MainQuestController main
minai_AIFF aiff

function Maintenance(minai_MainQuestController _main)
    main = _main
    MinaiUtil = (self as Quest) as minai_Util
    MinaiUtil.Info("Initializing Dirt and Blood minAI Module.")















    /Actor_Script
    if (Bob.GetLevel() > Game.GetPlayer().GetLevel())
        Debug.Trace("Bob is higher level than the player!")
      endIf

      Race PlayerRace = Game.GetPlayer().GetRace()
    
  if daedra.GetRelationshipRank(Game.GetPlayer()) >= 1
      Debug.Trace("Daedra likes the player")
    endIf

      4: Lover
      3: Ally
      2: Confidant
      1: Friend
      0: Acquaintance
      -1: Rival
      -2: Foe
      -3: Enemy
      -4: Archnemesis


; Is the actor sitting?
if (Sleepy.GetSitState() != 0)
    Debug.Trace("Sleepy is sitting (or wants to sit, or wants to get up)!")
  endIf


  ActorID.IsInFurnitureState    Returns 1 if the specified actor is currently in the selected furniture state.

  Lay
  Lean
  Sit



  ; Is the actor sleeping?
if (Sleepy.GetSleepState() == 3)
    Debug.Trace("Sleepy is sleeping!")
  endIf

  0 - Not sleeping
  1 (loading sleep idle)
  2 - Not sleeping, wants to sleep
  3 - Sleeping
  4 - Sleeping, wants to wake




  Bool IsOverEncumbered()

  Armor GetEquippedArmorInSlot(Int aiSlot)

  ; Is Nate on a mount?
bool nateOnMount = Nate.IsOnMount()



Bool IsSwimming()


OnLycanthropyStateChanged(Bool abIsWerewolf)

    Event received when the lycanthropy state of this actor changes

OnPlayerFastTravelEnd(Float afTravelGameTimeHours)

    Event received when the player finishes fast travel.

OnVampirismStateChanged(bool abIsVampire)

    Event received when the vampirism state of this actor changes

OnVampireFeed(Actor akTarget)

    Event received when this actor feeds on another, as a vampire


      Event OnEnterBleedout()
        Debug.Trace("We entered bleedout...")
      endEvent