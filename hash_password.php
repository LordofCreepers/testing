<?php
function hash_password( $password, $salt = null ) {
    if ( !isset( $salt ) )
        $salt = bin2hex( openssl_random_pseudo_bytes( 15 ) );
    return array( 
        "password" => hash( "sha256", 
            hash( "sha256", 
                hash( "sha256",
                    hash( "sha256", $password . substr( $salt, 0, 10 )
        ) . substr( $salt, 10, 8 ) ) . substr( 18, 6 ) ) . substr( 24, 6 ) ),
        "salt" => $salt
    );
};