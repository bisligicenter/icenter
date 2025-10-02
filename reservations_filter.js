document.addEventListener('DOMContentLoaded', () => {
  const categorySelect = document.getElementById('categorySelect');
  const storageSelect = document.getElementById('storageSelect');
  const productGrid = document.getElementById('productGrid');
  const productSearch = document.getElementById('productSearch');
  if (!categorySelect || !storageSelect || !productGrid) return;

  // Create and insert "Clear Filters" button
  const filtersContainer = categorySelect.parentElement.parentElement.parentElement;
  const clearFiltersBtn = document.createElement('button');
  clearFiltersBtn.textContent = 'Clear Filters';
  clearFiltersBtn.type = 'button';
  clearFiltersBtn.className = 'mt-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-gray-800 font-semibold';
  filtersContainer.appendChild(clearFiltersBtn);

  // Create and insert "No results found" message
  const noResultsMsg = document.createElement('p');
  noResultsMsg.textContent = 'No products found.';
  noResultsMsg.style.display = 'none';
  noResultsMsg.className = 'text-center text-gray-500 mt-4';
  productGrid.parentElement.appendChild(noResultsMsg);

  function updateProductCardListeners() {
    const productCards = productGrid.querySelectorAll('.card');
    productCards.forEach(card => {
      card.onclick = function () {
        // Remove highlight from all cards
        productGrid.querySelectorAll('.card').forEach(c => c.classList.remove('selected-product'));
        // Highlight this card
        this.classList.add('selected-product');
        // Set product_id input
        productIdInput.value = this.getAttribute('data-product-id');
        // Show preview
        selectedProductPreview.classList.remove('hidden');
        selectedProductImg.src = card.querySelector('img').src;
        selectedProductImg.alt = card.querySelector('img').alt;
        selectedProductName.textContent = card.querySelector('h3').textContent;
        selectedProductDesc.textContent = card.querySelector('p.text-gray-600') ? card.querySelector('p.text-gray-600').textContent : '';
      };
      // Add keyboard accessibility
      card.tabIndex = 0;
      card.onkeydown = function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.click();
        }
      };
    });
  }

  function filterProducts() {
    const selectedCategory = categorySelect.value.toLowerCase();
    const selectedStorage = storageSelect.value.toLowerCase();
    const searchValue = productSearch ? productSearch.value.trim().toLowerCase() : '';
    const productCards = productGrid.querySelectorAll('.card');

    let visibleCount = 0;
    productCards.forEach(card => {
      const cardCategory = card.getAttribute('data-category')?.toLowerCase() || '';
      const cardStorage = card.getAttribute('data-storage')?.toLowerCase() || '';
      const cardBrand = card.getAttribute('data-brand')?.toLowerCase() || '';
      const cardModel = card.getAttribute('data-model')?.toLowerCase() || '';
      const cardText = (cardBrand + ' ' + cardModel + ' ' + cardCategory + ' ' + cardStorage).toLowerCase();

      const categoryMatch = selectedCategory === 'all' || cardCategory === selectedCategory;
      const storageMatch = selectedStorage === 'all' || cardStorage === selectedStorage;
      const searchMatch = !searchValue || cardText.includes(searchValue);

      if (categoryMatch && storageMatch && searchMatch) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
        // Also remove highlight if hidden
        card.classList.remove('selected-product');
      }
    });

    noResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';

    updateProductCardListeners();
  }

  // Debounce function for search input
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  categorySelect.addEventListener('change', filterProducts);
  storageSelect.addEventListener('change', filterProducts);
  if (productSearch) {
    productSearch.addEventListener('input', debounce(filterProducts, 300));
  }

  clearFiltersBtn.addEventListener('click', () => {
    categorySelect.value = 'all';
    storageSelect.value = 'all';
    if (productSearch) productSearch.value = '';
    filterProducts();
  });

  // Product card selection logic
  const productIdInput = document.getElementById('product_id');
  const selectedProductPreview = document.getElementById('selectedProductPreview');
  const selectedProductImg = document.getElementById('selectedProductImg');
  const selectedProductName = document.getElementById('selectedProductName');
  const selectedProductDesc = document.getElementById('selectedProductDesc');

  updateProductCardListeners();
  filterProducts();
});
