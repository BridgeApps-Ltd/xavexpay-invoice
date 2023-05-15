#!/bin/bash

source config.ini
DOMAIN="$DOMAIN"
HOST="$HOST"
TYPE="$TYPE"
KEY="$KEY"
SECRET="$SECRET"

SERVER_IP=`curl -s "https://api.ipify.org"`

GODADDY_IP=`curl -s -X GET -H "Authorization: sso-key ${KEY}:${SECRET}" "https://api.godaddy.com/v1/domains/${DOMAIN}/records/${TYPE}/${HOST}" | cut -d'[' -f 2 | cut -d']' -f 1 | jq -r '.data'`

if [ "$SERVER_IP" != "$GODADDY_IP" -a "$SERVER_IP" != "" ]; then
echo "Updating IP Address"
        curl -s -X PUT "https://api.godaddy.com/v1/domains/${DOMAIN}/records/${TYPE}/${HOST}" -H "Authorization: sso-key ${KEY}:${SECRET}" -H "Content-Type: application/json" -d "[{\"data\": \"${SERVER_IP}\"}]"
echo "Updated"
fi

if [ "$SERVER_IP" = "$GODADDY_IP" ]; then
echo "Server and Godaddy IP's are equal, no update is required"
fi
