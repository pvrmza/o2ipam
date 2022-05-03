<?php

# phpIPAM

$phpipam_url=getenv("phpipam_url");
$phpipam_api_app_id=getenv("phpipam_api_app_id");
$phpipam_api_key=getenv("phpipam_api_key");

$phpipam_default_section=getenv("phpipam_default_section");
$observium_url=getenv("observium_url");

define( 'DB_HOST', getenv("db_host") ); // set database host
define( 'DB_USER', getenv("db_user") ); // set database user
define( 'DB_PASS', getenv("db_pass") ); // set database password
define( 'DB_NAME', getenv("db_name") ); // set database name


# ------------------ NO EDIT -----------------------#
# ----- environment variables have more weight -----#

# phpIPAM API base_url 
$base_url = "$phpipam_url/api/$phpipam_api_app_id/";

# Dababase function
require($base_dir."/include/class.db.php");


function dbFetchRows($query="") {
        //Initiate the class
        $database = new DB();
        $results = $database->get_results( $query );

        return $results;
}

# ------------------ NO EDIT -----------------------#
?>
