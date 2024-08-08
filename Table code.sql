CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    changed BOOLEAN DEFAULT false,
    role ENUM('owner', 'admin', 'moderator', 'user') DEFAULT 'user'
);

CREATE TABLE routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route VARCHAR(255) NOT NULL,
    changedRoute VARCHAR(255) NOT NULL
);

CREATE TABLE userdata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) DEFAULT NULL,
    role VARCHAR(50) DEFAULT NULL,
    ip VARCHAR(50) NOT NULL UNIQUE,
    country VARCHAR(255) NOT NULL,
    isp VARCHAR(255) NOT NULL
);

CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    header VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    button_text VARCHAR(255) DEFAULT NULL,
    link VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE footer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    link VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE celebrity_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    profile_documents TEXT,
    video VARCHAR(255),
    audio VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);