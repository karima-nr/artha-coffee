// API Base URL
const API_BASE = "../backend/api/";

// Shopping Cart State
let cart = JSON.parse(localStorage.getItem("cart")) || [];

// --- LOAD DATA FUNCTIONS ---

// Load products from database
async function loadProducts() {
  try {
    const response = await fetch(`${API_BASE}products.php`);
    const data = await response.json();

    const productsGrid = document.querySelector(".products-grid");
    if (!productsGrid) return;
    
    productsGrid.innerHTML = "";

    if (data.records && data.records.length > 0) {
      data.records.forEach((product) => {
        const productCard = `
          <div class="product-card fade-in-up">
            <img src="Images/${product.image}" alt="${product.name}" class="product-image">
            <h3>${product.name}</h3>
            <p>${product.description}</p>
            <div class="price">Rp ${parseInt(product.price).toLocaleString()}</div>
            <button class="cta-button small add-to-cart" 
                    data-id="${product.id}" 
                    data-name="${product.name}" 
                    data-price="${product.price}">
              Tambah ke Keranjang
            </button>
          </div>
        `;
        productsGrid.innerHTML += productCard;
      });

      // Add event listeners to buttons
      document.querySelectorAll(".add-to-cart").forEach((btn) => {
        btn.addEventListener("click", addToCart);
      });
    } else {
      productsGrid.innerHTML = "<p class='loading'>Belum ada menu tersedia.</p>";
    }
  } catch (error) {
    console.error("Error loading products:", error);
  }
}

// Load testimonials from database
async function loadTestimonials() {
  try {
    const response = await fetch(`${API_BASE}testimonials.php`);
    const data = await response.json();

    const testimonialsGrid = document.querySelector(".testimonials-grid");
    if (!testimonialsGrid) return;

    testimonialsGrid.innerHTML = "";

    if (data.records && data.records.length > 0) {
      data.records.forEach((testi) => {
        const stars = "⭐".repeat(testi.rating);
        const testiCard = `
          <div class="testimonial fade-in-up">
            <div class="stars">${stars}</div>
            <p>"${testi.content}"</p>
            <div class="author">- ${testi.name}${testi.occupation ? ", " + testi.occupation : ""}</div>
          </div>
        `;
        testimonialsGrid.innerHTML += testiCard;
      });
    } else {
      testimonialsGrid.innerHTML = "<p style='text-align:center; grid-column: 1/-1;'>Belum ada testimoni.</p>";
    }
  } catch (error) {
    console.error("Error loading testimonials:", error);
  }
}

// --- CART FUNCTIONS ---

function addToCart(e) {
  const btn = e.target;
  const product = {
    id: btn.dataset.id,
    name: btn.dataset.name,
    price: parseInt(btn.dataset.price),
    quantity: 1,
  };

  const existing = cart.find((item) => item.id == product.id);
  if (existing) {
    existing.quantity++;
  } else {
    cart.push(product);
  }

  saveCart();
  updateCartUI();
  
  // Visual feedback
  btn.innerText = "✅ Ditambahkan!";
  setTimeout(() => {
    btn.innerText = "Tambah ke Keranjang";
  }, 1000);
}

function removeFromCart(id) {
  cart = cart.filter(item => item.id != id);
  saveCart();
  updateCartUI();
}

function updateQuantity(id, delta) {
  const item = cart.find(item => item.id == id);
  if (item) {
    item.quantity += delta;
    if (item.quantity <= 0) {
      removeFromCart(id);
    } else {
      saveCart();
      updateCartUI();
    }
  }
}

function saveCart() {
  localStorage.setItem("cart", JSON.stringify(cart));
}

function updateCartUI() {
  const cartCount = document.querySelector(".cart-count");
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  cartCount.textContent = totalItems;
  
  const cartItemsContainer = document.getElementById("cartItems");
  const cartTotalLabel = document.getElementById("cartTotal");
  
  if (cartItemsContainer) {
    cartItemsContainer.innerHTML = "";
    let total = 0;

    if (cart.length === 0) {
      cartItemsContainer.innerHTML = "<p style='text-align:center; padding: 2rem;'>Keranjang kamu kosong ☕</p>";
      document.getElementById("checkoutForm").style.display = "none";
    } else {
      document.getElementById("checkoutForm").style.display = "block";
      cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        cartItemsContainer.innerHTML += `
          <div class="cart-item">
            <div class="cart-item-info">
              <h4>${item.name}</h4>
              <p>Rp ${item.price.toLocaleString()} x ${item.quantity}</p>
            </div>
            <div class="cart-item-actions">
              <button onclick="updateQuantity('${item.id}', -1)">-</button>
              <span>${item.quantity}</span>
              <button onclick="updateQuantity('${item.id}', 1)">+</button>
              <button onclick="removeFromCart('${item.id}')" style="background: #ffebee; color: #f44336; margin-left: 10px;"><i class="fas fa-trash"></i></button>
            </div>
          </div>
        `;
      });
    }
    
    if (cartTotalLabel) {
      cartTotalLabel.textContent = `Rp ${total.toLocaleString()}`;
    }
  }
}

// --- FORM SUBMISSIONS ---

// Handle Order / Checkout
const orderForm = document.getElementById("orderForm");
if (orderForm) {
  orderForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    
    if (cart.length === 0) {
      alert("Keranjang kosong!");
      return;
    }

    const btn = this.querySelector('button');
    btn.disabled = true;
    btn.innerText = "Memproses...";

    const name = document.getElementById("custName").value;
    const phone = document.getElementById("custPhone").value;
    const email = document.getElementById("custEmail").value;
    const products = cart.map((item) => `${item.name} (x${item.quantity})`).join(", ");
    const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    try {
      const response = await fetch(`${API_BASE}orders.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, phone, email, products, total }),
      });

      if (response.ok) {
        alert(`✅ Order Berhasil!\nTotal: Rp ${total.toLocaleString()}\nKami akan segera menghubungi via WA ${phone}.`);
        cart = [];
        saveCart();
        updateCartUI();
        document.getElementById("cartModal").style.display = "none";
        this.reset();
      } else {
        throw new Error("Gagal menyimpan order.");
      }
    } catch (error) {
      alert("Error: " + error.message);
    } finally {
      btn.disabled = false;
      btn.innerText = "Pesan Sekarang (Checkout)";
    }
  });
}

// Handle Testimonial Submission
const testimonialForm = document.getElementById("testimonialForm");
if (testimonialForm) {
  testimonialForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    
    const btn = this.querySelector('button');
    btn.disabled = true;
    btn.innerText = "Mengirim...";

    const formData = {
      name: document.getElementById("testiName").value,
      occupation: document.getElementById("testiOcc").value,
      rating: document.getElementById("testiRating").value,
      content: document.getElementById("testiContent").value,
    };

    try {
      const response = await fetch(`${API_BASE}testimonials.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        alert("✅ Terima kasih! Testimoni kamu telah terkirim.");
        this.reset();
        loadTestimonials();
      }
    } catch (error) {
      alert("Error: " + error.message);
    } finally {
      btn.disabled = false;
      btn.innerText = "Kirim Testimoni";
    }
  });
}

// Handle Contact Form
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  contactForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    
    const btn = this.querySelector('button');
    btn.disabled = true;
    btn.innerText = "Mengirim...";

    const formData = {
      name: this.querySelector('input[type="text"]').value,
      email: this.querySelector('input[type="email"]').value,
      phone: this.querySelector('input[type="tel"]').value,
      message: this.querySelector("textarea").value,
    };

    try {
      const response = await fetch(`${API_BASE}contacts.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        alert("✅ Pesan berhasil dikirim!");
        this.reset();
      }
    } catch (error) {
      alert("Error: " + error.message);
    } finally {
      btn.disabled = false;
      btn.innerText = "Kirim Pesan";
    }
  });
}

// --- MODAL & UI HELPERS ---

// Cart Modal Toggle
const cartBtn = document.getElementById("cartBtn");
const cartModal = document.getElementById("cartModal");
const closeModal = document.querySelector(".close-modal");

if (cartBtn) {
  cartBtn.onclick = () => {
    cartModal.style.display = "block";
    updateCartUI();
  };
}

if (closeModal) {
  closeModal.onclick = () => {
    cartModal.style.display = "none";
  };
}

window.onclick = (event) => {
  if (event.target == cartModal) {
    cartModal.style.display = "none";
  }
};

// Mobile Menu Toggle
const hamburger = document.querySelector(".hamburger");
const navMenu = document.querySelector(".nav-menu");

if (hamburger) {
  hamburger.addEventListener("click", () => {
    navMenu.classList.toggle("active");
  });
}

document.querySelectorAll(".nav-menu a").forEach((link) => {
  link.addEventListener("click", () => {
    navMenu.classList.remove("active");
  });
});

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href === "#") return;
    
    e.preventDefault();
    const target = document.querySelector(href);
    if (target) {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });
});

// Expose functions to window for onclick handlers
window.updateQuantity = updateQuantity;
window.removeFromCart = removeFromCart;

// --- INITIALIZE ---
document.addEventListener("DOMContentLoaded", () => {
  console.log("Initializing app...");
  loadProducts();
  loadTestimonials();
  updateCartUI();
});
