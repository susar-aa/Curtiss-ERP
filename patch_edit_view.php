<?php
$f = 'app/Views/estimates/edit.php';
$c = file_get_contents($f);

// 1. Form action
$c = str_replace(
    'action="<?= APP_URL ?>/estimate/create"',
    'action="<?= APP_URL ?>/estimate/edit/<?= $data[\'estimate\']->id ?>"',
    $c
);

// 2. Title
$c = str_replace(
    '<div class="qb-title">Create New Estimate</div>',
    '<div class="qb-title">Edit Estimate <?= htmlspecialchars($data[\'estimate\']->estimate_number) ?></div>',
    $c
);
$c = str_replace(
    '<div class="qb-subtitle">Draft a new quotation for a customer</div>',
    '<div class="qb-subtitle">Modify quotation details</div>',
    $c
);

// 3. Estimate Number, Date, Expiry
$c = str_replace(
    'value="<?= htmlspecialchars($data[\'estimate_number\']) ?>" required',
    'value="<?= htmlspecialchars($data[\'estimate\']->estimate_number) ?>" required readonly',
    $c
);
$c = str_replace(
    'name="estimate_date" class="qb-input" value="<?= date(\'Y-m-d\') ?>" required',
    'name="estimate_date" class="qb-input" value="<?= htmlspecialchars($data[\'estimate\']->estimate_date) ?>" required',
    $c
);
$c = str_replace(
    'name="expiry_date" class="qb-input" value="<?= date(\'Y-m-d\', strtotime(\'+30 days\')) ?>" required',
    'name="expiry_date" class="qb-input" value="<?= htmlspecialchars($data[\'estimate\']->expiry_date) ?>" required',
    $c
);

// 4. Inject existing items in JS
$jsInjection = <<<'EOD'
    // --- LOAD EXISTING DATA ---
    const existingCustId = "<?= $data['estimate']->customer_id ?>";
    if (existingCustId) {
        const cMatch = customerList.find(c => c.id == existingCustId);
        if (cMatch) selectCustomer(cMatch);
    }

    const existingItems = <?= json_encode($data['estimate_items'] ?? []) ?>;
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addLineItem(item.description, parseFloat(item.unit_price), parseFloat(item.quantity));
        });
    }
EOD;

$c = str_replace(
    '// ═══ 1. REALTIME CUSTOMER SEARCH & SELECTION ═══',
    $jsInjection . "\n\n    // ═══ 1. REALTIME CUSTOMER SEARCH & SELECTION ═══",
    $c
);

file_put_contents($f, $c);
echo "Patched edit.php successfully.";
