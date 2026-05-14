
```eof

### 7. Add Link to Main Layout Sidebar
We will add the link right between CRM and Sales to keep the logical workflow.

```php:main.php (app/Views/layouts/main.php)
// ... Find your sidebar navigation section and add the Estimate link: ...
        <nav class="sidebar">
            <a href="<?= APP_URL ?>/dashboard">Dashboard</a>
            <a href="<?= APP_URL ?>/crm">CRM & Leads</a>
            <a href="<?= APP_URL ?>/estimate">Estimates & Quotes</a> <!-- NEW LINK -->
            <a href="<?= APP_URL ?>/sales">Sales & AR</a>
            <a href="<?= APP_URL ?>/expenses">Expenses & AP</a>
// ... rest of the links remain the same ...
```eof

Once you run the SQL and save these files, try creating an Estimate. Once it's created, change its status to **"Accepted"** using the dropdown in the table. A bright green **"Convert to Invoice"** button will magically appear! Click it to bridge the workflow straight into your accounting engine.

Let me know when you've tested this, and we will move to **Step 2: Budgeting & Variance Reporting (Option D)**!