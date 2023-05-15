#!/bin/bash

source config.ini
DOMAIN="$DOMAIN"
HOST="$HOST"
TYPE="$TYPE"
KEY="$KEY"
SECRET="$SECRET"

WanIP=`curl -s "https://api.ipify.org"`
echo $WanIP

GDIP=`curl -s -X GET -H "Authorization: sso-key ${KEY}:${SECRET}" "https://api.godaddy.com/v1/domains/${DOMAIN}/records/${TYPE}/${HOST}" | cut -d'[' -f 2 | cut -d']' -f 1 | jq -r '.data'`

if [ "$WanIP" != "$GDIP" -a "$WanIP" != "" ]; then
        curl -s -X PUT "https://api.godaddy.com/v1/domains/${DOMAIN}/records/${TYPE}/${HOST}" -H "Authorization: sso-key ${KEY}:${SECRET}" -H "Content-Type: application/json" -d "[{\"data\": \"${WanIP}\"}]"
fi
