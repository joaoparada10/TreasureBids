SET search_path TO lbaw24114;

-- Insert Members
INSERT INTO Member (username, first_name, last_name, email, password, profile_pic, credit) VALUES 
('jdoe', 'John', 'Doe', 'jdoe@example.com', 'password1', 'pic1.jpg', 100),
('asmith', 'Alice', 'Smith', 'asmith@example.com', 'password2', 'pic2.jpg', 150),
('bwilliams', 'Bob', 'Williams', 'bwilliams@example.com', 'password3', 'pic3.jpg', 50),
('cjohnson', 'Carol', 'Johnson', 'cjohnson@example.com', 'password4', 'pic4.jpg', 200),
('ddavis', 'David', 'Davis', 'ddavis@example.com', 'password5', 'pic5.jpg', 300);

-- Insert Categories
INSERT INTO Category (name, color) VALUES 
('Electronics', '#FF0000'),
('Books', '#00FF00'),
('Fashion', '#0000FF'),
('Sports', '#FFFF00'),
('Home Decor', '#FF00FF');

-- Insert Auctions
INSERT INTO Auction (owner_id, starting_price, starting_date, end_date, buyout_price, title, picture, description, discount, status) VALUES
(1, 100, NOW(), NOW() + INTERVAL '7 days', 500, 'Vintage Camera', 'camera.jpg', 'A classic vintage camera in excellent condition.', 10, 'Scheduled'),
(2, 50, NOW(), NOW() + INTERVAL '5 days', 300, 'Rare Book', 'book.jpg', 'An original edition of a rare book.', 15, 'Scheduled'),
(3, 200, NOW(), NOW() + INTERVAL '3 days', 1000, 'Designer Handbag', 'handbag.jpg', 'A luxury designer handbag, barely used.', 20, 'Scheduled'),
(4, 20, NOW(), NOW() + INTERVAL '10 days', 150, 'Yoga Mat', 'yogamat.jpg', 'Eco-friendly yoga mat, perfect for all practices.', 5, 'Scheduled'),
(5, 75, NOW(), NOW() + INTERVAL '2 days', 400, 'Smartphone', 'smartphone.jpg', 'Latest model smartphone with all accessories.', 12, 'Scheduled'),
(8,50,NOW(),NOW()+INTERVAL '3days',500,'Filler', 'filler.jpg','Best filler in the west.',1,Scheduled);

-- Insert Bids
INSERT INTO Bid (user_id, auction_id, value) VALUES
(2, 1, 120),
(3, 1, 150),
(1, 2, 60),
(5, 3, 220),
(4, 3, 250);

-- Insert Transactions
INSERT INTO Transaction (buyer_id, auction_id, price) VALUES
(1, 1, 150),
(2, 2, 60),
(3, 3, 250);

-- Insert Report Reasons
INSERT INTO ReportReason (name) VALUES 
('Inappropriate content'),
('Misleading description'),
('Fake product'),
('Spam'),
('Fraud');

-- Insert Reports
INSERT INTO Report (reporter_id, auction_id, comment, reason_id) VALUES
(2, 1, 'This auction seems suspicious.', 1),
(3, 2, 'The description doesn’t match the item.', 2),
(1, 3, 'Fake product listing.', 3);

-- Insert AuctionCategory
INSERT INTO AuctionCategory (auction_id, category_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 1);
-- Populate the Rating table
INSERT INTO Rating (rating_value, comment, rater_id, rated_auction_id, date) VALUES
    (5, 'Excellent seller!', 1, 2, '2023-02-10 11:00:00'),
    (3, 'Average experience.', 2, 1, '2023-02-15 14:30:00');

-- Populate the Notification table
INSERT INTO Notification (notified_id, urgency, text, url, date) VALUES
    (1, 'High', 'Your auction is about to end!', '/auctions/1', '2023-01-05 09:00:00'),
    (2, 'Low', 'New bid placed on your item.', '/auctions/2', '2023-02-02 11:00:00');

-- Populate the AuctionNotification table
INSERT INTO AuctionNotification (notification_id, auction_id) VALUES
(1, 1),
(2, 2);

-- Populate the MemberNotification table
INSERT INTO MemberNotification (notification_id, member_id) VALUES
(1, 1),
(2, 2);

-- Populate the RatingNotification table
INSERT INTO RatingNotification (notification_id, rating_id) VALUES
(1, 1),
(2, 2);

-- Populate the TransactionNotification table
INSERT INTO TransactionNotification (notification_id, transaction_id) VALUES
(1, 1),
(2, 2);

-- Populate the ItemRarity table
INSERT INTO ItemRarity (name, color) VALUES
    ('Common', '#C0C0C0'),
    ('Rare', '#FFD700'),
    ('Epic', '#8A2BE2'),
    ('Legendary', '#FF4500');

-- Populate the FollowedAuction table
INSERT INTO FollowedAuction (follower_id, auction_id, date) VALUES
    (1, 2, '2023-01-15 08:00:00'),
    (2, 1, '2023-01-16 09:30:00');
