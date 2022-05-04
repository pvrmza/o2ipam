#!/bin/sh

case $1 in
	device) php8 /var/www/html/o2ipam_00_devices.php ;;
	network) php8 /var/www/html/o2ipam_01_network.php ;;
	address) php8 /var/www/html/o2ipam_02_address.php ;;
	ipmac) php8 /var/www/html/o2ipam_03_ipmac.php ;;
	*) /usr/sbin/crond -f -l 8;;
esac
