CREATE DATABASE art_coffee_db;
USE art_coffee_db;

-- Tabel Products
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price INT NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Orders
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    products TEXT NOT NULL,
    total_amount INT NOT NULL,
    status ENUM('pending', 'confirmed', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Testimonials
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    occupation VARCHAR(100),
    content TEXT NOT NULL,
    rating INT DEFAULT 5,
    approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Contacts
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO products (name, description, price, image) VALUES
('Coffee Latte', 'Klasik & creamy, perpaduan sempurna espresso dan susu lembut', 12000, 'latte.jpg'),
('Coffee Matcha', 'Perpaduan unik pahitnya kopi dan segar matcha hijau', 14000, 'matcha.jpg'),
('Coffee Vanilla', 'Aroma manis vanila yang menenangkan dengan espresso premium', 13000, 'vanilla.jpg'),
('Kopi Susu', 'Rasa otentik kopi pilihan + susu segar', 10000, 'kopi-susu.jpg');

INSERT INTO testimonials (name, occupation, content, rating) VALUES
('Rina', 'Freelancer', 'Matcha Coffee-nya juara banget! Terbaik di kota!', 5),
('Adit', 'Mahasiswa', 'Kopi susu 10k tapi rasa cafe fancy!', 5),
('Sari', 'Marketing', 'Vanilla coffee calming banget buat deadline!', 5);