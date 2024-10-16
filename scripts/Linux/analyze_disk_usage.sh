#!/bin/bash

# Description:
# This script uses ncdu to analyze disk usage in the current directory (or a specified directory).

# Command:
# Using 'ncdu' to display an interactive view of disk usage.
ncdu .

# Explanation:
# 'ncdu'       : A disk usage analyzer that provides an interactive interface to navigate through directories.
# '.'          : Represents the current directory. You can change this to specify a different directory.

# Usage:
# Save this script as 'analyze_disk_usage.sh' and run it in the terminal using:
# ./analyze_disk_usage.sh
# You need to have 'ncdu' installed. To install it, use:
# sudo apt-get install ncdu  # For Debian/Ubuntu
# sudo yum install ncdu      # For RHEL/CentOS
