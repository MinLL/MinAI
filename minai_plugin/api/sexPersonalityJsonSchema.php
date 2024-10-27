<?php

$sexPersonalityJsonSchema = '{
    "type": "object",
    "properties": {
        "orientation": {
            "type": "string",
            "description": "The character\'s sexual orientation, which describes their emotional, romantic, or sexual attraction to others.",
            "enum": [
                "heterosexual",
                "homosexual",
                "bisexual"
            ]
        },
        "relationshipStyle": {
            "type": "string",
            "description": "The character\'s preferred style of romantic relationship, which can impact their behavior and expectations in intimate relationships. 
monogamous - exclusive commitment to one partner; 
polyamorous - non-exclusive commitment to multiple partners; 
open relationship - commitment to one partner with freedom to explore outside relationships; 
swinging - non-monogamous relationships with mutual consent.",
            "enum": [
                "monogamous",
                "polyamorous",
                "open relationship",
                "swinging"
            ]
        },
        "preferredSexPositions": {
            "type": "array",
            "description": "A list of the character\'s favorite or preferred sex positions, which can provide insight into their physical and emotional comfort levels. Use well known positions keywords.",
            "items": [
                {
                    "type": "string"
                }
            ]
        },
        "speakStyleDuringSex": {
            "type": "string",
            "enum": [
                "dirty talk",
                "sweet talk",
                "sensual whispering",
                "dominant talk",
                "submissive talk",
                "teasing talk",
                "erotic storytelling",
                "breathless gasps",
                "sultry seduction",
                "playful banter"
            ],
            "description": "Based on character\'s personality and potential sexual behavior select character\'s communication style during sex.
Dirty Talk: Using explicit and provocative language to describe the act of sex and the partner\'s body.
Sweet Talk: Using affectionate and endearing language to express love and intimacy.
Sensual Whispering: Whispering sensual and erotic phrases in a soft and gentle tone.
Dominant Talk: Using commanding and authoritative language to take control of the situation.
Submissive Talk: Using submissive and obedient language to surrender to one\'s partner.
Teasing Talk: Using playful and flirtatious language to build anticipation and desire.
Erotic Storytelling: Telling erotic stories or fantasies to create a sensual atmosphere.
Breathless Gasps: Using short, breathless gasps and moans to express intense pleasure.
Sultry Seduction: Using smooth and seductive language to entice and seduce one\'s partner.
Playful Banter: Using lighthearted and playful language to create a fun and relaxed atmosphere."
        },
        "sexualBehavior": {
            "type": "array",
            "description": "The character\'s specific behaviors or activities they engage in during sex, which can indicate their desires and boundaries. 2-3 words. Consider character\'s orienation and sex.
cum play(playing with semen) - female, bisexual or homosexual males;
swallowing(ingesting semen) - female, bisexual or homosexual males;
spitting(expelling semen) - female, bisexual or homosexual males;
facials (receiving semen on the face) - female, bisexual or homosexual males;
cum tasting - female, bisexual or homosexual males;
cum licking - female, bisexual or homosexual males;
nipple play (stimulating nipples);
threesomes (sex with three people);
foursomes (sex with four people);
moresomes (sex with multiple people);
gangbangs (sex with multiple men);
orgies (sex with multiple people);
breast play - male, bisexual or homosexual female;
public sex (sex in public places);
outdoor sex (sex in outdoor settings);
voyeurism (watching others engage in sex);
exhibitionism (being watched during sex);
cuckolding (watching one\'s partner have sex with someone else);
foot fetish (arousal from feet);
LLM can come up with other different sexual behaviors;",
            "items": [
                {
                    "type": "string"
                }
            ]
        },
        "sexFantasies": {
            "type": "array",
            "description": "A list of the character\'s daring, risky erotic and sexual fantasies.",
            "items": [
                {
                    "type": "string"
                }
            ]
        },
        "sexPersonalityTraits": {
            "type": "array",
            "description": "The character\'s personality traits that influence their behavior and interactions during sex, which can impact their relationships and emotional well-being. 1-2 words.",
            "items": [
                {
                    "type": "string"
                }
            ]
        }
    },
    "required": [
        "orientation",
        "relationshipStyle",
        "preferredSexPositions",
        "speakStyleDuringSex",
        "sexualBehavior",
        "sexFantasies",
        "sexPersonalityTraits"
    ]
}';

?>
