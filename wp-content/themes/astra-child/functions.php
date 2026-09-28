<?php
/**
 * Astra Child Theme functions and definitions
 */

add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles' );
function astra_child_enqueue_styles() {
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'astra-parent-style' ), '1.0.7' );

    // Enqueue International Telephone Input & GSAP on contact page
    if ( is_page_template( 'page-contact.php' ) || is_page( 'contact' ) ) {
        wp_enqueue_style( 'intl-tel-input-css', 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.css', array(), '19.5.6' );
        wp_enqueue_script( 'intl-tel-input-js', 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js', array(), '19.5.6', true );
        wp_enqueue_script( 'gsap-js', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
    }
}

// -------------------------------------------------------------
// 1. SMTP CONFIGURATION
// -------------------------------------------------------------
add_action( 'phpmailer_init', 'skylark_configure_smtp' );
function skylark_configure_smtp( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'skylarkapparelltd.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->Username   = 'contact@skylarkapparelltd.com';
    $phpmailer->Password   = 'Pushing5-Explore6-Padding9-Scrawny6-Payable7';
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->From       = 'contact@skylarkapparelltd.com';
    $phpmailer->FromName   = 'Skylark Apparel Website';
}

// -------------------------------------------------------------
// 2. REGISTER WP ADMIN MENU: "Contact Inquiries" (Saved in DB)
// -------------------------------------------------------------
add_action( 'init', 'skylark_register_inquiries_cpt' );
function skylark_register_inquiries_cpt() {
    register_post_type( 'contact_inquiry', array(
        'labels' => array(
            'name'               => 'Contact Inquiries',
            'singular_name'      => 'Contact Inquiry',
            'menu_name'          => 'Contact Inquiries',
            'all_items'          => 'All Inquiries',
            'view_item'          => 'View Inquiry',
            'search_items'       => 'Search Inquiries',
            'not_found'          => 'No inquiries found',
        ),
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 26,
        'menu_icon'           => 'dashicons-email-alt',
        'supports'            => array( 'title', 'editor', 'custom-fields' ),
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
    ));
}

// Custom admin columns for inquiries
add_filter( 'manage_contact_inquiry_posts_columns', function( $columns ) {
    return array(
        'cb'        => '<input type="checkbox" />',
        'title'     => 'Sender Name',
        'role'      => 'Role / Profile',
        'email'     => 'Email',
        'phone'     => 'Phone',
        'date'      => 'Received Date',
    );
});

add_action( 'manage_contact_inquiry_posts_custom_column', function( $column, $post_id ) {
    switch ( $column ) {
        case 'role':
            echo esc_html( get_post_meta( $post_id, '_inquiry_role', true ) );
            break;
        case 'email':
            $email = get_post_meta( $post_id, '_inquiry_email', true );
            echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
            break;
        case 'phone':
            echo esc_html( get_post_meta( $post_id, '_inquiry_phone', true ) );
            break;
    }
}, 10, 2 );

// -------------------------------------------------------------
// 3. AJAX CONTACT FORM SUBMISSION HANDLER
// -------------------------------------------------------------
add_action( 'wp_ajax_submit_contact_form', 'handle_custom_contact_form' );
add_action( 'wp_ajax_nopriv_submit_contact_form', 'handle_custom_contact_form' );

function handle_custom_contact_form() {
    check_ajax_referer( 'custom_contact_nonce', 'nonce' );

    $role       = sanitize_text_field( $_POST['role'] ?? 'General' );
    $full_name  = sanitize_text_field( $_POST['full_name'] ?? '' );
    $email      = sanitize_email( $_POST['email'] ?? '' );
    $phone      = sanitize_text_field( $_POST['phone'] ?? '' );
    $message    = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty( $full_name ) || empty( $email ) ) {
        wp_send_json_error( array( 'message' => 'Please fill in all required fields.' ) );
    }

    // 1. Save into WordPress Database
    $post_id = wp_insert_post( array(
        'post_title'   => $full_name . ' (' . $role . ')',
        'post_content' => $message,
        'post_type'    => 'contact_inquiry',
        'post_status'  => 'publish',
    ));

    if ( $post_id && ! is_wp_error( $post_id ) ) {
        update_post_meta( $post_id, '_inquiry_role', $role );
        update_post_meta( $post_id, '_inquiry_email', $email );
        update_post_meta( $post_id, '_inquiry_phone', $phone );
    }

    // 2. Email Recipients list (Easily edit this array anytime)
    $recipients = array(
        'contact@skylarkapparelltd.com',
        'rkrakib2004@gmail.com'
    );

    $subject = sprintf( '[Skylark Inquiry] %s - %s', $role, $full_name );
    $body    = "Hello,\n\nYou have received a new contact inquiry via the Skylark Apparel website:\n\n"
             . "--------------------------------------------------\n"
             . "Profile / Role : " . $role . "\n"
             . "Full Name      : " . $full_name . "\n"
             . "Email Address  : " . $email . "\n"
             . "Phone Number   : " . ($phone ? $phone : 'Not provided') . "\n"
             . "--------------------------------------------------\n\n"
             . "Message:\n" . $message . "\n\n"
             . "---\n"
             . "Sent from: " . home_url( '/contact/' ) . "\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $full_name . ' <' . $email . '>'
    );

    $mail_sent = wp_mail( $recipients, $subject, $body, $headers );

    wp_send_json_success( array( 
        'message' => 'Thank you! Your message has been sent successfully. We will get back to you shortly.' 
    ) );
}
