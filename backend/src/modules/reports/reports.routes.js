const express = require('express');
const { 
    getDashboardStats, 
    getSalesReport, 
    getInventoryReport 
} = require('./reports.controller');
const { protect, authorize } = require('../../middlewares/auth.middleware');

const router = express.Router();

// All reporting routes require authentication
router.use(protect);

router.route('/dashboard')
    .get(authorize('Admin', 'Manager'), getDashboardStats);

router.route('/sales')
    .get(authorize('Admin', 'Manager', 'Accountant'), getSalesReport);

router.route('/inventory')
    .get(authorize('Admin', 'Manager'), getInventoryReport);

module.exports = router;