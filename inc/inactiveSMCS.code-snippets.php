<?php
/**
 * Sports Tax
 */
// Register Custom Taxonomy
function smcs_sports_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Sports', 'Taxonomy General Name', 'smcs' ),
		'singular_name'              => _x( 'Sport', 'Taxonomy Singular Name', 'smcs' ),
		'menu_name'                  => __( 'Sports', 'smcs' ),
		'all_items'                  => __( 'All Sports', 'smcs' ),
		'parent_item'                => __( 'Parent Sport', 'smcs' ),
		'parent_item_colon'          => __( 'Parent Sport:', 'smcs' ),
		'new_item_name'              => __( 'New Sport', 'smcs' ),
		'add_new_item'               => __( 'Add New Sport', 'smcs' ),
		'edit_item'                  => __( 'Edit Sport', 'smcs' ),
		'update_item'                => __( 'Update Sport', 'smcs' ),
		'view_item'                  => __( 'View Sport', 'smcs' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'smcs' ),
		'add_or_remove_items'        => __( 'Add or remove Sports', 'smcs' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'smcs' ),
		'popular_items'              => __( 'Popular Sports', 'smcs' ),
		'search_items'               => __( 'Search Sports', 'smcs' ),
		'not_found'                  => __( 'Not Found', 'smcs' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'smcs_sports', array( 'smcs_student' ), $args );

}
add_action( 'init', 'smcs_sports_taxonomy', 0 );

/**
 * GF sports list
 */
add_filter( 'gform_column_input', 'set_name_column', 10, 5 );

function set_name_column( $input_info, $field, $column, $value, $form_id ) {

$user_id = get_current_user_id(); 
$key1 = 'student_1_first';
$key2 = 'student_2_first'; 
$key3 = 'student_3_first'; 
$key4 = 'student_4_first'; 
$key5 = 'student_5_first'; 
$single = true; 
$student_one = get_user_meta( $user_id, $key1, $single );
$student_two = get_user_meta( $user_id, $key2, $single ); 
$student_three = get_user_meta( $user_id, $key3, $single ); 
$student_four = get_user_meta( $user_id, $key4, $single ); 
$student_five = get_user_meta( $user_id, $key5, $single ); 
    
if( ! current_user_can('gravityview_edit_others_entries') ) {
    return array( 
        'type' => 'select', 
        'choices' => array(
            '',
            $student_one,
            $student_two,
            $student_three,
            $student_four,
            $student_five
        )
    );
}
}

/**
 * GV no link on image
 */
add_filter( 'gravityview/fields/fileupload/disable_link', 'meh_remove_gv_image_link', 10, 2 );

function meh_remove_gv_image_link( $disable_wrapped_link, $field_data ) {
    $disable_wrapped_link = true;

    return $disable_wrapped_link;
}

/**
 * GV Widget class
 */
add_filter('gravityview/widget/enable_custom_class', '__return_true' );

/**
 * GF List Row count
 */
/**
 * Gravity Wiz // Gravity Forms // Set Number of List Field Rows by Field Value
 *
 * Add/remove list field rows automatically based on the value entered in the specified field. Removes the add/remove
 * that normally buttons next to List field rows.
 *
 * @version	  1.0
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/2012/06/03/set-number-of-list-field-rows-by-field-value/
 */
class GWAutoListFieldRows {

	private static $_is_script_output;

	function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'       => false,
			'input_html_id' => false,
			'list_field_id' => false
		) );

		extract( $this->_args ); // gives us $form_id, $input_html_id, and $list_field_id

		if( ! $form_id || ! $input_html_id || ! $list_field_id )
			return;

		add_filter( 'gform_pre_render_' . $form_id, array( $this, 'pre_render' ) );

	}

	function pre_render( $form ) {
		?>

		<style type="text/css"> #field_<?php echo $form['id']; ?>_<?php echo $this->_args['list_field_id']; ?> .gfield_list_icons { display: none; } </style>

		<?php

		add_filter( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );

		if( ! self::$_is_script_output )
			$this->output_script();

		return $form;
	}

	function register_init_script( $form ) {

		// remove this function from the filter otherwise it will be called for every other form on the page
		remove_filter( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );

		$args = array(
			'formId'      => $this->_args['form_id'],
			'listFieldId' => $this->_args['list_field_id'],
			'inputHtmlId' => $this->_args['input_html_id']
		);

		$script = "new gwalfr(" . json_encode( $args ) . ");";
		$key = implode( '_', $args );

		GFFormDisplay::add_init_script( $form['id'], 'gwalfr_' . $key , GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	function output_script() {
		?>

		<script type="text/javascript">

			window.gwalfr;

			(function($){

				gwalfr = function( args ) {

					this.formId      = args.formId,
						this.listFieldId = args.listFieldId,
						this.inputHtmlId = args.inputHtmlId;

					this.init = function() {

						var gwalfr = this,
							triggerInput = $( this.inputHtmlId );

						// update rows on page load
						this.updateListItems( triggerInput, this.listFieldId, this.formId );

						// update rows when field value changes
						triggerInput.change(function(){
							gwalfr.updateListItems( $(this), gwalfr.listFieldId, gwalfr.formId );
						});

					}

					this.updateListItems = function( elem, listFieldId, formId ) {

						var listField = $( '#field_' + formId + '_' + listFieldId ),
							count = parseInt( elem.val() );
						rowCount = listField.find( 'table.gfield_list tbody tr' ).length,
							diff = count - rowCount;

						if( diff > 0 ) {
							for( var i = 0; i < diff; i++ ) {
								listField.find( '.add_list_item:last' ).click();
							}
						} else {

							// make sure we never delete all rows
							if( rowCount + diff == 0 )
								diff++;

							for( var i = diff; i < 0; i++ ) {
								listField.find( '.delete_list_item:last' ).click();
							}

						}
					}

					this.init();

				}

			})(jQuery);

		</script>

		<?php
	}

}

// EXAMPLE #1: Number field for the "input_html_id"
//new GWAutoListFieldRows( array(
//	'form_id' => 240,
//	'list_field_id' => 3,
//	'input_html_id' => '#input_240_4'
//) );

// EXAMPLE #2: Single Product Field's Quantity input as the "input_html_id"
new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 10,
	'input_html_id' => '#ginput_quantity_8_17'
) );

// EXAMPLE #2: Single Product Field's Quantity input as the "input_html_id"
new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 24,
	'input_html_id' => '#ginput_quantity_8_28'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 25,
	'input_html_id' => '#ginput_quantity_8_20'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 31,
	'input_html_id' => '#ginput_quantity_8_21'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 32,
	'input_html_id' => '#ginput_quantity_8_30'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 33,
	'input_html_id' => '#ginput_quantity_8_29'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 36,
	'input_html_id' => '#ginput_quantity_8_22'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 37,
	'input_html_id' => '#ginput_quantity_8_35'
) );

new GWAutoListFieldRows( array(
	'form_id' => 8,
	'list_field_id' => 38,
	'input_html_id' => '#ginput_quantity_8_39'
) );

/**
 * Manual Email
 */
/**
 * Gravity Wiz // Gravity Forms // Send Manual Notifications
 *
 * Provides a custom notification event that allows you to create notifications that can be sent
 * manually (via Gravity Forms "Resend Notifications" feature).
 *
 * @version   1.2
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/send-manual-notifications-with-gravity-forms/
 */
class GW_Manual_Notifications {

    private static $instance = null;

    public static function get_instance() {
        if( null == self::$instance )
            self::$instance = new self;
        return self::$instance;
    }

    private function __construct() {

	    add_filter( 'gform_notification_events', array( $this, 'add_manual_notification_event' ) );

	    add_filter( 'gform_before_resend_notifications', array( $this, 'add_notification_filter' ) );

    }

	public function add_notification_filter( $form ) {
		add_filter( 'gform_notification', array( $this, 'evaluate_notification_conditional_logic' ), 10, 3 );
		return $form;
	}

	public function add_manual_notification_event( $events ) {
		$events['manual'] = __( 'Send Manually' );
		return $events;
	}

	public function evaluate_notification_conditional_logic( $notification, $form, $entry ) {

		// if it fails conditional logic, suppress it
		if( $notification['event'] == 'manual' && ! GFCommon::evaluate_conditional_logic( rgar( $notification, 'conditionalLogic' ), $form, $entry ) ) {
			add_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		}

		return $notification;
	}

	public function abort_next_notification( $args ) {
		remove_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		$args['abort_email'] = true;
		return $args;
	}

}

function gw_manual_notifications() {
    return GW_Manual_Notifications::get_instance();
}

gw_manual_notifications();

/**
 * GF Sports student counts
 */
//Fall Sports

// Football
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 72,
	'input_html_id' => '#ginput_quantity_24_28'
) );

// Cheer
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 73,
	'input_html_id' => '#ginput_quantity_24_17'
) );

// Volleyball
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 74,
	'input_html_id' => '#ginput_quantity_24_20'
) );

// Cross Country
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 76,
	'input_html_id' => '#ginput_quantity_24_29'
) );

// Soccer JV
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 75,
	'input_html_id' => '#ginput_quantity_24_30'
) );

// Soccer
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 77,
	'input_html_id' => '#ginput_quantity_24_22'
) );

// Flag Football
new GWAutoListFieldRows( array(
	'form_id' => 24,
	'list_field_id' => 81,
	'input_html_id' => '#ginput_quantity_24_80'
) );

// Spring Sports
new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 10,
	'input_html_id' => '#ginput_quantity_17_17'
) );

new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 24,
	'input_html_id' => '#ginput_quantity_17_28'
) );

new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 25,
	'input_html_id' => '#ginput_quantity_17_20'
) );

new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 31,
	'input_html_id' => '#ginput_quantity_17_21'
) );

new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 32,
	'input_html_id' => '#ginput_quantity_17_30'
) );

new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 33,
	'input_html_id' => '#ginput_quantity_17_29'
) );

new GWAutoListFieldRows( array(
	'form_id' => 17,
	'list_field_id' => 36,
	'input_html_id' => '#ginput_quantity_17_22'
) );


