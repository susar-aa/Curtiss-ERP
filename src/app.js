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

// TODO: Import and Mount Routes (auth, accounting, inventory, etc.) will go here

// Global Error Handler Middleware
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({
        status: 'error',
        message: err.message || 'Internal Server Error'
    });
});

module.exports = app;