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
    . '<circle cx="100" cy="100" r="80" fill="#111" stroke="' . htmlspecialchars($accent_color) . '" stroke-width="2"/>'
    . '<text x="100" y="108" text-anchor="middle" fill="' . htmlspecialchars($accent_color) . '" font-family="-apple-system, sans-serif" font-size="24" font-weight="600" letter-spacing="1">'
    . htmlspecialchars($company_name) . '</text></svg>';
$fallback_logo_data_uri = 'data:image/svg+xml,' . rawurlencode($fallback_logo_svg);
?>

<style>
    /* ===== MODERN PREMIUM DESIGN SYSTEM ===== */
    :root {
        --bg-base: #000000;
        --bg-surface: #0a0a0a;
        --text-primary: #f5f5f7;
        --text-secondary: #86868b;
        --accent: <?= $accent_color ?>;
        --accent-glow: <?= $accent_color ?>33; /* 20% opacity */
        --accent-strong: <?= $accent_color ?>80;
        --border-subtle: rgba(255, 255, 255, 0.06);
        --glass-bg: rgba(20, 20, 20, 0.4);
        --font-main: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
        --ease-smooth: cubic-bezier(0.25, 0.1, 0.25, 1);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        width: 100%;
        overflow-x: hidden;
        background: var(--bg-base);
        color: var(--text-primary);
        font-family: var(--font-main);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        scroll-behavior: smooth;
    }

    /* ===== UTILITIES & ANIMATIONS ===== */
    .section-padding { padding: 120px 0; }
    
    .text-gradient {
        background: linear-gradient(135deg, #ffffff 0%, var(--text-secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .text-accent-gradient {
        background: linear-gradient(135deg, #ffffff 20%, var(--accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Smooth Scroll Reveal */
    .reveal {
        opacity: 0;
        transform: translateY(40px) scale(0.98);
        transition: all 1.2s var(--ease-out-expo);
        will-change: opacity, transform;
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    .delay-1 { transition-delay: 0.1s; }
    .delay-2 { transition-delay: 0.2s; }
    .delay-3 { transition-delay: 0.3s; }

    /* ===== PREMIUM GLASS & 3D EFFECTS ===== */
    .glass-panel {
        background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-subtle);
        border-radius: 32px;
        box-shadow: 
            0 30px 60px rgba(0,0,0,0.4),
            inset 0 1px 0 rgba(255,255,255,0.1);
        transition: transform 0.6s var(--ease-out-expo), box-shadow 0.6s var(--ease-out-expo), border-color 0.6s ease;
    }
    .glass-panel:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 40px 80px rgba(0,0,0,0.6),
            0 0 40px var(--accent-glow),
            inset 0 1px 0 rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.15);
    }

    /* ===== HERO SECTION ===== */
    .hero-section {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    /* Ambient Background Orbs */
    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.4;
        animation: floatOrb 20s infinite ease-in-out alternate;
        pointer-events: none;
        z-index: 0;
    }
    .orb-1 {
        width: 60vw; height: 60vw;
        background: radial-gradient(circle, var(--accent-glow) 0%, transparent 60%);
        top: -10%; left: -10%;
    }
    .orb-2 {
        width: 50vw; height: 50vw;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
        bottom: -20%; right: -10%;
        animation-delay: -5s;
    }

    @keyframes floatOrb {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(5%, 10%) scale(1.1); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 1000px;
        padding: 0 20px;
    }

    .hero-label {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--text-primary);
        margin-bottom: 30px;
        border: 1px solid var(--border-subtle);
        padding: 8px 20px;
        border-radius: 40px;
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .hero-title {
        font-size: clamp(3rem, 7vw, 6rem);
        font-weight: 700;
        letter-spacing: -0.04em;
        line-height: 1.05;
        margin-bottom: 30px;
    }

    .hero-subtitle {
        font-size: clamp(1.1rem, 2vw, 1.3rem);
        color: var(--text-secondary);
        margin-bottom: 50px;
        line-height: 1.6;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        font-weight: 400;
    }

    /* ===== BUTTONS ===== */
    .btn-premium {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 40px;
        background: var(--text-primary);
        color: var(--bg-base);
        border-radius: 40px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        overflow: hidden;
        transition: all 0.4s var(--ease-out-expo);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px var(--accent-glow);
        background: var(--accent);
        color: #fff;
    }

    .btn-outline-premium {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 40px;
        background: transparent;
        color: var(--text-primary);
        border: 1px solid var(--border-subtle);
        border-radius: 40px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.4s var(--ease-out-expo);
        backdrop-filter: blur(10px);
    }
    .btn-outline-premium:hover {
        border-color: var(--accent);
        background: rgba(255,255,255,0.03);
    }

    /* ===== STORY SECTION ===== */
    .story-section { position: relative; z-index: 2; }
    
    .story-content-inner { padding: 60px 50px; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    
    .eyebrow {
        color: var(--accent);
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        margin-bottom: 20px;
        display: block;
    }

    .story-heading {
        font-size: clamp(2rem, 3.5vw, 3rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 30px;
    }

    .story-text {
        color: var(--text-secondary);
        font-size: 1.15rem;
        line-height: 1.7;
        margin-bottom: 25px;
    }

    /* ===== REFINED 3D REACTOR ===== */
    .visual-container {
        position: relative;
        width: 100%;
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        perspective: 1000px;
    }

    .reactor-core {
        position: relative;
        width: 100%;
        max-width: 480px;
        aspect-ratio: 1 / 1;
        background: radial-gradient(circle at 50% 50%, rgba(20, 20, 20, 0.4) 0%, transparent 70%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transform-style: preserve-3d;
        animation: floatCore 8s var(--ease-smooth) infinite;
    }

    @keyframes floatCore {
        0%, 100% { transform: translateY(0) rotateX(5deg) rotateY(-5deg); }
        50% { transform: translateY(-15px) rotateX(10deg) rotateY(5deg); }
    }

    .orbital-ring {
        position: absolute;
        border-radius: 50%;
        transform-style: preserve-3d;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .ring-1 {
        width: 100%; height: 100%;
        border-left: 1px solid var(--accent);
        box-shadow: inset 10px 0 30px -10px var(--accent-glow);
        animation: spin1 20s linear infinite;
    }
    .ring-2 {
        width: 75%; height: 75%;
        border-right: 1px solid var(--text-primary);
        animation: spin2 15s linear infinite reverse;
    }
    .ring-3 {
        width: 50%; height: 50%;
        border-top: 2px solid var(--accent);
        filter: drop-shadow(0 0 10px var(--accent-strong));
        animation: spin3 10s linear infinite;
    }

    @keyframes spin1 { to { transform: rotateX(60deg) rotateY(20deg) rotateZ(360deg); } from { transform: rotateX(60deg) rotateY(20deg) rotateZ(0deg); } }
    @keyframes spin2 { to { transform: rotateX(70deg) rotateY(-20deg) rotateZ(360deg); } from { transform: rotateX(70deg) rotateY(-20deg) rotateZ(0deg); } }
    @keyframes spin3 { to { transform: rotateX(50deg) rotateY(10deg) rotateZ(360deg); } from { transform: rotateX(50deg) rotateY(10deg) rotateZ(0deg); } }

    .logo-center {
        position: relative;
        z-index: 10;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: var(--bg-base);
        padding: 4px;
        box-shadow: 
            0 20px 40px rgba(0,0,0,0.8),
            0 0 0 1px rgba(255,255,255,0.1),
            inset 0 0 20px rgba(255,255,255,0.05);
        transform: translateZ(50px);
    }
    
    .logo-center img {
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    /* ===== PILLARS / VALUES ===== */
    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        margin-top: 60px;
    }
    
    .value-card {
        padding: 50px 40px;
        text-align: left;
    }

    .value-icon-wrapper {
        width: 56px; height: 56px;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 24px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    }

    .value-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--text-primary);
    }

    /* ===== STATS SECTION ===== */
    .stats-section {
        position: relative;
        border-top: 1px solid var(--border-subtle);
        border-bottom: 1px solid var(--border-subtle);
        background: radial-gradient(ellipse at center, rgba(20,20,20,0.5) 0%, var(--bg-base) 100%);
    }

    .stat-item { padding: 40px 0; }
    
    .stat-number {
        font-size: clamp(3rem, 5vw, 4.5rem);
        font-weight: 700;
        letter-spacing: -0.04em;
        line-height: 1;
        margin-bottom: 10px;
        display: block;
    }

    /* ===== CTA SECTION ===== */
    .cta-section {
        position: relative;
        text-align: center;
        overflow: hidden;
    }
    .cta-bg-glow {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 80vw; height: 80vw;
        background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
        filter: blur(80px);
        opacity: 0.5;
        z-index: 0;
        pointer-events: none;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .visual-container { min-height: 400px; }
        .reactor-core { max-width: 320px; }
        .story-content-inner { padding: 40px 30px; }
    }
</style>

<script src="https://cdn.lordicon.com/lordicon.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // High-performance Intersection Observer for smooth reveal animations
        const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
        
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target); // Run once for better performance
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
</script>

<section class="hero-section">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                <div class="hero-content">
                    <div class="reveal">
                        <span class="hero-label">Engineered for Perfection</span>
                    </div>
                    <h1 class="hero-title reveal delay-1">
                        Redefining <span class="text-accent-gradient">Excellence</span>
                    </h1>
                    <p class="hero-subtitle reveal delay-2">
                        Where premium quality meets cutting-edge technology. Experience the future of our products today, crafted meticulously for your lifestyle.
                    </p>
                    <div class="d-flex justify-content-center gap-3 reveal delay-3 flex-wrap">
                        <a href="#story" class="btn-premium">
                            Discover Our Story
                        </a>
                        <a href="?supplier_id=<?= $supplier_id ?>&page=products" class="btn-outline-premium">
                            View Collection
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="story" class="story-section section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            
            <div class="col-lg-6">
                <div class="glass-panel story-content-inner reveal">
                    <span class="eyebrow">Who We Are</span>
                    <h2 class="story-heading">Crafting Tomorrow's <br><span class="text-gradient">Standards Today.</span></h2>
                    
                    <p class="story-text">
                        <?= nl2br(htmlspecialchars($company_desc)) ?>
                    </p>
                    <p class="story-text">
                        At <strong style="color: var(--text-primary); font-weight: 600;"><?= htmlspecialchars($company_name) ?></strong>, we curate experiences. Our commitment to innovation is matched only by our dedication to your complete satisfaction.
                    </p>
                    
                    <div class="mt-4">
                        <a href="?supplier_id=<?= $supplier_id ?>&page=contact" class="btn-outline-premium" style="padding: 12px 24px; border-radius: 20px;">
                            Connect With Us
                            <lord-icon src="https://cdn.lordicon.com/zmkotitn.json" trigger="hover" colors="primary:#fff" style="width:20px;height:20px"></lord-icon>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="visual-container reveal delay-2">
                    <div class="reactor-core">
                        <div class="orbital-ring ring-1"></div>
                        <div class="orbital-ring ring-2"></div>
                        <div class="orbital-ring ring-3"></div>
                        <div class="logo-center">
                            <img src="../uploads/shops/<?= $supplier_id ?>/<?= htmlspecialchars($shop_assets['logo']) ?>" 
                                 alt="Brand Logo" 
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
            <div class="col-lg-8 reveal">
                <span class="eyebrow">Our Pillars</span>
                <h2 class="story-heading" style="margin-bottom: 0;">Built on Excellence</h2>
            </div>
        </div>

        <div class="values-grid">
            <div class="glass-panel value-card reveal">
                <div class="value-icon-wrapper">
                    <lord-icon src="https://cdn.lordicon.com/hjeefwhm.json" trigger="hover" colors="primary:#ffffff" style="width:28px;height:28px"></lord-icon>
                </div>
                <h4 class="value-title">Uncompromising Quality</h4>
                <p class="story-text" style="font-size: 1rem; margin:0;">Rigorous testing and premium materials ensure every product exceeds industry standards.</p>
            </div>
            <div class="glass-panel value-card reveal delay-1">
                <div class="value-icon-wrapper">
                    <lord-icon src="https://cdn.lordicon.com/cllunfud.json" trigger="hover" colors="primary:#ffffff" style="width:28px;height:28px"></lord-icon>
                </div>
                <h4 class="value-title">Absolute Security</h4>
                <p class="story-text" style="font-size: 1rem; margin:0;">Enterprise-grade architecture protects your data and guarantees peace of mind.</p>
            </div>
            <div class="glass-panel value-card reveal delay-2">
                <div class="value-icon-wrapper">
                    <lord-icon src="https://cdn.lordicon.com/zpxybbhl.json" trigger="hover" colors="primary:#ffffff" style="width:28px;height:28px"></lord-icon>
                </div>
                <h4 class="value-title">24/7 Priority Support</h4>
                <p class="story-text" style="font-size: 1rem; margin:0;">Round-the-clock expert assistance ensuring a flawless and seamless experience.</p>
            </div>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 stat-item reveal">
                <span class="stat-number text-gradient">100<span style="color: var(--accent);">%</span></span>
                <span style="color: var(--text-secondary); font-weight: 500; letter-spacing: 1px; text-transform: uppercase; font-size: 0.85rem;">Client Satisfaction</span>
            </div>
            <div class="col-md-4 stat-item reveal delay-1">
                <span class="stat-number text-gradient">10<span style="color: var(--accent);">k+</span></span>
                <span style="color: var(--text-secondary); font-weight: 500; letter-spacing: 1px; text-transform: uppercase; font-size: 0.85rem;">Products Delivered</span>
            </div>
            <div class="col-md-4 stat-item reveal delay-2">
                <span class="stat-number text-gradient">24<span style="color: var(--accent);">/</span>7</span>
                <span style="color: var(--text-secondary); font-weight: 500; letter-spacing: 1px; text-transform: uppercase; font-size: 0.85rem;">Support Available</span>
            </div>
        </div>
    </div>
</section>

<section class="cta-section section-padding">
    <div class="cta-bg-glow"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 reveal">
                <h2 class="story-heading">Ready to Experience the Best?</h2>
                <p class="story-text" style="margin-bottom: 40px;">Join thousands of satisfied customers and discover products that redefine excellence.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="?supplier_id=<?= $supplier_id ?>&page=products" class="btn-premium">Explore Catalog</a>
                    <a href="?supplier_id=<?= $supplier_id ?>&page=contact" class="btn-outline-premium">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>