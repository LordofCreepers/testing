<?php
function route( $method, $url_data, $form_data ) {
    include_once "connect_db.php";
    include_once "response.php";
    switch ( $method ) {
        case 'GET': {
            $result;
            if ( isset( $form_data[ "title" ] ) ) {
                $result = $mysqli->query( "SELECT * FROM `tests` WHERE `title` = '" . $form_data[ "title" ] . "'" );
                if ( !$result ) {
                    request_error( "Unable to locate resource" );
                    die();
                }
                $result = $result->fetch_all( MYSQLI_ASSOC );
                for ( $row = 0; $row < count( $result ); $row++ ) {
                    $data = json_decode( $result[ $row ][ "content" ], true );
                    for( $question = 0; $question < count( $data ); $question++ ) {
                        unset( $data[ $question ][ "correct_answers" ] );
                    }
                    $result[ $row ][ "content" ] = $data;
                }
            } else {
                $result = $mysqli->query( "SELECT title, description, publishedby_login, overall_rating, times_completed FROM `tests`" );
                $result = $result->fetch_all( MYSQLI_ASSOC );
            }
            request_response( $result );
            break;
        }
        case 'POST': {
            if (
                ( !isset( $_COOKIE[ "login" ] ) || !is_string( $_COOKIE[ "login" ] ) || $_COOKIE[ "login" ] == "" )
            ) {
                request_error( "Logging-in required", 401 );
                die();
            };
            if ( 
                ( !isset( $form_data[ "title" ] ) || !is_string( $form_data[ "title" ] ) || $form_data[ "title" ] == "" ) ||
                ( !isset( $form_data[ "questions" ] ) || count( $form_data[ "questions" ] ) == 0 )
            ) {
                request_error( "Incomplete or malformed data" );
                die();
            };
            $result = $mysqli->query( "SELECT * FROM `tests` WHERE `title`=" . $form_data[ "title" ] );
            if ( $result ) {
                request_error( "Test with this name already exists" );
                die();
            };
            $mysqli->query( "INSERT INTO `tests` (`title`, `description`, `publishedby_login`, `content`, `overall_rating`, `times_completed`) VALUES ('" . 
                $mysqli->real_escape_string( $form_data[ "title" ] ) . "', '" . 
                ( isset( $form_data[ "description" ] ) ? $mysqli->real_escape_string( $form_data[ "description" ] ) : "'Не предоставлено'" ) . "', '" . $_COOKIE[ "login" ] . "', '" . 
                $mysqli->real_escape_string( json_encode( $form_data[ "questions" ] ) ) . "', 0, 0)"
            );
            request_response( array(), 201 );
            break;
        }
        case 'HEAD':
            break;
        default:
            request_error( "No action for provided method", 501 );
            break;
    }
}
?>