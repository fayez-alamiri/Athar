# ATHAR (أَثَر) — Software Requirements Specification

## 1. Purpose

ATHAR is an Arabic-first literary platform for discovering, publishing, organizing, and reading quotations. The application is implemented with Laravel 13, PHP, Blade, Tailwind CSS 4, Vite, Alpine/vanilla JavaScript, SQLite for local development and PostgreSQL (Neon) for production.

## 2. User roles

- **Guest:** can browse, search, filter, copy, read the daily quote, browse authors, and use Zen mode. Publishing, liking, saving, and personal account pages require authentication.
- **Registered member:** can do everything a guest can, plus publish quick and book quotes, like/save quotations, manage only their own posts, and manage their own profile/avatar.

## 3. Functional requirements

### FR-01 Authentication and account
- Registration validates a 2–50 character name, unique valid email, and password of at least 8 characters with confirmation.
- Login uses Laravel authentication and throttling.
- Logout is a CSRF-protected POST request.
- Passwords are securely hashed by Laravel.
- Account page displays profile information, avatar controls, quote counts, received likes, and saved quote count.

### FR-02 Languages and themes
- Arabic is the default interface language and uses RTL layout.
- English is supported with LTR layout.
- The language switch persists for guests and registered users.
- The light/dark theme preference is persisted in browser local storage.
- Quote content is displayed in its original language and is not machine-translated.

### FR-03 Quote content
- Quick user quotes contain quote text (3–155 characters) and optional attribution.
- Book quotes require book title, uploaded cover image, quote text (3–1000 characters), and attributed author.
- Publisher identity is stored separately from the original attributed author.
- Any authenticated user may publish either quote type.
- Guests may browse both quote types.

### FR-04 Feed and discovery
- The homepage presents public quotes in a paginated feed.
- Users may filter by all, quick, and book quote types.
- Search includes quote text, book title, attributed author, author catalog name, and publisher name.
- Sort options include newest and most liked.
- Visitors can browse an author directory and an author's quotation page.

### FR-05 Authors and books catalog
- Author records store a normalized author name and unique URL slug.
- Book records store a normalized title, unique URL slug, and optional cover path.
- Quotes may be associated with an author and, for book quotes, a book.
- A migration backfills catalog relationships from existing quote metadata without discarding legacy fields or records.

### FR-06 Daily quotation
- The daily quote is selected according to the Asia/Riyadh calendar date.
- The selected quote ID is persisted in `daily_quotes` so that requests, instances, and restarts keep the same selection for that date.
- Empty quote databases return a designed empty state.

### FR-07 Zen reading mode
- Displays one quote at a time in a distraction-reduced layout.
- Supports previous/next links and keyboard arrow navigation.
- Escape exits Zen mode.
- Keyboard controls do not hijack input fields or editable controls.

### FR-08 Likes and saved quotes
- Only authenticated members may like/unlike or save/unsave.
- One like and one saved relationship per user/quote pair are enforced by database unique constraints.
- Saved quotes are private to the current account.

### FR-09 Owner management
- Members can view all their own quick and book quotes.
- Only the owner may edit or delete a quote.
- Single and bulk deletion use authenticated, CSRF-protected requests and explicit confirmation in the interface.
- Dependent likes/saves are removed through foreign-key cascade behavior.

### FR-10 Sharing and profile images
- Quotes can be copied to the clipboard with success/failure feedback.
- The sharing modal creates a 1080 × 1080 PNG using four templates, supports download, and tries Web Share API where supported.
- Avatar and book cover uploads are validated by server-side MIME/type and size rules.
- Production upload images are persisted in PostgreSQL as image data URIs in this deployment configuration.

## 4. Non-functional requirements

- **Security:** server-side validation, CSRF protection, authorization policies, hashed passwords, safe uploads, and no secret values in source control.
- **Data integrity:** foreign keys, unique constraints, additive migrations, and no production database reset during startup.
- **Performance:** pagination, eager loading, aggregate counts, indexes, optimized Vite assets, and production Laravel caches.
- **Accessibility:** semantic HTML, keyboard focus, meaningful control labels, reduced-motion respect, and readable contrast.
- **Responsive design:** phone, tablet, and desktop layouts with RTL/LTR support.
- **Deployment:** Docker + Nginx on Render with Neon PostgreSQL. Production startup runs migrations and never automatically creates demo accounts.

## 5. Acceptance checks

1. Guests can browse/search/filter quotes and authors.
2. Registered users can publish a valid quick quote and a valid book quote.
3. Invalid and incomplete submissions are rejected server-side.
4. Like and save actions require authentication and prevent duplicate records.
5. Users cannot modify other users' posts.
6. Daily quote is stable for the Riyadh date.
7. Zen mode supports keyboard navigation and Escape to exit.
8. Arabic/English switching and light/dark theme persist.
9. PNG share cards generate and download.
10. Migrations preserve existing users/quotes and production does not execute the demo seeder.
