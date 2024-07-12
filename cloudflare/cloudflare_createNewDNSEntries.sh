#!/bin/bash

# ============ EDITABLE SECTION ========================

#----- Update these details as needed ----
# Replace with your actual Cloudflare credentials
API_TOKEN="dFL7Dg-ILqcHMYKbhVDHvGsp0i7pRPInacxGf94P"
ZONE_ID="d3a2e7705feed2ca20bb41e981f03ab7"

# ---- Update *EVERYTIME* based on requirement
# List of DNS names to update
NAMES=("api.bridgeapps.co.uk" "apidesigner.bridgeapps.co.uk" "bian-auth.bridgeapps.co.uk" "bian-bank.bridgeapps.co.uk" "bian.bridgeapps.co.uk" "bian-customer-management.bridgeapps.co.uk" "bian-dashboard.bridgeapps.co.uk" "bian-document.bridgeapps.co.uk" "bian-document-management.bridgeapps.co.uk" "bian-party.bridgeapps.co.uk" "bian-product.bridgeapps.co.uk" "bian-product-management.bridgeapps.co.uk" "bian-sales.bridgeapps.co.uk" "bian-sales-management.bridgeapps.co.uk" "bian-session-dialogue.bridgeapps.co.uk" "bian-trace.bridgeapps.co.uk" "jwt-revoker.bridgeapps.co.uk" "kibana.bridgeapps.co.uk")


# ============ DO NOT TOUCH CODE BELOW ========================


# Ensure a new IP is provided as an argument
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <NEW_IP>"
    exit 1
fi

NEW_IP=$1


# Function to get all DNS records
get_dns_records() {
    local output_file=$1  
    curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records" \
        -H "Authorization: Bearer $API_TOKEN" \
        -H "Content-Type: application/json" \
        | jq '.result[] | {id: .id, name: .name, content: .content}' | tee "$output_file"
}

# Function to update a DNS record
create_new_dns_record() {
    local record_id=$1
    local new_content=$2
    local domain_name=$3

    echo "... Creating Domain Name : $domain_name with IP $new_content "
    curl -s -X POST "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records/" \
        -H "X-Auth-Email: info@bridgeapps.co.uk" \
        -H "Authorization: Bearer $API_TOKEN" \
        -H "Content-Type: application/json" \
        --data '{"type":"'"A"'","name":"'"$domain_name"'","content":"'"$new_content"'","proxied":'"true"',"ttl":'"1"'}'

}

# Get all DNS records and filter relevant ones
echo "... Fetching Entries from DNS Server into file preUpdate.dnsentries" 
all_records=$(get_dns_records "preUpdate.dnsentries")

# echo "... Creating records : $all_records"
echo "... Creating records"

# Iterate through the names array and update the records
for name in "${NAMES[@]}"; do
    record=$(echo "$all_records" | jq -c --arg NAME "$name" 'select(.name == $NAME)')
    echo "... ... Record search result :$record:$name:"
    if [ -n "$record" ]; then
        echo "... ... Record for DNS entry domain name - "$name" - already exists, ignoring Create, execute Update Script"
    else
        echo ""
        create_new_dns_record "$record_id" "$NEW_IP" "$name"
    fi
done

# Get updated DNS records
echo ""
echo "... Fetching Entries from DNS Server into file postUpdate.dnsentries" 
updated_records=$(get_dns_records "postUpdate.dnsentries" >> /dev/null)
echo "... Created all DNS records"