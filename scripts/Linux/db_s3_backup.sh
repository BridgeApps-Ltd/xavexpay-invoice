#!/bin/bash
##---------- Author : Mubinahmed Shaikh -----------------------------------------------------##
##---------- Purpose : Take cargofl db sql backup and uploads to S3 Bucket ------------------##
##---------- Tested on : Ubuntu 22/20/18 ----------------------------------------------------##
##---------- Updated version : v2.0 (Updated on 11th JUNE 2024) -----------------------------##
##-----NOTE: This script does't require root privileges, one could run the script directly --##
##----------- "sudo /bin/bash <ScriptName>" -------------------------------------------------##
##---- Requirements: Install and configure awscli and mysql-client --------------------------##

DATE=$(date +%Y-%m-%d-%H)
RDS_HOST="sample.cupuoeslvlzn.ap-south-1.rds.amazonaws.com"
RDS_PORT="3306"
RDS_USER="innoctive"
RDS_PASSWORD="password"
DB_NAME=cargofl
S3_BUCKET="cargofl-example-prod-dbbackups"
DB_IDENTIFIER="mysqldump_example-prod"
MINSANDSECS=$(date +%M-%S)

echo "Taking backup"
mysqldump -h $RDS_HOST -u $RDS_USER -p'samplepassword' $DB_NAME > $DB_IDENTIFIER_$DATE.sql

# Upload the mysqldump to the S3 bucket
echo "uploading to S3 Bucket"
aws s3 cp $DB_IDENTIFIER_$DATE.sql s3://$S3_BUCKET/$DB_IDENTIFIER-$DATE-$MINSANDSECS.sql

# Remove the local copy of the mysqldump
echo "deleting  unwanted files"
rm $DB_IDENTIFIER_$DATE.sql

echo -e "\n\t\t %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%" 
echo -e "\t\t   <>----------------------<> End OF THE SCRIPT  <>-------------------<>"
echo -e "\t\t %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%" 
