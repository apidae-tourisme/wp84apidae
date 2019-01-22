<?php
/**
 * @package wp84apidae
 */
/*
Plugin Name: WordPress Apidae Plug-in
Plugin URI: http://vaucluseprovence-attractivite.com/
Description: Affiche des listes d'objets touristiques en provenance de l'API apidae
Version: 0.1b
Author: Michel CHOUROT
License: GPLv3
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You have received a copy of the GNU General Public License
along with this program in the LICENSE.txt file.

Copyright 2017 Vaucluse Provence Attractivité.
*/

// Evite l'appel direct du plugin hors environnement Wordpress
if ( !function_exists( 'add_action' )) {
	echo 'Erreur...';
	exit;
}

define( 'WP84APIDAE_VERSION', '1.0c' );
define( 'WP84APIDAE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP84APIDAE_PLUGIN_INC', plugin_dir_path( __FILE__ ).DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR );
define( 'WP84APIDAE_PLUGIN_JS', plugin_dir_path( __FILE__ ).DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR );
define( 'WP84APIDAE_PLUGIN_CSS', plugin_dir_path( __FILE__ ).DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR );

register_activation_hook( __FILE__, array( 'WP84Apidae', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP84Apidae', 'plugin_deactivation' ) );

require_once(WP84APIDAE_PLUGIN_INC.'class.wp84apidae.php');
require_once(WP84APIDAE_PLUGIN_INC.'class.wp84apidae-reqAPI.php');
require_once(WP84APIDAE_PLUGIN_INC.'class.wp84apidae-template.php');

//prise en compte de la règle de rewriting
add_action('init', array( 'WP84Apidae','add_wp84_rewrite'), 10, 0);
//initialisation globale du plugin
add_action( 'init', array( 'WP84Apidae', 'init' ) );
//préparation pour affichage des notices admin
add_action( 'admin_notices', array('WP84Apidae','do_admin_notice') );
//cron pour le nettoyage de cache
add_action('wp84apidae_dailyclear', array('WP84Apidae','do_clear_cache'));
//init interface d'admin
if ( is_admin()){
    require_once(WP84APIDAE_PLUGIN_INC.'class.wp84apidae-admin.php');
    add_action( 'init', array( 'WP84ApidaeAdmin', 'init' ) );
}
