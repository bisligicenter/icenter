<div id="reserveModal" class="modal">
  <div class="modal-content">
    <span class="close" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #666; transition: color 0.3s;">&times;</span>
    
    <h3 style="text-align: center; margin-bottom: 25px; color: #333; font-size: 24px;">Confirm Reservation</h3>
    
    <div style="text-align: center; margin-bottom: 30px;">
      <img class="modal-product-image" src="" alt="" style="max-width: 200px; max-height: 200px; border-radius: 12px; margin: 0 auto 20px; display: block;">
      <h4 class="modal-product-name" style="margin: 15px 0; color: #333; font-size: 20px;"></h4>
      <div style="padding: 15px; margin: 15px 0;">
        <p class="modal-product-brand" style="color: #666; margin: 5px 0;"></p>
        <p class="modal-product-model" style="color: #666; margin: 5px 0;"></p>
        <p class="modal-product-price" style="color: #111; font-weight: bold; font-size: 1.2em; margin: 10px 0;"></p>
        <p class="modal-product-id" style="color: #666; margin: 5px 0; font-size: 0.9em; display: none;">Product ID: <span></span></p>
      </div>
    </div>
    
    <p style="text-align: center; margin: 20px 0; color: #555;">Would you like to proceed with reserving this product?</p>
    
    <div style="display: flex; justify-content: center; gap: 15px; margin-top: 25px;">
      <button class="modal-btn cancel-btn" style="padding: 7px 16px; font-size: 0.95em; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; background: #f8f9fa; color: #666;">Cancel</button>
      <button class="modal-btn confirm-btn proceed-btn" style="padding: 7px 16px; font-size: 0.95em; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; background: #111; color: white;">Proceed to Reservation</button>
    </div>
  </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="modal">
  <div class="modal-content">
    <span class="close" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #666; transition: color 0.3s;">&times;</span>
    
    <div style="text-align: center; margin-bottom: 20px;">
      <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 16px;"></i>
      <h3 style="color: #333; font-size: 20px; margin-bottom: 12px;">Success!</h3>
      <p style="color: #666; font-size: 16px; line-height: 1.5;">Your product has been successfully added to reservations.</p>
    </div>
    
    <div style="display: flex; justify-content: center;">
      <button class="modal-btn confirm-btn" style="padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; background: #007dd1; color: white;">Continue</button>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
  <div class="modal-content details-modal-content">
    <button class="close" style="position: absolute; top: 20px; right: 20px; font-size: 24px; cursor: pointer; color: #666; background: none; border: none; z-index: 10;">&times;</button>

    <div class="product-showcase-container">
      <!-- Left Side: Product Image and Thumbnails -->
      <div class="product-image-section">
        <div class="image-container">
          <img class="modal-product-image" src="" alt="Product Image">
        </div>
        <div id="modalThumbnails" class="thumbnail-gallery">
          <!-- Thumbnails will be populated by JS -->
        </div>
      </div>

      <!-- Right Side: Brand/Model and Specifications -->
      <div class="product-details-section">
        <!-- Brand and Model Header -->
        <div class="brand-model-header">
          <p class="modal-product-brand"></p>
          <h3 class="modal-product-name"></h3>
        </div>

        <!-- Specifications Container -->
        <div class="specifications-container">
          <!-- Basic Specifications (Left Column) -->
          <div class="specifications-section basic-specs">
            <h4>Basic Specifications</h4>
            <div class="specs-list">
              <div class="spec-item">
                <span class="spec-label">Price:</span>
                <span class="modal-product-price spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Model:</span>
                <span class="modal-product-model spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Storage:</span>
                <span class="modal-product-storage spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Screen Size:</span>
                <span class="modal-product-screen-size spec-value"></span>
              </div>
            </div>
          </div>

          <!-- Technical Specifications (Right Column) -->
          <div class="specifications-section technical-specs">
            <h4>Technical Specifications</h4>
            <div class="specs-list technical-list">
              <div class="spec-item">
                <span class="spec-label">Chip:</span>
                <span class="modal-product-chip spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Camera:</span>
                <span class="modal-product-camera-feature spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Charging Port:</span>
                <span class="modal-product-charging-port spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Material:</span>
                <span class="modal-product-material spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Water Resistance:</span>
                <span class="modal-product-water-resistance spec-value"></span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Display:</span>
                <span class="modal-product-display-output spec-value"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Customer Care Modals -->
<div id="contactUsModal" class="care-modal" role="dialog" aria-modal="true" aria-labelledby="contactUsHeader" tabindex="-1">
  <div class="modal-content care-modal-content">
    <div class="care-modal-header">
      <span class="care-modal-icon"><i class="fas fa-headset"></i></span>
      <h3 id="contactUsHeader" class="care-modal-title">Contact Us</h3>
      <button class="care-modal-close" aria-label="Close" tabindex="0">&times;</button>
    </div>
    <div class="care-modal-body">
      <p class="care-modal-intro">We're here to help! Reach out to us through any of the channels below.</p>
      <div class="contact-grid">
        <a href="tel:09760035417" class="contact-card">
          <i class="fas fa-phone-alt"></i>
          <h4>Call Us</h4>
          <p>0976 003 5417</p>
        </a>
        <a href="mailto:support@bisligicenter.com" class="contact-card">
          <i class="fas fa-envelope"></i>
          <h4>Email Us</h4>
          <p>support@bisligicenter.com</p>
        </a>
        <a href="https://maps.app.goo.gl/jfV82bbHNnCWb3jXA" target="_blank" class="contact-card">
          <i class="fas fa-map-marker-alt"></i>
          <h4>Visit Us</h4>
          <p>Bislig City, Surigao del Sur</p>
        </a>
        <a href="https://www.facebook.com/bisligicenter" target="_blank" class="contact-card">
          <i class="fab fa-facebook-f"></i>
          <h4>Message Us</h4>
          <p>on Facebook</p>
        </a>
      </div>
    </div>
  </div>
</div>

<div id="howToOrderModal" class="care-modal" role="dialog" aria-modal="true" aria-labelledby="howToOrderHeader" tabindex="-1">
  <div class="modal-content care-modal-content">
    <div class="care-modal-header">
      <span class="care-modal-icon"><i class="fas fa-shopping-cart"></i></span>
      <h3 id="howToOrderHeader" class="care-modal-title">How to Order</h3>
      <button class="care-modal-close" aria-label="Close" tabindex="0">&times;</button>
    </div>
    <div class="care-modal-body">
      <p class="care-modal-intro">Reserving your favorite tech is easy. Just follow these simple steps:</p>
      <ol class="enhanced-steps">
        <li>
          <div class="step-icon"><i class="fas fa-search"></i></div>
          <div class="step-content">
            <strong>Browse & Select:</strong> Explore our products and click the "Reserve" button on your desired item.
          </div>
        </li>
        <li>
          <div class="step-icon"><i class="fas fa-user-edit"></i></div>
          <div class="step-content">
            <strong>Fill Out Form:</strong> Provide your details in the reservation form.
          </div>
        </li>
        <li>
          <div class="step-icon"><i class="fas fa-wallet"></i></div>
          <div class="step-content">
            <strong>Pay Reservation Fee:</strong> For items over ₱1,000, a small reservation fee is required.
          </div>
        </li>
        <li>
          <div class="step-icon"><i class="fas fa-mobile-alt"></i></div>
          <div class="step-content">
            <strong>Wait for Confirmation:</strong> We'll contact you via call or message to confirm your reservation.
          </div>
        </li>
        <li>
          <div class="step-icon"><i class="fas fa-store"></i></div>
          <div class="step-content">
            <strong>Visit & Collect:</strong> Come to our store to complete your purchase and pick up your new gadget!
          </div>
        </li>
      </ol>
    </div>
  </div>
</div>

<div id="returnsRefundsModal" class="care-modal" role="dialog" aria-modal="true" aria-labelledby="returnsRefundsHeader" tabindex="-1">
  <div class="modal-content care-modal-content">
    <div class="care-modal-header">
      <span class="care-modal-icon"><i class="fas fa-undo-alt"></i></span>
      <h3 id="returnsRefundsHeader" class="care-modal-title">Returns and Refunds</h3>
      <button class="care-modal-close" aria-label="Close" tabindex="0">&times;</button>
    </div>
    <div class="care-modal-body">
      <div class="policy-section">
        <div class="policy-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="policy-content">
          <h4>7-Day Return Policy</h4>
          <p>We accept returns within 7 days of purchase for the following reasons:</p>
          <ul>
            <li><i class="fas fa-cogs"></i> Manufacturing defects</li>
            <li><i class="fas fa-box-open"></i> Wrong item received</li>
            <li><i class="fas fa-shipping-fast"></i> Damaged during delivery</li>
          </ul>
        </div>
      </div>
      <div class="policy-section important-note">
        <div class="policy-icon"><i class="fas fa-receipt"></i></div>
        <div class="policy-content">
          <h4>Requirements for Return</h4>
          <p>Please bring your official receipt and the item in its original, complete packaging to process your return.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="warrantyModal" class="care-modal" role="dialog" aria-modal="true" aria-labelledby="warrantyHeader" tabindex="-1">
  <div class="modal-content care-modal-content">
    <div class="care-modal-header">
      <span class="care-modal-icon"><i class="fas fa-shield-alt"></i></span>
      <h3 id="warrantyHeader" class="care-modal-title">Warranty Information</h3>
      <button class="care-modal-close" aria-label="Close" tabindex="0">&times;</button>
    </div>
    <div class="care-modal-body">
      <div class="policy-section">
        <div class="policy-icon"><i class="fab fa-apple"></i></div>
        <div class="policy-content">
          <h4>Apple Products</h4>
          <p>iPhones, iPads, and other Apple devices come with a <strong>1-year international warranty</strong>.</p>
        </div>
      </div>
      <div class="policy-section">
        <div class="policy-icon"><i class="fas fa-headphones-alt"></i></div>
        <div class="policy-content">
          <h4>Accessories</h4>
          <p>Most accessories are covered by a <strong>6-month warranty</strong> against manufacturing defects.</p>
        </div>
      </div>
      <div class="policy-section">
        <div class="policy-icon"><i class="fas fa-laptop-house"></i></div>
        <div class="policy-content">
          <h4>Other Devices</h4>
          <p>Warranty for other devices follows the respective manufacturer's policy. Please check the product documentation.</p>
        </div>
      </div>
      <div class="policy-section important-note">
        <div class="policy-icon"><i class="fas fa-receipt"></i></div>
        <div class="policy-content">
          <h4>Claiming Warranty</h4>
          <p>To claim warranty, please present your official receipt and the warranty card provided upon purchase.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* --- Product Details Modal Styles --- */
  .details-modal-content {
    max-width: 1400px;
    width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    will-change: auto;
  }

  .product-showcase-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    gap: 2rem;
  }

  /* Product Image Section */
  .product-image-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 450px;
    flex-shrink: 0;
  }

  .image-container {
    width: 100%;
    height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
  }

  .modal-product-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
  }

  /* Thumbnail Gallery */
  .thumbnail-gallery {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1rem;
  }

  .thumbnail-gallery img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    opacity: 0;
  }

  .thumbnail-gallery img:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .thumbnail-gallery img.active {
    border-color: #007dd1;
    box-shadow: 0 0 0 2px rgba(0,125,209,0.2);
  }

  /* Product Details Section */
  .product-details-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    align-items: center;
  }

  .brand-model-header {
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .brand-model-header .modal-product-brand {
    font-size: 1.2rem;
    color: #666;
    margin: 0 0 0.5rem 0;
    font-weight: 500;
  }

  .brand-model-header .modal-product-name {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111;
    margin: 0;
    line-height: 1.2;
  }

  /* Specifications Container */
  .specifications-container {
    display: flex;
    gap: 2rem;
    flex: 1;
    justify-content: center;
  }

  /* Specifications Sections */
  .specifications-section {
    flex: 1;
    text-align: center;
  }

  .specifications-section h4 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #333;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e0e0e0;
  }

  .technical-specs h4 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #555;
    border-bottom: 1px solid #e0e0e0;
  }

  .specs-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }

  .spec-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
  }

  .spec-item:last-child {
    border-bottom: none;
  }

  .spec-label {
    font-weight: 600;
    color: #555;
    font-size: 1.1rem;
    min-width: 120px;
  }

  .spec-value {
    font-weight: 700;
    color: #111;
    font-size: 1.1rem;
    text-align: right;
    flex: 1;
  }

  /* Technical specs specific styling */
  .technical-list .spec-item {
    padding: 0.5rem 0;
  }

  .technical-list .spec-label {
    font-size: 1rem;
    color: #666;
    font-weight: 500;
  }

  .technical-list .spec-value {
    font-size: 1rem;
    color: #333;
    font-weight: 600;
  }

  /* Responsive Design */
  @media (max-width: 1200px) {
    .product-showcase-container {
      gap: 1.5rem;
    }

    .product-image-section {
      max-width: 400px;
    }

    .specifications-container {
      gap: 1.5rem;
    }
  }

  @media (max-width: 1024px) {
    .product-showcase-container {
      flex-direction: column;
      align-items: center;
      gap: 1.5rem;
    }

    .product-image-section {
      width: 100%;
      max-width: 400px;
    }

    .product-details-section {
      width: 100%;
      max-width: 600px;
    }

    .specifications-container {
      flex-direction: column;
      gap: 1.5rem;
    }
  }

  @media (max-width: 768px) {
    .details-modal-content {
      width: 98vw;
      max-height: 95vh;
      margin: 2vh auto;
    }

    .product-showcase-container {
      padding: 1.5rem;
      gap: 1.5rem;
    }

    .image-container {
      height: 300px;
    }

    .modal-product-name {
      font-size: 2.2rem !important;
    }

    .spec-item {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.25rem;
    }

    .spec-value {
      text-align: left;
    }

    .specifications-section h4 {
      font-size: 1.1rem !important;
    }

    .technical-specs h4 {
      font-size: 1rem !important;
    }

    .spec-label,
    .spec-value,
    .technical-list .spec-label,
    .technical-list .spec-value {
      font-size: 1rem !important;
    }

    .thumbnail-gallery img {
      width: 50px;
      height: 50px;
    }
  }

  @media (max-width: 480px) {
    .product-showcase-container {
      padding: 1rem;
    }

    .modal-product-name {
      font-size: 2rem !important;
    }

    .specs-list {
      gap: 0.75rem;
    }

    .spec-item {
      padding: 0.5rem 0;
    }
  }

  /* --- Enhanced Customer Care Modal UI --- */
  .care-modal {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(2px);
    transition: opacity .25s ease, visibility .25s ease;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }

  .care-modal .modal-content.care-modal-content {
    border-radius: 18px;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
    padding: 20px 22px;
    max-width: 560px;
    width: 92vw;
    animation: careModalIn .22s ease-out;
  }

  @keyframes careModalIn {
    from { transform: translateY(10px) scale(.98); opacity: .85; }
    to   { transform: translateY(0) scale(1); opacity: 1; }
  }

  .care-modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 6px 14px 6px;
    border-bottom: 1px solid #efefef;
  }

  .care-modal-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #000;
    color: #fff;
    box-shadow: 0 8px 18px rgba(0,0,0,0.18);
  }

  .care-modal-title {
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: .2px;
    color: #111;
    margin: 0;
  }

  .care-modal-close {
    margin-left: auto;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f1f2f4;
    color: #333;
    cursor: pointer;
    transition: all .18s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .care-modal-close:hover {
    background: #000;
    color: #fff;
    transform: scale(1.06);
    box-shadow: 0 10px 24px rgba(0,0,0,0.2);
  }

  .care-modal-body {
    padding: 16px 6px 4px 6px;
    color: #333;
    line-height: 1.6;
    }

    .care-modal-intro {
      text-align: center;
      color: #555;
      margin-bottom: 24px;
      font-size: 1.05rem;
    }

    /* Contact Us Modal */
    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
    }
    .contact-card {
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      transition: all 0.3s ease;
      text-decoration: none;
      color: #333;
    }
    .contact-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.08);
      border-color: #007dd1;
      background: #fff;
    }
    .contact-card i {
      font-size: 2rem;
      color: #007dd1;
      margin-bottom: 12px;
    }
    .contact-card h4 {
      margin: 0 0 4px 0;
      font-size: 1rem;
      font-weight: 600;
    }
    .contact-card p {
      margin: 0;
      font-size: 0.9rem;
      color: #666;
    }

    /* How to Order Modal */
    .enhanced-steps {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .enhanced-steps li {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 16px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    .enhanced-steps li:last-child {
      border-bottom: none;
    }
    .step-icon {
      flex-shrink: 0;
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: #e9f5ff;
      color: #007dd1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }
    .step-content strong {
      display: block;
      font-weight: 600;
      color: #333;
      margin-bottom: 4px;
    }
    .step-content {
      color: #555;
    }

    /* Policy Modals (Returns & Warranty) */
    .policy-section {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 16px;
      border-radius: 12px;
      background: #f8f9fa;
      margin-bottom: 12px;
      border: 1px solid #e9ecef;
    }
    .policy-section.important-note {
      background: #fff9e6;
      border-color: #ffc107;
    }
    .policy-icon {
      flex-shrink: 0;
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: #e9f5ff;
      color: #007dd1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
    }
    .policy-section.important-note .policy-icon {
      background: #ffc107;
      color: #fff;
    }
    .policy-content h4 {
      margin: 0 0 8px 0;
      font-weight: 600;
      color: #333;
    }
    .policy-content p, .policy-content ul {
      margin: 0;
      padding: 0;
      color: #555;
      line-height: 1.5;
    }
    .policy-content ul {
      list-style: none;
      margin-top: 8px;
    }
    .policy-content ul li {
      padding-left: 20px;
      position: relative;
      margin-bottom: 4px;
    }
    .policy-content ul li i {
      position: absolute;
      left: 0;
      top: 4px;
      color: #007dd1;
      font-size: 0.8rem;
  }

  .contact-info p,
  .order-steps p,
  .returns-info p {
    margin: 8px 0;
  }

  .order-steps p::before {
    content: "✔";
    display: inline-block;
    margin-right: 8px;
    color: #000;
    font-weight: 700;
  }

  .care-modal .primary-action {
    display: inline-block;
    margin-top: 10px;
    background: #000;
    color: #fff;
    border: 1px solid #000;
    padding: 10px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
  }

  .care-modal .primary-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    background: #111;
  }

  @media (max-width: 420px) {
    .care-modal .modal-content.care-modal-content {
      padding: 16px 16px;
      border-radius: 14px;
    }
    .care-modal-icon { width: 36px; height: 36px; }
    .care-modal-title { font-size: 1.05rem; }
  }
</style>
<style>
  /* OVERRIDES: Remove transitions and enforce 2-column left-aligned specs */
  #detailsModal, #detailsModal * { transition: none !important; }
  #detailsModal .thumbnail-gallery img:hover { transform: none !important; box-shadow: none !important; }
  #detailsModal .thumbnail-gallery img { transition: none !important; }

  #detailsModal .specifications-container {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    align-items: start !important;
    justify-content: stretch !important;
    gap: 1.5rem !important;
  }
  #detailsModal .specifications-section { text-align: left !important; }
  #detailsModal .specs-list { align-items: stretch !important; }
  #detailsModal .spec-item { justify-content: flex-start !important; }
  #detailsModal .spec-label { text-align: left !important; }
  #detailsModal .spec-value { text-align: left !important; }

  /* Responsive: stack columns on very small screens */
  @media (max-width: 640px) {
    #detailsModal .specifications-container {
      grid-template-columns: 1fr !important;
    }
  }
</style>
<style>
  /* OVERRIDES: Make all specs bold */
  #detailsModal .specifications-section h4,
  #detailsModal .spec-label,
  #detailsModal .spec-value { font-weight: 700 !important; }
</style>
<style>
  /* OVERRIDES: Make Technical Specifications match Basic Specifications style */
  #detailsModal .technical-specs h4 {
    font-size: 1.3rem !important;
    font-weight: 700 !important;
    color: #333 !important;
    margin: 0 0 1rem 0 !important;
    padding-bottom: 0.5rem !important;
    border-bottom: 2px solid #e0e0e0 !important;
  }
</style>
<style>
  /* Kiosk Carousel Arrow Button Robust Fix */
.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
  background: rgba(0,0,0,0.7);
  color: #fff;
  border: none;
  border-radius: 50%;
  z-index: 10001 !important;
  cursor: pointer;
  opacity: 1 !important;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2em;
  transition: background 0.2s, color 0.2s;
  box-shadow: 0 4px 16px rgba(0,0,0,0.18);
}
.carousel-prev { left: 10px; }
.carousel-next { right: 10px; }
.carousel-btn:hover {
  background: #007dd1;
  color: #fff;
  opacity: 1 !important;
}
.carousel-btn:active {
  background: #111;
}
.carousel-btn[disabled] {
  opacity: 0.4 !important;
  pointer-events: none;
}
.carousel-container {
  position: relative;
}
</style>
<style>
  /* OVERRIDES: Force details modal to be centered immediately (prevent bottom flash) */
  #detailsModal .modal-content {
    margin: 0 !important;
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    animation: none !important;
  }
  #detailsModal.show .modal-content {
    opacity: 1 !important;
  }
</style>
