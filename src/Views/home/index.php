<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Canteen - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/pages/index.css">
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
                        <img src="assets/img/CanteenPic.jpeg" alt="Canteen Interior" onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'">
                        <div class="image-caption">
                            <h4>Our Canteen</h4>
                            <p>Clean & spacious dining area</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="assets/img/CanteenFront.jpeg" alt="Canteen Front" onerror="this.src='https://images.unsplash.com/photo-1592861956120-e524fc739696?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'">
                        <div class="image-caption">
                            <h4>Front View</h4>
                            <p>Welcome to your favorite spot</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="assets/img/foodcounter.jpeg" alt="Food Counter" onerror="this.src='https://images.unsplash.com/photo-1592861956120-e524fc739696?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'">
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
                <?php if($menuItems): ?>
                    <?php foreach($menuItems as $item): ?>
                        <div class="menu-item">
                            <div class="menu-item-info">
                                <h3><?php echo e($item['name']); ?></h3>
                                <p>Freshly prepared</p>
                            </div>
                            <div class="menu-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
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