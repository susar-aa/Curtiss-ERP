const pool = require('../../config/db');

// @desc    Get all orders/invoices
// @route   GET /api/sales/orders
// @access  Private
exports.getOrders = async (req, res, next) => {
    try {
        const [rows] = await pool.query('SELECT * FROM Orders ORDER BY created_at DESC');
        
        res.status(200).json({
            status: 'success',
            results: rows.length,
            data: { orders: rows }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Get single order/invoice
// @route   GET /api/sales/orders/:id
// @access  Private
exports.getOrder = async (req, res, next) => {
    try {
        const [rows] = await pool.query('SELECT * FROM Orders WHERE id = ?', [req.params.id]);
        
        if (rows.length === 0) {
            return res.status(404).json({ status: 'error', message: 'Order not found' });
        }

        res.status(200).json({
            status: 'success',
            data: { order: rows[0] }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Create new manual order/invoice
// @route   POST /api/sales/orders
// @access  Private (Admin, Manager, Accountant)
exports.createOrder = async (req, res, next) => {
    try {
        const { customer_name, total, status } = req.body;

        if (!customer_name || total === undefined) {
            return res.status(400).json({ status: 'error', message: 'Customer name and total are required' });
        }

        const [result] = await pool.query(
            'INSERT INTO Orders (customer_name, total, status) VALUES (?, ?, ?)',
            [customer_name, total, status || 'Pending']
        );

        res.status(201).json({
            status: 'success',
            data: {
                order: { id: result.insertId, customer_name, total, status: status || 'Pending' }
            }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Update order status
// @route   PUT /api/sales/orders/:id
// @access  Private (Admin, Manager, Accountant)
exports.updateOrder = async (req, res, next) => {
    try {
        const { status } = req.body;
        const orderId = req.params.id;

        // Validate status enum
        const validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
        if (status && !validStatuses.includes(status)) {
            return res.status(400).json({ status: 'error', message: 'Invalid order status' });
        }

        // Ensure order exists
        const [existing] = await pool.query('SELECT id FROM Orders WHERE id = ?', [orderId]);
        if (existing.length === 0) {
            return res.status(404).json({ status: 'error', message: 'Order not found' });
        }

        await pool.query(
            'UPDATE Orders SET status = ? WHERE id = ?',
            [status, orderId]
        );

        res.status(200).json({
            status: 'success',
            message: 'Order updated successfully'
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Delete order/invoice
// @route   DELETE /api/sales/orders/:id
// @access  Private (Admin)
exports.deleteOrder = async (req, res, next) => {
    try {
        const orderId = req.params.id;

        const [result] = await pool.query('DELETE FROM Orders WHERE id = ?', [orderId]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ status: 'error', message: 'Order not found' });
        }

        res.status(200).json({
            status: 'success',
            message: 'Order deleted successfully'
        });
    } catch (error) {
        next(error);
    }
};