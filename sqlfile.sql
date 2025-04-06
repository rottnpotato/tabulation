--
-- PostgreSQL database dump
--

-- Dumped from database version 17.0
-- Dumped by pg_dump version 17.0

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activities (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    user_id bigint,
    action_type character varying(255) NOT NULL,
    entity_type character varying(255),
    entity_id bigint,
    description text NOT NULL,
    icon character varying(255),
    metadata jsonb,
    ip_address character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.activities OWNER TO postgres;

--
-- Name: activities_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activities_id_seq OWNER TO postgres;

--
-- Name: activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activities_id_seq OWNED BY public.activities.id;


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    user_id bigint,
    user_role character varying(255),
    action_type character varying(255) NOT NULL,
    target_entity character varying(255),
    target_id bigint,
    details text NOT NULL,
    ip_address inet,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.audit_logs OWNER TO postgres;

--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.audit_logs_id_seq OWNER TO postgres;

--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    weight integer DEFAULT 100 NOT NULL,
    max_score numeric(8,2) DEFAULT '100'::numeric NOT NULL,
    scoring_type character varying(255) DEFAULT 'percentage'::character varying NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    criteria jsonb,
    active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categories OWNER TO postgres;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO postgres;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: contestant_images; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contestant_images (
    id bigint NOT NULL,
    contestant_id bigint NOT NULL,
    image_path character varying(255) NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    display_order smallint DEFAULT '0'::smallint NOT NULL,
    caption text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.contestant_images OWNER TO postgres;

--
-- Name: contestant_images_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.contestant_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contestant_images_id_seq OWNER TO postgres;

--
-- Name: contestant_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.contestant_images_id_seq OWNED BY public.contestant_images.id;


--
-- Name: contestants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contestants (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    number integer,
    origin character varying(255),
    age integer,
    photo character varying(255),
    bio text,
    scores jsonb,
    metadata jsonb,
    active boolean DEFAULT true NOT NULL,
    rank integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.contestants OWNER TO postgres;

--
-- Name: contestants_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.contestants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contestants_id_seq OWNER TO postgres;

--
-- Name: contestants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.contestants_id_seq OWNED BY public.contestants.id;


--
-- Name: criteria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.criteria (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    segment_id bigint,
    category_id bigint,
    name character varying(255) NOT NULL,
    description text,
    weight integer DEFAULT 0 NOT NULL,
    min_score numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    max_score numeric(5,2) DEFAULT '100'::numeric NOT NULL,
    allow_decimals boolean DEFAULT true NOT NULL,
    decimal_places integer DEFAULT 2 NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.criteria OWNER TO postgres;

--
-- Name: criteria_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.criteria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.criteria_id_seq OWNER TO postgres;

--
-- Name: criteria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.criteria_id_seq OWNED BY public.criteria.id;


--
-- Name: events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.events (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    type character varying(255) NOT NULL,
    start_datetime timestamp(0) without time zone,
    end_datetime timestamp(0) without time zone,
    venue character varying(255),
    location character varying(255),
    status character varying(255) DEFAULT 'Pending'::character varying NOT NULL,
    metadata jsonb,
    is_milestone boolean DEFAULT false NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT events_status_check CHECK (((status)::text = ANY ((ARRAY['Pending'::character varying, 'In Progress'::character varying, 'Completed'::character varying, 'Cancelled'::character varying])::text[])))
);


ALTER TABLE public.events OWNER TO postgres;

--
-- Name: events_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.events_id_seq OWNER TO postgres;

--
-- Name: events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.events_id_seq OWNED BY public.events.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: pageant_judges; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pageant_judges (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    user_id bigint NOT NULL,
    role character varying(255) DEFAULT 'judge'::character varying NOT NULL,
    assigned_categories jsonb,
    assigned_segments jsonb,
    active boolean DEFAULT true NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pageant_judges OWNER TO postgres;

--
-- Name: pageant_judges_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pageant_judges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pageant_judges_id_seq OWNER TO postgres;

--
-- Name: pageant_judges_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pageant_judges_id_seq OWNED BY public.pageant_judges.id;


--
-- Name: pageant_organizers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pageant_organizers (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    user_id bigint NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pageant_organizers OWNER TO postgres;

--
-- Name: pageant_organizers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pageant_organizers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pageant_organizers_id_seq OWNER TO postgres;

--
-- Name: pageant_organizers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pageant_organizers_id_seq OWNED BY public.pageant_organizers.id;


--
-- Name: pageant_tabulators; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pageant_tabulators (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    user_id bigint NOT NULL,
    active boolean DEFAULT true NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pageant_tabulators OWNER TO postgres;

--
-- Name: pageant_tabulators_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pageant_tabulators_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pageant_tabulators_id_seq OWNER TO postgres;

--
-- Name: pageant_tabulators_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pageant_tabulators_id_seq OWNED BY public.pageant_tabulators.id;


--
-- Name: pageants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pageants (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    start_date date,
    end_date date,
    venue character varying(255),
    location character varying(255),
    status character varying(255) DEFAULT 'Draft'::character varying NOT NULL,
    created_by bigint NOT NULL,
    is_edit_permission_granted boolean DEFAULT false NOT NULL,
    edit_permission_expires_at timestamp(0) without time zone,
    edit_permission_granted_to bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    scoring_system character varying(255) DEFAULT 'percentage'::character varying NOT NULL,
    required_judges integer DEFAULT 0 NOT NULL,
    cover_image character varying(255),
    logo character varying(255),
    progress integer DEFAULT 0 NOT NULL,
    CONSTRAINT pageants_scoring_system_check CHECK (((scoring_system)::text = ANY ((ARRAY['percentage'::character varying, '1-10'::character varying, '1-5'::character varying, 'points'::character varying])::text[]))),
    CONSTRAINT pageants_status_check CHECK (((status)::text = ANY ((ARRAY['Draft'::character varying, 'Setup'::character varying, 'Active'::character varying, 'Completed'::character varying, 'Unlocked_For_Edit'::character varying, 'Archived'::character varying, 'Cancelled'::character varying])::text[])))
);


ALTER TABLE public.pageants OWNER TO postgres;

--
-- Name: pageants_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pageants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pageants_id_seq OWNER TO postgres;

--
-- Name: pageants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pageants_id_seq OWNED BY public.pageants.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: segments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.segments (
    id bigint NOT NULL,
    pageant_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    start_datetime timestamp(0) without time zone,
    end_datetime timestamp(0) without time zone,
    type character varying(255) NOT NULL,
    weight integer DEFAULT 100 NOT NULL,
    max_score numeric(8,2) DEFAULT '100'::numeric NOT NULL,
    scoring_type character varying(255) DEFAULT 'percentage'::character varying NOT NULL,
    status character varying(255) DEFAULT 'Pending'::character varying NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    rules jsonb,
    scoring_criteria jsonb,
    active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT segments_status_check CHECK (((status)::text = ANY ((ARRAY['Pending'::character varying, 'In Progress'::character varying, 'Completed'::character varying, 'Cancelled'::character varying])::text[])))
);


ALTER TABLE public.segments OWNER TO postgres;

--
-- Name: segments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.segments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.segments_id_seq OWNER TO postgres;

--
-- Name: segments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.segments_id_seq OWNED BY public.segments.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    username character varying(255),
    is_verified boolean DEFAULT false NOT NULL,
    verification_token character varying(255),
    verification_expires_at timestamp(0) without time zone,
    role character varying(255) DEFAULT 'user'::character varying NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: activities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities ALTER COLUMN id SET DEFAULT nextval('public.activities_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: contestant_images id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contestant_images ALTER COLUMN id SET DEFAULT nextval('public.contestant_images_id_seq'::regclass);


--
-- Name: contestants id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contestants ALTER COLUMN id SET DEFAULT nextval('public.contestants_id_seq'::regclass);


--
-- Name: criteria id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteria ALTER COLUMN id SET DEFAULT nextval('public.criteria_id_seq'::regclass);


--
-- Name: events id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.events ALTER COLUMN id SET DEFAULT nextval('public.events_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: pageant_judges id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_judges ALTER COLUMN id SET DEFAULT nextval('public.pageant_judges_id_seq'::regclass);


--
-- Name: pageant_organizers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_organizers ALTER COLUMN id SET DEFAULT nextval('public.pageant_organizers_id_seq'::regclass);


--
-- Name: pageant_tabulators id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_tabulators ALTER COLUMN id SET DEFAULT nextval('public.pageant_tabulators_id_seq'::regclass);


--
-- Name: pageants id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageants ALTER COLUMN id SET DEFAULT nextval('public.pageants_id_seq'::regclass);


--
-- Name: segments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.segments ALTER COLUMN id SET DEFAULT nextval('public.segments_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activities (id, pageant_id, user_id, action_type, entity_type, entity_id, description, icon, metadata, ip_address, created_at, updated_at) FROM stdin;
1	15	5	PAGEANT_CREATED	Pageant	15	Created pageant 'Miss Universe 2025'	\N	\N	\N	2025-03-15 11:10:16	2025-03-15 11:10:16
2	15	28	PAGEANT_STATUS_CHANGED	Pageant	15	Changed pageant status from 'Draft' to 'Setup'	\N	\N	\N	2025-03-16 11:10:16	2025-03-16 11:10:16
3	15	28	EVENT_CREATED	Event	1	Added event 'Contestant Registration'	\N	\N	\N	2025-03-17 11:10:16	2025-03-17 11:10:16
4	15	29	CONTESTANT_ADDED	Contestant	1	Added contestant 'Sophia Anderson'	\N	\N	\N	2025-03-23 11:10:16	2025-03-23 11:10:16
5	15	29	CONTESTANT_ADDED	Contestant	2	Added contestant 'Isabella Martinez'	\N	\N	\N	2025-03-23 11:10:16	2025-03-23 11:10:16
6	15	28	JUDGE_ASSIGNED	User	36	Assigned Judge Judge 1 as head judge	\N	\N	\N	2025-03-25 11:10:16	2025-03-25 11:10:16
7	15	28	EVENT_COMPLETED	Event	1	Completed event 'Contestant Registration'	\N	\N	\N	2025-03-27 11:10:16	2025-03-27 11:10:16
8	15	36	SCORE_UPDATED	Contestant	1	Updated scores for 'Sophia Anderson'	\N	\N	\N	2025-03-29 11:10:16	2025-03-29 11:10:16
9	15	28	PAGEANT_STATUS_CHANGED	Pageant	15	Changed pageant status from 'Setup' to 'Active'	\N	\N	\N	2025-03-28 11:10:16	2025-03-28 11:10:16
10	15	5	EVENT_UPDATED	Event	3	Updated event 'Preliminary Interviews' status to 'In Progress'	\N	\N	\N	2025-03-29 11:10:16	2025-03-29 11:10:16
11	16	5	PAGEANT_CREATED	Pageant	16	Created pageant 'Miss World 2025'	\N	\N	\N	2025-03-25 11:10:16	2025-03-25 11:10:16
12	16	30	PAGEANT_STATUS_CHANGED	Pageant	16	Changed pageant status from 'Draft' to 'Setup'	\N	\N	\N	2025-03-26 11:10:16	2025-03-26 11:10:16
13	16	30	EVENT_CREATED	Event	1	Added event 'Planning Meeting'	\N	\N	\N	2025-03-26 11:10:16	2025-03-26 11:10:16
14	16	30	EVENT_COMPLETED	Event	1	Completed event 'Planning Meeting'	\N	\N	\N	2025-03-27 11:10:16	2025-03-27 11:10:16
15	16	30	JUDGE_ASSIGNED	User	40	Assigned Judge Judge 5 as head judge	\N	\N	\N	2025-03-28 11:10:16	2025-03-28 11:10:16
16	16	30	EVENT_CREATED	Event	2	Added event 'Contestant Registration Opens'	\N	\N	\N	2025-03-29 11:10:16	2025-03-29 11:10:16
17	16	30	EVENT_UPDATED	Event	2	Updated event 'Contestant Registration Opens' status to 'In Progress'	\N	\N	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
\.


--
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.audit_logs (id, user_id, user_role, action_type, target_entity, target_id, details, ip_address, created_at, updated_at) FROM stdin;
1	1	admin	PAGEANT_CREATED	Pageant	1	Created pageant 'Miss Universe 2025'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
2	1	admin	PAGEANT_STATUS_CHANGED	Pageant	1	Changed pageant 'Miss Universe 2025' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
3	1	admin	PAGEANT_STATUS_CHANGED	Pageant	1	Changed pageant 'Miss Universe 2025' status from 'Setup' to 'Active'	127.0.0.1	2025-04-01 04:24:36	2025-04-01 04:24:36
4	1	admin	ORGANIZER_ASSIGNED	Pageant	1	Assigned organizer 'Organizer 1' to pageant 'Miss Universe 2025'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
5	1	admin	ORGANIZER_ASSIGNED	Pageant	1	Assigned organizer 'Organizer 2' to pageant 'Miss Universe 2025'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
6	1	admin	PAGEANT_CREATED	Pageant	2	Created pageant 'Miss World 2025'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
7	1	admin	PAGEANT_STATUS_CHANGED	Pageant	2	Changed pageant 'Miss World 2025' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
8	1	admin	ORGANIZER_ASSIGNED	Pageant	2	Assigned organizer 'Organizer 2' to pageant 'Miss World 2025'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
9	1	admin	PAGEANT_CREATED	Pageant	3	Created pageant 'Miss International 2025'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
10	1	admin	ORGANIZER_ASSIGNED	Pageant	3	Assigned organizer 'Organizer 3' to pageant 'Miss International 2025'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
11	1	admin	PAGEANT_CREATED	Pageant	4	Created pageant 'Miss Earth 2024'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
12	1	admin	PAGEANT_STATUS_CHANGED	Pageant	4	Changed pageant 'Miss Earth 2024' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
13	1	admin	PAGEANT_STATUS_CHANGED	Pageant	4	Changed pageant 'Miss Earth 2024' status from 'Setup' to 'Completed'	127.0.0.1	2025-04-01 04:24:36	2025-04-01 04:24:36
14	1	admin	ORGANIZER_ASSIGNED	Pageant	4	Assigned organizer 'Organizer 4' to pageant 'Miss Earth 2024'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
15	1	admin	ORGANIZER_ASSIGNED	Pageant	4	Assigned organizer 'Organizer 5' to pageant 'Miss Earth 2024'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
16	1	admin	PAGEANT_CREATED	Pageant	5	Created pageant 'Miss Grand International 2024'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
17	1	admin	PAGEANT_STATUS_CHANGED	Pageant	5	Changed pageant 'Miss Grand International 2024' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
18	1	admin	PAGEANT_STATUS_CHANGED	Pageant	5	Changed pageant 'Miss Grand International 2024' status from 'Setup' to 'Completed'	127.0.0.1	2025-04-01 04:24:36	2025-04-01 04:24:36
19	1	admin	ORGANIZER_ASSIGNED	Pageant	5	Assigned organizer 'Organizer 1' to pageant 'Miss Grand International 2024'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
20	1	admin	PAGEANT_CREATED	Pageant	6	Created pageant 'Miss Supranational 2024'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
21	1	admin	PAGEANT_STATUS_CHANGED	Pageant	6	Changed pageant 'Miss Supranational 2024' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
22	1	admin	PAGEANT_STATUS_CHANGED	Pageant	6	Changed pageant 'Miss Supranational 2024' status from 'Setup' to 'Archived'	127.0.0.1	2025-04-01 04:24:36	2025-04-01 04:24:36
23	1	admin	ORGANIZER_ASSIGNED	Pageant	6	Assigned organizer 'Organizer 3' to pageant 'Miss Supranational 2024'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
24	1	admin	PAGEANT_CREATED	Pageant	7	Created pageant 'Miss Tourism 2023'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
25	1	admin	PAGEANT_STATUS_CHANGED	Pageant	7	Changed pageant 'Miss Tourism 2023' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
26	1	admin	PAGEANT_STATUS_CHANGED	Pageant	7	Changed pageant 'Miss Tourism 2023' status from 'Setup' to 'Archived'	127.0.0.1	2025-04-01 04:24:36	2025-04-01 04:24:36
27	1	admin	ORGANIZER_ASSIGNED	Pageant	7	Assigned organizer 'Organizer 2' to pageant 'Miss Tourism 2023'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
28	1	admin	ORGANIZER_ASSIGNED	Pageant	7	Assigned organizer 'Organizer 5' to pageant 'Miss Tourism 2023'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
29	1	admin	PAGEANT_CREATED	Pageant	8	Created pageant 'Ms. Universe 2023'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
30	1	admin	PAGEANT_STATUS_CHANGED	Pageant	8	Changed pageant 'Ms. Universe 2023' status from 'Draft' to 'Setup'	127.0.0.1	2025-03-31 04:24:36	2025-03-31 04:24:36
31	1	admin	PAGEANT_STATUS_CHANGED	Pageant	8	Changed pageant 'Ms. Universe 2023' status from 'Setup' to 'Cancelled'	127.0.0.1	2025-04-01 04:24:36	2025-04-01 04:24:36
32	1	admin	ORGANIZER_ASSIGNED	Pageant	8	Assigned organizer 'Organizer 4' to pageant 'Ms. Universe 2023'	127.0.0.1	2025-03-30 06:24:36	2025-03-30 06:24:36
33	1	admin	PAGEANT_CREATED	Pageant	9	Created pageant 'Miss Charity 2024'	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
34	1	admin	PAGEANT_STATUS_CHANGED	Pageant	9	Changed pageant 'Miss Charity 2024' status from 'Draft' to 'Completed'	127.0.0.1	2025-04-04 04:24:36	2025-04-04 04:24:36
35	1	admin	GRANT_EDIT_PERMISSION	Pageant	9	Granted edit permission for pageant 'Miss Charity 2024' to user 'Organizer 1' until 2025-04-06 04:24:36	127.0.0.1	2025-03-29 04:24:36	2025-03-29 04:24:36
36	\N	SYSTEM	SYSTEM_BACKUP	\N	\N	Automated system backup completed successfully	127.0.0.1	2025-03-27 04:24:36	2025-03-27 04:24:36
37	\N	SYSTEM	PERMISSION_EXPIRY_WARNING	Pageant	9	Edit permission for pageant 'Miss Charity 2024' will expire in 7 days	127.0.0.1	2025-03-30 04:24:36	2025-03-30 04:24:36
38	1	admin	PAGEANT_CREATED	Pageant	10	Created pageant 'sample pageant'	127.0.0.1	2025-03-30 05:15:50	2025-03-30 05:15:50
39	1	admin	PAGEANT_CREATED	Pageant	11	Created pageant 'test pageant'	127.0.0.1	2025-03-30 05:21:14	2025-03-30 05:21:14
40	1	admin	PAGEANT_CREATED	Pageant	12	Created pageant 'sample  2'	127.0.0.1	2025-03-30 05:26:06	2025-03-30 05:26:06
41	1	admin	PAGEANT_CREATED	Pageant	13	Created pageant 'another sample'	127.0.0.1	2025-03-30 05:28:23	2025-03-30 05:28:23
42	1	admin	PAGEANT_CREATED	Pageant	17	Created pageant 'Mr. and Ms.'	127.0.0.1	2025-04-01 08:35:52	2025-04-01 08:35:52
43	2	organizer	EVENT_CREATED	Event	12	Created event 'pictorial' for pageant 'sample pageant'	127.0.0.1	2025-04-05 07:19:30	2025-04-05 07:19:30
44	2	organizer	REQUIRED_JUDGES_UPDATED	Pageant	13	Updated required judges count for pageant 'another sample' to 3	127.0.0.1	2025-04-05 09:10:01	2025-04-05 09:10:01
45	2	organizer	SCORING_SYSTEM_UPDATED	Pageant	13	Updated scoring system for pageant 'another sample' to percentage	127.0.0.1	2025-04-05 09:30:22	2025-04-05 09:30:22
46	2	organizer	SCORING_SYSTEM_UPDATED	Pageant	13	Updated scoring system for pageant 'another sample' to 1-10	127.0.0.1	2025-04-05 09:30:32	2025-04-05 09:30:32
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories (id, pageant_id, name, description, weight, max_score, scoring_type, display_order, criteria, active, created_at, updated_at) FROM stdin;
1	15	Beauty	Overall beauty and appearance	30	100.00	percentage	1	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
2	15	Intelligence	Question and answer performance	35	100.00	percentage	2	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
3	15	Talent	Talent showcase performance	20	100.00	percentage	3	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
4	15	Poise & Posture	Elegance and confidence in walk and posture	15	100.00	percentage	4	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
5	16	Beauty	Overall beauty and appearance	25	10.00	scale	1	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
6	16	Humanitarian Project	Assessment of contestant's humanitarian work	30	10.00	scale	2	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
7	16	Talent	Performance in talent showcase	25	10.00	scale	3	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
8	16	Interview	Performance in interview rounds	20	10.00	scale	4	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
\.


--
-- Data for Name: contestant_images; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.contestant_images (id, contestant_id, image_path, is_primary, display_order, caption, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: contestants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.contestants (id, pageant_id, name, number, origin, age, photo, bio, scores, metadata, active, rank, created_at, updated_at) FROM stdin;
1	15	Sophia Anderson	1	California, USA	25	/images/contestants/sophia.jpg	Graduated with honors in Environmental Science, passionate about sustainability.	"{\\"average\\":92.5}"	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
2	15	Isabella Martinez	2	Madrid, Spain	24	/images/contestants/isabella.jpg	Professional dancer with a degree in International Relations.	"{\\"average\\":88.7}"	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
3	15	Olivia Johnson	3	London, UK	26	/images/contestants/olivia.jpg	Medical student and volunteer for healthcare missions in developing countries.	"{\\"average\\":90.1}"	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
4	15	Amara Okafor	4	Lagos, Nigeria	23	/images/contestants/amara.jpg	Entrepreneur who founded a tech startup focusing on education.	"{\\"average\\":85.4}"	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
5	15	Mei Li	5	Beijing, China	25	/images/contestants/mei.jpg	Classical pianist and advocate for arts education.	"{\\"average\\":87.2}"	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
\.


--
-- Data for Name: criteria; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.criteria (id, pageant_id, segment_id, category_id, name, description, weight, min_score, max_score, allow_decimals, decimal_places, display_order, created_at, updated_at) FROM stdin;
1	1	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
2	1	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
3	1	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
4	1	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
5	2	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
6	2	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
7	2	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
8	2	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
9	3	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
10	3	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
11	3	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
12	3	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
13	4	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
14	4	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
15	4	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
16	4	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
17	5	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
18	5	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
19	5	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
20	5	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
21	6	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
22	6	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
23	6	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
24	6	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
25	7	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
26	7	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
27	7	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
28	7	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
29	8	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
30	8	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
31	8	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
32	8	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
33	9	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
34	9	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
35	9	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
36	9	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
37	10	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
38	10	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
39	10	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
40	10	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
41	11	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
42	11	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
43	11	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
44	11	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
45	12	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
46	12	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
47	12	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
48	12	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
49	13	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:52:27	2025-03-30 11:52:27
50	13	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:52:27	2025-03-30 11:52:27
51	13	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:52:27	2025-03-30 11:52:27
52	13	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:52:27	2025-03-30 11:52:27
53	1	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
54	1	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
55	1	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
56	1	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
57	2	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
58	2	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
59	2	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
60	2	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
61	3	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
62	3	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
63	3	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
64	3	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
65	4	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
66	4	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
67	4	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
68	4	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
69	5	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
70	5	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
71	5	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
72	5	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
73	6	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
74	6	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
75	6	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
76	6	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
77	7	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
78	7	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
79	7	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
80	7	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
81	8	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
82	8	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
83	8	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
84	8	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
85	9	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
86	9	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
87	9	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
88	9	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
89	10	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
90	10	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
91	10	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
92	10	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
93	11	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
94	11	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
95	11	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
96	11	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
97	12	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
98	12	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
99	12	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
100	12	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
101	13	\N	\N	Beauty	Overall physical appearance and presence	30	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
102	13	\N	\N	Poise and Bearing	Elegance and composure while presenting	25	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
103	13	\N	\N	Communication Skills	Ability to articulate thoughts clearly	25	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
104	13	\N	\N	Intelligence	Quick thinking and problem solving abilities	20	0.00	100.00	t	2	4	2025-03-30 11:53:18	2025-03-30 11:53:18
105	15	\N	1	Content	Criteria for Beauty category	40	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
106	15	\N	1	Presentation	Criteria for Beauty category	30	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
107	15	\N	1	Technique	Criteria for Beauty category	30	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
108	15	\N	2	Content	Criteria for Intelligence category	40	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
109	15	\N	2	Presentation	Criteria for Intelligence category	30	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
110	15	\N	2	Technique	Criteria for Intelligence category	30	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
111	15	\N	3	Content	Criteria for Talent category	40	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
112	15	\N	3	Presentation	Criteria for Talent category	30	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
113	15	\N	3	Technique	Criteria for Talent category	30	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
114	15	\N	4	Content	Criteria for Poise & Posture category	40	0.00	100.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
115	15	\N	4	Presentation	Criteria for Poise & Posture category	30	0.00	100.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
116	15	\N	4	Technique	Criteria for Poise & Posture category	30	0.00	100.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
117	16	\N	5	Content	Criteria for Beauty category	40	0.00	10.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
118	16	\N	5	Presentation	Criteria for Beauty category	30	0.00	10.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
119	16	\N	5	Technique	Criteria for Beauty category	30	0.00	10.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
120	16	\N	6	Content	Criteria for Humanitarian Project category	40	0.00	10.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
121	16	\N	6	Presentation	Criteria for Humanitarian Project category	30	0.00	10.00	t	2	2	2025-03-30 11:53:18	2025-03-30 11:53:18
122	16	\N	6	Technique	Criteria for Humanitarian Project category	30	0.00	10.00	t	2	3	2025-03-30 11:53:18	2025-03-30 11:53:18
123	16	\N	7	Content	Criteria for Talent category	40	0.00	10.00	t	2	1	2025-03-30 11:53:18	2025-03-30 11:53:18
124	16	\N	7	Presentation	Criteria for Talent category	30	0.00	10.00	t	2	2	2025-03-30 11:53:19	2025-03-30 11:53:19
125	16	\N	7	Technique	Criteria for Talent category	30	0.00	10.00	t	2	3	2025-03-30 11:53:19	2025-03-30 11:53:19
126	16	\N	8	Content	Criteria for Interview category	40	0.00	10.00	t	2	1	2025-03-30 11:53:19	2025-03-30 11:53:19
127	16	\N	8	Presentation	Criteria for Interview category	30	0.00	10.00	t	2	2	2025-03-30 11:53:19	2025-03-30 11:53:19
128	16	\N	8	Technique	Criteria for Interview category	30	0.00	10.00	t	2	3	2025-03-30 11:53:19	2025-03-30 11:53:19
\.


--
-- Data for Name: events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.events (id, pageant_id, name, description, type, start_datetime, end_datetime, venue, location, status, metadata, is_milestone, display_order, created_at, updated_at) FROM stdin;
1	15	Contestant Registration	Official registration and documentation of contestants	registration	2025-03-25 11:10:16	2025-03-27 11:10:16	Grand Arena Convention Center	New York, USA	Completed	\N	t	1	2025-03-30 11:10:16	2025-03-30 11:10:16
2	15	Contestant Orientation	Briefing and introduction to pageant rules	setup	2025-03-28 11:10:16	2025-03-28 11:10:16	Grand Arena Convention Center	New York, USA	Completed	\N	t	2	2025-03-30 11:10:16	2025-03-30 11:10:16
3	15	Preliminary Interviews	First round of interviews with judges	preliminary	2025-03-29 11:10:16	2025-03-30 11:10:16	Grand Arena Convention Center	New York, USA	In Progress	\N	t	3	2025-03-30 11:10:16	2025-03-30 11:10:16
4	15	Talent Showcase	Contestants demonstrate their talents	preliminary	2025-04-02 11:10:16	2025-04-02 11:10:16	Grand Arena Convention Center	New York, USA	Pending	\N	t	4	2025-03-30 11:10:16	2025-03-30 11:10:16
5	15	Evening Gown Competition	Formal wear competition	preliminary	2025-04-04 11:10:16	2025-04-04 11:10:16	Grand Arena Convention Center	New York, USA	Pending	\N	f	5	2025-03-30 11:10:16	2025-03-30 11:10:16
6	15	Swimwear Competition	Swimwear runway showcase	preliminary	2025-04-06 11:10:16	2025-04-06 11:10:16	Grand Arena Convention Center	New York, USA	Pending	\N	f	6	2025-03-30 11:10:16	2025-03-30 11:10:16
7	15	Final Q&A Round	Final question and answer session	final	2025-04-09 11:10:16	2025-04-09 11:10:16	Grand Arena Convention Center	New York, USA	Pending	\N	t	7	2025-03-30 11:10:16	2025-03-30 11:10:16
8	15	Coronation Night	Final judging and winner announcement	final	2025-04-14 11:10:16	2025-04-14 11:10:16	Grand Arena Convention Center	New York, USA	Pending	\N	t	8	2025-03-30 11:10:16	2025-03-30 11:10:16
9	16	Planning Meeting	Initial planning and coordination	setup	2025-03-27 11:10:16	2025-03-27 11:10:16	Royal Convention Center	London, UK	Completed	\N	t	1	2025-03-30 11:10:16	2025-03-30 11:10:16
10	16	Contestant Registration Opens	Opening of contestant registration portal	registration	2025-03-30 11:10:16	2025-04-29 11:10:16	Online	Worldwide	In Progress	\N	t	2	2025-03-30 11:10:16	2025-03-30 11:10:16
11	16	Venue Setup	Preparation of venue for pageant	setup	2025-04-23 11:10:16	2025-04-30 11:10:16	Royal Convention Center	London, UK	Pending	\N	f	3	2025-03-30 11:10:16	2025-03-30 11:10:16
12	10	pictorial	pictorial	Photoshoot	2025-04-09 15:20:00	2025-04-17 15:19:00	bohol	bohol	Pending	\N	t	1	2025-04-05 07:19:30	2025-04-05 07:19:30
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
1	default	{"uuid":"74fdb1b8-2c5e-46de-bad1-7a7386d32fa6","displayName":"App\\\\Events\\\\EventUpdated","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Broadcasting\\\\BroadcastEvent","command":"O:38:\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\":14:{s:5:\\"event\\";O:23:\\"App\\\\Events\\\\EventUpdated\\":1:{s:4:\\"data\\";a:9:{s:4:\\"type\\";s:13:\\"event_created\\";s:10:\\"pageant_id\\";i:10;s:12:\\"pageant_name\\";s:14:\\"sample pageant\\";s:8:\\"event_id\\";i:12;s:10:\\"event_name\\";s:9:\\"pictorial\\";s:14:\\"organizer_name\\";s:14:\\"Organizer User\\";s:12:\\"is_milestone\\";b:1;s:7:\\"message\\";s:76:\\"New event 'pictorial' created for pageant 'sample pageant' by Organizer User\\";s:9:\\"timestamp\\";s:25:\\"2025-04-05T07:19:30+00:00\\";}}s:5:\\"tries\\";N;s:7:\\"timeout\\";N;s:7:\\"backoff\\";N;s:13:\\"maxExceptions\\";N;s:10:\\"connection\\";N;s:5:\\"queue\\";N;s:5:\\"delay\\";N;s:11:\\"afterCommit\\";N;s:10:\\"middleware\\";a:0:{}s:7:\\"chained\\";a:0:{}s:15:\\"chainConnection\\";N;s:10:\\"chainQueue\\";N;s:19:\\"chainCatchCallbacks\\";N;}"}}	0	\N	1743837570	1743837570
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_03_29_102021_create_pageants_table	1
5	2025_03_29_102100_create_pageant_organizers_table	1
6	2025_03_29_102123_create_audit_logs_table	1
7	2025_03_29_113306_add_verification_fields_to_users_table	1
8	2025_03_29_114343_add_verification_fields_to_users	1
9	2025_03_29_114412_add_all_verification_fields	1
10	2025_03_30_000000_add_role_to_users_table	1
11	2025_03_30_041350_create_contestants_table	1
12	2025_03_30_041354_create_categories_table	1
13	2025_03_30_041358_create_events_table	1
14	2025_03_30_041403_create_activities_table	1
15	2025_03_30_041412_create_segments_table	1
16	2025_03_30_041416_create_pageant_judges_table	1
17	2025_03_30_104031_add_scoring_system_to_pageants_table	2
18	2025_03_31_120000_create_criteria_table	3
19	2025_03_30_123820_create_pageant_tabulators_table	4
20	2025_03_30_123841_add_required_judges_to_pageants_table	4
21	2025_03_30_134137_add_images_to_pageants_table	5
22	2025_04_01_083000_create_contestant_images_table	6
23	2025_04_05_072025_add_progress_to_pageants_table	7
\.


--
-- Data for Name: pageant_judges; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pageant_judges (id, pageant_id, user_id, role, assigned_categories, assigned_segments, active, notes, created_at, updated_at) FROM stdin;
1	15	36	head_judge	\N	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
2	15	37	judge	\N	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
3	15	38	judge	\N	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
4	15	39	guest_judge	\N	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
5	16	40	head_judge	\N	\N	t	\N	2025-03-30 11:10:16	2025-03-30 11:10:16
\.


--
-- Data for Name: pageant_organizers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pageant_organizers (id, pageant_id, user_id, is_primary, created_at, updated_at) FROM stdin;
1	1	28	f	2025-03-30 04:24:36	2025-03-30 04:24:36
2	1	29	f	2025-03-30 04:24:36	2025-03-30 04:24:36
3	2	29	f	2025-03-30 04:24:36	2025-03-30 04:24:36
4	3	30	f	2025-03-30 04:24:36	2025-03-30 04:24:36
5	4	31	f	2025-03-30 04:24:36	2025-03-30 04:24:36
6	4	32	f	2025-03-30 04:24:36	2025-03-30 04:24:36
7	5	28	f	2025-03-30 04:24:36	2025-03-30 04:24:36
8	6	30	f	2025-03-30 04:24:36	2025-03-30 04:24:36
9	7	29	f	2025-03-30 04:24:36	2025-03-30 04:24:36
10	7	32	f	2025-03-30 04:24:36	2025-03-30 04:24:36
11	8	31	f	2025-03-30 04:24:36	2025-03-30 04:24:36
12	9	28	f	2025-03-30 04:24:36	2025-03-30 04:24:36
13	10	2	f	2025-03-30 05:15:50	2025-03-30 05:15:50
14	11	2	f	2025-03-30 05:21:14	2025-03-30 05:21:14
15	12	2	f	2025-03-30 05:26:06	2025-03-30 05:26:06
16	13	2	f	2025-03-30 05:28:23	2025-03-30 05:28:23
17	15	28	f	2025-03-30 11:10:16	2025-03-30 11:10:16
18	15	29	f	2025-03-30 11:10:16	2025-03-30 11:10:16
19	16	30	f	2025-03-30 11:10:16	2025-03-30 11:10:16
20	17	8	f	2025-04-01 08:35:52	2025-04-01 08:35:52
\.


--
-- Data for Name: pageant_tabulators; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pageant_tabulators (id, pageant_id, user_id, active, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: pageants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pageants (id, name, description, start_date, end_date, venue, location, status, created_by, is_edit_permission_granted, edit_permission_expires_at, edit_permission_granted_to, created_at, updated_at, scoring_system, required_judges, cover_image, logo, progress) FROM stdin;
1	Miss Universe 2025	The most prestigious international beauty pageant	2025-05-30	2025-06-06	Grand Arena	New York, USA	Active	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
2	Miss World 2025	Beauty with a purpose	2025-06-30	2025-07-05	Royal Theatre	London, UK	Setup	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
3	Miss International 2025	Celebrating global diversity and culture	2025-08-30	2025-09-02	Tokyo Dome	Tokyo, Japan	Draft	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
4	Miss Earth 2024	Promoting environmental awareness	2025-03-02	2025-03-12	Manila Arena	Manila, Philippines	Completed	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
5	Miss Grand International 2024	Stop the war and violence	2025-01-30	2025-02-06	Impact Arena	Bangkok, Thailand	Completed	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
6	Miss Supranational 2024	Empowering women globally	2024-12-30	2025-01-04	Strzelecki Park Amphitheatre	Krynica-Zdrój, Poland	Archived	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
7	Miss Tourism 2023	Promoting travel and cultural exchange	2024-07-30	2024-08-04	Marina Bay Sands	Singapore	Archived	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
8	Ms. Universe 2023	Celebrating beauty at all ages	2024-09-30	2024-10-03	Convention Center	Las Vegas, USA	Cancelled	1	f	\N	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
9	Miss Charity 2024	Pageant dedicated to charitable causes	2025-01-30	2025-02-02	City Auditorium	Boston, USA	Unlocked_For_Edit	1	t	2025-04-06 04:24:36	28	2025-03-30 04:24:36	2025-03-30 04:24:36	percentage	0	\N	\N	0
10	sample pageant	sample pageant	2025-03-30	2025-03-31	bohol	bohol	Setup	1	f	\N	\N	2025-03-30 05:15:50	2025-03-30 05:15:50	percentage	0	\N	\N	0
11	test pageant	test pageant	2025-03-30	2025-03-31	\N	\N	Setup	1	f	\N	\N	2025-03-30 05:21:14	2025-03-30 05:21:14	percentage	0	\N	\N	0
12	sample  2	sampels	2025-03-30	2025-03-31	bohol	bohol	Setup	1	f	\N	\N	2025-03-30 05:26:06	2025-03-30 05:26:06	percentage	0	\N	\N	0
15	Miss Universe 2025	The most prestigious international beauty pageant showcasing talent, intelligence, and beauty from around the world.	2025-04-09	2025-04-19	Grand Arena Convention Center	New York, USA	Active	5	f	\N	\N	2025-03-30 11:10:16	2025-03-30 11:10:16	percentage	0	\N	\N	0
16	Miss World 2025	Beauty with a purpose - celebrating humanitarian work alongside beauty and talent.	2025-05-30	2025-06-09	Royal Convention Center	London, UK	Setup	5	f	\N	\N	2025-03-30 11:10:16	2025-03-30 11:10:16	1-10	0	\N	\N	0
17	Mr. and Ms.	\N	2025-09-14	2025-09-15	Catigbian,Gym	Poblacion, Catigbian,Bohol	Draft	1	f	\N	\N	2025-04-01 08:35:52	2025-04-01 08:35:52	points	0	\N	\N	0
13	another sample	sample for another	2025-03-30	2025-04-01	bohol	bohol	Setup	1	f	\N	\N	2025-03-30 05:28:23	2025-04-05 09:30:32	1-10	3	\N	\N	0
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: segments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.segments (id, pageant_id, name, description, start_datetime, end_datetime, type, weight, max_score, scoring_type, status, display_order, rules, scoring_criteria, active, created_at, updated_at) FROM stdin;
1	15	Evening Gown	Formal wear segment showcasing elegance and poise	2025-04-04 11:10:16	2025-04-04 11:10:16	evening_gown	25	100.00	percentage	Pending	1	\N	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
2	15	Swimwear	Swimwear segment showcasing fitness and confidence	2025-04-06 11:10:16	2025-04-06 11:10:16	swimwear	25	100.00	percentage	Pending	2	\N	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
3	15	Talent	Talent showcase segment demonstrating unique abilities	2025-04-02 11:10:16	2025-04-02 11:10:16	talent	25	100.00	percentage	Pending	3	\N	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
4	15	Question & Answer	Final Q&A segment testing communication and intelligence	2025-04-09 11:10:16	2025-04-09 11:10:16	qa	25	100.00	percentage	Pending	4	\N	\N	t	2025-03-30 11:10:16	2025-03-30 11:10:16
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
oKrLFH31boUGnHtKGyGHLR2BB2YkzYoKcK1LcMNY	2	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.6780.64 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoicUl2NllCZmFyalQ2cmFwQmsxOTdRVFpPWVg3UG9DSzlCNFpZaHZZZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==	1743944412
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, username, is_verified, verification_token, verification_expires_at, role) FROM stdin;
5	Alec Collier	kihn.tevin@example.net	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	9QNVJeNfUN	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	admin
6	Carmela Cassin	althea12@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	8mO6O2Ujah	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	admin
7	Gertrude DuBuque	lebert@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	ewRZwN5JoA	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	admin
8	Telly Bashirian	rschumm@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	M2vFdodJ27	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	organizer
9	Kaci Conn	whintz@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	CqCkPJvEQw	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	organizer
10	Cleveland Langosh	korn@example.com	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	FN2xfP1m5L	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	organizer
11	Mrs. Kaela Shanahan	cbode@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	pyznKoyEBe	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	organizer
12	Eve Becker	xosinski@example.com	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	X6dp3Uz1Eh	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	organizer
13	Mireille Kub	olittle@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	YqPKwHHx9i	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	tabulator
14	Brent Wisoky III	hermann.dean@example.com	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	INu0VTJieK	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	tabulator
15	Lyda Murray	stephan.mueller@example.net	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	oQcNZ8x1DW	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	tabulator
16	Geovanny Reilly	estracke@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	xu5FNb2kzE	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	tabulator
17	Marco Hill	bernhard.idell@example.net	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	MvT2h7tUMt	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	tabulator
18	Roy Zieme	fadel.aidan@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	0brVDFmdTt	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
19	Kyra Trantow	supton@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	ry4m1nlSe7	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
20	Joesph Koss	urunte@example.net	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	82uzrDgX3R	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
21	Dr. Edmond Wiegand V	jed.kutch@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	SyrXTpcxvm	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
22	Micaela Russel	brayan.corkery@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	XLdTFejcTb	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
23	Guido Dickinson	lakin.winnifred@example.com	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	Mwv6IRNn40	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
24	Vaughn Jast	gerardo.leannon@example.com	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	Z18SbSV4vY	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
25	Stacy Grady	macie89@example.org	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	8UAjqwm9kF	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
26	Johanna Corwin	wsanford@example.net	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	aLjorKRhtg	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
27	Dr. Adrian Swaniawski PhD	crooks.shania@example.com	2025-03-30 04:24:30	$2y$12$Rm47xGT4Kb4MfAwSImOazepMNZwg2H2.TyJ4P3ymswPSF/cWevmVK	2PVgLrpKZF	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
28	Organizer 1	organizer1@example.com	2025-03-30 04:24:31	$2y$12$IPmWWfxC2JgbIpYjWpdgROgLHaf2QIuutkJtYTdG7rc88qAoU.Zt.	\N	2025-03-30 04:24:31	2025-03-30 04:24:31	\N	f	\N	\N	organizer
29	Organizer 2	organizer2@example.com	2025-03-30 04:24:31	$2y$12$NCxbESaWcNDeqXY33GBhTujPIGTM1pS7TW8zzPDQ2fzVaDSipLXsO	\N	2025-03-30 04:24:31	2025-03-30 04:24:31	\N	f	\N	\N	organizer
30	Organizer 3	organizer3@example.com	2025-03-30 04:24:32	$2y$12$mp681WhBmF1J9voxkPA5VeMKO4vucAOKcvqkja5VqO07jnE6NY6Lq	\N	2025-03-30 04:24:32	2025-03-30 04:24:32	\N	f	\N	\N	organizer
31	Organizer 4	organizer4@example.com	2025-03-30 04:24:32	$2y$12$fHfJ7KOVaZLXQDYBMy005OB85FBuqKawn5zvOoBS/qhtxM8ZZh/hi	\N	2025-03-30 04:24:32	2025-03-30 04:24:32	\N	f	\N	\N	organizer
32	Organizer 5	organizer5@example.com	2025-03-30 04:24:32	$2y$12$ZJHgfSyPLyMAjpSTI8zGeu7NNGHMeTJTDDxFdWNqIsK6a4Ncc9MCu	\N	2025-03-30 04:24:32	2025-03-30 04:24:32	\N	f	\N	\N	organizer
33	Tabulator 1	tabulator1@example.com	2025-03-30 04:24:32	$2y$12$.OlMTtC3oavZwTXqkfrRhO6zRzyEZL3kYg.FF25dW.2G63BRHmyRS	\N	2025-03-30 04:24:32	2025-03-30 04:24:32	\N	f	\N	\N	tabulator
34	Tabulator 2	tabulator2@example.com	2025-03-30 04:24:33	$2y$12$H1rWD6M.oYkGQq0vZp9CSeohsz6tYrU/QHukOTjFRO9QQNVl78/uG	\N	2025-03-30 04:24:33	2025-03-30 04:24:33	\N	f	\N	\N	tabulator
35	Tabulator 3	tabulator3@example.com	2025-03-30 04:24:33	$2y$12$QnfKJ8Cgs.uuqmjMQ.rMAu9oseBTTU4aeNiUHluAtJxmLzt2pnWqW	\N	2025-03-30 04:24:33	2025-03-30 04:24:33	\N	f	\N	\N	tabulator
36	Judge 1	judge1@example.com	2025-03-30 04:24:33	$2y$12$HdhoKG1XvBSwDQGgnRQdfOOHA3a7bKTC.ZnaiatojpcHvkAsviWwO	\N	2025-03-30 04:24:33	2025-03-30 04:24:33	\N	f	\N	\N	judge
37	Judge 2	judge2@example.com	2025-03-30 04:24:34	$2y$12$ZntySaC8HTo2d9/JtYeEiOXw4xkb2oB.ar1L1EicUipPaQKIPE9XW	\N	2025-03-30 04:24:34	2025-03-30 04:24:34	\N	f	\N	\N	judge
38	Judge 3	judge3@example.com	2025-03-30 04:24:34	$2y$12$6JJ35RtPnBwXd1N4wQ6j0u9J9sPuAjAV2FzmDxbd0htjK4sG0iI.S	\N	2025-03-30 04:24:34	2025-03-30 04:24:34	\N	f	\N	\N	judge
39	Judge 4	judge4@example.com	2025-03-30 04:24:34	$2y$12$oLdamy6uLFlXbdPTZVLLr.xgiqSd.rQpi2RImue0hp6/w9VQ4Micu	\N	2025-03-30 04:24:34	2025-03-30 04:24:34	\N	f	\N	\N	judge
40	Judge 5	judge5@example.com	2025-03-30 04:24:35	$2y$12$z.gMrDpfkL/oYUCQxEGoJe9.rzmoT8CEzp3/AReO/6FUHahwmfNnG	\N	2025-03-30 04:24:35	2025-03-30 04:24:35	\N	f	\N	\N	judge
41	Judge 6	judge6@example.com	2025-03-30 04:24:35	$2y$12$bSGpDrwjrjpMRJr6xefF7OVznhlWAXoIwAtLjp4tg98wHdOzVRGXK	\N	2025-03-30 04:24:35	2025-03-30 04:24:35	\N	f	\N	\N	judge
42	Judge 7	judge7@example.com	2025-03-30 04:24:35	$2y$12$NVfMiVKVR.OHRiOl68OEI.UC6mS9bd9L0kVA0DnH4KEtTvQBBqwum	\N	2025-03-30 04:24:35	2025-03-30 04:24:35	\N	f	\N	\N	judge
43	Judge 8	judge8@example.com	2025-03-30 04:24:36	$2y$12$Ll/54WRqB4sTiZuS79.7rOVJ7yG1A1FNvCICM6Eplv628G21uPxBq	\N	2025-03-30 04:24:36	2025-03-30 04:24:36	\N	f	\N	\N	judge
4	Judge User	judge@example.com	2025-03-30 04:24:30	$2y$12$GKIh2XeqGmrJfuczFjPMd.vIBgBVUrMPKi89.IkIEqz1SS0p3rM3.	NIp5j7m3IChX1bcM5IEt7HqgP0ioi8mhExbBgEqdKoMtmnx39FF4ph0tbv2v	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	judge
3	Tabulator User	tabulator@example.com	2025-03-30 04:24:30	$2y$12$a18qysWqAab/YEl7gzdDO.WX8PsGqvp/w5jSjvQrJsGK7xV/KZK.W	w3S6TPqIaa1BxvMRZo2XRxjbfdEyGI76Q2VaffvDBcxR8QKBacksqhkKrShV	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	tabulator
1	Admin User	admin@example.com	2025-03-30 04:24:29	$2y$12$QI3tc6zuSw2IQDjJFbLqIuOcyPuYyqZyGhQnKlVQelu4vE1sRXQVe	r0RYoPE1dY4EqWYWpnWK7VbQKetgEYdM2sQP0BWaXRQAgkcQmlwQXbUAJzRD	2025-03-30 04:24:29	2025-03-30 04:24:29	\N	f	\N	\N	admin
2	Organizer User	organizer@example.com	2025-03-30 04:24:30	$2y$12$RqDBMNoWHiCG8jW16GCap.CSdbfHoAljXH49TQ2iCHp1W3S06WPmu	Jee0yTrcAXAElHMaJsQTSQq5VgpwmzIUCeuoAJ2kz6r2MYho7IWb7P8CqYFP	2025-03-30 04:24:30	2025-03-30 04:24:30	\N	f	\N	\N	organizer
\.


--
-- Name: activities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activities_id_seq', 17, true);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 46, true);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_id_seq', 8, true);


--
-- Name: contestant_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.contestant_images_id_seq', 1, false);


--
-- Name: contestants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.contestants_id_seq', 5, true);


--
-- Name: criteria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.criteria_id_seq', 128, true);


--
-- Name: events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.events_id_seq', 12, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 23, true);


--
-- Name: pageant_judges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pageant_judges_id_seq', 5, true);


--
-- Name: pageant_organizers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pageant_organizers_id_seq', 20, true);


--
-- Name: pageant_tabulators_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pageant_tabulators_id_seq', 1, false);


--
-- Name: pageants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pageants_id_seq', 17, true);


--
-- Name: segments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.segments_id_seq', 4, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 43, true);


--
-- Name: activities activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: contestant_images contestant_images_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contestant_images
    ADD CONSTRAINT contestant_images_pkey PRIMARY KEY (id);


--
-- Name: contestants contestants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contestants
    ADD CONSTRAINT contestants_pkey PRIMARY KEY (id);


--
-- Name: criteria criteria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteria
    ADD CONSTRAINT criteria_pkey PRIMARY KEY (id);


--
-- Name: events events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: pageant_judges pageant_judges_pageant_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_judges
    ADD CONSTRAINT pageant_judges_pageant_id_user_id_unique UNIQUE (pageant_id, user_id);


--
-- Name: pageant_judges pageant_judges_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_judges
    ADD CONSTRAINT pageant_judges_pkey PRIMARY KEY (id);


--
-- Name: pageant_organizers pageant_organizers_pageant_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_organizers
    ADD CONSTRAINT pageant_organizers_pageant_id_user_id_unique UNIQUE (pageant_id, user_id);


--
-- Name: pageant_organizers pageant_organizers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_organizers
    ADD CONSTRAINT pageant_organizers_pkey PRIMARY KEY (id);


--
-- Name: pageant_tabulators pageant_tabulators_pageant_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_tabulators
    ADD CONSTRAINT pageant_tabulators_pageant_id_user_id_unique UNIQUE (pageant_id, user_id);


--
-- Name: pageant_tabulators pageant_tabulators_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_tabulators
    ADD CONSTRAINT pageant_tabulators_pkey PRIMARY KEY (id);


--
-- Name: pageants pageants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageants
    ADD CONSTRAINT pageants_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: segments segments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.segments
    ADD CONSTRAINT segments_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_unique UNIQUE (username);


--
-- Name: audit_logs_user_id_action_type_target_entity_target_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_user_id_action_type_target_entity_target_id_index ON public.audit_logs USING btree (user_id, action_type, target_entity, target_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: activities activities_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: activities activities_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: audit_logs audit_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: categories categories_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: contestant_images contestant_images_contestant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contestant_images
    ADD CONSTRAINT contestant_images_contestant_id_foreign FOREIGN KEY (contestant_id) REFERENCES public.contestants(id) ON DELETE CASCADE;


--
-- Name: contestants contestants_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contestants
    ADD CONSTRAINT contestants_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: criteria criteria_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteria
    ADD CONSTRAINT criteria_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE SET NULL;


--
-- Name: criteria criteria_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteria
    ADD CONSTRAINT criteria_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: criteria criteria_segment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteria
    ADD CONSTRAINT criteria_segment_id_foreign FOREIGN KEY (segment_id) REFERENCES public.segments(id) ON DELETE CASCADE;


--
-- Name: events events_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: pageant_judges pageant_judges_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_judges
    ADD CONSTRAINT pageant_judges_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: pageant_judges pageant_judges_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_judges
    ADD CONSTRAINT pageant_judges_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: pageant_organizers pageant_organizers_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_organizers
    ADD CONSTRAINT pageant_organizers_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: pageant_organizers pageant_organizers_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_organizers
    ADD CONSTRAINT pageant_organizers_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: pageant_tabulators pageant_tabulators_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_tabulators
    ADD CONSTRAINT pageant_tabulators_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- Name: pageant_tabulators pageant_tabulators_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageant_tabulators
    ADD CONSTRAINT pageant_tabulators_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: pageants pageants_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageants
    ADD CONSTRAINT pageants_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: pageants pageants_edit_permission_granted_to_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pageants
    ADD CONSTRAINT pageants_edit_permission_granted_to_foreign FOREIGN KEY (edit_permission_granted_to) REFERENCES public.users(id);


--
-- Name: segments segments_pageant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.segments
    ADD CONSTRAINT segments_pageant_id_foreign FOREIGN KEY (pageant_id) REFERENCES public.pageants(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

