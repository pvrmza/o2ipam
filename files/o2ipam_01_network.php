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
    global  $base_url,$api_key;
    $headers    = array(
        'Content-Type: application/json',
        sprintf('token: %s', $api_key)
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
        $exist=1;
    } 
    return $exist;

    curl_close($ch);

}
#---------------------------------------------------------
function add_network($network,$description="Agregada a mano") {
    global  $base_url,$api_key,$default_section;
    // check exist
    $existe=search_ipamnetwork($network);

    if ($existe==0) {
        // agregar red
        $var = explode("/", $network);
        $subnet=$var[0];
        $mask=$var[1];

        //echo "no existe. agregar subnet $subnet con mascara $mask \n" ;
        $params = array(
            'subnet' => $subnet,
            'mask' => $mask,
            'sectionId' => $default_section ,
            'description' => $description,
        );

        $headers = array(
            'Content-Type: multipart/form-data',
            sprintf('token: %s', $api_key)
        );

        $accion     = "subnets";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$base_url$accion");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $res = curl_exec($ch);
        $var=json_decode($res,true);
        if ( $var['success'] == "1" ) {
            echo "Added subnet $subnet/$mask \n";
        } else {
            //print_r($var);
            echo "Error - $var[message] \n";
        }
        curl_close($ch);
    } else {
        echo "Subnet $network exist \n"; 
    }


}

#---------------------------------------------------------
chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

if (count($argv) > 1)  { 
	$network=$argv[1];
    $description=$argv[2];
}

if (!is_null($network)) {
	$sal=add_network($network,$description);
} else {
	$sql="SELECT a.ipv4_network,CONCAT('Discovered from device ',c.hostname,' (',c.sysName,') with IP address ',b.ipv4_address) as description FROM ipv4_networks as a, ipv4_addresses as b, devices as c WHERE b.device_id=c.device_id AND a.ipv4_network_id=b.ipv4_network_id AND ipv4_prefixlen!=32 AND ipv4_prefixlen!=0 ORDER BY ipv4_network";
	foreach (dbFetchRows($sql) as $listdevice) {		
		$sal=add_network($listdevice['ipv4_network'],$listdevice['description']);
	}
}

?>