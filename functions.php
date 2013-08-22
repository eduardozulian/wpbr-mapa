<?php

add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
function enqueue_scripts() {

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false' );
    wp_enqueue_script( 'maptheme', get_stylesheet_directory_uri() . '/map.js' );

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
        $users[] = array(
            'ID' => $q->user_id,
            'display_name' => $q->display_name,
            'gravatar' => md5( $q->user_email ),
            'lat' => $loc[0],
            'lng' => $loc[1]
        );
    }
    return $users;

}

?>
