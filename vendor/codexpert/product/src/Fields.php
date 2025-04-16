<?php
namespace codexpert\product;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage Fields
 * @author codexpert <hello@codexpert.io>
 */
abstract class Fields extends Base {

	function hooks() {
		if( did_action( 'cx-plugin_loaded' ) ) return;
		do_action( 'cx-plugin_loaded' );

		$this->action( 'admin_head', 'callback_head', 99 );
	}

	public function enqueue_scripts() {
        wp_enqueue_media();

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        wp_register_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js' );
        wp_register_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css' );

        wp_register_script( 'chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js' );
        wp_register_style( 'chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css' );

        if( $this->has_select2() ) {
        	wp_enqueue_style( 'select2' );
        	wp_enqueue_script( 'select2' );
        }

        if( $this->has_chosen() ) {
        	wp_enqueue_style( 'chosen' );
        	wp_enqueue_script( 'chosen' );
        }

        wp_enqueue_style( 'codexpert-product-fields', plugins_url( 'assets/css/fields.css', __FILE__ ), [], '' );
        wp_enqueue_script( 'codexpert-product-fields', plugins_url( 'assets/js/fields.js', __FILE__ ), [ 'jquery','wp-i18n' ], '', true );
    }

	public function callback_head() {
		?>
		<script>
			jQuery(function($){<?php
				if( is_array( $this->sections ) && count( $this->sections ) > 0 ) {
					foreach ( $this->sections as $section_id => $section ) {
						if( isset( $section['fields'] ) && is_array( $section['fields'] ) && count( $section['fields'] ) > 0 ) {
							foreach ( $section['fields'] as $field ) {
								if( isset( $field['condition'] ) && is_array( $field['condition'] ) ) {
									$key = $field['condition']['key'] ? $field['condition']['key'] : null;
									$value = isset( $field['condition']['value'] ) ? $field['condition']['value'] : 'on';
									$compare = isset( $field['condition']['compare'] ) ? $field['condition']['compare'] : '==';

									if( 'checked' != $compare ) {
										echo "$('#{$section['id']}-{$key}').change(function(e){if( $('#{$section['id']}-{$key}').val() {$compare} '{$value}' ) { $('#cxrow-{$section['id']}-{$field['id']}').slideDown();}else { $('#cxrow-{$section['id']}-{$field['id']}').slideUp();}}).change();";
									}
									else {
										echo "$('#{$section['id']}-{$key}').change(function(e){if( $('#{$section['id']}-{$key}').is(':checked') ) { $('#cxrow-{$section['id']}-{$field['id']}').slideDown();}else { $('#cxrow-{$section['id']}-{$field['id']}').slideUp();}}).change();";
									}
								}
							}
						}
					}
				}
				?>
			})
		</script>
		<?php
	}

	public function callback_fields( $post = null, $metabox = [] ) {

		$config = $this->config;

		$scope = $metabox == [] ? 'option' : 'post';
		
		echo '<div class="wrap">';

		if( $scope == 'option' ) :
		$icon = $this->generate_icon( $config['icon'] );
		echo "<h2 class='cx-heading'>{$icon} {$config['title']}</h2>";
		endif;

		do_action( 'cx-settings-heading', $config );

		if( !isset( $this->sections ) || count( $this->sections ) <= 0 ) return;

		$tab_position = isset( $config['tab_position'] ) ? $config['tab_position'] : 'left';
		echo "<div class='cx-wrapper cx-tab-{$tab_position}'>";

		$sections = $this->sections;

		// nav tabs
		$display = count( $sections ) > 1 ? 'block' : 'none';
		echo '
		<div class="cx-navs-wrapper" style="display: ' . $display . '">
			<ul class="cx-nav-tabs">';
			foreach ( $sections as $section ) {
				$icon = $this->generate_icon( $section['icon'] );
				$color = isset( $section['color'] ) ? $section['color'] : '#23282d';
				echo "<li class='cx-nav-tab' data-color='{$color}'><a href='#{$section['id']}'>{$icon}<span id='cx-nav-label-{$section['id']}' class='cx-nav-label'> {$section['label']}</span></a></li>";
			}
			echo '</ul>
		</div><!--div class="cx-navs-wrapper"-->';

		// form areas
		echo '<div class="cx-sections-wrapper">';
		foreach ( $sections as $section ) {
			$icon = $this->generate_icon( $section['icon'] );
			$color = isset( $section['color'] ) ? $section['color'] : '#23282d';
			$submit_button = isset( $section['submit_button'] ) ? $section['submit_button'] : __( 'Save Settings' );
			$reset_button = isset( $section['reset_button'] ) ? $section['reset_button'] : __( 'Reset Default' );
			$_nonce = wp_create_nonce();
			$hideWhenEmpty = isset($section['hideWhenEmpty']) ? $section['hideWhenEmpty'] : false;
			$hide = isset($section['hide']) ? $section['hide'] : false;

			if($hide) continue;

			$fields = apply_filters( 'cx-settings-fields', $section['fields'], $section );
			$show_form = isset( $section['hide_form'] ) && $section['hide_form'] ? false : true;
			$show_form = apply_filters( 'cx-settigns-show-form', $show_form, $section );

			if( $hideWhenEmpty && count( $fields ) <= 0 ) continue;

			echo "<div id='{$section['id']}' class='cx-section' style='display:none'>";

			do_action( 'cx-settings-before-title', $section );
			
			echo "<h3 class='cx-subheading'><span style='color: {$color}'>{$icon}</span> {$section['label']}</h3>";
			
			if( isset( $section['desc'] ) && $section['desc'] != '' ) {
				echo "<p class='cx-desc'>{$section['desc']}</p>";
			}

			do_action( 'cx-settings-before-form', $section );

			if( $scope == 'option' && $show_form ):
				
			$page_load = isset( $section['page_load'] ) && $section['page_load'] ? 1 : 0;

			echo "<form id='cx-form-{$section['id']}' class='cx-form'>
					<div id='cx-message-{$section['id']}' class='cx-message'></div>
					<input type='hidden' name='action' value='cx-settings' />
					<input type='hidden' name='option_name' value='{$section['id']}' />
					<input type='hidden' name='page_load' value='{$page_load}' />
			";
			wp_nonce_field();
			endif; // if( $show_form ) :

			do_action( 'cx-settings-before-fields', $section );

			if( isset( $section['template'] ) && $section['template'] != '' ) echo $section['template'];

			if( count( $fields ) > 0 ) :
			foreach ( $fields as $field ) {
				if( isset( $field['type'] ) && $field['type'] == 'divider' ) {
					$style = isset( $field['style'] ) ? $field['style'] : '';
					echo "<div class='cxrow cx-divider' id='cxrow-{$section['id']}-{$field['id']}' style='{$style}'><span>{$field['label']}</span></div>";
				}
				else {
					$field_display = isset( $field['condition'] ) && is_array( $field['condition'] ) ? 'none' : '';
					$field_display = apply_filters( 'cx-settings-field-display', $field_display, $field, $section );
					$style_display = $field_display == 'none' ? 'style="display: none"' : '';
					echo "<div class='cxrow' id='cxrow-{$section['id']}-{$field['id']}' {$style_display}>";
					if( isset($field['hidelabel']) ):
						echo '<div class="cx-wrap">';

						do_action( 'cx-settings-before-field', $field, $section );

						if( isset( $field['template'] ) && $field['template'] != '' ) echo $field['template'];

						if( isset( $field['type'] ) && $field['type'] != '' ) echo $this->populate( $field, $section, $scope );

						do_action( 'cx-settings-after-field', $field, $section );

						if( isset( $field['desc'] ) && $field['desc'] != '' ) {
							echo "<p class='cx-desc'>{$field['desc']}</p>";
						}

						do_action( 'cx-settings-after-description', $field, $section );

						echo '</div></div>';
					else:
						echo "<div class='cx-label-wrap'>";

						do_action( 'cx-settings-before-label', $field, $section );

						echo "<label for='{$section['id']}-{$field['id']}'>{$field['label']}</label>";

						do_action( 'cx-settings-after-label', $field, $section );

						echo "</div>";
						// end label
						echo "<div class='cx-field-wrap'>";

						do_action( 'cx-settings-before-field', $field, $section );

						if( isset( $field['template'] ) && $field['template'] != '' ) echo $field['template'];

						if( isset( $field['type'] ) && $field['type'] != '' ) echo $this->populate( $field, $section, $scope );

						do_action( 'cx-settings-after-field', $field, $section );

						if( isset( $field['desc'] ) && $field['desc'] != '' ) {
							echo "<p class='cx-desc'>{$field['desc']}</p>";
						}

						do_action( 'cx-settings-after-description', $field, $section );

						echo "</div></div>";
					endif;
				}
			}
			endif; // if( count( $fields ) > 0 ) :

			do_action( 'cx-settings-after-fields', $section );

			if( $scope == 'option' && $show_form ) :
			$_is_sticky = isset( $section['sticky'] ) && !$section['sticky'] ? '' : ' cx-sticky-controls';
			echo "<div class='cx-controls-wrapper{$_is_sticky}'>";

			if( $reset_button ) echo "<button type='button' class='button cx-reset-button' data-option_name='{$section['id']}' data-_nonce='{$_nonce}'>{$reset_button}</button>&nbsp;";
			if( $submit_button ) echo "<input type='submit' class='button button-primary cx-submit' value='{$submit_button}' />";
			echo '</div class="cx-controls-wrapper">
				</form>';
			endif; // if( $show_form ) :

			do_action( 'cx-settings-after-form', $section );

			echo "</div><!--div id='{$section['id']}'-->";
		}
		echo '</div><!--div class="cx-sections-wrapper"-->
			 <div class="cx-sidebar-wrapper">';

		do_action( 'cx-settings-sidebar', $config );

		echo '</div><!--div class="cx-sidebar-wrapper"-->
			</div><!--div class="cx-wrapper"-->
		</div><!--div class="wrap"-->';

		if( isset( $config['css'] ) && $config['css'] != '' ) {
			echo "<style>{$config['css']}</style>";
		}
	}
	
	public function populate( $field, $section, $scope = 'option' ) {
		if ( in_array( $field['type'], [ 'text', 'number', 'email', 'url', 'password', 'color', 'range', 'date', 'time' ] ) ) {
			$callback_fn = 'field_text';
		}
		else {
			$callback_fn = "field_{$field['type']}";
		}

		return $this->$callback_fn( $field, $section, $scope );
	}

	public function get_value( $field, $section, $default = '', $scope = 'option' ) {
		if( $scope == 'option' ) {
			$section_values = get_option( $section['id'] );
		}
		else {
			global $post;
			$section_values = get_post_meta( $post->ID, $section['id'], true );
		}

		if( isset( $section_values[ $field['id'] ] ) ) {
			return $section_values[ $field['id'] ];
		}
		
		return $default;
	}

	public function field_admin_html($field, $section, $scope){
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";
		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$data 			= $field['html'];
		if(empty($data)){
			$data = '<p>No Content</p>';
		}
		$html = "<div id='{$id}'>{$data}</div>";
		return $html;
	}

	public function field_admin_shortcode($field, $section, $scope){
		$label 				= $field['label'];
		$shortcode			= $field['shortcode'];
		$copy				= $field['copy'];
		if(empty($shortcode)){
			if(empty($field['message'])){
				$shortcode = '[No Shortcode]';
			}
			else{
				$shortcode = $field['message'];
			}
		}
		$id 			= "{$section['id']}-{$field['id']}";
		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$copyHtml = '';
		if($copy){
			$copyHtml = "<button class=\"button button-info button-small\" onclick=\"navigator.clipboard.writeText(document.getElementById('{$id}').innerHTML)\">Copy</button>";
		}
		$html = "<div class='{$class}'><p id='{$id}'>{$shortcode}</p>".$copyHtml."</div>";
		return $html;
	}

	public function field_admin_button($field, $section, $scope){
		$label 				= $field['label'];
		$button			= $field['button'];
		$action 			= $field['action'] ? $field['action'] : 'admin_button';
		if(empty($button)){
			$button = 'No Button';
		}
		$id 			= "{$section['id']}-{$field['id']}";
		$class 			= "cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$_nonce = wp_create_nonce('cx-button');
		$html = "<div class='{$class}' id='{$id}'><button data-nonce='{$_nonce}' class=\"button admin_button button-info button-small\">{$button}</button></div>";

		/**
		 	$_nonce = wp_create_nonce('cx-createpage');
			$html .= "<button id=\"selectbutton-{$name}\" data-nonce='{$_nonce}' data-select=\"#select-{$name}\" data-post_type='".$default_page['post_type']."' data-post_status='".$default_page['post_status']."' data-post_content='".$default_page['post_content']."' data-post_title='".$default_page['post_title']."' class=\"button button-primary selectbutton cx-createpage\" type=\"button\">".__("Create Page")."</button>";
			$html .= "</div>";
		 */

		return $html;
	}

	public function field_admin_message($field, $section, $scope){
		$label 				= $field['label'];
		$message			= $field['message'];
		if(empty($message)){
			$message = 'No Message';
		}
		$id 			= "{$section['id']}-{$field['id']}";
		$class 			= "cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$html = "<div class='{$class}' id='{$id}'><p>{$message}</p></div>";
		return $html;
	}

	public function field_admin_email($field, $section, $scope){
		$label 			= $field['label'];
		$email			= $field['email'];
		if(empty($email)){
			$email = 'support@bizinkonline.com';
		}
		$id 			= "{$section['id']}-{$field['id']}";
		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$html = "<div class='{$class}' id='{$id}'><a href='mailto:{$email}'>{$email}</a></div>";
		return $html;
	}

	public function field_text( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->esc_str( $this->get_value( $field, $section, $default, $scope ) );

		$type 			= $field['type'];
		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$min 			= isset( $field['min'] ) && $field['min'] ? " min='{$field['min']}'" : "";
		$max 			= isset( $field['max'] ) && $field['max'] ? " max='{$field['max']}'" : "";
		$step 			= isset( $field['step'] ) && $field['step'] ? " step='{$field['step']}'" : "";

		if( $type == 'color' ) {
			$class .= ' cx-color-picker';
			$type = 'text';
		}

		$html = "<input type='{$type}' class='{$class}' id='{$id}' name='{$name}' value='{$value}' placeholder='{$placeholder}' {$min} {$max} {$step} {$required} {$readonly} {$disabled}/>";

		return $html;
	}

	public function field_textarea( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->esc_str( $this->get_value( $field, $section, $default, $scope ) );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$rows 			= isset( $field['rows'] ) ? $field['rows'] : 5;
		$cols 			= isset( $field['cols'] ) ? $field['cols'] : 3;

		$html  = "<textarea class='{$class}' id='{$id}' name='{$name}' cols='{$cols}' rows='{$rows}' placeholder='{$placeholder}' {$required} {$readonly} {$disabled}>{$value}</textarea>";

		return $html;
	}

	public function field_switch( $field, $section, $scope ){
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'] ? $field['label'] : '';
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$labelClass     = "cx-field-label-{$field['type']}";
		$labelClass     .= isset( $field['labelClass'] ) ? $field['labelClass'] : '';

		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";

		$html =  "<label class='{$labelClass}' for='{$id}'>";
		$html .= "<input type='checkbox' class='{$class}' id='{$id}' name='{$name}' value='on' {$required} {$readonly} {$disabled} " . checked( $value, 'on', false ) . "/>";
		$html .= "<span class='slider round'></span></label>";

		return $html;
	}

	public function field_radio( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$html = '';
		foreach ( $options as $key => $title ) {
			$html .= "<input type='radio' name='{$name}' id='{$id}-{$key}' class='{$class}' value='{$key}' {$required} {$disabled} " . checked( $value, $key, false ) . "/>";
			$html .= "<label for='{$id}-{$key}'>{$title}</label><br />";
		}
		
		return $html;
	}

	public function field_checkbox( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$multiple 		= isset( $field['multiple'] ) && $field['multiple'];
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$html  = '';
		if( $multiple ) {
			foreach ( $options as $key => $title ) {
				$html .= "
				<p>
					<input type='checkbox' name='{$name}[]' id='{$id}-{$key}' class='{$class}' value='{$key}' {$required} {$disabled} " . ( in_array( $key, (array)$value ) ? 'checked' : '' ) . "/>
					<label for='{$id}-{$key}'>{$title}</label>
				</p>";
			}
		}
		else {
			$html .= "<input type='checkbox' name='{$name}' id='{$id}' class='{$class}' value='on' {$required} {$disabled} " . checked( $value, 'on', false ) . "/>";
		}

		return $html;
	}

	public function field_select( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$class 			.= isset( $field['select2'] ) && $field['select2'] ? ' cx-select2' : '';
		$class 			.= isset( $field['chosen'] ) && $field['chosen'] ? ' cx-chosen' : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$multiple 		= isset( $field['multiple'] ) && $field['multiple'] ? 'multiple' : false;
		$options 		= isset( $field['options'] ) ? $field['options'] : [];

		$html  = '';
		if( $multiple ) {
			$html .= "<select name='{$name}[]' id='{$id}' class='{$class}' multiple {$required} {$disabled} data-placeholder='{$placeholder}'>";
			foreach ( $options as $key => $title ) {
				$html .= "<option value='{$key}' " . ( in_array( $key, (array)$value ) ? 'selected' : '' ) . ">{$title}</option>";
			}
			$html .= '</select>';
		}
		else {
			$html .= "<select name='{$name}' id='{$id}' class='{$class}' {$required} {$disabled} data-placeholder='{$placeholder}'>";
			foreach ( $options as $key => $title ) {
				$html .= "<option value='{$key}' " . selected( $value, $key, false ) . ">{$title}</option>";
			}
			$html .= '</select>';
		}

		return $html;
	}

	public function field_content_manager($field, $section, $scope){
		ob_start();
		$fields = $field['fields'];
		$html = '';
		$color = isset( $section['color'] ) ? $section['color'] : '#23282d';
		$hidden_posts = get_option('bizpress_hidden_posts',[]);
		$edited_posts = get_option('bizpress_edited_posts',[]);
		$nonce = wp_create_nonce('cx-content_manager');
		if(!empty($fields)){
			?>
			<div class="cx-content-manager">
				<div class="cx-content-manager-nav">
					<nav class="cx-content-manager-nav">
						<?php
						$i = 0;
						foreach($fields as $field){
							$selected = "";
							$style = "color:";
							if($i == 0){
								$selected = "selected";
								$style = "background:";
							}
							echo '<a class="cx-content-manager-nav-item '.$selected.'" data-color="'.$color.'" data-id="'.$field['id'].'" href="#" style="'.$style.$color.'">'.$field['label'].'</a>';
							$i++;
						}
						?>
					</nav>
				</div>
				<div class="cx-content-manager-content">
					<?php
					$i = 0;
					foreach($fields as $field){
						if($i == 0){$selected = "selected";}
						?>
						<div class="cx-content-manager-content-item <?php echo $selected; ?> cx-content-manager-content-<?php echo $field['id']; ?>">
							<h2><?php echo $field['label']; ?></h2>
							<div class="content-wrap">
						<?php
						if(!empty($field['posts'])):
							foreach($field['posts'] as $post):
								if($post->hidden){continue;} // Hidden on server
								// Hidden on Client
								$isHidden = false;
								if(in_array($post->ID,$hidden_posts)){
									$isHidden = true;
								}
								// Edited
								$editedPost = null;
								$isEdited = false;
								if(in_array($post->ID,$edited_posts)){
									$isEdited = $edited_posts[$post->ID];
									$editedPost = get_post($isEdited);
								}
								// Display
								$cssExtra = "";
								if($isHidden){
									$cssExtra = " content-post-hidden";
								}
								else if($isEdited){
									$cssExtra = " content-post-edited";
								}
								?>
								<div class="post content-post <?php echo $cssExtra; ?>" id="content-post-<?php echo $post->ID; ?>" data-id="<?php echo $post->ID; ?>" data-hidden="<?php echo ($isHidden ? 1:0); ?>" data-edit="<?php echo ($isEdited ? 1:0); ?>">
									<div class="content-post-banner hidden_banner">
										<p><?php _e("Hidden","bizink-client"); ?></p>
									</div>
									<div class="content-post-banner edit_banner">
										<p><?php _e("Eddited","bizink-client"); ?></p>
									</div>
									<img loading="lazy" height="161" src="<?php echo $post->thumbnail; ?>" alt="<?php echo ($isEdited ? $editedPost->post_title : $post->title); ?>" class="content-post_img"/>
									<h2 class="content-post_title"><?php echo ($isEdited ? $editedPost->post_title : $post->title); ?></h2>
									<div class="content-post_actions">
										<a href="#" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $post->ID; ?>" data-title="<?php echo ($isEdited ? $editedPost->post_title : $post->title); ?>" data-editid="<?php echo ($isEdited ? $isEdited : 0);?>" style="background:'.$color.';" class="content-post_action content-post_action-edit"><?php _e('Edit','bizink-client'); ?></a>
									<?php
										if($isHidden){
											echo '<a href="#" data-id="'.$post->ID.'" data-title="'.($isEdited ? $editedPost->post_title : $post->title).'" data-hidden="'.($isHidden ? 1:0).'" style="background:'.$color.';" class="content-post_action content-post_action-show">'. __("Un Hide",'bizink-client').'</a>';
										}
										else{
											echo '<a href="#" data-id="'.$post->ID.'" data-title="'.($isEdited ? $editedPost->post_title : $post->title).'" data-hidden="'.($isHidden ? 1:0).'" style="background:'.$color.';" class="content-post_action content-post_action-hide">'. __("Hide",'bizink-client').'</a>';
										}
									?>
									</div>
								</div>
								<?php
							endforeach;
						else:
							echo "<h3>". __('Sorry there are no posts at the moment','bizink-client')."</h3>";
						endif;
						?>
						</div>
						<?php
						$i++;
					}
					?>
					</div>
				</div>
			</div>
			<div class='cx-content-modal-bg' style='display:none;'>
				<div class='cx-content-modal cx-content-model_edit' style='display:none;'>
					<div class="cx-content-model-close">X</div>
					<h2 class='cx-content-modal_type'>Edit Post</h2>
					<input type="text" placeholder="Title" value="" class='cx-content-modal_title_input'/>
					<div id="cx-content-editor" class="content_editor">
						<div class="loader">
							<div class="loader-spinner"><div style="background:'<?php echo $color; ?>';"></div><div style="background:'<?php echo $color; ?>';"></div><div style="background:'<?php echo $color; ?>';"></div></div>
							<p><?php _e('Loading Editor...','bizink-client'); ?></p>
						</div>
					</div>
					<div class='cx-content-modal_actions'>
						<a href='#' style="background:'<?php echo $color; ?>';" class='cx-content-modal_action cx-content-modal_action-confirm' data-nonce="<?php echo $nonce; ?>"><?php _e("Save","bizink-client"); ?></a>
						<a href='#' style="background:'<?php echo $color; ?>';" class='cx-content-modal_action cx-content-modal_action-cancel'><?php _e("Close","bizink-client"); ?></a>
					</div>
				</div>
				<div class='cx-content-modal cx-contnet-model_hide' style='display:none;'>
					<div class="cx-content-model-close">X</div>
					<h2 class='cx-content-modal_title'></h2>
					<p><?php _e("Are you sure you wish to hide this post?","bizink-client"); ?></p>
					<div class='cx-content-modal_actions'>
						<a href='#' style="background:'<?php echo $color; ?>';" class='cx-content-modal_action cx-content-modal_action-confirm' data-nonce="<?php echo $nonce; ?>"><?php _e("Yes Hide","bizink-client"); ?></a>
						<a href='#' style="background:'<?php echo $color; ?>';" class='cx-content-modal_action cx-content-modal_action-cancel'><?php _e("No Cancel","bizink-client"); ?></a>
					</div> 
				</div>
				<div class='cx-content-modal cx-contnet-model_show' style='display:none;'>
					<div class="cx-content-model-close">X</div>
					<h2 class='cx-content-modal_title'></h2>
					<p><?php _e("Do you wish to show this post?","bizink-client"); ?></p>
					<div class='cx-content-modal_actions'>
						<a href='#' style="background:'<?php echo $color; ?>';" class='cx-content-modal_action cx-content-modal_action-confirm' data-nonce="<?php echo $nonce; ?>"><?php _e("Yes Show","bizink-client"); ?></a>
						<a href='#' style="background:'<?php echo $color; ?>';" class='cx-content-modal_action cx-content-modal_action-cancel'><?php _e("No Cancel","bizink-client"); ?></a>
					</div> 
				</div>
			</div>
			<?php
		}
		return ob_get_clean();
	}

	public function field_plugin_install_grid($field, $section, $scope){
		$plugins = $field['plugins'];
		$html = '';
		foreach ( $plugins as $plugin ) {
			$html .= "<div class='cx-plugin-install-grid-item'>";
				$html .= "<div class='cx-plugin-install-grid-item-thumb'>";
					$html .= "<img src='{$plugin['thumbnail']}' alt='{$plugin['name']}' width='100' />";
				$html .= "</div>";
				$html .= "<div class='cx-plugin-install-grid-item-content'>";
					$html .= "<h3>{$plugin['name']}</h3>";
					$html .= "<p>{$plugin['description']}</p>";
				$html .= "</div>";
				$html .= "<div class='cx-plugin-install-grid-item-actions'>";
					if($plugin['installed']){
						$html .= "<p class='cx-plugin-installed-text'>".__('Installed')."</p>";
					}
					else{
						$html .= "<a href='#' data-url='{$plugin['url']}' data-plugin='{$plugin['plugin']}' data-nonce=".wp_create_nonce('cx-plugin-install')." class='cx-plugin-install-grid-item-button'>";
							$html .= __('Install');		
						$html .= "</a>";
					}
				$html .= "</div>";
			$html .= "</div>";
		}
		return $html;
	}

	public function field_pageselect( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->get_value( $field, $section, $default, $scope );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-select cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';
		$class 			.= isset( $field['select2'] ) && $field['select2'] ? ' cx-select2' : '';
		$class 			.= isset( $field['chosen'] ) && $field['chosen'] ? ' cx-chosen' : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$options 		= isset( $field['options'] ) ? $field['options'] : [];
		$default_page   = isset( $field['default_page'] ) ? $field['default_page'] : false;

		$html  = '';
		if( $default_page ){
			$html .= "<div class=\"cx-field-pageselect-div\">";
		}
		$html .= "<select id='select-{$name}' name='{$name}' id='{$id}' class='{$class}' {$required} {$disabled} placeholder='{$placeholder}'>";
		foreach ( $options as $key => $title ) {
			$html .= "<option value='{$key}' " . selected( $value, $key, false ) . ">{$title}</option>";
		}
		$html .= '</select>';

		if( $default_page ){
			$_nonce = wp_create_nonce('cx-createpage');
			$html .= "<button id=\"selectbutton-{$name}\" data-nonce='{$_nonce}' data-select=\"#select-{$name}\" data-post_type='".$default_page['post_type']."' data-post_status='".$default_page['post_status']."' data-post_content='".$default_page['post_content']."' data-post_title='".$default_page['post_title']."' class=\"button button-primary selectbutton cx-createpage\" type=\"button\">".__("Create Page")."</button>";
			$html .= "</div>";
		}

		return $html;
	}

	public function field_file( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= $this->esc_str( $this->get_value( $field, $section, $default, $scope ) );

		$type 			= $field['type'];
		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$required 		= isset( $field['required'] ) && $field['required'] ? " required" : "";
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";

		$upload_button	= isset( $field['upload_button'] ) ? $field['upload_button'] : __( 'Choose File' );
		$select_button	= isset( $field['select_button'] ) ? $field['select_button'] : __( 'Select' );

		$html  = '';
		$html .= "<input type='text' class='{$class} cx-file' id='{$id}' name='{$name}' value='{$value}' placeholder='{$placeholder}' {$readonly} {$required} {$disabled}/>";
		$html  .= "<input type='button' class='button cx-browse' data-title='{$label}' data-select-text='{$select_button}' value='{$upload_button}' {$required} {$disabled} />";

		return $html;
	}

	public function field_wysiwyg( $field, $section, $scope ) {
		$default		= isset( $field['default'] ) ? $field['default'] : '';
		$value			= stripslashes( $this->get_value( $field, $section, $default, $scope ) );

		$name 			= $scope == 'option' ? $field['id'] : "{$section['id']}[{$field['id']}]";
		$label 			= $field['label'];
		$id 			= "{$section['id']}-{$field['id']}";

		$class 			= "cx-field cx-field-{$field['type']}";
		$class 			.= isset( $field['class'] ) ? $field['class'] : '';

		$placeholder	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$readonly 		= isset( $field['readonly'] ) && $field['readonly'] ? " readonly" : "";
		$disabled 		= isset( $field['disabled'] ) && $field['disabled'] ? " disabled" : "";
		$teeny			= isset( $field['teeny'] ) && $field['teeny'];
		$text_mode		= isset( $field['text_mode'] ) && $field['text_mode'];
		$media_buttons  = isset( $field['media_buttons'] ) && $field['media_buttons'];
		$rows 			= isset( $field['rows'] ) ? $field['rows'] : 10;

		$html  = '';
		$settings = [
			'teeny'         => $teeny,
			'textarea_name' => $name,
			'textarea_rows' => $rows,
			'quicktags'		=> $text_mode,
			'media_buttons'	=> $media_buttons,
		];

		if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
			$settings = array_merge( $settings, $field['options'] );
		}

		ob_start();
		wp_editor( $value, $id, $settings );
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function field_divider( $field, $section, $scope ) {
		return $field['label'];
	}

	public function field_group( $field, $section, $scope ) {
		$items = $field['items'];
		$html = '';
		foreach ( $items as $item ) {
			$item['class'] = ' cx-field-group';
			$html .= $this->populate( $item, $section, $scope );
		}

		return $html;
	}

	public function generate_icon( $value ) {
		if( $value == '' ) return '';
		if( strpos( $value, '://' ) !== false ) {
			return "<img class='cx-icon-{$this->config['id']}' src='{$value}' />";
		}
		return "<span class='dashicons {$value}'></span>";
	}

	public function esc_str( $string ) {
		return stripslashes( esc_attr( $string ) );
	}

	public function deep_key_exists( $arr, $key ) {
		if ( array_key_exists( $key, $arr ) && $arr[ $key ] == true ) return true;
		foreach( $arr as $element ) {
			if( is_array( $element ) && $this->deep_key_exists( $element, $key ) ) {
				return true;
			}
		}
		return false;
	}

	public function has_select2() {
		return $this->deep_key_exists( $this->config, 'select2' );
	}

	public function has_chosen() {
		return $this->deep_key_exists( $this->config, 'chosen' );
	}
}