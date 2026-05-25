<?php
class ReportController extends Controller {
    private $reportModel;
    private $companyModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->reportModel = $this->model('Report');
        $this->companyModel = $this->model('Company');
    }

    private function dateContext() {
        return $this->reportModel->normalizeDateRange(
            $_GET['start_date'] ?? null,
            $_GET['end_date'] ?? null
        );
    }

    private function baseReportData($title, $dated = false, $filterAction = '') {
        [$start, $end] = $this->dateContext();
        return [
            'title' => $title,
            'company' => $this->companyModel->getSettings(),
            'start_date' => $start,
            'end_date' => $end,
            'dated' => $dated,
            'filter_action' => $filterAction,
        ];
    }

    public function index() {
        [$start, $end] = $this->dateContext();
        $data = [
            'title' => 'Financial Reports Hub',
            'content_view' => 'reports/index',
            'default_start' => $start,
            'default_end' => $end,
        ];
        $this->view('layouts/main', $data);
    }

    public function trial_balance() {
        $accounts = $this->reportModel->getTrialBalanceData();
        $tbData = []; $totalDebit = 0; $totalCredit = 0;
        foreach ($accounts as $acc) {
            $debit = 0; $credit = 0;
            if (in_array($acc->account_type, ['Asset', 'Expense'])) {
                if ($acc->balance >= 0) { $debit = $acc->balance; } else { $credit = abs($acc->balance); }
            } else {
                if ($acc->balance >= 0) { $credit = $acc->balance; } else { $debit = abs($acc->balance); }
            }
            $tbData[] = ['code' => $acc->account_code, 'name' => $acc->account_name, 'type' => $acc->account_type, 'debit' => $debit, 'credit' => $credit];
            $totalDebit += $debit; $totalCredit += $credit;
        }
        $data = array_merge($this->baseReportData('Trial Balance'), [
            'tb_data' => $tbData, 'total_debit' => $totalDebit, 'total_credit' => $totalCredit,
        ]);
        $this->view('reports/trial_balance', $data);
    }

    public function profit_loss() {
        $accounts = $this->reportModel->getAccountsByTypes(['Revenue', 'Expense']);
        $revenues = []; $expenses = []; $totalRevenue = 0; $totalExpense = 0;
        foreach ($accounts as $acc) {
            if ($acc->account_type == 'Revenue') { $revenues[] = $acc; $totalRevenue += $acc->balance; }
            elseif ($acc->account_type == 'Expense') { $expenses[] = $acc; $totalExpense += $acc->balance; }
        }
        $data = array_merge($this->baseReportData('Profit & Loss (Ledger Balances)'), [
            'revenues' => $revenues, 'expenses' => $expenses,
            'total_revenue' => $totalRevenue, 'total_expense' => $totalExpense,
            'net_income' => $totalRevenue - $totalExpense,
        ]);
        $this->view('reports/profit_loss', $data);
    }

    public function profit_loss_period() {
        [$start, $end] = $this->dateContext();
        $accounts = $this->reportModel->getPeriodProfitLoss($start, $end);
        $revenues = []; $expenses = []; $totalRevenue = 0; $totalExpense = 0;
        foreach ($accounts as $acc) {
            $amount = ($acc->account_type === 'Revenue')
                ? ($acc->total_credit - $acc->total_debit)
                : ($acc->total_debit - $acc->total_credit);
            $acc->period_balance = $amount;
            if ($acc->account_type === 'Revenue') {
                $revenues[] = $acc; $totalRevenue += $amount;
            } else {
                $expenses[] = $acc; $totalExpense += $amount;
            }
        }
        $data = array_merge($this->baseReportData('Profit & Loss by Period', true, 'profit_loss_period'), [
            'revenues' => $revenues, 'expenses' => $expenses,
            'total_revenue' => $totalRevenue, 'total_expense' => $totalExpense,
            'net_income' => $totalRevenue - $totalExpense,
        ]);
        $this->view('reports/profit_loss_period', $data);
    }

    public function balance_sheet() {
        $plAccounts = $this->reportModel->getAccountsByTypes(['Revenue', 'Expense']);
        $netIncome = 0;
        foreach ($plAccounts as $acc) {
            if ($acc->account_type == 'Revenue') { $netIncome += $acc->balance; } else { $netIncome -= $acc->balance; }
        }
        $bsAccounts = $this->reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity']);
        $assets = []; $liabilities = []; $equities = [];
        $totalAssets = 0; $totalLiabilities = 0; $totalEquity = 0;
        foreach ($bsAccounts as $acc) {
            if ($acc->account_type == 'Asset') { $assets[] = $acc; $totalAssets += $acc->balance; }
            elseif ($acc->account_type == 'Liability') { $liabilities[] = $acc; $totalLiabilities += $acc->balance; }
            elseif ($acc->account_type == 'Equity') { $equities[] = $acc; $totalEquity += $acc->balance; }
        }
        $totalEquity += $netIncome;
        $data = array_merge($this->baseReportData('Balance Sheet'), [
            'assets' => $assets, 'liabilities' => $liabilities, 'equities' => $equities,
            'total_assets' => $totalAssets, 'total_liabilities' => $totalLiabilities,
            'total_equity_before_ni' => ($totalEquity - $netIncome),
            'net_income' => $netIncome, 'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilities + $totalEquity,
        ]);
        $this->view('reports/balance_sheet', $data);
    }

    public function cash_flow() {
        $accounts = $this->reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']);
        $netIncome = 0; $operating = []; $investing = []; $financing = []; $cashBalance = 0;
        foreach ($accounts as $acc) {
            $nameStr = strtolower($acc->account_name);
            if ($acc->account_type == 'Revenue') { $netIncome += $acc->balance; }
            elseif ($acc->account_type == 'Expense') { $netIncome -= $acc->balance; }
            if ($acc->account_type == 'Asset') {
                if (strpos($nameStr, 'cash') !== false || strpos($nameStr, 'bank') !== false) {
                    $cashBalance += $acc->balance;
                } elseif (strpos($nameStr, 'equipment') !== false || strpos($nameStr, 'asset') !== false) {
                    $investing[] = ['name' => 'Purchase of ' . $acc->account_name, 'amount' => -$acc->balance];
                } else {
                    $operating[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => -$acc->balance];
                }
            } elseif ($acc->account_type == 'Liability') {
                $operating[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => $acc->balance];
            } elseif ($acc->account_type == 'Equity') {
                if (strpos($nameStr, 'retained') === false) {
                    $financing[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => $acc->balance];
                }
            }
        }
        $data = array_merge($this->baseReportData('Statement of Cash Flows'), [
            'net_income' => $netIncome, 'operating' => $operating, 'investing' => $investing,
            'financing' => $financing, 'ending_cash' => $cashBalance,
        ]);
        $this->view('reports/cash_flow', $data);
    }

    public function ar_aging() {
        $invoices = $this->reportModel->getARAging();
        $agingData = [];
        $totals = ['current' => 0, 'thirty' => 0, 'sixty' => 0, 'ninety' => 0, 'older' => 0, 'total' => 0];
        foreach ($invoices as $inv) {
            $cust = $inv->customer_name;
            if (!isset($agingData[$cust])) {
                $agingData[$cust] = ['current' => 0, 'thirty' => 0, 'sixty' => 0, 'ninety' => 0, 'older' => 0, 'total' => 0];
            }
            $amount = floatval($inv->total_amount);
            $days = intval($inv->days_overdue);
            if ($days <= 0) { $agingData[$cust]['current'] += $amount; $totals['current'] += $amount; }
            elseif ($days <= 30) { $agingData[$cust]['thirty'] += $amount; $totals['thirty'] += $amount; }
            elseif ($days <= 60) { $agingData[$cust]['sixty'] += $amount; $totals['sixty'] += $amount; }
            elseif ($days <= 90) { $agingData[$cust]['ninety'] += $amount; $totals['ninety'] += $amount; }
            else { $agingData[$cust]['older'] += $amount; $totals['older'] += $amount; }
            $agingData[$cust]['total'] += $amount;
            $totals['total'] += $amount;
        }
        $data = array_merge($this->baseReportData('A/R Aging Summary'), [
            'aging_data' => $agingData, 'totals' => $totals, 'invoice_detail' => $invoices,
        ]);
        $this->view('reports/ar_aging', $data);
    }

    public function fifo_profit() {
        [$start, $end] = $this->dateContext();
        $invoices = $this->reportModel->getFIFOSalesData($start, $end);
        $totalRevenue = 0; $totalCost = 0;
        foreach ($invoices as $row) {
            $totalRevenue += floatval($row->revenue);
            $totalCost += floatval($row->total_cost);
        }
        $data = array_merge($this->baseReportData('FIFO Profit & Margin', true, 'fifo_profit'), [
            'invoices' => $invoices,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalRevenue - $totalCost,
        ]);
        $this->view('reports/fifo_profit', $data);
    }

    public function sales_summary() {
        [$start, $end] = $this->dateContext();
        $report = $this->reportModel->getSalesSummary($start, $end);
        $data = array_merge($this->baseReportData('Sales Summary', true, 'sales_summary'), [
            'summary' => $report['summary'],
            'daily' => $report['daily'],
        ]);
        $this->view('reports/sales_summary', $data);
    }

    public function sales_by_customer() {
        [$start, $end] = $this->dateContext();
        $rows = $this->reportModel->getSalesByCustomer($start, $end);
        $data = array_merge($this->baseReportData('Sales by Customer', true, 'sales_by_customer'), ['rows' => $rows]);
        $this->view('reports/sales_by_customer', $data);
    }

    public function sales_by_product() {
        [$start, $end] = $this->dateContext();
        $rows = $this->reportModel->getSalesByProduct($start, $end);
        $data = array_merge($this->baseReportData('Sales by Product', true, 'sales_by_product'), ['rows' => $rows]);
        $this->view('reports/sales_by_product', $data);
    }

    public function collections() {
        [$start, $end] = $this->dateContext();
        $report = $this->reportModel->getCollectionsReport($start, $end);
        $data = array_merge($this->baseReportData('Collections Report', true, 'collections'), $report);
        $this->view('reports/collections', $data);
    }

    public function purchases() {
        [$start, $end] = $this->dateContext();
        $rows = $this->reportModel->getPurchasesSummary($start, $end);
        $data = array_merge($this->baseReportData('Purchases & GRN Summary', true, 'purchases'), ['rows' => $rows]);
        $this->view('reports/purchases', $data);
    }

    public function general_ledger() {
        [$start, $end] = $this->dateContext();
        $rows = $this->reportModel->getGeneralLedger($start, $end);
        $data = array_merge($this->baseReportData('General Ledger Detail', true, 'general_ledger'), ['rows' => $rows]);
        $this->view('reports/general_ledger', $data);
    }

    public function sales_by_rep() {
        [$start, $end] = $this->dateContext();
        $rows = $this->reportModel->getSalesByRep($start, $end);
        $data = array_merge($this->baseReportData('Sales by Rep Route', true, 'sales_by_rep'), ['rows' => $rows]);
        $this->view('reports/sales_by_rep', $data);
    }

    public function tax_summary() {
        [$start, $end] = $this->dateContext();
        $report = $this->reportModel->getTaxSummary($start, $end);
        $data = array_merge($this->baseReportData('Tax & Invoice Totals', true, 'tax_summary'), $report);
        $this->view('reports/tax_summary', $data);
    }

    public function inventory_valuation() {
        $report = $this->reportModel->getInventoryValuation();
        $data = array_merge($this->baseReportData('Inventory Valuation'), $report);
        $this->view('reports/inventory_valuation', $data);
    }
}
