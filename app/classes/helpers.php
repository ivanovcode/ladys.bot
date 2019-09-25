<?php

function reroute($url) {
    /*echo '1: '.str_replace("/app", "", substr(dirname(__DIR__), strpos(dirname(__DIR__), $_SERVER['SERVER_NAME']) + strlen($_SERVER['SERVER_NAME']), strlen(dirname(__DIR__)))); echo '<br>';
    echo 'SERVER[SERVER_NAME]: '.$_SERVER['SERVER_NAME']; echo '<br>';
    echo 'SERVER[REQUEST_URI]: '.$_SERVER['REQUEST_URI']; echo '<br>';
    echo 'dirname(__DIR__): '.dirname(__DIR__); echo '<br>';
    echo 'Location: '.str_replace("/app", "", substr(dirname(__DIR__), strpos(dirname(__DIR__), $_SERVER['SERVER_NAME']) + strlen($_SERVER['SERVER_NAME']), strlen(dirname(__DIR__)))).$url;
    echo '<br>';
    echo '<br>';*/
    header('Location: '.str_replace("/app", "", substr(dirname(__DIR__), strpos(dirname(__DIR__), $_SERVER['SERVER_NAME']) + strlen($_SERVER['SERVER_NAME']), strlen(dirname(__DIR__)))).$url);
    exit;
}

function error_page($path, $messages='') {
    $file = $path . '/404.tpl';
    $replace = array('{messages}'); //foreach ($replacers as $key => $replace) {}
    $with = array((is_array($messages)?json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES):''));
    ob_start(); include($file); $ob = ob_get_clean();
    die(str_replace($replace, $with, $ob));
}
function getAuth() {
    $staff = [];
    if(!empty($_SESSION['user'])) {
        $staff = $GLOBALS['db']->query("
                SELECT
                s.id,
                s.login,
                s.password
                FROM
                staffs as s
                WHERE
                s.login = '" . $_SESSION['user'] . "'
                ");

        $staff = $staff->fetch(PDO::FETCH_ASSOC);
    }
    return $staff;
}

function auth() {
    session_start();
    $staff = getAuth();
    if (!isset($_SESSION['user']) || (isset($_SESSION['user'])?(empty($_SESSION['user']) && $_SESSION['user'] !== $staff['login']):false)) reroute('/login');
    return $staff;
}

function firstname($name) {
    $n = explode(' ',trim($name));
    return ((!isset($n[1]) || empty($n))?$n[0]:$n[1]);
}


function random($n) {
    $number = "";
    for($i=0; $i<$n; $i++) {
        $min = ($i == 0) ? 1:0;
        $number .= mt_rand($min,6);
    }
    return $number;
}