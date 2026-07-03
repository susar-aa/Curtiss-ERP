-- Migration to support Service Bills in Curtiss ERP
ALTER TABLE goods_receipt_notes 
    ADD COLUMN due_date DATE NULL,
    ADD COLUMN service_period VARCHAR(100) NULL,
    ADD COLUMN amount DECIMAL(15,2) NULL,
    ADD COLUMN tax DECIMAL(15,2) NULL,
    ADD COLUMN total_amount DECIMAL(15,2) NULL,
    ADD COLUMN status ENUM('Unpaid', 'Partially Paid', 'Paid') DEFAULT 'Unpaid',
    ADD COLUMN attachment VARCHAR(255) NULL;
