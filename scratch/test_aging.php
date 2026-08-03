<?php
$db = new PDO('mysql:host=localhost;dbname=curtiss_erp', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT sub.id, sub.customer_name, sub.customer_id, sub.territory, sub.customer_type,
         GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)) as `current`,
         GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)))) as `thirty`,
         GREATEST(0, LEAST(sub.S_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)) - GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)))))) as `sixty`,
         (sub.TOTAL_BAL 
          - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL))
          - GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL))))
          - GREATEST(0, LEAST(sub.S_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)) - GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL))))))
         ) as `ninety`,
         sub.TOTAL_BAL as total
FROM (
    SELECT c.id, c.name as customer_name, c.id as customer_id, c.territory, c.customer_type,
           COALESCE(aging.current_bal, 0) as C_inv,
           COALESCE(aging.thirty_bal, 0) as T_inv,
           COALESCE(aging.sixty_bal, 0) as S_inv,
           (COALESCE(aging.ninety_bal, 0) + COALESCE(c.opening_balance, 0)) as N_inv,
           (COALESCE(c.opening_balance, 0) + COALESCE(inv.total_billed, 0) - COALESCE(pmt.total_paid, 0) - COALESCE(cn.total_credited, 0)) as TOTAL_BAL
    FROM customers c
    LEFT JOIN (
        SELECT customer_id, 
               SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_billed
        FROM invoices 
        WHERE status != 'Voided'
        GROUP BY customer_id
    ) inv ON c.id = inv.customer_id
    LEFT JOIN (
        SELECT customer_id, SUM(amount) as total_paid
        FROM customer_payments 
        WHERE status = 'Active'
        GROUP BY customer_id
    ) pmt ON c.id = pmt.customer_id
    LEFT JOIN (
        SELECT customer_id, SUM(total_amount) as total_credited
        FROM credit_notes
        GROUP BY customer_id
    ) cn ON c.id = cn.customer_id
    LEFT JOIN (
        SELECT customer_id,
               SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) <= 30 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as current_bal,
               SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) > 30 AND DATEDIFF(NOW(), invoice_date) <= 60 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as thirty_bal,
               SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) > 60 AND DATEDIFF(NOW(), invoice_date) <= 90 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as sixty_bal,
               SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) > 90 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as ninety_bal
        FROM invoices
        WHERE status != 'Paid' AND status != 'Voided'
        GROUP BY customer_id
    ) aging ON c.id = aging.customer_id
    WHERE 1=1 
) sub
WHERE sub.TOTAL_BAL > 0.01 OR sub.TOTAL_BAL < -0.01 LIMIT 5";

try {
    $stmt = $db->query($sql);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
