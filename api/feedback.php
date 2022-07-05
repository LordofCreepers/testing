<?php
function route( $method, $url_data, $form_data ) {
    include_once "connect_db.php";
    include_once "response.php";
    switch ( $method ) {
        case 'GET': {
            $q = "SELECT * FROM `feedback`";
            if ( isset( $form_data[ "test" ] ) || isset( $form_data[ "user" ] ) ) $q .= " WHERE ";
            if ( isset( $form_data[ "test" ] ) ) $q .= "`test_title`='" . $mysqli->real_escape_string( $form_data[ "test" ] ) . "' ";
            if ( isset( $form_data[ "user" ] ) ) $q .= ( substr( $q, -2 ) == "' " ? "AND " : "" ) . "`completed_by`='" . $mysqli->real_escape_string( $form_data[ "user" ] ) . "' ";
            $result = $mysqli->query( $q );
            $result = $result->fetch_all( MYSQLI_ASSOC );
            request_response( $result );
            break;
        }
        case 'POST': {
            if (
                ( !isset( $_COOKIE[ "login" ] ) || $_COOKIE[ "login" ] == "" ) ||
                ( !isset( $form_data[ "title" ] ) || !is_string( $form_data[ "title" ] ) || $form_data[ "title" ] == "" ) ||
                ( !isset( $form_data[ "answers" ] ) || count( $form_data[ "answers" ] ) == 0 ) ||
                ( !isset( $form_data[ "comment" ] ) || !is_string( $form_data[ "comment" ] ) ) ||
                ( !isset( $form_data[ "mark" ] ) || (int)( $form_data[ "mark" ] ) < 1 || (int)( $form_data[ "mark" ] ) > 5 )
            ) {
                request_error( "Incomplete or malformed data" );
                die();
            };
            $result = $mysqli->query( "SELECT * FROM `tests` WHERE `title`='" . $form_data[ "title" ] . "'" );
            if ( !$result ) {
                request_error( "Feedback for test that doesn't exist" );
            };
            $result = $mysqli->query( "SELECT * FROM `feedback` WHERE `test_title`='" . $form_data[ "title" ] . "' AND `completed_by`='" . ( $_COOKIE[ "login" ] ? $_COOKIE[ "login" ] : "Гость" ) . "'" );
            if ( $result && count( $result->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {
                request_error( "Feedback had already been left" );
                die();
            };
            $q = "INSERT INTO `feedback` (`test_title`, `completed_by`, `answers`, `rating`, `comment`) VALUES ('" 
                . $mysqli->real_escape_string( $form_data[ "title" ] ) . "', '" 
                . ( $_COOKIE[ "login" ] ? $_COOKIE[ "login"] : "Гость" ) . "', '"
                . $mysqli->real_escape_string( json_encode( $form_data[ "answers" ] ) ) . "', "
                . ( (int)$form_data[ "mark" ] ) . ", '"
                . $mysqli->real_escape_string( $form_data[ "comment" ] ) . 
            "')";
            $mysqli->query( $q );
            $mysqli->query( "UPDATE `tests` SET `overall_rating` = (SELECT AVG(`rating`) FROM `feedback` WHERE `test_title`='" . $form_data[ "title" ] . "'), `times_completed`=`times_completed`+1 WHERE `title`='" . $form_data[ "title" ] . "'" );
            request_response( "Success", 201 );
            break;
        }
        case 'PATCH': {
            if ( 
                ( !isset( $_COOKIE[ "login" ] ) || $_COOKIE[ "login" ] == "" ) ||
                ( !isset( $form_data[ "title" ] ) || !is_string( $form_data[ "title" ] ) || $form_data[ "title" ] == "" )
            ) {
                request_error( "Incomplete or malformed data" );
                die();
            };
            $result = $mysqli->query( "SELECT * FROM `feedback` WHERE `test_title`='" . $form_data[ "title" ] . "' AND `completed_by`='" . $_COOKIE[ "login" ] . "'" );
            if ( !$result ) {
                request_error( "User doesn't exist or haven't left a feedback for this test" );
                die();
            };
            $q = "";
            if ( isset( $form_data[ "answers" ] ) && count( $form_data[ "answers" ] ) > 0 ) {
                $q .= "`answers`='" . $mysqli->real_escape_string( json_encode( $form_data[ "answers" ] ) ) . "', ";
            };
            if ( isset( $form_data[ "mark" ] ) && (int)$form_data[ "mark" ] >= 1 && (int)$form_data[ "mark" ] <= 5 ) {
                $q .= "`rating`='" . (int)$form_data[ "mark" ] . "', ";
            };
            if ( isset( $form_data[ "comment" ] ) && is_string( $form_data[ "comment" ] ) ) {
                $q .= "`comment`='" . $mysqli->real_escape_string( $form_data[ "comment" ] ) . "', ";
            };
            if ( $q == "" ) {
                request_error( "No changes to be applied" );
                die();
            };
            $q = substr( $q, 0, -2 ) . " ";
            $full_q = "UPDATE `feedback` SET " . $q . "WHERE `test_title`='" 
                . $mysqli->real_escape_string( $form_data[ "title" ] ) . "' AND `completed_by`='"
                . $mysqli->real_escape_string( $_COOKIE[ "login" ] ) . "'";
            $result = $mysqli->query( $full_q );
            $mysqli->query( "UPDATE `tests` SET `overall_rating` = (SELECT AVG(`rating`) FROM `feedback` WHERE `test_title`='" . $mysqli->real_escape_string( $form_data[ "title" ] ) . "')" );
            request_response( "Success", 201 );
            break;
        }
        default:
            # code...
            break;
    }
}