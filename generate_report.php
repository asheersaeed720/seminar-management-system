<?php
/**
 * WPL Course Report — generates and downloads Seminar_Track_WPL_Report.docx
 * Visit: http://localhost/seminar-management-system/generate_report.php
 */

if (!class_exists('ZipArchive')) {
    die('Enable php_zip in php.ini and restart Laragon.');
}

// ── XML helpers ───────────────────────────────────────────────────────────────

function esc(string $s): string {
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function runs(string $text): string {
    $parts = preg_split('/(\*\*[^*]+\*\*)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $xml = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        if (str_starts_with($part, '**') && str_ends_with($part, '**')) {
            $inner = substr($part, 2, -2);
            $xml .= '<w:r><w:rPr><w:b/><w:bCs/></w:rPr>'
                  . '<w:t xml:space="preserve">' . esc($inner) . '</w:t></w:r>';
        } else {
            $xml .= '<w:r><w:t xml:space="preserve">' . esc($part) . '</w:t></w:r>';
        }
    }
    return $xml;
}

function para(string $text, string $style = 'Normal', bool $center = false): string {
    $jc = $center ? '<w:jc w:val="center"/>' : '';
    return '<w:p><w:pPr><w:pStyle w:val="' . $style . '"/>' . $jc . '</w:pPr>'
         . runs($text) . '</w:p>';
}

function h1(string $t): string { return para($t, 'Heading1'); }
function h2(string $t): string { return para($t, 'Heading2'); }
function h3(string $t): string { return para($t, 'Heading3'); }
function bul(string $t): string { return para($t, 'ListBullet'); }
function num(string $t): string { return para($t, 'ListNumber'); }

function codeLines(array $lines): string {
    $out = '';
    foreach ($lines as $line) {
        $out .= '<w:p><w:pPr><w:pStyle w:val="CodeBlock"/></w:pPr>'
              . '<w:r><w:t xml:space="preserve">' . esc($line) . '</w:t></w:r></w:p>';
    }
    return $out;
}

function pb(): string { return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'; }
function sp(): string { return '<w:p/>'; }

function centerRun(string $text, int $sizePt, string $color, bool $bold = false): string {
    $sz   = $sizePt * 2;
    $bTag = $bold ? '<w:b/><w:bCs/>' : '';
    return '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
         . '<w:r><w:rPr>' . $bTag . '<w:color w:val="' . $color . '"/>'
         . '<w:sz w:val="' . $sz . '"/><w:szCs w:val="' . $sz . '"/></w:rPr>'
         . '<w:t xml:space="preserve">' . esc($text) . '</w:t></w:r></w:p>';
}

function tbl(array $headers, array $rows, ?array $widths = null): string {
    $totalW = 9026;
    $n      = count($headers);
    if (!$widths) {
        $base   = intdiv($totalW, $n);
        $widths = array_fill(0, $n, $base);
    }

    $grid = implode('', array_map(fn($w) => '<w:gridCol w:w="' . $w . '"/>', $widths));

    $xml = '<w:tbl><w:tblPr>'
         . '<w:tblStyle w:val="TableGrid"/>'
         . '<w:tblW w:w="' . $totalW . '" w:type="dxa"/>'
         . '<w:tblBorders>'
         . '<w:top    w:val="single" w:sz="6" w:color="1A3C6E"/>'
         . '<w:left   w:val="single" w:sz="6" w:color="1A3C6E"/>'
         . '<w:bottom w:val="single" w:sz="6" w:color="1A3C6E"/>'
         . '<w:right  w:val="single" w:sz="6" w:color="1A3C6E"/>'
         . '<w:insideH w:val="single" w:sz="4" w:color="BBBBBB"/>'
         . '<w:insideV w:val="single" w:sz="4" w:color="BBBBBB"/>'
         . '</w:tblBorders>'
         . '<w:tblCellMar>'
         . '<w:top w:w="80" w:type="dxa"/><w:left w:w="108" w:type="dxa"/>'
         . '<w:bottom w:w="80" w:type="dxa"/><w:right w:w="108" w:type="dxa"/>'
         . '</w:tblCellMar>'
         . '</w:tblPr>'
         . '<w:tblGrid>' . $grid . '</w:tblGrid>';

    // Header row
    $xml .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
    foreach ($headers as $i => $h) {
        $w    = $widths[$i] ?? intdiv($totalW, $n);
        $xml .= '<w:tc>'
              . '<w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/>'
              . '<w:shd w:val="clear" w:color="auto" w:fill="1A3C6E"/></w:tcPr>'
              . '<w:p><w:r><w:rPr><w:b/><w:bCs/><w:color w:val="FFFFFF"/>'
              . '<w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr>'
              . '<w:t xml:space="preserve">' . esc($h) . '</w:t></w:r></w:p></w:tc>';
    }
    $xml .= '</w:tr>';

    // Data rows
    foreach ($rows as $ri => $row) {
        $fill = ($ri % 2 === 0) ? 'F5F8FC' : 'FFFFFF';
        $xml .= '<w:tr>';
        foreach ($row as $ci => $cell) {
            $w    = $widths[$ci] ?? intdiv($totalW, $n);
            $xml .= '<w:tc>'
                  . '<w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/>'
                  . '<w:shd w:val="clear" w:color="auto" w:fill="' . $fill . '"/></w:tcPr>'
                  . '<w:p>' . runs($cell) . '</w:p></w:tc>';
        }
        $xml .= '</w:tr>';
    }

    return $xml . '</w:tbl>';
}

// ── Document body ─────────────────────────────────────────────────────────────

$body = '';

// Cover page
$body .= sp() . sp() . sp();
$body .= centerRun('WEB PROGRAMMING LANGUAGES (WPL)', 12, '777777', true);
$body .= centerRun('COURSE PROJECT REPORT', 12, '777777', true);
$body .= sp();
$body .= centerRun('Seminar Track', 28, '1A3C6E', true);
$body .= centerRun('University Seminar Management System', 14, '555555');
$body .= sp() . sp();

// Cover metadata table (centred)
$meta = [
    ['Submitted by', 'Asheer Saeed'],
    ['Email',        'asheersaeed313@gmail.com'],
    ['Course',       'Web Programming Languages (WPL)'],
    ['Submission',   'June 2, 2026'],
];
$body .= '<w:tbl><w:tblPr>'
       . '<w:tblW w:w="5000" w:type="dxa"/><w:jc w:val="center"/>'
       . '<w:tblBorders>'
       . '<w:top    w:val="single" w:sz="4" w:color="CCCCCC"/>'
       . '<w:left   w:val="single" w:sz="4" w:color="CCCCCC"/>'
       . '<w:bottom w:val="single" w:sz="4" w:color="CCCCCC"/>'
       . '<w:right  w:val="single" w:sz="4" w:color="CCCCCC"/>'
       . '<w:insideH w:val="single" w:sz="4" w:color="CCCCCC"/>'
       . '<w:insideV w:val="single" w:sz="4" w:color="CCCCCC"/>'
       . '</w:tblBorders>'
       . '</w:tblPr><w:tblGrid><w:gridCol w:w="2000"/><w:gridCol w:w="3000"/></w:tblGrid>';
foreach ($meta as $row) {
    $body .= '<w:tr>'
           . '<w:tc><w:tcPr><w:tcW w:w="2000" w:type="dxa"/>'
           . '<w:shd w:val="clear" w:color="auto" w:fill="EEF4FF"/></w:tcPr>'
           . '<w:p><w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">' . esc($row[0]) . '</w:t></w:r></w:p></w:tc>'
           . '<w:tc><w:tcPr><w:tcW w:w="3000" w:type="dxa"/></w:tcPr>'
           . '<w:p><w:r><w:t xml:space="preserve">' . esc($row[1]) . '</w:t></w:r></w:p></w:tc>'
           . '</w:tr>';
}
$body .= '</w:tbl>';
$body .= sp() . sp();
$body .= centerRun('A full-stack web application for managing university seminars,', 11, '555555');
$body .= centerRun('built with Core PHP, MySQL, and Bootstrap 5.', 11, '555555');
$body .= pb();

// 1. Introduction
$body .= h1('1. Introduction');
$body .= para('**Seminar Track** is a full-stack web application developed as a course project for the Web Programming Languages (WPL) module. The system digitises and streamlines the management of university seminars, replacing manual paper-based processes with an efficient, role-based web platform accessible from any modern browser.');
$body .= sp();
$body .= para('The application caters to three distinct user groups: **Administrators**, who control the entire platform; **Teachers**, who are assigned to seminars and manage attendance; and **Students / Public Users**, who browse upcoming seminars and self-register without requiring an account.');
$body .= sp();
$body .= para('The project demonstrates practical application of core web programming concepts including server-side scripting with PHP, relational database management with MySQL, responsive UI design with Bootstrap 5, and web security best practices such as CSRF protection and bcrypt password hashing.');

// 2. Objectives
$body .= h1('2. Project Objectives');
$body .= num('Provide a **centralised platform** for creating, managing, and publishing university seminars.');
$body .= num('Allow **public self-registration** for seminars without requiring students to create accounts.');
$body .= num('Enable **role-based access control** separating Admin, Teacher, and public user capabilities.');
$body .= num('Automate **capacity tracking** to prevent over-registration at seminars.');
$body .= num('Facilitate **digital attendance marking** by assigned teachers during live seminars.');
$body .= num('Generate **attendance reports** per seminar for administrative review.');
$body .= num('Demonstrate proficiency in **Core PHP, PDO, MySQL, HTML/CSS, Bootstrap 5**, and secure web development practices.');

// 3. Technologies
$body .= h1('3. Technologies Used');
$body .= tbl(
    ['Category', 'Technology', 'Purpose'],
    [
        ['Server-Side Language',  'PHP 8.1+',                   'Business logic, routing, session handling'],
        ['Database',              'MySQL 8.0 (InnoDB)',          'Persistent data storage with FK constraints'],
        ['Database Access',       'PDO (PHP Data Objects)',      'Prepared statements, SQL injection prevention'],
        ['Frontend Framework',    'Bootstrap 5.3',               'Responsive layout and UI components'],
        ['Icons',                 'Font Awesome 6.5',            'Interface iconography'],
        ['Typography',            'Google Fonts — Poppins',      'Clean, modern UI font'],
        ['Form Validation',       'jQuery Validate 1.19',        'Client-side form validation'],
        ['AJAX',                  'Vanilla JS Fetch API',        'Attendance marking without page reload'],
        ['Local Server',          'Laragon (Apache + PHP)',      'Development environment on Windows'],
        ['Version Control',       'Git',                         'Source code management'],
    ],
    [2600, 2500, 3926]
);
$body .= pb();

// 4. Architecture
$body .= h1('4. System Architecture');
$body .= para('The application follows a **three-tier architecture** without using an MVC framework, keeping the codebase straightforward and aligned with Core PHP learning objectives.');
$body .= h2('4.1  Presentation Tier');
$body .= para('All HTML output is generated server-side using PHP templates. Shared layout components (public_header.php, admin_header.php, teacher_header.php and matching footers) are included via require_once, ensuring consistent navigation and assets across all pages.');
$body .= h2('4.2  Logic / Application Tier');
$body .= para('Business logic lives in the includes/ directory as reusable function libraries:');
$body .= bul('**config.php** — site-wide constants: DB credentials, SITE_NAME, BASE_URL');
$body .= bul('**db.php** — singleton PDO connection factory via getDB()');
$body .= bul('**functions.php** — all query functions, helpers (formatting, flash messages, CSRF)');
$body .= bul('**auth.php** — session management, login/logout, role guards');
$body .= h2('4.3  Data Tier');
$body .= para('MySQL 8 with InnoDB storage engine and utf8mb4 character set. Relationships are enforced with foreign key constraints using ON DELETE CASCADE or SET NULL rules as appropriate.');
$body .= sp();
$body .= para('**Design Note:** getDB() uses a static local variable (static $pdo = null) to implement the Singleton pattern, ensuring exactly one PDO instance per HTTP request regardless of how many files call getDB().');

// 5. Database
$body .= h1('5. Database Design');
$body .= h2('5.1  Entity-Relationship Overview');
$body .= para('The database (seminar_db) contains five tables:');
$body .= bul('**users** — both admins and teachers, distinguished by a role ENUM.');
$body .= bul('**seminars** — core event data (title, date, time, venue, capacity, status).');
$body .= bul('**seminar_teachers** — many-to-many join linking teachers to seminars.');
$body .= bul('**registrations** — public student registrations, no account required.');
$body .= bul('**attendance** — one record per registration per seminar, marked by a teacher.');
$body .= sp();
$body .= codeLines([
    '  users ─────────────┬──────────── seminars',
    '  (admin / teacher)  │                 │',
    '                     │   seminar_teachers (join)',
    '                     │                 │',
    '                     │          registrations',
    '                     │                 │',
    '                     └──── attendance (marked_by → users)',
]);
$body .= h2('5.2  Table Descriptions');
$body .= para('**users** — Stores both admins and teachers.');
$body .= tbl(
    ['Column', 'Type', 'Description'],
    [
        ['id',               'INT UNSIGNED PK',        'Auto-increment primary key'],
        ['name',             'VARCHAR(100)',            'Full name'],
        ['email',            'VARCHAR(100) UNIQUE',     'Login email (must be unique)'],
        ['password',         'VARCHAR(255)',            'bcrypt hashed password'],
        ['role',             "ENUM('admin','teacher')", 'Determines access level'],
        ['phone, department','VARCHAR',                 'Optional contact information'],
    ],
    [2000, 2600, 4426]
);
$body .= sp();
$body .= para('**seminars** — Core seminar scheduling data.');
$body .= tbl(
    ['Column', 'Type', 'Description'],
    [
        ['id',           'INT UNSIGNED PK',                          'Auto-increment primary key'],
        ['title',        'VARCHAR(200)',                             'Seminar title'],
        ['speaker',      'VARCHAR(150)',                             'Presenter name'],
        ['seminar_date', 'DATE',                                    'Date of the seminar'],
        ['seminar_time', 'TIME',                                    'Start time'],
        ['venue',        'VARCHAR(200)',                             'Location / room'],
        ['capacity',     'INT UNSIGNED',                            'Maximum registrations allowed'],
        ['status',       "ENUM('upcoming','ongoing','completed','cancelled')", 'Lifecycle state'],
        ['created_by',   'FK → users.id SET NULL',                 'Admin who created the record'],
    ],
    [2000, 2900, 4126]
);
$body .= sp();
$body .= para('**registrations** — Public student registrations (no login required).');
$body .= tbl(
    ['Column', 'Type', 'Description'],
    [
        ['seminar_id',    'FK → seminars.id CASCADE', 'Which seminar'],
        ['student_name',  'VARCHAR(100)',              'Student full name'],
        ['student_email', 'VARCHAR(100)',              'Used for duplicate detection per seminar'],
        ['student_phone', 'VARCHAR(25)',               'Optional phone number'],
        ['student_roll',  'VARCHAR(50)',               'University roll / student ID'],
        ['department',    'VARCHAR(120)',              'Department selected from dropdown'],
    ],
    [2300, 2500, 4226]
);
$body .= sp();
$body .= para('**attendance** — One record per (registration, seminar) pair. A UNIQUE KEY prevents duplicate marking.');
$body .= tbl(
    ['Column',         'Type',                     'Description'],
    [
        ['registration_id', 'FK → registrations.id CASCADE', 'Which registration'],
        ['seminar_id',      'FK → seminars.id CASCADE',       'Which seminar'],
        ['status',          "ENUM('present','absent')",        'Attendance outcome'],
        ['marked_by',       'FK → users.id SET NULL',         'Teacher who marked attendance'],
    ],
    [2300, 2900, 3826]
);
$body .= pb();

// 6. Modules
$body .= h1('6. System Modules & Features');
$body .= h2('6.1  Public Portal');
$body .= para('Accessible to all visitors without authentication.');
$body .= tbl(
    ['Page', 'Key Features'],
    [
        ['index.php',    'Home page: live stats (total seminars, upcoming, teachers, registrations), grid of upcoming seminars with seat availability badges.'],
        ['seminars.php', 'Full seminar listing with status badges and capacity indicators.'],
        ['seminar.php',  'Seminar detail: description, speaker, venue, date/time, assigned teachers, seats remaining.'],
        ['register.php', 'Student registration form: no login required, duplicate email check, department dropdown (15 options), CSRF-protected POST.'],
        ['success.php',  'Confirmation page displayed after successful registration.'],
    ],
    [2200, 6826]
);
$body .= sp();
$body .= para('**Capacity Guard:** getSeminarCapacityInfo() queries live registration count vs capacity. If full, the Register button is disabled in the UI and blocked again server-side in the POST handler, preventing race-condition over-booking.');
$body .= h2('6.2  Admin Panel');
$body .= para('Every page calls requireAdmin() at the top. Unauthorised users are immediately redirected to the login page.');
$body .= tbl(
    ['Page', 'Functionality'],
    [
        ['admin/index.php',             '4 stat cards + recent seminars table + recent registrations feed.'],
        ['admin/seminars.php',          'Manage all seminars with status badges, edit and delete actions.'],
        ['admin/seminar_form.php',      'Create / edit a seminar (title, date, time, venue, speaker, capacity, status).'],
        ['admin/seminar_assign.php',    'Assign one or more teachers to a seminar via checkboxes.'],
        ['admin/teachers.php',          'List all teacher accounts with department and contact info.'],
        ['admin/teacher_form.php',      'Add / edit teacher accounts; password stored as bcrypt hash.'],
        ['admin/registrations.php',     'View all student registrations, filterable by seminar.'],
        ['admin/attendance_report.php', 'Per-seminar attendance summary: present / absent / unmarked counts and percentages.'],
    ],
    [2800, 6226]
);
$body .= h2('6.3  Teacher Portal');
$body .= para('Every page calls requireTeacher(). All data queries are scoped to the logged-in teacher\'s ID — teachers cannot access other teachers\' seminar data.');
$body .= tbl(
    ['Page', 'Functionality'],
    [
        ['teacher/index.php',        'Dashboard: assigned seminar count, upcoming count, total registrations.'],
        ['teacher/seminars.php',     'List of seminars assigned to the logged-in teacher only.'],
        ['teacher/participants.php', 'Registered student list for a seminar with current attendance status.'],
        ['teacher/attendance.php',   'Click-to-toggle attendance marking via AJAX (no page reload).'],
        ['ajax/mark_attendance.php', 'AJAX endpoint: validates CSRF, updates attendance table, returns JSON.'],
    ],
    [2800, 6226]
);
$body .= pb();

// 7. Security
$body .= h1('7. Security Implementation');
$body .= para('Security is applied at multiple layers following OWASP guidelines.');
$body .= h2('7.1  SQL Injection Prevention — PDO Prepared Statements');
$body .= codeLines([
    '// Every query uses parameterised binding',
    '$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1");',
    '$stmt->execute([$email, $role]);',
    '',
    '// Direct interpolation is never used',
    '// $db->query("... WHERE email = \'$email\'");  <-- NOT present in codebase',
]);
$body .= h2('7.2  XSS Prevention — Output Encoding');
$body .= codeLines([
    'function e(string $s): string {',
    '    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");',
    '}',
    '// Every echo that prints user data uses e()',
    'echo e($student["name"]);  // <script> becomes &lt;script&gt;',
]);
$body .= h2('7.3  CSRF Protection');
$body .= codeLines([
    '// Hidden token in every form',
    '<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">',
    '',
    '// First line of every POST handler',
    'verifyCsrf();  // hash_equals() comparison — returns HTTP 403 on mismatch',
]);
$body .= h2('7.4  Password Security');
$body .= codeLines([
    '// Storage (during account creation)',
    'password_hash($plaintext, PASSWORD_DEFAULT)  // bcrypt, e.g. $2y$10$...',
    '',
    '// Verification (during login)',
    'password_verify($submitted, $stored_hash)    // timing-safe, no plain-text stored',
]);
$body .= h2('7.5  Session Security');
$body .= para('session_regenerate_id(true) is called immediately after successful login to prevent session fixation. Logout clears $_SESSION, expires the cookie, and calls session_destroy().');
$body .= h2('7.6  Role-Based Access Control');
$body .= para('requireAdmin() and requireTeacher() check $_SESSION[\'role\'] server-side on every protected page. There is no client-side trust — even a direct URL visit is blocked.');
$body .= sp();
$body .= tbl(
    ['Threat', 'Mitigation'],
    [
        ['SQL Injection',           'PDO prepared statements on every query'],
        ['XSS',                     'htmlspecialchars() via e() on all template output'],
        ['CSRF',                    'Synchroniser token in every POST form, validated server-side'],
        ['Broken Authentication',   'bcrypt hashing + session_regenerate_id() on login'],
        ['Privilege Escalation',    'Server-side role check at top of every protected page'],
        ['Session Fixation',        'session_regenerate_id(true) immediately after authentication'],
    ],
    [3500, 5526]
);
$body .= pb();

// 8. PHP Concepts
$body .= h1('8. Key PHP Concepts Demonstrated');
$body .= h2('8.1  PDO with Prepared Statements');
$body .= para('getDB() returns a PDO instance configured with ERRMODE_EXCEPTION, FETCH_ASSOC, and EMULATE_PREPARES = false (true server-side prepared statements). All CRUD operations use prepare() / execute().');
$body .= h2('8.2  PHP Session Management');
$body .= para('Sessions carry authenticated state (user_id, user_name, role) across requests. Flash messages (one-time success/error notices) are also stored in the session via setFlash() and consumed once by getFlash().');
$body .= h2('8.3  Singleton Pattern');
$body .= codeLines([
    'function getDB(): PDO {',
    '    static $pdo = null;      // survives across calls within the same request',
    '    if ($pdo === null) {',
    '        $pdo = new PDO(...); // instantiated only once per request',
    '    }',
    '    return $pdo;',
    '}',
]);
$body .= h2('8.4  AJAX with Fetch API + JSON Responses');
$body .= para('Attendance toggling sends a fetch() POST to ajax/mark_attendance.php. PHP sets Content-Type: application/json and echoes json_encode([\'success\' => true]). JavaScript updates the badge colour in the DOM without a page reload.');
$body .= h2('8.5  Dual-Layer Form Validation');
$body .= para('jQuery Validate provides instant client-side feedback. PHP re-validates everything server-side before any database write — client-side validation is UX only and can be bypassed, so server-side is authoritative.');
$body .= h2('8.6  Include Architecture');
$body .= para('Every page uses require_once for header/footer files. A $pageTitle variable is set before the include so each page gets its own browser tab title without duplicating the <head> block.');
$body .= h2('8.7  PHP 8 Arrow Functions');
$body .= codeLines([
    '// Teacher dashboard filters assigned seminars in memory',
    '$upcoming = array_filter($mySeminars, fn($s) => $s["status"] === "upcoming");',
]);
$body .= h2('8.8  Environment-Aware Configuration');
$body .= codeLines([
    '// config.php reads env vars first, falls back to dev defaults',
    'define("DB_HOST", getenv("DB_HOST") ?: "localhost");',
    'define("DB_PASS", getenv("DB_PASS") ?: "");',
    '// No code change needed between development and production',
]);
$body .= pb();

// 9. File Structure
$body .= h1('9. Project File Structure');
$body .= codeLines([
    'seminar-management-system/',
    '│',
    '├── index.php                  ← Public home page',
    '├── seminars.php               ← Public seminar listing',
    '├── seminar.php                ← Seminar detail page',
    '├── register.php               ← Student registration form',
    '├── success.php                ← Registration confirmation',
    '├── setup.php                  ← One-time DB setup script',
    '│',
    '├── admin/                     ← Admin panel (requireAdmin on every page)',
    '│   ├── login.php / logout.php',
    '│   ├── index.php              ← Dashboard',
    '│   ├── seminars.php / seminar_form.php / seminar_delete.php',
    '│   ├── seminar_assign.php     ← Assign teachers to seminar',
    '│   ├── teachers.php / teacher_form.php / teacher_delete.php',
    '│   ├── registrations.php      ← All student registrations',
    '│   └── attendance_report.php  ← Attendance summary per seminar',
    '│',
    '├── teacher/                   ← Teacher portal (requireTeacher on every page)',
    '│   ├── login.php / logout.php',
    '│   ├── index.php / seminars.php / participants.php',
    '│   └── attendance.php         ← AJAX attendance marking UI',
    '│',
    '├── ajax/',
    '│   └── mark_attendance.php    ← JSON endpoint for attendance toggle',
    '│',
    '├── includes/',
    '│   ├── config.php             ← Constants (DB, SITE_NAME, BASE_URL)',
    '│   ├── db.php                 ← getDB() singleton',
    '│   ├── functions.php          ← All query functions and helpers',
    '│   ├── auth.php               ← Session, login, logout, role guards',
    '│   ├── public_header.php / public_footer.php',
    '│   ├── admin_header.php / admin_footer.php',
    '│   └── teacher_header.php / teacher_footer.php',
    '│',
    '├── assets/',
    '│   ├── css/style.css          ← Global stylesheet (all three portals)',
    '│   └── js/main.js             ← Sidebar toggle + AJAX attendance logic',
    '│',
    '└── database/',
    '    └── schema.sql             ← CREATE TABLE statements + seed data',
]);
$body .= pb();

// 10. Setup
$body .= h1('10. Setup & Deployment');
$body .= tbl(
    ['Step', 'Action'],
    [
        ['1', 'Copy project to C:\\laragon\\www\\seminar-management-system\\'],
        ['2', 'Start Laragon — ensure Apache and MySQL are running'],
        ['3', 'Open: http://localhost/seminar-management-system/setup.php'],
        ['4', 'Setup script creates database, tables, and seeds default accounts automatically'],
        ['5', 'Visit http://localhost/seminar-management-system/ — system is live'],
    ],
    [600, 8426]
);
$body .= sp();
$body .= tbl(
    ['Portal', 'URL', 'Default Credentials'],
    [
        ['Public Site',    '/seminar-management-system/',              'No login required'],
        ['Admin Panel',    '/seminar-management-system/admin/',        'admin@university.edu / Admin@123'],
        ['Teacher Portal', '/seminar-management-system/teacher/',      'teacher@university.edu / Teacher@123'],
    ],
    [2000, 3600, 3426]
);

// 11. Challenges
$body .= h1('11. Challenges & Solutions');
$body .= tbl(
    ['Challenge', 'Solution Applied'],
    [
        ['**Capacity race condition** — two simultaneous submissions could both pass the seats-available check.',
         'Capacity is re-checked server-side inside the POST handler after CSRF validation. MySQL row-level locking on the registrations table prevents double-booking at the database level.'],
        ['**Duplicate registrations** — same student registering for the same seminar twice.',
         'isAlreadyRegistered() queries for an existing (seminar_id, student_email) pair before any INSERT. A clear error message is shown if a duplicate is found.'],
        ['**CSRF tokens in AJAX** — fetch() requests have no HTML form to carry the token.',
         'The CSRF token is embedded in a <meta name="csrf-token"> tag. JavaScript reads it with document.querySelector() and attaches it to every fetch() POST body.'],
        ['**Teacher data isolation** — a logged-in teacher must not see other teachers\' seminars.',
         'All teacher queries JOIN seminar_teachers ON teacher_id = ? using the session user_id. Scoping is enforced at SQL level, not just in the UI.'],
        ['**Three-portal styling** — admin, teacher, and public portals need distinct but consistent styles.',
         'A single style.css uses scoped body classes (.admin-body, .teacher-body) and sidebar classes (.sidebar-admin, .sidebar-teacher) to differentiate themes without duplicating base rules.'],
        ['**Environment portability** — hard-coded credentials break when moving from Laragon to a live server.',
         'config.php reads all settings from getenv() first, falling back to dev defaults. Deploying to production only requires setting server environment variables.'],
    ],
    [3600, 5426]
);
$body .= pb();

// 12. Conclusion
$body .= h1('12. Conclusion');
$body .= para('**Seminar Track** successfully demonstrates full-stack web development using Core PHP and MySQL, covering the complete development cycle from database schema design through to a deployable, tested web application.');
$body .= sp();
$body .= para('Key learning outcomes achieved:');
$body .= bul('Writing secure parameterised SQL using PDO to prevent injection attacks');
$body .= bul('Implementing role-based access control entirely in server-side PHP without a framework');
$body .= bul('Designing a normalised relational database with foreign key integrity constraints');
$body .= bul('Building asynchronous UIs with the Fetch API and JSON-returning PHP endpoints');
$body .= bul('Applying OWASP practices: CSRF tokens, XSS encoding, bcrypt password storage, session hardening');
$body .= bul('Organising a multi-file PHP project with reusable includes, a singleton DB layer, and helper libraries');
$body .= sp();
$body .= para('The system runs on Laragon locally and is ready for demonstration. Future enhancements could include email confirmation on registration, PDF attendance certificates, and Excel export of reports.');
$body .= sp() . sp();

// Declaration box
$body .= '<w:tbl><w:tblPr>'
       . '<w:tblW w:w="9026" w:type="dxa"/>'
       . '<w:tblBorders>'
       . '<w:top    w:val="single" w:sz="8" w:color="E67E22"/>'
       . '<w:left   w:val="single" w:sz="8" w:color="E67E22"/>'
       . '<w:bottom w:val="single" w:sz="8" w:color="E67E22"/>'
       . '<w:right  w:val="single" w:sz="8" w:color="E67E22"/>'
       . '</w:tblBorders>'
       . '<w:tblCellMar>'
       . '<w:top w:w="140" w:type="dxa"/><w:left w:w="200" w:type="dxa"/>'
       . '<w:bottom w:w="140" w:type="dxa"/><w:right w:w="200" w:type="dxa"/>'
       . '</w:tblCellMar>'
       . '</w:tblPr>'
       . '<w:tblGrid><w:gridCol w:w="9026"/></w:tblGrid>'
       . '<w:tr><w:tc><w:tcPr><w:tcW w:w="9026" w:type="dxa"/>'
       . '<w:shd w:val="clear" w:color="auto" w:fill="FFF8F0"/></w:tcPr>'
       . '<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Declaration</w:t></w:r></w:p>'
       . '<w:p><w:r><w:t xml:space="preserve">I confirm that this project and report are my own original work, submitted in fulfilment of the requirements for the Web Programming Languages (WPL) course.</w:t></w:r></w:p>'
       . '<w:p/>'
       . '<w:p>'
       . '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">Student: </w:t></w:r>'
       . '<w:r><w:t xml:space="preserve">Asheer Saeed                    </w:t></w:r>'
       . '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">Date: </w:t></w:r>'
       . '<w:r><w:t>June 2, 2026</w:t></w:r>'
       . '</w:p>'
       . '</w:tc></w:tr></w:tbl>';

// ── OOXML file contents ───────────────────────────────────────────────────────

$WML = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/word/document.xml"  ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml"    ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/word/settings.xml"  ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>
  <Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>
  <Override PartName="/docProps/core.xml"  ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml"   ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>';

$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';

$docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"    Target="styles.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings"  Target="settings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>
</Relationships>';

$settings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:defaultTabStop w:val="720"/>
  <w:compat>
    <w:compatSetting w:name="compatibilityMode"
      w:uri="http://schemas.microsoft.com/office/word" w:val="15"/>
  </w:compat>
</w:settings>';

$coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties
  xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Seminar Track — WPL Project Report</dc:title>
  <dc:creator>Asheer Saeed</dc:creator>
  <dc:subject>Web Programming Languages Course Project</dc:subject>
  <dcterms:created xsi:type="dcterms:W3CDTF">' . date('Y-m-d') . 'T00:00:00Z</dcterms:created>
</cp:coreProperties>';

$appXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
  <Application>Seminar Track Report Generator</Application>
</Properties>';

$numbering = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:abstractNum w:abstractNumId="0">
    <w:multiLevelType w:val="hybridMultilevel"/>
    <w:lvl w:ilvl="0">
      <w:start w:val="1"/><w:numFmt w:val="bullet"/>
      <w:lvlText w:val="&#x2022;"/>
      <w:lvlJc w:val="left"/>
      <w:pPr><w:ind w:left="360" w:hanging="360"/></w:pPr>
    </w:lvl>
  </w:abstractNum>
  <w:abstractNum w:abstractNumId="1">
    <w:multiLevelType w:val="hybridMultilevel"/>
    <w:lvl w:ilvl="0">
      <w:start w:val="1"/><w:numFmt w:val="decimal"/>
      <w:lvlText w:val="%1."/>
      <w:lvlJc w:val="left"/>
      <w:pPr><w:ind w:left="360" w:hanging="360"/></w:pPr>
    </w:lvl>
  </w:abstractNum>
  <w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>
  <w:num w:numId="2"><w:abstractNumId w:val="1"/></w:num>
</w:numbering>';

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault><w:rPr>
      <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
      <w:sz w:val="22"/><w:szCs w:val="22"/>
      <w:lang w:val="en-US"/>
    </w:rPr></w:rPrDefault>
    <w:pPrDefault><w:pPr>
      <w:spacing w:after="140" w:line="276" w:lineRule="auto"/>
    </w:pPr></w:pPrDefault>
  </w:docDefaults>

  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:pPr><w:spacing w:after="140" w:line="276" w:lineRule="auto"/></w:pPr>
    <w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr>
  </w:style>

  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/><w:next w:val="Normal"/>
    <w:pPr>
      <w:keepNext/><w:keepLines/>
      <w:spacing w:before="480" w:after="160"/>
      <w:pBdr><w:bottom w:val="single" w:sz="6" w:color="E67E22" w:space="6"/></w:pBdr>
    </w:pPr>
    <w:rPr>
      <w:b/><w:bCs/>
      <w:color w:val="1A3C6E"/>
      <w:sz w:val="32"/><w:szCs w:val="32"/>
    </w:rPr>
  </w:style>

  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:basedOn w:val="Normal"/><w:next w:val="Normal"/>
    <w:pPr><w:keepNext/><w:spacing w:before="300" w:after="100"/></w:pPr>
    <w:rPr>
      <w:b/><w:bCs/>
      <w:color w:val="1A3C6E"/>
      <w:sz w:val="26"/><w:szCs w:val="26"/>
    </w:rPr>
  </w:style>

  <w:style w:type="paragraph" w:styleId="Heading3">
    <w:name w:val="heading 3"/>
    <w:basedOn w:val="Normal"/><w:next w:val="Normal"/>
    <w:pPr><w:spacing w:before="220" w:after="80"/></w:pPr>
    <w:rPr>
      <w:b/><w:bCs/>
      <w:color w:val="2D5FA6"/>
      <w:sz w:val="24"/><w:szCs w:val="24"/>
    </w:rPr>
  </w:style>

  <w:style w:type="paragraph" w:styleId="ListBullet">
    <w:name w:val="List Bullet"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr>
      <w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr>
      <w:spacing w:after="60"/>
    </w:pPr>
  </w:style>

  <w:style w:type="paragraph" w:styleId="ListNumber">
    <w:name w:val="List Number"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr>
      <w:numPr><w:ilvl w:val="0"/><w:numId w:val="2"/></w:numPr>
      <w:spacing w:after="60"/>
    </w:pPr>
  </w:style>

  <w:style w:type="paragraph" w:styleId="CodeBlock">
    <w:name w:val="Code Block"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr>
      <w:spacing w:before="0" w:after="0" w:line="240" w:lineRule="auto"/>
      <w:shd w:val="clear" w:color="auto" w:fill="F0F4F8"/>
      <w:ind w:left="200" w:right="200"/>
    </w:pPr>
    <w:rPr>
      <w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>
      <w:sz w:val="18"/><w:szCs w:val="18"/>
      <w:color w:val="1A3C6E"/>
    </w:rPr>
  </w:style>

  <w:style w:type="table" w:styleId="TableGrid">
    <w:name w:val="Table Grid"/>
    <w:tblPr>
      <w:tblBorders>
        <w:top    w:val="single" w:sz="4" w:color="auto"/>
        <w:left   w:val="single" w:sz="4" w:color="auto"/>
        <w:bottom w:val="single" w:sz="4" w:color="auto"/>
        <w:right  w:val="single" w:sz="4" w:color="auto"/>
        <w:insideH w:val="single" w:sz="4" w:color="auto"/>
        <w:insideV w:val="single" w:sz="4" w:color="auto"/>
      </w:tblBorders>
    </w:tblPr>
    <w:tcPr><w:tcMar>
      <w:top    w:w="80"  w:type="dxa"/>
      <w:left   w:w="108" w:type="dxa"/>
      <w:bottom w:w="80"  w:type="dxa"/>
      <w:right  w:w="108" w:type="dxa"/>
    </w:tcMar></w:tcPr>
  </w:style>
</w:styles>';

$docXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>
BODY_PLACEHOLDER
<w:sectPr>
  <w:pgSz w:w="11906" w:h="16838"/>
  <w:pgMar w:top="1440" w:right="1300" w:bottom="1440" w:left="1800"/>
</w:sectPr>
</w:body>
</w:document>';

$docXml = str_replace('BODY_PLACEHOLDER', $body, $docXml);

// ── Build .docx ZIP ───────────────────────────────────────────────────────────

$tmp = tempnam(sys_get_temp_dir(), 'rpt_') . '.zip';

$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    die('Failed to create temporary .docx file.');
}

$zip->addFromString('[Content_Types].xml',           $contentTypes);
$zip->addFromString('_rels/.rels',                   $rels);
$zip->addFromString('word/document.xml',             $docXml);
$zip->addFromString('word/_rels/document.xml.rels',  $docRels);
$zip->addFromString('word/styles.xml',               $styles);
$zip->addFromString('word/settings.xml',             $settings);
$zip->addFromString('word/numbering.xml',            $numbering);
$zip->addFromString('docProps/core.xml',             $coreXml);
$zip->addFromString('docProps/app.xml',              $appXml);
$zip->close();

// ── Stream download ───────────────────────────────────────────────────────────

$filename = 'Seminar_Track_WPL_Report.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmp));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($tmp);
@unlink($tmp);
exit;
