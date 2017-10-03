<?php

/*
Plugin Name: WP GraphQL Admin
Description: WP GraphQL Admin
Author: Netspective
Version: 1.0
Author URI: "https://www.netspective.com"
*/
?>
<?php

/**
 * Activation hook
 */
register_activation_hook( __FILE__, 'gql_activation' );

/**
 * Activation function.
 *
 * Activation function.
 */
function gql_activation() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global  $wpdb;
	$query_result   = $wpdb->get_results( "SHOW TABLES LIKE 'wp_grapql_support'" );// db call ok; no-cache ok.
	if ( empty( $query_result ) ) {
		$charset_collate = $wpdb->get_charset_collate();
		$query = 'CREATE TABLE `wp_grapql_support` (
						`id` BIGINT(11) AUTO_INCREMENT,  		 
						`identifier` varchar(50),
						`field` varchar(50),
						`alias` varchar(50),
						`name` varchar(50),
						`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE     CURRENT_TIMESTAMP,     
						PRIMARY KEY (`id`)
					)ENGINE=MYISAM DEFAULT CHARSET=latin1;';
		dbDelta( $query );		
	}else{
		$ret = $wpdb->delete(
				'wp_grapql_support',
			); // db call ok; no-cache ok.
	}
	$wpdb->insert(
			'wp_grapql_support', array(
				'identifier' => 'posttype',
				'field' => '',
				'alias' => '',
				'name' => 'post',
			)
		);
		$wpdb->insert(
			'wp_grapql_support', array(
				'identifier' => 'posttype',
				'field' => '',
				'alias' => '',
				'name' => 'page',
			)
		);
}
/**
 * Enque js and css.
*/
add_action( 'admin_enqueue_scripts', 'gql_load_scripts_style' );
/**
 * Load style and js
 */
function gql_load_scripts_style() {
	wp_enqueue_script( 'ajax-script',plugin_dir_url( __FILE__ ) . 'js/gqlscript.js' );
	wp_enqueue_style( 'gql-styles',  plugin_dir_url( __FILE__ ) . 'css/gqlstyle.css' );
	 wp_localize_script(
		 'ajax-script', 'ajaxhandler',
		 array(
			 'ajax_url' => admin_url( 'admin-ajax.php' ),
		 )
	 );

}
/**
 * Hook for admin menu.
 */
add_action( 'admin_menu', 'gql_admin_create_menu' );
/**
 * Add the plugin menu in the dashboard.
 *
 * Add the menus GraphQL Admin,Post Support,Field Support and Mutation Support
 */
function gql_admin_create_menu() {
	add_menu_page( __( 'GraphQL Support Page' ), __( 'GraphQL Admin' ), 'manage_options', 'gql_setting_page', 'gql_post_support_page' );
	add_submenu_page( 'gql_setting_page', __( 'Post Support' ), __( 'Post Support' ), 'manage_options', 'gql_setting_page','gql_post_support_page' );
	add_submenu_page( 'gql_setting_page', __( 'Field Support' ), __( 'Field Support' ), 'manage_options', 'gql_field_setting_page' ,'gql_filed_support_page' );
	add_submenu_page( 'gql_setting_page', __( 'Mutation Support' ), __( 'Mutation Support' ), 'manage_options', 'gql_mutation_setting_page','gql_mutation_support_page' );
}
/**
 * Displays the post types
 *
 * Display the available post types for adding the grapql post support.
 */
function gql_post_support_page() {
	gql_get_posttypes_postsupport();
}
/**
 * Displays the post types
 *
 * Display the available post types for adding the grapql mutation support.
 */
function gql_mutation_support_page() {
	global $wpdb;
	if ( isset( $_POST['posttype'][0] ) ) {
		if ( isset( $_POST['addpostnonce'] ) ) { // Input var okay.
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['addpostnonce'] ) ) , 'add-post' ) ) { // Input var okay.
				$alias_fields_names = array();
				$alias_mutations_names = array();
				$fposttype = sanitize_text_field( wp_unslash( $_POST['posttype'][0] ) );
				$meta_keys = generate_meta_keys( $fposttype );
				$alias_name_result = $wpdb->get_results( $wpdb->prepare( "select field,alias from wp_grapql_support where identifier='mutation' AND name=%s",$fposttype ) );// db call ok; no-cache ok.
				foreach ( $alias_name_result as $row ) {
					$alias_mutations_names[ $row->field ] = $row->alias;
				}
				if ( count( $meta_keys ) > 0 ) {
					$meta_key_value_arr = gql_get_metakey_for_form( $meta_keys,$alias_mutations_names );
				}
			}
		}
	} else {
		$fposttype = false;
	}
	gql_get_posttypes_fieldsupport( $fposttype,'mutation' );
	?>
	<div class="clear-fix"></div>
		<?php
		if ( isset( $meta_key_value_arr ) ) {
			gql_get_addfield_htmlform( $meta_key_value_arr, 'mutation', $fposttype );
		}
		?>
		
			<div>
		<?php
		if ( isset( $alias_mutations_names ) ) {
			gql_get_remove_field_htmlform( $alias_mutations_names,'mutation' );
		}
		?>
	
			</div>	
	<?php
}
/**
 * Displays the post types
 *
 * Display the available post types for adding the grapql field support.
 */
function gql_filed_support_page() {
	global $wpdb;
	if ( isset( $_POST['posttype'][0] ) ) {
		if ( isset( $_POST['addpostnonce'] ) ) { // Input var okay.
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['addpostnonce'] ) ) , 'add-post' ) ) { // Input var okay.
				$alias_fields_names = array();
				$fposttype = sanitize_text_field( wp_unslash( $_POST['posttype'][0] ) );
				$meta_keys = generate_meta_keys( $fposttype );
				$alias_name_result = $wpdb->get_results( $wpdb->prepare( "select field,alias from wp_grapql_support where identifier='field' AND name=%s",$fposttype ) );// db call ok; no-cache ok.
				if ( count( $alias_name_result ) > 0 ) {
					foreach ( $alias_name_result as $row ) {
						$alias_fields_names[ $row->field ] = $row->alias;
					}
				}
				if ( count( $meta_keys ) > 0 ) {
					$meta_key_value_arr = gql_get_metakey_for_form( $meta_keys,$alias_fields_names );
				}
			}
		}
	} else {
		$fposttype = false;
	}
	gql_get_posttypes_fieldsupport( $fposttype,'field' );
	?>
	<div class="clear-fix"></div>
	<div>
		<?php
		if ( isset( $meta_key_value_arr ) ) {
			gql_get_addfield_htmlform( $meta_key_value_arr, 'field', $fposttype );
		}
		?>
		
			</div>
	<div>	
	<?php
	if ( isset( $alias_fields_names ) ) {
		gql_get_remove_field_htmlform( $alias_fields_names,'field' );
	}
	?>
	</div>

<?php
}
/**
 * Html form for add fields
 *
 * Html form for add fields
 *
 * @param  array  $meta_key_value_arr  metakey array.
 * @param  string $type  type field or mutation.
 * @param  string $fposttype  posttype.
 */
function gql_get_addfield_htmlform( $meta_key_value_arr, $type, $fposttype ) {
	if ( 'field' === $type ) {
		$nonce = wp_create_nonce( 'add-field-field' );
		$jvscript_func = 'saveGraphqlFields()';
		$jv_element_id = 'ad-field-field';
		$input_box_name = 'metakeys[]';
		$formname = 'afields';
		$btn_display_name = 'Add Fields';
	} else if ( 'mutation' === $type ) {
		$nonce = wp_create_nonce( 'add-mut-field' );
		$jvscript_func = 'saveGraphqlMutationFields()';
		$jv_element_id = 'ad-mut-field';
		$input_box_name = 'mmetakeys[]';
		$formname = 'mfields';
		$btn_display_name = 'Add Mutaion Support';
	}
	?>
	<form id="<?php echo $formname;   // WPCS: XSS OK. ?>" name="<?php echo $formname;  // WPCS: XSS OK. ?>">
		<?php if ( isset( $meta_key_value_arr ) && count( $meta_key_value_arr ) > 0 ) { ?>
		<h1 class="marg">Custom Fields  of <?php echo $fposttype ; // WPCS: XSS OK. ?></h1>
		<ul class="gph-met-ul">
			<?php
			foreach ( $meta_key_value_arr as $meta_field_arr ) {
				?>
				<li>
					<input type="checkbox" name="<?php echo $input_box_name ; // WPCS: XSS OK. ?>" value="<?php echo $meta_field_arr['key']; // WPCS: XSS OK. ?>" <?php echo $meta_field_arr['chkornot']; // WPCS: XSS OK. ?>  />
					<span><?php echo $meta_field_arr['key']; // WPCS: XSS OK. ?></span>
					<input type="text" name="<?php echo $meta_field_arr['key']; // WPCS: XSS OK. ?>"  value="<?php echo $meta_field_arr['alias'];// WPCS: XSS OK. ?>" onKeyPress="return onlyText(event)"   onkeyup="return onlyText(event)" /> </li>
			<?php } ?>
		</ul>				
		<div class="clear-fix"></div>
		<input type="button" value="<?php echo $btn_display_name;// WPCS: XSS OK. ?>"  class="button button-primary button-large"   onclick="<?php echo $jvscript_func; // WPCS: XSS OK. ?>" />	
		<input type="hidden" id= "fposttype" name="fposttype" value="<?php echo  $fposttype; // WPCS: XSS OK. ?>"/>
		<?php } else if ( $fposttype ) { ?>
		   <h1 class="marg">No fields to display</h1>
		<?php } ?>	
		<input type="hidden" id="<?php echo $jv_element_id; // WPCS: XSS OK. ?>" value="<?php echo $nonce; // WPCS: XSS OK. ?>" />
	</form>	
	<?php
}
/**
 * Html form for remove fields
 *
 * @param  array  $removed_filed_array  metakey array.
 * @param  string $type  type field or mutation.
 */
function gql_get_remove_field_htmlform( $removed_filed_array, $type ) {
	if ( 'field' === $type ) {
		$nonce = wp_create_nonce( 'remove-field-field' );
		$jvscript_func = 'removeGraphqlFields()';
		$jv_element_id = 'rm-field-fields';
		$input_box_name = 'rmetakeys[]';
		$formname = 'rfields';
	} else if ( 'mutation' === $type ) {
		$nonce = wp_create_nonce( 'remove-mut-field' );
		$jvscript_func = 'removeGraphqlMutFields()';
		$jv_element_id = 'rm-mut-fields';
		$input_box_name = 'mrmetakeys[]';
		$formname = 'rmutfields';
	}
	?>
	<?php if ( isset( $removed_filed_array ) && count( $removed_filed_array ) > 0 ) { ?>
		<form id="<?php echo $formname ; // WPCS: XSS OK. ?>" name="<?php echo $formname ; // WPCS: XSS OK. ?>">
			<h1 class="marg">Remove Fields</h1>		
			<ul class="gph-met-ul">
				<li>
					<?php
					foreach ( $removed_filed_array as $key => $value ) {
						?>
						<li>
							<input type="checkbox" name="<?php echo $input_box_name ; // WPCS: XSS OK. ?>" value="<?php echo $key; // WPCS: XSS OK. ?>"  />
							<?php echo $key; // WPCS: XSS OK. ?>
				<?php } ?>	
				</li>
			</ul>				
			<div class="clear-fix"></div>
			<input type="button" value="Remove Fields" class="button button-primary button-large"  onclick="<?php echo $jvscript_func; // WPCS: XSS OK. ?>" />
			<input type="hidden" id="<?php echo $jv_element_id; // WPCS: XSS OK. ?>" value="<?php echo $nonce; // WPCS: XSS OK. ?>" />
			</form>	
		<?php } ?>
	</div>
<?php
}
/**
 * Get metakey for form
 *
 * Html form for add fields
 *
 * @param  array $meta_keys  metakey array.
 * @param  array $alias_fields_names  exists key array.
 */
function gql_get_metakey_for_form( $meta_keys, $alias_fields_names ) {
	 $meta_key_value_arr = array();
	foreach ( $meta_keys as $meta_key ) {
		$m_array_count = count( $meta_key_value_arr );
		if ( array_key_exists( $meta_key, $alias_fields_names ) ) {
			$meta_key_value = $alias_fields_names[ $meta_key ] ;
			$meta_key_value_arr[ $m_array_count ]['key'] = $meta_key;
			$meta_key_value_arr[ $m_array_count ]['alias'] = $alias_fields_names[ $meta_key ] ;
			$meta_key_value_arr[ $m_array_count ]['chkornot'] = 'checked';
		} else {
			$field_name_arr = explode( '-',$meta_key );
			$count = count( $field_name_arr );
			if ( $count > 1 ) {
				$field_name = ' ';
				for ( $j = 0;$j < $count; $j++ ) {
					if ( 0 === $j ) {
						$fname = $field_name_arr[ $j ];
					} else {
						$fname = ucfirst( $field_name_arr[ $j ] );
					}
					$field_name = $field_name . $fname;
				}
				$field_name = trim( $field_name );
				$meta_key_value_arr[ $m_array_count ]['key']  = $meta_key;
				$meta_key_value_arr[ $m_array_count ]['alias'] = $field_name;
				$meta_key_value_arr[ $m_array_count ]['chkornot'] = '';
			}
		}
	}
	return $meta_key_value_arr;
	print_r();
}
/**
 * Displays the post types
 *
 * Display the available post types for field support.
 *
 * @param  string $chk_posttype  checked posttypes.
 * @param  string $type  type field or mutation.
 */
function gql_get_posttypes_fieldsupport( $chk_posttype, $type ) {
	global $wpdb;
	$post_names = array();
	$post_names  = get_graphql_added_posts();
	if ( 'field' === $type ) {
		$ad_page = "admin_url( 'admin.php?page=gql_field_setting_page' )";

	} else if ( 'mutation' === $type ) {
		$ad_page = "admin_url( 'admin.php?page=gql_mutation_setting_page' )";
	}
	?>
	<div class="wrap"><div id="icon-tools" class="icon32"></div>
		<h2>GraphQL <?php echo ucfirst( $type );  // WPCS: XSS OK. ?> Support</h2>
	</div>	
	<form method="POST"  id= "fieldform" action="<?php $ad_page ;// WPCS: XSS OK. ?>">
		<div class="post-container">				
			<div class="added-posts">
				<h1>Enabled</h1>
				<div class="gql-note">Posts that have the GraphQL support.</div>
				<ul class="gph-ul">
					<?php
					if ( count( $post_names ) > 0 ) {
						foreach ( $post_names  as $ap ) {
							if ( $ap === $chk_posttype ) {
								$checked = 'checked';
							} else {
								$checked = '';
							}
						?>
						<li>
							<input type="checkbox" <?php echo $checked;  // WPCS: XSS OK. ?>   name="posttype[]" value="<?php echo  $ap;  // WPCS: XSS OK. ?>"  onclick="selectOneItem(event)" /> 	
							<?php echo  $ap; // WPCS: XSS OK. ?>
						</li>
					<?php
						}
					}
					?>
				</ul>
				<input type="submit" class="button button-primary button-large" value="View Post Fields" />
				<input type="hidden" name= "addpostnonce" value="<?php echo  wp_create_nonce( 'add-post' ); // WPCS: XSS OK. ?>" />
				<div class="clear-fix"></div>
			</div>			
		</div>
	</form>
	<?php
}

/**
 * Displays the post types
 *
 * Display the available post types for post support.
 */
function gql_get_posttypes_postsupport() {
	global $wpdb;
	$post_names = array();
	$added_post = array();
	$not_added_post = array();
	$post_names  = get_graphql_added_posts();
	$post_types = get_post_types( '', 'names' );
	foreach ( $post_types as $post_type ) {
		if ( array_key_exists( $post_type, $post_names ) ) {
			array_push( $added_post,$post_type );
		} else {
			array_push( $not_added_post,$post_type );
		}
	}
	?>
	<div class="wrap"><div id="icon-tools" class="icon32"></div>
		<h2>GraphQL Post Support</h2>
	</div>	
	<form method="POST"  id= "gpostform" action="<?php echo  admin_url( 'admin.php?page=gql_post_setting_page' ); // WPCS: XSS OK. ?>">
		<div class="post-container">				
			<div class="added-posts">
				<h1>Enabled</h1>
				<div class="gql-note">Posts that have the GraphQL support.</div>
				<ul class="gph-ul">
					<?php
					if ( count( $added_post ) > 0 ) {
						foreach ( $added_post as $ap ) {
						?>
						<li>
							<input type="checkbox"   name="aposttype[]" value="<?php echo  $ap; // WPCS: XSS OK. ?>" checked disabled /> 	
							<?php echo  $ap; // WPCS: XSS OK. ?>
						</li>
					<?php
						}
					}
					?>
				</ul>
				<div class="clear-fix"></div>
			</div>	
			<div class="not-added-posts">
				<h1>Not Enabled</h1>
				<div class="gql-note">Please select the post for which you like to enable GraphQL support.</div>
				<ul class="gph-ul">
					<?php
					if ( count( $not_added_post ) > 0 ) {
						foreach ( $not_added_post as $ap ) {
							$alias = ucwords( preg_replace( '/[^A-Za-z0-9]/', ' ', $ap ) ); // Removes special chars.
							$alias = str_replace( ' ', '', $alias );
						?>
						<li>
							<input type="checkbox"   name="posttype[]" value="<?php echo  $ap; // WPCS: XSS OK. ?>"  /> 	
							<span><?php echo  $ap ; // WPCS: XSS OK. ?></span>
							<input type="text" name="<?php echo $ap;// WPCS: XSS OK. ?>"  value="<?php echo $alias;// WPCS: XSS OK. ?>" onKeyPress="return onlyText(event)"   onkeyup="return onlyText(event)" />
						</li>
					<?php
						}
					}
					?>
				</ul>
				<div class="clear-fix"></div>
				<input type="hidden" id="ad-post-nonce" value="<?php echo  wp_create_nonce( 'add-post' ); // WPCS: XSS OK. ?>" />
				<input type="button" value="Add Post Support" class="button button-primary button-large" onclick="gqlPostSupport()" />	
				<div>Note: If you do not see the posts for which you like to enable GraphQL support, please go to Post Support tab and select and enable.</div>				
			</div>		
		</div>
	</form>
	<?php
}
/**
 * Displays the grapql supported post types
 *
 * Displays the grapql supported post types
 */
function get_graphql_added_posts() {
	global $wpdb;
	$post_names = array();
	$query_result   = $wpdb->get_results( "SHOW TABLES LIKE 'wp_grapql_support'" );// db call ok; no-cache ok.
	if ( ! empty( $query_result ) ) {
		$post_name_result = $wpdb->get_results( "select name from wp_grapql_support where identifier='posttype'" );// db call ok; no-cache ok.
		foreach ( $post_name_result as $row ) {
			$post_names[ $row->name ] = $row->name;
		}
	}
	$post_names['post'] = 'post';
	$post_names['page'] = 'page';
	return  $post_names;
}
/**
 * Displays the metakeys of a particular post types.
 *
 * Metakeys of a particular post types.
 *
 *  @param string $post_type     Name of the Post type.
 */
function generate_meta_keys( $post_type ) {
	global $wpdb;
	$meta_keys = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT($wpdb->postmeta.meta_key) FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_type = '%s' AND $wpdb->postmeta.meta_key != '' AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'", $post_type ) ); // db call ok; no-cache ok.
	set_transient( 'gql_meta_keys', $meta_keys, 60 * 60 * 24 ); // create 1 Day Expiration.
	return $meta_keys;
}
/**
 * Metakeys.
 */
function get_gql_meta_keys() {
	$cache = get_transient( 'gql_meta_keys' );
	$meta_keys = $cache ? $cache : generate_meta_keys();
	return $meta_keys;
}
/**
 * Enable the grapql support to post hook.
 */
add_action( 'wp_ajax_gql_support_add_posts', 'gql_support_add_posts' );
/**
 * Enable the grapql support to post.
 */
function gql_support_add_posts() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global  $wpdb;
	$post_names = array( 'post', 'page' );
	$prefix_insert_query = 'INSERT INTO wp_grapql_support (identifier,field,alias,name) VALUES ';
	$insert_query = '';
	$already_added_checker = false;
	$query_checker = false;
	$already_added_posttypes = '';
	if ( isset( $_POST['addpostnonce'] ) ) { // Input var okay.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['addpostnonce'] ) ) , 'add-post' ) ) { // Input var okay.
			if ( isset( $_POST['ptypes'] ) ) { // Input var okay.
				$gql_posts = sanitize_text_field( wp_unslash( $_POST['ptypes'] ) ); // Input var okay.
				$gpost_types = explode( ',', $gql_posts );
			}
			if ( isset( $gpost_types ) && count( $gpost_types ) > 0 ) {
				$query_result   = $wpdb->get_results( "SHOW TABLES LIKE 'wp_grapql_support'" );// db call ok; no-cache ok.
				if ( empty( $query_result ) ) {
					$charset_collate = $wpdb->get_charset_collate();
					$query = 'CREATE TABLE `wp_grapql_support` (
									`id` BIGINT(11) AUTO_INCREMENT,  		 
									`identifier` varchar(50),
									`field` varchar(50),
									`alias` varchar(50),
									`name` varchar(50),
									`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE     CURRENT_TIMESTAMP,     
									PRIMARY KEY (`id`)
								)ENGINE=MYISAM DEFAULT CHARSET=latin1;';
					dbDelta( $query );
				}
				foreach ( $gpost_types as $gtype ) {
					$gtype = trim( $gtype );
					if ( strlen( $gtype ) > 2 ) {
						// Check to exists.
						$gtype = esc_sql( strip_tags( wp_unslash( $gtype ) ) );
						if ( isset( $_POST[ $gtype ] ) ) {
							if ( strlen( $alias ) < 2 ) {
								$alias = $gtype;
							}
							$alias = sanitize_text_field( wp_unslash( $_POST[ $gtype ] ) ); // Input var okay.
							$alias = str_replace( "'",'', $alias );
							$alias = str_replace( '"','', $alias );

						}
						$gtype = str_replace( "'",'',$gtype );
						/*$query = $wpdb->prepare( "SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = %s AND alias = %s", $gtype, $alias );*/
						$result = $wpdb->get_results( 'SELECT EXISTS (' . $wpdb->prepare( "SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = %s AND alias = %s", $gtype, $alias ) . ')	AS counts' );// db call ok; no-cache ok.
						foreach ( $result as $row ) {
							$count = $row->counts;
						}
						if ( 0 == $count ) {
							if ( ! in_array( $gtype, $post_names ) ) {
								$insert_query .= "( 'posttype','','$alias','$gtype'),";
							}
						}
					} else {
						$output = 'Mininum character of posttype is alleast two';
					}
				}
				if ( strlen( $insert_query ) > 15 ) {
					$insert_query = trim( $insert_query,',' );
					$query  = $prefix_insert_query . $insert_query . ';';
					$database_result = dbDelta( $query );
					$query_checker = true;
					if ( '' !== $wpdb->last_error ) {
						$output = 'Something went wrong. Please try again';
						echo json_encode( $output );
						die();
					}
				}
				// GrapQL write file.
				if ( $query_checker ) {
					$result = $wpdb->get_results( "Select name from wp_grapql_support where identifier='posttype' " );// db call ok; no-cache ok.
					if ( count( $result ) > 0 ) {
						$file = dirname( __FILE__ ) . '/support/postsupport.php';
						$current = '<?php    add_action( "do_graphql_request", function() { global $wp_post_types;';
						foreach ( $result as $row ) {
							if ( isset( $row->name ) && strlen( $row->name ) > 2 ) {
								if ( substr( strtolower( $row->name ), -1 ) == 's' ) {
									$singular_name = chop( strtolower( $row->name ),'s' );
									$plural_name = $row->name;
								} else {
									$plural_name = $row->name . 's';
									$singular_name = $row->name;
								}
								$current .= 'if ( isset( $wp_post_types["' . $row->name . '"] ) ) {
								$wp_post_types["' . $row->name . '"]->show_in_graphql     = true;
								$wp_post_types["' . $row->name . '"]->graphql_single_name = "' . $singular_name . '";
								$wp_post_types["' . $row->name . '"]->graphql_plural_name = "' . $plural_name . '";
								}';
							}
						}
						if ( strlen( $current ) > 30 ) {
							$current .= '} );';
							file_put_contents( $file, $current );
						}
					}
				}
				if ( $already_added_checker ) {
					$already_added_posttypes  = trim( $already_added_posttypes,', ' );
					$output = ucfirst( $already_added_posttypes ) . ' post types are already added.';
				} else {
					$output = 'GrapQl Support Added Successfully';
				}
				echo json_encode( $output );
			}
		}
	}
	die();
}
/**
* Ajax hook.
*/
add_action( 'wp_ajax_gql_support_add_fields', 'gql_support_add_fields' );
/**
 * Enable the grapql field support to post fields.
 */
function gql_support_add_fields() {
	global  $wpdb;
	$update_query = '';
	$query_checker = false;
	$output = 'Something went wrong. Please try again.';
	if ( isset( $_POST['addfieldnonce'] ) ) { // Input var okay.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['addfieldnonce'] ) ) , 'add-field-field' ) ) { // Input var okay.
			if ( isset( $_POST['afields'] ) ) { // Input var okay.
				$afields = sanitize_text_field( wp_unslash( $_POST['afields'] ) ); // Input var okay.
			}
			$gpost_fields = explode( ',', $afields );
			$query_result   = $wpdb->get_results( "SHOW TABLES LIKE 'wp_grapql_support'" );// db call ok; no-cache ok.
			if ( empty( $query_result ) ) {
				$output = 'Please add the post first to add the fields.';
			} else {
				if ( isset( $_POST['posttype'] ) ) {// Input var okay.
					$fpost_type = sanitize_text_field( wp_unslash( $_POST['posttype'] ) ); // Input var okay.
					if ( strlen( $fpost_type ) > 2 ) {
						$fpost_type = str_replace( "'",'',$fpost_type );
						$result = $wpdb->get_results( 'SELECT EXISTS (' . $wpdb->prepare( "SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = %s", $fpost_type ) . ' )	AS counts' );// db call ok; no-cache ok.
						if ( is_wp_error( $result ) ) {
							exit;
						}
						foreach ( $result as $row ) {
							$count = intval( $row->counts );
						}
						if ( 1 === $count ) {
							// Check if the field field to add.
							// Database insert function.
							$write_checker = gql_table_insert( $fpost_type,$gpost_fields,'field',$_POST ); // Input var okay.
							if ( $write_checker ) {
								gql_field_writefile();
							}
							$output = 'Fields added successfully';
						} else {
							$output = 'Please add the post first to add the fields.';
						}
					} else {
						$output = 'Invalid post type';
					}
				}
			}
		}
	}

	echo wp_json_encode( $output );
	die();
}
/**
 * Ajax remove  the grapql filed support from post fields hook.
 */
add_action( 'wp_ajax_gql_support_remove_fields', 'gql_support_remove_fields' );
/**
 * Remove  the grapql filed support from post fields.
 */
function gql_support_remove_fields() {
	global  $wpdb;
	$update_query = '';
	$output = 'Something went wrong. Please try again';
	if ( isset( $_POST['remvfieldnonce'] ) ) { // Input var okay.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['remvfieldnonce'] ) ) , 'remove-field-field' ) ) {// Input var okay.
			if ( isset( $_POST['rfields'] ) ) {
				$rfields = sanitize_text_field( wp_unslash( $_POST['rfields'] ) );// Input var okay.
			}
			$gpost_fields = explode( ',',$rfields );
			$delete_checker = false;
			if ( isset( $_POST['posttype'] ) ) { // Input var okay.
				$fpost_type = sanitize_text_field( wp_unslash( $_POST['posttype'] ) ); // Input var okay.
				if ( strlen( $fpost_type ) > 2 ) {
					if ( isset( $gpost_fields ) && count( $gpost_fields ) > 0 ) {
						foreach ( $gpost_fields as $gpost_field ) {
							$gpost_field = esc_sql( strip_tags( wp_unslash( $gpost_field ) ) );
							$count_checker = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM wp_grapql_support WHERE identifier='field' AND name = %s AND field = %s " , $fpost_type, $gpost_field ) );// db call ok; no-cache ok.
							foreach ( $count_checker as $crow ) {
								$delete_id = $crow->id;
							}
							if ( $delete_id > 0 ) {
								$ret = $wpdb->delete(
									'wp_grapql_support', array(
										'ID' => $delete_id,
									)
								); // db call ok; no-cache ok.
								if ( $ret > 0 ) {
									$delete_checker = true;
								}
							}
						}
						if ( $delete_checker ) {
							gql_field_writefile();
						}
						$output = 'Fields removed successfully';
					}
				} else {
					$output = 'Invalid post type';
				}
			}
		}
	}
	echo wp_json_encode( $output );
	die();
}
/**
 * Write mutation field to file.
 */
function gql_field_writefile() {
	global $wpdb;
	$result = $wpdb->get_results( "Select alias,field,name from wp_grapql_support where identifier='field' order by name" );  // db call ok; no-cache ok.
	$file = dirname( __FILE__ ) . '/support/fieldsupport.php';
	if ( count( $result ) > 0 ) {
		$current = '<?php ';
		$oldpost_name = '';
		$loop_checker = 0;
		foreach ( $result as $row ) {
			if ( substr( strtolower( $row->name ), -1 ) === 's' ) {
				$singular_name = chop( strtolower( $row->name ),'s' );
			} else {
				$singular_name = $row->name;
			}
			$field_name_arr = explode( '-',$row->alias );
			$field_name = $field_name_arr[0] . ucfirst( $field_name_arr[1] );
			if ( $oldpost_name === $row->name ) {
				$current .= '$fields["' . $field_name . '"] = [
							        "type" => WPGraphQL\Types::string(),
							        "description" => __( "The sources of the post", "my-graphql-extension-namespace" ),
							        "resolve" => function( \WP_Post $' . $row->name . ', array $args, $context, $info ) {
							            $' . str_replace( '-','_',$row->field ) . ' = get_post_meta( $' . $row->name . '->ID, "' . $row->field . '", true );
							            return ( ! empty( $' . str_replace( '-','_',$row->field ) . ' ) && is_string( $' . str_replace( '-','_',$row->field ) . ' ) ) ? $' . str_replace( '-','_',$row->field ) . ' : null;
							        },
							 ];';
			} else {
				if ( 1 === $loop_checker ) {
					$current .= '}';
				}
				$current .= "add_filter( 'graphql_" . $singular_name . "_fields', '" . $row->name . "_cutomfield' );";
				$current .= 'function ' . $row->name . '_cutomfield( $fields ) { ';
				$current .= '$fields["' . $field_name . '"] = [
							        "type" => WPGraphQL\Types::string(),
							        "description" => __( "The sources of the post", "my-graphql-extension-namespace" ),
							        "resolve" => function( \WP_Post $' . $row->name . ', array $args, $context, $info ) {
							            $' . str_replace( '-','_',$row->field ) . ' = get_post_meta( $' . $row->name . '->ID, "' . $row->field . '", true );
							            return ( ! empty( $' . str_replace( '-','_',$row->field ) . ' ) && is_string( $' . str_replace( '-','_',$row->field ) . ' ) ) ? $' . str_replace( '-','_',$row->field ) . ' : null;
							        },
							 ];';
				$oldpost_name = $row->name;
				$loop_checker = 1;
			}
		}
		$current .= ' return $fields;}';
		file_put_contents( $file, $current );
	} else {
		file_put_contents( $file, '' );
	}
}
/**
* Ajax grapql mutation support to post fields.
*/
add_action( 'wp_ajax_gql_support_mutation_fields', 'gql_support_mutation_fields' );
/**
 * Enable the grapql mutation support to post fields.
 */
function gql_support_mutation_fields() {
	global  $wpdb;
	$update_query = '';
	if ( isset( $_POST['addmutnonce'] ) ) { // Input var okay.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['addmutnonce'] ) ) , 'add-mut-field' ) ) { // Input var okay.
			if ( isset( $_POST['mfields'] ) ) { // Input var okay.
				$mfields = sanitize_text_field( wp_unslash( $_POST['mfields'] ) ); // Input var okay.
			}
			$gpost_fields = explode( ',', $mfields );
			$query_result   = $wpdb->get_results( "SHOW TABLES LIKE 'wp_grapql_support'" );// db call ok; no-cache ok.
			if ( empty( $query_result ) ) {
				$output = 'Please add the post first to add the fields.';
			} else {
				if ( isset( $_POST['posttype'] ) ) { // Input var okay.
					$fpost_type = sanitize_text_field( wp_unslash( $_POST['posttype'] ) ); // Input var okay.
					$result = $wpdb->get_results( 'SELECT EXISTS (' . $wpdb->prepare( "SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = %s", $fpost_type ) . ' )	AS counts' );// db call ok; no-cache ok.
					foreach ( $result as $row ) {
						$count = $row->counts;
					}
					if ( 0 === $count ) {
						$output = 'Please add the post first to add the fields.';
					} else {
						// Check if the mutation field to add.
						if ( isset( $gpost_fields ) && count( $gpost_fields ) > 0 ) {
							// Database insert function.
							$write_checker = gql_table_insert( $fpost_type,$gpost_fields,'mutation',$_POST ); // Input var okay.
							if ( $write_checker ) {
								gql_mutation_writefile();
							}
							$output = 'Mutations added successfully';
						}
					}
				}
			}
		}
		$output = 'Mutations added successfully';
	}
	echo wp_json_encode( $output );
	die();
}
/**
 * Database insert query.
 *
 * @param string $post_type   Name of the Post type.
 * @param array  $field_array    Field array.
 * @param string $identifier    Name of the database identifier.
 * @param array  $post_array     GLOBAL POST array.
 */
function gql_table_insert( $post_type, $field_array, $identifier, $post_array ) {
	global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$value = ' ';
	$default_value = 'INSERT INTO wp_grapql_support (identifier,field,alias,name) VALUES';
	$write_checker = false;
	foreach ( $field_array as $gpost_field ) {
		$gpost_field = esc_sql( strip_tags( wp_unslash( $gpost_field ) ) );
		if ( isset( $post_array[ $gpost_field ] ) ) {
				$alias = sanitize_text_field( wp_unslash( $post_array[ $gpost_field ] ) ); // Form verified.
		}
		$count_checker = $wpdb->get_results( 'SELECT EXISTS (' . $wpdb->prepare( 'SELECT 1 FROM wp_grapql_support WHERE identifier = %s AND name = %s AND field =%s ', $identifier ,$post_type , $gpost_field ) . ' )	AS counts' );// db call ok; no-cache ok.
		$count_checker = $wpdb->get_results( $query ); // db call ok; no-cache ok.
		foreach ( $count_checker as $crow ) {
			$count = $crow->counts ;
		}
		if ( 0 == $count ) {
			if ( strlen( $alias ) < 2 ) {
				$alias = $gpost_field;
			}
			if ( strlen( $gpost_field ) > 2 ) {
				$value = $value . "('$identifier','$gpost_field','$alias','$post_type'" . '),';
				$write_checker = true;
			}
		} else {
			$exits_checker = $wpdb->get_results( $wpdb->prepare( 'SELECT alias from wp_grapql_support where  identifier = %s AND  field = %s' ,$identifier, $gpost_field ) ); // db call ok; no-cache ok.
			foreach ( $exits_checker as $erow ) {
				$old_alias = $erow->alias;
			}
			if ( $old_alias !== $alias ) {
				if ( strlen( $gpost_field ) > 2 && strlen( $alias ) > 2 ) {
					$update_query = "UPDATE wp_grapql_support SET alias='$alias' where identifier = '$identifier' AND field ='$gpost_field' AND name='$post_type';";
					dbDelta( $update_query );
					$write_checker = true;
				}
			}
		}
	}
	if ( strlen( $value ) > 5 ) {
		$query = trim( $value,',' );
		$query = $default_value . $query;
		dbDelta( $query );
	}
	return $write_checker;
}
/**
 * Write mutation field to file.
 */
function gql_mutation_writefile() {
	global $wpdb;
	$result = $wpdb->get_results( "Select alias,field,name from wp_grapql_support where identifier='mutation' order by name" ); // db call ok; no-cache ok.
	$file = dirname( __FILE__ ) . '/support/mutationsupport.php';
	if ( count( $result ) > 0 ) {
		$current = '<?php ';
		$oldpost_name = '';
		$loop_checker = 0;
		$add_mutation_fields = '';
		$save_mutation_fields = '';
		foreach ( $result as $row ) {
			if ( substr( strtolower( $row->name ), -1 ) === 's' ) {
				$singular_name = chop( strtolower( $row->name ),'s' );
			} else {
				$singular_name = $row->name;
			}
			$field_name_arr = explode( '-',$row->alias );
			$field_name = $field_name_arr[0] . ucfirst( $field_name_arr[1] );
			// Add field to post object mutation.
			$add_mutation_fields .= 'if ("' . $row->name . '" === $post_type_object->name ) {
												$fields["' . $row->alias . '"] = [
													"type" => WPGraphQL\Types::string(),
													"description" => __( "The sources of the post", "graphql-extension-namespace" ),		        
												];	 
										}';
			$save_mutation_fields .= 'if ( ! empty( $input["' . $row->alias . '"] ) && "' . $row->name . '" === $post_type_object->name  ) {
												   update_post_meta( $post_id,"' . $row->field . '", $input["' . $row->alias . '"] );
										   } ' ;
		}
		if ( strlen( $add_mutation_fields ) > 20 ) {
			$current .= 'add_filter( "graphql_post_object_mutation_input_fields", "add_fields_to_post_object_mutation", 10, 2 );
							function add_fields_to_post_object_mutation( $fields, $post_type_object ) {';
			$current .= $add_mutation_fields . ' return $fields;}';
			$current .= 'add_action( "graphql_post_object_mutation_update_additional_data", "save_post_object_mutation_fields", 10, 4 );
							function save_post_object_mutation_fields( $post_id, $input, $post_type_object, $mutation_name ) {';
			$current .= $save_mutation_fields . '}';
			file_put_contents( $file, $current );
		}
	} else {
		$current = '';
		file_put_contents( $file, $current );
	}
}
/**
 * Ajax hook
 */
add_action( 'wp_ajax_gql_support_remove_mut_fields', 'gql_support_remove_mut_fields' );
/**
 * Remove  the grapql mutation support from post fields.
 */
function gql_support_remove_mut_fields() {
	global  $wpdb;
	$update_query = '';
	if ( isset( $_POST['remvmutnonce'] ) ) { // Input var okay.
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['remvmutnonce'] ) ) , 'remove-mut-field' ) ) {// Input var okay.
			if ( isset( $_POST['mrfields'] ) ) {// Input var okay.
				$mrfields = sanitize_text_field( wp_unslash( $_POST['mrfields'] ) );// Input var okay.
			}
			$gpost_fields = explode( ',', $mrfields );
			$delete_checker = false;
			if ( isset( $_POST['posttype'] ) ) {// Input var okay.
				$fpost_type = sanitize_text_field( wp_unslash( $_POST['posttype'] ) ); // Input var okay.
				if ( strlen( $fpost_type ) > 2 ) {
					if ( isset( $gpost_fields ) && count( $gpost_fields ) > 0 ) {
						foreach ( $gpost_fields as $gpost_field ) {
							$gpost_field = esc_sql( strip_tags( wp_unslash( $gpost_field ) ) );
							if ( isset( $_POST[ "$gpost_field" ] ) ) { // Input var okay.
								$alias = sanitize_text_field( wp_unslash( $_POST[ "$gpost_field" ] ) ); // Input var okay.
							}
							$count_checker = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM wp_grapql_support WHERE identifier='mutation' AND name = %s AND field =%s", $fpost_type, $gpost_field ) ); // db call ok; no-cache ok.
							foreach ( $count_checker as $crow ) {
								$delete_id = $crow->id;
							}
							if ( $delete_id > 0 ) {
								$ret = $wpdb->delete(
									'wp_grapql_support', array(
										'ID' => $delete_id,
									)
								); // db call ok; no-cache ok.
								if ( $ret > 0 ) {
									$delete_checker = true;
								}
							}
						}
						if ( $delete_checker ) {
							gql_mutation_writefile();
							$output = 'Mutations removed successfully';
						} else {
							$output = 'Some thing went wrong. Please try again.';
						}
					} else {
						$output = 'Something went wrong please try again.';
					}
				} else {
					$output = 'Invalid post type';
				}
			} else {
				$output = 'Invalid post type';
			}
		}
	}
	echo wp_json_encode( $output );
	die();
}
// Include the files.
require_once dirname( __FILE__ ) . '/support/postsupport.php';
require_once dirname( __FILE__ ) . '/support/fieldsupport.php';
require_once dirname( __FILE__ ) . '/support/mutationsupport.php';

