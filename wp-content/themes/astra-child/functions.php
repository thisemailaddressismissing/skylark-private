<?php
/**
 * Astra Child Theme functions and definitions
 */

add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles' );
function astra_child_enqueue_styles() {
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'astra-parent-style' ), '1.0.9' );

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

// -------------------------------------------------------------
// 4. OPEN GRAPH & SOCIAL SHARE PREVIEW FOR HOMEPAGE
// -------------------------------------------------------------
add_filter( 'wpseo_opengraph_title', 'skylark_custom_og_title', 99 );
add_filter( 'wpseo_twitter_title', 'skylark_custom_og_title', 99 );
function skylark_custom_og_title( $title ) {
    if ( is_front_page() || is_home() ) {
        return 'Skylark Apparel Limited';
    }
    return $title;
}

add_filter( 'wpseo_opengraph_site_name', function( $name ) {
    return 'Skylark Apparel Limited';
}, 99 );

add_filter( 'wpseo_opengraph_image', 'skylark_custom_og_image', 99 );
add_filter( 'wpseo_twitter_image', 'skylark_custom_og_image', 99 );
function skylark_custom_og_image( $img ) {
    if ( is_front_page() || is_home() ) {
        return home_url( '/wp-content/uploads/2026/05/skylark-social-share.png' );
    }
    return $img;
}

add_filter( 'wpseo_twitter_card_type', function( $type ) {
    if ( is_front_page() || is_home() ) {
        return 'summary_large_image';
    }
    return $type;
}, 99 );

add_action( 'wp_head', 'skylark_fallback_social_meta', 1 );
function skylark_fallback_social_meta() {
    if ( ( is_front_page() || is_home() ) && ! defined( 'WPSEO_VERSION' ) ) {
        $img_url = home_url( '/wp-content/uploads/2026/05/skylark-social-share.png' );
        echo '<meta property="og:title" content="Skylark Apparel Limited" />' . "\n";
        echo '<meta property="og:site_name" content="Skylark Apparel Limited" />' . "\n";
        echo '<meta property="og:image" content="' . esc_url( $img_url ) . '" />' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="Skylark Apparel Limited" />' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url( $img_url ) . '" />' . "\n";
    }
}

// -------------------------------------------------------------
// 5. WEBP TRANSPARENT DELIVERY (PHP-based, .htaccess-safe)
// -------------------------------------------------------------
// Rewrites image URLs in HTML output to .webp when the browser
// supports WebP and a .webp file exists on disk. Runs in PHP so
// it survives .htaccess overwrites by WordPress/plugins.
// -------------------------------------------------------------

function skylark_browser_supports_webp() {
    return isset( $_SERVER['HTTP_ACCEPT'] ) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false;
}

add_action( 'template_redirect', 'skylark_webp_start_buffer' );
function skylark_webp_start_buffer() {
    if ( ! skylark_browser_supports_webp() ) {
        return;
    }
    if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_robots() ) {
        return;
    }
    ob_start( 'skylark_webp_rewrite_html' );
}

function skylark_webp_rewrite_html( $html ) {
    if ( empty( $html ) ) {
        return $html;
    }

    $upload_dir  = wp_get_upload_dir();
    $upload_path = $upload_dir['basedir'];
    $upload_url  = $upload_dir['baseurl'];

    // --- Pass 1: Rewrite normal URLs (src, srcset, url(), etc.) ---
    $escaped_url = preg_quote( $upload_url, '#' );
    $pattern = '#(' . $escaped_url . '/[^\s"\')\,\>\;]+\.(jpe?g|png|gif|bmp|tiff?))(?=[\s"\')\,\>\;])#i';

    $html = preg_replace_callback( $pattern, function( $match ) use ( $upload_path, $upload_url ) {
        return skylark_webp_resolve( $match[1], $match[2], $upload_path, $upload_url );
    }, $html );

    // --- Pass 2: Rewrite JSON-LD escaped URLs (e.g. http:\/\/...\/file.png) ---
    $json_url = str_replace( '/', '\\/', $upload_url );
    $escaped_json = preg_quote( $json_url, '#' );
    $json_pattern = '#(' . $escaped_json . '/[^\s"\')\,\>\;]+\.(jpe?g|png|gif|bmp|tiff?))(?=[\s"\')\,\>\;])#i';

    $html = preg_replace_callback( $json_pattern, function( $match ) use ( $upload_path, $upload_url, $json_url ) {
        $normal_url = str_replace( '\\/', '/', $match[1] );
        $result = skylark_webp_resolve( $normal_url, $match[2], $upload_path, $upload_url );
        if ( $result !== $normal_url ) {
            return str_replace( '/', '\\/', $result );
        }
        return $match[0];
    }, $html );

    return $html;
}

function skylark_webp_resolve( $image_url, $ext, $upload_path, $upload_url ) {
    $relative  = str_replace( $upload_url, '', $image_url );
    $relative  = str_replace( '/', DIRECTORY_SEPARATOR, $relative );
    $file_path = $upload_path . $relative;

    // Priority 1: file.ext.webp (e.g. photo.png.webp)
    if ( file_exists( $file_path . '.webp' ) ) {
        return $image_url . '.webp';
    }

    // Priority 2: file.webp (e.g. photo.webp)
    $webp_path = preg_replace( '/\.' . preg_quote( $ext, '/' ) . '$/i', '.webp', $file_path );
    $webp_url  = preg_replace( '/\.' . preg_quote( $ext, '/' ) . '$/i', '.webp', $image_url );
    if ( file_exists( $webp_path ) ) {
        return $webp_url;
    }

    return $image_url;
}
