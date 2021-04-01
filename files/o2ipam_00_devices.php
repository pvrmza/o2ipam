#!/usr/bin/env php
<?php

/**
 * Export device from Observium to phpIPAM
 *
 * @subpackage cli
 * @author     Pablo Vargas <pvr.mza@gmail.com>
 * @copyright  GNU GPL v3.0
 *
 */

$base_dir = realpath(dirname(__FILE__) );
include($base_dir."/include/o2ipam_config.php");

# --------- phpIPAM functions ---------
#--------------------------------------
function get_ipamdeviceID($cadena="") { 
    global  $base_url,$api_key;

    $headers    = array(
        'Content-Type: application/json',
        sprintf('token: %s', $api_key)
      );
    $accion     = "devices/search/$cadena";
    $url        = "$base_url$accion";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = curl_exec($ch);
    $var = json_decode($res,true);
    $existe=0;
    if ( $var['success'] == "1" ) {
        $array=$var['data'];
        $cant=count($array);
        
        if ( $cant > 0 ) {            
            foreach ($array as $clave => $valor) {
                if ( $cadena == $valor['hostname'] ) {
                    $existe=$valor['id'];
                }
                if ( $cadena == $valor['ip'] ) {
                    $existe=$valor['id'];
                }
            }
        }
    }    
    return $existe;
}	
function add_ipamDevice($params) {
    global  $base_url,$api_key;

    $headers = array(
        'Content-Type: multipart/form-data',
        sprintf('token: %s', $api_key)
    );

    $accion     = "devices";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url$accion");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

    $res = curl_exec($ch);
    $var=json_decode($res,true);
    //print_r($var);    
    $hostname=$params['hostname'];
    $ip_addr=$params['ip_addr'];

    if ( $var['success'] == "1" ) {
        echo "Agregado con exito el equipo $hostname con el IP $ip_addr \n";
    }

    curl_close($ch);
}
#---------------------------------------------
function update_ipamDevice($device_id,$params) {
    global  $base_url,$api_key;
    $headers = array(
        'Content-Type: multipart/form-data',
        sprintf('token: %s', $api_key)
    );

    $accion     = "devices";
    $params[id]=$device_id ;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url$accion");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

    //print_r($params);
    $hostname=$params[hostname];
    $ip_addr=$params[ip_addr];

    $res = curl_exec($ch);
    $var=json_decode($res,true);
    //print_r($var);
    if ( $var['success'] == "1" ) {
        echo "Actualizado con exito el equipo $hostname con el IP $ip_addr \n";
    }

    curl_close($ch);
}

# --------- Observium functions ---------
#---------------------------------------------
function get_deviceIP($hostname="") {
	$ip_addr= shell_exec("/usr/bin/fping -A $hostname 2> /dev/null | awk '{ print $1}'");
	return($ip_addr);
}
function get_deviceInfo($deviceID="0") {
    global $observium_url;
	$sql="SELECT device_id, hostname, snmp_community, snmp_authlevel, snmp_authname, snmp_authpass, snmp_authalgo, snmp_cryptopass, snmp_cryptoalgo, snmp_context, snmp_version, snmp_port, snmp_timeout, snmp_retries, snmp_maxrep, sysDescr, version, hardware, vendor, os, asset_tag, serial FROM devices WHERE device_id='$deviceID' ;";

	// List Devices
	foreach (dbFetchRows($sql) as $device)
	{

		$ip_addr=get_deviceIP($device['hostname']);
		//print_r($device);
		
		if ($device['snmp_version']=="v2c") {
			$device['snmp_version']=2;
		}
		if ($device['snmp_version']=="v3") {
			$device['snmp_version']=3;
			$device['snmp_community']=$device['snmp_authname'];
		}

	    $params = array(
	                    'ip_addr' 				=> $ip_addr,
	                    'hostname' 				=> $device['hostname'],
	                    'custom_observium_id'   => $device['device_id'],
                        'custom_observium_url'  => $observium_url."device/device=".$device['device_id'],
	                    'snmp_queries' 			=> "get_system_info;get_arp_table;get_interfaces_ip",
	                    'snmp_port' 			=> $device['snmp_port'],
	                    'snmp_version' 			=> $device['snmp_version'],
	                    'snmp_community' 		=> $device['snmp_community'],
	                );
	    if (!(is_null($device['sysDescr'])) )            { $params['description']=$device['sysDescr'] ;}
	    if (!(is_null($device['snmp_timeout'])))         { $params['snmp_timeout']=$device['snmp_timeout'] ;}
	    if (!(is_null($device['snmp_authalgo'])))        { $params['snmp_v3_auth_protocol']=$device['snmp_authalgo'] ;}
	    if (!(is_null($device['snmp_authlevel'])))       { $params['snmp_v3_sec_level']=$device['snmp_authlevel'] ;}
	    if (!(is_null($device['snmp_v3_auth_pass'])))    { $params['snmp_authpass']=$device['snmp_authpass'] ;}
	    if (!(is_null($device['vendor'])))               { $params['custom_vendor']=$device['vendor'] ;}
	    if (!(is_null($device['hardware'])) )            { $params['custom_hardware']=$device['hardware'] ;}	    
	    if (!(is_null($device['version'])) )             { $params['custom_version']=$device['version'] ;}
	    if (!(is_null($device['asset_tag'])) )           { $params['custom_asset_tag']=$device['asset_tag'] ;}
	    if (!(is_null($device['serial'])) )              { $params['custom_serial']=$device['serial'] ;}

        if (!(is_null($device['os'])) )                  { 
            $params['custom_os']=$device['os'];
            if ($device['os']=="routeros") {
                $params['custom_admin_url']="winbox://".$device['hostname'];
            } elseif ($device['os']=="hh3c") {
                $params['custom_admin_url']="http://".$device['hostname'];
            } elseif ($device['os']=="fortigate") {
                $params['custom_admin_url']="https://".$device['hostname'];
            } elseif ($device['os']=="vmware") {
                $params['custom_admin_url']="https://".$device['hostname'];
            }
        }

	    return($params);

	}
}

function add_deviceID($deviceID) {
    // get info
	$info=get_deviceInfo($deviceID);

	$ip_addr=$info['ip_addr'];
	$hostname=$info['hostname'];
    
	if (!is_null($hostname)) {
        // check exist by hostname
		$id=get_ipamdeviceID($hostname);
		if ( $id != "0" ) {
            // update
			//echo "El dispositivo $hostname existe en IPAM con el ID $id\n";
			update_ipamDevice($id,$info);
			return 0;
		} 
        // check exist by ip address
		$id=get_ipamdeviceID($ip_addr);
		if ( $id != "0" ) {
            // update
			//echo "El dispositivo $hostname existe en IPAM con el IP $ip_addr con el ID $id\n";
			update_ipamDevice($id,$info);
			return 0;
		} 
        // add device
        //echo "Agregando el disposifivo $hostname \n";
		add_ipamDevice($info);
	} else {
		echo "El dispositivo $deviceID no existe en Observium \n";
	}
}

#---------------------------------------------------------
chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

if (count($argv) > 1)  { 
	$deviceID=$argv[1];
}

if (!is_null($deviceID)) {
	$sal=add_deviceID($deviceID);
} else {
	$sql="SELECT device_id FROM devices ORDER BY `last_discovered_timetaken` ASC;";
	foreach (dbFetchRows($sql) as $listdevice) {		
		$sal=add_deviceID($listdevice['device_id']);
	}
}

?>