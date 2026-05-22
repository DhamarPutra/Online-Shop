<?php
include 'db/connect.php';
include 'auth/csrf.php';

// cdn library
echo '
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
';

// Mendapatkan URI dari server
$request = $_SERVER['REQUEST_URI'];

// Menghapus parameter query string (jika ada)
$request = strtok($request, '?');

// Routing sederhana
switch ($request) {
    case '/':
        require 'pages/index.php';
        break;

    case '/dashboard':
        require 'pages/admin/dashboard.php';
        break;

    case '/login':
        require 'pages/public/login.php';
        break;

    case '/register':
        require 'pages/public/register.php';
        break;

    case '/logout':
        require 'pages/public/logout.php';
        break;

    case '/profile':
        require 'pages/admin/profile.php';
        break;

    case '/userList':
        require 'pages/admin/userList.php';
        break;

    case '/editUser':
        require 'pages/admin/editUser.php';
        break;

    case '/deleteUser':
        require 'pages/admin/deleteUser.php';
        break;

    case '/addProduct':
        require 'pages/admin/addProduct.php';
        break;

    case '/productList':
        require 'pages/public/productList.php';
        break;

    case '/cart':
        require 'pages/public/cart.php';
        break;

    case '/shop':
        require 'pages/public/shop.php';
        break;

    default:
        require '404.php';
        break;
}
