<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coco Chutney - Authentic Indian Flavors</title>
    <link rel="shortcut icon" href="https://cocochutney.com/favicon.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="index.css" />
</head>

<body>
    <div id="nav">
        <img src="logo.png" alt="Coco Chutney Logo" />
        <div class="nav-links">
            <h4><a href="index.html" class="active">Home</a></h4>
            <h4><a href="menu.html">Menu</a></h4>
            <h4><a href="aboutus.html">About Us</a></h4>
            <h4><a href="reservation.html">Reservations</a></h4>
            <h4><a href="contact.html">Contact</a></h4>
        </div>
        <div id="hamburger-menu">
            <i class="ri-menu-3-line" id="open-menu"></i>
        </div>
    </div>

    <div id="mobile-nav">
        <i class="ri-close-line" id="close-menu"></i>
        <ul>
            <li><a href="index.html" class="active-link">Home</a></li>
            <li><a href="menu.html">Menu</a></li>
            <li><a href="aboutus.html">About Us</a></li>
            <li><a href="reservation.html">Reservations</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
    </div>

    <div id="cursor"></div>
    <div id="cursor-blur"></div>

    <video autoplay loop muted src="d2e91d65-90a0-4766-8059-2914891b6742.mp4"></video>

    <div id="main">
        <div id="page1">
            <div id="scroller1">
            <div id="scroller1-in">
                <h4>AUTHENTIC FLAVORS</h4>
                <h4>TRADITIONAL RECIPES</h4>
                <h4>FRESH INGREDIENTS</h4>
                <h4>CULINARY EXCELLENCE</h4>
                <h4>MEMORABLE EXPERIENCE</h4>
            </div>
        </div>

            <div class="page1-content">
                 <h1>COCO CHUTNEY</h1>
                 <h2>FLAVOR. SPICE. LOVE.</h2>
                 <p>
                     Experience the vibrant and rich tastes of authentic Indian cuisine. Made with passion, served with warmth.
                 </p>
                 <a href="#page2" class="scroll-down-arrow" aria-label="Scroll down to learn more">
                     <i class="ri-arrow-down-line"></i>
                 </a>
            </div>
        </div>

        <div id="scroller">
            <div id="scroller-in">
                <h4>AUTHENTIC FLAVORS</h4>
                <h4>TRADITIONAL RECIPES</h4>
                <h4>FRESH INGREDIENTS</h4>
                <h4>CULINARY EXCELLENCE</h4>
                <h4>MEMORABLE EXPERIENCE</h4>
            </div>
        </div>

        <div id="page2">
            <div id="about-us">
                <div class="about-us-image">
                    <img src="rava idli.png" alt="Delicious Masala Dosa" />
                </div>
                <div id="about-us-in">
                    <h3>ABOUT US</h3>
                    <p>
                        Coco Chutney is more than just a restaurant; it's a culinary journey through the diverse regions of India. We are dedicated to bringing you traditional recipes, prepared with the freshest ingredients and served with genuine hospitality. Our passion for authentic Indian food is in every dish we create.
                    </p>
                     <a href="aboutus.html" class="btn">Learn More</a>
                </div>
                 <div class="about-us-image">
                    <img src="myssore pak.png" alt="Variety of Indian Dishes" />
                </div>
            </div>
        </div>

        <div id="green-div">
             <div class="green-div-content">
                 <h4>
                     STAY UPDATED! SIGN UP FOR EXCLUSIVE OFFERS AND NEWS FROM COCO CHUTNEY.
                 </h4>
                 <div class="newsletter-form">
                     <input type="email" placeholder="Enter your email address">
                     <button class="btn">Subscribe</button>
                 </div>
             </div>
        </div>
<div class="particles" id="particles"></div>
        <div id="page3">
             <div class="testimonial-container">
                 <p>
                     Coco Chutney is an absolute gem! The food is incredibly flavorful, and the atmosphere is so welcoming. Every visit feels like a celebration of taste and culture. A must-try for any Indian food lover!
                 </p>
                 <img id="colon1" src="double-quotes-left.png" alt="Quote Mark" /> <img id="colon2" src="double-quotes-right.png" alt="Quote Mark" /> </div>
        </div>

        <div id="page4">
            <h1>EXPLORE OUR MENU HIGHLIGHTS</h1>
            <div class="elem-container">
                <div class="elem">
                    <img src="rava idli.png" alt="Rava Idli" />
                    <h2>APPETIZERS</h2>
                    <p>Start your meal with our tantalizing selection of traditional Indian appetizers.</p>
                     <a href="menu.html#appetizers" class="btn-small">View Appetizers</a>
                </div>
                <div class="elem">
                    <img src="mix veg uttapam.png" alt="Mix Veg Uttapam" />
                    <h2>MAIN COURSES</h2>
                    <p>Indulge in our rich and aromatic main courses, a true taste of India.</p>
                     <a href="menu.html#main-courses" class="btn-small">View Main Courses</a>
                </div>
                <div class="elem">
                    <img src="rava kesari.png" alt="Rava Kesari" />
                    <h2>DESSERTS</h2>
                    <p>End your culinary journey on a sweet note with our delightful Indian desserts.</p>
                     <a href="menu.html#desserts" class="btn-small">View Desserts</a>
                </div>
            </div>
        </div>

        <footer id="footer">
            <div class="footer-links">
                <div class="footer-column">
                    <h3>Menu</h3>
                    <a href="menu.html#appetizers">Appetizers</a>
                    <a href="menu.html#main-courses">Main Courses</a>
                    <a href="menu.html#desserts">Desserts</a>
                    <a href="menu.html#beverages">Beverages</a>
                </div>
                <div class="footer-column">
                    <h3>Explore</h3>
                    <a href="reservation.html">Reservations</a>
                    <a href="contact.html">Contact Us</a>
                    <a href="aboutus.html">About Us</a>
                </div>
                <div class="footer-column">
                    <h3>Visit Us</h3>
                    <p>
                        Satya The Hive,<br />
                        Dwarka ExpressWay, Sector 102,<br />
                        Gurgaon, Haryana<br />
                        <a href="mailto:info@cocochutney.com">info@cocochutney.com</a>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Coco Chutney. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.1/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.1/ScrollTrigger.min.js"></script>
    <script src="index.js"></script>
</body>
</html>
