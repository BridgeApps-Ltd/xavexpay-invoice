#!/bin/bash

# Define the file paths
TIME_FILE="/tmp/techchallenge_start_time"
LOG_FILE="/var/cache/somewhere/logger/backpacks.log"

# Email details
SOURCE="no-reply@cargofl.com"
RECIPIENTS="mubin@cargofl.com"
AWS_REGION="ap-south-1"

# Check if the user provided an argument
if [ $# -eq 0 ]; then
  echo "Usage: techchallenge [start|finish|help]"
  exit 1
fi

# Function to log messages
log_message() {
  local message="$1"
  echo "$(date '+%Y-%m-%d %H:%M:%S') - $message" >> "$LOG_FILE"
}

# Function to send email
send_help_email() {
     aws ses send-email \
      --from "$SOURCE" \
      --destination "ToAddresses=${RECIPIENTS}" \
      --message "Subject={Data=CargoFL TechChallenge Help Request!},Body={Text={Data=The User $USER has requested help with the TechChallenge. Please assist him or her.}}" \
      --region "$AWS_REGION"
  log_message "Help email sent to CargoFL Team. The Team will support you as soon as possible"
}

# Handle the commands
case $1 in
  start)
    # Record the start time
    date +%s > "$TIME_FILE"
    echo "TechChallenge has started!"
    log_message "TechChallenge started."
    ;;

  finish)
    # Check if the start time exists
    if [ -f "$TIME_FILE" ]; then
      # Read the start time
      start_time=$(<"$TIME_FILE")
      # Get the current time
      end_time=$(date +%s)
      # Calculate the elapsed time in seconds
      elapsed_time=$((end_time - start_time))

      # Format the elapsed time as hours, minutes, and seconds
      hours=$((elapsed_time / 3600))
      minutes=$(((elapsed_time % 3600) / 60))
      seconds=$((elapsed_time % 60))

      echo "TechChallenge has finished!"
      echo "Time elapsed: ${hours}h ${minutes}m ${seconds}s"
      log_message "TechChallenge finished. Time elapsed: ${hours}h ${minutes}m ${seconds}s"

      # Remove the time file
      rm "$TIME_FILE"
    else
      echo "Error: TechChallenge has not been started. Use 'techchallenge start' first."
      log_message "Attempted to finish without starting."
      exit 1
    fi
    ;;

  help)
    echo "Help request sent to $HELP_EMAIL."
    send_help_email
    ;;

  *)
    echo "Invalid command. Use 'start', 'finish', or 'help'."
    log_message "Invalid command used: $1"
    exit 1
    ;;
esac
