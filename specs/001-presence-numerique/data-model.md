# Data Model

**Feature**: Digital Presence Management
**Date**: 2026-02-01

This document defines the database schema based on the entities identified in the feature specification. The schema is designed for a PostgreSQL database and will be implemented using Laravel Eloquent models and migrations.

---

### `utilisateurs` table
Represents any individual interacting with the system. Managed by Laravel's default authentication system. A `role_id` will be added to link to the `roles` table.

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | `bigint` | Primary Key, Unsigned | Unique identifier for the user. |
| `nom` | `varchar(255)` | Not Null | Full name of the user. |
| `email` | `varchar(255)` | Not Null, Unique | Login email address. |
| `mot_de_passe`| `varchar(255)` | Not Null | Hashed password. |
| `token_authentification` | `varchar(100)` | Nullable | For "remember me" functionality. |
| `horodatages` | `timestamp` | | `created_at` and `updated_at`. |

---

### `roles` table
Stores the different user roles. Managed by `spatie/laravel-permission`.

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | `bigint` | Primary Key, Unsigned | Unique identifier for the role. |
| `nom` | `varchar(255)`| Not Null, Unique | Name of the role (e.g., "Etudiant", "Professeur", "Gestionnaire"). |
| `nom_garde` | `varchar(255)`| Not Null | The guard the role is associated with (e.g., "web", "api"). |
| `horodatages` | `timestamp` | | `created_at` and `updated_at`. |

*Note: `model_has_roles` and `role_has_permissions` pivot tables will be created by the Spatie package.*

---

### `cours` table
Represents a subject that is taught.

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | `bigint` | Primary Key, Unsigned | Unique identifier for the course. |
| `nom` | `varchar(255)` | Not Null | The name of the course. |
| `timestamps`| `timestamp` | | `created_at` and `updated_at`. |

---

### `seances` table
Represents a specific scheduled occurrence of a `Course`. (Named `course_sessions` to avoid conflict with Laravel's session system).

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | `bigint` | Primary Key, Unsigned | Unique identifier for the session. |
| `cours_id`| `bigint` | Foreign Key to `courses.id` | The course this session belongs to. |
| `enseignant_id`| `bigint` | Foreign Key to `users.id` | The teacher conducting the session. |
| `debut_le`| `timestamp` | Not Null | The start date and time of the session. |
| `fin_le` | `timestamp` | Not Null | The end date and time of the session. |
| `timestamps`| `timestamp` | | `created_at` and `updated_at`. |

---

### `emargements` table
Represents the specific action of taking attendance for a `CourseSession`, initiated by a teacher.

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | `bigint` | Primary Key, Unsigned | Unique identifier for the attendance-taking session. |
| `session_cours_id` | `bigint` | Foreign Key to `course_sessions.id` | The scheduled course this attendance session belongs to. |
| `methode` | `varchar(50)` | Not Null | The method used: "qr", "manual". |
| `token` | `varchar(255)` | Nullable, Unique | The unique token for this session, used in the QR code. |
| `expire_le`| `timestamp` | Nullable | The expiration time for the QR code. |
| `latitude_cours` | `decimal(10, 8)`| Nullable | Latitude of the classroom at the time of creation. |
| `longitude_cours`| `decimal(11, 8)`| Nullable | Longitude of the classroom at the time of creation. |
| `timestamps`| `timestamp` | | `created_at` and `updated_at`. |

---

### `presences` table
Records the attendance status of a student for a specific `EmargementSession`.

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | `bigint` | Primary Key, Unsigned | Unique identifier for the attendance record. |
| `session_emargement_id` | `bigint`| Foreign Key to `emargement_sessions.id` | The attendance session this record belongs to. |
| `etudiant_id` | `bigint`| Foreign Key to `users.id` | The student this record is for. |
| `statut` | `varchar(50)` | Not Null | Enum-like: "present", "absent". |
| `heure_scan` | `timestamp` | Nullable | Time the QR code was scanned. |
| `notes` | `text` | Nullable | Comments from the teacher or student. |
| `latitude_etudiant` | `decimal(10, 8)`| Nullable | Student's latitude at the time of scanning. |
| `longitude_etudiant`| `decimal(11, 8)`| Nullable | Student's longitude at the time of scanning. |
| `timestamps` | `timestamp` | | `created_at` and `updated_at`. |

---

### `inscriptions` pivot table
Links students to courses.

| Column | Type | Constraints | Description |
|---|---|---|---|
| `utilisateur_id` | `bigint` | Foreign Key to `users.id` | The ID of the student. |
| `cours_id`| `bigint` | Foreign Key to `courses.id`| The ID of the course. |

This table allows a many-to-many relationship between students and courses.
