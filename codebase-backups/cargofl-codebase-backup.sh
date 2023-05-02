#!/bin/bash

source config.ini
S3_BUCKET="$S3_BUCKET"
REPOSITORY_URL="$REPOSITORY_URL"
GIT_FOLDER_NAME="$GIT_FOLDER_NAME"

#cleaning old compressed file
rm /opt/archive/$GIT_FOLDER_NAME.zip.gz

# Function to clone/download a github repository
downloadcodebase() {
  # Check if the user provided the GitHub repository URL
  if [ -z "$1" ]
  then
      echo "Error: GitHub repository URL is missing please provide it in config.ini file."
      return 1
  fi

  # Read the GitHub ID and password/token from config.ini file
  source config.ini
  if [ -z "$GITHUB_ID" ] || [ -z "$GITHUB_TOKEN" ]
  then
      echo "Error: GitHub ID and password/token are missing in config.ini file."
      return 1
  fi

  # Set the GitHub credentials for authentication
  git config --global credential.helper store
  echo "https://$GITHUB_ID:$GITHUB_TOKEN@github.com" > ~/.git-credentials

  # Clone the repository
  git clone "$1"
  return 0
}

# Call the function with the GitHub repository URL as an argument
downloadcodebase "$REPOSITORY_URL"

createzip() {
  # Check if the user provided the GitHub repository directory
  if [ -z "$1" ]
  then
      echo "Error: GitHub repository directory is missing."
      return 1
  fi

  # Check if the user provided the destination directory
  if [ -z "$2" ]
  then
      echo "Error: Destination directory is missing."
      return 1
  fi

  # Navigate to the GitHub repository directory
  cd "$1"

  # Create a tar archive
  tar -czvf $GIT_FOLDER_NAME.tar.gz .

  # Create a zip archive of the tar archive
  zip $GIT_FOLDER_NAME.zip $GIT_FOLDER_NAME.tar.gz

  # Compress the zip archive using gzip
  gzip -9 $GIT_FOLDER_NAME.zip

  # Move the compressed zip archive to the destination directory
  mv $GIT_FOLDER_NAME.zip.gz "$2"

  # Clean up the temporary files
  rm -f $GIT_FOLDER_NAME.tar.gz

  # Return success
  return 0
}

# Call the function with the GitHub repository directory and destination directory as arguments
createzip "$GIT_FOLDER_NAME" "/opt/archive/"

# function to upload file to s3 bucket
uploads3() {
  # Check if the AWS CLI is installed
  if ! command -v aws &> /dev/null
  then
      echo "Error: AWS CLI is not installed."
      return 1
  fi

  # Check if the user provided the path to the compressed file
  if [ -z "$1" ]
  then
      echo "Error: Path to compressed file is missing."
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
  aws s3 cp "$1" "s3://$2/$current_date/"

  return 0
}

# Call the function with the path to the compressed file and S3 bucket name as arguments
uploads3 "/opt/archive/$GIT_FOLDER_NAME.zip.gz" "$S3_BUCKET"

#removing git folder
rm -rf /root/$GIT_FOLDER_NAME
