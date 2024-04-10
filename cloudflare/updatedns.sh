#!/bin/bash

# Function to display a formatted message
show_message() {
    echo "--------------------------------------------------"
    echo "$1"
    echo "--------------------------------------------------"
}

# Check if curl is installed
if ! command -v curl &> /dev/null; then
    echo "curl is not installed. Installing..."
    # Install curl using the appropriate package manager for your system
    # For example, on Debian-based systems (like Ubuntu), you can use:
    sudo apt-get install -y curl
fi

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo "jq is not installed. Installing..."
    # Install jq using the appropriate package manager for your system
    # For example, on Debian-based systems (like Ubuntu), you can use:
    sudo apt-get install -y jq
fi

# Source configuration variables
source .env

# Confirm the Cloudflare token is working correctly
show_message "Checking Cloudflare Token status"
response=$(curl -s -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
     -H "Authorization: Bearer $cloudflare_auth_key" \
     -H "Content-Type:application/json")

# Extract status and message using jq
status=$(echo "$response" | jq -r '.result.status')
message=$(echo "$response" | jq -r '.messages[0].message')

# Output status and message
echo "Status: $status"
echo "Message: $message"

# Get the current public IP address
addressip=$(curl -s -X GET https://checkip.amazonaws.com)

echo "Current IP is $addressip"

# Loop through each DNS record
for dnsrecord in "${dnsrecords[@]}"; do
    echo "Checking DNS record $dnsrecord"
    # Check if the DNS record is already set to the current IP address
    if host "$dnsrecord" 1.1.1.1 | grep "has address" | grep "$addressip"; then
      echo "$dnsrecord is currently set to $addressip; no changes needed"
    else
      show_message "Updating DNS record $dnsrecord"
      
      # Get the zone ID for the requested zone
      zoneid=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones?name=$zone&status=active" \
        -H "X-Auth-Email: $cloudflare_auth_email" \
        -H "Authorization: Bearer $cloudflare_auth_key" \
        -H "Content-Type: application/json" | jq -r '{"result"}[] | .[0] | .id')

      echo "Zoneid for $zone is $zoneid"

      # Get the DNS record ID
      dnsrecordid=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$zoneid/dns_records?type=A&name=$dnsrecord" \
        -H "X-Auth-Email: $cloudflare_auth_email" \
        -H "Authorization: Bearer $cloudflare_auth_key" \
        -H "Content-Type: application/json" | jq -r '{"result"}[] | .[0] | .id')

      echo "DNSrecordid for $dnsrecord is $dnsrecordid"

      # Update the DNS record
      curl -s -X PUT "https://api.cloudflare.com/client/v4/zones/$zoneid/dns_records/$dnsrecordid" \
        -H "X-Auth-Email: $cloudflare_auth_email" \
        -H "Authorization: Bearer $cloudflare_auth_key" \
        -H "Content-Type: application/json" \
        --data "{\"type\":\"A\",\"name\":\"$dnsrecord\",\"content\":\"$addressip\",\"ttl\":1,\"proxied\":true}" | jq
    fi
done
