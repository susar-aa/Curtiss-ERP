<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SUPPLIER RETURNS VIEW — DESIGN SYSTEM
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
    <a href="<?= APP_URL ?>/supplier-return" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fa-solid fa-print"></i> Print Return Note
    </button>
</div>

<div class="doc-wrapper">
    <div class="doc-header">
        <div class="doc-brand">
            <h1><?= APP_NAME ?></h1>
            <p>Authorized Supplier Return Note (SRN)</p>
        </div>
        <div class="doc-meta">
            <h2 class="doc-title">Return Note</h2>
            <div class="meta-grid">
                <div class="meta-lbl">Doc Ref:</div>
                <div class="meta-val"><?= htmlspecialchars($data['return']->return_number) ?></div>
                <div class="meta-lbl">Date:</div>
                <div class="meta-val"><?= date('d M Y', strtotime($data['return']->return_date)) ?></div>
                <div class="meta-lbl">Created By:</div>
                <div class="meta-val"><?= htmlspecialchars($data['return']->creator_name) ?></div>
            </div>
        </div>
    </div>

    <div class="party-grid">
        <div class="party-box">
            <div class="party-title"><i class="fa-solid fa-building-circle-arrow-right"></i> Returned To (Supplier)</div>
            <div class="party-name"><?= htmlspecialchars($data['return']->vendor_name) ?></div>
            <div class="party-details">
                <?php if(!empty($data['return']->phone)): ?><div><i class="fa-solid fa-phone" style="width:16px;"></i> <?= htmlspecialchars($data['return']->phone) ?></div><?php endif; ?>
                <?php if(!empty($data['return']->email)): ?><div><i class="fa-solid fa-envelope" style="width:16px;"></i> <?= htmlspecialchars($data['return']->email) ?></div><?php endif; ?>
                <?php if(!empty($data['return']->address)): ?><div style="margin-top:6px;"><?= nl2br(htmlspecialchars($data['return']->address)) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="party-box">
            <div class="party-title"><i class="fa-solid fa-truck-ramp-box"></i> Returned From</div>
            <div class="party-name"><?= APP_NAME ?> ERP</div>
            <div class="party-details">
                Physical Inventory Location<br>
                Dispatch Department
            </div>
        </div>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Product Description</th>
                <th style="width: 15%; text-align: right;">Cost (Rs)</th>
                <th style="width: 15%; text-align: right;">Return Qty</th>
                <th style="width: 20%; text-align: right;">Total Value (Rs)</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach($data['items'] as $item): ?>
                <tr>
                    <td class="col-num"><?= $i++ ?></td>
                    <td>
                        <div class="item-desc"><?= htmlspecialchars($item->description) ?></div>
                        <?php if($item->grn_number): ?>
                            <div class="item-meta">
                                <i class="fa-solid fa-link"></i> Linked Batch: <?= htmlspecialchars($item->grn_number) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="col-num"><?= number_format($item->unit_cost, 2) ?></td>
                    <td class="col-num"><?= number_format($item->quantity, 0) ?></td>
                    <td class="col-num" style="font-weight: 700;"><?= number_format($item->total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-box">
        <div class="tot-row tot-grand">
            <div class="tot-lbl">Total Credit Value</div>
            <div class="tot-val">Rs. <?= number_format($data['return']->total_amount, 2) ?></div>
        </div>
    </div>

    <?php if(!empty($data['return']->notes)): ?>
        <div class="notes-box">
            <div class="notes-title"><i class="fa-regular fa-comment-dots"></i> Reason for Return / Notes</div>
            <?= nl2br(htmlspecialchars($data['return']->notes)) ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; text-align: center; color: var(--qb-slate-500); font-size: 13px;">
        <div>
            <div style="border-bottom: 1px solid var(--qb-slate-300); width: 200px; margin: 0 auto 10px auto; padding-bottom: 20px;"></div>
            <div>Authorized Signature</div>
        </div>
        <div>
            <div style="border-bottom: 1px solid var(--qb-slate-300); width: 200px; margin: 0 auto 10px auto; padding-bottom: 20px;"></div>
            <div>Supplier Acknowledgement</div>
        </div>
    </div>
</div>
