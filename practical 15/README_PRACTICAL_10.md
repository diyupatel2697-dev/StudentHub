# Practical 10: Secure Login/Logout with Sessions, Role-Based Access Control (RBAC), Session Timeout, and Remember-Me Token

## 1. Practical Summary
- **Subject**: Web Development Frameworks (ITUE203)
- **Semester**: 3rd, 2026-27 (ODD)
- **Course Outcomes**: CO1, CO5
- **Technology Stack**: PHP, MySQLi, Sessions, Cookies, HTML5, CSS3 (StudentHub Green Theme), XAMPP

---

## 2. Problem Definition
Develop secure login and logout functionality using PHP sessions. Verify passwords using `password_verify()`, regenerate session IDs after login (session fixation prevention), restrict unauthorized dashboard access, implement role-based redirection (Admin vs Student), enforce session inactivity timeout, and implement secure token-based remember-me functionality.

---

## 3. Key Questions Answered

### Q1: Are sessions started and destroyed securely?
- **Started Securely**: Cookie parameters are hardened with `httponly = true` (stops XSS theft) and `samesite = Lax` (CSRF defense).
- **Session Fixation Prevention**: `session_regenerate_id(true)` is immediately executed upon valid credentials so an attacker cannot hijack a pre-set session ID.
- **Destroyed Securely**: [`logout.php`](logout.php) unsets `$_SESSION`, expires the session cookie with `setcookie(session_name(), '', time() - 42000, ...)`, deletes database remember-me tokens, and executes `session_destroy()`.

### Q2: Is password_verify() used correctly?
- Passwords entered by the user are validated against database hashes using PHP’s `password_verify($password, $user['password'])`. Plaintext passwords are never compared directly.

### Q3: Is unauthorized dashboard access blocked?
- Protected pages call `requireLogin()`. If `$_SESSION['logged_in']` is not set or false, users are blocked and redirected to `login.html?error=unauthorized` with an informative error banner.

### Q4: Is session timeout implemented?
- An inactivity timeout threshold of **15 minutes (900 seconds)** is enforced.
- On each user request, if `time() - $_SESSION['last_activity'] > 900`, the session is terminated and user is redirected to `login.html?error=timeout`.
- An interactive simulation button (`?simulate_timeout=1`) is provided on dashboards to demonstrate timeout live during evaluation.

### Q5: Are Admin and Student users redirected to appropriate dashboards?
- Role-Based Access Control (RBAC) inspects `$_SESSION['role']`:
  - **Admin** (`role = 'Admin'`) &rarr; redirected to `admin_dashboard.php`
  - **Student** (`role = 'Student'`) &rarr; redirected to `dashboard.php`
- If a Student attempts to navigate directly to `admin_dashboard.php`, they are blocked with a styled **403 Access Denied** page.

---

## 4. Key Files Created & Updated

| File | Purpose |
| :--- | :--- |
| [`auth.php`](auth.php) | Centralized session manager: cookie hardening, inactivity timeout, RBAC guards (`requireLogin`, `requireRole`), and remember-me auto-login. |
| [`login.php`](login.php) | Authentication processor: `password_verify()`, `session_regenerate_id(true)`, last-login update, remember-me token generation, and role redirection. |
| [`logout.php`](logout.php) | Secure session destroyer: unsets session, clears session cookie, removes remember-me database token. |
| [`login.html`](login.html) | Secure login interface with StudentHub Green Theme, alert banners, remember-me checkbox, and seed account credentials guide. |
| [`dashboard.php`](dashboard.php) | Student dashboard guarded by `requireLogin()`, displaying student details, last-login timestamp, and logout button. |
| [`admin_dashboard.php`](admin_dashboard.php) | Administrator dashboard guarded by `requireRole('Admin')`, displaying system stats, user list, and RBAC badges. |
| [`setup_db.php`](setup_db.php) | Database migration script: adds `role` and `last_login` columns, creates `remember_tokens` table, and seeds demo accounts. |
| [`test_practical10.php`](test_practical10.php) | Automated browser test suite running 7 verification tests in one click. |

---

## 5. Seed Accounts for Testing & Evaluation

| Account Type | Email / Username | Password | Assigned Role | Destination Dashboard |
| :--- | :--- | :--- | :--- | :--- |
| **Administrator** | `admin@studenthub.edu` (or `ADM001`) | `AdminPass123@` | **Admin** | [`admin_dashboard.php`](admin_dashboard.php) |
| **Student** | `john.doe@studenthub.edu` (or `STU001`) | `StudentPass123@` | **Student** | [`dashboard.php`](dashboard.php) |

---

## 6. Test Cases & Verification Report

| Test ID | Test Case Description | Input Data | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC01** | Valid Admin Login | `admin@studenthub.edu` / `AdminPass123@` | Session regenerated, redirected to `admin_dashboard.php` | **Pass** |
| **TC02** | Valid Student Login | `john.doe@studenthub.edu` / `StudentPass123@` | Session regenerated, redirected to `dashboard.php` | **Pass** |
| **TC03** | Invalid Password Attempt | `admin@studenthub.edu` / `WrongPass123@` | Blocked by `password_verify()`, redirected to `login.html?error=1` | **Pass** |
| **TC04** | Unauthorized Dashboard Access | Direct URL visit to `dashboard.php` without session | Blocked by `requireLogin()`, redirected to `login.html?error=unauthorized` | **Pass** |
| **TC05** | RBAC Privilege Escalation Block | Student logged in, visits `admin_dashboard.php` | Blocked by `requireRole('Admin')`, displays 403 Forbidden page | **Pass** |
| **TC06** | Session Inactivity Timeout | Click "Simulate Inactivity Timeout" button | Session destroyed, redirected to `login.html?error=timeout` | **Pass** |
| **TC07** | Secure Logout | Click "Logout" button on dashboard | Session and remember-me tokens destroyed, redirected to login | **Pass** |
| **TC08** | Remember-Me 30-Day Auto-Login | Check "Remember me" on login, close & reopen browser | Cookie selector & validator validated against database hash | **Pass** |
| **TC09** | Last-Login Timestamp Audit | Successful login | Column `last_login` in `students` updated to current timestamp | **Pass** |

---

## 7. How to Validate & Check Practical 10

1. **Automated Test Suite**:
   Open in your browser:
   `http://localhost/updated%20HTML%20with%20CSS/practical%2010/test_practical10.php`
   Verifies all 7 backend security checks with green badges.

2. **Admin Role-Based Redirection Test**:
   - Visit `http://localhost/updated%20HTML%20with%20CSS/practical%2010/login.html`
   - Login with: `admin@studenthub.edu` / `AdminPass123@`
   - Notice redirection to `admin_dashboard.php` showing user management and system counts.

3. **Student Role-Based Redirection Test**:
   - Logout, then login with: `john.doe@studenthub.edu` / `StudentPass123@`
   - Notice redirection to `dashboard.php` showing student academic information.

4. **Unauthorized Access Test**:
   - In a private/incognito window, visit `http://localhost/updated%20HTML%20with%20CSS/practical%2010/dashboard.php`
   - Notice immediate block with red alert: *"Unauthorized access: Please log in to view this page."*

5. **Timeout Test**:
   - While logged in on `dashboard.php`, click **"Simulate Inactivity Timeout"**.
   - Notice immediate redirection to `login.html` with message: *"Your session has expired due to inactivity."*
