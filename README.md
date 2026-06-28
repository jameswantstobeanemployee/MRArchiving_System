# MRA System — Medical Records Archiving System
A comprehensive Medical Records Archiving (MRA) System built with Laravel, designed to digitally manage, track, and archive patient charts in a hospital setting.

<img width="1919" height="944" alt="Screenshot 2026-06-28 223340" src="https://github.com/user-attachments/assets/2afc38de-fa65-42ab-bc88-956dca1ef895" />

## Features

### Chart Archive
- Archive and manage patient medical records (charts)
- Search by patient name, MR#, or case number
- Track chart status: Archived, Checked Out, Returned
- Retention date tracking and expiry monitoring
- Orphaned chart detection and reassignment
- Export to CSV

<img width="1906" height="942" alt="Screenshot 2026-06-28 221903" src="https://github.com/user-attachments/assets/fe365612-ebac-4374-837e-fadb96d156f4" />

##3 Patient Management
- Register and manage patient records
- Search by name or MR Number
- View all charts linked to a patient

<img width="1915" height="944" alt="Screenshot 2026-06-28 220216" src="https://github.com/user-attachments/assets/74791111-07b0-49c5-8ce2-3c28bc999486" />

### Checkout Management
- Check out and return charts to departments
- Track overdue charts with alerts
- View checkout history per chart
- Export checkout data to CSV

<img width="1919" height="944" alt="Screenshot 2026-06-28 220348" src="https://github.com/user-attachments/assets/4272a411-ec1d-42c2-967c-f8507ad88263" />

### Storage Locations
- Manage archive rooms, shelves, and boxes
- Physical location tracking (Room → Shelf → Box)
- Capacity monitoring with warning thresholds
- Detect charts with unassigned locations

<img width="1919" height="944" alt="Screenshot 2026-06-28 221127" src="https://github.com/user-attachments/assets/a8954f6c-cf8d-40fc-b891-4301377268ee" />

### Backup Management
- Schedule automated backups (DB + Files)
- Monitor backup history and status
- Configure backup destinations
- Failed backup error logging

<img width="1919" height="940" alt="Screenshot 2026-06-28 221331" src="https://github.com/user-attachments/assets/e3b38bac-234d-4dc3-a15a-3c8686561b5d" />

### Drive File Scanner
- Scan physical files on external drives
- Cross-reference files with the database
- Full Scan and Search Files per drive
- Detect orphaned or missing files

<img width="1919" height="939" alt="Screenshot 2026-06-28 221356" src="https://github.com/user-attachments/assets/bb68a9d5-f918-4b08-925f-3b3dc82eb40c" />

### Reports & Analytics
- Archive Inventory Report
- Box Status & Storage Usage
- Retention Report (expiring/expired charts)
- Checkout Status Report
- Location History & Chart Movement Log
- Activity Report by User
- Audit Trail (all system changes)

<img width="1919" height="936" alt="Screenshot 2026-06-28 221155" src="https://github.com/user-attachments/assets/0f690ed6-0ab3-4ea0-9117-2140cff96b17" />

### User Management
- Role-based access control (Admin / Staff)
- User activity tracking
- Charts archived per user
- Last login monitoring

<img width="1919" height="944" alt="Screenshot 2026-06-28 221224" src="https://github.com/user-attachments/assets/e1ae6281-2c7e-4ecf-999a-32eead4c8f5c" />

### System Settings
- Configure storage thresholds and capacities
- Set allowed file types and max upload sizes
- Notification and security settings
- Checkout and retention policies

<img width="1917" height="946" alt="Screenshot 2026-06-28 221420" src="https://github.com/user-attachments/assets/695ae1f4-a6be-463e-9e95-589aef71ad5b" />

### Notifications
- Real-time notifications for chart checkouts and returns
- Storage critical alerts
- Mark all as read

<img width="1904" height="944" alt="Screenshot 2026-06-28 221545" src="https://github.com/user-attachments/assets/1a4ae720-68cc-4e28-92a1-ff42e1abb49d" />

## Tech Stack

| Layer | Technology |
|---|---|
| Backend  | Laravel 10+ (PHP 8.2+) |
| Frontend  | Blade Templates |
| Database  | MySQL |
| Auth  | Laravel Sanctum  |
| Queue  | Laravel Queue / Jobs  |
| Storage  | Local File System / External Drives  |

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Admin  | admin@hospital.com  | password  |
| Staff  | staff@hospital.com  | password  |

## License
- This project is licensed under the MIT License.

##  Developer
- Developed by **James** as part of his On-the-Job Training (OJT) in a hospital setting.
