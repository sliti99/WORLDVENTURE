-- WorldVenture Travel Blog Platform Database Schema

-- Drop database if exists for clean install
DROP DATABASE IF EXISTS worldventure;
CREATE DATABASE worldventure CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE worldventure;

-- Create users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  avatar VARCHAR(255),
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create admins table (legacy support)
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create posts table
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  slug VARCHAR(255) NOT NULL,
  author_id INT NOT NULL,
  status ENUM('draft', 'published', 'archived') DEFAULT 'published',
  reactions INT DEFAULT 0,
  photo_path VARCHAR(255),
  location_lat DECIMAL(10, 8),
  location_lng DECIMAL(11, 8),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create comments table
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  reactions INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create reactions_log table to track user reactions
CREATE TABLE reactions_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('post', 'comment') NOT NULL,
  item_id INT NOT NULL, -- post_id or comment_id
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_reaction (user_id, type, item_id)
) ENGINE=InnoDB;

-- Insert admin user
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@worldventure.com', 'admin123', 'admin'),
('John Doe', 'john@example.com', 'password123', 'user'),
('Jane Smith', 'jane@example.com', 'password123', 'user'),
('Travel Guy', 'travel@example.com', 'password123', 'user');

-- Insert legacy admin
INSERT INTO admins (name, email, password) VALUES 
('Admin', 'admin@worldventure.com', 'admin123');

-- Insert sample posts
INSERT INTO posts (title, content, slug, author_id, status, reactions, photo_path) VALUES 
('Exploring the Hidden Beaches of Thailand', 
'Thailand is known for its beautiful beaches, but there are many hidden gems that most tourists never discover. Last month, I had the opportunity to explore some of these secluded spots. The crystal-clear waters and pristine white sands were absolutely breathtaking. The local food vendors offered some of the best seafood I\'ve ever tasted, grilled right on the beach. If you\'re planning a trip to Thailand, make sure to venture beyond the popular destinations like Phuket and Koh Samui. The smaller, less-known islands offer a more authentic experience without the crowds. My personal favorite was a small beach near Koh Lanta that required a short hike through a jungle path to reach. The effort was completely worth it!',
'exploring-hidden-beaches-thailand', 1, 'published', 5, 'uploads/photos/thailand-beach.jpg'),

('Ultimate Guide to Backpacking in Europe', 
'Backpacking through Europe was one of the most rewarding experiences of my life. Over the course of two months, I visited 12 countries and countless cities. The key to a successful trip is to pack light but smart. I managed to fit everything into a 40L backpack, including clothes for different weather conditions. Transportation in Europe is incredibly efficient. I primarily used trains with a Eurail pass, which proved to be cost-effective for the distance covered. Hostels were my main accommodation choice, not just for budget reasons but also for the social aspect. I met so many incredible people who became travel companions for portions of my journey. Food can be expensive in certain countries, so I often shopped at local markets and prepared simple meals in hostel kitchens. This not only saved money but also gave me a glimpse into local food culture.',
'ultimate-guide-backpacking-europe', 2, 'published', 8, 'uploads/photos/europe-backpacking.jpg'),

('Mountain Trekking in Nepal: A Life-Changing Adventure', 
'The Himalayas have always been on my bucket list, and last year I finally made the journey to Nepal. The trek to Everest Base Camp was physically challenging but spiritually rewarding. The majestic mountain views, combined with the warm hospitality of the Sherpa people, created an unforgettable experience. Preparation is crucial for this type of adventure. I trained for months, focusing on cardio and strength exercises to build endurance. Altitude sickness is a real concern, so taking time to acclimatize properly is essential. I spent a few days in Kathmandu and Lukla before gradually making my way up. The tea houses along the route provide basic but comfortable accommodation, and the dal bhat (lentil soup with rice) offered much-needed energy for the daily treks. The sense of achievement upon reaching Base Camp was indescribable. Standing in the shadow of the world\'s highest peak gave me a new perspective on life and my place in the world.',
'mountain-trekking-nepal-adventure', 3, 'published', 12, 'uploads/photos/nepal-mountains.jpg'),

('Safari Adventures in Tanzania', 
'Witnessing the great migration in the Serengeti was a dream come true. Thousands of wildebeests and zebras crossing the plains, with predators lurking in the shadows, showcased nature in its rawest form. Our guide, a local Maasai with years of experience, knew exactly where to position our vehicle for the best wildlife viewing opportunities. We stayed in a mix of lodges and tented camps, each offering a unique experience. The tented camps, while more basic, provided an immersive experience with nature sounds surrounding us throughout the night. Beyond the Serengeti, we also visited Ngorongoro Crater, which hosts an incredible concentration of wildlife within its natural enclosure. Conservation efforts in Tanzania are commendable, with strict regulations to protect the fragile ecosystem. If you\'re planning a safari, I highly recommend visiting during the migration season, though be prepared for higher prices and more tourists.',
'safari-adventures-tanzania', 4, 'published', 7, 'uploads/photos/tanzania-safari.jpg'),

('Cultural Immersion in Japan', 
'Japan offers a fascinating blend of ancient traditions and cutting-edge technology. My two-week journey took me from the bustling streets of Tokyo to the serene temples of Kyoto. The efficiency of the Japanese transportation system is remarkable, with the Shinkansen (bullet train) connecting major cities with punctual precision. In Tokyo, I explored diverse neighborhoods, each with its own character: Shibuya\'s youthful energy, Shinjuku\'s nightlife, and Asakusa\'s historical charm. A day trip to Mount Fuji provided a peaceful contrast to the urban experience. Kyoto, with its numerous temples and shrines, offered a glimpse into Japan\'s spiritual heritage. I participated in a traditional tea ceremony and stayed one night in a ryokan (traditional inn) with tatami floors and futon bedding. The Japanese cuisine was a highlight of the trip, from high-end sushi restaurants to humble street food stalls. Language can be a barrier, but most Japanese people are incredibly helpful despite communication challenges.',
'cultural-immersion-japan', 1, 'published', 6, 'uploads/photos/japan-temple.jpg');

-- Insert sample comments
INSERT INTO comments (post_id, user_id, content, reactions) VALUES 
(1, 2, 'This is amazing! I\'ve been planning a trip to Thailand. Could you recommend some specific beaches to visit?', 2),
(1, 3, 'Beautiful photos! Did you have any issues finding accommodations near these hidden beaches?', 1),
(2, 4, 'Great guide! How much would you budget per day for a mid-range backpacking experience?', 0),
(3, 2, 'I\'ve always wanted to trek in Nepal. Was it difficult to get permits for the Everest region?', 3),
(3, 3, 'The views look breathtaking! What camera equipment did you use to capture these shots?', 1),
(4, 1, 'Tanzania has been on my bucket list for years. Which season did you visit in?', 0),
(5, 4, 'I love Japanese culture! Did you get to visit any less touristy areas?', 2),
(5, 2, 'The mix of traditional and modern in Japan is fascinating. How did you handle the language barrier?', 1);

-- Insert reaction logs
INSERT INTO reactions_log (user_id, type, item_id) VALUES
(2, 'post', 1),
(3, 'post', 1),
(4, 'post', 1),
(1, 'post', 1),
(2, 'post', 1),
(1, 'post', 2),
(2, 'post', 2),
(3, 'post', 2),
(4, 'post', 2),
(1, 'post', 3),
(2, 'post', 3),
(3, 'post', 3),
(1, 'post', 3),
(2, 'post', 3),
(3, 'post', 3),
(4, 'post', 3),
(1, 'post', 4),
(2, 'post', 4),
(3, 'post', 4),
(4, 'post', 4),
(1, 'post', 5),
(2, 'post', 5),
(3, 'post', 5),
(4, 'post', 5),
(1, 'comment', 1),
(3, 'comment', 1),
(2, 'comment', 4),
(3, 'comment', 4),
(4, 'comment', 4),
(2, 'comment', 7),
(3, 'comment', 7);

-- Create indexes for better performance
CREATE INDEX idx_posts_author ON posts(author_id);
CREATE INDEX idx_comments_post ON comments(post_id);
CREATE INDEX idx_comments_user ON comments(user_id);
CREATE INDEX idx_reactions_user ON reactions_log(user_id);
CREATE INDEX idx_reactions_item ON reactions_log(type, item_id);

-- Set correct counts for reactions (to ensure consistency)
UPDATE posts p
SET p.reactions = (
    SELECT COUNT(*) FROM reactions_log 
    WHERE type = 'post' AND item_id = p.id
);

UPDATE comments c
SET c.reactions = (
    SELECT COUNT(*) FROM reactions_log 
    WHERE type = 'comment' AND item_id = c.id
);
