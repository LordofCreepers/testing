<?php
$mysqli = new mysqli('testing', 
    'root', 
    'root', 
    'supertests' 
);
if ($mysqli->connect_error) {
    die( 'Connect error ('.$mysqli->connect_errno.') '.$mysqli->connect_error );
};
?>