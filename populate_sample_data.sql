-- SQL Script to Populate Realistic Sample Business Data for Testing Mobile Sync
-- This will populate Categories, Products (Items), Territories (MCA Areas), Customers, Payment Terms, and Active Routes.

-- 1. Populate Item Categories
INSERT INTO `item_categories` (`id`, `name`, `description`, `status`) VALUES
(1, 'General', 'General category items', 'active'),
(2, 'Beverages', 'Soft drinks, energy drinks, and tea/coffee', 'active'),
(3, 'Snacks & Confectionery', 'Chocolates, biscuits, and chips', 'active'),
(4, 'Packaged Foods', 'Canned foods, grains, and spices', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`), `status` = VALUES(`status`);

-- 2. Populate Products (Items)
-- Note: items table uses price, wholesale_price, quantity_on_hand, item_code, status, category_id
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `price`, `wholesale_price`, `cost_price`, `quantity_on_hand`, `status`, `unit`, `brand`) VALUES
(1, 'BEV-001', 'Coca-Cola 500ml', 2, 150.00, 130.00, 100.00, 500, 'active', 'Bottle', 'Coca-Cola'),
(2, 'BEV-002', 'Sprite 500ml', 2, 150.00, 130.00, 100.00, 450, 'active', 'Bottle', 'Sprite'),
(3, 'SNA-001', 'Chocolate Biscuit 100g', 3, 120.00, 100.00, 80.00, 1000, 'active', 'Packet', 'Munchee'),
(4, 'SNA-002', 'Cheese Crackers 110g', 3, 140.00, 120.00, 95.00, 800, 'active', 'Packet', 'Maliban'),
(5, 'FOD-001', 'Basmati Rice 1kg', 4, 380.00, 350.00, 310.00, 200, 'active', 'Pack', 'Laila'),
(6, 'GEN-001', 'Multi-purpose Soap', 1, 90.00, 78.00, 60.00, 1500, 'active', 'Bar', 'Sunlight')
ON DUPLICATE KEY UPDATE 
    `item_code` = VALUES(`item_code`), 
    `name` = VALUES(`name`), 
    `category_id` = VALUES(`category_id`), 
    `price` = VALUES(`price`), 
    `wholesale_price` = VALUES(`wholesale_price`), 
    `cost_price` = VALUES(`cost_price`), 
    `quantity_on_hand` = VALUES(`quantity_on_hand`), 
    `status` = VALUES(`status`),
    `unit` = VALUES(`unit`),
    `brand` = VALUES(`brand`);

-- 3. Populate Territories (MCA Areas)
INSERT INTO `mca_areas` (`id`, `name`, `status`) VALUES
(1, 'Colombo North', 'active'),
(2, 'Colombo South', 'active'),
(3, 'Gampaha Route 01', 'active'),
(4, 'Kandy Main Town', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `status` = VALUES(`status`);

-- 4. Populate Payment Terms
INSERT INTO `payment_terms` (`id`, `name`, `days_due`, `is_active`) VALUES
(1, 'Cash on Delivery', 0, 1),
(2, '7 Days Credit', 7, 1),
(3, '14 Days Credit', 14, 1),
(4, '30 Days Credit', 30, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `days_due` = VALUES(`days_due`), `is_active` = VALUES(`is_active`);

-- 5. Populate Customers
-- Note: customers table uses name, phone, whatsapp, address, territory, latitude, longitude, mca_id, credit_limit, customer_type, status
INSERT INTO `customers` (`id`, `name`, `phone`, `whatsapp`, `address`, `territory`, `latitude`, `longitude`, `mca_id`, `credit_limit`, `customer_type`, `status`) VALUES
(1, 'Super City Supermarket', '0112345678', '0771234567', '123 Galle Road, Colombo 03', 'Colombo South', 6.9142, 79.8524, 2, 150000.00, 'Wholesale', 'active'),
(2, 'Family Grocers Gampaha', '0332244556', '0779988776', '45 Kandy Road, Gampaha', 'Gampaha Route 01', 7.0897, 79.9925, 3, 75000.00, 'Wholesale', 'active'),
(3, 'Jayasekara Stores', '0812233445', '', '88 Temple Road, Kandy', 'Kandy Main Town', 7.2906, 80.6337, 4, 50000.00, 'Standard', 'active'),
(4, 'Corner Shop Colombo 15', '0119988776', '', '12 Aluthmawatha Road, Colombo 15', 'Colombo North', 6.9602, 79.8667, 1, 20000.00, 'Standard', 'active')
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`), 
    `phone` = VALUES(`phone`), 
    `whatsapp` = VALUES(`whatsapp`), 
    `address` = VALUES(`address`), 
    `territory` = VALUES(`territory`), 
    `latitude` = VALUES(`latitude`), 
    `longitude` = VALUES(`longitude`), 
    `mca_id` = VALUES(`mca_id`), 
    `credit_limit` = VALUES(`credit_limit`), 
    `customer_type` = VALUES(`customer_type`), 
    `status` = VALUES(`status`);

-- 6. Populate Sample Invoices (Outstanding Credit Invoices) for Customers
-- Customer 1 outstanding credit invoices
INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `invoice_date`, `due_date`, `payment_term_id`, `total_amount`, `global_discount_val`, `global_discount_type`, `tax_amount`, `status`) VALUES
(1, 'INV-2026-0001', 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 2, 45000.00, 0.00, 'Rs', 0.00, 'Unpaid'),
(2, 'INV-2026-0002', 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2, 28000.00, 5.00, '%', 0.00, 'Unpaid')
ON DUPLICATE KEY UPDATE 
    `invoice_number` = VALUES(`invoice_number`), 
    `customer_id` = VALUES(`customer_id`), 
    `invoice_date` = VALUES(`invoice_date`), 
    `due_date` = VALUES(`due_date`), 
    `payment_term_id` = VALUES(`payment_term_id`), 
    `total_amount` = VALUES(`total_amount`), 
    `global_discount_val` = VALUES(`global_discount_val`), 
    `global_discount_type` = VALUES(`global_discount_type`), 
    `tax_amount` = VALUES(`tax_amount`), 
    `status` = VALUES(`status`);
