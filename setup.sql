CREATE DATABASE IF NOT EXISTS wolfcore_bank;
USE wolfcore_bank;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 100.00,
    is_vip BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_username VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'completed',
    FOREIGN KEY (from_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag_name VARCHAR(50) NOT NULL,
    flag_value VARCHAR(255) NOT NULL
);

-- Insert default users
INSERT INTO users (username, password, email, balance, is_vip) VALUES 
('bob_joseph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bob@wolfcore.com', 1000.00, TRUE),
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@email.com', 250.00, FALSE),
('sarah_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah@email.com', 1000.00, FALSE),
('mary_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mary@wolfcore.com', 1500.00, TRUE);

-- Insert service information
INSERT INTO services (service_name, description, category) VALUES
('Personal Checking', 'Standard checking account with no monthly fees', 'Accounts'),
('Savings Account', 'High-yield savings with competitive interest rates', 'Accounts'),
('Business Banking', 'Comprehensive solutions for your business needs', 'Business'),
('Mortgage Loans', 'Competitive rates for home financing', 'Loans'),
('Auto Loans', 'Fast approval for vehicle purchases', 'Loans'),
('Credit Cards', 'Reward cards with cashback benefits', 'Cards'),
('Investment Services', 'Grow your wealth with our investment options', 'Investments');

-- Insert flags
INSERT INTO flags (flag_name, flag_value) VALUES
('sql_injection', 'FLAG{SQL_1nj3ct10n_M4st3r_2024}');

