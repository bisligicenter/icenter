const selectedProducts = [];

function updateCompareButton() {
  let btn = document.getElementById('compare-now-btn');
  if (!btn) {
    btn = document.createElement('button');
    btn.id = 'compare-now-btn';
    btn.textContent = 'Compare Now';
    btn.style.position = 'fixed';
    btn.style.bottom = '30px';
    btn.style.left = '50%';
    btn.style.transform = 'translateX(-50%)';
    btn.style.padding = '16px 32px';
    btn.style.background = '#000';
    btn.style.color = '#fff';
    btn.style.fontWeight = 'bold';
    btn.style.border = 'none';
    btn.style.borderRadius = '8px';
    btn.style.boxShadow = '0 4px 16px rgba(0,0,0,0.15)';
    btn.style.zIndex = '9999';
    btn.style.cursor = 'pointer';
    btn.style.display = 'none';
    btn.onclick = function() {
      if (selectedProducts.length >= 2) {
        const ids = selectedProducts.map(p => encodeURIComponent(p.id)).join(',');
        // Check if we are on accessories.php, airpods.php, android.php, ipad.php, iphone.php, laptop.php, pcset.php, or printer.php
        const path = window.location.pathname;
        let url = 'compare.php?ids=' + ids;
        if (path.endsWith('accessories.php')) {
          url += '&category=accessories';
        } else if (path.endsWith('airpods.php')) {
          url += '&category=airpods';
        } else if (path.endsWith('android.php')) {
          url += '&category=android';
        } else if (path.endsWith('ipad.php')) {
          url += '&category=ipad';
        } else if (path.endsWith('iphone.php')) {
          url += '&category=iphone';
        } else if (path.endsWith('laptop.php')) {
          url += '&category=laptop';
        } else if (path.endsWith('pcset.php')) {
          url += '&category=pcset';
        } else if (path.endsWith('printer.php')) {
          url += '&category=printer';
        }
        window.location.href = url;
      }
    };
    document.body.appendChild(btn);
  }
  btn.style.display = selectedProducts.length >= 2 ? 'block' : 'none';
}

function showLimitModal() {
  let modal = document.getElementById('compare-limit-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'compare-limit-modal';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.background = 'rgba(0,0,0,0.4)';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.zIndex = '10001';
    modal.innerHTML = `
      <div style="background: #fff; padding: 32px 40px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); text-align: center; max-width: 90vw;">
        <div style="font-size: 1.3rem; font-weight: bold; margin-bottom: 18px; color: #000;">Limit Reached</div>
        <div style="font-size: 1.1rem; margin-bottom: 24px; color: #333;">You can only compare up to 3 products.</div>
        <button id="close-compare-limit-modal" style="background: #000; color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: 1rem; font-weight: bold; cursor: pointer;">OK</button>
      </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('close-compare-limit-modal').onclick = function() {
      modal.style.display = 'none';
    };
  } else {
    modal.style.display = 'flex';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.compare-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const card = checkbox.closest('.card');
      const productId = checkbox.value;
      const productName = card ? card.getAttribute('data-product-name') : '';
      if (checkbox.checked) {
        if (selectedProducts.length >= 3) {
          checkbox.checked = false;
          showLimitModal();
          return;
        }
        selectedProducts.push({ id: productId, name: productName });
        checkbox.closest('.card').style.border = '2px solid #007dd1';
      } else {
        const idx = selectedProducts.findIndex(p => p.id === productId);
        if (idx !== -1) selectedProducts.splice(idx, 1);
        checkbox.closest('.card').style.border = '';
      }
      updateCompareButton();
    });
  });
  updateCompareButton();
});
