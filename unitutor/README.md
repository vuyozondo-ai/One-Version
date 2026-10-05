# UniTutor - University Peer Tutoring Management System

A complete peer tutoring management system for universities, built with HTML5, CSS3, and vanilla JavaScript using localStorage for data persistence.

## 📋 Table of Contents

- [System Overview](#system-overview)
- [Features](#features)
- [Technologies](#technologies)
- [Database Design](#database-design)
- [Installation Instructions](#installation-instructions)
- [Running the System](#running-the-system)
- [Test Accounts](#test-accounts)
- [Demonstration Flow](#demonstration-flow)
- [CRUD Operations](#crud-operations)
- [Reports](#reports)
- [Project Structure](#project-structure)
- [Security Features](#security-features)
- [Troubleshooting](#troubleshooting)

## 🎯 System Overview

UniTutor is a comprehensive peer tutoring management system designed for universities. The system allows students to find and book peer tutors for various modules, while tutors can manage their availability and sessions. Administrators have full control over the system including user management, database operations, and report generation.

### Key Components

- **Student Portal**: Find tutors, book sessions, view bookings, give feedback
- **Tutor Portal**: Manage availability, confirm bookings, view sessions, track ratings
- **Admin Portal**: Full system management, CRUD operations, reports, database management

## ✨ Features

### For Students
- Search for tutors by module, name, or department
- View tutor profiles with ratings and availability
- Book tutoring sessions
- View booking history and status
- Give feedback on completed sessions
- Manage profile information

### For Tutors
- View assigned modules
- Create and manage available time slots
- Review and confirm/reject booking requests
- View session schedule
- Track ratings and feedback
- Manage profile information

### For Administrators
- Complete CRUD operations for all entities
- Manage students, tutors, departments, programs, modules
- Manage bookings, sessions, payments, applications
- View and manage feedback
- Generate 4 comprehensive reports
- Database table management interface
- System statistics dashboard

## 🛠 Technologies

- **Frontend**: HTML5
- **Styling**: CSS3
- **Scripting**: Vanilla JavaScript (ES6+)
- **Data Persistence**: localStorage (browser-based storage)
- **No Frameworks**: Pure HTML/CSS/JavaScript for educational purposes
- **No Backend Server Required**: Runs directly in the browser

## 🗄 Database Design

### Data Storage

The system uses **localStorage** to simulate a MySQL database. All data is stored in the browser's local storage as JSON objects. This allows the system to run without a backend server while still demonstrating database concepts.

### Tables (localStorage keys)

1. **admins** - System administrators
2. **applications** - Tutor applications
3. **departments** - Academic departments
4. **programs** - Academic programs
5. **modules** - Course modules
6. **students** - Student accounts
7. **tutors** - Tutor accounts
8. **tutor_modules** - Tutor-module assignments
9. **bookings** - Tutoring bookings
10. **sessions** - Actual tutoring sessions
11. **available_slots** - Tutor availability
12. **feedback** - Student feedback
13. **payments** - Payment records

### Normalization

The data structure is designed according to Third Normal Form (3NF):
- **1NF**: Eliminated repeating groups, atomic values
- **2NF**: All non-key attributes fully dependent on primary key
- **3NF**: No transitive dependencies

### Relationships

- One Admin manages many Departments
- One Department has many Programs
- One Program has many Modules
- One Program enrolls many Students
- One Student can give many Feedback records
- One Module can have many Tutor Module records
- One Tutor can be assigned to many Tutor Module records
- One Tutor can manage many Bookings
- One Booking can be associated with Sessions
- One Booking checks Available Slots

## 📥 Installation Instructions

### Prerequisites

- Modern web browser (Chrome, Firefox, Edge, Safari, etc.)
- No server installation required
- No database installation required

### Step 1: Download/Extract Project

1. Download or extract the `unitutor` folder
2. Place it in any location on your computer
3. No special installation needed

### Step 2: Open the Application

1. Navigate to the `unitutor` folder
2. Double-click on `index.html`
3. The application will open in your default browser
4. Alternatively, right-click `index.html` and select "Open with" → your browser

### Step 3: Automatic Database Initialization

The system automatically initializes with sample data on first load:
- 5 departments
- 10 programs
- 20 modules
- 1 admin account
- 30 students
- 10 tutors
- 20 bookings
- 10 sessions
- 10 available slots
- 10 feedback records
- 10 payment records
- 5 applications

## ⚙ Configuration

### No Configuration Required

The system uses localStorage which is built into all modern browsers. No database configuration, server setup, or environment variables are needed.

### Resetting Data

To reset all data to initial sample data:
1. Go to Admin Dashboard → Database Management
2. Click "Reset Database"
3. Confirm the reset
4. All data will be restored to sample data

### Clearing Browser Data

To completely clear all UniTutor data:
1. Open browser DevTools (F12)
2. Go to Application/Storage tab
3. Find Local Storage
4. Delete all items for the current domain
5. Refresh the page to reinitialize with sample data

## 🚀 Running the System

### Access the Application

1. Open `index.html` in your web browser
2. You will see the landing page with hero section
3. Navigate through the public pages (Home, About, How It Works)

### Login

1. Click "Login" in the navigation bar
2. Use one of the test accounts below
3. You will be redirected to the appropriate dashboard based on your role

## 🔑 Test Accounts

### Admin Account
- **Email**: admin@unitutor.test
- **Password**: Admin123
- **Access**: Full system administration

### Student Account
- **Email**: student@unitutor.test
- **Password**: Student123
- **Access**: Student dashboard, find tutors, book sessions

### Tutor Account
- **Email**: tutor@unitutor.test
- **Password**: Tutor123
- **Access**: Tutor dashboard, manage availability, confirm bookings

**Note**: Passwords are hashed using a simple hash function for demonstration purposes. In a production system, use bcrypt or similar.

## 🎓 Demonstration Flow

Follow this complete flow to demonstrate the system:

### Step 1: Admin Login
1. Login as admin (admin@unitutor.test / Admin123)
2. View the admin dashboard with statistics
3. Navigate through different sections

### Step 2: Admin Adds Module
1. Go to "Modules" in admin sidebar
2. Click "Add New Module"
3. Enter module details (e.g., CS107: Web Development)
4. Click "Add Module"

### Step 3: Admin Adds Tutor
1. Go to "Tutors" in admin sidebar
2. Click "Add New Tutor"
3. Enter tutor details
4. Click "Add Tutor"

### Step 4: Admin Assigns Tutor to Module
1. Go to admin dashboard
2. Navigate to "Tutor Modules" (or use Database Management)
3. Add tutor-module assignment through the interface

### Step 5: Student Login
1. Logout from admin
2. Login as student (student@unitutor.test / Student123)
3. View student dashboard

### Step 6: Student Searches for Module
1. Click "Find Tutor"
2. Search by module name or code
3. View available tutors

### Step 7: Student Views Tutor Profile
1. Click "View Profile" on a tutor card
2. View tutor information and available slots

### Step 8: Student Books Session
1. Click "Book" on an available time slot
2. Enter reason for tutoring
3. Click "Submit Booking"
4. See success message

### Step 9: Tutor Login
1. Logout from student
2. Login as tutor (tutor@unitutor.test / Tutor123)
3. View tutor dashboard with pending booking

### Step 10: Tutor Confirms Booking
1. Go to "Bookings"
2. Find the pending booking
3. Click "Confirm"
4. Session is automatically created

### Step 11: System Displays Session
1. Go to "Sessions"
2. View the scheduled session
3. Update session status to "Completed"

### Step 12: Student Gives Feedback
1. Logout from tutor
2. Login as student
3. Go to "My Feedback"
4. Select completed session
5. Enter rating and comments
6. Submit feedback

### Step 13: Admin Views Reports
1. Logout from student
2. Login as admin
3. Go to "Reports"
4. View all 4 reports:
   - Student Activity Report
   - Tutor Performance Report
   - Module Demand Report
   - Booking Report

### Step 14: Admin Views Database
1. Go to "Database Management" in admin sidebar
2. View all tables and record counts
3. Click "View Data" on any table
4. View table structure and data
5. Reset database if needed

## 🔧 CRUD Operations

The system demonstrates complete CRUD operations for all major entities:

### Students (admin/students.html)
- **Create**: Add new students with program assignment
- **Read**: View all students with search functionality
- **Update**: Edit student information
- **Delete**: Remove students (with confirmation)

### Tutors (admin/tutors.html)
- **Create**: Add new tutors with status
- **Read**: View all tutors with search functionality
- **Update**: Edit tutor information
- **Delete**: Remove tutors (with confirmation)

### Departments (admin/departments.html)
- **Create**: Add new departments
- **Read**: View all departments with program counts
- **Update**: Edit department information
- **Delete**: Remove departments (with confirmation)

### Programs (admin/programs.html)
- **Create**: Add new programs linked to departments
- **Read**: View all programs with module/student counts
- **Update**: Edit program information
- **Delete**: Remove programs (with confirmation)

### Modules (admin/modules.html)
- **Create**: Add new modules linked to programs
- **Read**: View all modules with tutor counts
- **Update**: Edit module information
- **Delete**: Remove modules (with confirmation)

### Bookings (admin/bookings.html)
- **Create**: Through student booking system
- **Read**: View all bookings with filters
- **Update**: Change booking status (confirm/cancel)
- **Delete**: Remove bookings (with confirmation)

### Sessions (admin/sessions.html)
- **Create**: Automatically created from confirmed bookings
- **Read**: View all sessions with filters
- **Update**: Change session status (complete/cancel)
- **Delete**: Remove sessions (with confirmation)

### Payments (admin/payments.html)
- **Create**: Add new payment records
- **Read**: View all payments with filters
- **Update**: Change payment status
- **Delete**: Remove payments (with confirmation)

### Feedback (admin/feedback.html)
- **Create**: Through student feedback system
- **Read**: View all feedback
- **Update**: Not applicable (feedback is read-only)
- **Delete**: Remove feedback (with confirmation)

## 📊 Reports

The system includes 4 comprehensive reports:

### 1. Student Activity Report (reports/student_activity.html)
Shows for each student:
- Student number, name, email, program
- Total bookings
- Completed sessions
- Cancelled sessions

**JavaScript Logic**: Filters bookings and sessions by student, counts totals, calculates statistics

### 2. Tutor Performance Report (reports/tutor_performance.html)
Shows for each tutor:
- Tutor number, name, email, status
- Total sessions
- Completed sessions
- Average rating
- Number of students helped

**JavaScript Logic**: Aggregates session data, calculates average rating from feedback, counts unique students

### 3. Module Demand Report (reports/module_demand.html)
Shows for each module:
- Module code, name, department, program
- Total bookings
- Completed sessions
- Number of students requesting tutoring
- Sorted by demand (bookings)

**JavaScript Logic**: Groups bookings by module, counts sessions and unique students, sorts by demand

### 4. Booking Report (reports/booking_report.html)
Shows detailed booking information with filters:
- Booking ID, student, tutor, module
- Date, status, reason
- Filter by: date, module, tutor, status

**JavaScript Logic**: Joins booking data with student, tutor, and module information, applies filters

## 📁 Project Structure

```
unitutor/
├── index.html                          # Landing page
├── login.html                          # Login page
├── register.html                       # Student registration
│
├── assets/
│   ├── css/
│   │   └── style.css                  # Main stylesheet
│   ├── js/
│   │   ├── database.js                 # Database simulation (localStorage)
│   │   └── auth.js                    # Authentication module
│   └── images/                       # Image assets
│
├── admin/
│   ├── dashboard.html                  # Admin dashboard
│   ├── students.html                   # Student management
│   ├── tutors.html                     # Tutor management
│   ├── departments.html                # Department management
│   ├── programs.html                   # Program management
│   ├── modules.html                    # Module management
│   ├── applications.html               # Application management
│   ├── bookings.html                   # Booking management
│   ├── sessions.html                   # Session management
│   ├── payments.html                   # Payment management
│   ├── feedback.html                   # Feedback management
│   └── database.html                  # Database management
│
├── student/
│   ├── dashboard.html                  # Student dashboard
│   ├── find_tutor.html                 # Find tutors
│   ├── bookings.html                   # My bookings
│   ├── sessions.html                   # My sessions
│   ├── feedback.html                   # My feedback
│   └── profile.html                    # My profile
│
├── tutor/
│   ├── dashboard.html                  # Tutor dashboard
│   ├── modules.html                    # My modules
│   ├── slots.html                      # Available slots
│   ├── bookings.html                   # Bookings
│   ├── sessions.html                   # Sessions
│   ├── feedback.html                   # Feedback
│   └── profile.html                    # Profile
│
└── reports/
    ├── index.html                      # Reports index
    ├── student_activity.html           # Student activity report
    ├── tutor_performance.html          # Tutor performance report
    ├── module_demand.html              # Module demand report
    └── booking_report.html             # Booking report
```

## 🔒 Security Features

- **Password Hashing**: All passwords stored using a simple hash function (for demonstration)
- **Input Validation**: All user inputs are validated before processing
- **Role-Based Access Control**: Users can only access pages appropriate to their role
- **Authentication Check**: Protected pages require login
- **Session Management**: Browser localStorage for session persistence
- **Data Isolation**: Each browser has its own isolated data

**Note**: This is a prototype using localStorage. For production, use a proper backend with secure password hashing (bcrypt), HTTPS, and server-side session management.

## 🔍 Troubleshooting

### Data Not Loading

**Problem**: System shows no data or empty tables

**Solution**:
1. Clear browser localStorage (see Configuration section)
2. Refresh the page to reinitialize with sample data
3. Check browser console for JavaScript errors
4. Ensure JavaScript is enabled in browser

### Login Not Working

**Problem**: Cannot login with test accounts

**Solution**:
1. Verify sample data was initialized
2. Check that test accounts exist in localStorage
3. Clear localStorage and refresh to reinitialize
4. Try using Incognito/Private mode

### Session Issues

**Problem**: Logged out automatically

**Solution**:
1. Check if localStorage is being cleared by browser settings
2. Ensure cookies/localStorage are enabled
3. Try using a different browser
4. Check browser console for errors

### CSS Not Loading

**Problem**: Page looks unstyled

**Solution**:
1. Check that `assets/css/style.css` exists in the correct location
2. Verify file path in HTML is correct
3. Clear browser cache
4. Check browser console for 404 errors

### Reports Not Showing Data

**Problem**: Reports show no data

**Solution**:
1. Ensure sample data was initialized
2. Check that localStorage has data (use DevTools)
3. Verify JavaScript is loading correctly
4. Check browser console for errors

## 📝 Additional Notes

### Database Reset

To reset the database:
1. Go to Admin Dashboard → Database Management
2. Click "Reset Database"
3. Confirm the reset
4. All data will be restored to sample data

### Adding New Sample Data

To add more sample data:
1. Use the Admin Dashboard to add records through CRUD interfaces
2. Or use browser DevTools to manually edit localStorage
3. Or use the Database Management page to view and edit data

### Customizing the System

To customize colors and styling:
- Edit `assets/css/style.css`
- Modify CSS variables in the `:root` section

To add new pages:
1. Create HTML file in appropriate directory
2. Include necessary JavaScript files (database.js, auth.js)
3. Follow existing page structure
4. Add navigation links in sidebars

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the code comments for explanations
3. Check browser console for JavaScript errors
4. Verify localStorage data using DevTools

## 📄 License

This is a university project prototype for educational purposes.

---

**UniTutor - University Peer Tutoring Management System**

A Database Systems Project demonstrating:
- Relational database design concepts
- Normalization (3NF)
- CRUD operations
- Role-based access control
- Report generation
- Frontend development practices
- localStorage data persistence

**Version**: 2.0 (localStorage version)  
**Date**: September 2026
