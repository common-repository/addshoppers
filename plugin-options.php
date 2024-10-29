<?php
/**
 * AddShoppers Plugin Options
 *
 * @package WPShopPe
 * @version 1.1
 */

if ( ! function_exists( 'shop_pe_plugin_admin_init' ) ):
/**
 * Initializes plugin options.
 *
 * @since WPShopPe 1.0
 */
function shop_pe_plugin_admin_init() {
    global $plugin_page;
    
    register_setting(
        'shop_pe_plugin_options',
        'shop_pe_options',
        'shop_pe_plugin_admin_validate'
    );
}
endif;
add_action( 'admin_init', 'shop_pe_plugin_admin_init' );

if ( ! function_exists( 'shop_pe_plugin_admin_add_page' ) ):
/**
 * Registers plugin options page.
 *
 * @since WPShopPe 1.0
 */
function shop_pe_plugin_admin_add_page() {
    add_options_page(
        'AddShoppers',
        'AddShoppers',
        'edit_theme_options',
        'shop-pe-plugin',
        'shop_pe_plugin_admin_do_page'
    );
}
endif;
add_action( 'admin_menu', 'shop_pe_plugin_admin_add_page' );

/**
 * Include admin CSS only the plugin settings page.
 *
 * @since WPShopPe 1.0
 */
function my_enqueue($hook) {
    if( 'settings_page_shop-pe-plugin' != $hook )
        return;
    wp_enqueue_style( 'addshoppers_admin_css', plugin_dir_url( __FILE__ ) . 'addshoppers-admin.css' );
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );


if ( ! function_exists( 'shop_pe_plugin_admin_do_page' ) ):
/**
 * Renders admin plugin options page.
 *
 * @since WPShopPe 1.0
 */
function shop_pe_plugin_admin_do_page() {
    $options = get_option( 'shop_pe_options' );
    if ( empty( $options ) ) {
        $options = array(
            'shop_id' => '',
            'default_buttons' => 1,
            'selected_networks' => addshoppers_networks('default')
        );
    }
    $all_networks = addshoppers_networks('all');
    if (!$options['selected_networks']) $options['selected_networks'] = addshoppers_networks('default');  
?>
    <div class="wrap addshoppers">
        <?php screen_icon(); ?><h2>AddShoppers</h2>
		
        <form method="post" action="options.php">
            <?php settings_fields( 'shop_pe_plugin_options' ); ?>
            <h3>Plugin settings</h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">Shop ID</th>
                        <td>
                            <input id="shop-id" class="regular-text" type="text" name="shop_pe_options[shop_id]" value="<?php echo( $options['shop_id'] ); ?>" />
                            <p class="description">(Optional) Enter your shop ID if you want to track the analytics of your sharing buttons. <br/>You can get your shop ID or sign up for one <a href="https://www.addshoppers.com/merchants">here</a>. Go to &rarr; Settings &rarr; Shops and copy the Shop ID for your shop into the field above.</p>
                        </td>
                     </tr>
                      <tr>
                        <th scope="row">Show Floating Buttons?</th>
                        <td>
                            <input id="default-buttons" type="checkbox" name="shop_pe_options[default_buttons]" value="1" <?php if ($options['default_buttons'] == 1 ) echo 'checked="checked" '; ?>/>
                            <p class="description">Check this box to show the default floating buttons. If you want different buttons, grab the code for the buttons you want in your <a href="https://www.addshoppers.com/merchants">AddShoppers Dashboard</a> under Apps &rarr; Sharing Buttons. Copy and paste the code for your buttons into the desired location in your active theme.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Choose Your Social Networks</th>
                        <td>
                        	<?php foreach ($all_networks as $key => $display) { ?>
                        		<input id="selected-networks-<?php echo $key; ?>" class="network_select <?php echo $key; ?>" type="checkbox" name="shop_pe_options[selected_networks][]" value="<?php echo $key; ?>" <?php if (in_array($key,$options['selected_networks'])) echo 'checked="checked" '; ?>/> <label for="selected-networks-<?php echo $key; ?>"></label>
                        	<?php } ?>
                            
                            <p class="description">Select the networks that you want in your sharing button set.</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="submit" value="Save" class="button-primary">
            </p>
        </form>
    </div>
<?php
}
endif;

function addshoppers_networks($which) {
	$networks = array();
	$networks['all'] = array(
		'facebook' => 'Facebook',
		'twitter' => 'Twitter',
		'google' => 'Google Plus',
		'email' => 'Email',
		'pinterest' => 'Pinterest',
		'stumbleupon' => 'StumbleUpon',
		'tumblr' => 'Tumblr',
		'wanelo' => 'Wanelo',
		'polyvore' => 'Polyvore',
		'kaboodle' => 'Kaboodle'
	);
	$networks['default'] = array(
		'facebook',
		'twitter',
		'email',
		'google'
	);
	return $networks[$which];
}

if ( ! function_exists( 'shop_pe_plugin_admin_validate' ) ):
/**
 * Validates data from the form.
 *
 * @since WPShopPe 1.0
 */
function shop_pe_plugin_admin_validate( $input ) {
    $options = get_option( 'shop_pe_options' );
    if ( empty( $options ) ) {
        $options = array(
            'shop_id' => '',
            'default_buttons' => 1,
            'selected_networks' => addshoppers_networks('default')
        );
    }

    $message = 'Options saved.';
    $type = 'updated';

    if ( empty( $input ) ) {
        $message = 'You must provide options.';
        $type = 'error';
    } else {
    	// validating shop ID
        $shop_id = strtolower( $input['shop_id'] );
        if ( preg_match( '/^[0-9a-f]{24}$/', $shop_id ) == 0 && !empty($shop_id) ) {
            $message = 'Invalid Shop ID: ' . $input['shop_id'];
            $type = 'error';
        }
        else {
            $options['shop_id'] = $shop_id;
        }
    }
	
	// default buttons
    $options['default_buttons'] = $input['default_buttons'];
    
    // networks to show
    if (empty($input['selected_networks'])) {
    	$message = 'You must select at least 1 network for your sharing buttons. To disable the default sharing buttons, uncheck the "Show Floating Buttons?" checkbox.';
        $type = 'error';
    }
    else {
    	$all_networks = addshoppers_networks('all');
    	$options['selected_networks'] = array();
    	foreach ($input['selected_networks'] as $network) {
    		if (array_key_exists($network,$all_networks)) {
    			$options['selected_networks'][] = $network;
    		}
    	}
    }

    add_settings_error(
        'shop_pe_options',
        'settings_updated',
        $message,
        $type
    );

    return $options;
}
endif;
