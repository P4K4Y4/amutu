# app.py
from flask import Flask, render_template, request, jsonify
import subprocess
import xml.etree.ElementTree as ET
import os

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'scans'

# Helper function to parse Nmap XML output
def parse_nmap_xml(xml_file):
    tree = ET.parse(xml_file)
    root = tree.getroot()
    
    results = []
    for host in root.findall('host'):
        for port in host.findall('ports/port'):
            port_data = {
                'port': port.get('portid'),
                'protocol': port.get('protocol'),
                'state': port.find('state').get('state'),
                'service': port.find('service').get('name') if port.find('service') is not None else 'unknown',
                'version': port.find('service').get('product') if port.find('service') is not None else ''
            }
            results.append(port_data)
    return results

@app.route('/scan', methods=['POST'])
def nmap_scan():
    try:
        data = request.json
        target = data['target']
        scan_type = data['scan_type']
        
        # Build Nmap command
        cmd = ['nmap']
        
        if scan_type == 'quick':
            cmd += ['-T4', '-F']
        elif scan_type == 'full':
            cmd += ['-A']
        elif scan_type == 'custom':
            # Add custom options handling
            pass
            
        cmd += ['-oX', 'scan.xml', target]
        
        # Run Nmap scan
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=300  # 5 minute timeout
        )
        
        # Parse XML results
        scan_results = parse_nmap_xml('scan.xml')
        
        return jsonify({
            'status': 'completed',
            'results': scan_results
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)