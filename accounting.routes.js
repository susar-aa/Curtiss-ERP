const express = require('express');
const { 
    getAccounts, 
    createAccount, 
    createJournalEntry, 
    getTransactions,
    getProfitAndLoss
} = require('./accounting.controller');
const { protect, authorize } = require('../../middlewares/auth.middleware');

const router = express.Router();

router.use(protect);

// Chart of Accounts routes
router.route('/accounts')
    .get(authorize('Admin', 'Accountant', 'Manager'), getAccounts)
    .post(authorize('Admin', 'Accountant'), createAccount);

// Journal Entries / General Ledger routes
router.route('/transactions')
    .get(authorize('Admin', 'Accountant', 'Manager'), getTransactions)
    .post(authorize('Admin', 'Accountant'), createJournalEntry);

// Reports routes
router.route('/reports/pl')
    .get(authorize('Admin', 'Accountant', 'Manager'), getProfitAndLoss);

module.exports = router;