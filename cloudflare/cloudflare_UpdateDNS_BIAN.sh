#!/bin/bash

# ============ EDITABLE SECTION ========================

# Replace with your actual Cloudflare credentials
API_TOKEN="dFL7Dg-ILqcHMYKbhVDHvGsp0i7pRPInacxGf94P"
ZONE_ID="d3a2e7705feed2ca20bb41e981f03ab7"

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
update_dns_record() {
    local record_id=$1
    local new_content=$2
    local domain_name=$3

    # Delete first
    echo "... Deleting : $domain_name "
    response=$(curl -s -w "%{http_code}" --request DELETE  --url "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records/$record_id"  \
	-H "X-Auth-Email: info@bridgeapps.co.uk" \
	-H "Authorization: Bearer $API_TOKEN" \
        -H 'Content-Type: application/json')

    # Extract the body and the status code
    # ... remove #200 etc code form response end afte the JSON response
    body=$(echo "$response" | sed 's/[0-9]\{3\}$//') 

    # get the last tail end number
    status_code=$(echo "$response" | sed 's/.*\(...\)$/\1/')

    # Extract the "success" value from the body using jq
    success=$(echo "$body" | jq -r '.success')

    echo "... Deletion of domain name : $domain_name - HTTP response code : $body - $status_code - $success :::  $response"

    # Check if the HTTP status code is 200
    if [ "$status_code" -eq 200 ] && [ "$success" == "true" ]; then
        echo "... Creating Domain Name : $domain_name with new IP $new_content"
        curl -s -X POST "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records/" \
            -H "X-Auth-Email: info@bridgeapps.co.uk" \
            -H "Authorization: Bearer $API_TOKEN" \
            -H "Content-Type: application/json"  \
            --data '{"type":"'"A"'","name":"'"pin"'","content":"'"$new_content"'","proxied":'"true"',"ttl":'"1"'}'
    else
        echo "... ERROR: Deletion of record from DNS for record_id $record_id failed. Exiting"
        exit 1; 
    fi

}

# Get all DNS records and filter relevant ones
echo "... Fetching Entries from DNS Server into file preUpdate.dnsentries" 
all_records=$(get_dns_records "preUpdate.dnsentries")


echo "... Updating records"

# Iterate through the names array and update the records
for name in "${NAMES[@]}"; do
    record=$(echo "$all_records" | jq -c --arg NAME "$name" 'select(.name == $NAME)')
    if [ -n "$record" ]; then
        record_id=$(echo "$record" | jq -r '.id')
        old_content=$(echo "$record" | jq -r '.content')
        echo "... Updating $name (Record ID: $record_id) from $old_content to $NEW_IP"
        update_dns_record "$record_id" "$NEW_IP" "$name"
    else
        echo "... ERROR: No record found for $name"
    fi
done

# Get updated DNS records
echo "... Fetching Entries from DNS Server into file postUpdate.dnsentries" 
updated_records=$(get_dns_records "postUpdate.dnsentries")
echo "... Updated all DNS records"

# Show previous and new IPs
for name in "${NAMES[@]}"; do
    old_record=$(echo "$all_records" | jq -c --arg NAME "$name" 'select(.name == $NAME)')
    if [ -n "$old_record" ]; then
        old_content=$(echo "$old_record" | jq -r '.content')
        echo "$name: Old IP: $old_content, New IP: $NEW_IP"
    fi
done
