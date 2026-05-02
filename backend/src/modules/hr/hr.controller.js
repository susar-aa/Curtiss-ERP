const pool = require('../../config/db');

// @desc    Get all employees
// @route   GET /api/hr/employees
// @access  Private (Admin, Manager)
exports.getEmployees = async (req, res, next) => {
    try {
        const query = `
            SELECT e.id, e.department, e.position, e.base_salary, e.hire_date, u.name, u.email 
            FROM Employees e
            JOIN Users u ON e.user_id = u.id
            ORDER BY e.created_at DESC
        `;
        const [rows] = await pool.query(query);
        res.status(200).json({ status: 'success', results: rows.length, data: { employees: rows } });
    } catch (error) {
        next(error);
    }
};

// @desc    Mark attendance for an employee
// @route   POST /api/hr/attendance
// @access  Private (Admin, Manager)
exports.markAttendance = async (req, res, next) => {
    try {
        const { employee_id, date, status } = req.body;

        if (!employee_id || !date) {
            return res.status(400).json({ status: 'error', message: 'Employee ID and date are required' });
        }

        await pool.query(
            'INSERT INTO Attendance (employee_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?',
            [employee_id, date, status || 'Present', status || 'Present']
        );

        res.status(201).json({ status: 'success', message: 'Attendance recorded successfully' });
    } catch (error) {
        next(error);
    }
};

// @desc    Process payroll (Basic implementation)
// @route   POST /api/hr/payroll
// @access  Private (Admin)
exports.processPayroll = async (req, res, next) => {
    try {
        const { employee_id, amount, payment_date } = req.body;

        if (!employee_id || !amount || !payment_date) {
            return res.status(400).json({ status: 'error', message: 'Missing payroll information' });
        }

        await pool.query(
            'INSERT INTO Payroll (employee_id, amount, payment_date, status) VALUES (?, ?, ?, ?)',
            [employee_id, amount, payment_date, 'Processed']
        );

        res.status(201).json({ status: 'success', message: 'Payroll processed successfully' });
    } catch (error) {
        next(error);
    }
};