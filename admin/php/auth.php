<?php
interface AuthBase
{
    /** Return (bool) whether or not the current user is logged in. */
    public static function is_logged_in();

    /** Logout the current user. */
    public static function logout();

    /** Is called as long as `is_logged_in()` returns `false`, so, e.g.,
     *  1. show the login form
     *  2. evaluate via $_POST
     *    - on success `return`
     *    - else back to 1.
     */
    public static function handle_login();
}