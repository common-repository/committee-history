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

class CommitteeHistorySettings {

	public function register_mysettings( ) { // whitelist options
		register_setting( 'general_settings', 'foundation_year' );
		register_setting( 'general_settings', 'year_type' );
	}

	public function committee_history_menu( ) {
		add_options_page( 'Committee History Settings', 'Committee history', 'manage_options', 'committee_history', array( $this, 'committee_history_options' ) );
	}

	public function committee_history_options( ) {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap"><h1>Committee History Settings</h1>
	
		<h2>General settings</h2>
		
		<form method="post" action="options.php"> 
			<?php settings_fields( 'general_settings' ); ?>
			
			<table class="form-table"><tr valign="top">
				<th scope="row">Year of foundation</th>
				<td>
					<input type="text" name="foundation_year" value="<?php echo esc_attr( get_option ( 'foundation_year' ) ); ?>" />
					<p class="description" id="tagline-description">The year of foundation of your association/club.</p>
				</td>
		  </tr></table>
			
			
			<table class="form-table"><tr valign="top">
				<th scope="row">Year type</th>
				<td>
					<p><input type="radio" name="year_type" value="college" <?php checked( 'college', get_option( 'year_type' ) ); ?> /><span>College year</span></p>
					<p><input type="radio" name="year_type" value="calendar" <?php checked( 'calendar', get_option( 'year_type' ) ); ?> /><span>Calendar year</span></p>
					<p class="description" id="tagline-description">Select whether your association/club changes commitees in the beginnning of a calender year or the college year.</p>
				</td>
		  </tr></table>

			<?php submit_button("Save general settings"); ?>
		</form>

		<h2>Manage committee tables</h2>
		
		<?php
	
		// Add table
		if(isset($_POST['AddButton'])){ //check if form was submitted
			$short_tablename = $_POST["table_name"]; //get input text
			$this->create_table ( $short_tablename );
			
			?><div class='update-nag'>Table "<?php echo $short_tablename?>" created</div><?php
		
		// Delete table
		}	elseif(isset($_POST['DeleteButton'])){ 
			$short_tablename = $_POST["table_name"];
			$this->delete_table ( $short_tablename );
			?><div class='update-nag'>Table "<?php echo $short_tablename?>" deleted</div><?php
		
		// Upload csv file to table
		}	elseif(isset($_POST['UploadButton'])){
			$short_tablename = $_POST["table_name"];
			$input_string = file_get_contents($_FILES["fileToUpload"]["tmp_name"]); 
			//TODO: validate input (must be csv with 3 columns)
			$input_array = $this->string2array( $input_string );

			foreach ($input_array as $line) {		
				if (is_numeric($line[0])) { // do not use headers
					$this->add_to_table ( $short_tablename, $line[0], $line[1], $line[2] );
				}
			}
		}
		
		global $wpdb;
		$plugin_tablename_prefix = $wpdb->prefix . "committeehistory_";
		$sql = "SHOW TABLES LIKE '$plugin_tablename_prefix%'";
		$results = $wpdb->get_results($sql);
		?>

		<table><tr><td><b>Table name</b></td><td><b>Delete</b></td><td><b>Upload csv</b></td><td><b>Shortcode</b></td></tr>
		<?php
		foreach($results as $value) {
		  foreach($value as $full_tablename) {
				$short_tablename = str_replace($plugin_tablename_prefix, '', $full_tablename);
				?>
				<tr>
					<td><?php echo $short_tablename; ?></td>
					<td>
						<form action="" method="post">
						<input type="hidden" name="table_name" value="<?php echo $short_tablename; ?>">
						<input type="submit" name="DeleteButton" class="button" value="Delete (without warning)">
						</form>
					</td>

					<td>
						<?php
						$num_rows = $wpdb->get_var("SELECT COUNT(*) FROM $full_tablename");
						if ( $num_rows > 0 ) {
							echo "$num_rows rows in table";
						} elseif ($num_rows == 0) {
						?>
						<div><i>Upload three-column csv file</i></div>
						<form action="" method="post" enctype="multipart/form-data">
						<input type="hidden" name="table_name" value="<?php echo $short_tablename; ?>">
						<input type="file" name="fileToUpload" id="fileToUpload" required>
						<input type="submit" name="UploadButton" class="button" value="Upload File">
						</form>
						<?php } ?>
					</td>

					<td><code>[committee_history sourcetable="<?php echo $short_tablename; ?>"]</code></td>
				</tr>
				<?php
			}
		}
		?>
		</table>
	
		<form action="" method="post">
			<input type="text" name="table_name" required>
			<input type="submit" name="AddButton" class="button" value="Add committee table">
		</form>

		<div>Here you can manage your comittee tables. Normally the number of tables that you need is equal to the number of pages with committees on your website. For example, if you want two committee lists on two different pages, create two committee tables with distinct names.</div>
		<div>Then upload a three-column csv (comma seperated) file for every table you made, which looks as follows:</div>
		<table border="1">
			<tr><td>relative year since foundation</td><td>committee</td><td>name</td></tr>
			<tr><td>1</td><td>chairman</td><td>person 1</td></tr>
			<tr><td>1</td><td>secretary</td><td>person 2</td></tr>
			<tr><td>2</td><td>chairman</td><td>name 1</td></tr>
			<tr><td>2</td><td>secretary</td><td>name 3</td></tr>
		</table>
		<div>Finally, add the committee history overview to the page of your choice by adding the WordPress shortcode: <code>[committee_history sourcetable="table_name"]</code>, where <code>table_name</code> is the name that you entered when creating the table.
		Add <code>type="table"</code> if you want a table layout instead of a bullet point layout.
	<?php
	}


	private function create_table( $table_name ) {

		global $wpdb;
		$full_table_name = $wpdb->prefix . "committeehistory_$table_name"; 
	
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $full_table_name (
			id smallint(9) NOT NULL AUTO_INCREMENT,
			year smallint(9) NOT NULL,
			committee varchar(50) NOT NULL,
			name varchar(100) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	private function delete_table( $table_name ) {
		global $wpdb;
		$full_table_name = $wpdb->prefix . "committeehistory_$table_name"; 
		$wpdb->query( "DROP TABLE IF EXISTS $full_table_name" );
	}

	private function add_to_table( $short_tablename, $year, $committee, $name ) {
		global $wpdb;
		$full_table_name = $wpdb->prefix . "committeehistory_$short_tablename";

		$wpdb->insert( 
			$full_table_name, 
			array( 'year' => $year, 'committee' => $committee, 'name' => $name ) 
		);
	}

	private function string2array( $input_string ) {
		$array_of_strings = explode( "\n", $input_string );
		$array_of_arrays = array_map( 'str_getcsv', $array_of_strings );
		return $array_of_arrays;
	}

}
