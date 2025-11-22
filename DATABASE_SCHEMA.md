# Database Schema Documentation

## users

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| name | string | User's full name |
| email | string | User's email address (Unique) |
| email_verified_at | timestamp | Timestamp when email was verified |
| password | string | Hashed password |
| remember_token | string | Token for "remember me" functionality |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |
| username | string | User's username (Unique, Nullable) |
| is_verified | boolean | Whether the user is verified (Default: false) |
| verification_token | string | Token used for verification |
| verification_expires_at | timestamp | Expiration time for verification token |
| role | string | User role (Default: 'user') |
| is_active | boolean | Whether the user account is active (Default: true) |

## password_reset_tokens

| Field name | Data type | Description |
| :--- | :--- | :--- |
| email | string | User's email address (Primary Key) |
| token | string | Password reset token |
| created_at | timestamp | Token creation timestamp |

## sessions

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | string | Session ID (Primary Key) |
| user_id | foreignId | ID of the user (Nullable) |
| ip_address | string(45) | IP address of the session |
| user_agent | text | User agent string |
| payload | longText | Session data payload |
| last_activity | integer | Timestamp of last activity |

## pageants

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| name | string | Name of the pageant |
| description | text | Description of the pageant |
| start_date | date | Start date of the pageant |
| end_date | date | End date of the pageant |
| venue | string | Venue name |
| location | string | Location address/city |
| status | enum | Status: 'Pending_Approval', 'Draft', 'Setup', 'Active', 'Completed', 'Unlocked_For_Edit', 'Archived', 'Cancelled' (Default: 'Pending_Approval') |
| created_by | foreignId | ID of the user who created the pageant |
| is_edit_permission_granted | boolean | Whether edit permission is granted (Default: false) |
| edit_permission_expires_at | dateTime | Expiration of edit permission |
| edit_permission_granted_to | foreignId | ID of the user granted edit permission |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |
| scoring_system | enum | Scoring system type: 'percentage', '1-10', '1-5', 'points' (Default: 'percentage') |
| required_judges | integer | Number of required judges (Default: 0) |
| cover_image | string | Path to cover image |
| logo | string | Path to logo image |
| progress | integer | Progress percentage (Default: 0) |
| pageant_date | date | Date of the pageant |
| is_locked | boolean | Whether the pageant is locked (Default: false) |
| locked_at | timestamp | Timestamp when pageant was locked |
| locked_by | foreignId | ID of the user who locked the pageant |
| current_round_id | foreignId | ID of the current active round |
| contestant_type | enum | Type of contestants: 'solo', 'pairs', 'both' (Default: 'both') |
| is_temporarily_editable | boolean | Whether temporary edit access is active (Default: false) |
| temporary_edit_granted_by | foreignId | ID of the user who granted temporary edit access |
| temporary_edit_granted_at | timestamp | Timestamp when temporary edit access was granted |

## contestants

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| name | string | Contestant's name |
| number | integer | Contestant number |
| origin | string | Origin/Representation |
| age | integer | Contestant's age |
| photo | string | Path to contestant's photo |
| bio | text | Biography |
| scores | jsonb | JSON storage for scores |
| metadata | jsonb | Additional metadata |
| active | boolean | Whether the contestant is active (Default: true) |
| rank | integer | Final rank |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |
| user_id | foreignId | Linked user ID (Nullable) |
| is_pair | boolean | Whether this contestant represents a pair (Default: false) |
| gender | string(10) | Gender of the contestant |
| pair_id | uuid | UUID linking pair members |

## contestant_images

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| contestant_id | foreignId | ID of the contestant |
| image_path | string | Path to the image |
| is_primary | boolean | Whether this is the primary image (Default: false) |
| display_order | smallInteger | Order for display (Default: 0) |
| caption | text | Image caption |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## contestant_members

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pair_contestant_id | foreignId | ID of the pair contestant record |
| member_contestant_id | foreignId | ID of the individual member contestant record |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## pageant_judges

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| user_id | foreignId | ID of the user acting as judge |
| role | string | Role of the judge (e.g., 'judge', 'head_judge') |
| assigned_categories | jsonb | JSON array of assigned category IDs |
| assigned_segments | jsonb | JSON array of assigned segment IDs |
| active | boolean | Whether the judge is active (Default: true) |
| notes | text | Notes about the judge |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## pageant_organizers

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| user_id | foreignId | ID of the user acting as organizer |
| is_primary | boolean | Whether this is the primary organizer (Default: false) |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## pageant_tabulators

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| user_id | foreignId | ID of the user acting as tabulator |
| active | boolean | Whether the tabulator is active (Default: true) |
| notes | text | Notes about the tabulator |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## categories

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| name | string | Category name |
| description | text | Category description |
| weight | integer | Weight in overall scoring (Default: 100) |
| max_score | decimal(8, 2) | Maximum score possible (Default: 100.00) |
| scoring_type | string | Scoring type (Default: 'percentage') |
| display_order | integer | Order for display (Default: 0) |
| criteria | jsonb | Sub-criteria in JSON format |
| active | boolean | Whether the category is active (Default: true) |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## criteria

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| segment_id | foreignId | ID of the segment (Nullable) |
| category_id | foreignId | ID of the category (Nullable) |
| name | string | Criteria name |
| description | text | Criteria description |
| weight | integer | Weight of the criteria (Default: 0) |
| min_score | decimal(5, 2) | Minimum score (Default: 0) |
| max_score | decimal(5, 2) | Maximum score (Default: 100) |
| allow_decimals | boolean | Whether decimals are allowed (Default: true) |
| decimal_places | integer | Number of decimal places (Default: 2) |
| display_order | integer | Order for display (Default: 0) |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |
| round_id | foreignId | ID of the round (Nullable) |

## segments

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| name | string | Segment name |
| description | text | Segment description |
| start_datetime | dateTime | Start date and time |
| end_datetime | dateTime | End date and time |
| type | string | Segment type |
| weight | integer | Weight in overall scoring (Default: 100) |
| max_score | decimal(8, 2) | Maximum score (Default: 100.00) |
| scoring_type | string | Scoring type (Default: 'percentage') |
| status | enum | Status: 'Pending', 'In Progress', 'Completed', 'Cancelled' (Default: 'Pending') |
| display_order | integer | Order for display (Default: 0) |
| rules | jsonb | Rules in JSON format |
| scoring_criteria | jsonb | Scoring criteria in JSON format |
| active | boolean | Whether the segment is active (Default: true) |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## rounds

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| name | string | Round name |
| description | text | Round description |
| type | string | Round type (Default: 'semi-final') |
| weight | integer | Weight percentage (Default: 100) |
| display_order | integer | Order for display (Default: 0) |
| is_active | boolean | Whether the round is active (Default: true) |
| scoring_config | json | Additional scoring configuration |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |
| is_locked | boolean | Whether the round is locked (Default: false) |
| locked_at | timestamp | Timestamp when round was locked |
| locked_by | foreignId | ID of the user who locked the round |
| identifier | string(50) | Unique identifier for round (e.g., SF, F) |

## scores

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| round_id | foreignId | ID of the round |
| criteria_id | foreignId | ID of the criteria |
| contestant_id | foreignId | ID of the contestant |
| judge_id | foreignId | ID of the judge (user) |
| score | decimal(8, 2) | The score value |
| notes | text | Notes from the judge |
| submitted_at | timestamp | Timestamp when score was submitted |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## audit_logs

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| user_id | foreignId | ID of the user (Nullable) |
| user_role | string | Role of the user |
| action_type | string | Type of action performed |
| target_entity | string | Target entity type |
| target_id | unsignedBigInteger | ID of the target entity |
| details | text | Details of the action |
| ip_address | ipAddress | IP address of the user |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## activities

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| user_id | foreignId | ID of the user (Nullable) |
| action_type | string | Type of activity |
| entity_type | string | Entity type involved |
| entity_id | unsignedBigInteger | ID of the entity |
| description | text | Description of the activity |
| icon | string | Icon for UI display |
| metadata | jsonb | Additional metadata |
| ip_address | string | IP address |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## edit_access_requests

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| pageant_id | foreignId | ID of the pageant |
| organizer_id | foreignId | ID of the organizer requesting access |
| reason | text | Reason for request |
| status | enum | Status: 'pending', 'approved', 'rejected' (Default: 'pending') |
| reviewed_by | foreignId | ID of the user who reviewed the request |
| admin_notes | text | Notes from admin |
| reviewed_at | timestamp | Timestamp when request was reviewed |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Record update timestamp |

## jobs

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| queue | string | Queue name |
| payload | longText | Job payload |
| attempts | unsignedTinyInteger | Number of attempts |
| reserved_at | unsignedInteger | Timestamp when reserved |
| available_at | unsignedInteger | Timestamp when available |
| created_at | unsignedInteger | Timestamp when created |

## cache

| Field name | Data type | Description |
| :--- | :--- | :--- |
| key | string | Cache key (Primary Key) |
| value | mediumText | Cache value |
| expiration | integer | Expiration timestamp |

## cache_locks

| Field name | Data type | Description |
| :--- | :--- | :--- |
| key | string | Lock key (Primary Key) |
| owner | string | Lock owner |
| expiration | integer | Expiration timestamp |

## job_batches

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | string | Batch ID (Primary Key) |
| name | string | Batch name |
| total_jobs | integer | Total jobs in batch |
| pending_jobs | integer | Pending jobs count |
| failed_jobs | integer | Failed jobs count |
| failed_job_ids | longText | IDs of failed jobs |
| options | mediumText | Batch options (Nullable) |
| cancelled_at | integer | Cancellation timestamp (Nullable) |
| created_at | integer | Creation timestamp |
| finished_at | integer | Completion timestamp (Nullable) |

## failed_jobs

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | bigint | Primary Key |
| uuid | string | Unique UUID |
| connection | text | Connection name |
| queue | text | Queue name |
| payload | longText | Job payload |
| exception | longText | Exception details |
| failed_at | timestamp | Failure timestamp |

## migrations

| Field name | Data type | Description |
| :--- | :--- | :--- |
| id | integer | Primary Key |
| migration | string | Migration filename |
| batch | integer | Batch number |
