# o2ipam
Export data from Observium to phpIPAM

Observium is network monitoring with intuition. It is a low-maintenance auto-discovering network monitoring platform supporting a wide range of device types

phpIPAM is an open-source web IP address management (IPAM) and dcim data center infrastructure management (DCIM) software 


Together they are two applications that help us keep our inventory updated in real time. 

Observium discovers devices, networks and IP addresses, phpIPAM documents ... and these little bits of code keep the information up to date 

Site: [Observium homepage](https://www.observium.org/) <---> [phpIPAM homepage](http://phpipam.net)  

![Observium logo](https://www.observium.org/images/observium-brand.png) <---> ![phpIPAM logo](http://phpipam.net/wp-content/uploads/2014/12/phpipam_logo_small.png)


## Features
- [x] Export devices from Observium to phpIPAM (hardware info, os, version release, snmp configuration, etc... )
- [x] Export discovered network from Observium to phpIPAM
- [x] Registers in phpIPAM the IPs used by the existing devices in Observium 
- [x] Registers in phpIPAM the IP / MAC found in the ARP tables of any Observium devices 
- [x] In phpIPAM, link to the URL of the administration device (you may be need to register the winbox:// url of your browser to the winbox application, in the case of the Mikrotik)
- [x] In phpIPAM, link to the device in Observium

![o2ipam](https://user-images.githubusercontent.com/12079274/111393457-91d87d80-8697-11eb-94d7-6ae3f8173ed4.png)



## Deploy
 - Define this custom field in phpIPAM in the device section:
	 - admin_url -> varchar(255)
	 - observium_url -> varchar(255)
	 - observium_id -> int(5)
	 - vendor -> varchar(255)
	 - hardware -> text
	 - os -> varchar(32)
	 - version -> text
	 - asset_tag -> varchar(32)
	 - serial -> varchar(128)
- Define in phpIPAM a new section where to import all the networks discovered by observium 
- In phpIPAM, create a new api app 

- Clone this repository en /opt/observium 
- Edit o2ipam_config.php with app name y api key from phpIPAM or set environment variable with value

## Enviroment Variable
- phpipam_url
- observium_url
- phpipam_api
- phpipam_key
- phpipam_default_section




