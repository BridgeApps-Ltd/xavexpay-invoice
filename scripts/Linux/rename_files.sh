#!/bin/bash

# Shell script to install 'rename' and rename .txt files to .md using a Perl regular expression
# This script installs the 'rename' utility and then renames all .txt files in the current directory to .md.

echo "Starting the batch renaming script..."

# Install 'rename' if not already installed
echo "Installing rename..."
sudo apt-get update -y
sudo apt-get install -y rename

# Check if installation was successful
if [ $? -eq 0 ]; then
    echo "Rename utility installed successfully."
else
    echo "Failed to install rename. Please check your system configuration."
    exit 1
fi

# Perform the renaming operation
echo "Renaming all .txt files to .md in the current directory..."
rename 's/\.txt$/\.md/' *.txt

# Check if the rename operation was successful
if [ $? -eq 0 ]; then
    echo "Files renamed successfully."
else
    echo "Renaming failed. No .txt files may be present or there was an issue with the rename command."
fi
