# Database Schema Documentation

**Database Engine:** PostgreSQL  
**Last Updated:** December 1, 2025

---

## Table of Contents
1. [Users & Authentication](#users--authentication)
2. [Pageants](#pageants)
3. [Contestants](#contestants)
4. [Rounds & Scoring](#rounds--scoring)
5. [Categories & Criteria](#categories--criteria)
6. [Activity & Audit Logging](#activity--audit-logging)
7. [Permissions & Access](#permissions--access)
8. [System Tables](#system-tables)

---

## Users & Authentication

### `users`
User accounts for the system.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique user identifier |
| `name` | varchar | NOT NULL | User's full name |
| `username` | varchar | UNIQUE | Unique username |
| `email` | varchar | UNIQUE, NOT NULL | User's email address |
| `email_verified_at` | timestamp | NULL | Email verification timestamp |
| `password` | varchar | NOT NULL | Hashed password |
| `remember_token` | varchar | NULL | Remember me token |
| `is_verified` | boolean | DEFAULT false | Email verification status |
| `verification_token` | varchar | NULL | Email verification token |
| `verification_expires_at` | timestamp | NULL | Token expiration |
| `role` | varchar | NOT NULL | User role (admin, organizer, judge, tabulator) |
| `is_active` | boolean | DEFAULT true | Account active status |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `users_pkey` (PRIMARY KEY on `id`)
- `users_email_unique` (UNIQUE on `email`)
- `users_username_unique` (UNIQUE on `username`)

---

### `password_reset_tokens`
Tokens for password reset functionality.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `email` | varchar | PRIMARY KEY | User's email address |
| `token` | varchar | NOT NULL | Reset token |
| `created_at` | timestamp | NOT NULL | Token creation timestamp |

---

### `sessions`
User session data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | varchar | PRIMARY KEY | Session identifier |
| `user_id` | bigint | INDEXED | Foreign key to users |
| `ip_address` | varchar | NULL | IP address of session |
| `user_agent` | text | NULL | Browser user agent |
| `payload` | text | NOT NULL | Session data |
| `last_activity` | integer | INDEXED | Unix timestamp of last activity |

**Indexes:**
- `sessions_pkey` (PRIMARY KEY on `id`)
- `sessions_user_id_index` (on `user_id`)
- `sessions_last_activity_index` (on `last_activity`)

---

## Pageants

### `pageants`
Main pageant/competition events.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique pageant identifier |
| `name` | varchar | NOT NULL | Pageant name |
| `description` | text | NULL | Detailed description |
| `start_date` | date | NULL | Event start date |
| `end_date` | date | NULL | Event end date |
| `pageant_date` | date | NULL | Main event date |
| `start_time` | time | NULL | Event start time |
| `end_time` | time | NULL | Event end time |
| `venue` | varchar | NULL | Venue name |
| `location` | varchar | NULL | Location/address |
| `cover_image` | varchar | NULL | Cover image path |
| `logo` | varchar | NULL | Logo image path |
| `scoring_system` | varchar | NOT NULL | Scoring method (percentage, 1-10, 1-5, points) |
| `ranking_method` | varchar | DEFAULT 'score_average' | Ranking calculation (score_average, rank_sum) |
| `tie_handling` | varchar | DEFAULT 'sequential' | Tie breaking method (sequential, average, minimum) |
| `required_judges` | integer | NULL | Minimum judges required |
| `contestant_type` | varchar | DEFAULT 'solo' | Type of contestants (solo, pairs, both) |
| `progress` | integer | DEFAULT 0 | Setup completion percentage |
| `status` | varchar | NOT NULL | Current status (see check constraint) |
| `is_locked` | boolean | DEFAULT false | Edit lock status |
| `locked_at` | timestamp | NULL | Lock timestamp |
| `locked_by` | bigint | FK to users | User who locked |
| `is_temporarily_editable` | boolean | DEFAULT false | Temporary edit permission flag |
| `temporary_edit_granted_by` | bigint | FK to users | Admin who granted temp access |
| `temporary_edit_granted_at` | timestamp | NULL | Temp permission timestamp |
| `is_edit_permission_granted` | boolean | DEFAULT false | Permanent edit permission flag |
| `edit_permission_granted_to` | bigint | FK to users | User with edit permission |
| `edit_permission_expires_at` | timestamp | NULL | Permission expiration |
| `current_round_id` | bigint | FK to rounds | Currently active round |
| `archive_reason` | varchar | NULL | Reason for archival |
| `archived_at` | timestamp | NULL | Archive timestamp |
| `created_by` | bigint | FK to users | Creator user |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Foreign Keys:**
- `created_by` → `users.id` (ON DELETE NO ACTION)
- `current_round_id` → `rounds.id` (ON DELETE SET NULL)
- `edit_permission_granted_to` → `users.id` (ON DELETE NO ACTION)
- `locked_by` → `users.id` (ON DELETE NO ACTION)
- `temporary_edit_granted_by` → `users.id` (ON DELETE SET NULL)

**Check Constraints:**
- `scoring_system` ∈ {'percentage', '1-10', '1-5', 'points'}
- `status` ∈ {'Pending_Approval', 'Draft', 'Setup', 'Active', 'Completed', 'Unlocked_For_Edit', 'Archived', 'Cancelled'}
- `contestant_type` ∈ {'solo', 'pairs', 'both'}
- `ranking_method` ∈ {'score_average', 'rank_sum'}
- `tie_handling` ∈ {'sequential', 'average', 'minimum'}

---

### `pageant_organizers`
Links organizers to pageants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `user_id` | bigint | FK to users | Organizer user reference |
| `is_primary` | boolean | DEFAULT false | Primary organizer flag |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Unique Constraint:** (`pageant_id`, `user_id`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `user_id` → `users.id` (ON DELETE CASCADE)

---

### `pageant_judges`
Judge assignments for pageants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `user_id` | bigint | FK to users | Judge user reference |
| `role` | varchar | NULL | Judge role/title |
| `assigned_categories` | jsonb | NULL | Categories assigned to judge |
| `assigned_segments` | jsonb | NULL | Segments assigned to judge |
| `active` | boolean | DEFAULT true | Active status |
| `notes` | text | NULL | Additional notes |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Unique Constraint:** (`pageant_id`, `user_id`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `user_id` → `users.id` (ON DELETE CASCADE)

---

### `pageant_tabulators`
Tabulator assignments for pageants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `user_id` | bigint | FK to users | Tabulator user reference |
| `active` | boolean | DEFAULT true | Active status |
| `notes` | text | NULL | Additional notes |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Unique Constraint:** (`pageant_id`, `user_id`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `user_id` → `users.id` (ON DELETE CASCADE)

---

## Contestants

### `contestants`
Participants in pageants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique contestant identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `user_id` | bigint | FK to users | Linked user account |
| `name` | varchar | NOT NULL | Contestant name |
| `number` | integer | NOT NULL | Contestant number |
| `gender` | varchar | NULL | Gender |
| `origin` | varchar | NULL | Place of origin |
| `age` | integer | NULL | Age |
| `photo` | varchar | NULL | Profile photo path |
| `bio` | text | NULL | Biography |
| `is_pair` | boolean | DEFAULT false | Is this a pair contestant |
| `pair_id` | uuid | INDEXED | Unique pair identifier |
| `scores` | jsonb | NULL | Cached scores data |
| `metadata` | jsonb | NULL | Additional metadata |
| `active` | boolean | DEFAULT true | Active status |
| `rank` | integer | NULL | Final rank |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `contestants_pkey` (PRIMARY KEY on `id`)
- `contestants_pageant_number_gender_index` (on `pageant_id`, `number`, `gender`)
- `contestants_pair_id_index` (on `pair_id`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `user_id` → `users.id` (ON DELETE NO ACTION)

---

### `contestant_members`
Links individual contestants to pair contestants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `pair_contestant_id` | bigint | FK to contestants | Pair contestant reference |
| `member_contestant_id` | bigint | FK to contestants, UNIQUE | Individual contestant reference |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Unique Constraints:**
- `member_contestant_id` (each member can only be in one pair)
- (`pair_contestant_id`, `member_contestant_id`)

**Foreign Keys:**
- `pair_contestant_id` → `contestants.id` (ON DELETE CASCADE)
- `member_contestant_id` → `contestants.id` (ON DELETE CASCADE)

---

### `contestant_images`
Additional images for contestants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `contestant_id` | bigint | FK to contestants | Contestant reference |
| `image_path` | varchar | NOT NULL | Image file path |
| `is_primary` | boolean | DEFAULT false | Primary image flag |
| `display_order` | smallint | DEFAULT 0 | Display order |
| `caption` | text | NULL | Image caption |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Foreign Keys:**
- `contestant_id` → `contestants.id` (ON DELETE CASCADE)

---

## Rounds & Scoring

### `rounds`
Competition rounds within a pageant.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique round identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `name` | varchar | NOT NULL | Round name |
| `description` | text | NULL | Round description |
| `type` | varchar | NULL | Round type |
| `identifier` | varchar | INDEXED | Unique identifier string |
| `weight` | integer | DEFAULT 0 | Weight for overall scoring |
| `display_order` | integer | DEFAULT 0 | Display order |
| `top_n_proceed` | integer | NULL | Number advancing to next round |
| `use_for_minor_awards` | boolean | DEFAULT false | Use for minor awards calculation |
| `is_active` | boolean | DEFAULT true | Active status |
| `is_locked` | boolean | DEFAULT false | Edit lock status |
| `locked_at` | timestamp | NULL | Lock timestamp |
| `locked_by` | bigint | FK to users | User who locked round |
| `scoring_config` | json | NULL | Scoring configuration |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `rounds_pkey` (PRIMARY KEY on `id`)
- `rounds_identifier_index` (on `identifier`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `locked_by` → `users.id` (ON DELETE SET NULL)

---

### `scores`
Individual scores given by judges.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique score identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `round_id` | bigint | FK to rounds | Round reference |
| `criteria_id` | bigint | FK to criteria | Criteria reference |
| `contestant_id` | bigint | FK to contestants | Contestant reference |
| `judge_id` | bigint | FK to users | Judge reference |
| `score` | numeric | NOT NULL | Score value |
| `notes` | text | NULL | Judge's notes |
| `submitted_at` | timestamp | NULL | Submission timestamp |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `scores_pkey` (PRIMARY KEY on `id`)
- `unique_score_per_judge` (UNIQUE on `pageant_id`, `round_id`, `criteria_id`, `contestant_id`, `judge_id`)
- `scores_pageant_id_round_id_index` (on `pageant_id`, `round_id`)
- `scores_contestant_id_round_id_index` (on `contestant_id`, `round_id`)
- `scores_judge_id_pageant_id_index` (on `judge_id`, `pageant_id`)
- `idx_scores_pageant_round_criteria` (on `pageant_id`, `round_id`, `criteria_id`)
- `idx_scores_contestant_pageant` (on `contestant_id`, `pageant_id`)
- `idx_scores_judge_progress` (on `judge_id`, `pageant_id`, `round_id`)
- `idx_scores_judge_round_contestant` (on `judge_id`, `round_id`, `contestant_id`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `round_id` → `rounds.id` (ON DELETE CASCADE)
- `criteria_id` → `criteria.id` (ON DELETE CASCADE)
- `contestant_id` → `contestants.id` (ON DELETE CASCADE)
- `judge_id` → `users.id` (ON DELETE CASCADE)

---

## Categories & Criteria

### `segments`
Competition segments (deprecated in favor of rounds).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique segment identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `name` | varchar | NOT NULL | Segment name |
| `description` | text | NULL | Segment description |
| `start_datetime` | timestamp | NULL | Start date and time |
| `end_datetime` | timestamp | NULL | End date and time |
| `type` | varchar | NULL | Segment type |
| `weight` | integer | DEFAULT 0 | Weight for scoring |
| `max_score` | numeric | NULL | Maximum possible score |
| `scoring_type` | varchar | NULL | Type of scoring |
| `status` | varchar | DEFAULT 'Pending' | Current status |
| `display_order` | integer | DEFAULT 0 | Display order |
| `rules` | jsonb | NULL | Segment rules |
| `scoring_criteria` | jsonb | NULL | Scoring criteria |
| `active` | boolean | DEFAULT true | Active status |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)

**Check Constraints:**
- `status` ∈ {'Pending', 'In Progress', 'Completed', 'Cancelled'}

---

### `categories`
Scoring categories for pageants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique category identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `name` | varchar | NOT NULL | Category name |
| `description` | text | NULL | Category description |
| `weight` | integer | DEFAULT 0 | Weight for scoring |
| `max_score` | numeric | NULL | Maximum possible score |
| `scoring_type` | varchar | NULL | Type of scoring |
| `display_order` | integer | DEFAULT 0 | Display order |
| `criteria` | jsonb | NULL | Category criteria |
| `active` | boolean | DEFAULT true | Active status |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)

---

### `criteria`
Individual scoring criteria within rounds.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique criteria identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `round_id` | bigint | FK to rounds | Round reference |
| `segment_id` | bigint | FK to segments | Segment reference (legacy) |
| `category_id` | bigint | FK to categories | Category reference |
| `name` | varchar | NOT NULL | Criteria name |
| `description` | text | NULL | Criteria description |
| `weight` | integer | DEFAULT 0 | Weight percentage |
| `min_score` | numeric | DEFAULT 0 | Minimum score |
| `max_score` | numeric | NOT NULL | Maximum score |
| `allow_decimals` | boolean | DEFAULT false | Allow decimal scores |
| `decimal_places` | integer | DEFAULT 0 | Number of decimal places |
| `display_order` | integer | DEFAULT 0 | Display order |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `round_id` → `rounds.id` (ON DELETE CASCADE)
- `segment_id` → `segments.id` (ON DELETE CASCADE)
- `category_id` → `categories.id` (ON DELETE SET NULL)

---

## Activity & Audit Logging

### `activities`
Real-time activity feed for pageant events.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique activity identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `user_id` | bigint | FK to users | User who performed action |
| `action_type` | varchar | NOT NULL | Type of action |
| `entity_type` | varchar | NULL | Entity type affected |
| `entity_id` | bigint | NULL | Entity ID affected |
| `description` | text | NOT NULL | Activity description |
| `icon` | varchar | NULL | Icon identifier |
| `metadata` | jsonb | NULL | Additional metadata |
| `ip_address` | varchar | NULL | IP address |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `user_id` → `users.id` (ON DELETE SET NULL)

---

### `audit_logs`
System-wide audit trail.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique log identifier |
| `user_id` | bigint | FK to users | User who performed action |
| `user_role` | varchar | NULL | User's role at time of action |
| `action_type` | varchar | NOT NULL | Type of action |
| `target_entity` | varchar | NULL | Target entity type |
| `target_id` | bigint | NULL | Target entity ID |
| `details` | text | NULL | Action details |
| `ip_address` | inet | NULL | IP address |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `audit_logs_pkey` (PRIMARY KEY on `id`)
- `audit_logs_user_id_action_type_target_entity_target_id_index` (on `user_id`, `action_type`, `target_entity`, `target_id`)

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE NO ACTION)

---

## Permissions & Access

### `role_permissions`
Role-based permission definitions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `role` | varchar | INDEXED | Role name |
| `permission_key` | varchar | NOT NULL | Permission key identifier |
| `permission_name` | varchar | NOT NULL | Permission display name |
| `permission_description` | text | NULL | Permission description |
| `granted` | boolean | DEFAULT false | Permission granted status |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `role_permissions_pkey` (PRIMARY KEY on `id`)
- `role_permissions_role_index` (on `role`)
- `role_permissions_role_permission_key_unique` (UNIQUE on `role`, `permission_key`)

---

### `edit_access_requests`
Requests for temporary edit access to locked pageants.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Unique identifier |
| `pageant_id` | bigint | FK to pageants | Pageant reference |
| `organizer_id` | bigint | FK to users | Requesting organizer |
| `reason` | text | NOT NULL | Request reason |
| `status` | varchar | NOT NULL | Request status (pending, approved, rejected) |
| `reviewed_by` | bigint | FK to users | Admin who reviewed |
| `admin_notes` | text | NULL | Admin's notes |
| `reviewed_at` | timestamp | NULL | Review timestamp |
| `created_at` | timestamp | NOT NULL | Record creation timestamp |
| `updated_at` | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- `edit_access_requests_pkey` (PRIMARY KEY on `id`)
- `edit_access_requests_pageant_id_organizer_id_status_index` (on `pageant_id`, `organizer_id`, `status`)

**Foreign Keys:**
- `pageant_id` → `pageants.id` (ON DELETE CASCADE)
- `organizer_id` → `users.id` (ON DELETE CASCADE)
- `reviewed_by` → `users.id` (ON DELETE SET NULL)

**Check Constraints:**
- `status` ∈ {'pending', 'approved', 'rejected'}

---

## System Tables

### `migrations`
Database migration tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | integer | PRIMARY KEY | Migration ID |
| `migration` | varchar | NOT NULL | Migration name |
| `batch` | integer | NOT NULL | Migration batch number |

---

### `jobs`
Queue job tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Job ID |
| `queue` | varchar | INDEXED | Queue name |
| `payload` | text | NOT NULL | Job payload |
| `attempts` | smallint | NOT NULL | Number of attempts |
| `reserved_at` | integer | NULL | Unix timestamp when reserved |
| `available_at` | integer | NOT NULL | Unix timestamp when available |
| `created_at` | integer | NOT NULL | Unix timestamp of creation |

**Indexes:**
- `jobs_pkey` (PRIMARY KEY on `id`)
- `jobs_queue_index` (on `queue`)

---

### `failed_jobs`
Failed queue jobs.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PRIMARY KEY | Failed job ID |
| `uuid` | varchar | UNIQUE | Unique job identifier |
| `connection` | text | NOT NULL | Queue connection |
| `queue` | text | NOT NULL | Queue name |
| `payload` | text | NOT NULL | Job payload |
| `exception` | text | NOT NULL | Exception details |
| `failed_at` | timestamp | NOT NULL | Failure timestamp |

---

### `job_batches`
Batch job tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | varchar | PRIMARY KEY | Batch ID |
| `name` | varchar | NOT NULL | Batch name |
| `total_jobs` | integer | NOT NULL | Total jobs in batch |
| `pending_jobs` | integer | NOT NULL | Pending jobs count |
| `failed_jobs` | integer | NOT NULL | Failed jobs count |
| `failed_job_ids` | text | NOT NULL | Failed job IDs |
| `options` | text | NULL | Batch options |
| `cancelled_at` | integer | NULL | Unix timestamp when cancelled |
| `created_at` | integer | NOT NULL | Unix timestamp of creation |
| `finished_at` | integer | NULL | Unix timestamp when finished |

---

### `cache`
Application cache storage.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `key` | varchar | PRIMARY KEY | Cache key |
| `value` | text | NOT NULL | Cached value |
| `expiration` | integer | NOT NULL | Unix timestamp expiration |

---

### `cache_locks`
Cache lock management.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `key` | varchar | PRIMARY KEY | Lock key |
| `owner` | varchar | NOT NULL | Lock owner |
| `expiration` | integer | NOT NULL | Unix timestamp expiration |

---

## Database Sequences

The following sequences are used for auto-incrementing primary keys:

- `migrations_id_seq` (int4, starts at 1)
- `users_id_seq` (int8, starts at 1)
- `jobs_id_seq` (int8, starts at 1)
- `failed_jobs_id_seq` (int8, starts at 1)
- `pageants_id_seq` (int8, starts at 1)
- `pageant_organizers_id_seq` (int8, starts at 1)
- `audit_logs_id_seq` (int8, starts at 1)
- `contestants_id_seq` (int8, starts at 1)
- `segments_id_seq` (int8, starts at 1)
- `categories_id_seq` (int8, starts at 1)
- `activities_id_seq` (int8, starts at 1)
- `pageant_judges_id_seq` (int8, starts at 1)
- `pageant_tabulators_id_seq` (int8, starts at 1)
- `criteria_id_seq` (int8, starts at 1)
- `scores_id_seq` (int8, starts at 1)
- `contestant_images_id_seq` (int8, starts at 1)
- `rounds_id_seq` (int8, starts at 1)
- `contestant_members_id_seq` (int8, starts at 1)
- `edit_access_requests_id_seq` (int8, starts at 1)
- `role_permissions_id_seq` (int8, starts at 1)

---

## Entity Relationships

### Key Relationships

1. **Pageants → Users**
   - Created by admin/organizer
   - Managed by multiple organizers (many-to-many via `pageant_organizers`)
   - Scored by judges (many-to-many via `pageant_judges`)
   - Tabulated by tabulators (many-to-many via `pageant_tabulators`)

2. **Pageants → Rounds → Criteria → Scores**
   - Pageants contain multiple rounds
   - Rounds contain multiple criteria
   - Scores link judges, contestants, and criteria
   - Unique constraint ensures one score per judge per criterion per contestant

3. **Contestants → Scores**
   - Contestants receive scores from judges
   - Contestants can be solo or pairs
   - Pair contestants link to individual members via `contestant_members`

4. **Activity & Audit Logging**
   - `activities` tracks pageant-specific events
   - `audit_logs` tracks system-wide administrative actions

5. **Access Control**
   - Role-based permissions via `role_permissions`
   - Temporary access via `edit_access_requests`
   - Pageant locking mechanism on `pageants` and `rounds`

---

## Notes

- **PostgreSQL Features:** Uses JSONB columns for flexible metadata storage
- **Cascading Deletes:** Most foreign keys cascade on delete to maintain referential integrity
- **Soft Deletes:** Not implemented; uses `active` flags where needed
- **Timestamps:** All main tables include `created_at` and `updated_at` timestamps
- **Locking Mechanism:** Pageants and rounds can be locked to prevent edits during judging
- **Scoring System:** Flexible scoring supports multiple methods (percentage, 1-10, 1-5, points)
- **Ranking Methods:** Supports score averaging or rank sum calculations with configurable tie handling

---

**End of Database Schema Documentation**
