<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Inquiry | Hungry Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #4ECDC4;
            --dark: #292929;
            --light: #F8F9FA;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding-top: 20px;
        }
        
        .status-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-processing { background: #cce5ff; color: #004085; }
        .badge-responded { background: #d4edda; color: #155724; }
        .badge-resolved { background: #f8f9fa; color: #6c757d; }
        
        .recent-contact {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .recent-contact:hover {
            border-left-color: var(--primary);
            transform: translateX(5px);
        }
        
        .contact-icon-small {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(78, 205, 196, 0.1));
            color: var(--primary);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="status-card">
            <h1 class="text-center mb-4"><i class="fas fa-search text-primary me-2"></i>Track Your Inquiry</h1>
            
            <?php
            $contact_id = $_GET['id'] ?? '';
            $found = false;
            $contact_data = null;
            
            if ($contact_id) {
                // Look in session arrays
                if (isset($_SESSION['contact_by_id'][$contact_id])) {
                    $found = true;
                    $contact_data = $_SESSION['contact_by_id'][$contact_id];
                } else if (isset($_SESSION['user_contacts'])) {
                    foreach ($_SESSION['user_contacts'] as $contact) {
                        if ($contact['id'] == $contact_id) {
                            $found = true;
                            $contact_data = $contact;
                            break;
                        }
                    }
                }
                
                if ($found && $contact_data) {
                    ?>
                    <div class="alert alert-success">
                        <h4><i class="fas fa-check-circle me-2"></i>Inquiry Found!</h4>
                        <p>Here are the details of your inquiry:</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Contact Information</h5>
                            <div class="d-flex align-items-center mb-3">
                                <div class="contact-icon-small me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong>Name:</strong><br>
                                    <?php echo htmlspecialchars($contact_data['name']); ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="contact-icon-small me-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <strong>Phone:</strong><br>
                                    <?php echo $contact_data['phone']; ?>
                                </div>
                            </div>
                            <?php if (!empty($contact_data['email'])): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="contact-icon-small me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <strong>Email:</strong><br>
                                    <?php echo htmlspecialchars($contact_data['email']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5>Inquiry Details</h5>
                            <div class="mb-3">
                                <strong>Reference ID:</strong><br>
                                <code><?php echo $contact_data['id']; ?></code>
                            </div>
                            <div class="mb-3">
                                <strong>Subject:</strong><br>
                                <?php echo htmlspecialchars($contact_data['subject']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Submitted:</strong><br>
                                <?php echo date('M d, Y h:i A', strtotime($contact_data['timestamp'])); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong><br>
                                <span class="status-badge badge-pending">Pending Review</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5><i class="fas fa-comment me-2"></i>Your Message</h5>
                        <div class="card">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($contact_data['message'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>What happens next?</h6>
                        <ul class="mb-0">
                            <li>Our team will review your inquiry within 2 hours</li>
                            <li>We'll contact you at <?php echo $contact_data['phone']; ?></li>
                            <li>You can also chat with us on WhatsApp for immediate assistance</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="contact.php" class="btn btn-outline-primary me-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Contact
                        </a>
                        <a href="https://wa.me/923170544863?text=Inquiry%20Reference:%20<?php echo urlencode($contact_data['id']); ?>%0AName:%20<?php echo urlencode($contact_data['name']); ?>" 
                           target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                        </a>
                    </div>
                    <?php
                } else {
                    // Not found in session, try localStorage via JavaScript
                    ?>
                    <div class="alert alert-warning" id="sessionNotFound">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Inquiry Not Found in Session</h4>
                        <p>We couldn't find an inquiry with the reference ID: <strong><?php echo htmlspecialchars($contact_id); ?></strong></p>
                        <p>This might be because:</p>
                        <ul>
                            <li>You opened the page in a different browser or tab</li>
                            <li>Your browser cookies/session was cleared</li>
                            <li>The session has expired</li>
                        </ul>
                        
                        <div id="localStorageCheck" class="mt-3">
                            <p><i class="fas fa-sync fa-spin me-2"></i>Checking localStorage for saved data...</p>
                        </div>
                    </div>
                    
                    <div id="localStorageResult" style="display: none;"></div>
                    
                    <div class="mt-3">
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Submit New Inquiry
                        </a>
                    </div>
                    <?php
                }
            } else {
                // No ID provided - show recent contacts
                ?>
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-primary mb-3"></i>
                        <h3>Track Your Inquiry</h3>
                        <p class="text-muted">Enter your reference ID or check recent submissions</p>
                    </div>
                    
                    <div id="recentContacts" class="mb-4">
                        <h5 class="mb-3"><i class="fas fa-history me-2"></i>Recent Inquiries</h5>
                        <div id="recentList" class="mb-3"></div>
                    </div>
                    
                    <div class="alert alert-info text-start">
                        <h6><i class="fas fa-lightbulb me-2"></i>How to find your reference ID:</h6>
                        <ul class="mb-0">
                            <li>Check the confirmation message after form submission</li>
                            <li>Look for "Reference ID" in the success notification</li>
                            <li>Check your browser's localStorage (we save it automatically)</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="contact.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Back to Contact Form
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check localStorage for saved contact data
        document.addEventListener('DOMContentLoaded', function() {
            const contactId = '<?php echo $contact_id; ?>';
            
            if (contactId) {
                // Check localStorage for this specific contact
                checkLocalStorage(contactId);
            } else {
                // Show recent contacts from localStorage
                showRecentContacts();
            }
        });
        
        function checkLocalStorage(contactId) {
            const localStorageData = localStorage.getItem('contact_' + contactId);
            
            if (localStorageData) {
                try {
                    const contact = JSON.parse(localStorageData);
                    document.getElementById('localStorageCheck').innerHTML = 
                        '<div class="alert alert-success">' +
                        '<h6><i class="fas fa-check-circle me-2"></i>Found in localStorage!</h6>' +
                        '<p>We found your inquiry data saved in your browser.</p>' +
                        '</div>';
                    
                    document.getElementById('localStorageResult').innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-file-alt me-2"></i>Inquiry Details (from localStorage)</h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${contact.name}</p>
                                    <p><strong>Phone:</strong> ${contact.phone}</p>
                                    <p><strong>Email:</strong> ${contact.email || 'Not provided'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Reference ID:</strong> <code>${contact.contact_id}</code></p>
                                    <p><strong>Subject:</strong> ${contact.subject}</p>
                                    <p><strong>Submitted:</strong> ${new Date(contact.timestamp).toLocaleString()}</p>
                                    <p><strong>Status:</strong> <span class="status-badge badge-pending">Pending</span></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6><i class="fas fa-comment me-2"></i>Your Message:</h6>
                                <div class="card">
                                    <div class="card-body">
                                        ${contact.message}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="contact.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Contact
                                </a>
                                <a href="https://wa.me/923170544863?text=Inquiry%20Reference:%20${encodeURIComponent(contact.contact_id)}%0AName:%20${encodeURIComponent(contact.name)}" 
                                   target="_blank" class="btn btn-success">
                                    <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                                </a>
                            </div>
                        </div>
                    `;
                    document.getElementById('localStorageResult').style.display = 'block';
                    document.getElementById('sessionNotFound').style.display = 'none';
                    
                } catch (e) {
                    console.error('Error parsing localStorage data:', e);
                    document.getElementById('localStorageCheck').innerHTML = 
                        '<div class="alert alert-danger">' +
                        '<h6><i class="fas fa-times-circle me-2"></i>Error loading data</h6>' +
                        '<p>There was an error loading your inquiry data.</p>' +
                        '</div>';
                }
            } else {
                // Also check in recent contacts
                let foundInRecent = false;
                const recentContacts = JSON.parse(localStorage.getItem('recent_contacts') || '[]');
                
                for (const contact of recentContacts) {
                    if (contact.contact_id === contactId) {
                        foundInRecent = true;
                        // Show this contact
                        document.getElementById('localStorageCheck').innerHTML = 
                            '<div class="alert alert-success">' +
                            '<h6><i class="fas fa-check-circle me-2"></i>Found in recent contacts!</h6>' +
                            '<p>We found your inquiry in recent submissions.</p>' +
                            '</div>';
                        
                        document.getElementById('localStorageResult').innerHTML = `
                            <div class="alert alert-success">
                                <h5><i class="fas fa-file-alt me-2"></i>Inquiry Details</h5>
                                <p><strong>Name:</strong> ${contact.name}</p>
                                <p><strong>Phone:</strong> ${contact.phone}</p>
                                <p><strong>Subject:</strong> ${contact.subject}</p>
                                <p><strong>Reference ID:</strong> <code>${contact.contact_id}</code></p>
                                <p><strong>Submitted:</strong> ${new Date(contact.timestamp).toLocaleString()}</p>
                                <div class="mt-4 text-center">
                                    <a href="contact.php" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Contact
                                    </a>
                                    <a href="https://wa.me/923170544863?text=Inquiry%20Reference:%20${encodeURIComponent(contact.contact_id)}%0AName:%20${encodeURIComponent(contact.name)}" 
                                       target="_blank" class="btn btn-success">
                                        <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                                    </a>
                                </div>
                            </div>
                        `;
                        document.getElementById('localStorageResult').style.display = 'block';
                        document.getElementById('sessionNotFound').style.display = 'none';
                        break;
                    }
                }
                
                if (!foundInRecent) {
                    document.getElementById('localStorageCheck').innerHTML = 
                        '<div class="alert alert-warning">' +
                        '<h6><i class="fas fa-exclamation-triangle me-2"></i>Not found in localStorage</h6>' +
                        '<p>We couldn\'t find your inquiry data in browser storage.</p>' +
                        '</div>';
                }
            }
        }
        
        function showRecentContacts() {
            const recentList = document.getElementById('recentList');
            let contacts = [];
            
            // Get recent contacts from localStorage
            const recentContacts = JSON.parse(localStorage.getItem('recent_contacts') || '[]');
            contacts = contacts.concat(recentContacts);
            
            // Also check for individual contact entries
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith('contact_') && key !== 'recent_contacts') {
                    try {
                        const contact = JSON.parse(localStorage.getItem(key));
                        // Check if not already in contacts
                        if (!contacts.some(c => c.contact_id === contact.contact_id)) {
                            contacts.push(contact);
                        }
                    } catch (e) {
                        console.error('Error parsing contact:', e);
                    }
                }
            }
            
            // Sort by timestamp (newest first)
            contacts.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
            
            if (contacts.length > 0) {
                let html = '<div class="list-group">';
                contacts.slice(0, 5).forEach(contact => {
                    const date = new Date(contact.timestamp);
                    const timeAgo = getTimeAgo(date);
                    
                    html += `
                        <a href="contact_status.php?id=${contact.contact_id}" class="list-group-item list-group-item-action recent-contact">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${contact.subject}</h6>
                                    <p class="mb-1">${contact.name} • ${contact.phone}</p>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">${timeAgo}</small><br>
                                    <small><code>${contact.contact_id.substring(0, 10)}...</code></small>
                                </div>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
                recentList.innerHTML = html;
            } else {
                recentList.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent inquiries found.</p>
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Submit Your First Inquiry
                        </a>
                    </div>
                `;
            }
        }
        
        function getTimeAgo(date) {
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;
            if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;
            if (days < 7) return `${days} day${days === 1 ? '' : 's'} ago`;
            return date.toLocaleDateString();
        }
    </script>
</body>
</html>