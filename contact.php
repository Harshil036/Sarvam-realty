<?php
/**
 * Sarvam Real Estate - General Contact Page
 */
require_once __DIR__ . '/config/db.php';

$page_title = "Contact Us";
$page_description = "Get in touch with Sarvam Real Estate. Send general inquiries, support requests, or visit our office in Mumbai.";

$inquiry_success = false;
$inquiry_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        $inquiry_error = 'All fields are required to submit your contact message.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $inquiry_error = 'Please provide a valid email address.';
    } else {
        // Prepare data to save into inquiries table
        $inquiry_data = [
            'property_id' => null,
            'user_id' => isLoggedIn() ? $_SESSION['user_id'] : null,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message
        ];

        if (submitInquiry($conn, $inquiry_data)) {
            $inquiry_success = true;
        } else {
            $inquiry_error = 'Error sending message. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="container">
    <h1 class="fw-bold text-white mb-2">Contact Us</h1>
    <p style="color:rgba(255,255,255,0.7);">Get in touch with Sarvam Real Estate</p>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,0.4);">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-sarvam">Home</a></li>
        <li class="breadcrumb-item active text-white">Contact Us</li>
      </ol>
    </nav>
  </div>
</section>

<!-- Two column: contact info + form -->
<section class="section-light">
  <div class="container">
    <div class="row g-4">
      <!-- Left: contact info cards with teal icons, white cards -->
      <div class="col-lg-5">
         <div class="card-sarvam p-4 h-100 d-flex flex-column justify-content-between">
            <div class="font-poppins">
                <h4 class="fw-bold text-heading mb-4 section-title d-inline-block">Headquarters Office</h4>
                
                <div class="d-flex mb-4 align-items-start text-muted small mt-4">
                    <i class="bi bi-geo-alt-fill text-sarvam fs-3 me-3 mt-1"></i>
                    <div>
                        <strong class="text-heading d-block mb-1 fs-6">Office Location</strong>
                        123 Business Avenue, Nariman Point, Mumbai, Maharashtra 400021
                    </div>
                </div>
                
                <div class="d-flex mb-4 align-items-start text-muted small">
                    <i class="bi bi-telephone-fill text-sarvam fs-3 me-3 mt-1"></i>
                    <div>
                        <strong class="text-heading d-block mb-1 fs-6">Phone Number</strong>
                        +91 98765 43210
                    </div>
                </div>

                <div class="d-flex mb-4 align-items-start text-muted small">
                    <i class="bi bi-envelope-fill text-sarvam fs-3 me-3 mt-1"></i>
                    <div>
                        <strong class="text-heading d-block mb-1 fs-6">Email Address</strong>
                        info@sarvamrealestate.com
                    </div>
                </div>

                <h5 class="fw-bold text-heading mb-3 mt-4"><i class="bi bi-clock-fill text-sarvam me-2"></i>Business Hours</h5>
                <p class="text-muted small mb-0 font-poppins">
                    <strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM<br>
                    <strong>Saturday:</strong> 10:00 AM - 4:00 PM<br>
                    <strong>Sunday:</strong> Closed
                </p>
            </div>

            <!-- Google Maps Embed Placeholder -->
            <div class="bg-sarvam-ice border-sarvam rounded-sarvam mt-4 overflow-hidden" style="height: 200px;">
                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-center p-3">
                    <small class="text-muted font-poppins"><i class="bi bi-map fs-3 mb-1 d-block text-sarvam"></i>Google Maps Location Embed</small>
                </div>
            </div>
        </div>
      </div>

      <!-- Right: contact form with form-sarvam class, btn-sarvam submit -->
      <div class="col-lg-7">
        <div class="card-sarvam p-4 h-100">
            <h3 class="fw-bold font-poppins text-heading mb-2 section-title d-inline-block">Get in Touch</h3>
            <p class="text-muted small mb-4 mt-3">Have any questions or looking to buy/rent a property? Fill out the form below and our team will get back to you shortly.</p>

            <?php if ($inquiry_success): ?>
                <div class="alert alert-success font-poppins small" style="background:#ECFDF5; border-color:#34D399; color:#065F46;">
                    <i class="bi bi-check-circle-fill me-2"></i>Your contact message has been sent successfully! We will get in touch with you soon.
                </div>
            <?php else: ?>
                <?php if ($inquiry_error): ?>
                    <div class="alert alert-danger font-poppins small mb-3" style="background:#FEF2F2; border-color:#F87171; color:#991B1B;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $inquiry_error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="form-sarvam">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-poppins small fw-medium text-heading">Your Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Rahul Sharma" value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-poppins small fw-medium text-heading">Email Address *</label>
                            <input type="email" name="email" class="form-control" required placeholder="name@example.com" value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-poppins small fw-medium text-heading">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" required placeholder="+91 98765 43210">
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-poppins small fw-medium text-heading">How Can We Help? *</label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="Type your message details here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-sarvam w-100 py-2.5 fw-semibold font-poppins"><i class="bi bi-send-fill me-2"></i>Send Message</button>
                </form>
            <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
