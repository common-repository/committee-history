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
class CommitteeHistoryPersonalHistoryGenerator {

	public function generate_personalhistory( ) {
	
		if ( isset( $_POST['SubmitButton'] ) ) { 
			$name = $_POST["member_name"];
			$this->lookup_committees( $name );
		}
		?>

		<h3>Look up member committee history</h3>

		<div>
			<form action="" method="post"> 
			<input type="text" name="member_name" placeholder="Full name" required>
			<input type="submit" name="SubmitButton" class="button" value="Look up committees">
			</form>
		</div>

		<style> 
			span.roundedbox {
				border: 2px solid #77216f;
				padding: 2px 15px; 
				background: #eadd4f;
				border-radius: 10px;
				display: inline-block;
			}
		</style>
	
		<?php
	}

	private function lookup_committees( $name ) {
		global $wpdb;
		global $committeehistory;

		$plugin_tablename_prefix = $wpdb->prefix . "committeehistory_";
		$sql = "SHOW TABLES LIKE '$plugin_tablename_prefix%'";
		$results = $wpdb->get_results( $sql );
	
		// First find out the correct full name, including caps, of the member
		foreach ( $results as $value ) {
		  foreach ( $value as $full_tablename ) {
				$found_fullname = $wpdb->get_results(
					"SELECT name FROM $full_tablename	WHERE name = '$name'",
					ARRAY_N #request arrays in arrays
				);
				if ( isset( $found_fullname[0][0] ) ) {
					$full_name = $found_fullname[0][0];
					break 2;
				}
			}
		}
	
		// If found, print out the committees of that member
		if ( !isset( $full_name ) ) {
			// That name is not found in any table
			echo "<div>No committees found for $name</div>";	

		}	else {
			// The name is found in at least one table
			$year_type = get_option( "year_type" );
			echo "<h3>Committee history of $full_name</h3>";
			foreach ( $results as $value ) {
				foreach ( $value as $full_tablename ) {
					$short_tablename = str_replace($plugin_tablename_prefix, '', $full_tablename);
					$tablename_neat = ucfirst($short_tablename);

					$committees_of_member = $wpdb->get_results(
						"SELECT year, committee, name
						FROM $full_tablename
						WHERE name = '$name'",
						ARRAY_N #request arrays in arrays
					);
			
					// print only if this person is found in this table
					if ( !empty( $committees_of_member ) ) {
						echo "<h4>$tablename_neat</h4>\r\n";
						$committees_of_member_sorted = $committeehistory->sort_data($committees_of_member);
			
						foreach ( $committees_of_member_sorted as $year_relative => $year_data ) {
							$startyear = get_option ("foundation_year") + $year_relative - 1;
							$endyear = $startyear + 1;
							echo "<div><b>$startyear";
							if ( $year_type == 'college' ) {
								echo "&nbsp;-&nbsp;$endyear";
							}
							echo "</b>  ";
							foreach ( $year_data as $committee => $__ ) {
								echo "<span class='roundedbox'>$committee</span> ";
							}
							echo "</div>\r\n";
						}
					} //name found in this table

				}		
			}
		} //name found at all

	} //function

} //class
