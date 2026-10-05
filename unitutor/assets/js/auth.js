/**
 * UniTutor Authentication Module
 * Handles login, register, and logout functionality
 */

const Auth = {
    // Login user
    login(email, password, role) {
        const user = DB.authenticate(email, password, role);
        
        if (user) {
            // Store user session
            const idField = role === 'admin' ? 'AdminID' : role === 'tutor' ? 'TutorID' : 'StudentID';
            const nameField = role === 'admin' ? 'AdminName' : role === 'tutor' ? 'TutorName' : 'StudentName';
            const surnameField = role === 'admin' ? 'AdminSurname' : role === 'tutor' ? 'TutorSurname' : 'StudentSurname';
            
            const session = {
                id: user[idField],
                email: role === 'admin' ? user.AdminEmail : role === 'tutor' ? user.TutorEmail : user.StudentEmail,
                role: role,
                name: `${user[nameField]} ${user[surnameField]}`,
                data: user
            };
            localStorage.setItem('user_session', JSON.stringify(session));
            return { success: true, user: session };
        }
        
        return { success: false, message: 'Invalid email or password' };
    },

    // Register new student
    registerStudent(data) {
        const students = DB.getAll('Student');
        
        // Check if email already exists
        if (students.some(s => s.StudentEmail === data.StudentEmail)) {
            return { success: false, message: 'Email already registered' };
        }
        
        // Create new student
        const newStudent = {
            StudentID: `ST${String(Date.now()).slice(-4)}`,
            StudentName: data.StudentName,
            StudentSurname: data.StudentSurname,
            StudentEmail: data.StudentEmail,
            StudentContactNumber: data.StudentContactNumber,
            StudentYearOfStudy: parseInt(data.StudentYearOfStudy),
            ProgrammeID: data.ProgrammeID,
            PasswordHash: DB.hashPassword(data.password)
        };
        
        DB.add('Student', newStudent);
        
        // Auto-login after registration
        return this.login(data.StudentEmail, data.password, 'student');
    },

    // Register new tutor
    registerTutor(data) {
        const tutors = DB.getAll('Tutor');
        
        // Check if email already exists
        if (tutors.some(t => t.TutorEmail === data.TutorEmail)) {
            return { success: false, message: 'Email already registered' };
        }
        
        // Create new tutor
        const newTutor = {
            TutorID: `TU${String(Date.now()).slice(-4)}`,
            TutorName: data.TutorName,
            TutorSurname: data.TutorSurname,
            TutorEmail: data.TutorEmail,
            TutorContactNumber: data.TutorContactNumber,
            TutorYearOfStudy: data.TutorYearOfStudy,
            TutorStatus: 'Active',
            PasswordHash: DB.hashPassword(data.password)
        };
        
        DB.add('Tutor', newTutor);
        
        // Auto-login after registration
        return this.login(data.TutorEmail, data.password, 'tutor');
    },

    // Register new admin
    registerAdmin(data) {
        const admins = DB.getAll('Administrator');
        
        // Check if email already exists
        if (admins.some(a => a.AdminEmail === data.AdminEmail)) {
            return { success: false, message: 'Email already registered' };
        }
        
        // Create new admin
        const newAdmin = {
            AdminID: `AD${String(Date.now()).slice(-4)}`,
            AdminName: data.AdminName,
            AdminSurname: data.AdminSurname,
            AdminEmail: data.AdminEmail,
            AdminContactNumber: data.AdminContactNumber,
            AdminUsername: data.AdminUsername,
            AdminRole: data.AdminRole || 'Admin',
            PasswordHash: DB.hashPassword(data.password)
        };
        
        DB.add('Administrator', newAdmin);
        
        // Auto-login after registration
        return this.login(data.AdminEmail, data.password, 'admin');
    },

    // Register new tutor application
    registerTutorApplication(data) {
        const applications = DB.getAll('Application');
        
        const newApplication = {
            ApplicationID: `APP${String(Date.now()).padStart(6, '0')}`,
            ApplicationDate: new Date().toISOString().split('T')[0],
            AcademicYear: 2026,
            ModuleMark: data.ModuleMark || 0,
            ApplicationStatus: 'Pending',
            ApplicationComments: data.ApplicationComments || '',
            AdminID: 1,
            TutorID: data.TutorID || null,
            ApplicantName: data.ApplicantName || '',
            ApplicantEmail: data.ApplicantEmail || ''
        };
        
        DB.add('Application', newApplication);
        
        return { success: true, message: 'Application submitted successfully' };
    },

    // Logout user
    logout() {
        localStorage.removeItem('user_session');
        window.location.href = '../index.html';
    },

    // Get current user session
    getCurrentUser() {
        const session = localStorage.getItem('user_session');
        return session ? JSON.parse(session) : null;
    },

    // Check if user is authenticated
    isAuthenticated() {
        return this.getCurrentUser() !== null;
    },

    // Check user role
    hasRole(role) {
        const user = this.getCurrentUser();
        return user && user.role === role;
    },

    // Redirect based on role
    redirectBasedOnRole() {
        const user = this.getCurrentUser();
        if (!user) {
            window.location.href = 'login.html';
            return;
        }
        
        switch (user.role) {
            case 'admin':
                window.location.href = 'admin/dashboard.html';
                break;
            case 'student':
                window.location.href = 'student/dashboard.html';
                break;
            case 'tutor':
                window.location.href = 'tutor/dashboard.html';
                break;
            default:
                window.location.href = 'login.html';
        }
    },

    // Protect routes
    requireAuth(role = null) {
        if (!this.isAuthenticated()) {
            window.location.href = 'login.html';
            return false;
        }
        
        if (role && !this.hasRole(role)) {
            this.redirectBasedOnRole();
            return false;
        }
        
        return true;
    },

    // Update user profile
    updateProfile(updates) {
        const user = this.getCurrentUser();
        if (!user) return { success: false, message: 'Not authenticated' };
        
        const table = user.role === 'admin' ? 'Administrator' : user.role === 'tutor' ? 'Tutor' : 'Student';
        const idField = user.role === 'admin' ? 'AdminID' : user.role === 'tutor' ? 'TutorID' : 'StudentID';
        
        const updated = DB.update(table, user.id, idField, updates);
        
        if (updated) {
            // Update session
            user.data = { ...user.data, ...updates };
            const nameField = user.role === 'admin' ? 'AdminName' : user.role === 'tutor' ? 'TutorName' : 'StudentName';
            const surnameField = user.role === 'admin' ? 'AdminSurname' : user.role === 'tutor' ? 'TutorSurname' : 'StudentSurname';
            user.name = `${updated[nameField] || user.data[nameField]} ${updated[surnameField] || user.data[surnameField]}`;
            localStorage.setItem('user_session', JSON.stringify(user));
            return { success: true, user };
        }
        
        return { success: false, message: 'Failed to update profile' };
    },

    // Change password
    changePassword(currentPassword, newPassword) {
        const user = this.getCurrentUser();
        if (!user) return { success: false, message: 'Not authenticated' };
        
        // Verify current password
        if (user.data.PasswordHash !== DB.hashPassword(currentPassword)) {
            return { success: false, message: 'Current password is incorrect' };
        }
        
        // Update password
        return this.updateProfile({ PasswordHash: DB.hashPassword(newPassword) });
    }
};
