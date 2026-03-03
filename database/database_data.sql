USE uiu_talenthub;

INSERT INTO users (full_name, student_id, email, password_hash, profile_picture, is_verified)
VALUES
('Purnata Choudhury', '0112330429', 'pchoudhury2330429@bscse.uiu.ac.bd', '$2y$10$8W3x5Q/8E5z/z/8E5z/z/u3PzV/X2rV/W/N0h/f.uD/7T.uOe3PzV/X2', NULL, 1),
('Jonayed Hasan', '0112330671', 'jonayed.hasan@bscse.uiu.ac.bd', '$2y$10$8W3x5Q/8E5z/z/8E5z/z/u3PzV/X2rV/W/N0h/f.uD/7T.uOe3PzV/X2', NULL, 1),
('Fahim Rahman', '0112330874', 'fahim.rahman@bscse.uiu.ac.bd', '$2y$10$8W3x5Q/8E5z/z/8E5z/z/u3PzV/X2rV/W/N0h/f.uD/7T.uOe3PzV/X2', NULL, 1),
('Suvo Ahmed', '0112330456', 'suvo.ahmed@bscse.uiu.ac.bd', '$2y$10$8W3x5Q/8E5z/z/8E5z/z/u3PzV/X2rV/W/N0h/f.uD/7T.uOe3PzV/X2', NULL, 1);

INSERT INTO posts (user_id, type, title, description, content_path, thumbnail_path, likes_count)
VALUES
(1, 'audio', 'Midnight Melodies', 'A calm acoustic performance for late-night study sessions.', '#', 'Images/trending_p1.png', 1200),
(1, 'video', 'UIU Winter Fest 2025', 'The drone shots and highlights from the biggest event of the year.', '#', 'Images/trending_l1.png', 5800),
(2, 'blog', 'A Big Day to Remember', 'Our journey to winning the national onsite hackathon.', 'Blog Page/postpage.php', 'Images/image.jpg', 1400),
(2, 'audio', 'Evening Tunes', 'Experimental Lo-fi beats mixed in the UIU computer lab.', '#', 'Images/trending_s1.png', 900),
(3, 'video', 'CSE Project Highlights', 'A walkthrough of our latest IoT and Robotics projects.', '#', 'Images/trending_w1.png', 300),
(3, 'blog', 'My Study Journey', 'Tips and tricks for surviving the CSE department at UIU.', 'Blog Page/postpage.php', 'Images/image.jpg', 450),
(4, 'audio', 'Morning Beats', 'Energizing music to start your campus day.', '#', 'Images/trending_p1.png', 700);

INSERT INTO likes (user_id, post_id)
VALUES
(1, 3),
(2, 1),
(3, 1),
(4, 2),
(4, 3),
(4, 7);
