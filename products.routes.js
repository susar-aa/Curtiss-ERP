const express = require('express');
const { 
    getProducts, 
    getProduct, 
    createProduct, 
    updateProduct, 
    deleteProduct 
} = require('./products.controller');
const { protect, authorize } = require('../../middlewares/auth.middleware');

const router = express.Router();

// All routes below this middleware are protected (require valid JWT)
router.use(protect);

router.route('/')
    .get(getProducts) // Any logged in user can view products
    .post(authorize('Admin', 'Manager'), createProduct); // Only Admins and Managers can create

router.route('/:id')
    .get(getProduct)
    .put(authorize('Admin', 'Manager'), updateProduct)
    .delete(authorize('Admin'), deleteProduct); // Only Admins can delete

module.exports = router;