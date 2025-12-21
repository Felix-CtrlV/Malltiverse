<section class="page-content about-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4">About <?= htmlspecialchars($supplier['company_name']) ?></h2>
                
                <?php if (!empty($supplier['description'])): ?>
                    <div class="about-content mb-5">
                        <p class="lead"><?= nl2br(htmlspecialchars($supplier['description'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="company-info card p-4">
                    <h4 class="mb-4">Contact Information</h4>
                    <?php if (!empty($supplier['email'])): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>"><?= htmlspecialchars($supplier['email']) ?></a></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($supplier['phone'])): ?>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($supplier['phone']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($supplier['address'])): ?>
                        <p><strong>Address:</strong> <?= htmlspecialchars($supplier['address']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

