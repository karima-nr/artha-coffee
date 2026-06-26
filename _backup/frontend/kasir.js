// Kasir Global Variables
let currentOrder = [];
let orderNumber = 1;
let products = [];

// Load products on startup
async function loadKasirProducts() {
    try {
        const response = await fetch('../backend/api/products.php');
        const data = await response.json();
        products = data.records;
        renderProducts(products);
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Render products
function renderProducts(productList) {
    const grid = document.getElementById('kasirProducts');
    grid.innerHTML = '';
    
    productList.forEach(product => {
        const productEl = document.createElement('div');
        productEl.className = 'kasir-product';
        productEl.innerHTML = `
            <img src="Images/${product.image}" alt="${product.name}">
            <h4>${product.name}</h4>
            <div class="price">Rp ${product.price.toLocaleString()}</div>
        `;
        productEl.onclick = () => addToOrder(product);
        grid.appendChild(productEl);
    });
}

// Add to order
function addToOrder(product) {
    const existing = currentOrder.find(item => item.id === product.id);
    
    if (existing) {
        existing.quantity += 1;
    } else {
        currentOrder.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1
        });
    }
    
    renderOrder();
    updateOrderNumber();
}

// Render order items
function renderOrder() {
    const container = document.getElementById('orderItems');
    container.innerHTML = '';
    
    currentOrder.forEach((item, index) => {
        const itemEl = document.createElement('div');
        itemEl.className = 'order-item';
        itemEl.innerHTML = `
            <div>
                <strong>${item.name}</strong><br>
                <small>${item.quantity}x Rp ${item.price.toLocaleString()}</small>
            </div>
            <div>
                Rp ${(item.price * item.quantity).toLocaleString()}
                <button onclick="removeItem(${index})" style="background:none;border:none;color:#ff4444;font-size:1.2rem;margin-left:10px;">×</button>
            </div>
        `;
        container.appendChild(itemEl);
    });
    
    calculateTotal();
}

// Remove item
function removeItem(index) {
    currentOrder.splice(index, 1);
    renderOrder();
}

// Calculate totals
function calculateTotal() {
    const subtotal = currentOrder.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = Math.floor(subtotal * 0.1);
    const grandTotal = subtotal - discount;
    
    document.getElementById('subtotal').textContent = `Rp ${subtotal.toLocaleString()}`;
    document.getElementById('discount').textContent = `Rp ${discount.toLocaleString()}`;
    document.getElementById('grandTotal').textContent = `Rp ${grandTotal.toLocaleString()}`;
    
    return { subtotal, discount, grandTotal };
}

// Clear order
function clearOrder() {
    currentOrder = [];
    renderOrder();
}

// Update order number
function updateOrderNumber() {
    const num = document.getElementById('orderNumber');
    num.textContent = String(orderNumber).padStart(3, '0');
}

// Process payment
function processPayment() {
    if (currentOrder.length === 0) {
        alert('Pesanan kosong!');
        return;
    }

    const cash = parseInt(document.getElementById('cashInput').value) || 0;
    const { grandTotal } = calculateTotal();
    
    if (cash < grandTotal) {
        alert('Uang kurang!');
        return;
    }
    
    const change = cash - grandTotal;
    document.getElementById('changeAmount').textContent = `Rp ${change.toLocaleString()}`;
    
    // Save to database
    saveOrderToDB();
    
    // Show success
    alert(`✅ TRANSAKSI SUKSES!\nKembali: Rp ${change.toLocaleString()}`);
    
    // Print receipt
    printReceipt();
    
    // Reset for next order
    resetOrder();
}

// Save to database
async function saveOrderToDB() {
    const { grandTotal } = calculateTotal();
    const products = currentOrder.map(item => `${item.name} x${item.quantity}`).join(', ');
    
    try {
        await fetch('../backend/api/orders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: 'Pelanggan Walk-in',
                phone: 'Kasir Mode',
                products,
                total: grandTotal
            })
        });
    } catch (error) {
        console.error('Error saving order:', error);
    }
}

// Print receipt
function printReceipt() {
    const { subtotal, discount, grandTotal } = calculateTotal();
    const printContent = `
        <div style="width:80mm;font-family:monospace;padding:20px;">
            <div style="text-align:center;border-bottom:2px solid #4B3621;padding-bottom:10px;">
                <h2>ART COFFEE ☕</h2>
                <p>Jl. Sudirman 123, Jakarta</p>
                <p>No. ${document.getElementById('orderNumber').textContent}</p>
            </div>
            
            <div style="margin:20px 0;">
                ${currentOrder.map(item => `
                    <div style="display:flex;justify-content:space-between;padding:5px 0;">
                        <span>${item.name} x${item.quantity}</span>
                        <span>Rp ${(item.price * item.quantity).toLocaleString()}</span>
                    </div>
                `).join('')}
            </div>
            
            <hr style="border:1px dashed #ccc;">
            <div style="display:flex;justify-content:space-between;font-weight:bold;">
                <span>TOTAL</span>
                <span>Rp ${grandTotal.toLocaleString()}</span>
            </div>
            
            <div style="text-align:center;margin-top:20px;font-size:0.8rem;">
                <p>Terima kasih!</p>
                <p>Kunjungi lagi ya ☕</p>
                <p style="font-size:0.7rem;">Printed: ${new Date().toLocaleString('id-ID')}</p>
            </div>
        </div>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head><title>Receipt #${document.getElementById('orderNumber').textContent}</title></head>
            <body onload="window.print();window.close();">${printContent}</body>
        </html>
    `);
    printWindow.document.close();
}

// Reset order
function resetOrder() {
    currentOrder = [];
    orderNumber++;
    document.getElementById('cashInput').value = '';
    renderOrder();
    updateOrderNumber();
    document.getElementById('changeAmount').textContent = 'Rp 0';
    localStorage.removeItem("cart");
}

// Category filter
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        document.querySelector('.cat-btn.active').classList.remove('active');
        e.target.classList.add('active');
        
        const category = e.target.dataset.cat;
        const filtered = category === 'all' ? products : 
                        products.filter(p => p.name.toLowerCase().includes(category));
        renderProducts(filtered);
    });
});

// Real-time datetime
function updateDateTime() {
    document.getElementById('datetime').textContent = 
        new Date().toLocaleString('id-ID', { 
            weekday: 'short', 
            day: 'numeric', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
}

setInterval(updateDateTime, 1000);

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadKasirProducts();
    updateDateTime();
    updateOrderNumber();
    
    // Load cart from index if exists
    const savedCart = JSON.parse(localStorage.getItem("cart"));
    if (savedCart && savedCart.length > 0) {
        currentOrder = savedCart;
        renderOrder();
    }
});