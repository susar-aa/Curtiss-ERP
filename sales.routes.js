const express = require('express');
const { 
    getOrders, 
    getOrder, 
    createOrder, 
    updateOrder,
    deleteOrder
} = require('./sales.controller');
const { protect, authorize } = require('../../middlewares/auth.middleware');

const router = express.Router();

// All routes below this middleware are protected (require valid JWT)
router.use(protect);

router.route('/orders')
    .get(getOrders) // Any logged-in user can view orders
    .post(authorize('Admin', 'Manager', 'Accountant'), createOrder);

router.route('/orders/:id')
    .get(getOrder)
    .put(authorize('Admin', 'Manager', 'Accountant'), updateOrder)
    .delete(authorize('Admin'), deleteOrder); // Only Admins can delete orders

module.exports = router;