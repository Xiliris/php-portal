INSERT INTO routes (route, changedRoute) VALUES
("/404", "/404"),
("/401", "/401"),
("/person-of-interest", "/person-of-interest"),
("/coming", "/coming"),
("/news", "/news"),
("/about", "/about"),
("/partners", "/partners"),
("/shop", "/shop"),
("/donate", "/donate"),
("/change-password", "/change-password"),
("/login/random", "/login/random"),
("/logout", "/logout");

INSERT INTO userdata (username, role, ip, country, isp) VALUES
('john_doe', 'user', '192.168.1.1', 'United States', 'Comcast'),
('jane_smith', 'admin', '192.168.1.2', 'Canada', 'Bell Canada'),
('sam_wilson', 'moderator', '192.168.1.3', 'United Kingdom', 'BT Group'),
('lisa_martin', 'owner', '192.168.1.4', 'Australia', 'Telstra'),
('peter_parker', 'user', '192.168.1.5', 'United States', 'Verizon'),
('bruce_wayne', 'moderator', '192.168.1.6', 'France', 'Orange S.A.'),
('clark_kent', 'admin', '192.168.1.7', 'Germany', 'Deutsche Telekom'),
('tony_stark', 'user', '192.168.1.8', 'United States', 'AT&T'),
('natasha_romanoff', 'user', '192.168.1.9', 'Russia', 'Rostelecom'),
('steve_rogers', 'moderator', '192.168.1.10', 'United States', 'Spectrum');

INSERT INTO users (username, password, changed, role) VALUES
("owner", "$2y$10$JaBePlAFAuLAp..QgqGQGuvVaZSwdKNiJZR3H1HwyIQ7fmhNj.U8q", true, "owner");

INSERT INTO about (title, content) VALUES
("About Us", "We are a team of developers working on this project.");

INSERT INTO fixed_track (description, time, moving, enabled) 
VALUES ('First record', 0, FALSE, FALSE);

INSERT INTO socials (name, link)
VALUES ('telegram', "https://adnanskopljak.com/1")
VALUES ('twitter', "https://adnanskopljak.com/2")
VALUES ('facebook', "https://adnanskopljak.com/3")
VALUES ('instagram', "https://adnanskopljak.com/4")