#!/bin/bash

# Configuration: Define constants for file paths, email details, and AWS region.
readonly TIME_FILE="/tmp/techchallenge_start_time"
readonly LOG_FILE="/var/cache/somewhere/logger/backpacks.log"
readonly SOURCE_EMAIL="no-reply@cargofl.com"
readonly RECIPIENT_EMAIL="mubin@cargofl.com"
readonly AWS_REGION="ap-south-1"
readonly TEAM="CargoFL Team"

# Usage function to display help message.
usage() {
  echo "Usage: $0 [start|finish|help]"
  exit 1
}

# Logging function to record messages with a timestamp.
log_message() {
  local message="$1"
  echo "$(date '+%Y-%m-%d %H:%M:%S') - $message" >> "$LOG_FILE"
}

# Function to send an email using AWS SES.
send_email() {
  local subject="$1"
  local body="$2"

  aws ses send-email \
    --from "$SOURCE_EMAIL" \
    --destination "ToAddresses=$RECIPIENT_EMAIL" \
    --message "Subject={Data=$subject},Body={Text={Data=$body}}" \
    --region "$AWS_REGION" >/dev/null 2>&1

  if [ $? -eq 0 ]; then
    echo "Notification has been sent to $TEAM"
    log_message "Email with subject '$subject' successfully sent to $RECIPIENT_EMAIL."
  else
    echo "Failed to send email to: $TEAM."
    log_message "Failed to send email with subject '$subject' to $RECIPIENT_EMAIL"
  fi
}

# Main script logic: Parse and handle commands.
if [ $# -ne 1 ]; then
  usage
fi

case "$1" in
  start)
    # Check if the user is root.
    if [ "$EUID" -eq 0 ]; then
      echo "Error: The TechChallenge cannot be started as the root user."
      log_message "Attempted to start TechChallenge as root user."
      exit 1
    fi

    # Record the start time.
    if date +%s > "$TIME_FILE"; then
      echo "TechChallenge has started."
      log_message "TechChallenge started."
      send_email "TechChallenge Started" "The TechChallenge has been started by user $USER."
    else
      echo "Error: Unable to record start time. Check permissions on $TIME_FILE."
      log_message "Failed to start TechChallenge."
      exit 1
    fi
    ;;

  finish)
    # Ensure the start time file exists.
    if [ -f "$TIME_FILE" ]; then
      start_time=$(<"$TIME_FILE")
      end_time=$(date +%s)
      elapsed_time=$((end_time - start_time))

      # Calculate elapsed time components.
      hours=$((elapsed_time / 3600))
      minutes=$(((elapsed_time % 3600) / 60))
      seconds=$((elapsed_time % 60))

      echo "TechChallenge has finished."
      echo "Time elapsed: ${hours}h ${minutes}m ${seconds}s"
      log_message "TechChallenge finished. Elapsed time: ${hours}h ${minutes}m ${seconds}s."

      # Send completion email.
      send_email "TechChallenge Finished" "The TechChallenge has been completed by user $USER. Time elapsed: ${hours}h ${minutes}m ${seconds}s."

      # Cleanup.
      rm -f "$TIME_FILE"
    else
      echo "Error: TechChallenge has not been started. Use 'start' command first."
      log_message "Attempted to finish without starting."
      exit 1
    fi
    ;;

  help)
    echo "Help request sent to $TEAM"
    send_email "TechChallenge Help Request" "The user $USER has requested help with the TechChallenge. Please assist them."
    ;;

  *)
    echo "Invalid command: $1"
    usage
    log_message "Invalid command used: $1"
    ;;
esac
