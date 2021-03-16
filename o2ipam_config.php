<?php

# phpIPAM
$phpipam_url = "https://phpipam.server.local";      	// phpIPAM url
$phpipam_api = "myapi";                           	// application id
$phpipam_key = "my-api-key";           				// api key

# default section for discovered network
$phpipam_default_section="13";


# Observium URL
$observium_url="https://observium.server.local/";


# ------------------ NO EDIT -----------------------#
if (!is_null(getenv("phpipam_url"))) {
	$api_url = getenv("phpipam_url") ;
} else {
	$api_url = $phpipam_url ;
}
if (!is_null(getenv("phpipam_api"))) {
	$api_app_id = getenv("phpipam_api") ;
} else {
	$api_app_id = $api_app_id ;
}
if (!is_null(getenv("phpipam_key"))) {
	$api_key = getenv("phpipam_key") ;
} else {
	$api_key = $api_key ;
}
if (!is_null(getenv("phpipam_default_section"))) {
	$default_section = getenv("phpipam_default_section") ;
} else {
	$default_section = $default_section ;
}
if (!is_null(getenv("observium_url"))) {
	$observium_url = getenv("observium_url") ;
} else {
	$observium_url = $observium_url ;
}

# observium function
$base_url   = "$api_url/api/$api_app_id/";
include("../includes/sql-config.inc.php");
# ------------------ NO EDIT -----------------------#

?>