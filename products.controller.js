const pool = require('../../config/db');

// @desc    Get all products
// @route   GET /api/products
// @access  Private
exports.getProducts = async (req, res, next) => {
    try {
        const [rows] = await pool.query('SELECT * FROM Products ORDER BY created_at DESC');
        
        res.status(200).json({
            status: 'success',
            results: rows.length,
            data: { products: rows }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Get single product
// @route   GET /api/products/:id
// @access  Private
exports.getProduct = async (req, res, next) => {
    try {
        const [rows] = await pool.query('SELECT * FROM Products WHERE id = ?', [req.params.id]);
        
        if (rows.length === 0) {
            return res.status(404).json({ status: 'error', message: 'Product not found' });
        }

        res.status(200).json({
            status: 'success',
            data: { product: rows[0] }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Create new product
// @route   POST /api/products
// @access  Private (Admin, Manager)
exports.createProduct = async (req, res, next) => {
    try {
        const { name, price, stock, woocommerce_id } = req.body;

        if (!name || price === undefined) {
            return res.status(400).json({ status: 'error', message: 'Product name and price are required' });
        }

        const [result] = await pool.query(
            'INSERT INTO Products (name, price, stock, woocommerce_id) VALUES (?, ?, ?, ?)',
            [name, price, stock || 0, woocommerce_id || null]
        );

        res.status(201).json({
            status: 'success',
            data: {
                product: { id: result.insertId, name, price, stock, woocommerce_id }
            }
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Update product
// @route   PUT /api/products/:id
// @access  Private (Admin, Manager)
exports.updateProduct = async (req, res, next) => {
    try {
        const { name, price, stock, woocommerce_id } = req.body;
        const productId = req.params.id;

        // Ensure product exists
        const [existing] = await pool.query('SELECT id FROM Products WHERE id = ?', [productId]);
        if (existing.length === 0) {
            return res.status(404).json({ status: 'error', message: 'Product not found' });
        }

        await pool.query(
            'UPDATE Products SET name = ?, price = ?, stock = ?, woocommerce_id = ? WHERE id = ?',
            [name, price, stock, woocommerce_id, productId]
        );

        res.status(200).json({
            status: 'success',
            message: 'Product updated successfully'
        });
    } catch (error) {
        next(error);
    }
};

// @desc    Delete product
// @route   DELETE /api/products/:id
// @access  Private (Admin)
exports.deleteProduct = async (req, res, next) => {
    try {
        const productId = req.params.id;

        const [result] = await pool.query('DELETE FROM Products WHERE id = ?', [productId]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ status: 'error', message: 'Product not found' });
        }

        res.status(200).json({
            status: 'success',
            message: 'Product deleted successfully'
        });
    } catch (error) {
        next(error);
    }
};