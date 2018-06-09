<?php
/**
 * Plugin Name: rgdb
 * Plugin URI: URI
 * Description: RGDB
 * Version: 1.0.0
 * Author: Ray Goat
 * Author URI: 
 * License: GPL2
 */

$db_records = "";
$db_table = "";
 
function plugin_main() {
    getData();
    showData();
}

function getData() {
    global $wpdb, $db_records, $db_table;
	$options = get_option( 'rgdb_settings' );
    $db_table = $wpdb->prefix . $options['rgdb_tabelnavn'];

    $sql = "SELECT * FROM " . $db_table;
    $db_records = $wpdb->get_results($sql, ARRAY_N);
}

function showData() {
    global $wpdb, $db_records, $db_table;
    $rowcolor = false;

	//Find kolonner til visning
	$options = get_option( 'rgdb_settings' );
	$kolonner = explode( ",", $options['rgdb_kolonner'] );
	// var_dump( $kolonner );
	
    if (sizeof($db_records)) {
        echo "<table id='rgdb_table' border='1'>";
        echo "<thead><tr>";
		
		//Headers
		$fieldCounter = 0;
		foreach ( $wpdb->get_col( "DESC " . $db_table, 0 ) as $column_name ) {
			if ( in_array( (string)$fieldCounter, $kolonner ) ) { 
				echo "<th title='Klik for at sortere på denne kolonne' onmouseover='style=\"cursor: pointer;\"'>$column_name</th>";
			}
			$fieldCounter++;
		}
        echo "</tr></thead>";
        echo "<tbody>";
        foreach($db_records as $hold) {
            if ($rowcolor) {echo '<tr class="even">';}
            else {echo '<tr class="odd">';}
			$fieldCounter = 0;
			foreach ( $wpdb->get_col( "DESC " . $db_table, 0 ) as $column_name ) {
				if ( in_array( (string)$fieldCounter, $kolonner ) ) { 
					echo "<td>" . $hold[ $fieldCounter ] . "</td>";
				}
				$fieldCounter++;
			}
            echo "</tr>";
            $rowcolor = !$rowcolor;
        }
        echo "</tbody></table>";
    }
}

add_shortcode( 'rgdb', 'plugin_main' );


/* ADD JAVASCRIPT AND CSS */

function rgdb_add_datatables_script() {
    wp_enqueue_script(
        'jquery-datatables',
        plugin_dir_url( __FILE__ ) . 'js/DataTables-1.10.9/js/jquery.dataTables.min.js',
        array('jquery'),
        '1.10.0',
        true
    );

    wp_enqueue_style(
        'jquery-datatables-css',
        plugin_dir_url( __FILE__ ) . 'js/DataTables-1.10.9/css/jquery.dataTables.min.css',
        array('jquery'),
        '1.10.0',
        true
    );

}
add_action('template_redirect', 'rgdb_add_datatables_script');

function rgdb_datatables_init() { ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#rgdb_table').DataTable({
                "paging":   false,
                "info":     false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.9/i18n/Danish.json"
                }
            });
        });
    </script>
<?php }
    add_action('wp_footer', 'rgdb_datatables_init', 999);
	
	
/* ADD SETTINGS  */
add_action( 'admin_menu', 'rgdb_add_admin_menu' );
add_action( 'admin_init', 'rgdb_settings_init' );

function rgdb_add_admin_menu() { 
	add_options_page( 'RGDB', 'RGDB', 'manage_options', 'rgdb', 'rgdb_options_page' );
}

function rgdb_settings_init() { 

	register_setting( 'pluginPage', 'rgdb_settings' );

	add_settings_section(
		'rgdb_pluginPage_section', 
		__( 'Indstillinger for RGDB', 'wordpress' ), 
		'rgdb_settings_section_callback', 
		'pluginPage'
	);

	// Database-tabel
	add_settings_field( 
		'rgdb_tabelnavn', 
		__( 'Angiv Tabelnavn: ', 'wordpress' ), 
		'rgdb_tabelnavn_render', 
		'pluginPage', 
		'rgdb_pluginPage_section' 
	);
	
	// Visning af kolonner
	add_settings_field( 
		'rgdb_kolonner', 
		__( 'Vis kolonner: ', 'wordpress' ), 
		'rgdb_kolonner_render', 
		'pluginPage', 
		'rgdb_pluginPage_section' 
	);
}

function rgdb_tabelnavn_render(  ) { 

	$options = get_option( 'rgdb_settings' );
	?>
	<input type='text' name='rgdb_settings[rgdb_tabelnavn]' value='<?php echo $options['rgdb_tabelnavn']; ?>'>
	<?php

}

function rgdb_kolonner_render(  ) { 

	$options = get_option( 'rgdb_settings' );
	?>
	<input type='text' name='rgdb_settings[rgdb_kolonner]' value='<?php echo $options['rgdb_kolonner']; ?>'>
	<?php

}

function rgdb_settings_section_callback(  ) { 
	echo __( 'Parametre', 'wordpress' );
}

function rgdb_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>RGDB</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php
}

	
	?>
