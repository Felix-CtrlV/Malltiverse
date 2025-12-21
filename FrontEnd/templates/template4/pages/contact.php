<section class="page-content contact-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="mb-4">Contact Us</h2>
                
                <div class="contact-info mb-4">
                    <?php if (!empty($supplier['email'])): ?>
                        <div class="contact-item mb-3">
                            <h5>Email</h5>
                            <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>"><?= htmlspecialchars($supplier['email']) ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($supplier['phone'])): ?>
                        <div class="contact-item mb-3">
                            <h5>Phone</h5>
                            <p><?= htmlspecialchars($supplier['phone']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($supplier['address'])): ?>
                        <div class="contact-item mb-3">
                            <h5>Address</h5>
                            <p><?= htmlspecialchars($supplier['address']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-form card p-4">
                    <h3 class="mb-4">Send us a Message</h3>
                    <form id="contactForm" method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message:</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

