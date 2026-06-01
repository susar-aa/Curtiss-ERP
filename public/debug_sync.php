<?php
/**
 * Sync Debug Log Viewer
 * Access at: http://curtiss.local/debug_sync.php
 * 
 * This file helps diagnose sync failures by displaying the latest sync debug logs
 */

$logPath = dirname(__DIR__) . '/sync_debug.log';

if (!file_exists($logPath)) {
    die("Debug log not found at: $logPath");
}

$logContent = file_get_contents($logPath);
$lines = explode("\n", $logContent);

// Display only the last 100 lines (most recent activity)
$recentLines = array_slice($lines, -100);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sync Debug Log</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #569cd6;
            border-bottom: 2px solid #569cd6;
            padding-bottom: 10px;
        }
        .log-entry {
            padding: 8px;
            margin: 5px 0;
            border-left: 3px solid #999;
            background: #252526;
        }
        .log-entry.success {
            border-left-color: #4ec9b0;
            color: #4ec9b0;
        }
        .log-entry.error {
            border-left-color: #f48771;
            color: #f48771;
        }
        .log-entry.warning {
            border-left-color: #dcdcaa;
            color: #dcdcaa;
        }
        .log-entry.info {
            border-left-color: #569cd6;
            color: #569cd6;
        }
        .timestamp {
            color: #858585;
        }
        .controls {
            margin-bottom: 20px;
        }
        button {
            padding: 8px 16px;
            margin-right: 10px;
            background: #569cd6;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }
        button:hover {
            background: #4fc1d6;
        }
        .search-box {
            padding: 8px 16px;
            margin-right: 10px;
            width: 300px;
            background: #3e3e42;
            color: white;
            border: 1px solid #569cd6;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Sync Debug Log Viewer</h1>
    
    <div class="controls">
        <button onclick="location.reload()">🔄 Refresh</button>
        <button onclick="clearLog()">🗑️ Clear Log</button>
        <input type="text" class="search-box" id="searchBox" placeholder="Search logs..." onkeyup="filterLogs()">
    </div>
    
    <div id="logContainer">
        <?php
        foreach ($recentLines as $line) {
            if (empty(trim($line))) continue;
            
            $class = 'info';
            if (stripos($line, 'ERROR') !== false || stripos($line, 'FAILED') !== false) {
                $class = 'error';
            } elseif (stripos($line, 'WARNING') !== false) {
                $class = 'warning';
            } elseif (stripos($line, 'SUCCESS') !== false || stripos($line, 'COMPLETED') !== false) {
                $class = 'success';
            }
            
            // Extract timestamp if present
            preg_match('/\[([^\]]+)\]/', $line, $matches);
            $timestamp = $matches[1] ?? '';
            
            echo '<div class="log-entry ' . $class . '">';
            if ($timestamp) {
                echo '<span class="timestamp">[' . htmlspecialchars($timestamp) . ']</span> ';
            }
            echo htmlspecialchars(str_replace("[$timestamp]", "", $line));
            echo '</div>';
        }
        ?>
    </div>
    
    <hr style="border-color: #3e3e42; margin-top: 30px;">
    <p style="color: #858585; font-size: 12px;">
        Log file: <code><?php echo $logPath; ?></code><br>
        Last updated: <code><?php echo date('Y-m-d H:i:s', filemtime($logPath)); ?></code>
    </p>
</div>

<script>
function clearLog() {
    if (confirm('Are you sure you want to clear the sync debug log?')) {
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=clear'
        }).then(() => location.reload());
    }
}

function filterLogs() {
    const searchTerm = document.getElementById('searchBox').value.toLowerCase();
    const entries = document.querySelectorAll('.log-entry');
    
    entries.forEach(entry => {
        if (entry.textContent.toLowerCase().includes(searchTerm)) {
            entry.style.display = 'block';
        } else {
            entry.style.display = 'none';
        }
    });
}
</script>

<?php
// Handle clear action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear') {
    file_put_contents($logPath, '');
    echo '<p style="color: #4ec9b0; padding: 10px; background: #253c25; border-radius: 3px;">✓ Log cleared</p>';
}
?>
</body>
</html>
