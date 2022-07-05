<?php
function route( $method, $url_data, $form_data ) {
    include_once "connect_db.php";
    include_once "response.php";
    switch ( $method ) {
        case 'GET': {
            break;
        }
        case 'POST': {
            if (
                ( !isset( $form_data[ "login" ] ) || !is_string( $form_data[ "login" ] ) || $form_data[ "login" ] == "" ) ||
                ( !isset( $form_data[ "password" ] ) || !is_string( $form_data[ "password" ] ) || $form_data[ "password" ] == "" )
            ) {
                request_error( "Incomplete or malformed data" );
                die();
            };
            $result = $mysqli->query( "SELECT * FROM `users` WHERE `login`='" . $form_data[ "login" ] . "'" );
            $result = $result->fetch_all( MYSQLI_ASSOC );
            if ( count( $result ) > 0 ) {
                request_error( "User already exists" );
                die();
            };
            $hash = password_hash( $form_data[ "password" ], PASSWORD_DEFAULT );
            $mysqli->query( "INSERT INTO `users` (`login`, `password`) VALUES ('" . $form_data[ "login" ] . "', '" . $hash . "')" );
            request_response( array(
                "success" => true,
                "reasons" => "User created",
            ), 201 );
        }
        default:
            # code...
            break;
    }
}