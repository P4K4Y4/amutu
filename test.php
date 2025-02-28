<?php
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    putenv('HOME=/tmp');
    $data = json_decode(file_get_contents('php://input'), true);
    $tool = $data['tool'];
    $target = $data['target'];
    $verbose = $data['verbose'] ? '-v' : '';

    // Define tool requirements (domain, ip, both)
    $toolRequirements = [
        'maltego' => 'both',
        'theharvester' => 'domain',
        'shodan' => 'ip',
        'amass' => 'domain',
        'spiderfoot' => 'both',
        'recon-ng' => 'domain',
        'googledork' => 'domain',
        'nmap' => 'both',
        'masscan' => 'both',
        'whatweb' => 'both',
        'nikto' => 'both',
        'dnsrecon' => 'domain',
        'fierce' => 'domain',
        'sublist3r' => 'domain'
    ];

    // Validate target type
    function isIP($target) {
        return filter_var($target, FILTER_VALIDATE_IP);
    }

    function isDomain($target) {
        return preg_match('/^(?!-)[A-Za-z0-9-]{1,63}(\.[A-Za-z]{2,})+$/', $target);
    }

    $targetType = '';
    if (isIP($target)) {
        $targetType = 'ip';
    } elseif (isDomain($target)) {
        $targetType = 'domain';
    } else {
        echo "Invalid target format. Must be a valid IP or domain.";
        exit;
    }

    // Check if tool supports the target type
    if (isset($toolRequirements[$tool])) {
        $requiredType = $toolRequirements[$tool];
        if ($requiredType !== 'both' && $targetType !== $requiredType) {
            echo "Tool '$tool' requires a $requiredType as target.";
            exit;
        }
    }

    // Resolve domain to IP if needed (example for tools requiring IP)
    $resolvedTarget = $target;
    if ($targetType === 'domain' && $toolRequirements[$tool] === 'ip') {
        $resolvedTarget = gethostbyname($target);
        if ($resolvedTarget === $target) {
            echo "Failed to resolve domain to IP.";
            exit;
        }
    }

    $targetEscaped = escapeshellarg($resolvedTarget);

    // Updated commands with better compatibility
    $commands = [
        'theharvester' => "theharvester -d $targetEscaped -b anubis,baidu,bevigil,bing,binaryedge,censys,certspotter,crtsh,dnsdumpster,duckduckgo,hackertarget,otx,rapiddns,sitedossier,threatminer,urlscan,yahoo,zoomeye $verbose",
        'shodan' => "shodan host $targetEscaped", // Ensure 'shodan cli' is configured
        'amass' => "amass enum -d $targetEscaped $verbose",
        'spiderfoot' => "spiderfoot -s $targetEscaped",
        'recon-ng' => "recon-ng -r $targetEscaped -m recon/domains-hosts/brute_hosts -x 'set SOURCE $targetEscaped; run;'",
        'nmap' => "nmap $verbose -A $targetEscaped",
        'whatweb' => "whatweb $targetEscaped",
        'wafw00f' => "wafw00f -a -r $targetEscaped",
        'nikto' => "nikto -h $targetEscaped",
        'dnsrecon' => "dnsrecon -d $targetEscaped $verbose",
        'fierce' => "fierce --domain $targetEscaped $verbose",
        'sublist3r' => "sublist3r -d $targetEscaped $verbose"
    ];

    // Check API key for Shodan
    if ($tool === 'shodan') {
        $apiKey = shell_exec('shodan init 2>&1');
        if (strpos($apiKey, 'Success') === false) {
            echo "Shodan requires an API key. Configure with 'shodan init YOUR_API_KEY'.";
            exit;
        }
    }

    if (isset($commands[$tool])) {
        $filteredOutput = shell_exec("tgpt -q " . escapeshellarg($output) . "2>&1");

        echo $filteredOutput ?: "Failed to process output with AI.";
    } else {
        echo "Invalid tool: $tool";
    }
}
?>
