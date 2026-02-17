<footer class="main-footer">
  <div class="footer-container">
    <div class="footer-brand">
      <div class="logo-box">MALL<span>TIVERSE</span></div>
      <p>Making the world’s data accessible and useful for teams everywhere.</p>
      <div class="social-icons">
        <a href="#">𝕏</a>
        <a href="#">in</a>
        <a href="#">ig</a>
      </div>
    </div>

    <div class="footer-links">
      <div class="link-column">
        <h4>PRODUCT</h4>
        <ul>
          <li><a href="#">Features</a></li>
          <li><a href="#">Integrations</a></li>
          <li><a href="#">Pricing</a></li>
          <li><a href="#">Changelog</a></li>
        </ul>
      </div>
      <div class="link-column">
        <h4>COMPANY</h4>
        <ul>
          <li><a href="#">About Us</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Blog</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </div>
      <div class="link-column legal-column">
        <h4>LEGAL</h4>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Cookie Policy</a></li>
        </ul>
      </div>
    </div>
  </div>
  
  <div class="footer-bottom">
    <p>&copy; 2025 MALLTIVERSE Inc. All rights reserved.</p>
  </div>
</footer>

<style>
.main-footer {
    background-color: #ffffff;
    padding: 80px 5% 40px 5%;
    font-family: -apple-system, sans-serif;
    color: #333;
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 40px;
}

.footer-brand {
    max-width: 320px;
    flex: 1 1 300px;
}

.logo-box {
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 15px;
}

.logo-box span { color: #0070f3; }

.footer-brand p {
    color: #555;
    line-height: 1.6;
    margin-bottom: 25px;
}

.social-icons { display: flex; gap: 20px; }
.social-icons a { color: #666; font-size: 1.5rem; text-decoration: none; }

.footer-links {
    display: flex;
    gap: 60px;
    flex-wrap: wrap;
    flex: 2 1 500px;
}

.link-column {
    min-width: 140px;
}

.link-column h4 {
    margin-bottom: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
}

.link-column ul { list-style: none; padding: 0; }
.link-column li { margin-bottom: 12px; }
.link-column a { text-decoration: none; color: #666; font-size: 0.95rem; }

.footer-bottom {
    max-width: 1400px;
    margin: 60px auto 0;
    padding-top: 30px;
    border-top: 1px solid #eaeaea;
    text-align: center;
}

.footer-bottom p { color: #999; font-size: 0.85rem; }

/* Mobile logic: PRODUCT & COMPANY side-by-side, LEGAL below */
@media (max-width: 600px) {
    .footer-links {
        display: grid;
        grid-template-columns: 1fr 1fr; /* 2 columns for Product & Company */
        gap: 30px;
    }

    .legal-column {
        grid-column: span 2; /* Legal takes full width below them */
        margin-top: 10px;
        padding-top: 20px;
        border-top: 1px solid #f5f5f5; /* Optional line to separate legal */
    }
}
</style>