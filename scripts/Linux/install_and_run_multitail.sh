#!/bin/bash

# Shell script to install multitail and view logs
# This script installs multitail if not present, then monitors /var/log/syslog and /var/log/auth.log
# chmod +x install_and_run_multitail.sh
# ./install_and_run_multitail.sh

echo "Starting multitail installation and log monitoring script..."

# Install multitail if not already installed
echo "Installing multitail..."
sudo apt-get update -y
sudo apt-get install -y multitail

# Check if installation was successful
if [ $? -eq 0 ]; then
    echo "Multitail installed successfully."
else
    echo "Failed to install multitail. Please check your system configuration."
    exit 1
fi

# Run multitail with the specified log files
echo "Running multitail to monitor /var/log/syslog and /var/log/auth.log..."
echo "Multitail will highlight and filter log entries for easy monitoring."
multitail /var/log/syslog /var/log/auth.log
