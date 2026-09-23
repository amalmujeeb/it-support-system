# SupportHub — IT Support Management System

A premium PHP + MySQL IT support ticketing system with role-based workspaces for **Administrator, IT Support Staff and Client**.

## Premium UI Release

This release applies the same professional SaaS design system across the full authenticated application:

- Premium dark sidebar with active states and collapse/expand control
- Mobile off-canvas sidebar with overlay
- Global search bar and keyboard shortcut (`Ctrl/Cmd + K`)
- Notification center and unread badges
- Profile context in the top navigation
- Premium KPI/stat cards
- Responsive tables, filters and action controls
- Admin analytics dashboard
- Ticket assignment and filtering workspace
- User management workspace
- Client dashboard, ticket creation wizard and ticket center
- Staff dashboard and assigned-ticket work queue
- Premium ticket detail page with status progress and timeline
- Premium notification center
- Login and registration remain supported with the existing authentication workflow
- Responsive mobile layouts for navigation, filters, ticket forms and data tables
- Protected form actions with validation, confirmation feedback and CSRF tokens

## Technology

- PHP 8+
- MySQL / MariaDB
- HTML5 / CSS3
- Vanilla JavaScript
- XAMPP / Apache

## Run locally

1. Copy the project to:
   `C:\xampp\htdocs\it-support-system`
2. Start **Apache** and **MySQL** in XAMPP.
3. Keep the existing database/data if the system is already configured.
4. Open:
   `http://localhost/it-support-system/login.php`

## Demo accounts

- Admin: `admin@supporthub.test` / `admin123`
- Staff: `staff@supporthub.test` / `staff123`
- Client: `client@supporthub.test` / `client123`

## Account registration

Clients can create their own account from the registration page. To register IT support staff, sign in as an administrator and open **Users** → **Create User Account**, then select **IT Support Staff**. This keeps staff access under administrator control.



Do **not** run `setup.php` again if the database is already working. The premium UI release changes presentation and navigation only; the existing ticket, assignment, status, comments and notification workflow is preserved.
