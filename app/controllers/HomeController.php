<?php

require_once 'app/core/Controller.php';
require_once 'app/core/Database.php';

class HomeController extends Controller
{
    public function index()
    {
        $userModel = $this->model('User');

        $user = $userModel->MOD_GET_USER();

        $data = [
            'title' => 'Home',
            'id'  => $user['id'],
            'name'  => $user['name']
        ];

        $this->view('home/index', $data);
    }
}
