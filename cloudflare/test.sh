#!/bin/bash

# Replace with your actual Cloudflare credentials
API_TOKEN="dFL7Dg-ILqcHMYKbhVDHvGsp0i7pRPInacxGf94P"
ZONE_ID="d3a2e7705feed2ca20bb41e981f03ab7"

# all_records = `cat postUpdate.dnsentries`

# List of DNS names to update
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
        --data '{"type":"'"A"'","name":"'"pin"'","content":"'"$new_content"'","proxied":'"true"',"ttl":'"1"'}'

}


create_new_dns_record x 165.120.111.185 xyz