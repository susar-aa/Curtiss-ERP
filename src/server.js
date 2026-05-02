const app = require('./app');
const dotenv = require('dotenv');
require('./config/db'); // Initialize database connection

// Load environment variables
dotenv.config();

const PORT = process.env.PORT || 5000;

// Start the server
const server = app.listen(PORT, () => {
    console.log(`🚀 Curtiss ERP Backend is running on port ${PORT}`);
    console.log(`Environment: ${process.env.NODE_ENV}`);
});

// Handle unhandled promise rejections (e.g., database failure after startup)
process.on('unhandledRejection', (err) => {
    console.error('UNHANDLED REJECTION! 💥 Shutting down...');
    console.error(err.name, err.message);
    server.close(() => {
        process.exit(1);
    });
});