<?php
// Minimal mysql_* compatibility layer for PHP 7/8 using mysqli.
if (!function_exists('mysql_connect')) {
    $GLOBALS['__MYSQLI_CONN'] = null;

    function __mc_conn($link = null) {
        return $link ?: $GLOBALS['__MYSQLI_CONN'];
    }

    function mysql_connect($host, $user, $pass) {
        $GLOBALS['__MYSQLI_CONN'] = mysqli_connect($host, $user, $pass);
        return $GLOBALS['__MYSQLI_CONN'];
    }

    function mysql_select_db($dbname, $link_identifier = null) {
        return mysqli_select_db(__mc_conn($link_identifier), $dbname);
    }

    function mysql_query($query, $link_identifier = null) {
        return mysqli_query(__mc_conn($link_identifier), $query);
    }

    function mysql_real_escape_string($string, $link_identifier = null) {
        return mysqli_real_escape_string(__mc_conn($link_identifier), $string);
    }

    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }

    function mysql_fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    function mysql_fetch_array($result) {
        return mysqli_fetch_array($result, MYSQLI_BOTH);
    }

    function mysql_insert_id($link_identifier = null) {
        return mysqli_insert_id(__mc_conn($link_identifier));
    }

    function mysql_error($link_identifier = null) {
        return mysqli_error(__mc_conn($link_identifier));
    }

    function mysql_close($link_identifier = null) {
        return mysqli_close(__mc_conn($link_identifier));
    }
}
