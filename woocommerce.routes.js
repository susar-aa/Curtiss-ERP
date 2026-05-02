const express = require('express');
const { handleProductWebhook, handleOrderWebhook } = require('./woocommerce.controller');

const router = express.Router();

// Webhook endpoints 
// Notice we do NOT use the protect/JWT middleware here because 
// WooCommerce (an external server) will be making these POST requests.
// In a production environment, you would add a middleware here to verify a WooCommerce Webhook Secret.

router.post('/webhook/product', handleProductWebhook);
router.post('/webhook/order', handleOrderWebhook);

module.exports = router;