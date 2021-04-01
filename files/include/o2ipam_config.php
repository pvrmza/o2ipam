<?php

# phpIPAM
$phpipam_url = "https://phpipam.local";     		 	// phpIPAM url
$phpipam_api_app_id = "myapi";                        	// application id
$phpipam_api_key = "my-api-key";           				// api key

# default section for discovered network
$phpipam_default_section="13";

# Observium URL
$observium_url="https://observium.local/";
# Observium DB

# ------------------ NO EDIT -----------------------#
# ----- environment variables have more weight -----#
if (!empty(getenv("phpipam_url"))) {
	$phpipam_url = getenv("phpipam_url") ;
} 
if (!empty(getenv("phpipam_api_app_id"))) {
	$phpipam_api_app_id = getenv("phpipam_api_app_id") ;
}
if (!empty(getenv("phpipam_api_key"))) {
	$phpipam_api_key = getenv("phpipam_api_key") ;
}
if (!empty(getenv("phpipam_default_section"))) {
	$phpipam_default_section = getenv("phpipam_default_section") ;
}
if (!empty(getenv("observium_url"))) {

	echo "entro -> ".getenv("observium_url") ."\n ";
	$observium_url = getenv("observium_url") ;
}


# phpIPAM API base_url 
$base_url = "$phpipam_url/api/$phpipam_api_app_id/";

# Dababase function
require($base_dir."/include/class.db.php");

define( 'DB_HOST', getenv("OBSERVIUM_db_host") ); // set database host
define( 'DB_USER', getenv("OBSERVIUM_db_user") ); // set database user
define( 'DB_PASS', getenv("OBSERVIUM_db_pass") ); // set database password
define( 'DB_NAME', getenv("OBSERVIUM_db_name") ); // set database name


function dbFetchRows($query="") {
	//Initiate the class
	$database = new DB();
	$results = $database->get_results( $query );

	return $results;
}




# ------------------ NO EDIT -----------------------#

?>