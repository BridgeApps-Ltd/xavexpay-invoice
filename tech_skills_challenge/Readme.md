TechChallenge Script

Overview

The TechChallenge script is a utility to manage a tech challenge process. It tracks the start and finish times, logs activities, and sends notifications via email. This guide explains how to set up and use the script, including converting it into a standalone binary executable.

Setup Instructions

Prerequisites

Create Log Directory

sudo mkdir -p /var/cache/somewhere/logger
sudo chmod -R 777 /var/cache/somewhere/logger

Set TimeZone

sudo timedatectl set-timezone Asia/Kolkata

Install Required Packages

Update and install dependencies:

sudo apt-get update
sudo apt-get install shc build-essential

Script Compilation

To enhance security and portability, the script can be converted into a binary executable using shc.

Step 1: Compile the Script

Navigate to the directory where the techchallenge script is located.

Run the following command to compile the script:

shc -f techchallenge

This will generate two files:

techchallenge.x: The compiled binary.

techchallenge.x.c: The generated C source code (optional).

Step 2: Rename and Move the Binary

Rename the binary for convenience:

mv techchallenge.x techchallenge

Move the binary to a directory in your PATH (e.g., /usr/local/bin):

sudo mv techchallenge /usr/local/bin/

Usage

Commands

Start the TechChallenge

techchallenge start

Logs the start time.

Sends an email notification to the configured recipient.

Finish the TechChallenge

techchallenge finish

Calculates and logs the elapsed time.

Sends an email notification with the completion details.

Request Help

techchallenge help

Sends a help request email to the configured recipient.

Invalid Command

Running the script with an invalid command will show the usage message:

Usage: techchallenge [start|finish|help]

Notes

The script prevents execution as the root user for safety.

Logs are saved to /var/log/techchallenge.log.

Important Information

Email Notifications:

Uses AWS SES to send emails.

Ensure AWS CLI is configured with valid credentials and permissions.

Recompilation:

If you modify the script, recompile it to reflect the changes in the binary.

Troubleshooting

AWS SES Errors:

Verify AWS CLI credentials.

Check if the configured AWS_REGION is correct.

Permissions Issues:

Ensure the log directory and files have the necessary permissions:

sudo chmod -R 777 /var/cache/somewhere/logger

Script Not Found:

Ensure the binary is moved to a directory in your PATH (e.g., /usr/local/bin).

Author

Developed by the CargoFL Team. Contact no-reply@cargofl.com for assistance.
