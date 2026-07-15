<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — CREATE STOCK AUDIT
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-separator:    rgba(60,60,67,0.12);
    --c-blue:         #007aff;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;
    
    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', sans-serif;
    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --r-md: 14px;
    --r-sm: 10px;
    --r-pill: 999px;
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
}

.create-wrap {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 24px 80px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.page-header {
    margin-bottom: 28px;
}
.eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 4px;
}
.title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.flash-msg {
    padding: 14px 20px;
    border-radius: var(--r-md);
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}
.flash-msg-error { background: var(--c-red-light); color: #bd2130; border: 0.5px solid rgba(255,59,48,0.3); }

.card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-md);
    padding: 28px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-field-full {
    grid-column: span 2;
}
.label {
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
}
.label-required::after {
    content: " *";
    color: var(--c-red);
}
.select, .input, .textarea {
    background: rgba(120,120,128,0.12);
    border: 0.5px solid transparent;
    border-radius: var(--r-sm);
    padding: 12px 16px;
    font-size: 14px;
    font-family: var(--f-system);
    color: var(--t-primary);
    outline: none;
    transition: all var(--dur-fast);
}
.select:focus, .input:focus, .textarea:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
}
.textarea {
    resize: vertical;
    min-height: 100px;
}

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
}
.btn-cancel {
    background: rgba(120,120,128,0.12);
    border: none;
    color: var(--t-primary);
    padding: 12px 24px;
    border-radius: var(--r-pill);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.btn-submit {
    background: var(--c-blue);
    border: none;
    color: #fff;
    padding: 12px 28px;
    border-radius: var(--r-pill);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-submit:hover {
    background: #0066cc;
}
</style>

<div class="create-wrap">
    <!-- Header -->
    <div class="page-header">
        <div class="eyebrow">Operations</div>
        <div class="title">New Stock Audit</div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-msg flash-msg-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card">
        <form method="POST" action="<?= APP_URL ?>/stockaudit/store">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-grid">
                <!-- Warehouse -->
                <div class="form-field form-field-full">
                    <label class="label label-required">Select Warehouse</label>
                    <select name="warehouse_id" class="select" required>
                        <option value="">-- Choose Warehouse --</option>
                        <?php foreach ($data['warehouses'] as $wh): ?>
                            <option value="<?= $wh->id; ?>"><?= htmlspecialchars($wh->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size: 11px; color: var(--t-secondary); margin-top: 4px;">
                        The audit sheet will be populated using the current stock levels of this warehouse.
                    </div>
                </div>

                <!-- Category -->
                <div class="form-field">
                    <label class="label">Filter Category (Optional)</label>
                    <select name="category_id" class="select">
                        <option value="">All Categories</option>
                        <?php foreach ($data['categories'] as $cat): ?>
                            <option value="<?= $cat->id; ?>"><?= htmlspecialchars($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Brand -->
                <div class="form-field">
                    <label class="label">Filter Brand (Optional)</label>
                    <select name="brand" class="select">
                        <option value="">All Brands</option>
                        <?php foreach ($data['brands'] as $brandRow): ?>
                            <option value="<?= htmlspecialchars($brandRow->brand); ?>"><?= htmlspecialchars($brandRow->brand); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Supplier -->
                <div class="form-field form-field-full">
                    <label class="label">Filter Supplier / Vendor (Optional)</label>
                    <select name="supplier_id" class="select">
                        <option value="">All Suppliers</option>
                        <?php foreach ($data['suppliers'] as $sup): ?>
                            <option value="<?= $sup->id; ?>"><?= htmlspecialchars($sup->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Remarks -->
                <div class="form-field form-field-full">
                    <label class="label">Audit Remarks / Instructions</label>
                    <textarea name="remarks" class="textarea" placeholder="e.g. Annual Year-end stock count for Main Warehouse..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions">
                <a href="<?= APP_URL ?>/stockaudit" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-play"></i> Initialize Audit Count
                </button>
            </div>
        </form>
    </div>
</div>
