<?php
function route( $method, $url_data, $form_data ) {
    include_once "response.php";
    switch ( $method ) {
        case 'GET': {
            if (
                ( !isset( $form_data[ "src" ] ) || !is_string( $form_data[ "src" ] ) )
            ) {
                request_error( "Incomplete or malformed data" );
                die();
            };
            if ( !file_exists( "pages/" . $form_data[ "src" ] . ".html" ) ) {
                request_error( "Unable to locate source page" );
                die();
            };
            header( "Content-Type: text/html; charset=utf-8" );
            echo file_get_contents( "pages/" . $form_data[ "src" ] . ".html" );
            break;
        }
        case 'HEAD':
            break;
        default:
            request_error( "No action for provided method", 501 );
            break;
    }
}