const pool = require('../../config/db');

// @desc    Handle WooCommerce Product Webhooks (Created/Updated)
// @route   POST /api/woocommerce/webhook/product
// @access  Public (WooCommerce Server calls this)
exports.handleProductWebhook = async (req, res, next) => {
    try {
        // WooCommerce sends the product data in the request body
        const { id: woocommerce_id, name, regular_price, stock_quantity } = req.body;

        if (!woocommerce_id || !name) {
            return res.status(400).json({ status: 'error', message: 'Invalid payload from WooCommerce' });
        }

        // Check if the product already exists in our ERP
        const [existing] = await pool.query('SELECT id FROM Products WHERE woocommerce_id = ?', [woocommerce_id]);

        const price = regular_price ? parseFloat(regular_price) : 0.00;
        const stock = stock_quantity ? parseInt(stock_quantity) : 0;

        if (existing.length > 0) {
            // Update existing product
            await pool.query(
                'UPDATE Products SET name = ?, price = ?, stock = ? WHERE woocommerce_id = ?',
                [name, price, stock, woocommerce_id]
            );
            console.log(`[WooCommerce Sync] Product Updated: ${name}`);
        } else {
            // Insert new product
            await pool.query(
                'INSERT INTO Products (woocommerce_id, name, price, stock) VALUES (?, ?, ?, ?)',
                [woocommerce_id, name, price, stock]
            );
            console.log(`[WooCommerce Sync] Product Created: ${name}`);
        }

        // Always respond with 200 OK so WooCommerce knows the webhook was received successfully
        res.status(200).send('Webhook received');
    } catch (error) {
        console.error('[WooCommerce Product Webhook Error]', error);
        res.status(500).send('Webhook processing failed');
    }
};

// @desc    Handle WooCommerce Order Webhooks (Created)
// @route   POST /api/woocommerce/webhook/order
// @access  Public
exports.handleOrderWebhook = async (req, res, next) => {
    try {
        const { id: woocommerce_order_id, billing, total, status, line_items } = req.body;

        const customer_name = billing ? `${billing.first_name} ${billing.last_name}` : 'Unknown Customer';

        // 1. Create or Update the order in the ERP
        await pool.query(
            `INSERT INTO Orders (customer_name, total, status, woocommerce_order_id) 
             VALUES (?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE status = ?, total = ?`,
            [customer_name, total, status, woocommerce_order_id, status, total]
        );

        // 2. Deduct inventory automatically based on the order line items
        if (line_items && line_items.length > 0) {
            for (const item of line_items) {
                if (item.product_id) {
                    await pool.query(
                        'UPDATE Products SET stock = GREATEST(stock - ?, 0) WHERE woocommerce_id = ?',
                        [item.quantity, item.product_id]
                    );
                }
            }
        }

        console.log(`[WooCommerce Sync] Order Processed: #${woocommerce_order_id}`);
        res.status(200).send('Webhook received');
    } catch (error) {
        console.error('[WooCommerce Order Webhook Error]', error);
        res.status(500).send('Webhook processing failed');
    }
};