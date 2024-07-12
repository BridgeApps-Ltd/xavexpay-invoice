#!/bin/sh

UBUNTU_CODENAME=$( \
   (egrep ^DISTRIB_CODENAME /etc/upstream-release/lsb-release || \
    egrep ^DISTRIB_CODENAME /etc/lsb-release) 2>/dev/null | \
   cut -d'=' -f2 )

wget --output-document=/etc/apt/sources.list.d/tilde.list http://os.ghalkes.nl/sources.list.d/${UBUNTU_CODENAME}.list && \
	apt-get --quiet update && \
	apt-get --yes --quiet --allow-unauthenticated install tilde-keyring && \
	apt-get --quiet update
