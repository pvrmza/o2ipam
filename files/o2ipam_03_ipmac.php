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
function search_ipamnetwork($network) {
    global  $base_url,$phpipam_api_key;
    $headers    = array(
        'Content-Type: application/json',
        sprintf('token: %s', $phpipam_api_key)
      );
    $accion     = "subnets/search/$network";
    $url        = "$base_url$accion";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = curl_exec($ch);
    $var=json_decode($res,true);
    $exist=0;
    if ( $var['success'] == "1" ) {
        $array=$var['data'];
        $exist=$array[0]['id'];
    } 
    return $exist;

    curl_close($ch);

}
#--------------------------------------
function search_ipamaddress($address) {
    global  $base_url,$phpipam_api_key;
    $headers    = array(
        'Content-Type: application/json',
        sprintf('token: %s', $phpipam_api_key)
      );
    $accion     = "addresses/search/$address/";
    $url        = "$base_url$accion";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $res = curl_exec($ch);
    $var=json_decode($res,true);
    $exist=0;
    if ( $var['success'] == "1" ) {
        $array=$var['data'];
        $exist=$array[0]['id'];
    } 
    return $exist;

    curl_close($ch);

}
#--------------------------------------
function get_ipamdeviceID($cadena="") { 
    global  $base_url,$phpipam_api_key;

    $headers    = array(
        'Content-Type: application/json',
        sprintf('token: %s', $phpipam_api_key)
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
#---------------------------------------------------------
function add_addressDevice($network, $address, $hostname="", $port="", $mac="") {
    global  $base_url,$phpipam_api_key;
    $ch = curl_init();
    
    $subnetId=search_ipamnetwork($network);
    $deviceId=get_ipamdeviceID($address);

    // si la red existe y el IP no es de un equipo en el inventario
    if ( $subnetId != 0 && $deviceId == 0) {
        $now = new DateTime('NOW');
        $params = array(
            'hostname' => "" ,
            'port' => $port,
            'mac' => $mac,
            'lastSeen' => $now->format('Y-m-d H:i:s'),
            'tag' => '2',            
        );
        
        
        $addressID=search_ipamaddress($address);
        if ( $addressID != 0 ) {
            // update
            $params['id']=$addressID;        
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        } else {
            // add 
            $params['subnetId']=$subnetId;        
            $params['ip']=$address; 
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $headers = array(
            'Content-Type: multipart/form-data',
            sprintf('token: %s', $phpipam_api_key)
        );

        $accion     = "addresses";
        curl_setopt($ch, CURLOPT_URL, "$base_url$accion");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $res = curl_exec($ch);
        $var=json_decode($res,true);
        if ( $var['success'] == "1" ) {
            if ( $addressID != 0 ) {
                echo "Update address $address in $network \n";
            } else {
                echo "Added address $address in $network \n";
            }
        } else {
            //print_r($var);
            echo "Error: $var[message] \n";
            print_r($params);
        }
        curl_close($ch);
    } else {
        echo "Subnet $network not exist OR IP $address is a registered devices \n" ;
    }
}

#---------------------------------------------------------
chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

if (count($argv) > 4 )  { 
	$network=$argv[1];
    $address=$argv[2];
    $hostname=$argv[3];
    $port=$argv[4];
    $mac=$argv[5];

	$sal=add_addressDevice($network, $address, $hostname, $port, $mac);

} else {
	$sql="SELECT a.ipv4_network, c.hostname, d.ifName, e.mac_address, e.ip_address FROM ipv4_networks as a, ipv4_addresses as b, devices as c, ports as d, ip_mac as e WHERE b.device_id=c.device_id AND a.ipv4_network_id=b.ipv4_network_id AND b.port_id=d.port_id AND d.port_id=e.port_id AND ipv4_prefixlen!=32 GROUP BY e.ip_address;";
	foreach (dbFetchRows($sql) as $listaddress) {		
		$sal=add_addressDevice($listaddress['ipv4_network'], $listaddress['ip_address'], $listaddress['hostname'], $listaddress['ifName'], $listaddress['mac_address']);
	}
}

?>