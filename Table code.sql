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

CREATE TABLE celebrity_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)


CREATE TABLE celebrity_event_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    celebrity_profile_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    views INT default 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (celebrity_profile_id) REFERENCES celebrity_profile(id)
);

CREATE TABLE celebrity_event_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES celebrity_event_data(id)
);

CREATE TABLE celebrity_event_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES celebrity_event_data(id)
);

CREATE TABLE celebrity_event_audios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    audio_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES celebrity_event_data(id)
);

CREATE TABLE celebrity_event_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    doc_type VARCHAR(10) NOT NULL;
    document_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES celebrity_event_data(id)
);

CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link VARCHAR(255) NOT NULL,
    image_path TEXT NOT NULL
);

CREATE TABLE about (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);