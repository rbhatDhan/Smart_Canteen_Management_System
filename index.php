<?php
session_start();
include 'db_connection.php';

$today = date('l');
$menuItems = $conn->query("SELECT name, price FROM menu WHERE menu_day = '$today' LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Canteen - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #14b8a6;
            --primary-soft: #ccfbf1;
            --dark: #115e59;
            --darker: #134e4a;
            --light: #f0fdfa;
            --gray: #64748b;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            line-height: 1.6;
            overflow-x: hidden;
            background: white;
            position: relative;
            min-height: 100vh;
        }

        /* Random Rectangle Background - More Visible */
        .rectangle-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .rect {
            position: absolute;
            background: var(--primary);
            opacity: 0.10; /* Increased from 0.05 to 0.15 for better visibility */
            border-radius: 30px;
            box-shadow: 0 0 30px rgba(13,148,136,0.1);
        }

        .rect-1 {
            top: 10%;
            left: 5%;
            width: 350px; /* Slightly larger */
            height: 180px;
            transform: rotate(15deg);
            animation: floatRect1 22s ease-in-out infinite;
        }

        .rect-2 {
            top: 60%;
            right: 8%;
            width: 450px;
            height: 220px;
            transform: rotate(-10deg);
            animation: floatRect2 26s ease-in-out infinite;
        }

        .rect-3 {
            bottom: 15%;
            left: 12%;
            width: 300px;
            height: 400px;
            transform: rotate(20deg);
            animation: floatRect3 24s ease-in-out infinite;
        }

        .rect-4 {
            top: 25%;
            right: 15%;
            width: 400px;
            height: 200px;
            transform: rotate(-15deg);
            animation: floatRect4 28s ease-in-out infinite;
        }

        .rect-5 {
            bottom: 35%;
            left: 35%;
            width: 250px;
            height: 320px;
            transform: rotate(35deg);
            animation: floatRect5 25s ease-in-out infinite 1s;
        }

        .rect-6 {
            top: 70%;
            right: 25%;
            width: 380px;
            height: 190px;
            transform: rotate(-25deg);
            animation: floatRect6 27s ease-in-out infinite 0.5s;
        }

        .rect-7 {
            top: 12%;
            left: 25%;
            width: 320px;
            height: 250px;
            transform: rotate(8deg);
            animation: floatRect7 24s ease-in-out infinite 1.5s;
        }

        .rect-8 {
            bottom: 10%;
            right: 35%;
            width: 420px;
            height: 210px;
            transform: rotate(-12deg);
            animation: floatRect8 29s ease-in-out infinite 2s;
        }

        @keyframes floatRect1 {
            0%, 100% { transform: rotate(15deg) translate(0, 0); }
            50% { transform: rotate(15deg) translate(45px, -35px); }
        }

        @keyframes floatRect2 {
            0%, 100% { transform: rotate(-10deg) translate(0, 0); }
            50% { transform: rotate(-10deg) translate(-40px, 45px); }
        }

        @keyframes floatRect3 {
            0%, 100% { transform: rotate(20deg) translate(0, 0) scale(1); }
            50% { transform: rotate(20deg) translate(35px, 35px) scale(1.08); }
        }

        @keyframes floatRect4 {
            0%, 100% { transform: rotate(-15deg) translate(0, 0); }
            50% { transform: rotate(-15deg) translate(-45px, -25px); }
        }

        @keyframes floatRect5 {
            0%, 100% { transform: rotate(35deg) translate(0, 0); }
            50% { transform: rotate(35deg) translate(25px, -45px); }
        }

        @keyframes floatRect6 {
            0%, 100% { transform: rotate(-25deg) translate(0, 0); }
            50% { transform: rotate(-25deg) translate(-30px, 35px); }
        }

        @keyframes floatRect7 {
            0%, 100% { transform: rotate(8deg) translate(0, 0); }
            50% { transform: rotate(8deg) translate(35px, -25px); }
        }

        @keyframes floatRect8 {
            0%, 100% { transform: rotate(-12deg) translate(0, 0); }
            50% { transform: rotate(-12deg) translate(-45px, 25px); }
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            z-index: 1000;
            padding: 1rem 0;
        }

        .navbar .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .logo i {
            color: var(--primary);
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            font-size: 1.1rem;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .btn-login {
            background: var(--primary);
            color: white !important;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s !important;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
        }

        .btn-login::after {
            display: none;
        }

        /* Hero Section - Teal Box */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 6rem 0 4rem;
            position: relative;
            z-index: 1;
        }

        .hero-box {
            max-width: 1280px;
            margin: 0 auto;
            padding: 3rem;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            border-radius: 40px;
            box-shadow: 0 30px 50px -20px rgba(13,148,136,0.5);
            position: relative;
            z-index: 2;
        }

        .hero .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 3;
        }

        .hero-content {
            animation: fadeInLeft 1s ease;
            color: white;
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: white;
        }

        .hero-content h1 span {
            color: #ffd700;
            position: relative;
            display: inline-block;
        }

        .hero-content h1 span::before {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 30%;
            background: rgba(255,255,255,0.2);
            z-index: -1;
            border-radius: 10px;
        }

        .hero-content p {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            background: #f0fdfa;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
        }

        /* Image Gallery inside Teal Box */
        .image-gallery {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            animation: fadeInRight 1s ease;
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .gallery-item {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.3);
            transition: all 0.5s;
            height: 250px;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .gallery-item.large {
            grid-column: span 2;
            height: 300px;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
            pointer-events: none;
        }

        .image-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            color: white;
            z-index: 2;
            transform: translateY(100%);
            transition: transform 0.3s;
            background: linear-gradient(to top, rgba(13,148,136,0.9), transparent);
        }

        .gallery-item:hover .image-caption {
            transform: translateY(0);
        }

        .image-caption h4 {
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
        }

        .image-caption p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Role Selection Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 3rem;
            border-radius: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 30px 50px -20px rgba(0,0,0,0.3);
            animation: modalPop 0.3s ease;
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-content h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .modal-content p {
            color: var(--gray);
            margin-bottom: 2rem;
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .role-card {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 20px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .role-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.1);
        }

        .role-card i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .role-card h3 {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .role-card p {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .close-modal {
            background: var(--gray);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .close-modal:hover {
            background: var(--dark);
        }

        /* Menu Section */
        .menu-section {
            padding: 5rem 0;
            background: transparent;
            position: relative;
            z-index: 1;
        }

        .menu-section .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: inline-block;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .section-title p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .menu-item {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.4s;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 20px -10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .menu-item:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: var(--primary);
            box-shadow: 0 20px 30px -10px rgba(13,148,136,0.15);
        }

        .menu-item:hover::before {
            opacity: 1;
        }

        .menu-item-info h3 {
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .menu-item-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .menu-item-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            position: relative;
        }

        .view-all {
            text-align: center;
        }

        .view-all .btn {
            padding: 1rem 3rem;
            font-size: 1.1rem;
        }

        /* Contact Section */
        .contact-section {
            padding: 5rem 0;
            background: transparent;
            position: relative;
            z-index: 1;
        }

        .contact-section .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }

        .contact-info {
            background: white;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .contact-info:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 25px 35px -10px rgba(13,148,136,0.1);
        }

        .contact-info h3 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .contact-info h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .contact-detail {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            color: var(--gray);
            transition: all 0.3s;
            padding: 0.5rem;
            border-radius: 10px;
        }

        .contact-detail:hover {
            background: var(--primary-soft);
            transform: translateX(5px);
        }

        .contact-detail i {
            color: var(--primary);
            width: 2rem;
            height: 2rem;
            background: var(--primary-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .contact-detail:hover i {
            background: var(--primary);
            color: white;
        }

        .contact-form {
            background: white;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .contact-form:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 25px 35px -10px rgba(13,148,136,0.1);
        }

        .contact-form h3 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .contact-form h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .contact-form textarea {
            border-radius: 30px;
            resize: none;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 5px rgba(13,148,136,0.1);
        }

        .contact-form button {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            margin-top: 2rem;
            z-index: 1;
        }

        .footer .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-logo i {
            color: var(--primary);
        }

        .footer-about p {
            opacity: 0.8;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px) rotate(360deg);
        }

        .footer-links h4 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-links h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--primary);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .image-gallery {
                margin-top: 2rem;
            }
            
            .hero-box {
                margin: 0 1rem;
                padding: 2rem;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .role-options {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .image-gallery {
                grid-template-columns: 1fr;
            }
            
            .gallery-item.large {
                grid-column: span 1;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-box {
                padding: 1.5rem;
                border-radius: 30px;
            }
            
            .modal-content {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Random Rectangle Background - More Visible -->
    <div class="rectangle-bg">
        <div class="rect rect-1"></div>
        <div class="rect rect-2"></div>
        <div class="rect rect-3"></div>
        <div class="rect rect-4"></div>
        <div class="rect rect-5"></div>
        <div class="rect rect-6"></div>
        <div class="rect rect-7"></div>
        <div class="rect rect-8"></div>
    </div>

    <!-- Role Selection Modal -->
    <div class="modal" id="roleModal">
        <div class="modal-content">
            <h2>Welcome to SmartCanteen! 👋</h2>
            <p>Please select how you want to continue</p>
            <div class="role-options">
                <a href="student_login.php" class="role-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Student</h3>
                    <p>Order food, track meals, view history</p>
                </a>
                <a href="admin_login.php" class="role-card">
                    <i class="fas fa-user-tie"></i>
                    <h3>Admin</h3>
                    <p>Manage orders, update menu, view stats</p>
                </a>
            </div>
            <button class="close-modal" onclick="closeModal()">Cancel</button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <span>SMART CANTEEN</span>
            </div>
            <ul class="nav-links">
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="#menu">Menu</a></li>
                <li><a href="#contact">Contact</a></li>
                <?php if(isset($_SESSION['username'])): ?>
                    <li><a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="btn-login">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="javascript:void(0);" onclick="openModal()" class="btn-login">Login</a></li>
                    <li><a href="javascript:void(0);" onclick="openModal()" class="btn-login">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section with Teal Box -->
    <section id="home" class="hero">
        <div class="hero-box">
            <div class="container">
                <div class="hero-content">
                    <h1>Welcome To <span>SmartCanteen</span></h1>
                    <p>Experience the future of campus dining. Order food, track meals, and enjoy seamless service all in one place.</p>
                    <div class="hero-buttons">
                        <?php if(!isset($_SESSION['username'])): ?>
                            <a href="javascript:void(0);" onclick="openModal()" class="btn btn-primary">Get Started <i class="fas fa-arrow-right"></i></a>
                            <a href="#menu" class="btn btn-outline">View Menu <i class="fas fa-utensils"></i></a>
                        <?php else: ?>
                            <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="btn btn-primary">Go to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Image Gallery -->
                <div class="image-gallery">
                    <div class="gallery-item large">
                        <img src="canteenpic.jpeg" alt="Canteen Interior" onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'">
                        <div class="image-caption">
                            <h4>Our Canteen</h4>
                            <p>Clean & spacious dining area</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="CanteenFront.jpeg" alt="Canteen Front" onerror="this.src='https://images.unsplash.com/photo-1592861956120-e524fc739696?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'">
                        <div class="image-caption">
                            <h4>Front View</h4>
                            <p>Welcome to your favorite spot</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="foodcounter.jpeg" alt="Food Counter" onerror="this.src='https://images.unsplash.com/photo-1592861956120-e524fc739696?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'">
                        <div class="image-caption">
                            <h4>Fresh Food</h4>
                            <p>Always hygienic & tasty</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="menu-section">
        <div class="container">
            <div class="section-title">
                <h2>Today's Special Menu</h2>
                <p>Check out what's cooking today</p>
            </div>
            <div class="menu-grid">
                <?php if($menuItems && $menuItems->num_rows > 0): ?>
                    <?php while($item = $menuItems->fetch_assoc()): ?>
                        <div class="menu-item">
                            <div class="menu-item-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Freshly prepared</p>
                            </div>
                            <div class="menu-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="menu-item">
                        <div class="menu-item-info">
                            <h3>Ghee Rice</h3>
                            <p>Freshly prepared</p>
                        </div>
                        <div class="menu-item-price">₹45</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-info">
                            <h3>Masala Dosa</h3>
                            <p>Crispy & delicious</p>
                        </div>
                        <div class="menu-item-price">₹65</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-info">
                            <h3>Veg Biryani</h3>
                            <p>Fragrant rice</p>
                        </div>
                        <div class="menu-item-price">₹120</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-info">
                            <h3>Cold Coffee</h3>
                            <p>Refreshing drink</p>
                        </div>
                        <div class="menu-item-price">₹45</div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="view-all">
                <a href="view_menu.php" class="btn btn-primary">View Full Menu <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Get in touch with us</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Visit Our Canteen</h3>
                    <div class="contact-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>GSSSIETW Campus, Metagalli</span>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-phone"></i>
                        <span>+91 XXXXXXXXXX</span>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-envelope"></i>
                        <span>canteen@university.edu</span>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-clock"></i>
                        <span>Mon-Sat: 9:00 AM - 6:00 PM</span>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Send a Message</h3>
                    <form>
                        <div class="form-group">
                            <input type="text" placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Your Email">
                        </div>
                        <div class="form-group">
                            <textarea rows="4" placeholder="Your Message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-logo">
                        <i class="fas fa-utensils"></i>
                        <span>SMART CANTEEN</span>
                    </div>
                    <p>Your one-stop solution for campus dining. Fresh food, fast service, happy students.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="#menu"><i class="fas fa-chevron-right"></i> Menu</a></li>
                        <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Terms</a></li>
                    </ul>
                </div>
            <!--</div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Smart Canteen. All rights reserved.</p>
            </div>
        </div>-->
    </footer>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('roleModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('roleModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('roleModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    closeModal();
                }
            });
        });

        // Active link highlighting
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (window.scrollY >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>