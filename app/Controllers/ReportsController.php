<?php
class ReportController extends Controller {
    private $reportModel;
    private $companyModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->reportModel = $this->model('Report');
        $this->companyModel = $this->model('Company');
    }

    public function index() {
        $data = ['title' => 'Financial Reports', 'content_view' => 'reports/index'];
        $this->view('layouts/main', $data);
    }

    public function trial_balance() {
        $accounts = $this->reportModel->getTrialBalanceData();
        $company = $this->companyModel->getSettings();
        
        $tbData = []; $totalDebit = 0; $totalCredit = 0;
        foreach($accounts as $acc) {
            $debit = 0; $credit = 0;
            if (in_array($acc->account_type, ['Asset', 'Expense'])) {
                if ($acc->balance >= 0) { $debit = $acc->balance; } else { $credit = abs($acc->balance); }
            } else {
                if ($acc->balance >= 0) { $credit = $acc->balance; } else { $debit = abs($acc->balance); }
            }
            $tbData[] = ['code' => $acc->account_code, 'name' => $acc->account_name, 'type' => $acc->account_type, 'debit' => $debit, 'credit' => $credit];
            $totalDebit += $debit; $totalCredit += $credit;
        }

        $data = ['title' => 'Trial Balance', 'company' => $company, 'tb_data' => $tbData, 'total_debit' => $totalDebit, 'total_credit' => $totalCredit];
        $this->view('reports/trial_balance', $data);
    }

    public function profit_loss() {
        $accounts = $this->reportModel->getAccountsByTypes(['Revenue', 'Expense']);
        $company = $this->companyModel->getSettings();

        $revenues = []; $expenses = []; $totalRevenue = 0; $totalExpense = 0;
        foreach($accounts as $acc) {
            if ($acc->account_type == 'Revenue') { $revenues[] = $acc; $totalRevenue += $acc->balance; } 
            elseif ($acc->account_type == 'Expense') { $expenses[] = $acc; $totalExpense += $acc->balance; }
        }
        $netIncome = $totalRevenue - $totalExpense;

        $data = ['title' => 'Profit & Loss', 'company' => $company, 'revenues' => $revenues, 'expenses' => $expenses, 'total_revenue' => $totalRevenue, 'total_expense' => $totalExpense, 'net_income' => $netIncome];
        $this->view('reports/profit_loss', $data);
    }

    public function balance_sheet() {
        $plAccounts = $this->reportModel->getAccountsByTypes(['Revenue', 'Expense']);
        $netIncome = 0;
        foreach($plAccounts as $acc) {
            if ($acc->account_type == 'Revenue') { $netIncome += $acc->balance; } else { $netIncome -= $acc->balance; }
        }

        $bsAccounts = $this->reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity']);
        $company = $this->companyModel->getSettings();

        $assets = []; $liabilities = []; $equities = [];
        $totalAssets = 0; $totalLiabilities = 0; $totalEquity = 0;

        foreach($bsAccounts as $acc) {
            if ($acc->account_type == 'Asset') { $assets[] = $acc; $totalAssets += $acc->balance; } 
            elseif ($acc->account_type == 'Liability') { $liabilities[] = $acc; $totalLiabilities += $acc->balance; } 
            elseif ($acc->account_type == 'Equity') { $equities[] = $acc; $totalEquity += $acc->balance; }
        }

        $totalEquity += $netIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $data = [
            'title' => 'Balance Sheet', 'company' => $company, 'assets' => $assets, 'liabilities' => $liabilities, 'equities' => $equities,
            'total_assets' => $totalAssets, 'total_liabilities' => $totalLiabilities, 'total_equity_before_ni' => ($totalEquity - $netIncome),
            'net_income' => $netIncome, 'total_equity' => $totalEquity, 'total_liabilities_equity' => $totalLiabilitiesAndEquity
        ];
        $this->view('reports/balance_sheet', $data);
    }

    public function cash_flow() {
        $accounts = $this->reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']);
        $company = $this->companyModel->getSettings();

        $netIncome = 0; $operating = []; $investing = []; $financing = []; $cashBalance = 0;

        foreach($accounts as $acc) {
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

        $data = ['title' => 'Statement of Cash Flows', 'company' => $company, 'net_income' => $netIncome, 'operating' => $operating, 'investing' => $investing, 'financing' => $financing, 'ending_cash' => $cashBalance];
        $this->view('reports/cash_flow', $data);
    }

    // NEW: AR Aging Report Logic
    public function ar_aging() {
        $invoices = $this->reportModel->getARAging();
        $company = $this->companyModel->getSettings();

        $agingData = [];
        $totals = ['current' => 0, 'thirty' => 0, 'sixty' => 0, 'ninety' => 0, 'older' => 0, 'total' => 0];

        foreach ($invoices as $inv) {
            $cust = $inv->customer_name;
            if (!isset($agingData[$cust])) {
                $agingData[$cust] = ['current' => 0, 'thirty' => 0, 'sixty' => 0, 'ninety' => 0, 'older' => 0, 'total' => 0];
            }

            $amount = $inv->total_amount;
            $days = $inv->days_overdue;

            if ($days <= 0) { $agingData[$cust]['current'] += $amount; $totals['current'] += $amount; }
            elseif ($days <= 30) { $agingData[$cust]['thirty'] += $amount; $totals['thirty'] += $amount; }
            elseif ($days <= 60) { $agingData[$cust]['sixty'] += $amount; $totals['sixty'] += $amount; }
            elseif ($days <= 90) { $agingData[$cust]['ninety'] += $amount; $totals['ninety'] += $amount; }
            else { $agingData[$cust]['older'] += $amount; $totals['older'] += $amount; }

            $agingData[$cust]['total'] += $amount;
            $totals['total'] += $amount;
        }

        $data = ['title' => 'A/R Aging Summary', 'company' => $company, 'aging_data' => $agingData, 'totals' => $totals];
        $this->view('reports/ar_aging', $data);
    }
}