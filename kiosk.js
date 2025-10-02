/**
 * Perform a global product search with validation
 * @param {string} term The search term
 */
function performGlobalSearch(term) {
    if (!term?.trim()) return;
    window.location.href = `kiosk.php?search=${encodeURIComponent(term.trim())}`;
}

/**
 * Initialize all search functionality
 * @param {string} inputId - The ID of the search input element
 * @param {string} buttonId - The ID of the search button element
 * @param {string} suggestionsId - The ID of the suggestions container element
 * @param {boolean} isStandalone - Whether this is the standalone search or header search
 */
function initializeSearch(inputId, buttonId, suggestionsId, isStandalone = false) {
    const searchInput = document.getElementById(inputId);
    const searchButton = document.getElementById(buttonId);
    const suggestionsContainer = document.getElementById(suggestionsId);
    let debounceTimer;
    let mouseOverSuggestions = false;

    if (!searchInput || !searchButton || !suggestionsContainer) {
        console.error(`Search elements not found: ${inputId}, ${buttonId}, ${suggestionsId}`);
        return;
    }

    // This function performs the main search
    const performSearch = (term) => {
        suggestionsContainer.style.display = 'none'; // Hide suggestions
        if (!term || term.trim().length < 2) return;

        // If this is the standalone search, redirect to the dedicated search results page.
        if (isStandalone) {
            window.location.href = `search_results.php?q=${encodeURIComponent(term.trim())}`;
            return;
        }
        
        if (typeof fetchAndRenderProducts === 'function' && typeof state === 'object') {
            state.search = term.trim();
            state.page = 1; // Reset to first page on new search
            fetchAndRenderProducts();
        } else {
            // Fallback for other pages or standalone search
            window.location.href = `kiosk.php?search=${encodeURIComponent(term.trim())}`;
        }
    };

    // This function fetches and displays live suggestions
    const fetchSuggestions = (query) => {
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        fetch(`api_search_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    renderSuggestions(data.products);
                } else {
                    suggestionsContainer.innerHTML = `<div class="suggestion-item no-results">No suggestions found.</div>`;
                    suggestionsContainer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Suggestion fetch error:', error);
                suggestionsContainer.style.display = 'none';
            });
    };

    // Render suggestions in the dropdown
    const renderSuggestions = (products) => {
        suggestionsContainer.innerHTML = '';
        
        products.forEach(product => {
            const name = product.brand + ' ' + product.model;
            const price = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'PHP'
            }).format(product.selling_price);
            const image = product.image1 ? `uploads/${product.image1}` : 'images/no-image.png';
            
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.setAttribute('data-id', product.product_id);
            item.innerHTML = `
                <img src="${image}" alt="${name}" class="suggestion-image">
                <div class="suggestion-info">
                    <strong class="suggestion-name">${name}</strong>
                    <span class="suggestion-price">${price}</span>
                </div>
            `;

            // When a suggestion is clicked, open the details modal directly
            item.addEventListener('click', () => {
                fetch('api_get_product_details.php?product_id=' + encodeURIComponent(product.product_id))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.product) {
                            if (window.populateAndShowDetailsModal) {
                                window.populateAndShowDetailsModal(data.product);
                            } else {
                                console.error('populateAndShowDetailsModal function not found');
                            }
                        } else {
                            console.error('Failed to fetch product details:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching product details:', error);
                    });
                
                suggestionsContainer.style.display = 'none';
                searchInput.value = name; // Optionally fill the search bar
            });
            suggestionsContainer.appendChild(item);
        });
        suggestionsContainer.style.display = 'block';
    };

    // Event listeners
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = searchInput.value;
        debounceTimer = setTimeout(() => fetchSuggestions(query), 200);
    });

    searchInput.addEventListener('focus', function() {
        if (searchInput.value.length > 1) fetchSuggestions(searchInput.value);
    });

    // Track mouse over suggestion box
    suggestionsContainer.addEventListener('mouseenter', function() {
        mouseOverSuggestions = true;
    });
    suggestionsContainer.addEventListener('mouseleave', function() {
        mouseOverSuggestions = false;
    });

    // Hide suggestions only if not hovering over them
    searchInput.addEventListener('blur', function() {
        setTimeout(function() {
            if (!mouseOverSuggestions) {
                suggestionsContainer.style.display = 'none';
            }
        }, 120);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('mousedown', function(e) {
        if (!suggestionsContainer.contains(e.target) && e.target !== searchInput) {
            suggestionsContainer.style.display = 'none';
        }
    });

    // Search button click
    searchButton.addEventListener('click', function() {
        performSearch(searchInput.value);
    });

    // Enter key press
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            performSearch(searchInput.value);
            suggestionsContainer.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM Content Loaded'); // Debug log

  // Central state for filters
  const state = {
    category: 'all',
    search: '',
    sort: 'default',
    page: 1
  };

  // Initialize all UI components
  initModals();
  initAjaxFiltering();
  initSearch();
  initVideoControls();
  initScrollToTop();
  initProductDetails();
  initProductImageHover();
  initCollectionsEnhancements();
  initCarousel();
  initProfileIcon();
  initCartIcon();

  // Initial load
  const initialParams = new URLSearchParams(window.location.search);
  state.category = initialParams.get('category') || 'all';
  state.search = initialParams.get('search') || '';
  state.sort = initialParams.get('sort') || 'default';
  state.page = parseInt(initialParams.get('page')) || 1;

  // Update UI to match initial state (e.g., active buttons)
  document.querySelectorAll('.category-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-category') === state.category);
  });

  fetchAndRenderProducts();

  // Handle browser back/forward buttons
  window.addEventListener('popstate', (event) => {
    if (event.state) {
      Object.assign(state, event.state);
      // Update UI to reflect state (e.g., active buttons)
      // ...
      fetchAndRenderProducts(false); // false to not push state again
    } else {
      // Handle initial state on back button
      state.category = 'all';
      state.search = '';
      state.sort = 'default';
      state.page = 1;
      fetchAndRenderProducts(false);
    }
  });

  // --- AJAX and Rendering Functions ---

  async function fetchAndRenderProducts(updateHistory = true) {
    const productGrid = document.getElementById('productGrid');
    if (!productGrid) return;

    // Show loading spinner
    productGrid.innerHTML = '<div class="loading-spinner-container"><div class="loading-spinner"></div></div>';

    const params = new URLSearchParams(state);
    try {
      const response = await fetch(`api_get_products.php?${params.toString()}`);
      if (!response.ok) throw new Error('Network response was not ok');
      
      const data = await response.json();

      if (data.success) {
        renderProducts(data.products);
        // renderPagination(data.pagination); // Pagination can be added here
        if (updateHistory) {
          updateURL();
        }
      } else {
        productGrid.innerHTML = '<p class="no-results">Error loading products.</p>';
      }
    } catch (error) {
      console.error('Fetch error:', error);
      productGrid.innerHTML = '<p class="no-results">Could not load products. Please try again later.</p>';
    }
  }

  function renderProducts(products) {
    const productGrid = document.getElementById('productGrid');
    productGrid.innerHTML = ''; // Clear existing content

    if (products.length === 0) {
      productGrid.innerHTML = '<p class="no-results" style="width: 100%; text-align: center;">No products found for the selected criteria.</p>';
      return;
    }

    products.forEach(product => {
      const stockStatus = getStockStatus(product.stock_quantity);
      const price = product.selling_price ? `₱${parseFloat(product.selling_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : 'Price not available';
      const imagePath = product.image1 || 'images/default.png';
      const brandModel = `${product.brand || ''} ${product.model || ''}`.trim();

      const cardHTML = `
        <div class="card" data-product-id="${product.product_id}" data-category="${(product.product || '').toLowerCase()}" data-brand="${(product.brand || '').toLowerCase()}" data-model="${(product.model || '').toLowerCase()}" data-description="${product.description || ''}" data-price="${price}" data-images='${JSON.stringify([product.image1, product.image2, product.image3, product.image4, product.image5, product.image6, product.image7, product.image8].filter(Boolean))}' data-water-resistance="${product.water_resistance || ''}" data-display-output="${product.display_output || ''}" data-screen-size="${product.screen_size || ''}" data-charging-port="${product.charging_port || ''}" data-material="${product.material || ''}" data-chip="${product.chip || ''}" data-camera-feature="${product.camera_feature || ''}">
          <div class="image-wrapper" style="height: 350px; background-color: #f9fafb; padding: 1rem; border-radius: 0.75rem;">
            <img src="${imagePath}" alt="${product.product}" style="height: 100%; width: 100%; object-fit: contain;">
          </div>
          <h3 class="card-link" style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-top: 1rem;">${brandModel}</h3>
          <p class="card-price" style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;">${price}</p>
          <div class="status-badge" style="margin-top: 1rem; padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 600;">${stockStatus.text}</div>
          <div class="card-buttons" style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
            <button class="details-btn" style="background-color: #1f2937; color: #ffffff; border-radius: 8px; padding: 10px 20px; font-weight: 600;">See Detail</button>
          </div>
        </div>
      `;
      productGrid.insertAdjacentHTML('beforeend', cardHTML);
    });

    // Re-initialize event listeners for new cards
    initCarousel(); // Re-init carousel logic
    centerProductsIfFit();
  }

  function getStockStatus(stock) {
    const stockNum = parseInt(stock, 10);
    if (stockNum <= 0) return { text: 'Out of Stock', class: 'out-of-stock' };
    if (stockNum <= 5) return { text: `Low Stock (${stockNum})`, class: 'low-stock' };
    return { text: 'In Stock', class: 'in-stock' };
  }

  function updateURL() {
    const params = new URLSearchParams();
    if (state.category && state.category !== 'all') params.set('category', state.category);
    if (state.search) params.set('search', state.search);
    if (state.sort && state.sort !== 'default') params.set('sort', state.sort);
    if (state.page > 1) params.set('page', state.page);

    const newUrl = `${window.location.pathname}?${params.toString()}`;
    history.pushState(state, '', newUrl);
  }
  
  // Initial UI setup
  centerProductsIfFit();

  // === COMPONENT INITIALIZATION FUNCTIONS ===

  /**
   * Center products if they fit in the container
   */
  function centerProductsIfFit() {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    setTimeout(() => {
      // Get visible cards
      const productCards = productGridContainer.querySelectorAll('.card');
      const visibleCards = Array.from(productCards).filter(card => card.style.display !== 'none');
      
      // Calculate dimensions
      const containerWidth = productGridContainer.clientWidth;
      const cardWidth = visibleCards.length > 0 ? visibleCards[0].offsetWidth + 30 : 0; // card width + gap
      const totalWidth = cardWidth * visibleCards.length;

      if (totalWidth <= containerWidth) {
        // Center the products if they fit
        productGridContainer.style.justifyContent = 'center';
        productGridContainer.style.overflowX = 'hidden';
        productGridContainer.scrollLeft = 0;
      } else {
        // Use scroll if they don't fit
        productGridContainer.style.justifyContent = 'flex-start';
        productGridContainer.style.overflowX = 'auto';
      }
      
      // Check if carousel is needed
      const carouselContainer = document.querySelector('.carousel-container');
      const prevBtn = document.getElementById('carouselPrev');
      const nextBtn = document.getElementById('carouselNext');
      
      if (carouselContainer && prevBtn && nextBtn) {
        if (totalWidth <= containerWidth) {
          // Hide carousel buttons if products fit
          prevBtn.style.display = 'none';
          nextBtn.style.display = 'none';
        } else {
          // Show carousel buttons if products don't fit
          prevBtn.style.display = 'flex';
          nextBtn.style.display = 'flex';
        }
      }

    }, 100);
  }

  /**
   * Initialize all modal dialogs
   */
  function initModals() {
    console.log('Initializing modals'); // Debug log

    // Call modal specific handlers
    const callModal = document.getElementById('callModal');
    const callIcon = document.getElementById('callIcon');
    const closeCallModal = document.getElementById('closeCallModal');

    if (callIcon && callModal) {
      callIcon.addEventListener('click', () => {
        openModal(callModal);
      });
    }

    if (closeCallModal && callModal) {
      closeCallModal.addEventListener('click', () => {
        closeModal(callModal);
      });
    }

    // Customer care modals are handled by kioskmodals.php
    // No initialization needed here to prevent conflicts
  }

  /**
   * Open a modal with animation
   * @param {HTMLElement} modal - The modal element to open
   */
  function openModal(modal) {
    if (!modal) {
      console.error('Modal element not found');
      return;
    }
    
    // Skip customer care modals - they are handled by kioskmodals.php
    if (modal.classList.contains('care-modal')) {
      console.log('Skipping customer care modal - handled by kioskmodals.php');
      return;
    }
    
    console.log('Opening modal:', modal); // Debug log
    
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
    
    // Show modal centered immediately without jump (no transition)
    modal.style.display = 'block';
    modal.classList.add('show');
    
    // Focus trap
    const focusableElements = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (modal && typeof modal.focus === 'function') {
      try { modal.setAttribute('tabindex','-1'); modal.focus({ preventScroll: true }); } catch (e) {}
    }
    if (focusableElements.length) {
      const firstFocusable = focusableElements[0];
      const lastFocusable = focusableElements[focusableElements.length - 1];
      
      // Focus first element without scrolling viewport
      try { firstFocusable.focus({ preventScroll: true }); } catch (e) { try { firstFocusable.focus(); } catch (e2) {} }
      
      // Handle tab key
      modal.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
          if (e.shiftKey) {
            if (document.activeElement === firstFocusable) {
              e.preventDefault();
              lastFocusable.focus();
            }
          } else {
            if (document.activeElement === lastFocusable) {
              e.preventDefault();
              firstFocusable.focus();
            }
          }
        }
      });
    }

    // Add ESC key handler
    const escHandler = function(e) {
      if (e.key === 'Escape') {
        closeModal(modal);
      }
    };
    document.addEventListener('keydown', escHandler);
    modal._escHandler = escHandler;

    // Add click outside handler
    const clickOutsideHandler = function(e) {
      if (e.target === modal) {
        closeModal(modal);
      }
    };
    modal.addEventListener('click', clickOutsideHandler);
    modal._clickOutsideHandler = clickOutsideHandler;
  }

  /**
   * Close a modal with animation
   * @param {HTMLElement} modal - The modal element to close
   */
  function closeModal(modal) {
    if (!modal) {
      console.error('Modal element not found');
      return;
    }
    
    // Skip customer care modals - they are handled by kioskmodals.php
    if (modal.classList.contains('care-modal')) {
      console.log('Skipping customer care modal - handled by kioskmodals.php');
      return;
    }
    
    console.log('Closing modal:', modal); // Debug log
    
    // Remove show class for animation
    modal.classList.remove('show');
    
    // Remove event listeners
    if (modal._escHandler) {
      document.removeEventListener('keydown', modal._escHandler);
      delete modal._escHandler;
    }
    if (modal._clickOutsideHandler) {
      modal.removeEventListener('click', modal._clickOutsideHandler);
      delete modal._clickOutsideHandler;
    }
    
    // Wait for animation to complete
    setTimeout(() => {
      modal.style.display = 'none';
      // Restore body scrolling
      document.body.style.overflow = '';
    }, 300);
  }

  /**
   * Initialize category filtering buttons
   */
  function initAjaxFiltering() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        state.category = this.getAttribute('data-category');
        state.page = 1;

        categoryButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        fetchAndRenderProducts();
      });
    });
  }

  /**
   * Show no results message when filtering returns no products
   */
  function showNoResultsMessage(show, category) {
    const productGrid = document.getElementById('productGrid');
    if (!productGrid) return;
    
    // Remove existing no results message
    const existingMessage = productGrid.querySelector('.no-results-message');
    if (existingMessage) {
      existingMessage.remove();
    }
    
    if (show) {
      const noResultsMessage = document.createElement('div');
      noResultsMessage.className = 'no-results-message';
      noResultsMessage.style.cssText = `
        width: 100%;
        text-align: center;
        padding: 60px 20px;
        color: #666;
        font-size: 18px;
        font-weight: 500;
        background: #f8f9fa;
        border-radius: 16px;
        margin: 20px 0;
        border: 2px dashed #ddd;
      `;
      
      noResultsMessage.innerHTML = `
        <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px; display: block;"></i>
        <h3 style="margin: 0 0 10px 0; color: #333;">No products found</h3>
        <p style="margin: 0; color: #666;">
          No products found in the "${category}" category.
          <br>Try selecting a different category or browse all products.
        </p>
        <button class="show-all-btn" style="
          margin-top: 20px;
          padding: 12px 24px;
          background: #111;
          color: #fff;
          border: none;
          border-radius: 8px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
        ">Show All Products</button>
      `;
      
      productGrid.appendChild(noResultsMessage);
      
      // Add click handler to "Show All Products" button
      const showAllBtn = noResultsMessage.querySelector('.show-all-btn');
      if (showAllBtn) {
        showAllBtn.addEventListener('click', () => {
          const allBtn = document.querySelector('.category-btn[data-category="all"]');
          if (allBtn) {
            allBtn.click();
          }
        });
        
        // Add hover effect
        showAllBtn.addEventListener('mouseenter', () => {
          showAllBtn.style.background = '#333';
          showAllBtn.style.transform = 'translateY(-2px)';
        });
        
        showAllBtn.addEventListener('mouseleave', () => {
          showAllBtn.style.background = '#111';
          showAllBtn.style.transform = 'translateY(0)';
        });
      }
    }
  }

  /**
   * Update carousel controls based on visible products
   */
  function updateCarouselControls() {
    const productGrid = document.getElementById('productGrid');
    const carouselContainer = document.querySelector('.carousel-container');
    const prevBtn = document.getElementById('carouselPrev');
    const nextBtn = document.getElementById('carouselNext');
    
    if (!productGrid || !carouselContainer || !prevBtn || !nextBtn) return;
    
    const visibleCards = Array.from(productGrid.querySelectorAll('.card')).filter(card => 
      card.style.display !== 'none'
    );
    
    // Calculate if carousel is needed
    const containerWidth = productGrid.clientWidth;
    const cardWidth = visibleCards.length > 0 ? visibleCards[0].offsetWidth + 30 : 0;
    const totalWidth = cardWidth * visibleCards.length;
    
    if (totalWidth <= containerWidth) {
      // Hide carousel buttons if products fit
      prevBtn.style.display = 'none';
      nextBtn.style.display = 'none';
    } else {
      // Show carousel buttons if products don't fit
      prevBtn.style.display = 'flex';
      nextBtn.style.display = 'flex';
    }
  }

  /**
   * Initialize search functionality
   */
  function initSearch() {
      initializeSearch('searchInput', 'searchButton', 'searchSuggestions');
      // Also initialize the standalone search bar if it exists on the page
      initializeSearch('standaloneSearchInput', 'standaloneSearchButton', 'standaloneSearchSuggestions', true);
  }



  
  // Update resetScrollPosition to call updateCarouselButtons
  function resetScrollPosition() {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    setTimeout(() => {
      // Get visible cards
      const productCards = productGridContainer.querySelectorAll('.card');
      const visibleCards = Array.from(productCards).filter(card => card.style.display !== 'none');
      
      // Calculate dimensions
      const containerWidth = productGridContainer.clientWidth;
      const cardWidth = visibleCards.length > 0 ? visibleCards[0].offsetWidth + 10 : 0; // card width + gap
      const totalWidth = cardWidth * visibleCards.length;

      if (totalWidth > containerWidth) {
        // Scroll to first visible card
        const firstVisibleCard = visibleCards[0];
        const scrollLeftValue = firstVisibleCard ? 
          firstVisibleCard.offsetLeft : 0;
        
        productGridContainer.scrollLeft = scrollLeftValue > 0 ? scrollLeftValue : 0;
        productGridContainer.style.justifyContent = 'flex-start';
        productGridContainer.style.overflowX = 'auto';
      } else {
        productGridContainer.scrollLeft = 0;
        productGridContainer.style.justifyContent = 'center';
        productGridContainer.style.overflowX = 'hidden';
      }
      

    }, 0);
  }
  
  /**
   * Initialize video player controls
   */
  function initVideoControls() {
    const video = document.getElementById('video0');
    if (!video) return;
    
    const playBtn = document.querySelector('.video-control-btn.play');
    const pauseBtn = document.querySelector('.video-control-btn.pause');
    const muteBtn = document.querySelector('.video-control-btn.mute');
    
    if (playBtn) playBtn.addEventListener('click', () => video.play());
    if (pauseBtn) pauseBtn.addEventListener('click', () => video.pause());
    if (muteBtn) muteBtn.addEventListener('click', () => video.muted = !video.muted);
  }

  /**
   * Initialize scroll-to-top button
   */
  function initScrollToTop() {
    // Create scroll-to-top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.innerHTML = `
      <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true" focusable="false">
        <circle cx="14" cy="14" r="13" fill="none"/>
        <polyline points="8,16 14,10 20,16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `;
    scrollTopBtn.style.filter = 'drop-shadow(0 2px 6px rgba(0,0,0,0.25))';
    scrollTopBtn.id = 'scrollTopBtn';
    scrollTopBtn.setAttribute('aria-label', 'Scroll to top');
    scrollTopBtn.style.position = 'fixed';
    scrollTopBtn.style.bottom = '32px';
    scrollTopBtn.style.right = '32px';
    scrollTopBtn.style.width = '48px';
    scrollTopBtn.style.height = '48px';
    scrollTopBtn.style.padding = '0';
    scrollTopBtn.style.fontSize = '2rem';
    scrollTopBtn.style.fontWeight = 'bold';
    scrollTopBtn.style.border = '2px solid #fff';
    scrollTopBtn.style.borderRadius = '50%';
    scrollTopBtn.style.background = '#111';
    scrollTopBtn.style.color = '#fff';
    scrollTopBtn.style.cursor = 'pointer';
    scrollTopBtn.style.display = 'none';
    scrollTopBtn.style.zIndex = '10000';
    scrollTopBtn.style.boxShadow = '0 4px 16px rgba(0,0,0,0.25)';
    scrollTopBtn.style.opacity = '0';
    scrollTopBtn.style.transition = 'opacity 0.4s, background 0.2s, color 0.2s, border 0.2s';

    // Hover/focus effect: invert colors
    scrollTopBtn.addEventListener('mouseenter', () => {
      scrollTopBtn.style.background = '#fff';
      scrollTopBtn.style.color = '#111';
      scrollTopBtn.style.border = '2px solid #111';
    });
    scrollTopBtn.addEventListener('mouseleave', () => {
      scrollTopBtn.style.background = '#111';
      scrollTopBtn.style.color = '#fff';
      scrollTopBtn.style.border = '2px solid #fff';
    });
    scrollTopBtn.addEventListener('focus', () => {
      scrollTopBtn.style.background = '#fff';
      scrollTopBtn.style.color = '#111';
      scrollTopBtn.style.border = '2px solid #111';
    });
    scrollTopBtn.addEventListener('blur', () => {
      scrollTopBtn.style.background = '#111';
      scrollTopBtn.style.color = '#fff';
      scrollTopBtn.style.border = '2px solid #fff';
    });

    document.body.appendChild(scrollTopBtn);

    // Show button when scrolled down
    window.addEventListener('scroll', function () {
      if (window.scrollY > 200) {
        scrollTopBtn.style.display = 'block';
        setTimeout(() => { scrollTopBtn.style.opacity = '1'; }, 10);
      } else {
        scrollTopBtn.style.opacity = '0';
        setTimeout(() => { scrollTopBtn.style.display = 'none'; }, 400);
      }
    });

    // Smooth scroll to top on button click
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      scrollTopBtn.blur();
    });

    // Keyboard accessibility: Enter/Space triggers scroll
    scrollTopBtn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        scrollTopBtn.blur();
      }
    });
  }

  /**
   * Initialize product details and reservation functionality
   */
  function initProductDetails() {
    // Make showProductDetails globally accessible
    window.showProductDetails = async (productId) => {
        try {
            const response = await fetch(`api_get_product_details.php?product_id=${productId}`);
            const data = await response.json();
            if (data.success && data.product) {
                populateAndShowDetailsModal(data.product);
            }
        } catch (error) { console.error('Error fetching product details:', error); }
    };
    console.log('Initializing product details');
    
    const detailsModal = document.getElementById('detailsModal');
    const reserveModal = document.getElementById('reserveModal');
    const successModal = document.getElementById('successModal');
    
    if (!detailsModal || !reserveModal || !successModal) {
      console.error('Required modals not found');
      return;
    }
    
    const modalImage = detailsModal.querySelector('.modal-product-image');
    const modalName = detailsModal.querySelector('.modal-product-name');
    const modalDescription = detailsModal.querySelector('.modal-product-description');
    const modalPrice = detailsModal.querySelector('.modal-product-price');
    const modalBrand = detailsModal.querySelector('.modal-product-brand');
    const modalModel = detailsModal.querySelector('.modal-product-model');
    const modalStorage = detailsModal.querySelector('.modal-product-storage');
    const modalWaterResistance = detailsModal.querySelector('.modal-product-water-resistance');
    const modalDisplayOutput = detailsModal.querySelector('.modal-product-display-output');
    const modalScreenSize = detailsModal.querySelector('.modal-product-screen-size');
    const modalChargingPort = detailsModal.querySelector('.modal-product-charging-port');
    const modalMaterial = detailsModal.querySelector('.modal-product-material');
    const modalChip = detailsModal.querySelector('.modal-product-chip');
    const modalCameraFeature = detailsModal.querySelector('.modal-product-camera-feature');
    const closeBtn = detailsModal.querySelector('.close');


    // Close modal when clicking the close button
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        closeModal(detailsModal);
      });
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target === detailsModal) {
        closeModal(detailsModal);
      }
    });

    // Close modal when pressing Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && detailsModal.style.display === 'block') {
        closeModal(detailsModal);
      }
    });

    const productGrid = document.getElementById('productGrid');
    if (!productGrid) {
      console.error('Product grid not found');
      return;
    }

    // Debug: Log the number of product cards found
    const productCards = productGrid.querySelectorAll('.card');
    console.log('Found product cards:', productCards.length);

    // Enhanced event delegation for all product buttons
    productGrid.addEventListener('click', function(e) {
      const target = e.target;
      console.log('Click event on:', target.tagName, target.className);
      
      // Handle Details button clicks
      if (target.classList.contains('details-btn') || target.closest('.details-btn')) {
        e.preventDefault();
        e.stopPropagation();

        const card = target.closest('.card');
        if (!card) {
          console.error('No card found for details button');
          return;
        }

        const name = card.querySelector('.card-link')?.textContent || ''; // Keep for alt text
        const description = card.getAttribute('data-description') || '';
        const image = card.querySelector('img')?.src || '';
        const price = card.getAttribute('data-price') || '';
        const brand = card.getAttribute('data-brand') || '';
        const model = card.getAttribute('data-model') || '';
        const storage = card.getAttribute('data-storage') || '';
        
        // Extract new fields from HTML attributes
        const waterResistance = card.getAttribute('data-water-resistance') || '';
        const displayOutput = card.getAttribute('data-display-output') || '';
        const screenSize = card.getAttribute('data-screen-size') || '';
        const chargingPort = card.getAttribute('data-charging-port') || '';
        const material = card.getAttribute('data-material') || '';
        const chip = card.getAttribute('data-chip') || '';
        const cameraFeature = card.getAttribute('data-camera-feature') || '';
        
        const imagesJson = card.getAttribute('data-images') || '[]';

        let images = [];
        try { 
          images = JSON.parse(imagesJson); 
        } catch { 
          images = [image]; 
        }
        if (!Array.isArray(images) || images.length === 0) images = [image];

        // Use the globally exposed function
        if (typeof window.showProductDetails === 'function') {
            window.showProductDetails(card.getAttribute('data-product-id'));
        }

        return;
      }

      // Handle Reserve button clicks
      if (target.classList.contains('reserve-btn') || target.closest('.reserve-btn')) {
        e.preventDefault();
        e.stopPropagation();
        
        const card = target.closest('.card');
        if (!card) {
          console.error('No card found for reserve button');
          return;
        }

        console.log('Reserve button clicked for product:', card.getAttribute('data-product-id'));

        const productData = {
          product_id: card.getAttribute('data-product-id'),
          name: card.querySelector('.card-link')?.textContent || '',
          image: card.querySelector('img')?.src || '',
          price: card.getAttribute('data-price') || '',
          brand: card.getAttribute('data-brand') || '',
          model: card.getAttribute('data-model') || '',
          category: card.getAttribute('data-category') || ''
        };

        // Update modal content
        const modalProductImage = reserveModal.querySelector('.modal-product-image');
        const modalProductName = reserveModal.querySelector('.modal-product-name');
        const modalProductBrand = reserveModal.querySelector('.modal-product-brand');
        const modalProductModel = reserveModal.querySelector('.modal-product-model');
        const modalProductPrice = reserveModal.querySelector('.modal-product-price');
        const modalProductId = reserveModal.querySelector('.modal-product-id span');

        if (modalProductImage) modalProductImage.src = productData.image;
        if (modalProductName) modalProductName.textContent = productData.name;
        if (modalProductBrand) modalProductBrand.textContent = productData.brand;
        if (modalProductModel) modalProductModel.textContent = productData.model;
        if (modalProductPrice) modalProductPrice.textContent = productData.price;
        if (modalProductId) modalProductId.textContent = productData.product_id;

        // Store product data in sessionStorage
        const productDetails = {
          product_id: productData.product_id,
          name: productData.name,
          image: productData.image,
          price: productData.price,
          brand: productData.brand,
          model: productData.model,
          category: productData.category,
          selected: true,
          timestamp: new Date().getTime()
        };
        sessionStorage.setItem('selectedProduct', JSON.stringify(productDetails));

        // Open the reserve modal
        openModal(reserveModal);
        return;
      }
    });

    // Add event listeners for reserve modal buttons
    const confirmBtn = reserveModal.querySelector('.confirm-btn');
    const cancelBtn = reserveModal.querySelector('.cancel-btn');
    const reserveCloseBtn = reserveModal.querySelector('.close');

    if (confirmBtn) {
      confirmBtn.addEventListener('click', () => {
        closeModal(reserveModal);
        openModal(successModal);
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        closeModal(reserveModal);
      });
    }

    if (reserveCloseBtn) {
      reserveCloseBtn.addEventListener('click', () => {
        closeModal(reserveModal);
      });
    }

    // Add event listeners for success modal
    const successCloseBtn = successModal.querySelector('.close');
    const successContinueBtn = successModal.querySelector('.confirm-btn');

    if (successCloseBtn) {
      successCloseBtn.addEventListener('click', () => {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      });
    }

    if (successContinueBtn) {
      successContinueBtn.addEventListener('click', () => {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      });
    }

    // Close success modal when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target === successModal) {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      }
    });

    // Additional safety: Re-attach event listeners after any dynamic content changes
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
          // Check if new product cards were added
          const hasNewCards = Array.from(mutation.addedNodes).some(node => 
            node.nodeType === Node.ELEMENT_NODE && 
            (node.classList?.contains('card') || node.querySelector?.('.card'))
          );
          
          if (hasNewCards) {
            console.log('New product cards detected, ensuring event listeners are attached');
            // The event delegation above should handle this automatically
          }
        }
      });
    });

    // Start observing the product grid for changes
    observer.observe(productGrid, {
      childList: true,
      subtree: true
    });

    // Fallback: Direct event listeners for buttons (in case event delegation fails)
    function attachDirectButtonListeners() {
      const reserveButtons = productGrid.querySelectorAll('.reserve-btn');
      const detailsButtons = productGrid.querySelectorAll('.details-btn');
      
      reserveButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const card = this.closest('.card');
          if (!card) return;
          
          console.log('Direct reserve button clicked for product:', card.getAttribute('data-product-id'));
          
          const productData = {
            product_id: card.getAttribute('data-product-id'),
            name: card.querySelector('.card-link')?.textContent || '',
            image: card.querySelector('img')?.src || '',
            price: card.getAttribute('data-price') || '',
            brand: card.getAttribute('data-brand') || '',
            model: card.getAttribute('data-model') || '',
            category: card.getAttribute('data-category') || ''
          };

          // Update modal content
          const modalProductImage = reserveModal.querySelector('.modal-product-image');
          const modalProductName = reserveModal.querySelector('.modal-product-name');
          const modalProductBrand = reserveModal.querySelector('.modal-product-brand');
          const modalProductModel = reserveModal.querySelector('.modal-product-model');
          const modalProductPrice = reserveModal.querySelector('.modal-product-price');
          const modalProductId = reserveModal.querySelector('.modal-product-id span');

          if (modalProductImage) modalProductImage.src = productData.image;
          if (modalProductName) modalProductName.textContent = productData.name;
          if (modalProductBrand) modalProductBrand.textContent = productData.brand;
          if (modalProductModel) modalProductModel.textContent = productData.model;
          if (modalProductPrice) modalProductPrice.textContent = productData.price;
          if (modalProductId) modalProductId.textContent = productData.product_id;

          // Store product data in sessionStorage
          const productDetails = {
            product_id: productData.product_id,
            name: productData.name,
            image: productData.image,
            price: productData.price,
            brand: productData.brand,
            model: productData.model,
            category: productData.category,
            selected: true,
            timestamp: new Date().getTime()
          };
          sessionStorage.setItem('selectedProduct', JSON.stringify(productDetails));

          // Open the reserve modal
          openModal(reserveModal);
        });
      });
      
      detailsButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const card = this.closest('.card');
          if (!card) return;
          
          console.log('Direct details button clicked for product:', card.getAttribute('data-product-id'));
          
          // Gather product data
          const name = card.querySelector('.card-link')?.textContent || '';
          const description = card.getAttribute('data-description') || '';
          const image = card.querySelector('img')?.src || '';
          const price = card.getAttribute('data-price') || '';
          const brand = card.getAttribute('data-brand') || '';
          const model = card.getAttribute('data-model') || '';
          const storage = card.getAttribute('data-storage') || '';
          
          // Extract new fields from HTML attributes
          const waterResistance = card.getAttribute('data-water-resistance') || '';
          const displayOutput = card.getAttribute('data-display-output') || '';
          const screenSize = card.getAttribute('data-screen-size') || '';
          const chargingPort = card.getAttribute('data-charging-port') || '';
          const material = card.getAttribute('data-material') || '';
          const chip = card.getAttribute('data-chip') || '';
          const cameraFeature = card.getAttribute('data-camera-feature') || '';
          
          const imagesJson = card.getAttribute('data-images') || '[]';

          let images = [];
          try { 
            images = JSON.parse(imagesJson); 
          } catch { 
            images = [image]; 
          }
          if (!Array.isArray(images) || images.length === 0) images = [image];

          // Fill modal fields with animation
          if (modalName) {
            modalName.textContent = name;
            modalName.style.opacity = '0';
            setTimeout(() => modalName.style.opacity = '1', 50);
          }
          if (modalDescription) {
            modalDescription.textContent = description;
            modalDescription.style.opacity = '0';
            setTimeout(() => modalDescription.style.opacity = '1', 100);
          }
          if (modalImage) {
            modalImage.style.opacity = '0';
            setTimeout(() => {
              modalImage.src = images[0] || image;
              modalImage.alt = name;
              modalImage.style.opacity = '1';
            }, 150);
          }
          if (modalPrice) {
            modalPrice.textContent = price;
            modalPrice.style.opacity = '0';
            setTimeout(() => modalPrice.style.opacity = '1', 200);
          }
          if (modalBrand) {
            modalBrand.textContent = brand;
            modalBrand.style.opacity = '0';
            setTimeout(() => modalBrand.style.opacity = '1', 250);
          }
          if (modalModel) {
            modalModel.textContent = model;
            modalModel.style.opacity = '0';
            setTimeout(() => modalModel.style.opacity = '1', 300);
          }
          if (modalStorage) {
            modalStorage.textContent = storage;
            modalStorage.style.opacity = '0';
            setTimeout(() => modalStorage.style.opacity = '1', 350);
          }
          
          // Populate new fields with debug logging
          const modalWaterResistance = detailsModal.querySelector('.modal-product-water-resistance');
          const modalDisplayOutput = detailsModal.querySelector('.modal-product-display-output');
          const modalScreenSize = detailsModal.querySelector('.modal-product-screen-size');
          const modalChargingPort = detailsModal.querySelector('.modal-product-charging-port');
          const modalMaterial = detailsModal.querySelector('.modal-product-material');
          const modalChip = detailsModal.querySelector('.modal-product-chip');
          const modalCameraFeature = detailsModal.querySelector('.modal-product-camera-feature');
          
          // Debug logging
          console.log('=== MODAL DATA DEBUG ===');
          console.log('Extracted data:', {
            waterResistance,
            displayOutput,
            screenSize,
            chargingPort,
            material,
            chip,
            cameraFeature
          });
          console.log('Modal elements found:', {
            modalWaterResistance: !!modalWaterResistance,
            modalDisplayOutput: !!modalDisplayOutput,
            modalScreenSize: !!modalScreenSize,
            modalChargingPort: !!modalChargingPort,
            modalMaterial: !!modalMaterial,
            modalChip: !!modalChip,
            modalCameraFeature: !!modalCameraFeature
          });
          
          if (modalWaterResistance) {
            modalWaterResistance.textContent = waterResistance || 'Not specified';
            modalWaterResistance.style.opacity = '0';
            setTimeout(() => modalWaterResistance.style.opacity = '1', 400);
            console.log('Set water resistance to:', waterResistance);
          } else {
            console.log('Water resistance element not found!');
          }
          
          if (modalDisplayOutput) {
            modalDisplayOutput.textContent = displayOutput || 'Not specified';
            modalDisplayOutput.style.opacity = '0';
            setTimeout(() => modalDisplayOutput.style.opacity = '1', 450);
            console.log('Set display output to:', displayOutput);
          } else {
            console.log('Display output element not found!');
          }
          
          if (modalScreenSize) {
            modalScreenSize.textContent = screenSize || 'Not specified';
            modalScreenSize.style.opacity = '0';
            setTimeout(() => modalScreenSize.style.opacity = '1', 500);
            console.log('Set screen size to:', screenSize);
          } else {
            console.log('Screen size element not found!');
          }
          
          if (modalChargingPort) {
            modalChargingPort.textContent = chargingPort || 'Not specified';
            modalChargingPort.style.opacity = '0';
            setTimeout(() => modalChargingPort.style.opacity = '1', 550);
            console.log('Set charging port to:', chargingPort);
          } else {
            console.log('Charging port element not found!');
          }
          
          if (modalMaterial) {
            modalMaterial.textContent = material || 'Not specified';
            modalMaterial.style.opacity = '0';
            setTimeout(() => modalMaterial.style.opacity = '1', 600);
            console.log('Set material to:', material);
          } else {
            console.log('Material element not found!');
          }
          
          if (modalChip) {
            modalChip.textContent = chip || 'Not specified';
            modalChip.style.opacity = '0';
            setTimeout(() => modalChip.style.opacity = '1', 650);
            console.log('Set chip to:', chip);
          } else {
            console.log('Chip element not found!');
          }
          
          if (modalCameraFeature) {
            modalCameraFeature.textContent = cameraFeature || 'Not specified';
            modalCameraFeature.style.opacity = '0';
            setTimeout(() => modalCameraFeature.style.opacity = '1', 700);
            console.log('Set camera feature to:', cameraFeature);
          } else {
            console.log('Camera feature element not found!');
          }

          // Hide technical specs section if all fields are empty or not specified
          const technicalSpecsSection = detailsModal.querySelector('.technical-specs');
          if (technicalSpecsSection) {
            const values = [
              waterResistance, displayOutput, screenSize, chargingPort, material, chip, cameraFeature
            ];
            const hasAnySpec = values.some(v => v && String(v).trim().toLowerCase() !== 'not specified');
            technicalSpecsSection.style.display = hasAnySpec ? '' : 'none';
          }

          // Update thumbnails
          updateThumbnails(images, name, modalImage);

          // Open the details modal with animation
          openModal(detailsModal);
        });
      });
    }

    // Attach direct listeners after a delay to ensure DOM is ready
    setTimeout(attachDirectButtonListeners, 500);

    // Debug: Test button functionality after a short delay
    setTimeout(() => {
      const testButtons = productGrid.querySelectorAll('.reserve-btn, .details-btn');
      console.log('Test buttons found:', testButtons.length);
      testButtons.forEach((btn, index) => {
        console.log(`Button ${index}:`, btn.className, btn.textContent);
        
        // Add a simple test click handler to verify buttons are clickable
        btn.addEventListener('click', function(e) {
          console.log('Button clicked:', this.className, this.textContent);
        });
      });
    }, 1000);

    // Additional test: Check if buttons are properly positioned
    setTimeout(() => {
      const cards = productGrid.querySelectorAll('.card');
      cards.forEach((card, index) => {
        const reserveBtn = card.querySelector('.reserve-btn');
        const detailsBtn = card.querySelector('.details-btn');
        
        if (reserveBtn) {
          console.log(`Card ${index} reserve button:`, {
            display: getComputedStyle(reserveBtn).display,
            visibility: getComputedStyle(reserveBtn).visibility,
            zIndex: getComputedStyle(reserveBtn).zIndex,
            pointerEvents: getComputedStyle(reserveBtn).pointerEvents
          });
        }
        
        if (detailsBtn) {
          console.log(`Card ${index} details button:`, {
            display: getComputedStyle(detailsBtn).display,
            visibility: getComputedStyle(detailsBtn).visibility,
            zIndex: getComputedStyle(detailsBtn).zIndex,
            pointerEvents: getComputedStyle(detailsBtn).pointerEvents
          });
        }
      });
    }, 1500);
  }
  
  /**
   * Populates and shows the product details modal.
   * @param {object} product - The product data object.
   */
  function populateAndShowDetailsModal(product) {
      const detailsModal = document.getElementById('detailsModal');
      if (!detailsModal) return;

      const name = `${product.brand || ''} ${product.model || ''}`.trim();
      const images = [product.image1, product.image2, product.image3, product.image4, product.image5, product.image6, product.image7, product.image8].filter(Boolean);
      const mainImage = images.length > 0 ? images[0] : 'images/default.png';

      const fields = {
          '.modal-product-name': name,
          '.modal-product-description': product.description || '',
          '.modal-product-price': product.selling_price ? `₱${parseFloat(product.selling_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : 'Price not available',
          '.modal-product-brand': product.brand || '',
          '.modal-product-model': product.model || '',
          '.modal-product-storage': product.storage || '',
          '.modal-product-water-resistance': product.water_resistance || 'Not specified',
          '.modal-product-display-output': product.display_output || 'Not specified',
          '.modal-product-screen-size': product.screen_size || 'Not specified',
          '.modal-product-charging-port': product.charging_port || 'Not specified',
          '.modal-product-material': product.material || 'Not specified',
          '.modal-product-chip': product.chip || 'Not specified',
          '.modal-product-camera-feature': product.camera_feature || 'Not specified'
      };

      // Set main image
      const modalImage = detailsModal.querySelector('.modal-product-image');
      if (modalImage) {
          modalImage.src = mainImage;
          modalImage.alt = name;
      }

      // Populate text fields
      Object.entries(fields).forEach(([selector, value]) => {
          const el = detailsModal.querySelector(selector);
          if (el) {
              el.textContent = value;
              // Fade-in animation
              el.style.opacity = '0';
              setTimeout(() => el.style.opacity = '1', 50);
          }
      });

      // Hide technical specs section if all fields are empty or not specified
      const technicalSpecsSection = detailsModal.querySelector('.technical-specs');
      if (technicalSpecsSection) {
          const specValues = [
              product.water_resistance, product.display_output, product.screen_size,
              product.charging_port, product.material, product.chip, product.camera_feature
          ];
          const hasAnySpec = specValues.some(v => v && String(v).trim().toLowerCase() !== 'not specified' && String(v).trim() !== '');
          technicalSpecsSection.style.display = hasAnySpec ? '' : 'none';
      }

      // Update thumbnails
      updateThumbnails(images, name, modalImage);

      // Open the modal
      openModal(detailsModal);
  }









  /**
   * Update thumbnails in the details modal
   */
  function updateThumbnails(images, productName, mainImage) {
    const thumbnailsContainer = document.getElementById('modalThumbnails');
    if (!thumbnailsContainer) return;

    thumbnailsContainer.innerHTML = '';

    if (images.length <= 1) {
      // Hide thumbnail gallery if only one image
      thumbnailsContainer.style.display = 'none';
      return;
    }

    thumbnailsContainer.style.display = 'flex';

    images.forEach((src, idx) => {
      const thumbnail = document.createElement('img');
      thumbnail.src = src;
      thumbnail.alt = `${productName} - Image ${idx + 1}`;
      thumbnail.className = idx === 0 ? 'active' : '';
      thumbnail.style.opacity = '0';

      // Add click handler
      thumbnail.addEventListener('click', () => {
        // Update main image with fade effect
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src = src;
          mainImage.style.opacity = '1';
        }, 150);

        // Update active thumbnail
        thumbnailsContainer.querySelectorAll('img').forEach(thumb => {
          thumb.classList.remove('active');
        });
        thumbnail.classList.add('active');
      });

      thumbnailsContainer.appendChild(thumbnail);

      // Fade in thumbnail with stagger
      setTimeout(() => {
        thumbnail.style.opacity = '1';
      }, 100 * idx);
    });
  }

  /**
   * Initialize product image hover effects
   */
  function initProductImageHover() {
    const productCards = document.querySelectorAll('.card');
    
    productCards.forEach(card => {
      const mainImage = card.querySelector('img');
      if (!mainImage) return;
      
      // Get the second image from data attribute if available
      const secondImage = card.getAttribute('data-second-image');
      if (!secondImage) return;
      
      // Store original image
      const originalSrc = mainImage.src;
      
      // Add hover effects
      card.addEventListener('mouseenter', () => {
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src = secondImage;
          mainImage.style.opacity = '1';
        }, 150);
      });
      
      card.addEventListener('mouseleave', () => {
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src = originalSrc;
          mainImage.style.opacity = '1';
        }, 150);
      });
    });
  }

  // === UTILITY FUNCTIONS ===

  /**
   * Filter products by category or search term
   * @param {string|null} category - Category to filter by
   * @param {string|null} searchTerm - Search term to filter by
   */
  function filterProducts(category, searchTerm) {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    const productCards = productGridContainer.querySelectorAll('.card');
    
    productCards.forEach(card => {
      const name = card.querySelector('.card-link').textContent.toLowerCase();
      const brand = card.getAttribute('data-brand').toLowerCase();
      const model = card.getAttribute('data-model').toLowerCase();
      const cardCategory = card.getAttribute('data-category').toLowerCase();
      
      let visible = true;
      
      // Apply category filter if specified
      if (category && category !== 'all') {
        visible = cardCategory === category.toLowerCase();
      }
      
      // Apply search filter if specified
      if (visible && searchTerm) {
        visible = name.includes(searchTerm) || 
                 brand.includes(searchTerm) || 
                 model.includes(searchTerm) || 
                 cardCategory.includes(searchTerm);
      }
      
      card.style.display = visible ? 'flex' : 'none';
    });
    
    // Update UI based on filtered results
    centerProductsIfFit();

  }



  /**
   * Get width of a product card including margins
   * @returns {number} Card width in pixels
   */
  function getCardWidth() {
    const productGrid = document.getElementById('productGrid');
    if (!productGrid) {
      console.log('Product grid not found in getCardWidth');
      return 350;
    }
    
    const card = productGrid.querySelector('.card');
    if (!card) {
      console.log('Card not found in getCardWidth');
      return 350;
    }
    
    const style = window.getComputedStyle(card);
    const margin = parseInt(style.marginLeft) + parseInt(style.marginRight);
    const padding = parseInt(style.paddingLeft) + parseInt(style.paddingRight);
    const border = parseInt(style.borderLeftWidth) + parseInt(style.borderRightWidth);
    const totalWidth = card.offsetWidth + margin + padding + border;
    
    console.log('Card width calculation:', {
      offsetWidth: card.offsetWidth,
      margin: margin,
      padding: padding,
      border: border,
      totalWidth: totalWidth
    });
    
    return totalWidth;
  }

  // Add highlight animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes highlight { 0% { background-color: rgba(0, 125, 209, 0.2); } 100% { background-color: transparent; } }
  `;
  document.head.appendChild(style);

  /**
   * Initialize enhanced collections functionality
   */
  function initCollectionsEnhancements() {
    console.log('Initializing collections enhancements');
    
    const collectionCards = document.querySelectorAll('.collection-card');
    
    if (collectionCards.length === 0) {
      console.log('No collection cards found');
      return;
    }
    
    // Add staggered loading animation
    collectionCards.forEach((card, index) => {
      // Set initial state
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      
      // Add staggered animation
      setTimeout(() => {
        card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 150);
      
      // Add enhanced click feedback
      card.addEventListener('click', function(e) {
        // Add ripple effect
        const ripple = document.createElement('div');
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(0, 125, 209, 0.3)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s linear';
        ripple.style.left = (e.clientX - card.offsetLeft) + 'px';
        ripple.style.top = (e.clientY - card.offsetTop) + 'px';
        ripple.style.width = ripple.style.height = '20px';
        ripple.style.pointerEvents = 'none';
        
        card.appendChild(ripple);
        
        setTimeout(() => {
          ripple.remove();
        }, 600);
        
        // Add success animation
        this.classList.add('success');
        setTimeout(() => {
          this.classList.remove('success');
        }, 600);
      });
      
      // Add keyboard navigation
      card.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.click();
        }
      });
      
      // Add hover sound effect (optional)
      card.addEventListener('mouseenter', function() {
        // Add subtle hover effect
        this.style.transform = 'translateY(-15px) scale(1.02)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });
    
    // Add intersection observer for performance
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('loaded');
        }
      });
    }, observerOptions);
    
    collectionCards.forEach(card => {
      observer.observe(card);
    });
    
    // Add category filtering for collections (if needed)
    const categoryButtons = document.querySelectorAll('[data-category-filter]');
    categoryButtons.forEach(button => {
      button.addEventListener('click', function() {
        const category = this.getAttribute('data-category-filter');
        filterCollections(category);
      });
    });
  }
  
  /**
   * Filter collections by category
   * @param {string} category - Category to filter by
   */
  function filterCollections(category) {
    const collectionCards = document.querySelectorAll('.collection-card');
    
    collectionCards.forEach(card => {
      const cardCategory = card.getAttribute('data-category');
      
      if (category === 'all' || cardCategory === category) {
        card.style.display = 'flex';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      } else {
        card.style.display = 'none';
      }
    });
  }

  // Add ripple animation CSS
  const rippleStyle = document.createElement('style');
  rippleStyle.textContent = `
    @keyframes ripple {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(rippleStyle);

  /**
   * Initialize cart icon functionality
   */
  function initCartIcon() {
    console.log('Initializing cart icon'); // Debug log
    
    const cartIcon = document.getElementById('cartIcon');
    const cartDropdownContainer = document.querySelector('.cart-dropdown-container');
    const cartDropdownMenu = document.querySelector('.cart-dropdown-menu');
    
    if (!cartIcon || !cartDropdownContainer || !cartDropdownMenu) {
      console.log('Cart icon or dropdown elements not found'); // Debug log
      return;
    }
    
    // Handle cart action buttons
    const viewCartBtn = cartDropdownMenu.querySelector('.view-cart-btn');
    const checkoutBtn = cartDropdownMenu.querySelector('.checkout-btn');
    
    if (viewCartBtn) {
      viewCartBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('View Cart clicked'); // Debug log
        alert('View Cart functionality coming soon!');
        // Future: Navigate to cart page or open cart modal
      });
    }
    
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Checkout clicked'); // Debug log
        alert('Checkout functionality coming soon!');
        // Future: Navigate to checkout page
      });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!cartDropdownContainer.contains(e.target)) {
        cartDropdownMenu.style.opacity = '0';
        cartDropdownMenu.style.visibility = 'hidden';
        cartDropdownMenu.style.transform = 'translateY(-10px)';
      }
    });
    
    // Keyboard navigation support
    cartIcon.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        // Toggle dropdown visibility
        const isVisible = cartDropdownMenu.style.opacity === '1';
        if (isVisible) {
          cartDropdownMenu.style.opacity = '0';
          cartDropdownMenu.style.visibility = 'hidden';
          cartDropdownMenu.style.transform = 'translateY(-10px)';
        } else {
          cartDropdownMenu.style.opacity = '1';
          cartDropdownMenu.style.visibility = 'visible';
          cartDropdownMenu.style.transform = 'translateY(0)';
        }
      }
    });
    
    // Function to update cart count (for future use)
    function updateCartCount(count) {
      const cartCountElement = cartDropdownMenu.querySelector('.cart-count');
      if (cartCountElement) {
        cartCountElement.textContent = `${count} items`;
      }
    }
    
    // Function to update cart total (for future use)
    function updateCartTotal(total) {
      const totalAmountElement = cartDropdownMenu.querySelector('.total-amount');
      if (totalAmountElement) {
        totalAmountElement.textContent = `₱${total.toFixed(2)}`;
      }
    }
    
    // Expose functions for future use
    window.updateCartCount = updateCartCount;
    window.updateCartTotal = updateCartTotal;
  }

  /**
   * Initialize profile icon functionality
   */
  function initProfileIcon() {
    console.log('Initializing profile icon'); // Debug log
    
    const profileIcon = document.getElementById('profileIcon');
    const dropdownContainer = document.querySelector('.profile-dropdown-container');
    const dropdownMenu = document.querySelector('.profile-dropdown-menu');
    
    if (!profileIcon || !dropdownContainer || !dropdownMenu) {
      console.log('Profile icon or dropdown elements not found'); // Debug log
      return;
    }
    
    // Handle dropdown item clicks
    const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        const itemText = this.querySelector('span').textContent;
        console.log('Dropdown item clicked:', itemText); // Debug log
        
        // Handle different dropdown items
        switch(itemText) {
          case 'My Orders':
            alert('My Orders functionality coming soon!');
            break;
          case 'My Messages':
            alert('My Messages functionality coming soon!');
            break;
          case 'My Coupons':
            alert('My Coupons functionality coming soon!');
            break;
          case 'My Points':
            alert('My Points functionality coming soon!');
            break;
          case 'Recently Viewed':
            alert('Recently Viewed functionality coming soon!');
            break;
          case 'More Services':
            alert('More Services functionality coming soon!');
            break;
          default:
            alert('Feature coming soon!');
        }
      });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!dropdownContainer.contains(e.target)) {
        dropdownMenu.style.opacity = '0';
        dropdownMenu.style.visibility = 'hidden';
        dropdownMenu.style.transform = 'translateY(-10px)';
      }
    });
    
    // Keyboard navigation support
    profileIcon.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        // Toggle dropdown visibility
        const isVisible = dropdownMenu.style.opacity === '1';
        if (isVisible) {
          dropdownMenu.style.opacity = '0';
          dropdownMenu.style.visibility = 'hidden';
          dropdownMenu.style.transform = 'translateY(-10px)';
        } else {
          dropdownMenu.style.opacity = '1';
          dropdownMenu.style.visibility = 'visible';
          dropdownMenu.style.transform = 'translateY(0)';
        }
      }
    });
  }

  /**
   * Initialize responsive carousel functionality for products
   */
  function initCarousel() {
    console.log('Initializing responsive carousel functionality');

    const grid = document.getElementById('productGrid');
    const prevBtn = document.getElementById('carouselPrev');
    const nextBtn = document.getElementById('carouselNext');

    // Dynamic visible count based on screen size
    function getVisibleCount() {
      if (window.innerWidth <= 480) return 1;
      if (window.innerWidth <= 768) return 2;
      if (window.innerWidth <= 1024) return 3;
      return 4; // Desktop
    }

    let visibleCount = getVisibleCount();
    let startIdx = 0;

    // Update carousel function with responsive logic
    updateCarousel = function() {
      const allCards = grid ? Array.from(grid.children) : [];
      // Only consider cards that are visible after filtering
      const visibleCards = allCards.filter(card => card.style.display !== 'none');

      // Update visible count based on current screen size
      visibleCount = getVisibleCount();

      if (visibleCards.length <= visibleCount) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        // Remove carousel-hidden from all visible cards
        visibleCards.forEach(card => card.classList.remove('carousel-hidden'));
        return;
      }

      if (prevBtn) prevBtn.style.display = 'flex';
      if (nextBtn) nextBtn.style.display = 'flex';

      function showProducts() {
        visibleCards.forEach((prod, idx) => {
          if (idx >= startIdx && idx < startIdx + visibleCount) {
            prod.classList.remove('carousel-hidden');
          } else {
            prod.classList.add('carousel-hidden');
          }
        });

        if (prevBtn) prevBtn.disabled = startIdx === 0;
        if (nextBtn) nextBtn.disabled = startIdx + visibleCount >= visibleCards.length;
      }

      showProducts();

      if (prevBtn) {
        prevBtn.onclick = function () {
          startIdx = Math.max(0, startIdx - visibleCount);
          showProducts();
        };
      }

      if (nextBtn) {
        nextBtn.onclick = function () {
          if (startIdx + visibleCount < visibleCards.length) {
            startIdx += visibleCount;
            showProducts();
          }
        };
      }
    }

    // Initial setup
    updateCarousel();

    // Update on window resize
    window.addEventListener('resize', function() {
      updateCarousel();
    });

    // Listen for category changes
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Reset carousel position when category changes
        startIdx = 0;
        setTimeout(updateCarousel, 100);
      });
    });

    // Listen for search changes
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        // Reset carousel position when search changes
        startIdx = 0;
        setTimeout(updateCarousel, 100);
      });
    }
  }

  // === MOBILE NAVIGATION ===
  function initMobileNav() {
    const navToggle = document.getElementById('mobileNavToggle');
    const mobileNav = document.getElementById('mobileNav');
    const mobileNavOverlay = document.getElementById('mobileNavOverlay');
    
    if (!navToggle || !mobileNav) return;

    const openNav = () => {
      mobileNav.classList.add('open');
      if (mobileNavOverlay) mobileNavOverlay.style.display = 'block';
      document.body.style.overflow = 'hidden';
    };
    
    const closeNav = () => {
      mobileNav.classList.remove('open');
      if (mobileNavOverlay) mobileNavOverlay.style.display = 'none';
      document.body.style.overflow = '';
    };

    navToggle.addEventListener('click', () => {
      mobileNav.classList.contains('open') ? closeNav() : openNav();
    });
    
    if (mobileNavOverlay) {
      mobileNavOverlay.addEventListener('click', closeNav);
    }
    
    // Close on link click and ESC key
    mobileNav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeNav);
    });
    
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileNav.classList.contains('open')) {
        closeNav();
      }
    });
  }
  
  initMobileNav();

});

function centerCarouselCards() {
  const productGrid = document.getElementById('productGrid');
  if (!productGrid) return;
  const visibleCards = Array.from(productGrid.querySelectorAll('.card')).filter(card => card.style.display !== 'none');
  if (visibleCards.length === 0) return;
  // Calculate total width of visible cards
  let totalWidth = 0;
  visibleCards.forEach(card => {
    totalWidth += card.offsetWidth;
  });
  // Add gap if any (match CSS gap)
  const gap = 30;
  totalWidth += gap * (visibleCards.length - 1);
  // Compare to productGrid width
  if (totalWidth < productGrid.offsetWidth) {
    productGrid.classList.add('centered');
  } else {
    productGrid.classList.remove('centered');
  }
}

// MutationObserver to re-center when cards change
window.addEventListener('DOMContentLoaded', function() {
  setTimeout(() => {
    centerCarouselCards();
    // Observe changes to #productGrid children
    const productGrid = document.getElementById('productGrid');
    if (productGrid) {
      const observer = new MutationObserver(() => {
        setTimeout(centerCarouselCards, 50);
      });
      observer.observe(productGrid, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
    }
  }, 400);
});
