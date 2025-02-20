<?php
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define error log file
    $error_log_file = "/home/dum1ya/Desktop/sqlmap_debug.log";
    
    function log_error($message) {
        global $error_log_file;
        file_put_contents($error_log_file, date("Y-m-d H:i:s") . " - " . $message . "\n", FILE_APPEND);
    }

    // Validate and sanitize inputs
    $target_url = escapeshellarg($_POST['target_url']);
    $scan_type = $_POST['scan_type'];
    $db_type = $_POST['db_type'];
    $login_url = escapeshellarg($_POST['login_url'] ?? '');
    $session_cookie = escapeshellarg($_POST['session_cookie'] ?? '');
    $custom_headers = escapeshellarg($_POST['custom_headers'] ?? '');
    $detection_level = $_POST['detection_level'] ?? 'medium';
    $scan_speed = $_POST['scan_speed'] ?? 'normal';

    // Validate target URL
    if (empty(trim($_POST['target_url']))) {
        echo json_encode(["status" => "error", "message" => "Target URL cannot be empty."]);
        exit;
    }

    // Construct the sqlmap command
    $command = "sqlmap -u $target_url";

    // Add scan type options
    if ($scan_type === "full") {
        $command .= " --level=5 --risk=3"; // Full scan with maximum level and risk
    } elseif ($scan_type === "custom") {
        $command .= " --level=" . escapeshellarg($detection_level);
        if (isset($_POST['boolean-blind'])) $command .= " --technique=B";
        if (isset($_POST['time-blind'])) $command .= " --technique=T";
        if (isset($_POST['error-based'])) $command .= " --technique=E";
        if (isset($_POST['union-based'])) $command .= " --technique=U";
        if (isset($_POST['stacked-queries'])) $command .= " --technique=S";
    }

    // Add database type
    $command .= " --dbms=" . escapeshellarg($db_type);

    // Add authentication options
    if (!empty($login_url)) {
        $command .= " --login-url=$login_url";
    }
    if (!empty($session_cookie)) {
        $command .= " --cookie=$session_cookie";
    }
    if (!empty($custom_headers)) {
        $command .= " --headers=$custom_headers";
    }

    // Add scan speed
    if ($scan_speed === "slow") {
        $command .= " --delay=5";
    } elseif ($scan_speed === "fast") {
        $command .= " --threads=10";
    }

    // Add advanced options
    if (isset($_POST['waf-bypass'])) $command .= " --tamper=space2comment";
    if (isset($_POST['use-tor'])) $command .= " --tor";
    if (isset($_POST['save-logs'])) $command .= " --output-dir=/home/dum1ya/Desktop/sqlmap_logs";

    // Log command execution
    log_error("Executing command: $command");

    // Execute command and capture output
    $output = shell_exec("$command 2>&1");
    log_error("Command output: " . $output);

    // Parse sqlmap output to extract results
    $results = [];
    if (preg_match_all('/Parameter: (\w+)\s+Type: (\w+)\s+Payload: (.+)/', $output, $matches)) {
        for ($i = 0; $i < count($matches[0]); $i++) {
            $results[] = [
                "parameter" => $matches[1][$i],
                "type" => $matches[2][$i],
                "payload" => $matches[3][$i],
                "database" => "example_db", // Placeholder, replace with actual database name
                "severity" => "Critical" // Placeholder, replace with actual severity
            ];
        }
    }

    // Return the results as JSON
    echo json_encode(["status" => "success", "results" => $results]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
