DROP TABLE IF EXISTS minai_threads;

CREATE TABLE IF NOT EXISTS minai_threads (
        prev_scene_id character varying(256),
        curr_scene_id character varying(256),
        female_actors text,
        male_actors text,
        victim_actors text,
        thread_id integer PRIMARY KEY,
        framework character varying(256),
        fallback text;

CREATE TABLE IF NOT EXISTS custom_context (
        modName TEXT NOT NULL,
        eventKey TEXT NOT NULL,
        eventValue TEXT NOT NULL,
        ttl INT,
        expiresAt INT,
        npcName TEXT NOT NULL,
        PRIMARY KEY (modName, eventKey);

CREATE TABLE IF NOT EXISTS custom_actions (
        actionName TEXT NOT NULL,
        actionPrompt TEXT NOT NULL,
        targetDescription TEXT NOT NULL,
        targetEnum TEXT NOT NULL,
        enabled INT,
        ttl INT,
        npcName TEXT NOT NULL,
        expiresAt INT,
        PRIMARY KEY (actionName, actionPrompt);


CREATE TABLE IF NOT EXISTS equipment_description (
        baseFormId TEXT NOT NULL,
        modName TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        is_restraint INTEGER DEFAULT 0,
        body_part TEXT,
        hidden_by TEXT,
        is_enabled INTEGER DEFAULT 1,
        PRIMARY KEY (baseFormId, modName);

CREATE TABLE IF NOT EXISTS tattoo_description (
        section TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        hidden_by TEXT,
        PRIMARY KEY (section, name);

DROP TABLE IF EXISTS minai_items CASCADE;

CREATE TABLE IF NOT EXISTS minai_items (
                id SERIAL PRIMARY KEY,
                item_id TEXT NOT NULL,
                file_name TEXT NOT NULL,
                name TEXT NOT NULL,
                description TEXT,
                is_available BOOLEAN DEFAULT TRUE,
                item_type TEXT DEFAULT 'Item',
                category TEXT,
                mod_index TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(item_id, file_name);


ALTER TABLE speech ADD COLUMN IF NOT EXISTS mood TEXT; 
ALTER TABLE speech ADD COLUMN IF NOT EXISTS emotion TEXT; 
ALTER TABLE speech ADD COLUMN IF NOT EXISTS emotion_intensity TEXT; 

DROP FUNCTION IF EXISTS public.sql_exec2(text) CASCADE;

CREATE FUNCTION public.sql_exec2(text) returns text 
 language plpgsql volatile 
 AS 
 $$
 BEGIN
  EXECUTE $1;
  RETURN $1;
 END;
 $$; 

SELECT sql_exec2('ALTER TABLE "'||pgc.relname||'" SET (autovacuum_enabled = on, toast.autovacuum_enabled = on) '||';')
 FROM pg_catalog.pg_class pgc
 LEFT JOIN pg_catalog.pg_namespace pgn ON pgn.oid = pgc.relnamespace
 WHERE (pgc.relkind ='r')
 AND (pgn.nspname='public'); 



