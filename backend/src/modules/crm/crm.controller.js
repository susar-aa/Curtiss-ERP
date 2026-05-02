const pool = require('../../config/db');

// @desc    Get all customers (Leads & Active)
// @route   GET /api/crm/customers
// @access  Private
exports.getCustomers = async (req, res, next) => {
    try {
        const [rows] = await pool.query('SELECT * FROM Customers ORDER BY created_at DESC');
        res.status(200).json({ status: 'success', results: rows.length, data: { customers: rows } });
    } catch (error) {
        next(error);
    }
};

// @desc    Create new customer/lead
// @route   POST /api/crm/customers
// @access  Private (Admin, Manager, Employee)
exports.createCustomer = async (req, res, next) => {
    try {
        const { name, email, phone, company, status } = req.body;

        if (!name) {
            return res.status(400).json({ status: 'error', message: 'Customer name is required' });
        }

        const [result] = await pool.query(
            'INSERT INTO Customers (name, email, phone, company, status) VALUES (?, ?, ?, ?, ?)',
            [name, email || null, phone || null, company || null, status || 'Lead']
        );

        res.status(201).json({
            status: 'success',
            data: { customer: { id: result.insertId, name, email, phone, company, status: status || 'Lead' } }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Update customer details (e.g., move Lead to Active)
// @route   PUT /api/crm/customers/:id
// @access  Private
exports.updateCustomer = async (req, res, next) => {
    try {
        const { name, email, phone, company, status } = req.body;
        const customerId = req.params.id;

        await pool.query(
            'UPDATE Customers SET name = ?, email = ?, phone = ?, company = ?, status = ? WHERE id = ?',
            [name, email, phone, company, status, customerId]
        );

        res.status(200).json({ status: 'success', message: 'Customer updated successfully' });
    } catch (error) {
        next(error);
    }
};