<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$workerId = isset($argv[1]) ? $argv[1] : 0;

if ($workerId == 0) {
    echo "Starting lock test...\n";
    $pipes = [];
    $proc1 = proc_open("c:\\xampp\\php\\php.exe ".__FILE__." 1", [1 => ["pipe", "w"]], $pipes1);
    sleep(1); // Ensure proc1 starts and gets the lock
    $proc2 = proc_open("c:\\xampp\\php\\php.exe ".__FILE__." 2", [1 => ["pipe", "w"]], $pipes2);
    
    $out1 = stream_get_contents($pipes1[1]);
    $out2 = stream_get_contents($pipes2[1]);
    
    echo "P1: $out1";
    echo "P2: $out2";
} else {
    $db1 = new Database();
    $db1->beginTransaction();
    echo "Worker $workerId: Transaction started. Time: " . time() . "\n";
    
    $db2 = new Database();
    $db2->query("SELECT quantity_on_hand FROM items WHERE id = 4205 FOR UPDATE");
    $row = $db2->single();
    echo "Worker $workerId: Locked via db2. Value: " . $row->quantity_on_hand . ". Time: " . time() . "\n";
    
    if ($workerId == 1) {
        sleep(3);
        $db3 = new Database();
        $db3->query("UPDATE items SET quantity_on_hand = quantity_on_hand + 1 WHERE id = 4205");
        $db3->execute();
        echo "Worker 1: Updated stock via db3. Time: " . time() . "\n";
    }
    
    $db1->commit();
    echo "Worker $workerId: Committed. Time: " . time() . "\n";
}
