<?php
interface AuthBase
{
    /** Return (bool) whether or not the current user is logged in. */
    public static function is_logged_in();

    /** Logout the current user. */
    public static function logout();

    /** Is called as long as `is_logged_in()` returns `false`, so, e.g.,
     *  - show the login form
     *  - evaluate via $_POST
     *  - redirect to './' on success
     */
    public static function handle_login();
}