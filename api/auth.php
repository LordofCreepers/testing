<?php
function route( $method, $url_data, $form_data ) {
    include_once "connect_db.php";
    include_once "response.php";
    switch ( $method ) {
        case 'POST': {
            if ( isset( $_COOKIE[ "login" ] ) ) {
                request_response( array(
                    "success" => true,
                    "reasons" => "Already logged-in",
                    "credentials" => $_COOKIE[ "login" ],
                ));
                die();
            };
            if ( !isset( $_COOKIE[ "captcha" ] ) ) {
                request_error( "Captcha required" );
                die();
            };
            include_once "hash_captcha.php";
            if ( $_COOKIE[ "captcha" ] != hash_captcha( $form_data[ "captcha" ] ) ) {
                request_error( "Incorrect captcha", 401 );
                die();
            };
            if (
                ( !isset( $form_data[ "login" ] ) || !is_string( $form_data[ "login" ] ) || $form_data == "" ) ||
                ( !isset( $form_data[ "password" ] ) || !is_string( $form_data[ "password" ] ) )
            ) {
                request_error( "Incomplete or malformed data" );
                die();
            };
            $result = $mysqli->query( "SELECT * FROM `users` WHERE `login`='" . $form_data[ "login" ] . "'" );
            $incorrect = array(
                "success" => false,
                "reasons" => "Incorrect login or password",
                "credentials" => array(),
            );
            if ( !$result ) {
                // sleep( rand( 0.5, 1.5 ) );
                request_response( $incorrect );
                die();
            };
            // include_once "hash_password.php";
            $result = $result->fetch_all( MYSQLI_ASSOC );
            if ( !password_verify( $form_data[ "password" ], $result[ 0 ][ "password" ] ) ) {
                // sleep( rand( 0.5, 1.5 ) );
                request_response( $incorrect );
                die();
            };
            setcookie( "login", $form_data[ "login" ], time() + 788400000 );
            request_response( array( 
                "success" => true,
                "reasons" => "",
                "credentials" => $_COOKIE,
            ), 201 );
            break;
        }
        case 'DELETE': {
            if ( !isset( $_COOKIE[ "login" ] ) ) {
                request_error( "No session to destroy" );
                die();
            };
            session_destroy();
            session_start();
            session_regenerate_id();
            setcookie( "captcha", null, 1 );
            setcookie( "login", null, 1 );
            request_response( array() );
            break;
        }
        case 'HEAD':
            break;
        default:
            request_error( "No action for provided method", 501 );
            break;
    }
}