--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: chat; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE chat (
    id integer NOT NULL,
    demo_id integer NOT NULL,
    "from" character varying(255) NOT NULL,
    text character varying(255) NOT NULL,
    "time" integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);


--
-- Name: chat_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE chat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: chat_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE chat_id_seq OWNED BY chat.id;


--
-- Name: demos; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE demos (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    url character varying(255) NOT NULL,
    map character varying(255) NOT NULL,
    red character varying(255) NOT NULL,
    blu character varying(255) NOT NULL,
    uploader integer NOT NULL,
    duration integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    backend character varying(255) NOT NULL,
    path character varying(255) NOT NULL,
    "scoreBlue" integer DEFAULT 0 NOT NULL,
    "scoreRed" integer DEFAULT 0 NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    server character varying(255) DEFAULT ''::character varying NOT NULL,
    nick character varying(255) DEFAULT ''::character varying NOT NULL,
    deleted_at timestamp without time zone,
    "playerCount" integer DEFAULT 0 NOT NULL,
    hash character varying(255) DEFAULT ''::character varying NOT NULL,
    blue_team_id integer,
    red_team_id integer
);


--
-- Name: demos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE demos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: demos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE demos_id_seq OWNED BY demos.id;


--
-- Name: kills; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE kills (
    id integer NOT NULL,
    demo_id integer NOT NULL,
    attacker_id integer NOT NULL,
    assister_id integer NOT NULL,
    victim_id integer NOT NULL,
    weapon character varying(255) NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);


--
-- Name: kills_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE kills_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: kills_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE kills_id_seq OWNED BY kills.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE migrations (
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: players; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE players (
    id integer NOT NULL,
    demo_id integer NOT NULL,
    demo_user_id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(255) NOT NULL,
    team character varying(255) NOT NULL,
    class character varying(255) NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);


--
-- Name: players_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE players_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: players_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE players_id_seq OWNED BY players.id;


--
-- Name: storage_keys; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE storage_keys (
    id integer NOT NULL,
    userid integer NOT NULL,
    type character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);


--
-- Name: storage_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE storage_keys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: storage_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE storage_keys_id_seq OWNED BY storage_keys.id;


--
-- Name: teams; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE teams (
    id integer NOT NULL,
    profile_id integer NOT NULL,
    name character varying(255) NOT NULL,
    tag character varying(255) NOT NULL,
    avatar character varying(255) NOT NULL,
    steam character varying(255) NOT NULL,
    league character varying(255) NOT NULL,
    division character varying(255) NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    CONSTRAINT teams_league_check CHECK (((league)::text = ANY ((ARRAY['ugc'::character varying, 'etf2l'::character varying])::text[])))
);


--
-- Name: teams_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE teams_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: teams_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE teams_id_seq OWNED BY teams.id;


--
-- Name: upload_blacklist; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE upload_blacklist (
    id integer NOT NULL,
    uploader_id integer NOT NULL,
    reason character varying,
    block boolean
);


--
-- Name: upload_blacklist_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE upload_blacklist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: upload_blacklist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE upload_blacklist_id_seq OWNED BY upload_blacklist.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    steamid character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    avatar character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY chat ALTER COLUMN id SET DEFAULT nextval('chat_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY demos ALTER COLUMN id SET DEFAULT nextval('demos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY kills ALTER COLUMN id SET DEFAULT nextval('kills_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY players ALTER COLUMN id SET DEFAULT nextval('players_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY storage_keys ALTER COLUMN id SET DEFAULT nextval('storage_keys_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY teams ALTER COLUMN id SET DEFAULT nextval('teams_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY upload_blacklist ALTER COLUMN id SET DEFAULT nextval('upload_blacklist_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Name: chat_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY chat
    ADD CONSTRAINT chat_pkey PRIMARY KEY (id);


--
-- Name: demos_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY demos
    ADD CONSTRAINT demos_pkey PRIMARY KEY (id);


--
-- Name: kills_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY kills
    ADD CONSTRAINT kills_pkey PRIMARY KEY (id);


--
-- Name: players_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY players
    ADD CONSTRAINT players_pkey PRIMARY KEY (id);


--
-- Name: storage_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY storage_keys
    ADD CONSTRAINT storage_keys_pkey PRIMARY KEY (id);


--
-- Name: teams_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY teams
    ADD CONSTRAINT teams_pkey PRIMARY KEY (id);


--
-- Name: upload_blacklist_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY upload_blacklist
    ADD CONSTRAINT upload_blacklist_pkey PRIMARY KEY (id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: alias_trgm_idx; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX alias_trgm_idx ON players USING gist (name gist_trgm_ops);


--
-- Name: demos_blue_team_id_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX demos_blue_team_id_index ON demos USING btree (blue_team_id);


--
-- Name: demos_hash_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX demos_hash_index ON demos USING btree (hash);


--
-- Name: demos_playercount_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX demos_playercount_index ON demos USING btree ("playerCount");


--
-- Name: demos_red_team_id_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX demos_red_team_id_index ON demos USING btree (red_team_id);


--
-- Name: demos_uploader_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX demos_uploader_index ON demos USING btree (uploader);


--
-- Name: kills_demo_id_idx; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX kills_demo_id_idx ON kills USING btree (demo_id);


--
-- Name: players_class_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX players_class_index ON players USING btree (class);


--
-- Name: players_demo_id_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX players_demo_id_index ON players USING btree (demo_id);


--
-- Name: players_name_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX players_name_index ON players USING btree (name);


--
-- Name: players_user_id_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX players_user_id_index ON players USING btree (user_id);


--
-- Name: teams_id_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX teams_id_index ON teams USING btree (id);


--
-- Name: teams_profile_id_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX teams_profile_id_index ON teams USING btree (profile_id);


--
-- Name: upload_blacklist_uploader_id_idx; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX upload_blacklist_uploader_id_idx ON upload_blacklist USING btree (uploader_id);


--
-- PostgreSQL database dump complete
--

