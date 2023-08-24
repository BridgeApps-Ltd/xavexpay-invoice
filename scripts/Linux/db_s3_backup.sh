#!/bin/bash

#########################################################################
#Backup script for CARGOFL, that take sql backup and uploads to S3 Bucket.
#########################################################################

DATE=$(date +%Y-%m-%d-%H)
RDS_HOST="sample.cu0uoeslvlzn.ap-south-1.rds.amazonaws.com"
RDS_PORT="3306"
RDS_USER="innoctive"
RDS_PASSWORD="password"
DB_NAME=cargofl
S3_BUCKET="cargofl-decathlon-prod-dbbackups"
DB_IDENTIFIER="mysqldump_decathlon-prod"
MINSANDSECS=$(date +%M-%S)

echo "Taking backup"
mysqldump -h $RDS_HOST -u $RDS_USER -p'NAp8iW8RZavA5tTDi1Fm' $DB_NAME > $DB_IDENTIFIER_$DATE.sql

# Upload the mysqldump to the S3 bucket
echo "uploading to S3 Bucket"
aws s3 cp $DB_IDENTIFIER_$DATE.sql s3://$S3_BUCKET/$DB_IDENTIFIER-$DATE-$MINSANDSECS.sql

# Remove the local copy of the mysqldump
echo "deleting  unwanted files"
rm $DB_IDENTIFIER_$DATE.sql
