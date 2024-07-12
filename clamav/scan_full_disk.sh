#!/bin/bash
# scans entire disk and then moves all found virus to this folder

# clamscan -r /
sudo clamscan -r --move=/ /home/kurudi/virus-found

