#!/bin/bash

source config.ini
S3_BUCKET="$S3_BUCKET"
REPOSITORY_URL="$REPOSITORY_URL"
GIT_FOLDER_NAME="$GIT_FOLDER_NAME"

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

  # Get the current date in the format "YYYY-MM-DD"
  current_date=$(date +%Y-%m-%d)

  # Upload the compressed file to S3
  aws s3 cp "s3://$2/$current_date/cargofl.zip.gz" "$1"

  return 0
}

# Call the function with the path to the compressed file and S3 bucket name as arguments
restorefroms3 "/opt/archive/cargofl.zip.gz" "$S3_BUCKET"
