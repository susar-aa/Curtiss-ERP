const jwt = require('jsonwebtoken');

// Protect routes - Verify JWT token
const protect = async (req, res, next) => {
    let token;

    // Check if Authorization header exists and starts with Bearer
    if (req.headers.authorization && req.headers.authorization.startsWith('Bearer')) {
        token = req.headers.authorization.split(' ')[1];
    }

    if (!token) {
        return res.status(401).json({ 
            status: 'error', 
            message: 'Not authorized to access this route. Please log in.' 
        });
    }

    try {
        // Verify token
        const decoded = jwt.verify(token, process.env.JWT_SECRET);

        // Attach user info (id, role) to the request object for use in downstream controllers
        req.user = decoded; 
        
        next();
    } catch (error) {
        return res.status(401).json({ 
            status: 'error', 
            message: 'Token failed or expired. Please log in again.' 
        });
    }
};

// Grant access to specific roles
const authorize = (...roles) => {
    return (req, res, next) => {
        if (!roles.includes(req.user.role)) {
            return res.status(403).json({ 
                status: 'error', 
                message: `User role '${req.user.role}' is not authorized to access this action.` 
            });
        }
        next();
    };
};

module.exports = { protect, authorize };