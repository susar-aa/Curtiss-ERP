const express = require('express');
const { getCustomers, createCustomer, updateCustomer } = require('./crm.controller');
const { protect } = require('../../middlewares/auth.middleware');

const router = express.Router();

router.use(protect); // All CRM routes require login

router.route('/customers')
    .get(getCustomers)
    .post(createCustomer);

router.route('/customers/:id')
    .put(updateCustomer);

module.exports = router;