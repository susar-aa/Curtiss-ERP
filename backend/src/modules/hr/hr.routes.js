const express = require('express');
const { getEmployees, markAttendance, processPayroll } = require('./hr.controller');
const { protect, authorize } = require('../../middlewares/auth.middleware');

const router = express.Router();

router.use(protect);

router.route('/employees')
    .get(authorize('Admin', 'Manager'), getEmployees);

router.route('/attendance')
    .post(authorize('Admin', 'Manager'), markAttendance);

router.route('/payroll')
    .post(authorize('Admin'), processPayroll); // Strict access for Payroll

module.exports = router;