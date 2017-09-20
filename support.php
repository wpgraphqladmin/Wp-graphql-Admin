<?php
/*
Plugin Name: GraphQL Admin
Description: GraphQL Admin
Author: Netspective
Version: 1.0
Author URI: "https://www.netspective.com"
*/
?>
<?php
register_activation_hook( __FILE__, 'gql_activation' );
function gql_activation(){
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global  $wpdb;	
	$query_result   = $wpdb->get_results("SHOW TABLES LIKE 'wp_grapql_support'"); 		
	if (empty($query_result)) {				
		$charset_collate = $wpdb->get_charset_collate();
		$query = "CREATE TABLE `wp_grapql_support` (
						`id` BIGINT(11) AUTO_INCREMENT,  		 
						`identifier` varchar(50),
						`field` varchar(50),
						`alias` varchar(50),
						`name` varchar(50),
						`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE     CURRENT_TIMESTAMP,     
						PRIMARY KEY (`id`)
					)ENGINE=MYISAM DEFAULT CHARSET=latin1;";                       
		dbDelta( $query );
		/*$query  = "insert into wp_grapql_support (identifier,field,alias,name) values('posttype','','','post'),('posttype','','','page');";
		$database_result = dbDelta( $query);*/ 
		$wpdb->insert('wp_grapql_support', array(
			    'identifier' => 'posttype',
			    'field' => '',
			    'alias' => '', 
			    'name' => 'post',
		));
		$wpdb->insert('wp_grapql_support', array(
			    'identifier' => 'posttype',
			    'field' => '',
			    'alias' => '', 
			    'name' => 'page'
		));
	}	
}
add_action('admin_enqueue_scripts', 'gql_load_scripts_style');
function gql_load_scripts_style($hook) { 	
	wp_enqueue_script( 'ajax-script',plugin_dir_url( __FILE__ ) .'js/gqlscript.js' );
	wp_enqueue_style( 'gql-styles',  plugin_dir_url( __FILE__ ) . 'css/gqlstyle.css');
	 wp_localize_script( 'ajax-script', 'ajaxhandler',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	
}

add_action('admin_menu', 'gql_admin_create_menu' );
/**
* Add the plugin menu in the dashboard.
*
* Add the menus GraphQL Admin,Post Support,Field Support and Mutation Support
*/
function gql_admin_create_menu() {
	add_menu_page(__('GraphQL Support Page'), __('GraphQL Admin'), 'manage_options', 'gql_setting_page', 'gql_post_support_page');
	add_submenu_page('gql_setting_page', __('Post Support'), __('Post Support'), 'manage_options', 'gql_setting_page','gql_post_support_page' );
	add_submenu_page('gql_setting_page', __('Field Support'), __('Field Support'), 'manage_options', 'gql_field_setting_page' ,'gql_filed_support_page');
	add_submenu_page('gql_setting_page', __('Mutation Support'), __('Mutation Support'), 'manage_options', 'gql_mutation_setting_page','gql_mutation_support_page' );
}
/**
* Displays the post types 
*
* Display the available post types for adding the grapql post support.
*/
function gql_post_support_page(){
	gql_post_types('post');
}
/**
* Displays the post types 
*
* Display the available post types for adding the grapql mutation support.
*/
function gql_mutation_support_page(){
	global $wpdb;
	gql_post_types('mutation');	
	if(isset($_POST['posttype'])){
			$metakey_html = "";
			$remove_metakey_html = "";
			$alias_fields_names = array();
			$alias_mutations_names = array();
			foreach($_POST['posttype'] as $posttype){
					$fposttype = trim($posttype); 					
					$fposttype = esc_sql( strip_tags( wp_unslash($fposttype)));
					$meta_keys = generate_meta_keys($fposttype);
					$query = "select field,alias from wp_grapql_support where identifier='field' AND name='$fposttype'";
					$alias_name_result =$wpdb->get_results($query);					
					foreach ($alias_name_result as $row ) {  
						$alias_fields_names[$row->field] =  $row->alias;						
					}	
					$query = "select field,alias from wp_grapql_support where identifier='mutation' AND name='$fposttype'";
					$alias_name_result =$wpdb->get_results($query);					
					foreach ($alias_name_result as $row ) {  
						$alias_mutations_names[$row->field] =  $row->alias;
						$remove_metakey_html = $remove_metakey_html.'
						<li><input type="checkbox" name="mrmetakeys[]" value="'. $row->field.'"  />'.$row->field .'</li>';	
					}						
					foreach ( $meta_keys as $meta_key ) { 	
						if (array_key_exists($meta_key, $alias_mutations_names)) {
							$meta_key_value = $alias_mutations_names[$meta_key] ;
							$checked = "checked";
						}else if (array_key_exists($meta_key, $alias_fields_names)) {
							$meta_key_value = $alias_fields_names[$meta_key] ;
							$checked = "" ;
						}else{							
							$field_name_arr = explode("-",$meta_key);	
							if(sizeof($field_name_arr) > 1){
								$field_name = " ";
								for($j=0;$j < sizeof($field_name_arr);$j++){
									if($j == 0 ){
										$fname = $field_name_arr[$j];
									}else{
										$fname = ucfirst($field_name_arr[$j]);
									}
									$field_name = $field_name .$fname;
								}
								
								$meta_key_value =trim($field_name);	
							}else{

							}	
							$checked = "" ;
						}
						$metakey_html = $metakey_html.'
						<li>
						<input type="checkbox" name="mmetakeys[]" value="'. $meta_key.'" '.$checked.' /><span>' . $meta_key. 
						'</span><input type="text" name="'. $meta_key.'"  value="'.$meta_key_value.'" /> </li>';						
					} 
					echo '<input type="hidden" id= "fposttype" name="fposttype" value="'.$fposttype.'"/>';	
					?>
					<h1 class="marg">Custom Fields  of <?php echo $fposttype ;?></h1>					
					<form id="mfields" name="mfields">
					<ul class="gph-met-ul">
						<?php echo $metakey_html;?>
					</ul>				
					<div class="clear-fix"></div>
	      				<input type="button" value="Add Mutaion Support"   class="button button-primary button-large" onclick="saveGraphqlMutationFields()" />	
	      			</form>		
					<?php	
					if(sizeof($alias_mutations_names ) > 0 ) {?>
						<h1 class="marg">Remove Fields</h1>
						<form id="mfields" name="mfields">
							<ul class="gph-met-ul">
								<?php echo $remove_metakey_html;?>
							</ul>				
							<div class="clear-fix"></div>
			      			<input type="button" value="Remove Fields"  class="button button-primary button-large"  onclick="removeGraphqlMutFields()" />	
		      			</form>	

				<?php }
				break;
			}

	}	
}
/**
* Displays the post types 
*
* Display the available post types for adding the grapql field support.
*/
function gql_filed_support_page(){
    global $wpdb;
	gql_post_types('field');	
	if(isset($_POST['posttype'])){
			$metakey_html = "";
			$remove_metakey_html = "";
			$alias_fields_names = array();
			foreach($_POST['posttype'] as $posttype){
					$fposttype = trim($posttype); 
					$fposttype = esc_sql( strip_tags( wp_unslash($fposttype)));
					$meta_keys = generate_meta_keys($fposttype);								
					$query = "select field,alias from wp_grapql_support where identifier='field' AND name='$fposttype'";
					$alias_name_result =$wpdb->get_results($query);
					foreach ($alias_name_result as $row ) {  
						$alias_fields_names[$row->field] =  $row->alias;
						$remove_metakey_html = $remove_metakey_html.'
						<li><input type="checkbox" name="rmetakeys[]" value="'. $row->field.'"  />'.$row->field .'</li>';	
					}				
					foreach ( $meta_keys as $meta_key ) { 	
						if (array_key_exists($meta_key, $alias_fields_names)) {
							$meta_key_value = $alias_fields_names[$meta_key] ;
							$checked = "checked";
						}else{
							$field_name_arr = explode("-",$meta_key);	
							if(sizeof($field_name_arr) > 1){
								$field_name = " ";
								for($j=0;$j < sizeof($field_name_arr);$j++){
									if($j == 0 ){
										$fname = $field_name_arr[$j];
									}else{
										$fname = ucfirst($field_name_arr[$j]);
									}
									$field_name = $field_name .$fname;
								}
								
								$meta_key_value =trim($field_name);	
							}else{

							}							
							$checked = "" ;
						}
						$metakey_html = $metakey_html.'
						<li>
						<input type="checkbox" name="metakeys[]" value="'. $meta_key.'" '.$checked.' /><span>' . $meta_key. 
						'</span><input type="text" name="'. $meta_key.'"  value="'.$meta_key_value.'" onKeyPress="return onlyText(event)"   onkeyup="return onlyText(event)" /> </li>';						
					} 
					echo '<input type="hidden" id= "fposttype" name="fposttype" value="'.$fposttype.'"/>';	
					$hide_class = '';
					if(strlen (trim($metakey_html)) < 5 ){
						$metakey_html = "No fields to display";
						$hide_class = "hide";
					}else{												
					}	

					?>
					<h1 class="marg <?php echo $hide_class ?>">Custom Fields  of <?php echo $fposttype ;?></h1>					
					<form id="afields" name="afields">
					<ul class="gph-met-ul">
						<?php 
								echo $metakey_html;				
						?>
					</ul>				
					<div class="clear-fix"></div>
	      			<input type="button" value="Add Fields"  class="button button-primary button-large <?php echo $hide_class; ?>"   onclick="saveGraphqlFields()" />	
	      			</form>		
					<?php	
					if(sizeof($alias_fields_names ) > 0 )	{?>
						<h1 class="marg">Remove Fields</h1>
						<form id="rfields" name="rfields">
							<ul class="gph-met-ul">
								<?php echo $remove_metakey_html;?>
							</ul>				
							<div class="clear-fix"></div>
			      			<input type="button" value="Remove Fields" class="button button-primary button-large"  onclick="removeGraphqlFields()" />	
		      			</form>	

				<?php }
				break;
			}

	}			
}
/**
* Displays the post types 
*
* Display the available post types.
* @param string $viewpost      variable to check the current action. ie post support,field support or mutation suport
*							   Default value is post.
* @param string $display_not_enabled      variable to check the not added post types.Default value is false.
*/
function gql_post_types($viewpost ='post',$display_not_enabled = "false"){	
	global $wpdb;
	$post_names = array();
	$field_buttons = '<div>Note: If you do not see the posts for which you like to enable GraphQL support, please go to Post Support tab and select and enable.</div>
	<input type="submit" class="button button-primary button-large" value="View Post Fields" />';
	$post_buttons = '<input type="button" value="Add Post Support" class="button button-primary button-large" onclick="gqlPostSupport()" />';   
	$post_names  = get_graphql_added_posts();
	if($viewpost == 'post'){
		$action =admin_url( 'admin.php?page=gql_post_setting_page' );
		$title ="GraphQL Post Support";
		$buttons =  $post_buttons;
		$added_posts_class='class="added-posts"';
	}else if($viewpost == 'field'){		
		
		$action =admin_url( 'admin.php?page=gql_field_setting_page' );
		$title ="GraphQL Field Support";
		$buttons =  $field_buttons;		
		$added_posts_class='';
	}else if($viewpost == 'mutation'){
		$action =admin_url( 'admin.php?page=gql_mutation_setting_page' );
		$title ="GraphQL Mutation Support";
		$buttons =  $field_buttons;
		$added_posts_class='';
	}
	$post_types = get_post_types( '', 'names' );  
	?>
	<div class="wrap"><div id="icon-tools" class="icon32"></div>
		<h2><?php echo $title ?></h2>
	</div>		
   	<?php
   		$not_added_post = $added_post = "";
		foreach ( $post_types as $post_type ) { 
			if(isset($_POST['posttype'][0]) && trim($_POST['posttype'][0]) == $post_type){
				$checked = 'checked';
			}else{
				$checked ='';
			}
			$disabled='';
			$click = '';
			if(array_key_exists( $post_type, $post_names)){			
				if($viewpost == 'post' ){	
					$checked='checked';	
					$disabled=' disabled';	
					
				}else{
					$click = ' onclick="selectOneItem(event)"';		
				}
				$added_post .= '<li><input type="checkbox"   name="posttype[]" value="'. $post_type.'" '. $checked. $disabled . $click.'  />' . $post_type . '</li>';
			}else{
				if($display_not_enabled){
					if($viewpost == 'post' ){					
					}else{
						$disabled='disabled';
					}
					$not_added_post .= '<li><input type="checkbox"   name="posttype[]" value="'. $post_type.'" '. $checked. $disabled . $click.'  />' . $post_type . '</li>';
				}
			}		
			//echo '<li><input type="checkbox"   name="posttype[]" value="'. $post_type.'" '. $checked. $disabled .'   />' . $post_type . '</li>';	
		} 
		?>
		<form method="POST"  id= "fieldform" action="<?php echo  $action; ?>">
		<div class="post-container">				
			<div <?php echo $added_posts_class; ?>>
				<h1>Enabled</h1>
				<div class="gql-note">Posts that have the GraphQL support.</div>
				<?php echo '<ul class="gph-ul">'.$added_post.'</ul>'; ?>
				<div class="clear-fix"></div>
			</div>
			<?php 
			if($viewpost == 'post'){				
			?>	
			<div class="not-added-posts">
				<h1>Not Enabled</h1>
				<div class="gql-note">Please select the post for which you like to enable GraphQL support.</div>
				<?php echo '<ul class="gph-ul">'.$not_added_post.'</ul>'; ?>
				<div class="clear-fix"></div>
			</div>

			<?php }else{
				//echo '<ul class="gph-ul">'.$not_added_post.$added_post.'</ul>';
			}
	?>	
	<div class="clear-fix"></div>
	</div>
	<div class="clear-fix"></div>
	<?php echo $buttons; ?>				
	</form>
	<?php 	
}
/**
* Displays the grapql supported post types 
*
* Displays the grapql supported post types 
*
*/
function get_graphql_added_posts(){
	global $wpdb;
	$post_names = array();
	$query_result   = $wpdb->get_results("SHOW TABLES LIKE 'wp_grapql_support'"); 		
	if (!empty($query_result)) {		
		$query = "select name from wp_grapql_support where identifier='posttype'";
		$post_name_result =$wpdb->get_results($query);		
		foreach ($post_name_result as $row ) {  
			$post_names[$row->name] =  $row->name;		
		}
	}	
	$post_names['post'] =  'post';	
	$post_names['page'] =  'page';	
	return 	$post_names;
}
/**
* Displays the metakeys of a particular post types. 
*
* Metakeys of a particular post types.  
*  @param string $post_type 	Name of the Post type 
*/
function generate_meta_keys($post_type ){
    global $wpdb;  
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s' 
        AND $wpdb->postmeta.meta_key != '' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
    ";
    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
    set_transient('gql_meta_keys', $meta_keys, 60*60*24); # create 1 Day Expiration
    return $meta_keys;
}
function get_gql_meta_keys(){
    $cache = get_transient('gql_meta_keys');
    $meta_keys = $cache ? $cache : generate_meta_keys();
    return $meta_keys;
}
/**
* Enable the grapql support to post. 
*
* 
*/
add_action( 'wp_ajax_gql_support_add_posts', 'gql_support_add_posts' );
function gql_support_add_posts(){
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global  $wpdb;
	$post_names = array('post','page');	
	$prefix_insert_query = "INSERT INTO wp_grapql_support (identifier,field,alias,name) VALUES ";
	$insert_query = "";
	$already_added_checker =false;
	$query_checker = false;
	$gpost_types = explode(",",$_POST['ptypes']);
	$already_added_posttypes = "";
	if(isset($gpost_types) && sizeof($gpost_types) > 0 ){
		$query_result   = $wpdb->get_results("SHOW TABLES LIKE 'wp_grapql_support'"); 		
		if (empty($query_result)) {				
			$charset_collate = $wpdb->get_charset_collate();
			$query = "CREATE TABLE `wp_grapql_support` (
							`id` BIGINT(11) AUTO_INCREMENT,  		 
							`identifier` varchar(50),
							`field` varchar(50),
							`alias` varchar(50),
							`name` varchar(50),
							`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE     CURRENT_TIMESTAMP,     
							PRIMARY KEY (`id`)
						)ENGINE=MYISAM DEFAULT CHARSET=latin1;";                       
			dbDelta( $query );
		}	
		foreach($gpost_types as $gtype){	
			$gtype = trim ($gtype);
			if(strlen($gtype) > 2 ){				
				//Check to exists				
				$gtype = esc_sql( strip_tags( wp_unslash($gtype)));
				$query ="SELECT EXISTS (SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = '$gtype' )
				 AS counts";			
				$result =$wpdb->get_results($query);
				foreach ($result as $row ) {  
					$count = $row->counts;
				}					
				if($count ==  0 ){	
					if (in_array($gtype, $post_names)) {					
					}else{
						$insert_query .= "( 'posttype','','','$gtype'),";												
					}
				}else{
					//$already_added_checker = true;
					//$already_added_posttypes .= "$gtype, ";	
				}	
			}else{
				$output = "Mininum character of posttype is alleast two";
			}
		}		
		if(strlen($insert_query) > 15 ){			
			$insert_query = trim($insert_query,",");
			$query  = $prefix_insert_query.$insert_query.";";			
			$database_result = dbDelta( $query); 
			$query_checker = true;
			if($wpdb->last_error !== ''){
				$output = "Something went wrong. Please try again";	
				echo json_encode($output );
				die();
			}
		}
		////GrapQL write file
		if($query_checker){
			$query = "Select name from wp_grapql_support where identifier='posttype' ";
			$result =$wpdb->get_results($query);
			if(sizeof($result) > 0 ){
				$file =  dirname( __FILE__ ) . '/support/postsupport.php';				
				$current ='<?php    add_action( "do_graphql_request", function() { global $wp_post_types;'; 
	    		foreach ($result as $row ) {  								
					if(isset($row->name) && strlen($row->name) > 2) {
						if(substr(strtolower($row->name), -1) == "s"){
							$singular_name = chop(strtolower($row->name),"s");
							$plural_name = $row->name;
						}else{
							$plural_name = $row->name."s";
							$singular_name = $row->name;
						}	
						$current .='if ( isset( $wp_post_types["'.$row->name.'"] ) ) {
						$wp_post_types["'.$row->name.'"]->show_in_graphql     = true;
						$wp_post_types["'.$row->name.'"]->graphql_single_name = "'.$singular_name.'";
						$wp_post_types["'.$row->name.'"]->graphql_plural_name = "'.$plural_name.'";
						}';	
					}
				}	
				if(strlen($current) > 30 ){
					$current .= "} );";
					file_put_contents($file, $current);	
				}
			}
		}
		if($already_added_checker){
			$already_added_posttypes  = trim($already_added_posttypes,', ');
			$output = ucfirst($already_added_posttypes)." post types are already added.";
		}else{
		$output = "GrapQl Support Added Successfully";	
		}
		echo json_encode($output );		
	}
	die();
}
/**
* Enable the grapql field support to post fields. 
*
*/
add_action( 'wp_ajax_gql_support_add_fields', 'gql_support_add_fields' );
function gql_support_add_fields(){		
	global  $wpdb;
	$update_query ="";
	$query_checker = false;
	$gpost_fields = explode(",",$_POST['afields']);	
	$query_result   = $wpdb->get_results("SHOW TABLES LIKE 'wp_grapql_support'"); 
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	if (empty($query_result)) {	
		$output = "Please add the post first to add the fields.";
	}else{
		if(isset($_POST['posttype']) && strlen($_POST['posttype']) > 2) {				
			$fpost_type =  trim($_POST['posttype']);			
			$fpost_type = esc_sql( strip_tags( wp_unslash($fpost_type)));
			$query ="SELECT EXISTS (SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = '$fpost_type' )
				 AS counts";			
			$result =$wpdb->get_results($query);
			foreach ($result as $row ) {  
				$count = $row->counts;
			}	
			if($count == 0 ){
				$output = "Please add the post first to add the fields.";
			}else{
				if(isset($gpost_fields) && sizeof($gpost_fields) > 0 ){
					$value=" ";
					$default_value="INSERT INTO wp_grapql_support (identifier,field,alias,name) VALUES";
					foreach($gpost_fields as $gpost_field){
						$gpost_field = strip_tags( wp_unslash($gpost_field));
						$gpost_field = esc_sql($gpost_field);
						$alias = trim($_POST["$gpost_field"]);
						$query ="SELECT EXISTS (SELECT 1 FROM wp_grapql_support WHERE identifier='field' AND name = '$fpost_type' AND field ='$gpost_field' )
							AS counts";			
							$count_checker =$wpdb->get_results($query);
							foreach ($count_checker as $crow ) {  
								$count = $crow->counts;
							}	
						if($count == 0)	{							
							if(strlen($alias) > 2){
							}else{
								$alias = $gpost_field;
							}
							if(strlen($gpost_field) > 2 && strlen($alias) > 2 ) {
								$value = $value."('field','$gpost_field','$alias','$fpost_type'"."),";
							}
						}else{
							$query = "SELECT alias from wp_grapql_support where  identifier='field' AND  field = '$gpost_field'";
							$exits_checker =$wpdb->get_results($query);
							foreach ($exits_checker as $erow ) {  
								$old_alias = $erow->alias;
							}	
							if($old_alias == $alias){}else{
								if(strlen($gpost_field) > 2 && strlen($alias) > 2 ) {
									$update_query = "UPDATE wp_grapql_support SET alias='$alias' where identifier='field'  AND field ='$gpost_field' AND name='$fpost_type';";									
									dbDelta($update_query );
									$query_checker=true;
								} 
							}
						}
					}
					if(strlen($value) > 5 ){
						$query = trim($value,",");
						$query = $default_value.$query;
						dbDelta( $query ); 
						$query_checker=true; 
					}
					//
					if($query_checker){
						$query = "Select alias,field,name from wp_grapql_support where identifier='field' order by name";
						$result =$wpdb->get_results($query);
						if(sizeof($result) > 0 ){
							$file =  dirname( __FILE__ ) . '/support/fieldsupport.php';				
							$current ='<?php '; 
							$oldpost_name = "";
							$loop_checker = 0;						
							foreach($result as $row){									
								if(substr(strtolower($row->name), -1) == "s"){
									$singular_name = chop(strtolower($row->name),"s");									
								}else{
									
									$singular_name = $row->name;
								}	
								$field_name_arr = explode("-",$row->alias);	
								if(sizeof($field_name_arr) > 1){
									$field_name = $field_name_arr[0].ucfirst($field_name_arr[1]);	
								}else{
									$field_name =$row->alias;
								}
								$field_name = str_replace(' ', '', $field_name );
								if($oldpost_name == $row->name){
									$current .='$fields["'.$field_name.'"] = [
											        "type" => WPGraphQL\Types::string(),
											        "description" => __( "The sources of the post", "my-graphql-extension-namespace" ),
											        "resolve" => function( \WP_Post $'.$row->name.', array $args, $context, $info ) {
											            $'.str_replace("-","_",$row->field).' = get_post_meta( $'.$row->name.'->ID, "'.$row->field.'", true );
											            return ( ! empty( $'.str_replace("-","_",$row->field).' ) && is_string( $'.str_replace("-","_",$row->field).' ) ) ? $'.str_replace("-","_",$row->field).' : null;
											        },
											 ];';
								}else{
									if( $loop_checker == 1){
										$current .= "}";
									}
									$current .= "add_filter( 'graphql_".$singular_name."_fields', '".$row->name."_customfield' );";
									$current .= 'function '.$row->name.'_customfield( $fields ) { ';
									$current .='$fields["'.$field_name .'"] = [
											        "type" => WPGraphQL\Types::string(),
											        "description" => __( "The sources of the post", "my-graphql-extension-namespace" ),
											        "resolve" => function( \WP_Post $'.$row->name.', array $args, $context, $info ) {
											            $'.str_replace("-","_",$row->field).' = get_post_meta( $'.$row->name.'->ID, "'.$row->field.'", true );
											            return ( ! empty( $'.str_replace("-","_",$row->field).' ) && is_string( $'.str_replace("-","_",$row->field).' ) ) ? $'.str_replace("-","_",$row->field).' : null;
											        },
											 ];';
									$oldpost_name =  $row->name;
									 $loop_checker =1;
								}									
							}
							if(strlen($current )> 20){
								$current .= ' return $fields;}';
								file_put_contents($file, $current);	
							}
						}
					}	
				}				
				$output = "Fields added successfully";
			}
		}else{
			$output =" Invalid post type";
		}		
	}
	echo json_encode($output);	
	die();		
}
/**
* Remove  the grapql filed support from post fields. 
*
*/
add_action( 'wp_ajax_gql_support_remove_fields', 'gql_support_remove_fields' );
function gql_support_remove_fields(){	
	global  $wpdb;
	$update_query ="";
	$gpost_fields = explode(",",$_POST['rfields']);	
	$delete_checker = false;	
	if(isset($_POST['posttype']) && strlen($_POST['posttype']) > 2) {
		$fpost_type =  trim($_POST['posttype']);
		$fpost_type = esc_sql( strip_tags( wp_unslash($fpost_type)));			
		if(isset($gpost_fields) && sizeof($gpost_fields) > 0 ){		
			foreach($gpost_fields as $gpost_field){
				$gpost_field = esc_sql( strip_tags( wp_unslash($gpost_field)));				
				$alias = trim($_POST["$gpost_field"]);
				$query ="SELECT id FROM wp_grapql_support WHERE identifier='field' AND name = '$fpost_type' AND field ='$gpost_field'";	
					$count_checker =$wpdb->get_results($query);
					foreach ($count_checker as $crow ) {  
						$delete_id = $crow->id;
					}	
				if($delete_id > 0 )	{	
					
					$ret = $wpdb->delete( 'wp_grapql_support', array( 'ID' => $delete_id ) );		
					if($ret > 0){
						$delete_checker = true;
					}
				}
			}		
			if($delete_checker) {
				$query = "Select alias,field,name from wp_grapql_support where identifier='field' order by name";
				$result =$wpdb->get_results($query);
				$file =  dirname( __FILE__ ) . '/support/fieldsupport.php';		
				if(sizeof($result) > 0 ){							
					$current ='<?php '; 
					$oldpost_name = "";
					$loop_checker = 0;						
					foreach($result as $row){									
							if(substr(strtolower($row->name), -1) == "s"){
								$singular_name = chop(strtolower($row->name),"s");									
							}else{
								
								$singular_name = $row->name;
							}	
							$field_name_arr = explode("-",$row->alias);	
							$field_name = $field_name_arr[0].ucfirst($field_name_arr[1]);	
							if($oldpost_name == $row->name){
								$current .='$fields["'.$field_name .'"] = [
										        "type" => WPGraphQL\Types::string(),
										        "description" => __( "The sources of the post", "my-graphql-extension-namespace" ),
										        "resolve" => function( \WP_Post $'.$row->name.', array $args, $context, $info ) {
										            $'.str_replace("-","_",$row->field).' = get_post_meta( $'.$row->name.'->ID, "'.$row->field.'", true );
										            return ( ! empty( $'.str_replace("-","_",$row->field).' ) && is_string( $'.str_replace("-","_",$row->field).' ) ) ? $'.str_replace("-","_",$row->field).' : null;
										        },
										 ];';
							}else{
								if( $loop_checker == 1){
									$current .= "}";
								}
								$current .= "add_filter( 'graphql_".$singular_name."_fields', '".$row->name."_cutomfield' );";
								$current .= 'function '.$row->name.'_cutomfield( $fields ) { ';
								$current .='$fields["'.$field_name .'"] = [
										        "type" => WPGraphQL\Types::string(),
										        "description" => __( "The sources of the post", "my-graphql-extension-namespace" ),
										        "resolve" => function( \WP_Post $'.$row->name.', array $args, $context, $info ) {
										            $'.str_replace("-","_",$row->field).' = get_post_meta( $'.$row->name.'->ID, "'.$row->field.'", true );
										            return ( ! empty( $'.str_replace("-","_",$row->field).' ) && is_string( $'.str_replace("-","_",$row->field).' ) ) ? $'.str_replace("-","_",$row->field).' : null;
										        },
										 ];';
								$oldpost_name =  $row->name;
								 $loop_checker =1;
							}	
							
					}
					$current .= ' return $fields;}';
					file_put_contents($file, $current);	
				}else{
					file_put_contents($file, '');	
				}
			}
			///

		}		
		$output = "Fields removed successfully";		
	}else{
		$output ="Invalid post type";
	}	
	echo json_encode($output);	
	die();
}
/**
* Enable the grapql mutation support to post fields. 
* 
*/
add_action( 'wp_ajax_gql_support_mutation_fields', 'gql_support_mutation_fields' );
function gql_support_mutation_fields(){
	global  $wpdb;
	$update_query ="";
	$gpost_fields = explode(",",$_POST['mfields']);	
	$query_result   = $wpdb->get_results("SHOW TABLES LIKE 'wp_grapql_support'"); 
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	if (empty($query_result)) {	
		$output = "Please add the post first to add the fields.";
	}else{
		if(isset($_POST['posttype']) && strlen($_POST['posttype']) > 2) {
			$fpost_type =  trim($_POST['posttype']);
			$fpost_type = esc_sql( strip_tags( wp_unslash($fpost_type)));
			$query ="SELECT EXISTS (SELECT 1 FROM wp_grapql_support WHERE identifier='posttype' AND name = '$fpost_type' )
				 AS counts";			
			$result =$wpdb->get_results($query);
			foreach ($result as $row ) {  
				$count = $row->counts;
			}	
			if($count == 0 ){
				$output = "Please add the post first to add the fields.";
			}else{
				if(isset($gpost_fields) && sizeof($gpost_fields) > 0 ){
					$value=" ";
					$default_value="INSERT INTO wp_grapql_support (identifier,field,alias,name) VALUES";
					$write_checker = false;	
					foreach($gpost_fields as $gpost_field){
						$gpost_field = esc_sql( strip_tags( wp_unslash($gpost_field)));
						$alias = trim($_POST["$gpost_field"]);
						$query ="SELECT EXISTS (SELECT 1 FROM wp_grapql_support WHERE identifier='mutation' AND name = '$fpost_type' AND field ='$gpost_field' )
							AS counts";			
							$count_checker =$wpdb->get_results($query);
							foreach ($count_checker as $crow ) {  
								$count = $crow->counts;
							}	
						if($count == 0)	{													
							if(strlen($alias) > 2){
							}else{
									$alias = $gpost_field;
							}
							if(strlen($gpost_field) > 2 ) {
								$value = $value."('mutation','$gpost_field','$alias','$fpost_type'"."),";
								$write_checker = true;	
							}
						}else{
							$query = "SELECT alias from wp_grapql_support where  identifier = 'mutation' AND  field = '$gpost_field'";
							$exits_checker =$wpdb->get_results($query);
							foreach ($exits_checker as $erow ) {  
								$old_alias = $erow->alias;
							}	
							if($old_alias == $alias){}else{
								if(strlen($gpost_field) > 2 && strlen($alias) > 2 ) {
									$update_query = "UPDATE wp_grapql_support SET alias='$alias' where identifier = 'mutation' AND field ='$gpost_field' AND name='$fpost_type';";	
									dbDelta($update_query );
									$write_checker = true;	
								} 
							}
						}
					}
					if(strlen($value) > 5 ){
						$query = trim($value,",");
						$query = $default_value.$query;
						dbDelta( $query ); 
					}
					//
					if($write_checker){
							$query = "Select alias,field,name from wp_grapql_support where identifier='mutation' order by name";
							$result =$wpdb->get_results($query);
							if(sizeof($result) > 0 ){
								$file =  dirname( __FILE__ ) . '/support/mutationsupport.php';				
								$current ='<?php '; 
								$oldpost_name = "";
								$loop_checker = 0;	
								$add_mutation_fields = "";
								$save_mutation_fields = "";
								foreach($result as $row){									
										if(substr(strtolower($row->name), -1) == "s"){
											$singular_name = chop(strtolower($row->name),"s");									
										}else{
											
											$singular_name = $row->name;
										}	
										$field_name_arr = explode("-",$row->alias);	
										$field_name = $field_name_arr[0].ucfirst($field_name_arr[1]);	
										// Add field to post object mutation
										$add_mutation_fields .= 'if ("'. $row->name.'" === $post_type_object->name ) {
																		$fields["'. $row->alias.'"] = [
																			"type" => WPGraphQL\Types::string(),
																			"description" => __( "The sources of the post", "graphql-extension-namespace" ),		        
																		];	 
																}';
										$save_mutation_fields .= 'if ( ! empty( $input["'.$row->alias.'"] ) && "'.$row->name.'" === $post_type_object->name  ) {
		     														   update_post_meta( $post_id,"'.$row->field.'", $input["'.$row->alias.'"] );
	    														   } ' ;
								}
								if(strlen($add_mutation_fields )> 20){
									$current .='add_filter( "graphql_post_object_mutation_input_fields", "add_fields_to_post_object_mutation", 10, 2 );
												function add_fields_to_post_object_mutation( $fields, $post_type_object ) {';
									$current .= $add_mutation_fields. ' return $fields;}';
									$current .='add_action( "graphql_post_object_mutation_update_additional_data", "save_post_object_mutation_fields", 10, 4 );
												function save_post_object_mutation_fields( $post_id, $input, $post_type_object, $mutation_name ) {';
									$current .=	$save_mutation_fields. "}"; 
									file_put_contents($file, $current);	
								}
							}
					}
				}				
				$output = "Mutations added successfully";
			}
		}else{
			$output =" Invalid post type";
		}		
	}
	echo json_encode($output);	
	die();	
}
/**
* Remove  the grapql mutation support from post fields. 
* 
*/
add_action( 'wp_ajax_gql_support_remove_mut_fields', 'gql_support_remove_mut_fields' );
function gql_support_remove_mut_fields(){	
	global  $wpdb;
	$update_query ="";
	$gpost_fields = explode(",",$_POST['mrfields']);	
	$delete_checker = false;	
	if(isset($_POST['posttype']) && strlen($_POST['posttype']) > 2) {
		$fpost_type =  trim($_POST['posttype']);	
		$fpost_type = esc_sql( strip_tags( wp_unslash($fpost_type)));		
		if(isset($gpost_fields) && sizeof($gpost_fields) > 0 ){						
			foreach($gpost_fields as $gpost_field){
				$gpost_field = esc_sql( strip_tags( wp_unslash($gpost_field)));
				$alias = trim($_POST["$gpost_field"]);
				$query ="SELECT id FROM wp_grapql_support WHERE identifier='mutation' AND name = '$fpost_type' AND field ='$gpost_field'";	
					$count_checker =$wpdb->get_results($query);
					foreach ($count_checker as $crow ) {  
						$delete_id = $crow->id;
					}	
				if($delete_id > 0 )	{	
					$ret = $wpdb->delete( 'wp_grapql_support', array( 'ID' => $delete_id ) );		
					if($ret > 0){
						$delete_checker = true;
					}																			
				}
			}					
			//
			if($delete_checker) {			
				$query = "Select alias,field,name from wp_grapql_support where identifier='mutation' order by name";
				$result =$wpdb->get_results($query);
				$file =  dirname( __FILE__ ) . '/support/mutationsupport.php';	
				if(sizeof($result) > 0 ){								
					$current ='<?php '; 
					$oldpost_name = "";
					$loop_checker = 0;	
					$add_mutation_fields = "";
					$save_mutation_fields = "";
					foreach($result as $row){									
							if(substr(strtolower($row->name), -1) == "s"){
								$singular_name = chop(strtolower($row->name),"s");									
							}else{
								
								$singular_name = $row->name;
							}	
							$field_name_arr = explode("-",$row->alias);	
							$field_name = $field_name_arr[0].ucfirst($field_name_arr[1]);	
							// Add field to post object mutation
							$add_mutation_fields .= 'if ("'. $row->name.'" === $post_type_object->name ) {
															$fields["'. $row->alias.'"] = [
																"type" => WPGraphQL\Types::string(),
																"description" => __( "The sources of the post", "graphql-extension-namespace" ),		        
															];	 
													}';
							$save_mutation_fields .= 'if ( ! empty( $input["'.$row->alias.'"] ) && "'.$row->name.'" === $post_type_object->name  ) {
															   update_post_meta( $post_id,"'.$row->field.'", $input["'.$row->alias.'"] );
													   } ' ;
					}
					if(strlen($add_mutation_fields )> 20){
						$current .='add_filter( "graphql_post_object_mutation_input_fields", "add_fields_to_post_object_mutation", 10, 2 );
									function add_fields_to_post_object_mutation( $fields, $post_type_object ) {';
						$current .= $add_mutation_fields. ' return $fields;}';
						$current .='add_action( "graphql_post_object_mutation_update_additional_data", "save_post_object_mutation_fields", 10, 4 );
									function save_post_object_mutation_fields( $post_id, $input, $post_type_object, $mutation_name ) {';
						$current .=	$save_mutation_fields. "}"; 
						file_put_contents($file, $current);	
					}
				}else{
					$current ='';
					file_put_contents($file, $current);
				}
			}
			$output = "Mutations removed successfully";
		}else{
			$output = "Something went wrong please try again.";
		}

	}else{
		$output =" Invalid post type";
	}		
	echo json_encode($output);	
	die();
}
// Include the files
require_once(dirname( __FILE__ ) .'/support/postsupport.php');
require_once(dirname( __FILE__ ) .'/support/fieldsupport.php');
require_once(dirname( __FILE__ ) .'/support/mutationsupport.php');
