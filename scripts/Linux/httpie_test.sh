#!/bin/bash

# Shell script to install HTTPie and demonstrate basic HTTP requests
# This script installs HTTPie if not already installed, then sends a GET and POST request as examples.

echo "Starting the HTTPie installation and testing script..."

# Install HTTPie if not already installed
echo "Installing HTTPie..."
sudo apt-get update -y
sudo apt-get install -y httpie

# Check if installation was successful
if [ $? -eq 0 ]; then
    echo "HTTPie installed successfully."
else
    echo "Failed to install HTTPie. Please check your system configuration."
    exit 1
fi

# Demonstrate a GET request
echo "Sending a GET request to https://example.com..."
http GET https://example.com

# Check if the GET request was successful
if [ $? -eq 0 ]; then
    echo "GET request completed successfully."
else
    echo "GET request failed. Please check the URL or your internet connection."
fi

# Demonstrate a POST request with JSON data
echo "Sending a POST request to https://example.com with JSON data (name=Ubuntu, version=22.04)..."
http POST https://example.com name=Ubuntu version=22.04

# Check if the POST request was successful
if [ $? -eq 0 ]; then
    echo "POST request completed successfully."
else
    echo "POST request failed. Please check the URL, data format, or your internet connection."
fi
