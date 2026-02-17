<?php
// Ensure this file is accessed via index.php
if (!defined('DIR') && !isset($supplier)) {
    // optional security check
}

// Fallback description
$company_desc = !empty($supplier['description'])
    ? $supplier['description']
    : "Welcome to " . htmlspecialchars($supplier['company_name']) . ". We are dedicated to providing the best quality products and exceptional service to our customers. Our journey began with a simple mission: to make premium goods accessible to everyone.";

// Fallback logic for colors/names
$company_name = $supplier['company_name'] ?? 'BRAND';
$accent_color = $shop_assets['primary_color'] ?? '#D4AF37';

// Generate a fallback logo data URI
$fallback_logo_svg = '<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">'
    . '<circle cx="100" cy="100" r="80" fill="#333" stroke="' . htmlspecialchars($accent_color) . '" stroke-width="4"/>'
    . '<text x="100" y="120" text-anchor="middle" fill="' . htmlspecialchars($accent_color) . '" font-family="Arial, sans-serif" font-size="24" font-weight="bold">'
    . htmlspecialchars($company_name) . '</text></svg>';
$fallback_logo_data_uri = 'data:image/svg+xml,' . rawurlencode($fallback_logo_svg);
?>

<style>
    /* ===== MODERN DESIGN SYSTEM ===== */
    :root {
        --bg-color: #050505;
        --card-bg: rgba(20, 20, 20, 0.6);
        --text-main: #ffffff;
        --text-muted: #a0a0a0;
        --accent: <?= $accent_color ?>;
        --accent-glow: <?= $accent_color ?>40;
        --font-display: 'Helvetica Neue', 'Arial Black', sans-serif;
        --font-body: 'Helvetica', sans-serif;
        --transition-smooth: cubic-bezier(0.2, 0.8, 0.2, 1);
        --gradient-text: linear-gradient(135deg, #fff 30%, var(--accent) 100%);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        width: 100%;
        overflow-x: hidden;
        background: var(--bg-color);
        color: var(--text-main);
        font-family: var(--font-body);
        scroll-behavior: smooth;
    }

    /* ===== UTILITIES ===== */
    .section-padding { padding: 100px 0; }
    
    .text-gradient {
        background: var(--gradient-text);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .page-entrance {
        opacity: 0;
        animation: fadeInUp 1s var(--transition-smooth) forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%      { transform: translateY(-15px); }
    }

    /* ===== HERO SECTION ===== */
    .hero-section {
        position: relative;
        min-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at 50% 30%, #151515 0%, #000000 70%);
        overflow: hidden;
    }

    .hero-glow {
        position: absolute;
        width: 60vw;
        height: 60vw;
        background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
        filter: blur(80px);
        opacity: 0.4;
        top: -20%;
        left: 50%;
        transform: translateX(-50%);
        pointer-events: none;
        z-index: 1;
    }

    .particles-container {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
    }

    .particle {
        position: absolute;
        background: var(--accent);
        border-radius: 50%;
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        z-index: 5;
        text-align: center;
        max-width: 900px;
        padding: 0 20px;
    }

    .hero-label {
        display: inline-block;
        font-size: 0.85rem;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 20px;
        border: 1px solid var(--accent-glow);
        padding: 8px 16px;
        border-radius: 30px;
        background: rgba(0,0,0,0.3);
        backdrop-filter: blur(5px);
    }

    .hero-title {
        font-family: var(--font-display);
        font-size: clamp(3.5rem, 8vw, 6.5rem);
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 25px;
        letter-spacing: -1px;
    }

    .hero-subtitle {
        font-size: clamp(1.1rem, 2vw, 1.4rem);
        color: var(--text-muted);
        margin-bottom: 40px;
        line-height: 1.6;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Fixed Scroll Indicator */
    .scroll-indicator {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        opacity: 0.6;
        animation: float 2s infinite ease-in-out;
        z-index: 5;
        pointer-events: none; /* Prevents overlay blocking clicks */
    }

    /* Hide scroll indicator on short screens to avoid overlap */
    @media (max-height: 700px) {
        .scroll-indicator { display: none; }
    }

    .mouse-icon {
        width: 26px;
        height: 40px;
        border: 2px solid var(--text-muted);
        border-radius: 20px;
        position: relative;
    }

    .mouse-icon::before {
        content: '';
        position: absolute;
        top: 6px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 4px;
        background: var(--accent);
        border-radius: 50%;
        animation: scrollWheel 2s infinite;
    }

    @keyframes scrollWheel {
        0% { top: 6px; opacity: 1; }
        100% { top: 20px; opacity: 0; }
    }

    /* ===== STORY SECTION ===== */
    .story-section {
        position: relative;
        background: #080808;
        overflow: hidden;
    }

    /* Modern Glass Card */
    .story-card-glass {
        background: var(--card-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        padding: 50px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .story-heading {
        font-size: clamp(2rem, 3vw, 3rem);
        font-weight: 700;
        margin-bottom: 25px;
        line-height: 1.2;
    }

    .story-text {
        color: var(--text-muted);
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .highlight {
        color: #fff;
        border-bottom: 1px solid var(--accent);
    }

    /* ===== RESTORED PREMIUM REACTOR ANIMATION ===== */
    @keyframes spinRing1 {
        0% { transform: rotateX(65deg) rotateY(10deg) rotateZ(0deg); }
        100% { transform: rotateX(65deg) rotateY(10deg) rotateZ(360deg); }
    }
    @keyframes spinRing2 {
        0% { transform: rotateX(50deg) rotateY(-20deg) rotateZ(0deg); }
        100% { transform: rotateX(50deg) rotateY(-20deg) rotateZ(360deg); }
    }
    @keyframes spinRing3 {
        0% { transform: rotateX(75deg) rotateY(15deg) rotateZ(0deg); }
        100% { transform: rotateX(75deg) rotateY(15deg) rotateZ(360deg); }
    }
    @keyframes premiumFloat {
        0%, 100% { transform: translateY(0px) scale(1); }
        50% { transform: translateY(-12px) scale(1.02); }
    }
    @keyframes spinAmbient {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Reactor Wrapper (Alignment) */
    .visual-container {
        position: relative;
        width: 100%;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        perspective: 1200px;
    }

    /* Original Reactor Styles */
    .reactor-container-modern {
        position: relative;
        width: 100%;
        max-width: 450px;
        aspect-ratio: 1 / 1;
        background: radial-gradient(circle at 30% 30%, rgba(26, 26, 26, 0.8) 0%, #050505 100%);
        border-radius: 40px;
        box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.8), 0 25px 50px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transform-style: preserve-3d;
        transition: transform 0.6s var(--transition-smooth);
    }

    .reactor-container-modern:hover {
        transform: translateY(-10px) rotateX(5deg) rotateY(-5deg);
        box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.8), 0 35px 60px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .ambient-glow {
        position: absolute;
        width: 150%;
        height: 150%;
        background: conic-gradient(from 0deg at 50% 50%, transparent 0deg, var(--accent-glow) 180deg, transparent 360deg);
        animation: spinAmbient 15s linear infinite;
        opacity: 0.15;
    }

    .orbital-ring {
        position: absolute;
        border-radius: 50%;
        transform-style: preserve-3d;
    }

    .ring-1 {
        width: 320px;
        height: 320px;
        border: 1px solid rgba(255, 255, 255, 0.03);
        border-left: 2px solid var(--accent);
        box-shadow: 0 0 20px var(--accent-glow);
        animation: spinRing1 12s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .ring-2 {
        width: 240px;
        height: 240px;
        border: 1px dashed rgba(255, 255, 255, 0.15);
        border-right: 2px solid #ffffff;
        animation: spinRing2 16s cubic-bezier(0.4, 0, 0.6, 1) infinite reverse;
        opacity: 0.6;
    }

    .ring-3 {
        width: 160px;
        height: 160px;
        border: 2px solid rgba(255, 255, 255, 0.08);
        border-top: 2px solid var(--accent);
        animation: spinRing3 8s linear infinite;
    }

    .logo-wrapper {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: premiumFloat 6s ease-in-out infinite;
        transform-style: preserve-3d;
    }

    .floating-logo {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
        background: #000;
    }

    /* ===== BUTTONS ===== */
    .btn-glow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 40px;
        background: var(--text-main);
        color: #000;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 0 20px var(--accent-glow);
    }

    .btn-glow:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px var(--accent-glow);
        background: var(--accent);
    }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 40px;
        background: transparent;
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    /* ===== OTHER SECTIONS ===== */
    .values-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 60px;
    }
    .value-card-modern {
        background: linear-gradient(145deg, #111, #0f0f0f);
        border-radius: 25px;
        padding: 50px 40px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.5s var(--transition-smooth);
    }
    .value-card-modern:hover { transform: translateY(-10px); border-color: var(--accent); }
    .value-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.05); border-radius: 15px; display:flex; align-items:center; justify-content:center; margin-bottom:25px; }
    
    .stats-section { background: linear-gradient(to bottom, #050505, #111); }
    .stat-number { font-size: 3.5rem; font-weight: 900; color: #fff; display: block; }
    
    .cta-section { background: linear-gradient(135deg, #0a0a0a 0%, var(--accent) 300%); }

    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .visual-container { min-height: 400px; margin-top: 40px; }
        .hero-section { min-height: 80vh; }
        .story-card-glass { text-align: center; padding: 40px 20px; }
        .reactor-container-modern { max-width: 350px; }
        .ring-1 { width: 280px; height: 280px; }
        .ring-2 { width: 200px; height: 200px; }
        .ring-3 { width: 140px; height: 140px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.particles-container');
        if(container) {
            for(let i=0; i<40; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                const size = Math.random() * 3 + 1;
                p.style.width = size + 'px';
                p.style.height = size + 'px';
                p.style.left = Math.random() * 100 + '%';
                p.style.top = Math.random() * 100 + '%';
                p.style.animation = `float ${Math.random() * 10 + 10}s infinite linear`;
                container.appendChild(p);
            }
        }
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        });
        document.querySelectorAll('.page-entrance').forEach(el => observer.observe(el));
    });
</script>

<section class="hero-section">
    <div class="particles-container"></div>
    <div class="hero-glow"></div>
    
    <div class="container">
        <div class="hero-content page-entrance">
            <span class="hero-label">Established to Innovate</span>
            <h1 class="hero-title">
                Redefining <span class="text-gradient">Excellence</span>
            </h1>
            <p class="hero-subtitle">
                Where premium quality meets cutting-edge technology. Experience the future of our products today.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#story" class="btn-glow">
                    Our Story
                    <lord-icon
                        src="https://cdn.lordicon.com/msoeawqm.json"
                        trigger="loop"
                        colors="primary:#000"
                        style="width:20px;height:20px">
                    </lord-icon>
                </a>
                <a href="?supplier_id=<?= $supplier_id ?>&page=products" class="btn-outline">
                    View Products
                </a>
            </div>
        </div>
    </div>

    <div class="scroll-indicator">
        <div class="mouse-icon"></div>
        <span style="font-size: 12px; letter-spacing: 2px;">SCROLL</span>
    </div>
</section>

<section id="story" class="story-section section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            
            <div class="col-lg-6 page-entrance">
                <div class="story-card-glass">
                    <span style="color: var(--accent); letter-spacing: 2px; font-size: 0.9rem; margin-bottom: 15px; display:block;">WHO WE ARE</span>
                    <h2 class="story-heading">Crafting Tomorrow's <br><span class="text-gradient">Standards Today</span></h2>
                    
                    <p class="story-text">
                        <?= nl2br(htmlspecialchars($company_desc)) ?>
                    </p>
                    <p class="story-text">
                        At <span class="highlight"><?= htmlspecialchars($company_name) ?></span>, we don't just sell products; we curate experiences. Our commitment to innovation is matched only by our dedication to your satisfaction.
                    </p>
                    
                    <div class="mt-4">
                        <a href="?supplier_id=<?= $supplier_id ?>&page=contact" style="color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                            Connect With Us
                            <lord-icon
                                src="https://cdn.lordicon.com/zmkotitn.json"
                                trigger="hover"
                                colors="primary:<?= $accent_color ?>"
                                style="width:20px;height:20px">
                            </lord-icon>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 page-entrance" style="animation-delay: 0.2s">
                <div class="visual-container">
                    <div class="reactor-container-modern">
                        <div class="ambient-glow"></div>
                        <div class="orbital-ring ring-1"></div>
                        <div class="orbital-ring ring-2"></div>
                        <div class="orbital-ring ring-3"></div>
                        <div class="logo-wrapper">
                            <img
                                src="../uploads/shops/<?= $supplier_id ?>/<?= htmlspecialchars($shop_assets['logo']) ?>"
                                alt="Logo"
                                class="floating-logo"
                                onerror="this.src='<?= $fallback_logo_data_uri ?>'">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8 page-entrance">
                <span style="color: var(--accent); letter-spacing: 2px; font-size: 0.9rem;">OUR PILLARS</span>
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-top: 10px;">Built on Excellence</h2>
            </div>
        </div>

        <div class="values-grid-modern">
            <div class="value-card-modern page-entrance">
                <div class="value-icon">
                    <lord-icon src="https://cdn.lordicon.com/hjeefwhm.json" trigger="hover" colors="primary:#ffffff" style="width:35px;height:35px"></lord-icon>
                </div>
                <h4 style="color:#fff; margin-bottom:15px;">Quality First</h4>
                <p style="color: var(--text-muted);">Rigorous testing ensures every product meets our premium standards.</p>
            </div>
            <div class="value-card-modern page-entrance" style="animation-delay: 0.2s">
                <div class="value-icon">
                    <lord-icon src="https://cdn.lordicon.com/cllunfud.json" trigger="hover" colors="primary:#ffffff" style="width:35px;height:35px"></lord-icon>
                </div>
                <h4 style="color:#fff; margin-bottom:15px;">Secure</h4>
                <p style="color: var(--text-muted);">Military-grade encryption protects your data and peace of mind.</p>
            </div>
            <div class="value-card-modern page-entrance" style="animation-delay: 0.4s">
                <div class="value-icon">
                    <lord-icon src="https://cdn.lordicon.com/zpxybbhl.json" trigger="hover" colors="primary:#ffffff" style="width:35px;height:35px"></lord-icon>
                </div>
                <h4 style="color:#fff; margin-bottom:15px;">24/7 Support</h4>
                <p style="color: var(--text-muted);">Round-the-clock assistance ensuring a flawless experience.</p>
            </div>
        </div>
    </div>
</section>

<section class="stats-section section-padding">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 page-entrance">
                <span class="stat-number">100%</span>
                <span style="color: var(--accent);">Client Satisfaction</span>
            </div>
            <div class="col-md-4 page-entrance" style="animation-delay: 0.2s">
                <span class="stat-number">10K+</span>
                <span style="color: var(--accent);">Products Delivered</span>
            </div>
            <div class="col-md-4 page-entrance" style="animation-delay: 0.4s">
                <span class="stat-number">24/7</span>
                <span style="color: var(--accent);">Support Available</span>
            </div>
        </div>
    </div>
</section>

<section class="cta-section section-padding">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8 page-entrance">
                <h2 style="font-size: 3rem; margin-bottom: 20px;">Join the Revolution</h2>
                <p style="margin-bottom: 40px; color: rgba(255,255,255,0.8);">Discover products that redefine excellence.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="?supplier_id=<?= $supplier_id ?>&page=products" class="btn-glow">Explore</a>
                    <a href="?supplier_id=<?= $supplier_id ?>&page=contact" class="btn-outline">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.lordicon.com/lordicon.js"></script>