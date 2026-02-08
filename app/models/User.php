<?php

require_once 'app/core/Query.php';

class User
{
    public function MOD_GET_USER(): array
    {
        $users = Query::table('users')->where('id=1')->get();

        return $users;
    }
}
