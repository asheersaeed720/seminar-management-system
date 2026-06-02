<?php
// ── Site Configuration ──────────────────────────────────────
define('BASE_URL',  getenv('BASE_URL') ?: '');
define('SITE_NAME', 'Seminar Track');
define('SITE_TAGLINE', 'University Seminar Management System');

// ── Database ─────────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'seminar_db');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');
