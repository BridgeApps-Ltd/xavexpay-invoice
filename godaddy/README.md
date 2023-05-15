
### setup a crontab for running this file at every reboot or start ###

crontab -e

@reboot /root/updatedns.sh >/dev/null 2>&1
