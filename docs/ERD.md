# ATHAR — Entity Relationship Diagram (ERD)

The database uses a unified `quotes` table for both quick and book quotations. `user_id` identifies the member who published a post. `author_id` / `attributed_author` identify the person credited for the quotation. These identities are intentionally separate.

```mermaid
erDiagram
    USERS ||--o{ QUOTES : publishes
    AUTHORS ||--o{ QUOTES : attributed_to
    BOOKS ||--o{ QUOTES : source_book
    USERS ||--o{ QUOTE_LIKES : creates
    QUOTES ||--o{ QUOTE_LIKES : receives
    USERS ||--o{ SAVED_QUOTES : saves
    QUOTES ||--o{ SAVED_QUOTES : bookmarked
    QUOTES ||--o{ DAILY_QUOTES : featured_on

    USERS {
      bigint id PK
      string name
      string email UK
      string password
      text avatar_path
      string locale
      timestamps
    }
    AUTHORS {
      bigint id PK
      string name UK
      string slug UK
      text bio
      timestamps
    }
    BOOKS {
      bigint id PK
      string title UK
      string slug UK
      text cover_path
      timestamps
    }
    QUOTES {
      bigint id PK
      bigint user_id FK
      bigint author_id FK "nullable"
      bigint book_id FK "nullable"
      string type
      text quote_text
      string book_title "legacy/display field"
      text book_cover_path "legacy/display field"
      string attributed_author "legacy/display field"
      timestamps
    }
    QUOTE_LIKES {
      bigint id PK
      bigint user_id FK
      bigint quote_id FK
      timestamps
      "unique user_id + quote_id"
    }
    SAVED_QUOTES {
      bigint id PK
      bigint user_id FK
      bigint quote_id FK
      timestamps
      "unique user_id + quote_id"
    }
    DAILY_QUOTES {
      date quote_date PK
      bigint quote_id FK
      timestamps
    }
```

## Tables

- **users:** authentication details, profile avatar, and preferred locale.
- **quotes:** both quick and book posts; owner, text, type, optional catalog relations, and legacy book/attribution columns retained for compatibility.
- **authors:** normalized author directory entries, unique by display name and slug.
- **books:** normalized book records keyed by title and slug, with optional cover path.
- **quote_likes:** user-to-quote likes; a unique `(user_id, quote_id)` constraint prevents duplicates.
- **saved_quotes:** private user bookmarks; a unique `(user_id, quote_id)` constraint prevents duplicates.
- **daily_quotes:** one persisted quote selection per Riyadh calendar date; deleting the selected quote removes that day's row through cascade behavior.

## Migrations

The migrations are additive. The catalog migration creates `authors` and `books`, adds nullable `author_id` and `book_id` foreign keys to `quotes`, and backfills existing quote records from the original attribution and book-title fields. The daily-quote migration creates the date-keyed `daily_quotes` table. The application startup runs `php artisan migrate --force`; it does not use `migrate:fresh`, `db:wipe`, or automatic production seeding.
