<?php

add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
function enqueue_scripts() {

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false' );
    wp_enqueue_script( 'maptheme', get_stylesheet_directory_uri() . '/js/map.js' );
    wp_enqueue_script( 'markerclusterer', get_stylesheet_directory_uri() . '/js/markerclusterer.min.js' );

    if ( !defined( 'GEOUSER_INITIAL_LAT' ) || !GEOUSER_INITIAL_LAT
        || !defined( 'GEOUSER_INITIAL_LNG' ) || !GEOUSER_INITIAL_LNG ) {
        // Brazil
        define( 'GEOUSER_INITIAL_LAT', -15 );
        define( 'GEOUSER_INITIAL_LNG', -55 );
    }

    $params['lat'] = GEOUSER_INITIAL_LAT;
    $params['lng'] = GEOUSER_INITIAL_LNG;
    $params['imgbase'] = get_stylesheet_directory_uri() . '/img/';
    $params['users'] = get_map_users();
    wp_localize_script( 'maptheme', 'maptheme', $params );

}

function get_map_users() {

    global $wpdb;

    if ( $users = get_transient( 'map_users' ) )
        return $users;

    $query = $wpdb->get_results( "
        SELECT user_id, user_email, display_name, meta_value
        FROM {$wpdb->users}, {$wpdb->usermeta}
        WHERE 1=1
            AND user_id = ID
            AND meta_key = 'location'
    " );

    $users = array();
    foreach( $query as $q ) {
        $loc = unserialize( $q->meta_value );
        if ( empty( $loc[0] ) || empty( $loc[1] ) )
            continue;
        $marker = !empty($loc[2]) ? get_stylesheet_directory_uri() . '/img/'.'pins/'.$loc[2].'.png' : $params['imgbase'] . 'marker.png';
        $users[] = array(
            'ID' => $q->user_id,
            'display_name' => $q->display_name,
            'gravatar' => md5( $q->user_email ),
            'lat' => $loc[0],
            'lng' => $loc[1],
            'marker' => $marker
        );
    }

    set_transient( 'map_users', $users, 3600 * 24 );

    return $users;

}

if ( isset( $_GET['embed'] ) )
    add_filter('show_admin_bar', '__return_false');

?>
