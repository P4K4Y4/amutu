<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Set correct headers for Server-Sent Events (SSE)
    header("Content-Type: text/event-stream");
    header("Cache-Control: no-cache");
    header("Connection: keep-alive");
    header("X-Accel-Buffering: no"); // Disable buffering (for Nginx servers)

    // Get form data safely
    $target_url = escapeshellarg($_POST["target_url"]);
    $db_type = escapeshellarg($_POST["db_type"]);
    $scan_type = $_POST["scan_type"];
    $retrieve_db_names = isset($_POST["retrieve_db_names"]) ? "--dbs" : "";
    $retrieve_table_names = isset($_POST["retrieve_table_names"]) ? "--tables" : "";
    $retrieve_column_names = isset($_POST["retrieve_column_names"]) ? "--columns" : "";
    $dump_table_data = isset($_POST["dump_table_data"]) ? "--dump" : "";
    $waf_bypass = isset($_POST["waf_bypass"]) ? "--tamper=space2comment" : "";
    $use_tor = isset($_POST["use_tor"]) ? "--tor" : "";

    // Set SQLMap scan options
    $scan_options = ($scan_type == "full") ? "--batch --risk=3 --level=5" : "--batch --risk=1 --level=1";
    
    // Construct SQLMap command
    $sqlmap_cmd = "sqlmap -u $target_url --dbms=$db_type $retrieve_db_names $retrieve_table_names $retrieve_column_names $dump_table_data $scan_options $waf_bypass $use_tor";

    // Open process to run SQLMap
    $descriptorspec = [
        1 => ["pipe", "r"], // Standard output
        2 => ["pipe", "w"], // Standard error
    ];
    
    $process = proc_open($sqlmap_cmd, $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        while (!feof($pipes[1])) {
            $output = fgets($pipes[1]);
            if ($output !== false) {
                echo "data: " . trim($output) . "\n\n";
                ob_flush();
                flush(); // Push output to client
            }
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        echo "data: Error starting SQLMap\n\n";
        ob_flush();
        flush();
    }
}
?>
