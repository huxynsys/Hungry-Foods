// Main JavaScript file for Hungry Food website

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart from localStorage
    initializeCart();
    
    // Menu filter functionality
    setupMenuFilter();
    
    // Add to cart functionality
    setupAddToCart();
    
    // Form submission handlers
    setupFormSubmissions();
    
    // Smooth scrolling for anchor links
    setupSmoothScrolling();
    
    // Navbar scroll effect
    setupNavbarScroll();
});

function initializeCart() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    updateCartCount(cart.length);
}

function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'flex' : 'none';
    }
}

function setupMenuFilter() {
    const filterButtons = document.querySelectorAll('[data-category]');
    const menuItems = document.querySelectorAll('.menu-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            
            // Filter menu items
            menuItems.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

function setupAddToCart() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart')) {
            e.preventDefault();
            
            const button = e.target;
            const card = button.closest('.card');
            const itemId = button.getAttribute('data-id');
            const itemName = card.querySelector('.card-title').textContent;
            const itemPrice = parseFloat(card.querySelector('.price').textContent.replace('$', ''));
            const itemImage = card.querySelector('img').src;
            
            addToCart({
                id: itemId,
                name: itemName,
                price: itemPrice,
                image: itemImage,
                quantity: 1
            });
            
            // Show feedback
            button.textContent = 'Added!';
            button.classList.add('btn-success');
            button.classList.remove('btn-primary');
            
            setTimeout(() => {
                button.textContent = 'Add to Cart';
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            }, 1500);
        }
    });
}

function addToCart(item) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(cartItem => cartItem.id === item.id);
    
    if (existingItemIndex > -1) {
        // Update quantity
        cart[existingItemIndex].quantity += 1;
    } else {
        // Add new item
        cart.push(item);
    }
    
    // Save to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart count
    updateCartCount(cart.length);
    
    // Show notification
    showNotification(`${item.name} added to cart!`);
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'alert alert-success position-fixed';
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `;
    notification.textContent = message;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function setupFormSubmissions() {
    const contactForm = document.getElementById('contactForm');
    const reservationForm = document.getElementById('reservationForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple validation
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            
            if (!name || !email) {
                alert('Please fill in all required fields');
                return;
            }
            
            // In a real application, you would send this data to a server
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
    }
    
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple validation
            const date = document.getElementById('date').value;
            const guests = document.getElementById('guests').value;
            
            if (!date || !guests) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Check if date is in the past
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Please select a future date');
                return;
            }
            
            // In a real application, you would send this data to a server
            alert('Table reservation submitted successfully! We will confirm shortly.');
            this.reset();
        });
    }
}

function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
}

function setupNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
        
        // Add/remove background on scroll
        if (scrollTop > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.style.background = 'white';
            navbar.style.backdropFilter = 'none';
        }
    });
}

// Animation for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);