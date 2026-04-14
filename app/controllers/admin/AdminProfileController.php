<?php
// app/controllers/admin/AdminProfileController.php

class AdminProfileController extends ProfileController {
    public function __construct() {
        $this->requireAdmin();
        $this->layout = 'admin';
        $this->isFrontend = false;
        $this->viewName = 'profile/index'; // Tái sử dụng cùng một file view
        $this->baseRedirect = '/admin/profile';
    }
}
