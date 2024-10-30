<?php
/*  Copyright 2016 Justin van Steijn

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
class CommitteeHistoryOverviewGenerator {

	public function __construct ( $atts_input ) {

		$atts_default = array( "sourcetable" => NULL, "type" => "list" );
		$this->atts = shortcode_atts( $atts_default, $atts_input );
	}

	private function fetch_all_data( $table ) {
	
		global $wpdb;
	
		$full_table_name = $wpdb->prefix . "committeehistory_$table";

		$full_data_array = $wpdb->get_results(
			"SELECT year, committee, name
			FROM $full_table_name",
			ARRAY_N #request arrays in arrays
		);
		return $full_data_array;
	}

	public function generate_overview( ) {

		$source_table = $this->atts["sourcetable"];
		$type = $this->atts["type"];
		$year_type = get_option( "year_type" );

		$original_array = $this->fetch_all_data( $source_table );
		
		global $committeehistory;
		$sorted_data = $committeehistory->sort_data( $original_array );

		$overview = "";
		echo "<h2><a name=\"contents\"></a>Contents</h2>\r\n";

		foreach ( $sorted_data as $year_relative => $year_data ) {

			$startyear = get_option( "foundation_year" ) + $year_relative - 1;
			$endyear = $startyear + 1;
		
			// for in table of contents
			echo "<a href=\"#$startyear\">$startyear";
			if ( $year_type == 'college' ) {
				echo "&nbsp;-&nbsp;$endyear";
			}
			echo "</a>&nbsp;| \r\n";
			
			// for in the overview itself
			$overview .= "<h2><a name=\"$startyear\"></a>$startyear";
			if ( $year_type == 'college' ) {
				$overview .= "&nbsp;-&nbsp;$endyear";
			}
			$overview .= "</h2>\r\n";

			if ( $type == 'table' ) {
				$overview .= "<table border=\"1\" style=\"width:100%\">\r\n";
			} elseif ( $type == 'list' ) {
				$overview .= "<div id=\"col\">\r\n";
			}
			foreach ($year_data as $committee => $names) {
		
				if ( $type == 'table' ) {
					foreach ($names as $name)	{
						$overview .= "<tr><td>$name</td><td>$committee</td></tr>\r\n";
					}
				}	elseif ( $type == 'list' ) {
					$overview .= "<h4>$committee</h4>\r\n";
					$overview .= "<ul>\r\n";

					foreach ( $names as $name ) {
						$overview .= "<li>$name</li>\r\n";
					}
					$overview .= "</ul>\r\n";
				}
			}
			if ( $type == 'table' ) {	
				$overview .= "</table>\r\n";
			} elseif ( $type == 'list' ) {
				$overview .= "</div>"; 
			}
			
			$overview .= "<a href=\"#contents\">Jump to Contents</a>\r\n";
		}
	
		return $overview;
	}

}
