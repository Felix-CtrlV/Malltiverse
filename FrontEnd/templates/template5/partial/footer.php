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
      <div class="link-column">
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
/* Global Footer Styling */
.main-footer {
    background-color: #ffffff;
    padding: 80px 5% 40px 5%;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #333;
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    align-items: flex-start;
    flex-wrap: wrap;
}

/* --- Left Side: Brand Section --- */
.footer-brand {
    max-width: 320px;
    text-align: left;
}

.logo-box {
    font-size: 1.5rem;
    font-weight: 800;
    color: #000;
    margin-bottom: 15px;
    letter-spacing: -0.5px;
}

.logo-box span {
    color: #0070f3;
}

.footer-brand p {
    color: #555;
    line-height: 1.6;
    font-size: 1rem;
    margin-bottom: 25px;
}

.social-icons {
    display: flex;
    gap: 20px;
}

.social-icons a {
    text-decoration: none;
    color: #666;
    font-size: 1.5rem;
    transition: color 0.2s;
}

.social-icons a:hover {
    color: #0070f3;
}

/* --- Right Side: Links Section --- */
.footer-links {
    display: flex;
   
    gap: 80px; 
    
    margin-left:340px; 
}

.link-column {
    display: flex;
    flex-direction: column;
    align-items: flex-start; 
    min-width: 120px;
}

.link-column h4 {
    margin: 0 0 25px 0;
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #111;
}

.link-column ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.link-column li {
    margin-bottom: 15px;
}

.link-column a {
    text-decoration: none;
    color: #666;
    font-size: 0.9rem;
    transition: color 0.2s;
}

.link-column a:hover {
    color: #0070f3;
}

/* --- Bottom Copyright Section --- */
.footer-bottom {
    max-width: 1400px;
    margin: 60px auto 0;
    padding-top: 30px;
    border-top: 1px solid #eaeaea;
    /* Copyright ကို ဒုတိယပုံထဲကအတိုင်း အလယ်မှာထားပါတယ် */
    text-align: center; 
}

.footer-bottom p {
    color: #999;
    font-size: 0.8rem;
    text-transform: uppercase;
    margin: 0;
}

/* --- Mobile Responsiveness --- */
@media (max-width: 900px) {
    .footer-container {
        flex-direction: column;
        gap: 40px;
    }

    .footer-links {
        margin-left: 0;
        width: 100%;
        gap: 30px;
        justify-content: space-between;
    }
}
</style>