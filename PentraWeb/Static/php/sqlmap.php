<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: text/event-stream");
    header("Cache-Control: no-cache");
    header("Connection: keep-alive");

    $target_url = escapeshellarg($_POST["target_url"]);
    $scan_type = $_POST["scan_type"];
    $db_type = escapeshellarg($_POST["db_type"]);
    $retrieve_db_names = isset($_POST["retrieve_db_names"]) ? "--dbs" : "";
    $retrieve_table_names = isset($_POST["retrieve_table_names"]) ? "--tables" : "";
    $retrieve_column_names = isset($_POST["retrieve_column_names"]) ? "--columns" : "";
    $dump_table_data = isset($_POST["dump_table_data"]) ? "--dump" : "";
    $waf_bypass = isset($_POST["waf_bypass"]) ? "--tamper=space2comment" : "";
    $use_tor = isset($_POST["use_tor"]) ? "--tor" : "";

    // Define scan type options
    $scan_options = "";
    if ($scan_type == "full") {
        $scan_options = "--batch --risk=3 --level=5";
    } elseif ($scan_type == "custom") {
        if (isset($_POST["boolean_blind"])) {
            $scan_options .= " --technique=B";
        }
        if (isset($_POST["time_blind"])) {
            $scan_options .= " --technique=T";
        }
        if (isset($_POST["error_based"])) {
            $scan_options .= " --technique=E";
        }
        if (isset($_POST["union_based"])) {
            $scan_options .= " --technique=U";
        }
        if (isset($_POST["stacked_queries"])) {
            $scan_options .= " --technique=S";
        }
    } else {
        $scan_options = "--batch --risk=1 --level=1"; // Basic scan
    }

    // SQLMap command
    $sqlmap_cmd = "sqlmap -u $target_url --dbms=$db_type $retrieve_db_names $retrieve_table_names $retrieve_column_names $dump_table_data $scan_options $waf_bypass $use_tor";

    // Execute command and stream output
    $descriptorspec = [
        1 => ["pipe", "r"], // Standard output
        2 => ["pipe", "w"], // Standard error
    ];
    
    $process = proc_open($sqlmap_cmd, $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        while (!feof($pipes[1])) {
            $output = fgets($pipes[1]);
            echo "data: " . trim($output) . "\n\n";
            ob_flush();
            flush();
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        echo "data: Error starting SQLMap\n\n";
    }
}
?>
