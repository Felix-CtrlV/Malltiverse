<?php
// ... (Database connection code assumed above or included)

// 1. Handle Insertion if POST (Ensure this only runs on POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO reviews (supplier_id, customer_id, review, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    // Note: Make sure variables $supplier_id, $customer_id, $review_text, $rating are defined from $_POST input before this
    $stmt->bind_param("iisi", $supplier_id, $customer_id, $review, $rating);
    $stmt->execute();
    $stmt->close();
}

// 2. Fetch Stats
$sql_stats = "SELECT rating FROM reviews";
$result_stats = $conn->query($sql_stats);

$total_reviews = 0;
$total_stars = 0;
$star_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

if ($result_stats->num_rows > 0) {
    while ($row = $result_stats->fetch_assoc()) {
        $r = $row['rating'];

        // FIX: Check if $r is not null before using it as an array key
        if ($r !== null && isset($star_counts[$r])) {
            $star_counts[$r]++;
            // Only add to totals if the rating is valid
            $total_stars += $r;
            $total_reviews++;
        }
    }
}

$average_rating = $total_reviews > 0 ? round($total_stars / $total_reviews, 1) : 0;

// 3. Fetch Recent Reviews
$sql_reviews = "
    SELECT r.review, r.rating, r.created_at, c.name, c.image 
    FROM reviews r 
    JOIN customers c ON r.customer_id = c.customer_id 
    ORDER BY r.created_at DESC LIMIT 5";
$result_reviews = $conn->query($sql_reviews);
?>

<style>
    /* --- CSS VARIABLES FOR COLORS --- */
    :root {
        /* Primary: Gold (Active Star), Secondary: Light Grey (Inactive Star) */
        --star-primary: #c5a47e;
        --star-secondary: #e0e0e0;
        --bg-white: #ffffff;
        --text-dark: #333333;
    }

    /* --- PAGE OVERRIDES --- */
    /* Force specific styles for the Review Page to look like the reference image */

    /* 1. Ensure the Navigation Bar is visible against white content when scrolling */
    nav.home {
        background-color: #000 !important;
        /* Force black background for header */
        border-bottom: 1px solid #333;
    }

    /* 2. Main Container - White Background */
    .review-page-container {
        background-color: var(--bg-white);
        color: var(--text-dark);
        min-height: 100vh;
        padding: 120px 10% 80px;
        /* Top padding to account for fixed header */
        font-family: 'Roboto', sans-serif;
        /* Clean font for reviews */
    }

    /* --- STATS SECTION --- */
    .review-stats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 50px;
        background: #f9f9f9;
        /* Very light grey for contrast against white */
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 60px;
    }

    .rating-bars {
        flex: 2;
        min-width: 300px;
    }

    .bar-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 12px;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .star-label {
        width: 50px;
        color: #555;
    }

    .progress-track {
        flex-grow: 1;
        height: 8px;
        background: var(--star-secondary);
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: var(--star-primary);
        border-radius: 10px;
    }

    .percentage-label {
        width: 50px;
        text-align: right;
        color: #777;
        font-size: 0.9rem;
    }

    .count-label {
        width: 40px;
        text-align: right;
        color: #999;
        font-size: 0.8rem;
    }

    .overall-rating {
        flex: 1;
        min-width: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .big-score {
        font-size: 4rem;
        font-weight: 800;
        color: var(--star-primary);
        line-height: 1;
    }

    .stars-display {
        color: var(--star-primary);
        font-size: 1.5rem;
        margin: 10px 0;
    }

    /* --- CONTENT GRID --- */
    .content-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 60px;
    }

    h2.section-title {
        font-size: 1.8rem;
        margin-bottom: 25px;
        color: #000;
        font-weight: 700;
        font-family: 'Roboto', sans-serif;
    }

    /* --- FEEDBACK LIST --- */
    .review-card {
        background: #fff;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        border: 1px solid #eee;
        transition: transform 0.3s;
    }

    .review-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .reviewer-header {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        background: #ddd;
    }

    .reviewer-info h4 {
        margin: 0;
        font-size: 1.1rem;
        color: #000;
        font-weight: 600;
    }

    .review-date {
        font-size: 0.8rem;
        color: #999;
        display: block;
        margin-top: 2px;
    }

    .card-stars {
        color: var(--star-primary);
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .review-text {
        color: #555;
        line-height: 1.6;
    }

    /* --- REVIEW FORM --- */
    .review-form-container {
        background: #fff;
        padding: 35px;
        border-radius: 20px;
        height: fit-content;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid #eee;
    }

    /* INTERACTIVE STARS */
    .star-rating-widget {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .star-item {
        font-size: 2rem;
        color: var(--star-secondary);
        transition: color 0.2s;
    }

    /* Class added by JS when active */
    .star-item.active {
        color: var(--star-primary);
    }

    .input-group {
        margin-bottom: 20px;
    }

    .input-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }

    .input-group input,
    .input-group textarea {
        width: 100%;
        padding: 14px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        color: #333;
        border-radius: 8px;
        outline: none;
        font-family: inherit;
        transition: 0.3s;
    }

    .input-group input:focus,
    .input-group textarea:focus {
        border-color: var(--star-primary);
        background: #fff;
    }

    .submit-btn {
        width: 100%;
        padding: 15px;
        background: var(--star-primary);
        border: none;
        color: white;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        transition: 0.3s;
        box-shadow: 0 4px 10px rgba(197, 164, 126, 0.4);
    }

    .submit-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
    }

    /* Footer Adjustment */
    footer.spline-footer {
        background-color: #000;
        /* Ensure footer stays black */
        color: white;
    }

    @media (max-width: 900px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="review-page-container">

    <div class="review-stats-container">
        <div class="rating-bars">
            <?php
            $labels = ['FIVE', 'FOUR', 'THREE', 'TWO', 'ONE'];
            for ($i = 5; $i >= 1; $i--) {
                $count = $star_counts[$i];
                $percent = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                $formatted_percent = number_format($percent, 1);
            ?>
                <div class="bar-row">
                    <span class="star-label"><?php echo $labels[5 - $i]; ?></span>
                    <span style="color: var(--star-primary); margin-right: 10px;"><i class="fas fa-star"></i></span>

                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                    </div>

                    <span class="percentage-label"><?php echo $formatted_percent; ?>%</span>
                    <span class="count-label">(<?php echo $count; ?>)</span>
                </div>
            <?php } ?>
        </div>

        <div class="overall-rating">
            <div class="big-score"><?php echo $average_rating; ?></div>
            <div class="stars-display">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= round($average_rating)) echo '<i class="fas fa-star"></i>';
                    else echo '<i class="far fa-star" style="color:var(--star-secondary)"></i>';
                }
                ?>
            </div>
            <p style="color: #777; margin-top: 5px;"><?php echo $total_reviews; ?> Ratings</p>
        </div>
    </div>

    <div class="content-grid">
        <div class="feedback-list">
            <h2 class="section-title">Recent Feedbacks</h2>

            <?php if ($result_reviews->num_rows > 0): ?>
                <?php while ($row = $result_reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="reviewer-header">
                            <img src="<?php echo $row['image'] ? $row['image'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" alt="User" class="avatar">
                            <div class="reviewer-info">
                                <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                <div class="card-stars">
                                    <?php
                                    for ($k = 1; $k <= 5; $k++) {
                                        if ($k <= $row['rating']) echo '<i class="fas fa-star"></i>';
                                        else echo '<i class="fas fa-star" style="color:var(--star-secondary)"></i>';
                                    }
                                    ?>
                                </div>
                                <span class="review-date"><?php echo date('F j, Y', strtotime($row['created_at'])); ?></span>
                            </div>
                        </div>
                        <p class="review-text">
                            <?php echo htmlspecialchars($row['review']); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#666;">No reviews yet. Be the first!</p>
            <?php endif; ?>
        </div>

        <div class="review-form-container">
            <h2 class="section-title">Add a Review</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="reviewForm">

                <div class="input-group">
                    <label>Your Rating</label>
                    <input type="hidden" name="rating" id="ratingValue" value="0" required>

                    <div class="star-rating-widget" id="starWidget">
                        <i class="fas fa-star star-item" data-value="1"></i>
                        <i class="fas fa-star star-item" data-value="2"></i>
                        <i class="fas fa-star star-item" data-value="3"></i>
                        <i class="fas fa-star star-item" data-value="4"></i>
                        <i class="fas fa-star star-item" data-value="5"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="John Doe" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="john@example.com" required>
                </div>

                <div class="input-group">
                    <label>Write Your Review</label>
                    <textarea name="review_text" rows="5" placeholder="Share your experience..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">SUBMIT REVIEW</button>
            </form>
        </div>
    </div>

</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.star-item');
        const ratingInput = document.getElementById('ratingValue');
        let currentRating = 0;

        stars.forEach(star => {
            // Mouse Over: Highlight stars up to this one
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.getAttribute('data-value'));
                highlightStars(value);
            });

            // Mouse Leave: Reset to current selected rating
            star.addEventListener('mouseout', function() {
                highlightStars(currentRating);
            });

            // Click: Set the rating
            star.addEventListener('click', function() {
                currentRating = parseInt(this.getAttribute('data-value'));
                ratingInput.value = currentRating;
                highlightStars(currentRating);
            });
        });

        function highlightStars(count) {
            stars.forEach(star => {
                const value = parseInt(star.getAttribute('data-value'));
                if (value <= count) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // Prevent form submission if no rating selected
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            if (ratingInput.value == 0) {
                e.preventDefault();
                alert('Please select a star rating!');
            }
        });
    });
</script>