#!/bin/bash
##---------- Author : Mubinahmed Shaikh -----------------------------------------------------##
##---------- Purpose : To update the new server ip into godaddy dns -------------------------##
##---------- Tested on : RHEL8/7/6, Ubuntu20/18/16, CentOS , --------------------------------##
##---------- Boss6(Debian) variants. It may work on other vari as well, but not tested. -----##
##---------- Updated version : v2.0 (Updated on 11th JUNE 2024) -----------------------------##
##-----NOTE: This script requires root privileges, otherwise one could run the script -------##
##---- as a sudo user who got root privileges. ----------------------------------------------##
##----------- "sudo /bin/bash <ScriptName>" -------------------------------------------------##

source config.ini
DOMAIN="$DOMAIN"
HOST="$HOST"
TYPE="$TYPE"
KEY="$KEY"
SECRET="$SECRET"

### Get the server ip ###
SERVER_IP=`curl -s "https://api.ipify.org"`

### Get the Current godaddy ip for the subdomain.domain ###
GODADDY_IP=$(curl -s -X GET -H "Authorization: sso-key ${KEY}:${SECRET}" \
    "https://api.godaddy.com/v1/domains/${DOMAIN}/records/${TYPE}/${HOST}" \
    | cut -d'[' -f 2 \
    | cut -d']' -f 1 \
    | jq -r '.data')


### Checking if both server and godaddy ip's are not similar and if they're not similar update the server ip ###
if [ "$SERVER_IP" != "$GODADDY_IP" -a "$SERVER_IP" != "" ]; then
curl -s -X PUT "https://api.godaddy.com/v1/domains/${DOMAIN}/records/${TYPE}/${HOST}" \
    -H "Authorization: sso-key ${KEY}:${SECRET}" \
    -H "Content-Type: application/json" \
    -d "[{\"data\": \"${SERVER_IP}\"}]"

echo "Updated new ip address $SERVER_IP in godaddy for domain $HOST.$DOMAIN as Type: $TYPE Record"
fi

### Checking if both server and godaddy ip's are similar ###
if [ "$SERVER_IP" = "$GODADDY_IP" ]; then
echo "Server and Godaddy IP's are equal $SERVER_IP = $GODADDY_IP, no update is required"
fi

echo -e "\n\t\t %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%" 
echo -e "\t\t   <>----------------------<> End OF THE SCRIPT  <>-------------------<>"
echo -e "\t\t %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%" 
