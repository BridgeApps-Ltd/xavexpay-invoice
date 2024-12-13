#!/bin/bash

# Variables
HOSTED_ZONE_ID="Z076966821393DJU8M6KK"
DOMAIN_NAME="uat.example.com"
NEW_IP=$(curl -s http://checkip.amazonaws.com)

# Route 53 Change Batch JSON
cat > change-batch.json <<EOF
{
  "Comment": "Updating A record to new IP ${NEW_IP} for ${DOMAIN_NAME}",
  "Changes": [
    {
      "Action": "UPSERT",
      "ResourceRecordSet": {
        "Name": "${DOMAIN_NAME}",
        "Type": "A",
        "TTL": 60,
        "ResourceRecords": [
          {
            "Value": "${NEW_IP}"
          }
        ]
      }
    }
  ]
}
EOF

# Update Route 53
aws route53 change-resource-record-sets \
    --hosted-zone-id "${HOSTED_ZONE_ID}" \
    --change-batch file://change-batch.json

# Clean up
rm change-batch.json
