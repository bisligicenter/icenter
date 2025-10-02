// --- Archive/Delete Button Logic (copied from view_products.php) ---
function initializeArchiveEventListeners() {
  // Archive modal elements and functions
  const modal = document.getElementById('archiveModal');
  const confirmBtn = document.getElementById('confirmArchive');
  const cancelBtn = document.getElementById('cancelArchive');
  let currentProductId = null;

  function openModal(productId) {
    currentProductId = productId;
    modal.classList.remove('hidden');
  }

  function closeModal() {
    currentProductId = null;
    modal.classList.add('hidden');
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', closeModal);
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
      // ...existing code for confirming archive...
    });
  }

  document.querySelectorAll('.trash-can-button').forEach(button => {
    button.addEventListener('click', function () {
      openModal(productId);
    });

    // Add hover effects
    button.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.1)';
    });

    button.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1)';
    });
  });
}

// --- Sale Button Logic (copied from view_products.php) ---
function initializeSaleEventListeners() {
  // Sale confirmation modal elements and functions
  const saleSuccessModal = document.getElementById('saleSuccessModal');
  const closeSaleSuccessModalBtn = document.getElementById('closeSaleSuccessModal');

  if (closeSaleSuccessModalBtn) {
    closeSaleSuccessModalBtn.addEventListener('click', function () {
      saleSuccessModal.classList.add('hidden');
    });
  }

  // Sale confirmation prompt modal elements and functions
  const saleConfirmModal = document.getElementById('saleConfirmModal');
  const cancelSaleConfirmBtn = document.getElementById('cancelSaleConfirm');
  const confirmSaleConfirmBtn = document.getElementById('confirmSaleConfirm');

  let pendingSale = null;

  if (cancelSaleConfirmBtn) {
    cancelSaleConfirmBtn.addEventListener('click', function () {
      pendingSale = null;
    });
  }

  if (confirmSaleConfirmBtn) {
    confirmSaleConfirmBtn.addEventListener('click', function () {
      // ...existing code for confirming sale...
    });
  }

  // Handle Sold button click
  document.querySelectorAll('.sold-button').forEach(button => {
    button.addEventListener('click', function () {
      saleConfirmModal.classList.remove('hidden');
    });
  });
}

function showSaleErrorModal(message) {
  const saleErrorModal = document.getElementById('saleErrorModal');
  const saleErrorMessage = document.getElementById('saleErrorMessage');
  if (saleErrorMessage) saleErrorMessage.textContent = message;
  if (saleErrorModal) saleErrorModal.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
  initializeArchiveEventListeners();
  initializeSaleEventListeners();
  // Sale error modal close button
  const closeSaleErrorModalBtn = document.getElementById('closeSaleErrorModal');
  if (closeSaleErrorModalBtn) {
    closeSaleErrorModalBtn.addEventListener('click', function () {
      document.getElementById('saleErrorModal').classList.add('hidden');
    });
  }
});

// Chat Notification Sound (Temporarily Disabled)
// function playChatNotification() {
//   const audio = document.getElementById('chatNotificationSound');
//   if (audio) { ... }
// }