<?php
    session_start();

    include "accessformdata.php";

    // Определяем метод запроса
    $method = $_SERVER[ 'REQUEST_METHOD' ];

    // Получаем данные из тела запроса
    $formData = getFormData($method);

    // Разбираем url
    $url = (isset($_GET['q'])) ? $_GET['q'] : '';
    $url = rtrim($url, '/');
    $urls = explode('/', $url);

    // Определяем роутер и url data
    $router = $urls[0];
    $urlData = array_slice($urls, 1);

    // Подключаем файл-роутер и запускаем главную функцию
    if ( file_exists( "api/" . $router . '.php' ) ) {
        include_once "api/" . $router . '.php';
        route( $method, $urlData, $formData );
    }
    elseif ( $router == "" ) {
        include_once "page_default_header.php";
        include_once "pages/index.html";
        include_once "page_default_footer.php";
    }
    else {
        header( "HTTP/1.0 404 Not Found" );
    }
?>