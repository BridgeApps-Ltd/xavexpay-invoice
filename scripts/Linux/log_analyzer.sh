#!/bin/bash

# Log Analyzer & Report Generator Script
# --------------------------------------
# This Bash script automates the analysis of log files and generates daily summary reports.
# Key Features:
# 1. Error Count: Scans the log file and counts occurrences of "ERROR" or "Failed".
# 2. Top 5 Error Messages: Identifies the most frequent error messages to help prioritize issues.
# 3. Critical Events: Finds lines containing "CRITICAL" and prints them with their line numbers.
# 4. Summary Report: Automatically generates a report including:
#    - Date of analysis
#    - Log file name
#    - Total lines processed
#    - Total error count
#    - Top 5 error messages
#    - List of critical events (with line numbers)

# How to Run:
# 1. Save the script as log_analyzer.sh.
# 2. Make the script executable: chmod +x log_analyzer.sh
# 3. Run the script with the path to the log file as an argument:
#    ./log_analyzer.sh /path/to/your/logfile.log
# 4. The script will output a summary report in the current directory
#    with a filename like log_report_YYYY-MM-DD.txt, containing analysis details.

# Define the log file (can be passed as an argument)
LOG_FILE=$1

# Check if a log file is provided
if [ -z "$LOG_FILE" ]; then
    echo "Usage: $0 <log_file>"
    exit 1
fi

# Check if the log file exists
if [ ! -f "$LOG_FILE" ]; then
    echo "Log file not found!"
    exit 1
fi

# Variables for the report
DATE=$(date '+%Y-%m-%d')
TOTAL_LINES=$(wc -l < "$LOG_FILE")
ERROR_COUNT=$(grep -E 'ERROR|Failed' "$LOG_FILE" | wc -l)
CRITICAL_LINES=$(grep -n "CRITICAL" "$LOG_FILE")

# Function to get the top 5 most frequent error messages
function top_5_errors {
    grep -E 'ERROR|Failed' "$LOG_FILE" | sort | uniq -c | sort -nr | head -5
}

# Generate the report
REPORT="log_report_${DATE}.txt"
{
    echo "Log Analysis Report - $DATE"
    echo "Log File: $LOG_FILE"
    echo "Total Lines Processed: $TOTAL_LINES"
    echo "Total Errors Found (ERROR or Failed): $ERROR_COUNT"
    echo ""
    echo "Top 5 Error Messages:"
    top_5_errors
    echo ""
    echo "Critical Events (with line numbers):"
    if [ -z "$CRITICAL_LINES" ]; then
        echo "No critical events found."
    else
        echo "$CRITICAL_LINES"
    fi
} > "$REPORT"

echo "Log analysis complete. Report generated: $REPORT"
