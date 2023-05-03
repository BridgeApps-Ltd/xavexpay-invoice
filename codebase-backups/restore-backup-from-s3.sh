#!/bin/bash

source config.ini
S3_BUCKET="$S3_BUCKET"
REPOSITORY_URL="$REPOSITORY_URL"
GIT_FOLDER_NAME="$GIT_FOLDER_NAME"
CURRENT_DATE=$(date +%Y-%m-%d)
ARCHIVE_PATH="/opt/archive"

# function to upload file to s3 bucket
restorefroms3() {
  # Check if the AWS CLI is installed
  if ! command -v aws &> /dev/null
  then
      echo "Error: AWS CLI is not installed."
      return 1
  fi

  # Check if the user provided the S3 bucket name
  if [ -z "$2" ]
  then
      echo "Error: S3 bucket name is missing."
      return 1
  fi

  # Upload the compressed file to S3
  aws s3 cp "s3://$2/$3/$GIT_FOLDER_NAME.zip.gz" "$1"

  return 0
}

# Call the function with the path to the compressed file and S3 bucket name as arguments
restorefroms3 "$ARCHIVE_PATH/$GIT_FOLDER_NAME.zip.gz" "$S3_BUCKET" "$CURRENT_DATE"
