/**
 * UniTutor Database Simulation Module
 * Uses localStorage to simulate MySQL database operations
 * This replaces the PHP/MySQL backend for the prototype
 */

const DB = {
    // Initialize database with sample data
    init() {
        if (!localStorage.getItem('unitutor_initialized')) {
            this.seedData();
            localStorage.setItem('unitutor_initialized', 'true');
        } else {
            // Migrate existing data to ensure all applications have complete info
            this.migrateApplications();
        }
    },

    // Migrate existing applications to have complete data
    migrateApplications() {
        const applications = this.getAll('Application');
        const tutors = this.getAll('Tutor');
        let needsUpdate = false;

        applications.forEach(app => {
            if (!app.ApplicantName || !app.ApplicantEmail) {
                const tutor = tutors.find(t => t.TutorID === app.TutorID);
                if (tutor) {
                    app.ApplicantName = `${tutor.TutorName} ${tutor.TutorSurname}`;
                    app.ApplicantEmail = tutor.TutorEmail;
                    needsUpdate = true;
                } else {
                    // Fallback if no tutor found
                    app.ApplicantName = app.ApplicantName || 'Pending Applicant';
                    app.ApplicantEmail = app.ApplicantEmail || 'pending@unitutor.test';
                    needsUpdate = true;
                }
            }
        });

        if (needsUpdate) {
            localStorage.setItem('Application', JSON.stringify(applications));
        }
    },

    // Seed initial sample data
    seedData() {
        // Administrator
        const administrators = [
            { AdminID: 'AD001', AdminName: 'Admin', AdminSurname: 'User', AdminEmail: 'admin@unitutor.test', AdminContactNumber: 1234567890, AdminUsername: 'admin', PasswordHash: this.hashPassword('Admin123'), AdminRole: 'Super Admin' }
        ];

        // Department
        const departments = [
            { DepartmentID: 'DP1001', DepartmentName: 'Natural and Applied Science', Faculty: 'Faculty of Natural and Applied Sciences', AdminID: 'AD001' },
            { DepartmentID: 'DP1002', DepartmentName: 'Data Science', Faculty: 'Faculty of Natural and Applied Sciences', AdminID: 'AD001' },
            { DepartmentID: 'DP1003', DepartmentName: 'Information Technology', Faculty: 'Faculty of Engineering and Technology', AdminID: 'AD001' }
        ];

        // Program
        const programs = [
            { ProgrammeID: 'PC1001', ProgrammeName: 'BSc Mathematical and Computer Science', NQFLevel: 7, Duration: 4, DepartmentID: 'DP1001' },
            { ProgrammeID: 'PC1002', ProgrammeName: 'BSc Data Science', NQFLevel: 7, Duration: 4, DepartmentID: 'DP1002' },
            { ProgrammeID: 'PC1003', ProgrammeName: 'Diploma in Information Computer Technology (ICT)', NQFLevel: 6, Duration: 3, DepartmentID: 'DP1003' }
        ];

        // Module
        const modules = [
            { ModuleCode: 'CS101', ModuleName: 'Database Systems', ModuleDescription: 'Introduction to database design and SQL', ModuleLevel: 1, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS102', ModuleName: 'Operating Systems', ModuleDescription: 'Study of operating system concepts', ModuleLevel: 1, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS103', ModuleName: 'System Analysis and Design', ModuleDescription: 'Systems analysis methodologies', ModuleLevel: 1, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'MATH201', ModuleName: 'Distribution Theory', ModuleDescription: 'Probability and statistics', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1003' },
            { ModuleCode: 'CS105', ModuleName: 'Algorithm Analysis', ModuleDescription: 'Algorithm design and analysis', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS106', ModuleName: 'Programming', ModuleDescription: 'Introduction to programming', ModuleLevel: 1, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS107', ModuleName: 'Computer Networks', ModuleDescription: 'Network fundamentals', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'MATH202', ModuleName: 'Calculus I', ModuleDescription: 'Differential and integral calculus', ModuleLevel: 1, Credits: 15, ProgrammeID: 'PC1003' },
            { ModuleCode: 'MATH203', ModuleName: 'Linear Algebra', ModuleDescription: 'Linear algebra concepts', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1003' },
            { ModuleCode: 'PHYS301', ModuleName: 'Mechanics', ModuleDescription: 'Classical mechanics', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CHEM401', ModuleName: 'Organic Chemistry', ModuleDescription: 'Organic chemistry fundamentals', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'BUS501', ModuleName: 'Principles of Management', ModuleDescription: 'Management principles', ModuleLevel: 1, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS108', ModuleName: 'Web Development', ModuleDescription: 'Web technologies', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS109', ModuleName: 'Data Structures', ModuleDescription: 'Data structures and algorithms', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS110', ModuleName: 'Software Engineering', ModuleDescription: 'Software engineering practices', ModuleLevel: 3, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS111', ModuleName: 'Machine Learning', ModuleDescription: 'ML fundamentals', ModuleLevel: 3, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS112', ModuleName: 'Artificial Intelligence', ModuleDescription: 'AI concepts', ModuleLevel: 3, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'MATH204', ModuleName: 'Statistics', ModuleDescription: 'Statistical methods', ModuleLevel: 2, Credits: 15, ProgrammeID: 'PC1003' },
            { ModuleCode: 'CS113', ModuleName: 'Cybersecurity', ModuleDescription: 'Security fundamentals', ModuleLevel: 3, Credits: 15, ProgrammeID: 'PC1001' },
            { ModuleCode: 'CS114', ModuleName: 'Mobile App Development', ModuleDescription: 'Mobile development', ModuleLevel: 3, Credits: 15, ProgrammeID: 'PC1001' }
        ];

        // Student
        const students = [];
        const firstNames = ['James', 'Emma', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Jessica', 'William', 'Ashley', 'Daniel', 'Jennifer', 'Matthew', 'Stephanie', 'Christopher', 'Nicole', 'Andrew', 'Melissa', 'Joshua', 'Amanda', 'Ryan', 'Elizabeth', 'Brandon', 'Michelle', 'Jason', 'Kimberly', 'Justin', 'Laura', 'Kevin', 'Rebecca'];
        const lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        
        for (let i = 1; i <= 30; i++) {
            students.push({
                StudentID: `ST${String(i).padStart(4, '0')}`,
                StudentName: firstNames[i % firstNames.length],
                StudentSurname: lastNames[i % lastNames.length],
                StudentEmail: `student${i}@unitutor.test`,
                StudentContactNumber: 1234567890 + i,
                StudentYearOfStudy: (i % 4) + 1,
                ProgrammeID: ['PC1001', 'PC1002', 'PC1003'][i % 3],
                PasswordHash: this.hashPassword(i === 1 ? 'Student123' : 'password123')
            });
        }

        // Tutor
        const tutors = [];
        const tutorFirstNames = ['Alex', 'Jordan', 'Taylor', 'Morgan', 'Casey', 'Riley', 'Jamie', 'Quinn', 'Avery', 'Parker'];
        for (let i = 1; i <= 10; i++) {
            tutors.push({
                TutorID: `TU${String(i).padStart(4, '0')}`,
                TutorName: tutorFirstNames[i % tutorFirstNames.length],
                TutorSurname: lastNames[i % lastNames.length],
                TutorEmail: `tutor${i}@unitutor.test`,
                TutorContactNumber: 1234567890 + i,
                TutorYearOfStudy: (i % 4) + 1,
                TutorStatus: 'Active',
                PasswordHash: this.hashPassword(i === 1 ? 'Tutor123' : 'password123')
            });
        }

        // TutorModule
        const tutorModules = [];
        const moduleCodes = modules.map(m => m.ModuleCode);
        for (let i = 1; i <= 10; i++) {
            tutorModules.push({
                TutorID: `TU${String(i).padStart(4, '0')}`,
                ModuleCode: moduleCodes[(i - 1) % 20],
                Semester: 'Semester 1'
            });
            tutorModules.push({
                TutorID: `TU${String(i).padStart(4, '0')}`,
                ModuleCode: moduleCodes[i % 20],
                Semester: 'Semester 2'
            });
        }

        // Booking
        const bookings = [];
        const statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
        const bookingTypes = ['Private', 'Public'];
        for (let i = 1; i <= 20; i++) {
            const bookingDate = new Date();
            bookingDate.setDate(bookingDate.getDate() + i);
            const startTime = new Date(bookingDate);
            startTime.setHours(9 + (i % 4), 0, 0);
            const endTime = new Date(startTime);
            endTime.setHours(startTime.getHours() + 1);
            
            bookings.push({
                BookingID: `BK${String(i).padStart(6, '0')}`,
                TutorID: `TU${String((i % 10) + 1).padStart(4, '0')}`,
                ModuleCode: moduleCodes[(i - 1) % 20],
                StudentID: `ST${String((i % 30) + 1).padStart(4, '0')}`,
                BookingDate: bookingDate.toISOString().split('T')[0],
                BookingStartTime: startTime.toISOString(),
                BookingEndTime: endTime.toISOString(),
                BookingStatus: statuses[i % 4],
                BookingType: bookingTypes[i % 2]
            });
        }

        // Tutorial
        const tutorials = [];
        for (let i = 1; i <= 10; i++) {
            const tutorialDate = new Date();
            tutorialDate.setDate(tutorialDate.getDate() + i);
            const startTime = new Date(tutorialDate);
            startTime.setHours(9 + (i % 4), 0, 0);
            const endTime = new Date(startTime);
            endTime.setHours(startTime.getHours() + 1);
            
            tutorials.push({
                TutorialID: `TUT${String(i).padStart(6, '0')}`,
                BookingID: `BK${String(i).padStart(6, '0')}`,
                TutorialDate: tutorialDate.toISOString().split('T')[0],
                TutorialStartTime: startTime.toISOString(),
                TutorialEndTime: endTime.toISOString(),
                TutorialMode: 'In-Person',
                TutorialType: 'Individual',
                TutorialAttendance: 1,
                TutorialStatus: i <= 5 ? 'Completed' : 'Scheduled'
            });
        }

        // Application
        const applications = [];
        for (let i = 1; i <= 5; i++) {
            applications.push({
                ApplicationID: `APP${String(i).padStart(6, '0')}`,
                ApplicationDate: new Date().toISOString().split('T')[0],
                AcademicYear: 2026,
                ModuleMark: 70 + (i * 5),
                ApplicationStatus: i <= 2 ? 'Approved' : 'Pending',
                ModulesAppliedFor: [moduleCodes[(i - 1) % 20], moduleCodes[i % 20]],
                AdminID: 'AD001',
                TutorID: `TU${String(i).padStart(4, '0')}`,
                ApplicantName: `${tutorFirstNames[i % tutorFirstNames.length]} ${lastNames[i % lastNames.length]}`,
                ApplicantEmail: `tutor${i}@unitutor.test`
            });
        }

        // Feedback
        const feedback = [];
        for (let i = 1; i <= 10; i++) {
            feedback.push({
                FeedbackID: `FB${String(i).padStart(6, '0')}`,
                Rating: Math.floor(Math.random() * 2) + 4,
                FeedbackComments: 'Great tutoring session, very helpful!',
                FeedbackDate: new Date().toISOString().split('T')[0],
                TutorID: `TU${String((i % 10) + 1).padStart(4, '0')}`,
                StudentID: `ST${String((i % 30) + 1).padStart(4, '0')}`,
                BookingID: `BK${String(i).padStart(6, '0')}`
            });
        }

        // Payment
        const payments = [];
        for (let i = 1; i <= 10; i++) {
            const payPeriodStart = new Date();
            payPeriodStart.setDate(1);
            payPeriodStart.setMonth(payPeriodStart.getMonth() - 1);
            const payPeriodEnd = new Date();
            payPeriodEnd.setDate(0);
            const paymentDate = new Date();
            paymentDate.setDate(25);
            
            payments.push({
                PaymentID: `PAY${String(i).padStart(6, '0')}`,
                PaymentDate: paymentDate.toISOString().split('T')[0],
                PayPeriodStart: payPeriodStart.toISOString().split('T')[0],
                PayPeriodEnd: payPeriodEnd.toISOString().split('T')[0],
                HoursWorked: Math.min(10, i),
                HourlyRate: 190.00,
                PaymentStatus: 'Completed',
                PaymentReference: `PAY${String(i).padStart(6, '0')}`,
                TutorID: `TU${String(i).padStart(4, '0')}`,
                AdminID: 'AD001'
            });
        }

        // AvailableSlot - Start empty, tutors will add their own slots
        const availableSlots = [];

        // FreeSlot_Availability
        const freeSlots = [
            { StartTime: '09:00:00', EndTime: '10:00:00', DayOfWeek: 'Monday' },
            { StartTime: '10:00:00', EndTime: '11:00:00', DayOfWeek: 'Monday' },
            { StartTime: '11:00:00', EndTime: '12:00:00', DayOfWeek: 'Monday' },
            { StartTime: '14:00:00', EndTime: '15:00:00', DayOfWeek: 'Monday' },
            { StartTime: '15:00:00', EndTime: '16:00:00', DayOfWeek: 'Monday' },
            { StartTime: '09:00:00', EndTime: '10:00:00', DayOfWeek: 'Tuesday' },
            { StartTime: '10:00:00', EndTime: '11:00:00', DayOfWeek: 'Tuesday' },
            { StartTime: '11:00:00', EndTime: '12:00:00', DayOfWeek: 'Tuesday' },
            { StartTime: '14:00:00', EndTime: '15:00:00', DayOfWeek: 'Tuesday' },
            { StartTime: '15:00:00', EndTime: '16:00:00', DayOfWeek: 'Tuesday' },
            { StartTime: '09:00:00', EndTime: '10:00:00', DayOfWeek: 'Wednesday' },
            { StartTime: '10:00:00', EndTime: '11:00:00', DayOfWeek: 'Wednesday' },
            { StartTime: '11:00:00', EndTime: '12:00:00', DayOfWeek: 'Wednesday' },
            { StartTime: '14:00:00', EndTime: '15:00:00', DayOfWeek: 'Wednesday' },
            { StartTime: '15:00:00', EndTime: '16:00:00', DayOfWeek: 'Wednesday' },
            { StartTime: '09:00:00', EndTime: '10:00:00', DayOfWeek: 'Thursday' },
            { StartTime: '10:00:00', EndTime: '11:00:00', DayOfWeek: 'Thursday' },
            { StartTime: '11:00:00', EndTime: '12:00:00', DayOfWeek: 'Thursday' },
            { StartTime: '14:00:00', EndTime: '15:00:00', DayOfWeek: 'Thursday' },
            { StartTime: '15:00:00', EndTime: '16:00:00', DayOfWeek: 'Thursday' },
            { StartTime: '09:00:00', EndTime: '10:00:00', DayOfWeek: 'Friday' },
            { StartTime: '10:00:00', EndTime: '11:00:00', DayOfWeek: 'Friday' },
            { StartTime: '11:00:00', EndTime: '12:00:00', DayOfWeek: 'Friday' },
            { StartTime: '14:00:00', EndTime: '15:00:00', DayOfWeek: 'Friday' },
            { StartTime: '15:00:00', EndTime: '16:00:00', DayOfWeek: 'Friday' }
        ];

        // Save all data to localStorage
        localStorage.setItem('Administrator', JSON.stringify(administrators));
        localStorage.setItem('Department', JSON.stringify(departments));
        localStorage.setItem('Program', JSON.stringify(programs));
        localStorage.setItem('Module', JSON.stringify(modules));
        localStorage.setItem('Student', JSON.stringify(students));
        localStorage.setItem('Tutor', JSON.stringify(tutors));
        localStorage.setItem('TutorModule', JSON.stringify(tutorModules));
        localStorage.setItem('Booking', JSON.stringify(bookings));
        localStorage.setItem('Tutorial', JSON.stringify(tutorials));
        localStorage.setItem('Application', JSON.stringify(applications));
        localStorage.setItem('Feedback', JSON.stringify(feedback));
        localStorage.setItem('Payment', JSON.stringify(payments));
        localStorage.setItem('AvailableSlot', JSON.stringify(availableSlots));
        localStorage.setItem('FreeSlot_Availability', JSON.stringify(freeSlots));
        localStorage.setItem('PublicSession', JSON.stringify([]));
        localStorage.setItem('AuditTrail', JSON.stringify([]));
    },

    // Simple password hashing simulation
    hashPassword(password) {
        // In a real system, use bcrypt or similar
        // For prototype, we'll use a simple hash
        let hash = 0;
        for (let i = 0; i < password.length; i++) {
            const char = password.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return hash.toString(16);
    },

    // Generic CRUD operations
    getAll(table) {
        const data = localStorage.getItem(table);
        return data ? JSON.parse(data) : [];
    },

    getById(table, id, idField) {
        const items = this.getAll(table);
        return items.find(item => item[idField] === id);
    },

    add(table, item) {
        const items = this.getAll(table);
        items.push(item);
        localStorage.setItem(table, JSON.stringify(items));
        return item;
    },

    update(table, id, idField, updates) {
        const items = this.getAll(table);
        const index = items.findIndex(item => item[idField] === id);
        if (index !== -1) {
            items[index] = { ...items[index], ...updates };
            localStorage.setItem(table, JSON.stringify(items));
            return items[index];
        }
        return null;
    },

    delete(table, id, idField) {
        const items = this.getAll(table);
        const filtered = items.filter(item => item[idField] !== id);
        localStorage.setItem(table, JSON.stringify(filtered));
        return filtered.length < items.length;
    },

    // Authentication
    authenticate(email, password, role) {
        const table = role === 'admin' ? 'Administrator' : role === 'tutor' ? 'Tutor' : 'Student';
        const users = this.getAll(table);
        const emailField = role === 'admin' ? 'AdminEmail' : role === 'tutor' ? 'TutorEmail' : 'StudentEmail';
        const passwordField = 'PasswordHash';
        const user = users.find(u => u[emailField] === email && u[passwordField] === this.hashPassword(password));
        return user || null;
    },

    // Statistics
    getCount(table) {
        return this.getAll(table).length;
    },

    getAverageRating(tutorId) {
        const feedback = this.getAll('Feedback');
        const tutorFeedback = feedback.filter(f => f.TutorID === tutorId);
        if (tutorFeedback.length === 0) return 0;
        const sum = tutorFeedback.reduce((acc, f) => acc + f.Rating, 0);
        return (sum / tutorFeedback.length).toFixed(1);
    },

    // Audit Trail functions
    logAudit(action, entity, entityId, user, userRole, description) {
        const audit = this.getAll('AuditTrail');
        const now = new Date();
        audit.push({
            AuditID: `AUD${String(audit.length + 1).padStart(6, '0')}`,
            DateTime: now.toISOString(),
            User: user,
            UserRole: userRole,
            Action: action,
            Entity: entity,
            EntityID: entityId,
            Description: description
        });
        localStorage.setItem('AuditTrail', JSON.stringify(audit));
    },

    getAuditTrail() {
        return this.getAll('AuditTrail').sort((a, b) => new Date(b.DateTime) - new Date(a.DateTime));
    },

    // Search and filter functions
    searchStudents(query) {
        const students = this.getAll('Student');
        const programs = this.getAll('Program');
        return students.filter(s => 
            s.StudentName.toLowerCase().includes(query.toLowerCase()) ||
            s.StudentSurname.toLowerCase().includes(query.toLowerCase()) ||
            s.StudentEmail.toLowerCase().includes(query.toLowerCase())
        ).map(s => ({
            ...s,
            ProgrammeName: programs.find(p => p.ProgrammeID === s.ProgrammeID)?.ProgrammeName || 'N/A'
        }));
    },

    searchTutors(query) {
        const tutors = this.getAll('Tutor');
        const tutorModules = this.getAll('TutorModule');
        const modules = this.getAll('Module');
        
        return tutors.filter(t => 
            t.TutorName.toLowerCase().includes(query.toLowerCase()) ||
            t.TutorSurname.toLowerCase().includes(query.toLowerCase()) ||
            t.TutorEmail.toLowerCase().includes(query.toLowerCase()) ||
            t.TutorID.toLowerCase().includes(query.toLowerCase())
        ).map(t => {
            const tModules = tutorModules.filter(tm => tm.TutorID === t.TutorID);
            const moduleNames = tModules.map(tm => modules.find(m => m.ModuleCode === tm.ModuleCode)?.ModuleName || '').filter(Boolean);
            return {
                ...t,
                modules: moduleNames,
                average_rating: this.getAverageRating(t.TutorID)
            };
        });
    },

    filterBookings(filters) {
        let bookings = this.getAll('Booking');
        const students = this.getAll('Student');
        const tutors = this.getAll('Tutor');
        const modules = this.getAll('Module');
        
        if (filters.BookingStatus) {
            bookings = bookings.filter(b => b.BookingStatus === filters.BookingStatus);
        }
        if (filters.BookingType) {
            bookings = bookings.filter(b => b.BookingType === filters.BookingType);
        }
        if (filters.TutorID) {
            bookings = bookings.filter(b => b.TutorID === filters.TutorID);
        }
        if (filters.StudentID) {
            bookings = bookings.filter(b => b.StudentID === filters.StudentID);
        }
        if (filters.ModuleCode) {
            bookings = bookings.filter(b => b.ModuleCode === filters.ModuleCode);
        }
        if (filters.BookingDate) {
            bookings = bookings.filter(b => b.BookingDate === filters.BookingDate);
        }

        return bookings.map(b => ({
            ...b,
            student_name: `${students.find(s => s.StudentID === b.StudentID)?.StudentName || ''} ${students.find(s => s.StudentID === b.StudentID)?.StudentSurname || ''}`,
            tutor_name: `${tutors.find(t => t.TutorID === b.TutorID)?.TutorName || ''} ${tutors.find(t => t.TutorID === b.TutorID)?.TutorSurname || ''}`,
            module_name: modules.find(m => m.ModuleCode === b.ModuleCode)?.ModuleName || 'N/A'
        }));
    },

    // Book a session
    bookSession(bookingData) {
        const booking = {
            BookingID: `BK${String(Date.now()).padStart(6, '0')}`,
            ...bookingData,
            BookingStatus: 'Pending'
        };
        this.add('Booking', booking);
        return { success: true, booking };
    },

    // Confirm booking
    confirmBooking(bookingId) {
        const booking = this.getById('Booking', bookingId, 'BookingID');
        if (!booking) return { success: false, message: 'Booking not found' };

        this.update('Booking', bookingId, 'BookingID', { BookingStatus: 'Confirmed' });

        // Create tutorial
        const tutorial = {
            TutorialID: `TUT${String(Date.now()).padStart(6, '0')}`,
            BookingID: bookingId,
            TutorialDate: booking.BookingDate,
            TutorialStartTime: booking.BookingStartTime,
            TutorialEndTime: booking.BookingEndTime,
            TutorialMode: 'In-Person',
            TutorialType: booking.BookingType,
            TutorialAttendance: 0,
            TutorialStatus: 'Scheduled'
        };
        this.add('Tutorial', tutorial);

        return { success: true, tutorial };
    },

    // Get tutor modules with details
    getTutorModules(tutorId) {
        const tutorModules = this.getAll('TutorModule');
        const modules = this.getAll('Module');
        const programs = this.getAll('Program');
        const departments = this.getAll('Department');

        return tutorModules
            .filter(tm => tm.TutorID === tutorId)
            .map(tm => {
                const module = modules.find(m => m.ModuleCode === tm.ModuleCode);
                const program = programs.find(p => p.ProgrammeID === module?.ProgrammeID);
                const department = departments.find(d => d.DepartmentID === program?.DepartmentID);
                return {
                    ...tm,
                    ModuleCode: module?.ModuleCode,
                    ModuleName: module?.ModuleName,
                    ProgrammeName: program?.ProgrammeName,
                    DepartmentName: department?.DepartmentName
                };
            });
    },

    // Get available slots for a tutor and module
    getAvailableSlots(tutorId, moduleCode) {
        const slots = this.getAll('AvailableSlot');
        return slots.filter(s => s.TutorID === tutorId && s.ModuleCode === moduleCode && s.SlotStatus === 'Available');
    },

    // Book a slot
    bookSlot(slotId, studentId, tutorId, moduleCode, reason) {
        const slot = this.getById('AvailableSlot', slotId, 'SlotID');
        if (!slot || slot.SlotStatus !== 'Available') {
            return { success: false, message: 'Slot not available' };
        }

        // Update slot status
        this.update('AvailableSlot', slotId, 'SlotID', { SlotStatus: 'Booked' });

        // Create booking
        const booking = {
            BookingID: `BK${String(Date.now()).padStart(6, '0')}`,
            StudentID: studentId,
            TutorID: tutorId,
            ModuleCode: moduleCode,
            BookingDate: slot.SlotDate,
            BookingStartTime: slot.SlotDate + 'T' + slot.StartTime,
            BookingEndTime: slot.SlotDate + 'T' + slot.EndTime,
            BookingStatus: 'Pending',
            BookingType: 'Private',
            Reason: reason
        };
        this.add('Booking', booking);

        return { success: true, booking };
    },

    // Generate sequential ID for entities
    generateSequentialID(prefix, table, idField) {
        const items = this.getAll(table);
        if (items.length === 0) {
            return `${prefix}1001`;
        }
        const maxId = items.reduce((max, item) => {
            const num = parseInt(item[idField].replace(prefix, ''));
            return num > max ? num : max;
        }, 0);
        return `${prefix}${String(maxId + 1).padStart(4, '0')}`;
    },

    // Reset database (keep only admins)
    resetDatabase() {
        const admins = this.getAll('Administrator');
        localStorage.clear();
        localStorage.setItem('Administrator', JSON.stringify(admins));
        localStorage.setItem('AuditTrail', JSON.stringify([]));
        localStorage.setItem('unitutor_initialized', 'true');
        return { success: true };
    },

    // Check for slot conflicts
    hasSlotConflict(tutorId, date, startTime, endTime, excludeSlotId = null) {
        const slots = this.getAll('AvailableSlot');
        const tutorSlots = slots.filter(s => s.TutorID === tutorId && s.SlotDate === date);
        
        for (const slot of tutorSlots) {
            if (excludeSlotId && slot.SlotID === excludeSlotId) continue;
            
            const slotStart = this.timeToMinutes(slot.StartTime);
            const slotEnd = this.timeToMinutes(slot.EndTime);
            const newStart = this.timeToMinutes(startTime);
            const newEnd = this.timeToMinutes(endTime);
            
            if (newStart < slotEnd && newEnd > slotStart) {
                return true;
            }
        }
        return false;
    },

    timeToMinutes(timeStr) {
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    },

    // Public Session functions
    createPublicSession(sessionData) {
        const publicSessions = this.getAll('PublicSession');
        const { ModuleCode, TutorID, NormalSlot, AfterHoursSlot, AdditionalTutors = [] } = sessionData;
        
        // Check for conflicts with existing public sessions for the same module
        const moduleSessions = publicSessions.filter(ps => ps.ModuleCode === ModuleCode);
        
        // Check normal slot conflict
        if (NormalSlot) {
            const [day, timeRange] = NormalSlot.split('|');
            const [startTime, endTime] = timeRange.split('-');
            const hasConflict = moduleSessions.some(ps => {
                if (!ps.NormalSlot) return false;
                const [psDay, psTimeRange] = ps.NormalSlot.split('|');
                if (psDay !== day) return false;
                const [psStart, psEnd] = psTimeRange.split('-');
                return this.timeRangesOverlap(startTime, endTime, psStart, psEnd);
            });
            if (hasConflict) {
                return { success: false, message: 'Conflict: Normal slot overlaps with existing public session for this module' };
            }
        }
        
        // Check after-hours slot conflict
        if (AfterHoursSlot) {
            const [day, timeRange] = AfterHoursSlot.split('|');
            const [startTime, endTime] = timeRange.split('-');
            const hasConflict = moduleSessions.some(ps => {
                if (!ps.AfterHoursSlot) return false;
                const [psDay, psTimeRange] = ps.AfterHoursSlot.split('|');
                if (psDay !== day) return false;
                const [psStart, psEnd] = psTimeRange.split('-');
                return this.timeRangesOverlap(startTime, endTime, psStart, psEnd);
            });
            if (hasConflict) {
                return { success: false, message: 'Conflict: After-hours slot overlaps with existing public session for this module' };
            }
        }
        
        // Create public session
        const session = {
            SessionID: this.generateSequentialID('PS', 'PublicSession', 'SessionID'),
            ModuleCode: ModuleCode,
            PrimaryTutorID: TutorID,
            AdditionalTutorIDs: AdditionalTutors,
            NormalSlot: NormalSlot,
            AfterHoursSlot: AfterHoursSlot,
            SessionStatus: 'Active',
            CreatedAt: new Date().toISOString()
        };
        
        this.add('PublicSession', session);
        return { success: true, session };
    },
    
    timeRangesOverlap(start1, end1, start2, end2) {
        const s1 = this.timeToMinutes(start1);
        const e1 = this.timeToMinutes(end1);
        const s2 = this.timeToMinutes(start2);
        const e2 = this.timeToMinutes(end2);
        return s1 < e2 && e1 > s2;
    },
    
    getPublicSessions(moduleCode = null) {
        const sessions = this.getAll('PublicSession');
        if (moduleCode) {
            return sessions.filter(s => s.ModuleCode === moduleCode && s.SessionStatus === 'Active');
        }
        return sessions.filter(s => s.SessionStatus === 'Active');
    },

    // Report functions
    getStudentActivityReport() {
        const students = this.getAll('Student');
        const bookings = this.getAll('Booking');
        const tutorials = this.getAll('Tutorial');

        return students.map(student => {
            const studentBookings = bookings.filter(b => b.StudentID === student.StudentID);
            const completedTutorials = tutorials.filter(t => 
                studentBookings.some(b => b.BookingID === t.BookingID) && t.TutorialStatus === 'Completed'
            );
            const cancelledBookings = studentBookings.filter(b => b.BookingStatus === 'Cancelled');

            return {
                StudentID: student.StudentID,
                StudentName: `${student.StudentName} ${student.StudentSurname}`,
                StudentEmail: student.StudentEmail,
                ProgrammeID: student.ProgrammeID,
                total_bookings: studentBookings.length,
                completed_sessions: completedTutorials.length,
                cancelled_sessions: cancelledBookings.length
            };
        });
    },

    getTutorPerformanceReport() {
        const tutors = this.getAll('Tutor');
        const bookings = this.getAll('Booking');
        const tutorials = this.getAll('Tutorial');
        const feedback = this.getAll('Feedback');

        return tutors.map(tutor => {
            const tutorBookings = bookings.filter(b => b.TutorID === tutor.TutorID);
            const completedTutorials = tutorials.filter(t => 
                tutorBookings.some(b => b.BookingID === t.BookingID) && t.TutorialStatus === 'Completed'
            );
            const tutorFeedback = feedback.filter(f => f.TutorID === tutor.TutorID);
            const avgRating = tutorFeedback.length > 0 
                ? (tutorFeedback.reduce((sum, f) => sum + f.Rating, 0) / tutorFeedback.length).toFixed(1)
                : 'N/A';
            const uniqueStudents = [...new Set(tutorBookings.map(b => b.StudentID))].length;

            return {
                TutorID: tutor.TutorID,
                TutorName: `${tutor.TutorName} ${tutor.TutorSurname}`,
                TutorEmail: tutor.TutorEmail,
                TutorStatus: tutor.TutorStatus,
                total_sessions: tutorBookings.length,
                completed_sessions: completedTutorials.length,
                average_rating: avgRating,
                students_helped: uniqueStudents
            };
        });
    },

    getModuleDemandReport() {
        const modules = this.getAll('Module');
        const bookings = this.getAll('Booking');
        const tutorials = this.getAll('Tutorial');

        return modules.map(module => {
            const moduleBookings = bookings.filter(b => b.ModuleCode === module.ModuleCode);
            const completedTutorials = tutorials.filter(t => 
                moduleBookings.some(b => b.BookingID === t.BookingID) && t.TutorialStatus === 'Completed'
            );
            const uniqueStudents = [...new Set(moduleBookings.map(b => b.StudentID))].length;

            return {
                ModuleCode: module.ModuleCode,
                ModuleName: module.ModuleName,
                ModuleLevel: module.ModuleLevel,
                Credits: module.Credits,
                ProgrammeID: module.ProgrammeID,
                total_bookings: moduleBookings.length,
                completed_sessions: completedTutorials.length,
                students_requesting: uniqueStudents
            };
        }).sort((a, b) => b.total_bookings - a.total_bookings);
    },

    getBookingReport(filters = {}) {
        let bookings = this.getAll('Booking');
        const students = this.getAll('Student');
        const tutors = this.getAll('Tutor');
        const modules = this.getAll('Module');

        if (filters.BookingDate) {
            bookings = bookings.filter(b => b.BookingDate === filters.BookingDate);
        }
        if (filters.ModuleCode) {
            bookings = bookings.filter(b => b.ModuleCode === filters.ModuleCode);
        }
        if (filters.TutorID) {
            bookings = bookings.filter(b => b.TutorID === filters.TutorID);
        }
        if (filters.BookingStatus) {
            bookings = bookings.filter(b => b.BookingStatus === filters.BookingStatus);
        }

        return bookings.map(b => ({
            BookingID: b.BookingID,
            student_name: `${students.find(s => s.StudentID === b.StudentID)?.StudentName || ''} ${students.find(s => s.StudentID === b.StudentID)?.StudentSurname || ''}`,
            tutor_name: `${tutors.find(t => t.TutorID === b.TutorID)?.TutorName || ''} ${tutors.find(t => t.TutorID === b.TutorID)?.TutorSurname || ''}`,
            module_name: modules.find(m => m.ModuleCode === b.ModuleCode)?.ModuleName || 'N/A',
            date: b.BookingDate,
            time: new Date(b.BookingStartTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            status: b.BookingStatus
        }));
    },

    // Reset database
    resetDatabase() {
        localStorage.clear();
        this.seedData();
        localStorage.setItem('unitutor_initialized', 'true');
        return { success: true, message: 'Database reset successfully' };
    }
};

// Initialize database on load
DB.init();

// Force reset if schema changed - remove this after first run
if (localStorage.getItem('unitutor_schema_version') !== '4') {
    localStorage.clear();
    DB.seedData();
    localStorage.setItem('unitutor_initialized', 'true');
    localStorage.setItem('unitutor_schema_version', '4');
}
