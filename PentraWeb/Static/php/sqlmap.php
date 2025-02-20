<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $target_url = escapeshellarg($_POST["target_url"]);
    $scan_type = $_POST["scan_type"];
    $db_type = escapeshellarg($_POST["db_type"]);
    $retrieve_db_names = isset($_POST["retrieve_db_names"]) ? "--dbs" : "";
    $retrieve_table_names = isset($_POST["retrieve_table_names"]) ? "--tables" : "";
    $retrieve_column_names = isset($_POST["retrieve_column_names"]) ? "--columns" : "";
    $dump_table_data = isset($_POST["dump_table_data"]) ? "--dump" : "";
    $waf_bypass = isset($_POST["waf_bypass"]) ? "--tamper=space2comment" : "";
    $use_tor = isset($_POST["use_tor"]) ? "--tor" : "";
    $log_file = "scan_results.txt";

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
    $sqlmap_cmd = "sqlmap -u $target_url --dbms=$db_type $retrieve_db_names $retrieve_table_names $retrieve_column_names $dump_table_data $scan_options $waf_bypass $use_tor > $log_file 2>&1";
    
    // Execute command
    exec($sqlmap_cmd, $output, $return_code);

    // Return results
    if ($return_code === 0) {
        echo json_encode(["status" => "success", "message" => "Scan completed. Check scan_results.txt"]);
    } else {
        echo json_encode(["status" => "error", "message" => "SQLMap execution failed."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
