<?php
/*
Plugin Name: Committee History
Plugin URI: https://www.wordpress.org
Description: Easily insert overviews of previous committee members on your association's website. View your personal committee history. Ideal for associations and clubs that want to show appreciation for their current and former volunteers.
Version: 1.0.0
Author: Justin van Steijn
Text Domain: committee-history
License: GNU GPL v2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*	Copyright 2016 Justin van Steijn

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// shortcode
//[committee_history sourcetable="bestuur" type="table"]
function committee_history_func( $atts_input ) {

	include_once "overview_generator.php";

	$commhist_overviewgenerator = new CommitteeHistoryOverviewGenerator( $atts_input ) ;
	$overview = $commhist_overviewgenerator->generate_overview( );
	
	return $overview;
}

// shortcode
//[committee_history_personal]
function committee_history_personal_func( ) {
	
	include_once "personalhistory_generator.php";	
	
	$commhist_pershistgen = new CommitteeHistoryPersonalHistoryGenerator;
	$pers_overview = $commhist_pershistgen->generate_personalhistory( );
	
	return $pers_overview;
}

add_shortcode( 'committee_history', 'committee_history_func' );

add_shortcode( 'committee_history_personal', 'committee_history_personal_func' );

// The setting menu things
if ( is_admin() ){ // admin actions

	include_once "settings.php";
	
	$committeehistory_settings = new CommitteeHistorySettings;
	
	add_action( 'admin_init', array( $committeehistory_settings, 'register_mysettings') );
	add_action( 'admin_menu', array( $committeehistory_settings, 'committee_history_menu' ) );
}

$committeehistory = new CommitteeHistory;

class CommitteeHistory {

	public function sort_data( $original_array ) {
	
		$data = array( );
		foreach ( $original_array as $line ) {
		
			$year_relative = $line[0]; # the relative year since foundation
			$committee = $line[1]; # committee of the person
			$name = $line[2]; # name of the person
	
			if ( !isset( $data[$year_relative] ) ) {
				$data[$year_relative] = array( ); 
			}
			if ( !isset ( $data[$year_relative]["$committee"] ) ) {
				$data[$year_relative]["$committee"] = array( );
			}

			array_push( $data[$year_relative]["$committee"], $name );
		}
	
		//reverse the data so newest committees are first
		$data = array_reverse( $data, true );	
	
		return $data;
	}

}
