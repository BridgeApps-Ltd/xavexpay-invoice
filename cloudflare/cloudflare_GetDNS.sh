#!/bin/bash

# This Scripts gets all the DNS entries from Cloudflare for the Domain mentioned
# Get these IDS from Cloudflare

# ============ EDITABLE SECTION ========================

zone_id='d3a2e7705feed2ca20bb41e981f03ab7'
acct_id='454ef5decd9082e33ea9e69b502d4a49'
auth_token='dFL7Dg-ILqcHMYKbhVDHvGsp0i7pRPInacxGf94P'

# ============ DO NOT TOUCH CODE BELOW ========================

curl -s --request GET \
  --url https://api.cloudflare.com/client/v4/zones/${zone_id}/dns_records \
  --header "Authorization: Bearer ${auth_token}" \
  --header 'Content-Type: application/json' \
  --header 'X-Auth-Email: ' \
  | jq '.result[] | {id: .id, name: .name, content: .content}'

echo 



