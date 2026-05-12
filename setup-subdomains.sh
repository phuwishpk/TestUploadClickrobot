#!/bin/bash

# Setup subdomains for local development
# Run: bash setup-subdomains.sh

echo "=========================================="
echo "  Setup Subdomains for Local Development"
echo "=========================================="
echo ""

# Check if running on macOS
if [[ "$OSTYPE" == "darwin"* ]]; then
    HOSTS_FILE="/etc/hosts"
else
    HOSTS_FILE="/etc/hosts"
fi

echo "Adding subdomains to $HOSTS_FILE..."
echo ""

# Backup first
sudo cp $HOSTS_FILE $HOSTS_FILE.bak.$(date +%Y%m%d%H%M%S)
echo "✓ Backed up hosts file"

# Add subdomains
sudo bash -c "cat >> $HOSTS_FILE" << 'EOF'

# School Media Upload - Subdomains
127.0.0.1    admin.localhost
127.0.0.1    bnk.localhost
127.0.0.1    srb.localhost
127.0.0.1    nbr.localhost
EOF

echo "✓ Added subdomains:"
echo "  - admin.localhost"
echo "  - bnk.localhost"
echo "  - srb.localhost"
echo "  - nbr.localhost"
echo ""
echo "Now flush DNS cache:"
echo "  macOS: sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder"
echo "  Linux: sudo systemd-resolve --flush-caches"
echo ""
echo "Done! You can now access:"
echo "  - http://localhost:8080           (Login)"
echo "  - http://admin.localhost:8080    (Admin)"
echo "  - http://bnk.localhost:8080      (School 1 - Bangrak)"
echo "  - http://srb.localhost:8080      (School 2 - Saraburi)"
echo "  - http://nbr.localhost:8080      (School 3 - Nonthaburi)"
