<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   CUSTOMER RETURNS / CREDIT NOTE VIEW — DESIGN SYSTEM
   ============================================================ */
:root {
    --qb-bg:           #f8fafc;
    --qb-surface:      #ffffff;
    --qb-primary:      #c62828;
    --qb-primary-light:#ffebee;
    
    --qb-slate-50:     #f8fafc;
    --qb-slate-100:    #f1f5f9;
    --qb-slate-200:    #e2e8f0;
    --qb-slate-300:    #cbd5e1;
    --qb-slate-400:    #94a3b8;
    --qb-slate-500:    #64748b;
    --qb-slate-600:    #475569;
    --qb-slate-700:    #334155;
    --qb-slate-800:    #1e293b;
    --qb-slate-900:    #0f172a;

    --qb-font:         'Inter', sans-serif;
    --qb-mono:         ui-monospace, monospace;
    --qb-radius-lg:    12px;
    --qb-shadow-sm:    0 1px 2px 0 rgb(0 0 0 / 0.05);
}

body { background: var(--qb-bg); font-family: var(--qb-font); color: var(--qb-slate-800); margin:0; padding: 20px; }

.action-bar {
    max-width: 900px; margin: 0 auto 20px auto; display: flex; justify-content: space-between; align-items: center;
}
.btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-family: var(--qb-font);
}
.btn-primary { background: var(--qb-primary); color: #fff; }
.btn-primary:hover { background: #b71c1c; }
.btn-secondary { background: #fff; color: var(--qb-slate-700); border: 1px solid var(--qb-slate-200); box-shadow: var(--qb-shadow-sm); }
.btn-secondary:hover { background: var(--qb-slate-50); }

.doc-wrapper {
    max-width: 900px; margin: 0 auto; background: var(--qb-surface); border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius-lg); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); padding: 40px;
}
.doc-header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid var(--qb-primary); padding-bottom: 20px; }
.doc-brand h1 { margin: 0; color: var(--qb-primary); font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
.doc-brand p { margin: 4px 0 0 0; color: var(--qb-slate-500); font-size: 13px; font-weight: 500; }
.doc-meta { text-align: right; }
.doc-title { font-size: 24px; font-weight: 800; color: var(--qb-slate-900); margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.05em; }
.meta-grid { display: grid; grid-template-columns: auto auto; gap: 6px 20px; font-size: 13px; text-align: right; }
.meta-lbl { color: var(--qb-slate-500); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.02em; }
.meta-val { color: var(--qb-slate-900); font-weight: 700; font-family: var(--qb-mono); }

.party-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
.party-box { padding: 16px; background: var(--qb-slate-50); border-radius: 8px; border: 1px solid var(--qb-slate-100); }
.party-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--qb-slate-500); letter-spacing: 0.05em; margin-bottom: 8px; }
.party-name { font-size: 16px; font-weight: 700; color: var(--qb-slate-900); margin-bottom: 6px; }
.party-details { font-size: 13px; color: var(--qb-slate-600); line-height: 1.6; }

.item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
.item-table th { background: var(--qb-slate-100); color: var(--qb-slate-700); font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 12px 16px; border-bottom: 2px solid var(--qb-slate-200); text-align: left; }
.item-table td { padding: 12px 16px; border-bottom: 1px solid var(--qb-slate-100); font-size: 13px; color: var(--qb-slate-800); }
.item-desc { font-weight: 600; color: var(--qb-slate-900); }
.item-meta { font-size: 11.5px; color: var(--qb-slate-500); margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; background: var(--qb-slate-50); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--qb-slate-200); }
.col-num { font-family: var(--qb-mono); font-weight: 600; text-align: right; }

.totals-box { width: 300px; margin-left: auto; border: 1px solid var(--qb-slate-200); border-radius: 8px; background: var(--qb-slate-50); overflow: hidden; }
.tot-row { display: flex; justify-content: space-between; padding: 12px 16px; font-size: 13px; border-bottom: 1px solid var(--qb-slate-200); }
.tot-row:last-child { border-bottom: none; }
.tot-lbl { font-weight: 600; color: var(--qb-slate-600); }
.tot-val { font-family: var(--qb-mono); font-weight: 700; color: var(--qb-slate-900); }
.tot-grand { background: var(--qb-primary-light); }
.tot-grand .tot-lbl { color: var(--qb-primary); font-size: 14px; font-weight: 800; }
.tot-grand .tot-val { color: var(--qb-primary-dark); font-size: 18px; }

.notes-box { margin-top: 40px; padding: 16px; border-left: 4px solid var(--qb-primary); background: var(--qb-slate-50); font-size: 13px; color: var(--qb-slate-700); line-height: 1.5; }
.notes-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--qb-slate-500); margin-bottom: 6px; }

@media print {
    body { background: #fff; padding: 0; }
    .action-bar { display: none; }
    .doc-wrapper { box-shadow: none; border: none; padding: 0; }
}
</style>

<div class="action-bar">
    <a href="<?= APP_URL ?>/creditnote" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fa-solid fa-print"></i> Print Return Note
    </button>
</div>

<div class="doc-wrapper">
    <div class="doc-header">
        <div class="doc-brand">
            <h1><?= htmlspecialchars($data['company']->company_name ?? APP_NAME) ?></h1>
            <p>Customer Return & Credit Note</p>
        </div>
        <div class="doc-meta">
            <h2 class="doc-title">Credit Note</h2>
            <div class="meta-grid">
                <div class="meta-lbl">Doc Ref:</div>
                <div class="meta-val"><?= htmlspecialchars($data['credit_note']->credit_note_number) ?></div>
                <div class="meta-lbl">Date:</div>
                <div class="meta-val"><?= date('d M Y', strtotime($data['credit_note']->note_date)) ?></div>
                <div class="meta-lbl">Status:</div>
                <div class="meta-val" style="color:#2e7d32;"><?= htmlspecialchars($data['credit_note']->status) ?></div>
            </div>
        </div>
    </div>

    <div class="party-grid">
        <div class="party-box">
            <div class="party-title"><i class="fa-solid fa-building-circle-arrow-right"></i> Issued By</div>
            <div class="party-name"><?= htmlspecialchars($data['company']->company_name ?? APP_NAME) ?></div>
            <div class="party-details">
                <?= htmlspecialchars($data['company']->address ?? 'No. 100, Galle Road') ?><br>
                Colombo, Sri Lanka<br>
                Phone: <?= htmlspecialchars($data['company']->phone ?? '+94 11 234 5678') ?><br>
                Email: <?= htmlspecialchars($data['company']->email ?? 'billing@curtisserp.com') ?>
            </div>
        </div>
        <div class="party-box">
            <div class="party-title"><i class="fa-solid fa-user"></i> Credit To (Customer)</div>
            <div class="party-name"><?= htmlspecialchars($data['credit_note']->customer_name) ?></div>
            <div class="party-details">
                <?php if(!empty($data['credit_note']->phone)): ?><div><i class="fa-solid fa-phone" style="width:16px;"></i> <?= htmlspecialchars($data['credit_note']->phone) ?></div><?php endif; ?>
                <?php if(!empty($data['credit_note']->email)): ?><div><i class="fa-solid fa-envelope" style="width:16px;"></i> <?= htmlspecialchars($data['credit_note']->email) ?></div><?php endif; ?>
                <?php if(!empty($data['credit_note']->address)): ?><div style="margin-top:6px;"><?= nl2br(htmlspecialchars($data['credit_note']->address)) ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Item Description</th>
                <th style="width: 15%; text-align: right;">Price (Rs)</th>
                <th style="width: 15%; text-align: right;">Return Qty</th>
                <th style="width: 20%; text-align: right;">Total Credit (Rs)</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach($data['items'] as $item): ?>
                <tr>
                    <td class="col-num"><?= $i++ ?></td>
                    <td>
                        <div class="item-desc"><?= htmlspecialchars($item->description) ?></div>
                    </td>
                    <td style="text-align: right; font-family: var(--qb-mono);"><?= number_format($item->unit_price, 2) ?></td>
                    <td style="text-align: right; font-family: var(--qb-mono);"><?= number_format($item->quantity, 0) ?></td>
                    <td style="text-align: right; font-family: var(--qb-mono); font-weight: 700; color: var(--qb-slate-900);">
                        <?= number_format($item->total, 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-box">
        <div class="tot-row">
            <div class="tot-lbl">Subtotal</div>
            <div class="tot-val">Rs: <?= number_format($data['credit_note']->total_amount, 2) ?></div>
        </div>
        <div class="tot-row tot-grand">
            <div class="tot-lbl">Total Credit</div>
            <div class="tot-val">Rs: <?= number_format($data['credit_note']->total_amount, 2) ?></div>
        </div>
    </div>

    <div class="notes-box">
        <div class="notes-title">Notice</div>
        Thank you for your business. This is a computer-generated Customer Return Note & Credit Note. The corresponding amount has been automatically credited to the customer's ledger account. No signature required.
    </div>
</div>
