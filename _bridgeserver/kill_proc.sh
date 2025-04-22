#!/bin/bash

if [ -z "$1" ]; then
  echo "Usage: $0 <process_name or command_pattern>"
  exit 1
fi

PROCESS_NAME="$1"
SCRIPT_PID=$$

echo "🔍 Getting list of processes to kill for pattern: '$PROCESS_NAME'"
echo "--------------------------------------------"

# Get matching processes, excluding:
# - this script's own process ($$)
# - grep itself
PROCESS_LINES=$(ps aux | grep "$PROCESS_NAME" | grep -v grep | grep -v "$SCRIPT_PID")

if [ -z "$PROCESS_LINES" ]; then
  echo "❌ No process matching '$PROCESS_NAME' found."
  exit 0
fi

# Print matched processes
echo "$PROCESS_LINES" | while IFS= read -r line; do
  echo "$line"
done

echo "--------------------------------------------"
echo

# Extract and loop through each PID
echo "$PROCESS_LINES" | awk '{print $2}' | while read -r PID; do
  echo "🛑 Killing process with PID: $PID"
  kill -9 "$PID" 2>/dev/null

  # Wait until the process is actually terminated
  while kill -0 "$PID" 2>/dev/null; do
    echo "⏳ Waiting for PID $PID to exit..."
    sleep 0.5
  done

  echo "✅ Process $PID terminated."
done

echo
echo "🔁 Final check - listing remaining matching processes:"
echo "--------------------------------------------"
ps aux | grep "$PROCESS_NAME" | grep -v grep | grep -v "$SCRIPT_PID"
echo "--------------------------------------------"
