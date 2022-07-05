<?php
function hash_captcha( $captcha ) {
    $result = $captcha;
    for ( $i = 0;  $i < 64;  $i++ ) { 
        $result = hash( "sha256", $result );        
    };
    return $result;
}