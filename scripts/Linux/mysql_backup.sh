#!/bin/bash

# Set variables mysql database connection and S3 bucket to upload
HOSTNAME=<ADD HOSTNAME>
USERNAME=<ADD DATABASE USERNAME>
PASSWORD='<ADD DATABASE PASSWORD IN SINGLE QUOTES'
DATABASE_NAME=<ADD DATABASE NAME>
DUMP_FILE=dump_$(date +%Y-%m-%d).sql
S3_BUCKET=<ADD S3 BUCKET NAME>
BUCKET_FOLDER=<ADD S3 BUCKER FOLDER NAME IF YOU HAVE>

# Take MySQL dump of RDS database
mysqldump -h $HOSTNAME -u $USERNAME -p$PASSWORD $DATABASE_NAME > $DUMP_FILE

#compress file with gzip 
gzip $DUMP_FILE

# Upload gzip dump file to S3 bucket
echo "Uploading mysql dump to s3 bucket..."
aws s3 cp $DUMP_FILE.gz s3://$S3_BUCKET/$BUCKET_FOLDER/$DUMP_FILE.gz

# Remove dump file from local machine
rm $DUMP_FILE.gz

echo "MySQL dump and S3 upload completed"
