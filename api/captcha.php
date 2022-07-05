<?php
function route( $method, $url_data, $form_data ) {
    // define( "CAPTCHA_CHARSET", "абвгдеёжзийклмнопрстуфхцчшщъыьэюя1234567890" );
    define( "CAPTCHA_CHARSET", "abcdefghijklmnopqrstuvwxyz123456789" );
    include_once "response.php";
    include_once "hash_captcha.php";
    switch ( $method ) {
        case 'GET':
            break;
        case 'POST': {
            $sequence = "";
            $len = random_int( 5, 8 );
            for ( $i = 0; $i < $len; $i++ ) { 
                $sequence .= substr( CAPTCHA_CHARSET, random_int( 0, strlen( CAPTCHA_CHARSET ) - 1 ), 1 );
            }
            setcookie( "captcha", hash_captcha( $sequence ), time() + 120 );
            $sz_x = 300;
            $sz_y = 80;
            $img = imagecreate( $sz_x, $sz_y );
            $white = imagecolorallocate( $img, 255, 255, 255 );
            imagefilledrectangle( $img, 0, 0, 239, 179, $white );
            $black = imagecolorallocate( $img, 0, 0, 0 );
            for( $i = 0; $i < strlen( $sequence ); $i++ ) {
                $key = substr( $sequence, $i, 1 );
                imagettftext( $img, random_int( 16, 24 ), random_int( -50, 50 ), $sz_x * 0.1 + $sz_x * 0.8 / $len * $i, 40, $black, $_SERVER[ "DOCUMENT_ROOT" ] . "/resources/fonts/Lobster-Regular.ttf", $key );
            };
            imagejpeg( $img, null, 5 );
            imagedestroy( $img );
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