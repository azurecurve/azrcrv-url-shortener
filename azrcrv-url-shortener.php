<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name:		URL Shortener
 * Description:		Automatically shortens URls for new posts, of all standard and custom types, and all past posts when loaded for the first time after activation; custom post type allows external links to be shortened.
 * Version:			1.5.5
 * Requires CP:		1.0
 * Requires PHP:	7.4
 * Author:			azurecurve
 * Author URI:		https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/
 * Donate link:		https://development.azurecurve.co.uk/support-development/
 * Text Domain:		url-shortener
 * Domain Path:		/languages
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.html
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname(__FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_urls');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */
// add actions
register_activation_hook(__FILE__, 'azrcrv_urls_install');

// add actions
add_action('admin_menu', 'azrcrv_urls_create_admin_menu');
add_action('admin_post_azrcrv_urls_save_options', 'azrcrv_urls_save_options');
add_action('admin_menu', 'azrcrv_urls_add_sidebar_metabox');
add_action('save_post', 'azrcrv_urls_save_short_url');
add_action('wp_head', 'azrcrv_urls_add_links_to_site_header');
add_action('init', 'azrcrv_urls_redirect_incoming');
add_action('init', 'azrcrv_urls_create_post_type');
add_action('plugins_loaded', 'azrcrv_urls_load_languages');

// add filters
add_filter('plugin_action_links', 'azrcrv_urls_add_plugin_action_link', 10, 2);
add_filter('get_shortlink', 'azrcrv_urls_admin_get_custom_shortlink', 10, 3);
add_filter('codepotent_update_manager_image_path', 'azrcrv_urls_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_urls_custom_image_url');

// add shortcodes
add_shortcode('short-url', 'azrcrv_urls_get_custom_shortlink');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('url-shortener', false, $plugin_rel_path);
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_load_css(){
	wp_enqueue_style('azrcrv-urls', plugins_url('css/style.css', __FILE__), '', '1.0.0');
}

/**
 * Load JQuery.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_load_jquery(){
	wp_enqueue_script('azrcrv-urls', plugins_url('jquery.js', __FILE__), array('jquery'), '3.9.1');
}

/**
 * Custom plugin image path.
 *
 * @since 1.4.0
 *
 */
function azrcrv_urls_custom_image_path($path){
    if (strpos($path, 'azrcrv-url-shortener') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.4.0
 *
 */
function azrcrv_urls_custom_image_url($url){
    if (strpos($url, 'azrcrv-url-shortener') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Get options including defaults.
 *
 * @since 1.4.0
 *
 */
function azrcrv_urls_get_option($option_name){
 
	$defaults = array(
						'lower_chars' => 1,
						'upper_chars' => 1,
						'numeric_chars' => 1,
						'length' => 6,
						'protocol' => 'https',
						'domain' => '',
						'priority' => 'default',
					);

	$options = get_option($option_name, $defaults);

	$options = wp_parse_args($options, $defaults);

	return $options;

}
/**
 * Add URL Shortener action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-urls').'"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'url-shortener').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_create_admin_menu(){
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("URL Shortener Settings", "url-shortener")
						,esc_html__("URL Shortener", "url-shortener")
						,'manage_options'
						,'azrcrv-urls'
						,'azrcrv_urls_display_options');
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_display_options(){
	if (!current_user_can('manage_options')){
		$error = new WP_Error('not_found', esc_html__('You do not have sufficient permissions to access this page.' , 'url-shortener'), array('response' => '200'));
		if(is_wp_error($error)){
			wp_die($error, '', $error->get_error_data());
		}
    }
	
	// Retrieve plugin site options from database
	$options = azrcrv_urls_get_option('azrcrv-urls');
	?>
	<div id="azrcrv-urls-general" class="wrap">
		<fieldset>
			<h1>
				<?php
					echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
					esc_html_e(get_admin_page_title());
				?>
			</h1>
			
			<?php
			if(isset($_GET['settings-updated'])){
				if (isset($_GET['url-error'])){
					?>
					<div id="message" class="error">
						<p><strong><?php esc_html_e('Settings have been saved except the domain which must be different to the site URL.', 'url-shortener') ?></strong></p>
					</div>
					<?php
				}else{
					?>
					<div class="notice notice-success is-dismissible">
						<p><strong><?php esc_html_e('Site settings have been saved.', 'url-shortener') ?></strong></p>
					</div>
					<?php
				}
			}
			?>
			
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="azrcrv_urls_save_options" />
				<input name="page_options" type="hidden" value="lower_chars,upper_chars,numeric_chars,length,domain,protocol" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field('azrcrv-urls', 'azrcrv-urls-nonce'); ?>
				<table class="form-table">
				
					<tr><th scope="row"><?php esc_html_e('Use Lowercase Characters', 'url-shortener'); ?></th><td>
						<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Use lowercase characters', 'url-shortener'); ?></span></legend>
						<label for="lower_chars"><input name="lower_chars" type="checkbox" id="lower_chars" value="1" <?php checked('1', $options['lower_chars']); ?> /><?php esc_html_e('Use lowercase characters', 'url-shortener'); ?></label>
						</fieldset>
					</td></tr>
					
					<tr><th scope="row"><?php esc_html_e('Use Uppercase Characters', 'url-shortener'); ?></th><td>
						<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Use uppercase characters', 'url-shortener'); ?></span></legend>
						<label for="upper_chars"><input name="upper_chars" type="checkbox" id="upper_chars" value="1" <?php checked('1', $options['upper_chars']); ?> /><?php esc_html_e('Use uppercase characters', 'url-shortener'); ?></label>
						</fieldset>
					</td></tr>
					
					<tr><th scope="row"><?php esc_html_e('Use Numeric Characters', 'url-shortener'); ?></th><td>
						<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Use numeric characters', 'url-shortener'); ?></span></legend>
						<label for="numeric_chars"><input name="numeric_chars" type="checkbox" id="numeric_chars" value="1" <?php checked('1', $options['numeric_chars']); ?> /><?php esc_html_e('Use numeric characters', 'url-shortener'); ?></label>
						</fieldset>
					</td></tr>
					
					<tr><th scope="row"><label for="length"><?php esc_html_e('Length', 'url-shortener'); ?></label></th><td>
						<input type="text" name="length" value="<?php echo esc_html(stripslashes($options['length'])); ?>" class="small-text" />
						<p class="description"><?php esc_html_e('Length of the shortened url', 'url-shortener'); ?></p>
					</td></tr>
					
					<tr><th scope="row"><label for="protocol"><?php esc_html_e('Protocol', 'url-shortener'); ?></label></th><td>
						<input type="text" name="protocol" value="<?php echo esc_html(stripslashes($options['protocol'])); ?>" class="small-text" />
						<p class="description"><?php esc_html_e('Protocol used for redirects (http or https)', 'url-shortener'); ?></p>
					</td></tr>
					
					<tr><th scope="row"><label for="domain"><?php esc_html_e('Domain', 'url-shortener'); ?></label></th><td>
						<input type="text" name="domain" value="<?php echo esc_html(stripslashes($options['domain'])); ?>" class="long-text" />
						<p class="description"><?php esc_html_e('Domain used for redirects (such as azrcrv.co.uk)', 'url-shortener'); ?></p>
					</td></tr>
					
					<tr><th scope="row"><label for="priority"><?php esc_html_e('Sidebar Priority', 'url-shortener'); ?></label></th>
						<td>
							<select name="priority" style="width: 200px">
								<option value='high' <?php echo selected(esc_html(stripslashes($options['priority'])),  "high"); ?>>High</option>
								<option value='core' <?php echo selected(esc_html(stripslashes($options['priority'])),  "core"); ?>>Core</option>
								<option value='default' <?php echo selected(esc_html(stripslashes($options['priority'])),  "default"); ?>>Default</option>
								<option value='low' <?php echo selected(esc_html(stripslashes($options['priority'])),  "low"); ?>>Low</option>
							</select>
					</td></tr>
					
					<tr><th scope="row"><label for="exampleusage"><?php esc_html_e('Example usage', 'url-shortener'); ?></label></th>
						<td>
						<p><?php esc_html_e('Example shortcode usage', 'url-shortener'); ?>:<br />
						<pre><code>&lt;?php echo do_shortcode('[short-url]'); ?&gt;</code></pre></p>

						<p><?php esc_html_e('Example function usage', 'url-shortener'); ?>:<br />
<pre><code>&lt;?php
if(function_exists('azrcrv_urls_get_custom_shortlink')){
&nbsp;&nbsp;&nbsp;&nbsp;printf (' <span class="short-link">%s</span>', '&lt;a href="'. azrcrv_urls_get_custom_shortlink() .'" title="Shortlink to '.the_title_attribute('echo=0').'" rel="bookmark"&gt;'.'Shortlink'.'</a>');
}
?&gt;</code></pre>
							</p>
					</td></tr>
					
				</table>
				<input type="submit" value="<?php esc_html_e('Submit', 'url-shortener'); ?>" class="button-primary"/>
			</form>
		</fieldset>
	</div>
	<?php
}

/**
 * Save settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_save_options(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'url-shortener'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-urls', 'azrcrv-urls-nonce')){
		// Retrieve original plugin options array
		$options = get_option('azrcrv-urls');
		
		$option_name = 'lower_chars';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'upper_chars';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'numeric_chars';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'length';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'protocol';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$urlerror = "";
		if (trailingslashit(get_site_url()) == $_POST['protocol']."://".$_POST['domain']."/"){
			$urlerror = "&url-error";
		}else{
			$option_name = 'domain';
			if (isset($_POST[$option_name])){
				$options[$option_name] = sanitize_text_field($_POST[$option_name]);
			}
		}
		
		$option_name = 'priority';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		// Store updated options array to database
		update_option('azrcrv-urls', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-urls&settings-updated'.$urlerror, admin_url('admin.php')));
		exit;
	}
}

/**
 * Add URL Shortener metabox to sidebar.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_add_sidebar_metabox(){
	
	$options = azrcrv_urls_get_option('azrcrv-urls');
	
	$post_types = get_post_types(array('public' => true));
	foreach($post_types as $post_type){
		add_meta_box('azrcrv-urls-box', esc_html__('Short URL', 'url-shortener'), 'azrcrv_urls_generate_sidebar_metabox', $post_type, 'side', $options['priority']);
	}
}

/**
 * Generate URLS Shortener sidebar metabox.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_generate_sidebar_metabox(){
	
	global $post;
	
	$options = azrcrv_urls_get_option('azrcrv-urls');

	?>
	
	<p class="azrcrv_urls_current_url">
		<?php wp_nonce_field(basename(__FILE__), 'azrcrv-urls-nonce'); ?>

		<?php esc_html_e("This post's short url is", 'url-shortener'); ?>:<br />
		<span class="azrcrv_urls_url_prefix">
			<?php
				$post_id = $post->ID;

				$short_url = azrcrv_urls_get_shortlink($post_id);
				echo "<a href='".azrcrv_urls_get_base_url().$short_url."'>".azrcrv_urls_get_base_url().$short_url."</a>";
			?>
			<input type="text" id="short-url" name="short-url" style="display: none;" value="<?php echo $short_url; ?>" ></p>
			
			<?php $custom_short_url = azrcrv_urls_get_shortlink_customshorturl($post_id); ?>
			<label for="custom-short-url">Custom Short URL</label>
			<input name='custom-short-url' type='text' value='<?php echo $custom_short_url; ?>' />
		</span>
	<?php
}

/**
 * Save Short URL.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_save_short_url($post_id){
	
	if(! isset($_POST[ 'azrcrv-urls-nonce' ]) || ! wp_verify_nonce($_POST[ 'azrcrv-urls-nonce' ], basename(__FILE__))){
		return $post_id;
	}
	
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;
	
	if(! current_user_can('edit_post', $post_id)){
		return $post_id;
	}
	
	$post = get_post($post_id);
	
	update_post_meta($post_id, '_ShortURL', esc_attr($_POST['short-url']));
	if (strlen($_POST['custom-short-url']) > 0){
		update_post_meta($post_id, '_CustomShortURL', esc_attr($_POST['custom-short-url']));
	}
	
	return esc_attr($_POST[ 'short-url' ]);
	
}

/**
 * Add links to site header.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_add_links_to_site_header(){
	
	$post_id = get_the_ID();
	
	$short_url = azrcrv_urls_get_shortlink($post_id);
	
	if($short_url != ''){
		$short_url_permalink = azrcrv_urls_get_short_url_permalink($post_id);
		
		echo "<link rel='shorturl' type='text/html' href='".$short_url_permalink."'>\n";
		echo "<link rel='shortlink' type='text/html' href='".$short_url_permalink."'>\n";
	}
	
}

/**
 * Get short url permalink.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_get_short_url_permalink($post_id){
	
	$short_url = azrcrv_urls_get_shortlink($post_id);
	
	if($short_url == ''){
		return '';
	}else{
		return azrcrv_urls_get_base_url().$short_url;
	}
	
}

/**
 * Get base url.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_get_base_url(){
	
	$options = azrcrv_urls_get_option('azrcrv-urls');
	
	return trailingslashit($options['protocol'].'://'.$options['domain']);
	
}

/**
 * Get short url or generate new one.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_get_shortlink($post_id){
	
	global $wpdb;
	
	if($post_id == ''){ return ''; }
	
	$custom_short_url = azrcrv_urls_get_shortlink_customshorturl($post_id);
	
	if($custom_short_url == ''){
		$sql =  "select meta_value FROM $wpdb->postmeta where meta_key = '_ShortURL' and post_id = '%s'";
		
		$short_url = $wpdb->get_var($wpdb->prepare($sql, $post_id));
	}else{
		$short_url = $custom_short_url;
	}
	
	if($short_url != ''){
		return $short_url;
	}else {
		$short_url = azrcrv_urls_make_save_short_url($post_id);
		
		return $short_url;
	}
}

/**
 * Get custom short url.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_get_shortlink_customshorturl($post_id){
	
	global $wpdb;
	
	if($post_id == ''){ return ''; }
	
	$sql =  "select meta_value FROM $wpdb->postmeta where meta_key = '_CustomShortURL' and post_id = '%s'";
	
	$custom_short_url = $wpdb->get_var($wpdb->prepare($sql, $post_id));
	
	return $custom_short_url;
}

/**
 * Generate a short URL and save to the post meta.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_make_save_short_url($post_id){
	if($post_id != ''){
		$short_url = azrcrv_urls_generate_shortlink('post');

		return ! update_post_meta($post_id, '_ShortURL', $short_url) ? false : $short_url;
	}
	return false;
}

/**
 * Generate short URL.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_generate_shortlink($short_type){
	
	global $wpdb;
	
	$options = azrcrv_urls_get_option('azrcrv-urls');
	
	$characters = '';
	if ($options['numeric_chars'] = 1){
		$characters .= "1234567890";
	}
	if ($options['lower_chars'] = 1){
		$characters .= "abcdefghijklmnopqrstuvwxyz";
	}
	if ($options['upper_chars'] = 1){
		$characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	}
	
	$invalid = 1;
	while($invalid >= 1){
		$short_url = "";
		
		for ($int = 0; $int < $options['length']; $int++){
			$short_url .= substr($characters,mt_rand(1,strlen($characters))-1,1);
		}
		
		if ($short_type = 'post' or $short_type = 'page'){
			$sql =  "select count(post_id) FROM $wpdb->postmeta where meta_key = '_ShortURL' and meta_value = '%s'";
		}else{
			$sql = $wpdb->prefix."shortened";
			$sql = "select count(id) from $sql where Short_URL_VC = '%s'";
		}
		$invalid = $wpdb->get_var($wpdb->prepare($sql, $short_url));
	}
	return $short_url;
}

/**
 * Create table on installation.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_install(){
    global $wpdb;
    $table_name = $wpdb->base_prefix.'azrcrv_urls_clicks';

    if($wpdb->get_var("show tables like '{$table_name}'") != $table_name){
		
		$charset_collate = $wpdb->get_charset_collate();
		
        $sql = "CREATE TABLE ".$table_name." (
					ID INT NOT NULL AUTO_INCREMENT,
					blog_id INT NOT NULL,
					IP_VC VARCHAR(15) NOT NULL,
					Querystring_VC VARCHAR(400) NOT NULL,
					Short_URL_VC VARCHAR(45) NOT NULL,
					Referrer_VC VARCHAR(300) NOT NULL,
					Created_DT DATETIME NOT NULL,
					Created_User_ID VARCHAR(45) NOT NULL,
				PRIMARY KEY (ID),
				UNIQUE INDEX ID_UNIQUE (ID ASC)
				) $charset_collate;";

        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

/**
 * Redirect incoming url.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_redirect_incoming(){
	
	$short_url = rtrim(ltrim(strtok(strtok($_SERVER[ 'REQUEST_URI' ], '?'), '#'), '/'), '/');
	
	$current_url = 'http'.(((! empty($_SERVER[ 'HTTPS' ]) && $_SERVER[ 'HTTPS' ] !== 'off') || $_SERVER[ 'SERVER_PORT' ] == 443) ? 's' : '').'://'.$_SERVER[ 'HTTP_HOST' ].'/'. $short_url;
	
	$post_id = azrcrv_urls_identify_post_from_shortlink($short_url);
	if ($post_id){
		if (get_post_type($post_id) == 'short-url'){
			$permalink = get_the_title($post_id);
			if (substr($permalink,0,4) != 'http'){
				$permalink = 'http://'.$permalink;
			}
		}else{
			$permalink = get_permalink($post_id);
		}
		
		if($permalink != $current_url){
			wp_redirect($permalink, 301);
			exit;
		}
	}else{
		if (trailingslashit('http://'.$_SERVER['SERVER_NAME']) == trailingslashit(azrcrv_urls_get_base_url())){
			wp_redirect(home_url('/'), 301);
			exit;
		}
	}
	
}

/**
 * Identify post from short url.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_identify_post_from_shortlink($short_url){
	global $wpdb;
	
	$sql = $wpdb->prepare("SELECT pm.post_id FROM ".$wpdb->postmeta." AS pm INNER JOIN $wpdb->posts AS p ON p.ID = pm.post_id WHERE meta_key IN ('_ShortURL', '_CustomShortURL') AND meta_value = '%s' AND post_name <> '%s'", $short_url, $short_url);
	
	$post_id = $wpdb->get_var($sql);
	
	return $post_id;
}

/**
 * Get custom shortlink instead of normal shortlink.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_admin_get_custom_shortlink($link, $id, $context){
		return azrcrv_urls_get_custom_shortlink($id);
}

/**
 * Get custom shortlink instead of normal shortlink.
 *
 * @since 1.0.0
 *
 */
function azc_urls_get_custom_shortlink($post_id = null){
		return azrcrv_urls_get_custom_shortlink($post_id);
}

/**
 * Return custom shortlink URL matching full permalink.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_get_custom_shortlink($post_id = null){
	if (isset($_GET['preview']) AND $_GET['preview'] == 'true'){ return ''; }
	
	global $post;
	if (!isset($post_id)){
		$post_id = $post->ID;
	}
	
	$short_url = azrcrv_urls_get_shortlink($post_id);

	if ($short_url == ''){
		return ''; 
	}else{
		return azrcrv_urls_get_base_url().$short_url;
	}
}

/**
 * Create custom Short URL post type.
 *
 * @since 1.0.0
 *
 */
function azrcrv_urls_create_post_type(){
	register_post_type('short-url',
		array(
				'labels' => array(
				'name' => esc_html__('Short URL', 'url-shortener'),
				'singular_name' => esc_html__('Short URL', 'url-shortener'),
				'add_new' => esc_html__('Add New', 'url-shortener'),
				'add_new_item' => esc_html__('Add New Short URL', 'url-shortener'),
				'edit' => esc_html__('Edit', 'url-shortener'),
				'edit_item' => esc_html__('Edit Short URL', 'url-shortener'),
				'new_item' => esc_html__('New Short URL', 'url-shortener'),
				'view' => esc_html__('View', 'url-shortener'),
				'view_item' => esc_html__('View Short URL', 'url-shortener'),
				'search_items' => esc_html__('Search Short URLs', 'url-shortener'),
				'not_found' => esc_html__('No Short URLs found', 'url-shortener'),
				'not_found_in_trash' => esc_html__('No Short URLs found in Trash', 'url-shortener'),
				'parent' => esc_html__('Parent Short URL', 'url-shortener')
			),
		'public' => true,
		'menu_position' => 20,
		'supports' => array('title', 'comments', 'trackbacks', 'revisions', 'editor'),
		'taxonomies' => array(''),
		'menu_icon' => 'dashicons-admin-links',
		)
	);
}