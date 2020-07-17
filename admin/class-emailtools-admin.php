<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
class Emailtools_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $emailtools    The ID of this plugin.
	 */
	private $emailtools;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $emailtools       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $emailtools, $version ) {

		$this->emailtools = $emailtools;
		$this->version = $version;
		$this->setings_page = 'emailtools';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() { }

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() { }

	public function plugin_init() {
		add_action('admin_menu', [$this,'emailtools_menu']);
		add_action( 'admin_init', [$this, 'emailtools_settings'] );
		add_action('update_option_emailtools' ,[$this,'update_option'], 10, 2);
	}

	//Clear log file
	public function update_option( $option_name, $option_value ) {
		if(isset($option_value['is_logging'])) return;

		$logFile = ABSPATH . 'emtlog.txt';
		file_put_contents($logFile, '');
  	}

	public function wp() { }

	public function admin_init() { 
		add_action( 'admin_notices', [$this, 'woocommerce_notice']);
		add_action( 'admin_notices', [$this, 'emailtools_notice']);
	}

	public function emailtools_notice() {
		if( get_transient( 'emt_terms_and_conditions' )){
			$options = get_option('emailtools');
			$settings_url = admin_url( 'options-general.php?page=emailtools');
			$is_terms =isset($options['is_terms_and_conditions']) ? $options['is_terms_and_conditions'] : ''; 
			
			if(!$is_terms){ ?>
				<div class="notice notice-info is-dismissible">
					<p><strong>
						<?php 
							$msg = sprintf( 
								__( 'You should accept the terms and conditions on a emailtools plugin <a href="%s">settings page</a>', 'emailtools-for-wordpress' ), 
								$settings_url
							);
							echo $msg;
						?>
					</strong>.</p>
				</div>
			<?php }
			delete_transient( 'emt_terms_and_conditions' );
		}
	} 

	public function woocommerce_notice() {
		if( !class_exists( 'WooCommerce' ) && get_transient( 'required_wc' ) ){
			?>
			<div class="notice notice-error">
				<p><strong>
					<?php _e('Emailtools required WooCommerce plugin!', 'emailtools-for-wordpress')?>
				</strong>.</p>
			</div>
			<?php
			delete_transient( 'emt_required_wc' );
		}
	} 
		
	public function emailtools_menu() {
		add_options_page( 
			__('Emailtools settings', 'emailtools-for-wordpress'),
			__('Emailtools settings', 'emailtools-for-wordpress'), 
			'manage_options', 
			$this->setings_page, 
		[$this, 'emailtools_menu_page']); 
	
	}

	public function emailtools_menu_page(){
		?>
			<div class="wrap">
			
			<h2><?php _e('Retro Add Emailtools API KEY', 'emailtools-for-wordpress'); ?></h2>
			<p><?php _e('This plugin will attempt to add your EMT API KEY.', 'emailtools-for-wordpress'); ?></p>
				<form method="post" enctype="multipart/form-data" action="options.php">
					<?php 
					settings_fields($this->setings_page); 
					do_settings_sections($this->setings_page);
					?>
					<p class="submit">  
						<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'emailtools-for-wordpress') ?>" />  
					</p>
				</form>
			</div>
		<?php
	}


	public function emailtools_settings() {
		
		register_setting( $this->setings_page, $this->setings_page, [$this,'validate_settings'] ); 
		add_settings_section(  $this->setings_page.'_section1', '', '', $this->setings_page );
	 
		$params = array(
			'type'      => 'text', 
			'id'        => 'emt_api_key',
			'placeholder'=> __('Enter your API KEY here', 'emailtools-for-wordpress'), 
			'desc'      => __('Your API KEY', 'emailtools-for-wordpress')
		);
		add_settings_field( 
			'emt_api_key', 
			__('API Key', 'emailtools-for-wordpress'),  
			[$this,'display_field'], 
			$this->setings_page, 
			$this->setings_page.'_section1', 
			$params );


		$params = array(
			'type'      => 'checkbox', 
			'id'        => 'is_logging',
			'desc'      => __('Keep a log file', 'emailtools-for-wordpress'), 
		);
		add_settings_field( 
			'is_logging', 
			__('Logging', 'emailtools-for-wordpress'),  
			[$this,'display_field'], 
			$this->setings_page, 
			$this->setings_page.'_section1', 
			$params );

		$params = array(
			'type'      => 'checkbox', 
			'id'        => 'is_terms_and_conditions',
			'desc'      =>'',
		
		);
		add_settings_field( 
			'is_terms_and_conditions', 
			__('Terms and conditions', 'emailtools-for-wordpress'),  
			[$this,'display_field'], 
			$this->setings_page, 
			$this->setings_page.'_section1', 
			$params );


		
	}

	function validate_settings($input) {
		$valid_input = [];
		foreach($input as $k => $v) {
			switch ($k) {
				case 'emt_api_key':
					$valid_input[$k] = sanitize_text_field($v);
					$valid_input[$k] = intval($valid_input[$k]) ? intval($valid_input[$k]) : '';
					break;
				case 'is_terms_and_conditions':
				case 'is_logging':
					$valid_input[$k] = sanitize_text_field($v);
					break;
				
			}
		}
		return $valid_input;
	}

	private function get_terms_and_conditions() {
		$msg = '<div class="terms-and-conditions" style="max-width: 600px; margin-left:23px; margin-top:10px">' . 
		__('Terms and conditions By installing this plugin, you confirm the compliance of 
			your site with the requirements for the collection and processing of personal 
			data of site users, the laws of the countries in which you carry out 
			activities and interact with users.
			For example, in the Russian Federation this is 152 federal law, in 
			the European Union - General Data Protection Regulation (GDPR).','emailtools-for-wordpress');
		$msg .= ' '. sprintf( 
			__( '<a target="_blank" href="%s">details</a>', 'emailtools-for-wordpress' ), 
				TERMS_URL
		);
		$msg .='</div>';
		return $msg ;
	}


	
	

	function display_field($args) {
		
		$type = isset($args['type']) ? $args['type'] : 'text';
		$id = isset($args['id']) ? $args['id'] : '';
		$placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
		$desc = isset($args['desc']) ? $args['desc'] : '';

		$settings = get_option( $this->setings_page );
		$setting = isset($settings[$id]) ? esc_attr( sanitize_text_field($settings[$id]))  :  '';
		
		switch ( $type ) {  
			case 'text':  
				echo "<input placeholder='". esc_attr($placeholder) . "' class='regular-text' type='text' id='".esc_attr($id)."' name='" . esc_attr($this->setings_page) . "[".esc_attr($id)."]' value='".esc_attr($setting)."' />";  
				echo ($desc != '') ? "<br /><span class='description'>".esc_html($desc) ."</span>" : "";
			break;
			case 'textarea':  
				echo "<textarea class='code large-text' cols='30' rows='3' type='text' id='".esc_attr($id)."' name='" . esc_attr($this->setings_page) . "[".esc_attr($id)."]'>".esc_html($setting)."</textarea>";  
				echo ($desc != '') ? "<br /><span class='description'>".esc_html($desc)."</span>" : "";  
			break;
			case 'checkbox':
				$checked = ($setting == 'on') ? " checked='checked'" :  '';  
				echo "<label><input type='checkbox' id='".esc_attr($id)."' name='" . esc_attr($this->setings_page) . "[".esc_attr($id)."]' $checked /> ";  
				if($id == 'is_terms_and_conditions') {
					echo __('You must accept the terms and conditions', 'emailtools-for-wordpress');
				} else {
					echo ($desc != '') ? esc_html($desc) : "";
				}
			
				echo "</label>";  
				if($id == 'is_terms_and_conditions') {
					echo $this->get_terms_and_conditions();
				}
			break;
			case 'select':
				echo "<select id='".esc_attr($id)."' name='" . esc_attr($this->setings_page) . "[".esc_attr($id)."]'>";
				foreach($vals as $v => $l){
					$selected = ($setting == $v) ? "selected='selected'" : '';  
					echo "<option value='".esc_attr($v)."' $selected>".esc_html($l)."</option>";
				}
				echo ($desc != '') ? esc_html($desc) : "";
				echo "</select>";  
			break;
			case 'radio':
				echo "<fieldset>";
				foreach($vals as $v=>$l){
					$checked = ($setting == $v) ? "checked='checked'" : '';  
					echo "<label><input type='radio' name='" . esc_attr($this->setings_page) . "[".esc_attr($id)."]' value='".esc_attr($v)."' $checked />".esc_html($l)."</label><br />";
				}
				echo "</fieldset>";  
			break; 
		}
	}
}

