<?php
/**
 * GV Compact data-table
 */
function gravityview_datatables_table_class_compact( $class = 'display dataTables' ) {
	return 'display compact dataTables';
}
add_filter( 'gravityview_datatables_table_class', 'gravityview_datatables_table_class_compact' );

/**
 * login to homepage
 */
/* redirect users to front page after login */
function meh_redirect_to_front_page() {
global $redirect_to;
if (!isset($_GET['redirect_to'])) {
$redirect_to = get_option('siteurl');
}
}
add_action('login_form', 'meh_redirect_to_front_page');

/**
 * Hide facet counts
 */
add_filter( 'facetwp_facet_dropdown_show_counts', '__return_false' );

/**
 * GV Back to list after edit
 */
function gv_meh_update_message( $message, $view_id, $entry, $back_link ) {
    $link = str_replace( 'entry/'.$entry['id'].'/', '', $back_link );
    return 'Entry Updated. <a href="'.esc_url($link).'">Return to the list</a>';
}
add_filter( 'gravityview/edit_entry/success', 'gv_meh_update_message', 10, 4 );

/**
 * cpt-archive
 */
add_action( 'init', function() {
    add_post_type_support( 'press_release', 'archive' );
} );

/**
 * Bulletin Board
 */
add_action( 'tha_content_before', 'bulletin_slide' );
function bulletin_slide() {

if ( is_page( 'parent-home' ) ) {
    $time = current_time( 'timestamp' );
    $args = array (
	    'post_type'              => array( 'sc_event', 'bulletin' ),
	    'order'                  => 'asc',
        'meta_key'               => 'sc_event_end_date_time',
        'meta_compare'           => '>=',
        'meta_value'             => mktime( 0, 0, 0, date( 'n', $time ), date( 'd', $time ), date( 'Y', $time ) ),
	    'orderby'                => 'date',
	    'tax_query' => array(
		    'relation' => 'OR',
		    array(
			    'taxonomy' => 'sc_event_category',
			    'field'    => 'term_id',
			    'terms'    => array( 'pto','macs','school','athletics' ),
			    'operator' => 'NOT IN',
		    ),
        ),
    );

$query = new WP_Query( $args );

if ( $query->have_posts() ) { ?>
    <div class="gallery bg-2 js-flickity" data-flickity-options='{ "autoPlay": 1500, "wrapAround": true }'>
	<?php while ( $query->have_posts() ) { ?>
		<?php $query->the_post(); ?>
            
            <a href="<?php the_permalink(); ?>" class="btn gallery-cell u-1/2 u-1/5@md">
                <i class="material-icons opacity">&#xE878;</i> <?php the_title(); ?>
            </a>
            
	<?php } ?>
        </div>
<?php }

wp_reset_postdata();
    }

}

/**
 * Edit Post
 */
add_action( 'tha_content_bottom', 'smcs_edit_post' );

function smcs_edit_post() {
	edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			esc_html__( 'Edit %s', '_s' ),
			the_title( '<span class="screen-reader-text">"', '"</span>', false )
		),
		'<span class="edit-link">',
		'</span>'
	);
}

/**
 * smaa_member_meta
 */
function smaa_member_meta( $user_contact_method ) {

    if ( ! current_user_can( 'edit_users'  ) )
        return;
    
	$user_contact_method['smaa_member'] = __( 'SMAA Member year ( example 2016-2017 )', 'smcs' );

	return $user_contact_method;

}
add_filter( 'user_contactmethods', 'smaa_member_meta' );

/**
 * GV cache
 */
add_filter('gravityview_use_cache', '__return_false');
