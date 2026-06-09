# Copilot Instructions — seminar-management-system

Purpose
-------
This file tells GitHub Copilot (and other repository assistants) how to provide useful, safe, and repository-consistent suggestions for the `seminar-management-system` PHP application.

Repository summary
------------------
- Language: PHP (procedural), HTML, CSS, JavaScript
- Framework: None — simple PHP application designed for Laragon/XAMPP
- Important folders: `admin/`, `teacher/`, `includes/`, `assets/`, `database/`
- Entry points: `index.php`, `register.php`, `seminars.php`, and files under `admin/` and `teacher/`

Developer guidelines
--------------------
- Follow the existing procedural style; avoid converting the codebase to a different architecture.
- Use existing include files (`includes/public_header.php`, `includes/admin_header.php`, etc.) when adding pages.
- Keep HTML structure consistent with `public_header.php`/`public_footer.php` and `admin_header.php`/`admin_footer.php`.
- When editing database code, prefer prepared statements or parameterized queries to avoid SQL injection.
- Do not modify `includes/config.php` or `includes/db.php` credentials without explicit instruction.
- Validate and sanitize all external input (POST/GET) and display user-facing errors safely.

Security and safety
-------------------
- Add CSRF protection when adding or modifying forms.
- Escape outputs where appropriate to prevent XSS.
- Never include secrets or API keys in code; point to `includes/config.php` for DB settings.

Schema and migrations
---------------------
- The schema is in `database/schema.sql`. If you change the database layout, update that file with the corresponding DDL.

Testing & running
-----------------
- This project runs under Laragon/XAMPP. To run locally:
  1. Start Apache and MySQL via Laragon/XAMPP.
  2. Import `database/schema.sql` into a new database.
  3. Update DB credentials in `includes/config.php`.

Style & formatting
------------------
- Keep code changes small and focused. Prefer explicit, readable code over clever shortcuts.
- Match the existing indentation and brace style used in nearby files.

When responding as Copilot
-------------------------
- Provide concise code suggestions and explain security implications for DB and input handling.
- When introducing new dependencies, explain trade-offs and ask for confirmation.
- If a suggested change affects multiple files, list affected files and required manual steps.
- For UI changes, prefer using existing `assets/css/style.css` and `assets/js/main.js`.

PR guidance
-----------
- Use small commits with clear messages.
- In the PR description, include how to test changes locally and any DB updates.

If uncertain
----------
- Ask a short clarifying question before making broad architectural changes.

Thank you — treat this as the repository-specific policy for automated assistants and for Copilot suggestions.
