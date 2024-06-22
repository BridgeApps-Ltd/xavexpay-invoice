#!/bin/bash

# Define color codes
RED='\033[0;31m'  # Red color
GREEN='\033[0;32m'  # Green color
NC='\033[0m'     # No color

# Function to check and print the status
check_service() {
  local service_name="$1"
  local status="$(systemctl is-active --quiet "$service_name" && echo -e "${GREEN}[ACTIVE]${NC}" || echo -e "${RED}[INACTIVE]${NC}")"
  printf "%-3s %-20s %-15s\n" "$count." "$service_name:" "$status"
  ((count++)) # Increment counter
}

# Print table headers
printf "%-3s %-20s %-15s\n" "No." "Service Name" "Status"
echo "------------------------------------------"

# Check the status of each service
count=1
check_service "tomcat"
check_service "nginx"
check_service "php8.2-fpm"
check_service "podx-payment"
check_service "gmp-app"
