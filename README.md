# Formula 1 Data Platform & Support Portal

## Project Overview

This repository contains the complete coursework deliverable for an Formula 1 themed database system. It combines a MySQL relational schema, stored procedures, and triggers with a PHP-based web interface and a MongoDB-backed support ticket workflow. The project demonstrates how transactional SQL workloads and NoSQL document storage can coexist inside a single application.

## Features

- **Relational Schema**: `project_db.sql` defines richly linked tables for teams, drivers, cars, races, penalties, sponsors, and supporting relationships inside the `F1_db` MySQL database.
- **Seed Data**: `table_insertion.sql` loads curated historical records so the UI can surface meaningful results immediately.
- **Stored Procedure Library**: Three reusable procedures (career summary, team victories, standings) live under `scripts/stored_procedures/` and drive the PHP demos.
- **Trigger Automation**: `scripts/triggers/trigger_{1..3}` keep team, driver, and car statistics in sync after race inserts; before/after screenshots document the outcome.
- **Interactive Web UI**: Pages in `f1_project/user/` let you execute procedures, fire triggers, and explore the data. Admin tooling under `f1_project/admin/` manages MongoDB support tickets.
- **Support Desk Integration**: PHP endpoints create and triage tickets inside the `support_system.tickets` MongoDB collection, illustrating multi-database workflows.
- **Documentation**: `ER_model.pdf` and `CS306_GROUP_46_HW2_REPORT.pdf` capture the conceptual model, while `CS306_GROUP_46_HW3_SQLDUMP.sql` bundles the full schema and sample content.

## Repository Structure

```text
post2kf1_db/
├── admin/                         # Legacy admin ticket interface powered by MongoDB
├── f1_project/
│   ├── admin/                     # Web admin pages (view/resolve tickets)
│   └── user/                      # Public pages for trigger/procedure demos and ticket submission
├── scripts/
│   ├── stored_procedures/         # Individual procedure definitions (SQL)
│   └── triggers/                  # Trigger SQL plus before/after evidence
├── project_db.sql                 # Core MySQL schema for F1_db
├── table_insertion.sql            # Sample data for the schema
├── CS306_GROUP_46_HW3_SQLDUMP.sql # Combined dump (schema + data + routines)
├── config.php                     # PHP configuration for MySQL and MongoDB endpoints
├── db_connect.php                 # Shared connection helpers (mysqli + MongoDB\Driver)
├── ER_model.pdf                   # Entity-relationship diagram
├── CS306_GROUP_46_HW2_REPORT.pdf  # Project narrative and analysis
└── README.md
```

## Prerequisites

- PHP 8.x with the `mysqli` extension enabled
- PHP MongoDB driver (`pecl install mongodb`) with `extension=mongodb` configured
- MySQL 8.x (or MariaDB equivalent) with access to create databases, triggers, and routines
- MongoDB Community Server 6.x (or compatible Atlas cluster) for the support ticket store

## Getting Started

1. **Clone the Repository**
   ```bash
   git clone https://github.com/YagizEbil/post2kf1_db.git
   cd post2kf1_db
   ```

2. **Provision the MySQL Schema**
   ```bash
   # Create schema and tables
   mysql -u root -p < project_db.sql
   # Populate reference data
   mysql -u root -p F1_db < table_insertion.sql
   # Register stored procedures
   mysql -u root -p F1_db < scripts/stored_procedures/stored_procedure_1.sql
   mysql -u root -p F1_db < scripts/stored_procedures/stored_procedure_2.sql
   mysql -u root -p F1_db < scripts/stored_procedures/stored_procedure_3.sql
   # Install triggers
   mysql -u root -p F1_db < scripts/triggers/trigger_1/trigger_1.sql
   mysql -u root -p F1_db < scripts/triggers/trigger_2/trigger_2.sql
   mysql -u root -p F1_db < scripts/triggers/trigger_3/trigger_3.sql
   ```
   > Tip: `CS306_GROUP_46_HW3_SQLDUMP.sql` loads everything (schema, data, routines, triggers) in a single pass if you prefer a one-file setup.

3. **Prepare MongoDB**
   - Ensure a MongoDB instance is running (default connection: `mongodb://localhost:27017`).
   - Create the `support_system` database (optional; collections are created automatically when tickets are inserted).

4. **Configure PHP Connectivity**
   - Update `config.php` if your MySQL username/password, hostnames, or MongoDB URI differ from the defaults.
   - Confirm the PHP MongoDB extension is enabled (`php -m | grep mongodb`).

5. **Run the Local Web Server**
   ```bash
   php -S localhost:8000 -t f1_project
   ```
   Open `http://localhost:8000/user/index.php` for the public demo pages or `http://localhost:8000/admin/admin_view_tickets.php` for the ticket dashboard.

## Using the Application

- **Trigger Demonstrations**  
  Insert race data via `trigger_update_team_stats.php`, `trigger_update_driver_stats.php`, and `trigger_update_car_stats.php` to watch the triggers adjust aggregate statistics. Before/after snapshots live alongside each trigger script.

- **Stored Procedure Explorers**  
  Forms such as `sp_get_driver_summary.php`, `sp_get_team_races.php`, and `sp_get_team_standings.php` collect parameters, execute the respective stored procedures, and render tabular results.

- **Support Ticket Workflow**  
  Use `submit_ticket_form.php` to open a ticket (persisted in MongoDB), `user_ticket_list.php` to review open cases, and the admin console to append comments or close tickets.

- **Legacy Prototypes**  
  The root-level `admin/` and PHP files (`ticket_list.php`, `ticket_create.php`, etc.) provide alternative interfaces that were retained for reference during iteration.

## SQL and Documentation Assets

- **Diagrams & Reports**: Review `ER_model.pdf` and `CS306_GROUP_46_HW2_REPORT.pdf` for the conceptual design narrative.
- **Automation Evidence**: Each trigger directory stores the SQL definition plus `before_*.png` and `after_*.png` screenshots that verify the automated updates.
- **Database Dump**: `CS306_GROUP_46_HW3_SQLDUMP.sql` is ideal for quickly recreating the full environment during grading or demos.

## Authors

- [Zilan Turunç](https://github.com/zilan-turunc)
- [Ardıl Yüce](https://github.com/ardilyce)
- [Kadir Yagiz Ebil](https://github.com/YagizEbil)

## License

This project is provided for academic coursework; no explicit open-source license has been applied.
