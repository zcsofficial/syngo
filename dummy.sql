-- Inserting dummy users
INSERT INTO users (first_name, last_name, email, password, profile_picture, role)
VALUES
('John', 'Doe', 'john.doe@example.com', 'password123', 'https://i.pravatar.cc/150?img=1', 'user'),
('Jane', 'Smith', 'jane.smith@example.com', 'password123', 'https://i.pravatar.cc/150?img=2', 'user'),
('Alice', 'Johnson', 'alice.johnson@example.com', 'password123', 'https://picsum.photos/200/300', 'user'),
('Bob', 'Davis', 'bob.davis@example.com', 'password123', 'https://loremfaces.net/96/id/1.jpg', 'admin');

-- Inserting dummy trips
INSERT INTO trips (title, destination, start_date, end_date, group_size, description, created_by)
VALUES
('Adventure in the Alps', 'Switzerland', '2025-06-01', '2025-06-07', 10, 'An amazing trip to the Swiss Alps for hiking and sightseeing.', 1),
('Beach Getaway', 'Maldives', '2025-07-15', '2025-07-22', 6, 'Relaxing on the beaches of the Maldives.', 2),
('Cultural Tour', 'Japan', '2025-08-10', '2025-08-20', 8, 'Explore Japan’s rich history and modern culture.', 3);

-- Inserting trip members
INSERT INTO trip_members (trip_id, user_id)
VALUES
(1, 1),
(1, 2),
(2, 3),
(3, 4);

-- Inserting dummy communities
INSERT INTO communities (name, description, created_by)
VALUES
('Travel Enthusiasts', 'A community for people who love to travel and share experiences.', 1),
('Adventure Seekers', 'For those who are always looking for the next adventure.', 2);

-- Inserting user communities
INSERT INTO user_communities (user_id, community_id)
VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 2);

-- Inserting dummy notifications
INSERT INTO notifications (user_id, message, is_read)
VALUES
(1, 'New trip to Switzerland available!', false),
(2, 'Your Maldives trip has been confirmed.', true);

-- Inserting activity logs
INSERT INTO activity_log (user_id, activity_type, activity_details)
VALUES
(1, 'Joined Trip', 'Joined the Adventure in the Alps trip.'),
(2, 'Created Community', 'Created the Adventure Seekers community.');

