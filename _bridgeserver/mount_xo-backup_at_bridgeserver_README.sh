#! /bin/bash

# This  script mounts  the backup path in  BridgeServer > VM  Backup path  as a mount on this server
# Use this path /mnt/xoa-vm-backup - to create a "Remotes" connection  config in XOA - which is to be used in "Backups > New Backup" configuration  

# this command may fail, if this already exists. 
mkdir /mnt/xoa-vm-backups

# now use SSHFS to create the remote path    as mount using ssh
sudo sshfs -o allow_other,default_permissions bridgeserver:/xo-vm-backups /mnt/xoa-vm-backup
