#!/bin/bash

# Shell script to install nload and start monitoring network traffic and bandwidth usage
# This script installs nload if not already installed, then runs nload to monitor network interfaces.
# chmod +x network_monitor.sh
# ./network_monitor.sh

echo "Starting the nload installation and monitoring script..."

# Install nload if not already installed
echo "Installing nload..."
sudo apt-get update -y
sudo apt-get install -y nload

# Check if installation was successful
if [ $? -eq 0 ]; then
    echo "Nload installed successfully."
else
    echo "Failed to install nload. Please check your system configuration."
    exit 1
fi

# Run nload to monitor network bandwidth
echo "Running nload to monitor network traffic and bandwidth usage..."
echo "Use the arrow keys to switch between network interfaces."
nload
