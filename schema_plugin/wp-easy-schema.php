<?php
/*
*
*
*
Plugin Name: Wordpress EZ Schema
Plugin URI: -
Description: Easily add Schema.org Markup into your web page (supports both microdata and JSON-LD)
Version: 2.3.0
Author: Alex Nordhausen @ Oozle Media
Author URI: http://northhousedesigns.com/
Text Domain: wp-ez-schema
*
*
*
*/

class ez_schema {

	public function __construct() {
		// Hook into the admin menu
		add_action( 'admin_menu', array( $this, 'create_menu_page' ) );
		add_action('admin_enqueue_scripts', array($this, 'init'));			
		add_action('wp_head', array($this, 'add_to_head'));
	    add_action('wp_ajax_process_form', array($this, 'process_ajax'));			
		add_shortcode('schema', array($this, 'add_schema'));
	}

	public function init() {
		// load script
		wp_enqueue_script('button-js', plugin_dir_url(__FILE__) . 'add-button.js', array('jquery'));
		
		// localize script
		wp_localize_script('button-js', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
	}
	
	public function create_menu_page() {
		$page_title = "EZSchema Setup";
		$menu_title = "EZSchema";
		$permissions = "manage_options";
		$menu_slug = "ez-setup-page";
		$function = array($this, "ez_callback");
		$icon_url = "dashicons-list-view";
		$position = "20";	
		
		// Create actual page using settings above
		add_menu_page(
			$page_title,
			$menu_title,
			$permissions,
			$menu_slug,
			$function,
			$icon_url,
			$position
		);
	}
	
	public function ez_callback() {
		?>
        <style>
			#plugin form { box-sizing: border-box; float: left; background-color: white; padding: 25px; width: 90%; margin: 25px; box-shadow: 2px 2px 10px #acacac; }
			textarea, input, select { width: 100%; }
			
			/* Addition form styles */
			#form-controllers { background: white; padding: 25px; box-shadow: 2px 2px 10px #acacac; width: 280px; margin: auto; text-align: center; }
			button#ajax-create-field { margin: 5px 5px; }
			button#ajax-remove-field { margin: 5px 5px; }
			
			/* Accents and other styles */
			/*.wp-core-ui .button-primary { background: #FF2D16; border-color: #D91500 #D51500 #D51500; box-shadow: 0 1px 0 #B31200; text-shadow: 0 -1px 1px #D51500,1px 0 1px #D51500,0 1px 1px #D51500,-1px 0 1px #D51500 } */
			.accent { color: #FF2D16; }
			
			@media only screen and (min-width: 960px) {				
				/* Addition form styles */
				#form-controllers { background: white; padding: 25px; box-shadow: 2px 2px 10px #acacac; width: 720px; margin: auto; text-align: center; }
				button#ajax-create-field { margin: 0 5px; }
				button#ajax-remove-field { margin: 0 5px; }			
			}
			
			@media only screen and (min-width: 1180px) {
				#plugin form { box-sizing: border-box; float: left; background-color: white; padding: 25px; width: 45%; margin: 25px; box-shadow: 2px 2px 10px #acacac; }
				textarea, input, select { width: 100%; }
			}
		</style>
        <?php
		if(!isset($_REQUEST['settings-updated'])) {
			$_REQUEST['settings-updated'] = false;
		} 
		echo "<img src='http://spruce.oozlethemes.com/wp-content/uploads/2016/08/logo.png' />";
		echo "<h1 class='setup-title'>EZSchema Setup</h1>";
		echo "<a style='margin: 0 5px;' href='#'><button class='button button-primary'>Give us Feedback!</button></a>";
		echo "<a href='#'><button class='button button-secondary'>Get Technical Support!</button></a>";
		echo "<hr>";
		echo "<div id='form-controllers'><button id='ajax-create-field' class='button button-primary'>Add Field Group</button>";
		echo "<button id='ajax-remove-field' class='button button-secondary'>Remove Field Group</button></div>";
		?>

        <?php $n = 1; ?>
        <div id="plugin" class="form-wrap">
        	<?php while($n <= get_option('form-count') || $n == 1) : ?>
            <form method="POST" class="plugin-form plugin-form-<?php echo $n; ?>">
            	<input type="hidden" name="updated-<?php echo $n; ?>" value="true" />
            	<?php wp_nonce_field( 'update_nonce', 'field_form' ); // security ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><label for="business-name">Business Name:</label></th>
                            <td><input name="business-name" class="field" id="business-name" type="text" placeholder="Business Name" value="<?php echo get_option('business-name-' . $n); ?>" class="form-field" /></td>
                        </tr>
                        <tr>
                            <th><label for="business-url">Business URL:</label></th>
                            <td><input name="business-url" class="field" id="business-name" type="text" placeholder="Business URL (Website)" value="<?php echo get_option('business-url-' . $n); ?>" class="form-field" /></td>
                        </tr>
                         <tr>
                            <th><label for="business-image">Business Logo:</label></th>
                            <td><input name="business-image" class="field" id="business-image" type="text" placeholder="Business Logo URL" value="<?php echo get_option('business-image-' . $n); ?>" class="form-field" /></td>
                        </tr>
                        <tr>
                            <th><label for="business-reviewcount">Review Count (How many reviews):</label></th>
                            <td><input name="business-reviewcount" class="field" id="business-reviewcount" type="text" placeholder="How many people have reviewed this business?" value="<?php echo get_option('business-reviewcount-' . $n); ?>" class="form-field" /></td>
                        </tr>
                        <tr>
                            <th><label for="business-rating">Rating Value (x/5 stars):</label></th>
                            <td><input name="business-rating" class="field" id="business-rating" type="text" placeholder="What's the star rating value (i.e 4.5)?" value="<?php echo get_option('business-rating-' . $n); ?>" class="form-field" /></td>
                        </tr>
                        <tr>
                            <th><label for="business-description">Business Description:</label></th>
                            <td><textarea name="business-description" class="field" id="business-description" type="text" placeholder="Business Description" class="form-field" /><?php echo get_option('business-description-' . $n); ?></textarea>
                        </tr>
                        <tr>
                            <th><label for="business-type">Business Type:</label></th>
                            <td><select name="business-type" class="field" id="business-type" />
                            	<option id="salon" value="LocalBusiness" <?php if(get_option('business-type-' . $n) == 'LocalBusiness') : ?>selected<?php endif; ?>>Local Business</option>
                            	<option id="salon" value="BeautySalon" <?php if(get_option('business-type-' . $n) == 'BeautySalon') : ?>selected<?php endif; ?>>Beauty Salon</option>
                                <option id="plumber" value="Plumber" <?php if(get_option('business-type-' . $n) == 'Plumber') : ?>selected<?php endif; ?>>Plumber</option>
                                <option id="mechanic" value="AutoRepair" <?php if(get_option('business-type-' . $n) == 'AutoRepair') : ?>selected<?php endif; ?>>Automobile Repair</option>
                                <option id="mechanic" value="Hospital" <?php if(get_option('business-type-' . $n) == 'Hospital') : ?>selected<?php endif; ?>>Hospital</option>
                                <option id="mechanic" value="FireStation" <?php if(get_option('business-type-' . $n) == 'FireStation') : ?>selected<?php endif; ?>>Fire Station</option>
                                <option id="mechanic" value="PoliceStation" <?php if(get_option('business-type-' . $n) == 'PoliceStation') : ?>selected<?php endif; ?>>Police Station</option>
                                <option id="mechanic" value="CafeOrCoffeeShop" <?php if(get_option('business-type-' . $n) == 'CafeOrCoffeeShop') : ?>selected<?php endif; ?>>Cafe/Coffee Shop</option>
                            </select></td>
                        </tr>		
                        <tr>
                            <th><label for="business-phone">Business Phone:</label></th>
                            <td><input name="business-phone" class="field" id="business-phone" type="text" placeholder="Business Phone" value="<?php echo get_option('business-phone-' . $n); ?>" class="form-field" /></td>
                        </tr>	
                        <tr>
                            <th><label for="business-address">Business Address:</label></th>
                            <td><input name="business-address" class="field" id="business-address" type="text" placeholder="Business Address" value="<?php echo get_option('business-address-' . $n); ?>" class="form-field" /></td>
                        </tr>	
                        <tr>
                            <th><label for="business-city">Business City:</label></th>
                            <td><input name="business-city" class="field" id="business-city" type="text" placeholder="Business City" value="<?php echo get_option('business-city-' . $n); ?>" class="form-field" /></td>
                        </tr>	
                        <tr>
                            <th><label for="business-state">Business State:</label></th>
                            <td><input name="business-state" class="field" id="business-state" type="text" placeholder="Business State" value="<?php echo get_option('business-state-' . $n); ?>" class="form-field" /></td>
                        </tr>	
                        <tr>
                            <th><label for="business-zip">Business ZIP:</label></th>
                            <td><input name="business-zip" class="field" id="business-zip" type="text" placeholder="Business ZIP Code" value="<?php echo get_option('business-zip-' . $n); ?>" class="form-field" /></td>
                        </tr>	
                        <tr>
                            <th><label for="sitewide-check">Make Sitewide?</label></th>
                            <td><input name="sitewide-check" class="field" id="sitewide-check" type="checkbox" value="1" <?php checked('1', get_option('sitewide_check-' . $n)); ?>/></td>	
                        </tr>
                        <tr>
                        	<th><label for="shortcode">Copy/Paste on desired page:</label></th>
                            <td><input name="shortcode" class="field" id="shortcode" type="text" value="[schema id=<?php echo $n; ?>]" disabled /></td>
                        </tr>
                    </tbody>
                </table>    
                <p class="submit">
                    <input type="submit" name="submit" id="ezschema-submit" class="button button-primary" value="Save Field Group" />
                </p>   
                <?php // check if form has been updated - if so, launch our handler 
				if($_POST['updated-' . $n] === 'true') {
					$this->handle_form($n);
				} ?>
                
           
            </form>	
            <?php $n++; ?>
            <?php endwhile; ?>		
        </div>
        <div id="add-form">
        	<form method="POST" id="add-new">
            	<?php wp_nonce_field( 'update_nonce', 'field_form' ); // security ?>
            	<input type="hidden" name="updated" value="true" />
            	<input type="hidden" name="formcount" class="form-counter" value="<?php if(get_option('form-count') != 0 || get_option('form-count') != null) : echo get_option('form-count'); else : ?>1<?php endif; ?>"></input>  
                
				<?php if (get_option('form-count') == null) {
						update_option('form-count', 1);
				} ?>
                <?php // check if form has been updated - if so, launch our handler 
				if($_POST['updated'] === 'true') {
					$this->handle_addition();
				} ?> 
            </form>
        </div>       
		<?php
		
		
	}
	
	public function handle_form($n) {
		
		// check nonce
		if(!isset($_POST['field_form']) || !wp_verify_nonce($_POST['field_form'], 'update_nonce')) {
			?>
            <div class="error">
            	<p>Sorry, your nonce was not correct. Please try again.</p>
            </div>
            <?php
			exit; // get out of here!
		} else {
			// Handle form data if authenticated			
			$business_name = sanitize_text_field($_POST['business-name']);	
			$business_url = sanitize_text_field($_POST['business-url']);
			$business_image = sanitize_text_field($_POST['business-image']);
			$business_reviewcount = sanitize_text_field($_POST['business-reviewcount']);
			$business_rating = sanitize_text_field($_POST['business-rating']);	
			$business_desc = sanitize_text_field($_POST['business-description']);
			$business_type = sanitize_text_field($_POST['business-type']);	
			$business_phone = sanitize_text_field($_POST['business-phone']);
			$business_address = sanitize_text_field($_POST['business-address']);
			$business_city = sanitize_text_field($_POST['business-city']);	
			$business_state = sanitize_text_field($_POST['business-state']);
			$business_zip = sanitize_text_field($_POST['business-zip']);
			
			$is_sitewide = $_POST['sitewide-check'];
			//$form_count = $_POST['formcount'];
					
			update_option('business-name-' . $n, $business_name);
			update_option('business-url-' . $n, $business_url);
			update_option('business-image-' . $n, $business_image);
			update_option('business-reviewcount-' . $n, $business_reviewcount);
			update_option('business-rating-' . $n, $business_rating);
			update_option('business-description-' . $n, $business_desc);
			update_option('business-type-' . $n, $business_type);
			update_option('business-phone-' . $n, $business_phone);
			update_option('business-address-' . $n, $business_address);
			update_option('business-city-' . $n, $business_city);
			update_option('business-state-' . $n, $business_state);
			update_option('business-zip-' . $n, $business_zip);
			
			update_option('sitewide_check-'. $n, $is_sitewide);
					  
		 	//update_option('form-count', $form_count);
			
			?>
                        
            <script>
			location.reload();
			</script>

            <?php

			
		}
					  

	}
	
	public function handle_addition() {
		
		// check nonce
		if(!isset($_POST['field_form']) || !wp_verify_nonce($_POST['field_form'], 'update_nonce')) {
			?>
            <div class="error">
            	<p>Sorry, your nonce was not correct. Please try again.</p>
            </div>
            <?php
			exit; // get out of here!
		} else {

			$form_count = $_POST['formcount'];
					  
		 	update_option('form-count', $form_count);
			
			?>
            <script>
			location.reload();
			</script>
            <?php
			
		}
					  

	}
	
	
	public function add_to_head() {
		$n = 0;
		while($n <= get_option('form-count')) : 
			if(get_option('sitewide_check-' . $n) === '1') {
				$schema = "<script type='application/ld+json'>";
				$schema .= '{';
				$schema .= '"@context": "http://www.schema.org",';
				$schema .= '"@type": "'. get_option('business-type-' . $n) .'",';
				$schema .= '"name": "'. get_option('business-name-' . $n) .'",';
				$schema .= '"image": "'. get_option('business-image-' . $n) .'",';
				if (get_option('business-rating-' . $n) != '' && get_option('business-reviewcount-' . $n) != '') {
					$schema .= '"aggregateRating": {';
					$schema .= '"@type": "AggregateRating",';
					$schema .= '"ratingValue": "'. get_option('business-rating-' . $n) .'",';
					$schema .= '"reviewCount": "'. get_option('business-reviewcount-' . $n) .'"';
					$schema .= '},';
				}
				$schema .= '"url": "'. get_option('business-url-' . $n) .'",';
				$schema .= '"description": "'. get_option('business-description-' . $n) .'",';
				$schema .= '"address": {';
				$schema .= '"@type": "PostalAddress",';
				$schema .= '"streetAddress": "'. get_option('business-address-' . $n) .'",';
				$schema .= '"addressLocality": "'. get_option('business-state-' . $n) .'",';
				$schema .= '"addressRegion": "'. get_option('business-city-' . $n) .'",';
				$schema .= '"postalCode": "'. get_option('business-zip-' . $n) .'",';
				$schema .= '"addressCountry": "United States"';
				$schema .= '},';
				$schema .= '"contactPoint": {';
				$schema .= '"@type": "ContactPoint",';
				$schema .= '"contactType": "sales",';
				$schema .= '"telephone": "+1'. get_option('business-phone-' . $n) .'"';
				$schema .= '}';
				$schema .= '}';
				$schema .= '</script>';
				
				echo ($schema); // ouput
			}
			$n++;
		endwhile;
	}	
	
	public function process_ajax() {

		if(isset($_POST['form_number'])) {
			$response = $_POST['form_number'];
			
			echo $response;
			die();
		}
	}
	
	public function add_schema($atts) {
		$a = shortcode_atts(array(
			'id' => 0,
		), $atts);
		
		$schema = '<div itemscope itemtype="http://schema.org/'. get_option('business-type-' . $a['id']) .'">';
		  $schema .= '<h1><span itemprop="name">' . get_option('business-name-' . $a['id']) . '</span></h1>';
		   $schema .= ' <span itemprop="description">' . get_option('business-description-' . $a['id']) . '</span>';
		   $schema .= '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
			 $schema .= '<span itemprop="streetAddress">' . get_option('business-address-' . $a['id']) . '</span> ';
			 $schema .= '<span itemprop="addressLocality">' . get_option('business-city-' . $a['id']) . '</span>, ';
			 $schema .= '<span itemprop="addressRegion">' . get_option('business-state-' . $a['id']) . '</span> ';
			 $schema .= '<span itemprop="postalCode">' . get_option('business-zip-' . $a['id']) . '</span>';
		   $schema .= '</div>';
		   if (get_option('business-rating-' . $a['id']) && get_option('business-reviewcount-' . $a['id'])) :
		   $schema .= '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
			 $schema .= 'Rating: <span itemprop="ratingValue">' . get_option('business-rating-' . $a['id']) . '</span> <br/> ';
			 $schema .= 'Based on: <span itemprop="reviewCount">' . get_option('business-reviewcount-' . $a['id']) . '</span> customer review(s) ';
		   $schema .= '</div>';
		   endif;
		   $schema .= 'Phone: <span itemprop="telephone">' . get_option('business-phone-' . $a['id']) . '</span>';
		 $schema .= '</div>';
		return $schema;
	}

}

new ez_schema();







