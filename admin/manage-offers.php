<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Offers | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            width: 25px;
        }
        .main-content {
            padding: 30px;
        }
        .offer-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .offer-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .offer-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-utensils me-2"></i>
                        Admin Panel
                    </h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage-offers.php">
                                <i class="fas fa-tags"></i> Manage Offers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-utensils"></i> Menu Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link" href="../../index.html">
                                <i class="fas fa-home"></i> View Website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../includes/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Manage Offers</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfferModal">
                        <i class="fas fa-plus me-2"></i>Add New Offer
                    </button>
                </div>

                <!-- Offers Grid -->
                <div class="row">
                    <?php
                    // Sample offers data
                    $offers = [
                        [
                            'title' => 'Weekend Special',
                            'description' => 'Get 20% off on all orders above $50',
                            'code' => 'WEEKEND20',
                            'discount' => '20%',
                            'valid_till' => '2023-12-31',
                            'status' => 'active'
                        ],
                        [
                            'title' => 'First Order',
                            'description' => '30% off on your first order',
                            'code' => 'FIRST30',
                            'discount' => '30%',
                            'valid_till' => '2023-12-31',
                            'status' => 'active'
                        ],
                        [
                            'title' => 'Free Delivery',
                            'description' => 'Free delivery on orders above $30',
                            'code' => 'FREEDEL',
                            'discount' => 'Free Delivery',
                            'valid_till' => '2023-11-30',
                            'status' => 'active'
                        ]
                    ];

                    foreach ($offers as $offer): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card offer-card h-100">
                            <div class="card-body">
                                <span class="badge <?php echo $offer['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?> offer-badge">
                                    <?php echo ucfirst($offer['status']); ?>
                                </span>
                                <h5 class="card-title"><?php echo $offer['title']; ?></h5>
                                <p class="card-text text-muted"><?php echo $offer['description']; ?></p>
                                <div class="mb-3">
                                    <small class="text-muted">Coupon Code:</small>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" value="<?php echo $offer['code']; ?>" readonly>
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToClipboard('<?php echo $offer['code']; ?>')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Discount:</small>
                                        <p class="mb-1 fw-bold"><?php echo $offer['discount']; ?></p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">Valid Till:</small>
                                        <p class="mb-1"><?php echo date('M d, Y', strtotime($offer['valid_till'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editOfferModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Offer Modal -->
    <div class="modal fade" id="addOfferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Offer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addOfferForm">
                        <div class="mb-3">
                            <label for="offerTitle" class="form-label">Offer Title</label>
                            <input type="text" class="form-control" id="offerTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="offerDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="offerDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="offerCode" class="form-label">Coupon Code</label>
                            <input type="text" class="form-control" id="offerCode" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="discountType" class="form-label">Discount Type</label>
                                <select class="form-select" id="discountType">
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="delivery">Free Delivery</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="discountValue" class="form-label">Discount Value</label>
                                <input type="text" class="form-control" id="discountValue" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="validTill" class="form-label">Valid Till</label>
                            <input type="date" class="form-control" id="validTill" required>
                        </div>
                        <div class="mb-3">
                            <label for="minOrder" class="form-label">Minimum Order Amount (Optional)</label>
                            <input type="number" class="form-control" id="minOrder" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addOfferForm" class="btn btn-primary">Create Offer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Offer Modal -->
    <div class="modal fade" id="editOfferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Offer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editOfferForm">
                        <div class="mb-3">
                            <label for="editOfferTitle" class="form-label">Offer Title</label>
                            <input type="text" class="form-control" id="editOfferTitle" value="Weekend Special" required>
                        </div>
                        <div class="mb-3">
                            <label for="editOfferDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editOfferDescription" rows="3" required>Get 20% off on all orders above $50</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editOfferCode" class="form-label">Coupon Code</label>
                            <input type="text" class="form-control" id="editOfferCode" value="WEEKEND20" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editDiscountType" class="form-label">Discount Type</label>
                                <select class="form-select" id="editDiscountType">
                                    <option value="percentage" selected>Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="delivery">Free Delivery</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editDiscountValue" class="form-label">Discount Value</label>
                                <input type="text" class="form-control" id="editDiscountValue" value="20%" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editValidTill" class="form-label">Valid Till</label>
                            <input type="date" class="form-control" id="editValidTill" value="2023-12-31" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="editStatus" id="editActive" checked>
                                <label class="form-check-label" for="editActive">Active</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="editStatus" id="editInactive">
                                <label class="form-check-label" for="editInactive">Inactive</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editOfferForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Coupon code copied: ' + text);
            });
        }
    </script>
</body>
</html>