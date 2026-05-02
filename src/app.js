const express = require('express');
const cors = require('cors');

// Initialize the Express application
const app = express();

// Middlewares
app.use(cors()); // Enable Cross-Origin Resource Sharing for the frontend
app.use(express.json()); // Parse incoming JSON payloads
app.use(express.urlencoded({ extended: true })); // Parse URL-encoded bodies

// Basic Health Check Route
app.get('/api/health', (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'Curtiss ERP API is running smoothly.',
        timestamp: new Date().toISOString()
    });
});

// Import and Mount Routes
const authRoutes = require('./modules/auth/auth.routes');
const productRoutes = require('./modules/inventory/products.routes');
const woocommerceRoutes = require('./modules/woocommerce/woocommerce.routes');
const salesRoutes = require('./modules/sales/sales.routes');
const accountingRoutes = require('./modules/accounting/accounting.routes');

app.use('/api/auth', authRoutes);
app.use('/api/products', productRoutes);
app.use('/api/woocommerce', woocommerceRoutes);
app.use('/api/sales', salesRoutes);
app.use('/api/accounting', accountingRoutes);

// Global Error Handler Middleware
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({
        status: 'error',
        message: err.message || 'Internal Server Error'
    });
});

module.exports = app;