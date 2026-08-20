<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Cheque Returns <small class="text-muted" style="font-size: 14px;">(Cheques in Hand)</small></h2>

        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="<?= APP_URL ?>/chequereturn" class="mb-4 row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by Cheque #, Bank or Customer..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= APP_URL ?>/chequereturn" class="btn btn-secondary w-100">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cheque Number</th>
                                <th>Bank</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Banking Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($cheques)): ?>
                                <tr><td colspan="6" class="text-center">No pending cheques in hand found.</td></tr>
                            <?php else: ?>
                                <?php foreach($cheques as $chk): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($chk->cheque_number) ?></strong></td>
                                        <td><?= htmlspecialchars($chk->bank_name) ?></td>
                                        <td><?= htmlspecialchars($chk->customer_name) ?></td>
                                        <td class="text-end">Rs: <?= number_format($chk->amount, 2) ?></td>
                                        <td><?= date('d-M-Y', strtotime($chk->banking_date)) ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="openReturnModal(<?= $chk->id ?>, '<?= htmlspecialchars($chk->cheque_number) ?>', '<?= htmlspecialchars($chk->customer_name) ?>', <?= $chk->amount ?>)">
                                                Mark as Returned
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/chequereturn/processReturn" id="returnForm">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Mark Cheque as Returned</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="cheque_id" id="return_cheque_id">
              
              <div class="alert alert-warning">
                  You are about to return Cheque #<strong id="disp_cheque_num"></strong> from <strong id="disp_customer"></strong>.
                  <br>Amount: <strong>Rs: <span id="disp_amount"></span></strong>
              </div>

              <div class="mb-3">
                  <label class="form-label">Return Reason *</label>
                  <select class="form-select" name="return_reason" id="return_reason" required onchange="toggleOtherReason()">
                      <option value="">-- Select Reason --</option>
                      <option value="Insufficient Funds">Insufficient Funds</option>
                      <option value="Signature Mismatch">Signature Mismatch</option>
                      <option value="Account Closed">Account Closed</option>
                      <option value="Payment Stopped">Payment Stopped</option>
                      <option value="Other">Other</option>
                  </select>
              </div>

              <div class="mb-3" id="other_reason_div" style="display:none;">
                  <label class="form-label">Specify Reason *</label>
                  <input type="text" name="other_reason" id="other_reason" class="form-control" placeholder="Enter reason">
              </div>

              <div class="mb-3">
                  <label class="form-label">Return Date *</label>
                  <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
              </div>
              
              <hr>
              <h6>Bank Return Charges (Optional)</h6>
              <div class="mb-3">
                  <label class="form-label">Bank Charge Amount</label>
                  <input type="number" step="0.01" min="0" name="return_charge" class="form-control" placeholder="0.00">
                  <small class="text-muted">Enter any fee charged by the bank.</small>
              </div>

              <div class="mb-3">
                  <label class="form-label">Expense Account for Charge</label>
                  <select name="charge_account_id" class="form-select">
                      <option value="">-- Select Account --</option>
                      <?php foreach($expense_accounts as $acc): ?>
                          <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                      <?php endforeach; ?>
                  </select>
                  <small class="text-muted">Required if charge amount is entered.</small>
              </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure? This will reverse the customer\'s payment and reopen their invoices.');">Confirm Return</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function openReturnModal(id, num, customer, amount) {
    document.getElementById('return_cheque_id').value = id;
    document.getElementById('disp_cheque_num').textContent = num;
    document.getElementById('disp_customer').textContent = customer;
    document.getElementById('disp_amount').textContent = amount.toFixed(2);
    
    var myModal = new bootstrap.Modal(document.getElementById('returnModal'));
    myModal.show();
}

function toggleOtherReason() {
    var val = document.getElementById('return_reason').value;
    var otherDiv = document.getElementById('other_reason_div');
    var otherInput = document.getElementById('other_reason');
    if (val === 'Other') {
        otherDiv.style.display = 'block';
        otherInput.required = true;
    } else {
        otherDiv.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}
</script>
