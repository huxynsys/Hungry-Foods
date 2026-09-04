<?php
// Add this at the VERY TOP of the file
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Hungry Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --primary-light: #FF8B5C;
            --primary-dark: #E55A2B;
            --secondary: #4ECDC4;
            --dark: #292929;
            --light: #F8F9FA;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
            --shadow-hover: 0 15px 40px rgba(0,0,0,0.12);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        body {
            background-color: #ffffff;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Hero Section */
        .about-hero {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(78, 205, 196, 0.1) 100%), 
                       linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.7)), 
                       url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 160px 0 120px;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(transparent, #ffffff);
            z-index: 1;
        }

        .about-hero .container {
            position: relative;
            z-index: 2;
        }

        .about-hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Section Titles */
        .section-title {
            position: relative;
            margin-bottom: 3rem;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        /* Story Section */
        .story-section {
            position: relative;
            overflow: hidden;
        }

        .story-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.03) 0%, rgba(78, 205, 196, 0.03) 100%);
        }

        .story-image {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .story-image:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .story-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .story-image:hover img {
            transform: scale(1.05);
        }

        .milestone-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
            height: 100%;
        }

        .milestone-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .milestone-number {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        /* Values Section */
        .value-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(78, 205, 196, 0.1));
            color: var(--primary);
            font-size: 2rem;
            transition: var(--transition);
        }

        .value-card:hover .value-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(78, 205, 196, 0.2));
        }

        /* Team Section */
        .team-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .team-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(78, 205, 196, 0.1) 0%, transparent 50%);
        }

        .team-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .team-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .team-card:hover .team-img {
            transform: scale(1.05);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border-radius: 50%;
            color: var(--dark);
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }

        .timeline-item {
            display: flex;
            margin-bottom: 3rem;
            position: relative;
        }

        .timeline-item:nth-child(odd) {
            flex-direction: row-reverse;
        }

        .timeline-dot {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            background: var(--primary);
            border: 4px solid white;
            border-radius: 50%;
            z-index: 2;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.2);
        }

        .timeline-content {
            width: 45%;
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--primary), var(--secondary));
            border-radius: 4px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .about-hero {
                padding: 120px 0 80px;
                background-attachment: scroll;
            }
            
            .about-hero h1 {
                font-size: 2.8rem;
            }
            
            .timeline::before {
                left: 30px;
            }
            
            .timeline-item {
                flex-direction: column !important;
                margin-left: 60px;
            }
            
            .timeline-content {
                width: 100%;
            }
            
            .timeline-dot {
                left: 30px;
            }
        }

        /* Animations */
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

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Buttons */
        .btn-primary {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.6);
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-2 fw-bold">Our Story of<br>Culinary Excellence</h1>
                    <p class="lead fs-3 mt-4">Where passion for food meets innovation in delivery</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="story-section py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="story-image">
                        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                             alt="Our Restaurant" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4 section-title">Our Journey</h2>
                    <p class="lead text-muted fs-5 mb-4">Founded in 2023, Hungry Food emerged from a simple but powerful vision: to revolutionize food delivery by combining culinary excellence with technological innovation.</p>
                    <p class="text-muted mb-4">What started as a small team of food enthusiasts in a local kitchen has grown into a city-wide phenomenon, connecting food lovers with the best restaurants in town. Our journey is built on three pillars: quality, speed, and customer satisfaction.</p>
                    <p class="text-muted mb-0">Today, we partner with over 500 restaurants and serve thousands of happy customers daily, but our commitment remains the same - delivering exceptional dining experiences right to your doorstep.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Milestones -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3">Our Milestones</h2>
                    <p class="text-muted fs-5">Celebrating achievements along our journey</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="milestone-card animate-fade-in-up" style="animation-delay: 0.1s">
                        <div class="milestone-number">500+</div>
                        <h5>Restaurant Partners</h5>
                        <p class="text-muted mb-0">Carefully curated culinary partners</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="milestone-card animate-fade-in-up" style="animation-delay: 0.2s">
                        <div class="milestone-number">50K+</div>
                        <h5>Happy Customers</h5>
                        <p class="text-muted mb-0">Served with love and dedication</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="milestone-card animate-fade-in-up" style="animation-delay: 0.3s">
                        <div class="milestone-number">25</div>
                        <h5>Minute Delivery</h5>
                        <p class="text-muted mb-0">Average delivery time</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="milestone-card animate-fade-in-up" style="animation-delay: 0.4s">
                        <div class="milestone-number">24/7</div>
                        <h5>Service</h5>
                        <p class="text-muted mb-0">Always here for you</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3">Our Core Values</h2>
                    <p class="text-muted fs-5">The principles that guide everything we do</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4 class="mb-3">Quality First</h4>
                        <p class="text-muted">Every dish we deliver meets our rigorous quality standards. We partner only with restaurants that share our commitment to excellence.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4 class="mb-3">Lightning Fast</h4>
                        <p class="text-muted">We optimize every step of the delivery process to ensure your food arrives hot, fresh, and on time, every time.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="mb-3">Customer Obsessed</h4>
                        <p class="text-muted">Your satisfaction is our priority. Our support team is available 24/7 to ensure your experience is perfect.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3">Our Timeline</h2>
                    <p class="text-muted fs-5">The journey from a single kitchen to city-wide service</p>
                </div>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4 class="mb-2">2023 - The Beginning</h4>
                        <p class="text-muted mb-0">Launched with 10 restaurant partners and a vision to change food delivery forever.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4 class="mb-2">Mid 2023 - Growth</h4>
                        <p class="text-muted mb-0">Expanded to 100+ restaurant partners and introduced our premium delivery service.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4 class="mb-2">Late 2023 - Innovation</h4>
                        <p class="text-muted mb-0">Launched our mobile app with real-time tracking and introduced catering services.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4 class="mb-2">2024 - Expansion</h4>
                        <p class="text-muted mb-0">Partnered with 500+ restaurants and served our 50,000th customer.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-3">Meet Our Leadership</h2>
                    <p class="text-muted fs-5">The passionate team behind Hungry Food</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="CEO" class="team-img">
                        <div class="p-4">
                            <h4 class="mb-1">Alex Chen</h4>
                            <p class="text-primary mb-3">CEO & Founder</p>
                            <p class="text-muted">Former restaurateur with 15 years of experience in the food industry.</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Operations Director" class="team-img">
                        <div class="p-4">
                            <h4 class="mb-1">Maria Rodriguez</h4>
                            <p class="text-primary mb-3">Operations Director</p>
                            <p class="text-muted">Logistics expert with a passion for creating seamless customer experiences.</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Head Chef" class="team-img">
                        <div class="p-4">
                            <h4 class="mb-1">Sarah Johnson</h4>
                            <p class="text-primary mb-3">Head of Culinary Excellence</p>
                            <p class="text-muted">Award-winning chef dedicated to quality and innovation in food delivery.</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Ready to Experience<br>The Hungry Food Difference?</h2>
                    <p class="mb-4 fs-5 text-muted">Join thousands of satisfied customers who trust us for their food delivery needs.</p>
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                        <a href="menu.php" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-utensils me-2"></i>Order Now
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg px-5">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate elements on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe elements for animation
            document.querySelectorAll('.milestone-card, .value-card, .team-card, .timeline-content').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });

            // Add hover effect to timeline items
            const timelineItems = document.querySelectorAll('.timeline-content');
            timelineItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'var(--shadow)';
                });
            });
        });
    </script>
</body>
</html>