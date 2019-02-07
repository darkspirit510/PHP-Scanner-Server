<?php

class Request {

    public static function getPage() {
        $page = Get_Values('page');

        if ($page == NULL) {
            $page = Config::get()->getHomepage();
        }

        if ($RequireLogin && !$Auth) {
            $page == 'Login';
        }

        return $page;
    }

    public static function getHomepage() {

    }
}

?>