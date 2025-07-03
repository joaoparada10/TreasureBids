-- DROP SCHEMA IF EXISTS lbaw24114 CASCADE;
CREATE SCHEMA IF NOT EXISTS lbaw24114;
SET search_path TO lbaw24114;

DROP TABLE IF EXISTS FollowedAuction CASCADE;
DROP TABLE IF EXISTS ItemRarity CASCADE;
DROP TABLE IF EXISTS Notification CASCADE;
DROP TABLE IF EXISTS AuctionNotification CASCADE;
DROP TABLE IF EXISTS MemberNotification CASCADE;
DROP TABLE IF EXISTS RatingNotification CASCADE;
DROP TABLE IF EXISTS TransactionNotification CASCADE;
DROP TABLE IF EXISTS Rating CASCADE;
DROP TABLE IF EXISTS Admin CASCADE;
DROP TABLE IF EXISTS AuctionCategory CASCADE;
DROP TABLE IF EXISTS Report CASCADE;
DROP TABLE IF EXISTS ReportReason CASCADE;
DROP TABLE IF EXISTS Transaction CASCADE;
DROP TABLE IF EXISTS Bid CASCADE;
DROP TABLE IF EXISTS Auction CASCADE;
DROP TABLE IF EXISTS Category CASCADE;
DROP TABLE IF EXISTS Member CASCADE;

DROP TYPE IF EXISTS auction_status CASCADE;
DROP TYPE IF EXISTS notification_urgency CASCADE;



----------------------------------------TABLE CREATION----------------------------------------

-- Create the Member table
CREATE TABLE Member (
    id SERIAL PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    first_name TEXT,
    last_name TEXT,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    profile_pic TEXT,
    credit NUMERIC DEFAULT 0 CHECK (credit >= 0),
    blocked BOOL NOT NULL DEFAULT False,
    remember_token TEXT,
    address TEXT
);

-- Create the Category table
CREATE TABLE Category (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    color TEXT NOT NULL
);

CREATE TYPE auction_status AS ENUM ('Scheduled', 'Active', 'Concluded', 'Cancelled');

-- Create the Auction table
CREATE TABLE Auction (
    id SERIAL PRIMARY KEY,
    owner_id INTEGER REFERENCES Member(id),
    starting_price INTEGER CHECK (starting_price >= 0) NOT NULL,
    starting_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP CHECK (end_date > starting_date) NOT NULL,
    buyout_price INTEGER CHECK (buyout_price >= starting_price),
    title TEXT NOT NULL,
    picture TEXT NOT NULL,
    description TEXT NOT NULL,
    discount FLOAT,
    status auction_status NOT NULL,
	CONSTRAINT chk_end_after_start CHECK (end_date > starting_date)  -- Ensures end_date is after start_date
);

-- Create the Bid table
CREATE TABLE Bid (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES Member(id),
    auction_id INTEGER REFERENCES Auction(id) ON DELETE CASCADE,
    value NUMERIC NOT NULL,
    date TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Create the Transaction table
CREATE TABLE Transaction (
    id SERIAL PRIMARY KEY,
    buyer_id INTEGER REFERENCES Member(id),
    auction_id INTEGER REFERENCES Auction(id) ON DELETE CASCADE,
    price NUMERIC NOT NULL CHECK (price > 0),
    date TIMESTAMP NOT NULL DEFAULT NOW()
);


CREATE TABLE ReportReason (
    id SERIAL PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);

-- Create the AuctionReport table
CREATE TABLE Report (
    id SERIAL PRIMARY KEY,
    reporter_id INTEGER REFERENCES Member(id),
    auction_id INTEGER REFERENCES Auction(id) ON DELETE CASCADE,
    comment TEXT,
    reason_id INTEGER REFERENCES ReportReason(id) ON DELETE CASCADE,
    date TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Create the AuctionCategory table
CREATE TABLE AuctionCategory (
    id SERIAL PRIMARY KEY,
    auction_id INTEGER REFERENCES Auction(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES Category(id) ON DELETE CASCADE
);

CREATE TABLE Admin(
    id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    remember_token TEXT
);

CREATE TABLE Rating (
    id SERIAL PRIMARY KEY,
    rating_value INTEGER NOT NULL CHECK (rating_value >= 1 AND rating_value <= 5),
    comment TEXT,
    date TIMESTAMP NOT NULL DEFAULT NOW(),
    rater_id INTEGER REFERENCES Member(id),
    rated_auction_id INTEGER REFERENCES Auction(id) ON DELETE CASCADE
);


CREATE TYPE notification_urgency AS ENUM ('Low', 'Medium', 'High');

CREATE TABLE Notification(
    id SERIAL PRIMARY KEY,
    notified_id INTEGER REFERENCES Member(id) ON DELETE CASCADE,
    urgency notification_urgency NOT NULL,
    text TEXT NOT NULL,
    url TEXT,
    date TIMESTAMP NOT NULL DEFAULT NOW(),
    seen BOOL NOT NULL DEFAULT FALSE
);

CREATE TABLE AuctionNotification(
    notification_id INTEGER PRIMARY KEY REFERENCES Notification(id) ON DELETE CASCADE,
    auction_id INTEGER REFERENCES Auction(id)
);

CREATE TABLE MemberNotification(
    notification_id INTEGER PRIMARY KEY REFERENCES Notification(id) ON DELETE CASCADE,
    member_id INTEGER REFERENCES Member(id)
);

CREATE TABLE RatingNotification(
    notification_id INTEGER PRIMARY KEY REFERENCES Notification(id) ON DELETE CASCADE,
    rating_id INTEGER REFERENCES Rating(id)
);

CREATE TABLE TransactionNotification(
    notification_id INTEGER PRIMARY KEY REFERENCES Notification(id) ON DELETE CASCADE,
    transaction_id INTEGER REFERENCES Transaction(id)
);


CREATE TABLE ItemRarity(
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    color TEXT NOT NULL UNIQUE
);

CREATE TABLE FollowedAuction(
    id SERIAL PRIMARY KEY,
    follower_id INTEGER REFERENCES Member(id) ON DELETE CASCADE,
    auction_id INTEGER REFERENCES Auction(id) ON DELETE CASCADE,
    date TIMESTAMP NOT NULL DEFAULT NOW()
);






----------------------------------------AUXILIARY FUNCTIONS----------------------------------------


CREATE OR REPLACE FUNCTION insert_notification(
    p_notified_id INTEGER,
    p_urgency notification_urgency,
    p_text TEXT,
    p_url TEXT,
    p_type TEXT,  -- Type can be 'auction', 'member', 'rating', or 'transaction'
    p_related_id INTEGER  -- This represents auction_id, member_id, rating_id, or transaction_id based on the type
)
RETURNS VOID AS $$
DECLARE
    notification_id INTEGER;
BEGIN
    -- Insert into the main Notification table
    INSERT INTO Notification (notified_id, urgency, text, url)
    VALUES (p_notified_id, p_urgency, p_text, p_url)
    RETURNING id INTO notification_id;

    -- Based on the notification type, insert into the appropriate subtype table
    IF p_type = 'auction' THEN
        INSERT INTO AuctionNotification (notification_id, auction_id)
        VALUES (notification_id, p_related_id);
    ELSIF p_type = 'member' THEN
        INSERT INTO MemberNotification (notification_id, member_id)
        VALUES (notification_id, p_related_id);
    ELSIF p_type = 'rating' THEN
        INSERT INTO RatingNotification (notification_id, rating_id)
        VALUES (notification_id, p_related_id);
    ELSIF p_type = 'transaction' THEN
        INSERT INTO TransactionNotification (notification_id, transaction_id)
        VALUES (notification_id, p_related_id);
    ELSE
        RAISE EXCEPTION 'Unknown notification type: %', p_type;
    END IF;
END;
$$ LANGUAGE plpgsql;




CREATE OR REPLACE FUNCTION end_auctions() 
RETURNS VOID AS $$
DECLARE
    auction_record RECORD;
    highest_bid RECORD;
    auction_title TEXT;
BEGIN
    -- Loop over all auctions that have ended but are not yet marked as 'Concluded'
    FOR auction_record IN 
        SELECT id, owner_id, title 
        FROM Auction 
        WHERE end_date < NOW() AND status = 'Active'
    LOOP
        -- Mark the auction as Concluded
        UPDATE Auction
        SET status = 'Concluded'
        WHERE id = auction_record.id;

        -- Find the highest bid for this auction, if any
        SELECT user_id, value INTO highest_bid
        FROM Bid
        WHERE auction_id = auction_record.id
        ORDER BY value DESC, date ASC  -- Highest bid, earliest in case of a tie
        LIMIT 1;

        -- Notify the auction owner that the auction has ended
        PERFORM insert_notification(
            auction_record.owner_id,
            'High'::notification_urgency,
            'Your auction "' || auction_record.title || '" has concluded.',
            '/auctions/' || auction_record.id,   -- URL to view the auction
            'auction',
            auction_record.id
        );

        -- If there's a highest bidder, notify them and create a transaction
        IF highest_bid.user_id IS NOT NULL THEN
            -- Notify the highest bidder that they won the auction
            PERFORM insert_notification(
                highest_bid.user_id,
                'High'::notification_urgency,
                'Congratulations! You won the auction "' || auction_record.title || '"!',
                '/auctions/' || auction_record.id,  -- URL to view the auction
                'auction',
                auction_record.id
            );

            -- Insert a transaction record for the auction sale
            INSERT INTO Transaction (buyer_id, auction_id, price, date)
            VALUES (highest_bid.user_id, auction_record.id, highest_bid.value, NOW());

            PERFORM add_credit(auction_record.owner_id, highest_bid.value);
        END IF;
    END LOOP;

    -- Handle scheduled auctions that need to be activated
    FOR auction_record IN 
        SELECT id, owner_id, title 
        FROM Auction 
        WHERE starting_date < NOW() AND status = 'Scheduled'
    LOOP
        -- Mark the auction as Active
        UPDATE Auction
        SET status = 'Active'
        WHERE id = auction_record.id;

        -- Notify the auction owner that the auction has started
        PERFORM insert_notification(
            auction_record.owner_id,
            'High'::notification_urgency,
            'Your auction "' || auction_record.title || '" has started.',
            '/auctions/' || auction_record.id,   -- URL to view the auction
            'auction',
            auction_record.id
        );
    END LOOP;
END;
$$ LANGUAGE plpgsql;




----------------------------------------TRIGGERS----------------------------------------



--CREATES NOTIFICATION FOR OWNER AND HIGHEST BIDDER AFTER INSERTING BID--
CREATE OR REPLACE FUNCTION notify_owner_and_highest_bidder() RETURNS TRIGGER AS $$
DECLARE
    auction_owner_id INTEGER;
    previous_highest_bidder_id INTEGER;
    auction_title TEXT;
    auction_url TEXT := '/auctions/' || NEW.auction_id;  -- Construct URL for the auction
BEGIN
    -- Fetch the auction owner's ID and title
    SELECT owner_id, title INTO auction_owner_id, auction_title
    FROM Auction
    WHERE id = NEW.auction_id;

    -- Insert notification for the auction owner using insert_notification function
    PERFORM insert_notification(
        auction_owner_id,
        'Medium',
        'A new bid has been placed on your auction "' || auction_title || '".',
        auction_url,
        'auction',
        NEW.auction_id
    );

    -- Fetch the previous highest bidder, if any
    SELECT user_id INTO previous_highest_bidder_id
    FROM Bid
    WHERE auction_id = NEW.auction_id
    ORDER BY value DESC, date DESC
    LIMIT 1
    OFFSET 1;  -- Offset 1 to get the previous highest bid before the new one

    -- Notify the previous highest bidder if they exist
    IF previous_highest_bidder_id IS NOT NULL THEN
        PERFORM insert_notification(
            previous_highest_bidder_id,
            'Medium',
            'You have been outbid on the auction "' || auction_title || '".',
            auction_url,
            'auction',
            NEW.auction_id
        );
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Trigger to call notify_owner_and_highest_bidder function after a new bid is inserted
CREATE OR REPLACE TRIGGER trigger_notify_owner_and_highest_bidder
AFTER INSERT ON Bid
FOR EACH ROW
EXECUTE FUNCTION notify_owner_and_highest_bidder();






--WHEN AUCTIONS END DATE IS NEAR AND BID IS INSERTED: 
--CREATES NOTIFICATION TO AUCTION OWNER
--AND EXTENDS AUCTION END DATE--
CREATE OR REPLACE FUNCTION extend_auction_end_date() RETURNS TRIGGER AS $$
DECLARE
    time_threshold INTERVAL := '15 minutes';  -- Time threshold for extending the auction
    extension_duration INTERVAL := '30 minutes';  -- Extension duration
    auction_owner_id INTEGER;
    new_end_date TIMESTAMP;
    auction_title TEXT;
    auction_url TEXT := '/auctions/' || NEW.auction_id;  -- Construct URL for the auction
BEGIN
    -- Fetch auction details including owner and title
    SELECT owner_id, end_date, title INTO auction_owner_id, new_end_date, auction_title
    FROM Auction
    WHERE id = NEW.auction_id;

    -- Check if bid is placed within the last 15 minutes of the auction
    IF new_end_date - NOW() <= time_threshold THEN
        -- Extend the auction's end date by the specified duration
        new_end_date := new_end_date + extension_duration;

        -- Update the auction's end date
        UPDATE Auction SET end_date = new_end_date WHERE id = NEW.auction_id;

        -- Notify the auction owner about the extension
        PERFORM insert_notification(
            auction_owner_id,
            'Medium',
            'The duration of your auction "' || auction_title || '" has been extended.',
            auction_url,
            'auction',
            NEW.auction_id
        );
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Create the trigger to activate the function on bid insert
CREATE OR REPLACE TRIGGER trigger_extend_auction_end_date
AFTER INSERT ON Bid
FOR EACH ROW
EXECUTE FUNCTION extend_auction_end_date();




CREATE OR REPLACE FUNCTION notify_transaction() 
RETURNS TRIGGER AS $$
DECLARE
    seller_id INTEGER;
    auction_title TEXT;
BEGIN
    -- Get the auction owner ID and title based on the auction ID in the transaction
    SELECT owner_id, title INTO seller_id, auction_title FROM Auction WHERE id = NEW.auction_id;

    -- Notify the buyer
    PERFORM insert_notification(
        NEW.buyer_id,
        'Medium'::notification_urgency,
        'You successfully bought the item ' || auction_title || '.',
        '/transactions/' || NEW.id,
        'transaction',  
        NEW.id  
    );

    -- Notify the seller
    PERFORM insert_notification(
        seller_id,
        'Medium'::notification_urgency,
        'You successfully sold the item ' || auction_title || '.',
        '/transactions/' || NEW.id,
        'transaction',  
        NEW.id  
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Step 2: Create the trigger to execute the function after an insert on Transaction
CREATE OR REPLACE TRIGGER transaction_notification_trigger
AFTER INSERT ON Transaction
FOR EACH ROW
EXECUTE FUNCTION notify_transaction();




CREATE OR REPLACE FUNCTION notify_rating() 
RETURNS TRIGGER AS $$
DECLARE
    rated_member_id INTEGER;
    rater_username TEXT;
BEGIN

    SELECT owner_id INTO rated_member_id FROM Auction WHERE id = NEW.rated_auction_id;
    SELECT username INTO rater_username FROM Member WHERE id = NEW.rater_id;

    --notify the rated
    PERFORM insert_notification(
    rated_member_id,
    'Medium'::notification_urgency,
    rater_username || ' gave you ' || NEW.rating_value || ' stars!',
    '/profile/',
    'rating',  
    NEW.id  
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Step 2: Create the trigger to execute the function after an insert on Rating
CREATE OR REPLACE TRIGGER rating_notification_trigger
AFTER INSERT ON Rating
FOR EACH ROW
EXECUTE FUNCTION notify_rating();







----------------------------------------TRANSACTIONS----------------------------------------


CREATE OR REPLACE FUNCTION place_bid(
    p_user_id INTEGER,
    p_auction_id INTEGER,
    p_value NUMERIC
)
RETURNS VOID AS $$
DECLARE
    last_bid_value NUMERIC;
BEGIN
    -- Set the transaction isolation level
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

    -- Get the last bid value for the specified auction
    SELECT MAX(value) INTO last_bid_value
    FROM Bid
    WHERE auction_id = p_auction_id;

    -- Check if the new bid is higher than the last bid
    IF p_value > COALESCE(last_bid_value, 0) THEN
        BEGIN
            -- Insert the new bid
            INSERT INTO Bid (user_id, auction_id, value, date)
            VALUES (p_user_id, p_auction_id, p_value, NOW());
            
            -- Commit the transaction implicitly
            RETURN;
        EXCEPTION WHEN OTHERS THEN
            -- Handle the error and raise a notice, exiting the function
            RAISE NOTICE 'Failed to place bid. No partial changes were applied.';
            RETURN;
        END;
    ELSE
        -- If the bid is not higher than the last bid, raise a notice and do nothing
        RAISE NOTICE 'Bid value is too low to be accepted.';
    END IF;
END;
$$ LANGUAGE plpgsql;





CREATE OR REPLACE FUNCTION delete_member(p_member_id INTEGER) RETURNS VOID AS $$
BEGIN
    -- Set the transaction isolation level to SERIALIZABLE for strict isolation
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

    -- Check if the member has any active auctions
    IF EXISTS (SELECT 1 FROM Auction WHERE owner_id = p_member_id AND status = 'Active'::auction_status) THEN
        RAISE EXCEPTION 'Cannot delete member with active auctions';
    END IF;

    -- Begin the deletion process
    BEGIN
        -- Nullify reporter_id in Report table
        UPDATE Report 
        SET reporter_id = NULL
        WHERE reporter_id = p_member_id;

        -- Delete notifications for this user
        DELETE FROM Notification
        WHERE notified_id = p_member_id;

        -- Nullify owner_id in Auction table
        UPDATE Auction 
        SET owner_id = NULL
        WHERE owner_id = p_member_id;

        -- Nullify user_id in Bid table
        UPDATE Bid
        SET user_id = NULL
        WHERE user_id = p_member_id;

        -- Nullify and anonymize rater_id and comments in Rating table
        UPDATE Rating
        SET rater_id = NULL, comment = '[Deleted User]'
        WHERE rater_id = p_member_id;

        -- Nullify buyer_id in Transaction table
        UPDATE Transaction
        SET buyer_id = NULL
        WHERE buyer_id = p_member_id;

        -- Delete followed auctions for this user
        DELETE FROM FollowedAuction
        WHERE follower_id = p_member_id;

        -- Finally, delete the member
        DELETE FROM Member
        WHERE id = p_member_id;

    EXCEPTION WHEN OTHERS THEN
        -- Raise an error notice and exit the function if any operation fails
        RAISE NOTICE 'An error occurred while deleting the member and related data. No partial changes were applied.';
        RETURN;  -- Exit the function to indicate that the transaction failed
    END;
END;
$$ LANGUAGE plpgsql;



CREATE OR REPLACE FUNCTION buyout_now(p_buyer_id INTEGER, p_auction_id INTEGER) RETURNS VOID AS $$
DECLARE
    auction_owner_id INTEGER;
    buyer_credit NUMERIC;
    auction_price NUMERIC;
BEGIN
    -- Set the transaction isolation level to ensure strict isolation
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

    BEGIN
        -- Get the auction owner, buyout price, and the buyer's current credit
        SELECT owner_id, buyout_price INTO auction_owner_id, auction_price
        FROM Auction 
        WHERE id = p_auction_id AND status = 'Active'::auction_status;

        -- Check if the buyout price is valid (should be non-null and positive)
        IF auction_price IS NULL OR auction_price <= 0 THEN
            RAISE EXCEPTION 'Invalid or missing buyout price for this auction';
        END IF;

        -- Get the buyer's credit and check if sufficient
        SELECT credit INTO buyer_credit FROM Member WHERE id = p_buyer_id;
        IF buyer_credit < auction_price THEN
            RAISE EXCEPTION 'Insufficient credit for this purchase';
        END IF;

        -- Deduct the buyout price from the buyer's credit
        UPDATE Member
        SET credit = credit - auction_price
        WHERE id = p_buyer_id;

        -- Add the buyout price to the auction owner's credit
        UPDATE Member
        SET credit = credit + auction_price
        WHERE id = auction_owner_id;

        -- Close the auction by updating its status to 'Concluded'
        UPDATE Auction
        SET status = 'Concluded'::auction_status
        WHERE id = p_auction_id;

        -- Insert the buyout transaction
        INSERT INTO Transaction (buyer_id, auction_id, price, date)
        VALUES (p_buyer_id, p_auction_id, auction_price, NOW());



    EXCEPTION WHEN OTHERS THEN
        RAISE NOTICE 'An error occurred during buyout. No changes were applied.';
        RETURN;
    END;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION add_credit(p_member_id INTEGER, p_amount NUMERIC) RETURNS VOID AS $$
BEGIN
    -- Update the member's credit balance
    UPDATE Member
    SET credit = credit + p_amount
    WHERE id = p_member_id;

    -- Notify the member of credit addition
    PERFORM insert_notification(
        p_member_id,
        'Medium',
        'Credit has been added to your account.',
        '/profile',
        'member',
        p_member_id
    );
END;
$$ LANGUAGE plpgsql;




----------------------------------------INDEXES----------------------------------------




DROP INDEX IF EXISTS idx_auction_title;
DROP INDEX IF EXISTS idx_auction_end_date;
DROP INDEX IF EXISTS idx_auction_category;

-- Performance indexes

CREATE INDEX idx_auction_title ON Auction USING btree(title); -- compensa +/-

CREATE INDEX idx_auction_end_date ON Auction USING btree(end_date);
CLUSTER Auction USING idx_auction_end_date; -- não compensa muito

CREATE INDEX idx_auction_category ON AuctionCategory USING btree(category_id);
CLUSTER AuctionCategory USING idx_auction_category;

-- Full-text Search Indexes

DO $$
BEGIN
    -- Check if the column search_vector exists, and add it if it doesn't
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'auction' 
        AND column_name = 'search_vector'
    ) THEN
        ALTER TABLE Auction ADD COLUMN search_vector tsvector;
    END IF;
END $$;

UPDATE Auction 
SET search_vector = to_tsvector('english', title || ' ' || description); -- populates the new column

CREATE OR REPLACE FUNCTION update_search_vector() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.search_vector = (setweight(to_tsvector('english', NEW.title), 'A') || 
        setweight(to_tsvector('english', NEW.description), 'B'));
    END IF;
    IF TG_OP = 'UPDATE' THEN
        IF (NEW.title <> OLD.title OR NEW.description <> OLD.description) THEN 
            NEW.search_vector = (setweight(to_tsvector('english', NEW.title), 'A') || 
            setweight(to_tsvector('english', NEW.description), 'B'));
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to call update_search_vector() on title or description changes
CREATE OR REPLACE TRIGGER trigger_update_search_vector
BEFORE INSERT OR UPDATE ON Auction
FOR EACH ROW
EXECUTE FUNCTION update_search_vector();

-- GIN index on the search_vector column to optimize full-text search
CREATE INDEX idx_auction_search_vector ON Auction USING GIN(search_vector);

----------------------------------------INSERTS----------------------------------------

-- Insert Members
INSERT INTO Member (username, first_name, last_name, email, password, profile_pic, credit, address, blocked, remember_token) VALUES
('daniel_hernandez', 'Daniel', 'Hernandez', 'daniel_hernandez@example.com', 'password001', 'pic1.jpg', 4839200, 'Washington Street, 100', TRUE, 'token001'),
('joao_brown', 'João', 'Brown', 'joao_brown@example.com', 'password002', 'pic2.jpg', 7293010, 'Main Avenue, 45', FALSE, 'token002'),
('william_taylor', 'William', 'Taylor', 'william_taylor@example.com', 'password003', 'pic3.jpg', 950210, 'Broadway, 78', FALSE, 'token003'),
('david_garcia', 'David', 'Garcia', 'david_garcia@example.com', 'password004', 'pic4.jpg', 390012, 'Elm Street, 20', TRUE, 'token004'),
('robert_miller', 'Robert', 'Miller', 'robert_miller@example.com', 'password005', 'pic5.jpg', 6720193, 'Highland Road, 15', FALSE, 'token005'),
('anna_thomas', 'Anna', 'Thomas', 'anna_thomas@example.com', 'password006', 'pic6.jpg', 2384019, 'Sunset Boulevard, 200', FALSE, 'token006'),
('user', 'Chris', 'Martin', 'user@mail.com', '$2y$10$QjRjMaC2S0hKCBmZMVHQ6uJtr8YBkayymkIGaWJ7K7TEDZ/v2M5ea', 'pic36.jpg', 999999999, 'Washington Street, 500', FALSE, ''),
('anna_garcia', 'Anna', 'Garcia', 'anna_garcia@example.com', 'password007', 'pic7.jpg', 1298930, 'Lakeview Drive, 56', TRUE, 'token007'),
('jane_martinez', 'Jane', 'Martinez', 'jane_martinez@example.com', 'password008', 'pic8.jpg', 5492830, 'Maple Street, 89', FALSE, 'token008'),
('laura_harris', 'Laura', 'Harris', 'laura_harris@example.com', 'password009', 'pic9.jpg', 981273, 'Oak Lane, 10', TRUE, 'token009'),
('alexander_moore', 'Alexander', 'Moore', 'alexander_moore@example.com', 'password010', 'pic10.jpg', 4081923, 'Cedar Avenue, 35', FALSE, 'token010'),
('robert_harris', 'Robert', 'Harris', 'robert_harris@example.com', 'password011', 'pic11.jpg', 7392812, 'Pine Street, 98', TRUE, 'token011'),
('laura_hernandez', 'Laura', 'Hernandez', 'laura_hernandez@example.com', 'password012', 'pic12.jpg', 3124980, 'Birch Avenue, 76', FALSE, 'token012'),
('william_doe', 'William', 'Doe', 'william_doe@example.com', 'password013', 'pic13.jpg', 4829341, 'Willow Lane, 34', FALSE, 'token013'),
('anna_doe', 'Anna', 'Doe', 'anna_doe@example.com', 'password014', 'pic14.jpg', 5839204, 'Chestnut Street, 22', TRUE, 'token014'),
('william_thomas', 'William', 'Thomas', 'william_thomas@example.com', 'password015', 'pic15.jpg', 7298310, 'Elmwood Drive, 15', FALSE, 'token015'),
('laura_miller', 'Laura', 'Miller', 'laura_miller@example.com', 'password016', 'pic16.jpg', 2394823, 'Hickory Road, 67', TRUE, 'token016'),
('daniel_smith', 'Daniel', 'Smith', 'daniel_smith@example.com', 'password017', 'pic17.jpg', 5839201, 'Maple Avenue, 44', FALSE, 'token017'),
('jane_white', 'Jane', 'White', 'jane_white@example.com', 'password018', 'pic18.jpg', 2948301, 'Oakwood Street, 59', TRUE, 'token018'),
('lucas_anderson', 'Lucas', 'Anderson', 'lucas_anderson@example.com', 'password019', 'pic19.jpg', 102394, 'Spruce Lane, 12', FALSE, 'token019'),
('olivia_harris', 'Olivia', 'Harris', 'olivia_harris@example.com', 'password020', 'pic20.jpg', 583010, 'Sycamore Road, 88', FALSE, 'token020'),
('olivia_brown', 'Olivia', 'Brown', 'olivia_brown@example.com', 'password021', 'pic21.jpg', 2094381, 'Aspen Street, 90', TRUE, 'token021'),
('alexander_hernandez', 'Alexander', 'Hernandez', 'alexander_hernandez@example.com', 'password022', 'pic22.jpg', 8329104, 'Cypress Avenue, 16', TRUE, 'token022'),
('jessica_miller', 'Jessica', 'Miller', 'jessica_miller@example.com', 'password023', 'pic23.jpg', 2381045, 'Poplar Drive, 24', FALSE, 'token023'),
('laura_garcia', 'Laura', 'Garcia', 'laura_garcia@example.com', 'password024', 'pic24.jpg', 4930129, 'Dogwood Street, 64', TRUE, 'token024'),
('daniel_davis', 'Daniel', 'Davis', 'daniel_davis@example.com', 'password025', 'pic25.jpg', 5723104, 'Magnolia Lane, 80', FALSE, 'token025'),
('matthew_martinez', 'Matthew', 'Martinez', 'matthew_martinez@example.com', 'password026', 'pic26.jpg', 3981205, 'Willow Avenue, 35', TRUE, 'token026'),
('alexander_miller', 'Alexander', 'Miller', 'alexander_miller@example.com', 'password027', 'pic27.jpg', 4829301, 'Cherry Street, 50', FALSE, 'token027'),
('laura_martin', 'Laura', 'Martin', 'laura_martin@example.com', 'password028', 'pic28.jpg', 830291, 'Holly Drive, 18', TRUE, 'token028'),
('david_taylor', 'David', 'Taylor', 'david_taylor@example.com', 'password029', 'pic29.jpg', 4928302, 'Peach Lane, 14', FALSE, 'token029'),
('michael_white', 'Michael', 'White', 'michael_white@example.com', 'password030', 'pic30.jpg', 3940201, 'Ivy Road, 99', TRUE, 'token030'),
('jessica_moore', 'Jessica', 'Moore', 'jessica_moore@example.com', 'password031', 'pic31.jpg', 2381048, 'Alder Avenue, 47', FALSE, 'token031'),
('sarah_white', 'Sarah', 'White', 'sarah_white@example.com', 'password032', 'pic32.jpg', 5902841, 'Hazel Drive, 82', TRUE, 'token032'),
('john_smith', 'John', 'Smith', 'john_smith@example.com', 'password033', 'pic33.jpg', 2849302, 'Fir Street, 73', FALSE, 'token033'),
('robert_anderson', 'Robert', 'Anderson', 'robert_anderson@example.com', 'password034', 'pic34.jpg', 5839200, 'Walnut Road, 37', TRUE, 'token034'),
('olivia_garcia', 'Olivia', 'Garcia', 'olivia_garcia@example.com', 'password035', 'pic35.jpg', 4829301, 'Hemlock Lane, 40', FALSE, 'token035'),
('basic', 'Bruno', 'Fernandes', 'bruno@example.com', '$2y$10$mXReWl0UYdLsdaRlM0N1Bek.r7qBwl.xNN6Fgb53pgWz3/sdBpPou', 'pic37.jpg', 0, 'Rua da Alegria, 100', FALSE, '');

-- Insert Categories
INSERT INTO Category (name, color) VALUES 
('Electronics', '#FF0000'),
('Books', '#00FF00'),
('Fashion', '#0000FF'),
('Sports', '#FFFF00'),
('Home Decor', '#FF00FF'),
('Furniture', '#A1FBFD'),
('Clothing', '#D0A985'),
('Toys', '#98C9B9'),
('Music', '#18E53D'),
('Beauty', '#62E2FA'),
('Gardening', '#091D41'),
('Health', '#26A8F4'),
('Automotive', '#1D1AA4'),
('Education', '#8474BA'),
('Office Supplies', '#B7BAA6'),
('Groceries', '#541EFF'),
('Pet Supplies', '#32141B'),
('Jewelry', '#6CAC69'),
('Footwear', '#50EB48'),
('Art Supplies', '#AA3893'),
('Travel', '#D2DABE'),
('Games', '#63A0D2'),
('Movies', '#FD91A6'),
('Fitness', '#45FC53'),
('Baby Products', '#E36D5F'),
('Tools', '#B4FE04'),
('Outdoor', '#F582F8'),
('Stationery', '#C0D2EE'),
('Kitchenware', '#54F5AB'),
('Photography', '#F51546'),
('DIY', '#8D1CC3');

-- Insert Auctions
INSERT INTO Auction (owner_id, starting_price, starting_date, end_date, buyout_price, title, picture, description, discount, status) VALUES
(1, 2000000, NOW() - INTERVAL '10 days', NOW() - INTERVAL '5 days', NULL, 'Luxury Car', 'auction1.jpg', 'A high-end luxury car in excellent condition.', 15, 'Concluded'),
(2, 1500, NOW() - INTERVAL '1 day', NOW() + INTERVAL '4 days', NULL, 'Antique Vase', 'auction2.jpg', 'An exquisite antique vase from the 18th century.', 10, 'Active'),
(3, 500, NOW(), NOW() + INTERVAL '3 days', NULL, 'Designer Watch', 'auction3.jpg', 'A stylish designer watch with minimal wear.', 20, 'Active'),
(4, 50, NOW() + INTERVAL '2 days', NOW() + INTERVAL '7 days', NULL, 'Vintage Mug', 'auction4.jpg', 'A rare vintage mug with unique artwork.', 5, 'Scheduled'),
(5, 25000, NOW() - INTERVAL '15 days', NOW() - INTERVAL '10 days', NULL, 'Electric Scooter', 'auction5.jpg', 'A modern electric scooter in good condition.', 12, 'Concluded'),
(6, 350000, NOW() - INTERVAL '7 days', NOW() + INTERVAL '7 days', NULL, 'Motorcycle', 'auction6.webp', 'A fast and reliable motorcycle.', 8, 'Active'),
(7, 100, NOW(), NOW() + INTERVAL '2 days', NULL, 'Kitchen Blender', 'auction7.jpg', 'A high-performance kitchen blender with all accessories.', 10, 'Active'),
(8, 7500, NOW() + INTERVAL '3 days', NOW() + INTERVAL '10 days', NULL, 'Gaming Console', 'auction8.jpg', 'A brand-new gaming console with controller.', 15, 'Scheduled'),
(9, 800000, NOW() - INTERVAL '20 days', NOW() - INTERVAL '5 days', NULL, 'Luxury Apartment', 'auction9.jpg', 'A luxurious apartment with modern amenities.', 10, 'Concluded'),
(10, 100, NOW() - INTERVAL '2 days', NOW() + INTERVAL '2 days', NULL, 'Sports Shoes', 'auction10.jpg', 'A pair of lightweight sports shoes in good condition.', 12, 'Active'),
(11, 300, NOW() + INTERVAL '5 days', NOW() + INTERVAL '12 days', NULL, 'Handmade Jewelry', 'auction11.jpg', 'Beautiful handmade jewelry with intricate design.', 10, 'Scheduled'),
(12, 4000000, NOW(), NOW() + INTERVAL '6 days', NULL, 'Luxury Yacht', 'auction12.jpg', 'A state-of-the-art luxury yacht in pristine condition.', 20, 'Active'),
(13, 25, NOW(), NOW() + INTERVAL '3 days', NULL, 'Notebook', 'auction13.jpg', 'A simple and practical notebook for daily use.', 5, 'Active'),
(14, 550, NOW() - INTERVAL '1 day', NOW() - INTERVAL '1 hour', NULL, 'Wireless Headphones', 'auction14.jpg', 'High-quality wireless headphones with noise cancellation.', 10, 'Concluded'),
(15, 10000, NOW() + INTERVAL '3 days', NOW() + INTERVAL '8 days', NULL, 'Smartphone', 'auction15.jpg', 'A top-brand smartphone with high-end features.', 15, 'Scheduled'),
(16, 150, NOW() - INTERVAL '6 days', NOW() - INTERVAL '2 days', NULL, 'Camping Tent', 'auction16.jpg', 'A durable and spacious camping tent for outdoor enthusiasts.', 8, 'Concluded'),
(17, 80, NOW(), NOW() + INTERVAL '5 days', NULL, 'Board Game', 'auction17.jpg', 'A fun and engaging board game for the whole family.', 5, 'Active'),
(18, 50000, NOW() - INTERVAL '10 days', NOW() - INTERVAL '1 day', NULL, 'Mountain Bike', 'auction18.jpg', 'A lightweight and durable mountain bike for adventure seekers.', 12, 'Concluded'),
(19, 3000, NOW() + INTERVAL '1 day', NOW() + INTERVAL '4 days', NULL, 'Laptop', 'auction19.jpg', 'A reliable laptop suitable for work and gaming.', 10, 'Scheduled'),
(20, 100, NOW() - INTERVAL '5 days', NOW(), NULL, 'Kitchen Utensils Set', 'auction20.jpg', 'A complete set of high-quality kitchen utensils.', 7, 'Active'),
(21, 30000, NOW() + INTERVAL '7 days', NOW() + INTERVAL '12 days', NULL, 'Designer Sofa', 'auction21.jpg', 'A stylish and comfortable designer sofa.', 20, 'Scheduled'),
(22, 60, NOW(), NOW() + INTERVAL '2 days', NULL, 'Fitness Tracker', 'auction22.jpg', 'A compact and efficient fitness tracker.', 8, 'Active'),
(23, 150000, NOW(), NOW() + INTERVAL '7 days', NULL, 'Electric Car', 'auction23.jpg', 'An eco-friendly electric car with excellent mileage.', 12, 'Active'),
(24, 350, NOW() - INTERVAL '8 days', NOW() - INTERVAL '2 days', NULL, 'Office Chair', 'auction24.jpg', 'An ergonomic office chair in great condition.', 15, 'Concluded'),
(25, 5, NOW() - INTERVAL '12 days', NOW() - INTERVAL '6 days', NULL, 'Coffee Mug', 'auction25.jpg', 'A colorful coffee mug with a unique design.', 5, 'Concluded'),
(26, 0, NOW() - INTERVAL '1 day', NOW(), NULL, 'Pen', 'auction26.jpg', 'A high-quality pen for everyday use.', 0, 'Active'),
(27, 7000, NOW(), NOW() + INTERVAL '4 days', NULL, 'Professional Camera', 'auction27.jpg', 'A DSLR camera with multiple lenses.', 15, 'Active'),
(28, 1, NOW() - INTERVAL '1 day', NOW() + INTERVAL '3 days', NULL, 'Vintage Book', 'auction28.jpg', 'A rare vintage book in good condition.', 10, 'Active'),
(29, 100000000, NOW() + INTERVAL '10 days', NOW() + INTERVAL '20 days', NULL, 'Private Jet', 'auction29.jpg', 'A luxurious private jet with modern interiors.', 25, 'Scheduled'),
(30, 300, NOW(), NOW() + INTERVAL '2 days', NULL, 'Electric Kettle', 'auction30.jpg', 'A fast-boiling electric kettle for everyday use.', 8, 'Active'),
(1, 10000, NOW() - INTERVAL '10 days', NOW() - INTERVAL '5 days', NULL, 'Abstract Painting', 'auction31.jpg', 'A mesmerizing abstract painting by a renowned artist.', 12, 'Concluded'),
(2, 50000, NOW() - INTERVAL '1 day', NOW() + INTERVAL '6 days', NULL, 'Vintage Statue', 'auction32.jpg', 'A marble statue from the 19th century.', 10, 'Active'),
(2, 1500, NOW(), NOW() + INTERVAL '3 days', NULL, 'Rare Coin Collection', 'auction33.jpg', 'An exclusive collection of rare coins from around the world.', 15, 'Active'),
(3, 3000, NOW() + INTERVAL '2 days', NOW() + INTERVAL '10 days', NULL, 'Antique Clock', 'auction34.jpg', 'A stunning antique clock in perfect working condition.', 8, 'Scheduled'),
(3, 70000, NOW() - INTERVAL '15 days', NOW() - INTERVAL '10 days', NULL, 'Renaissance Painting', 'auction35.jpg', 'An authentic painting from the Renaissance era.', 20, 'Concluded'),
(3, 1200, NOW() - INTERVAL '7 days', NOW() + INTERVAL '3 days', NULL, 'Vintage Porcelain Set', 'auction36.jpg', 'A complete porcelain set with delicate designs.', 5, 'Active'),
(4, 400, NOW(), NOW() + INTERVAL '2 days', NULL, 'Handcrafted Vase', 'auction37.jpg', 'A beautifully handcrafted vase with unique patterns.', 12, 'Active'),
(4, 20000, NOW() + INTERVAL '3 days', NOW() + INTERVAL '9 days', NULL, 'Vintage Guitar', 'auction38.jpg', 'A vintage acoustic guitar with excellent sound quality.', 15, 'Scheduled'),
(4, 90000, NOW() - INTERVAL '20 days', NOW() - INTERVAL '7 days', NULL, 'Rare Sculpture', 'auction39.jpg', 'A rare sculpture from the early 20th century.', 10, 'Concluded'),
(4, 350, NOW() - INTERVAL '2 days', NOW() + INTERVAL '3 days', NULL, 'Handwoven Rug', 'auction40.jpg', 'A colorful handwoven rug with intricate details.', 8, 'Active'),
(5, 5000, NOW() + INTERVAL '5 days', NOW() + INTERVAL '12 days', NULL, 'Vintage Chess Set', 'auction41.jpg', 'A vintage chess set with handcrafted pieces.', 10, 'Scheduled'),
(5, 250000, NOW(), NOW() + INTERVAL '7 days', NULL, 'Impressionist Painting', 'auction42.jpg', 'An original impressionist painting by a celebrated artist.', 20, 'Active'),
(5, 150, NOW(), NOW() + INTERVAL '4 days', NULL, 'Vintage Candle Holder', 'auction43.jpg', 'A vintage candle holder made of brass.', 5, 'Active'),
(5, 4500, NOW() - INTERVAL '2 days', NOW() - INTERVAL '1 day', NULL, 'Antique Mirror', 'auction44.jpg', 'An antique mirror with a gilded frame.', 15, 'Concluded'),
(5, 8500, NOW() + INTERVAL '3 days', NOW() + INTERVAL '8 days', NULL, 'Rare Stamp Collection', 'auction45.jpg', 'A rare stamp collection featuring global issues.', 10, 'Scheduled'),
(6, 300, NOW() - INTERVAL '5 days', NOW() - INTERVAL '2 days', NULL, 'Vintage Typewriter', 'auction46.jpg', 'A classic typewriter in working condition.', 12, 'Concluded'),
(6, 1000, NOW(), NOW() + INTERVAL '5 days', NULL, 'Antique Jewelry Box', 'auction47.jpg', 'An ornate jewelry box from the Victorian era.', 8, 'Active'),
(6, 1000000, NOW() - INTERVAL '30 days', NOW() - INTERVAL '10 days', NULL, 'Masterpiece Painting', 'auction48.jpg', 'A masterpiece painting by a legendary artist.', 25, 'Concluded'),
(6, 75, NOW() - INTERVAL '10 days', NOW() - INTERVAL '5 days', NULL, 'Vintage Teapot', 'auction49.jpg', 'A vintage teapot with floral patterns.', 5, 'Concluded'),
(6, 15000, NOW() - INTERVAL '1 day', NOW(), NULL, 'Antique Globe', 'auction50.jpg', 'A detailed antique globe with a wooden stand.', 10, 'Active'),
(6, 4000, NOW(), NOW() + INTERVAL '3 days', NULL, 'Rare Book Collection', 'auction51.jpg', 'A collection of rare books with historical significance.', 15, 'Active'),
(7, 120000, NOW() + INTERVAL '7 days', NOW() + INTERVAL '15 days', NULL, 'Baroque Painting', 'auction52.jpg', 'An original baroque painting by a famous artist.', 20, 'Scheduled'),
(7, 250, NOW(), NOW() + INTERVAL '2 days', NULL, 'Vintage Wall Clock', 'auction53.jpg', 'A charming vintage wall clock in good condition.', 5, 'Active'),
(7, 50000, NOW() - INTERVAL '12 days', NOW() - INTERVAL '3 days', NULL, 'Rare Artifact', 'auction54.jpg', 'A rare artifact from ancient times.', 18, 'Concluded'),
(7, 700, NOW(), NOW() + INTERVAL '4 days', NULL, 'Antique Lamp', 'auction55.jpg', 'An antique lamp with an intricate design.', 8, 'Active'),
(7, 3500, NOW() + INTERVAL '5 days', NOW() + INTERVAL '12 days', NULL, 'Vintage Wine Collection', 'auction56.jpg', 'A collection of vintage wines in pristine condition.', 15, 'Scheduled'),
(7, 100000, NOW(), NOW() + INTERVAL '7 days', NULL, 'Famous Artwork', 'auction57.jpg', 'A famous artwork from the modernist era.', 20, 'Active'),
(7, 300, NOW() - INTERVAL '8 days', NOW() - INTERVAL '2 days', NULL, 'Vintage Candelabra', 'auction58.jpg', 'A vintage candelabra made of silver.', 10, 'Concluded'),
(8, 2000, NOW(), NOW() + INTERVAL '3 days', NULL, 'Antique Cabinet', 'auction59.jpg', 'An antique cabinet with beautiful carvings.', 12, 'Active'),
(8, 120000, NOW() + INTERVAL '10 days', NOW() + INTERVAL '20 days', NULL, 'Rare Tapestry', 'auction60.jpg', 'A rare tapestry with historic significance.', 15, 'Scheduled');




-- Insert Bids
INSERT INTO Bid (user_id, auction_id, value) VALUES
-- Auction 1 (Luxury Car): Concluded
(5, 1, 2100000),
(3, 1, 2205000),
(8, 1, 2315250),

-- Auction 2 (Antique Vase): Active
(4, 2, 1600),
(7, 2, 1680),
(5, 2, 1764),
(8, 2, 1852),

-- Auction 3 (Designer Watch): Active
(2, 3, 525),
(4, 3, 551),
(5, 3, 578),
(6, 3, 606),
(7, 3, 636),
(8, 3, 668),

-- Auction 5 (Electric Scooter): Concluded
(6, 5, 26250),
(3, 5, 27562),
(8, 5, 28940),

-- Auction 6 (Motorcycle): Active
(2, 6, 367500),
(4, 6, 385875),
(5, 6, 405168),
(7, 6, 425426),

-- Auction 7 (Kitchen Blender): Active
(8, 7, 105),
(9, 7, 110),
(5, 7, 115),
(2, 7, 121),
(3, 7, 127),
(6, 7, 133),
(4, 7, 140),

-- Auction 9 (Luxury Apartment): Concluded
(3, 9, 840000),
(4, 9, 882000),
(5, 9, 926100),

-- Auction 10 (Sports Shoes): Active
(4, 10, 105),
(6, 10, 110),
(7, 10, 115),
(8, 10, 121),

-- Auction 12 (Luxury Yacht): Active
(5, 12, 4200000),
(6, 12, 4410000),
(8, 12, 4630500),

-- Auction 13 (Notebook): Active
(4, 13, 26),
(7, 13, 27),
(8, 13, 28),
(5, 13, 29),
(6, 13, 30),
(2, 13, 32),
(3, 13, 33),

-- Auction 14 (Wireless Headphones): Concluded
(2, 14, 578),
(5, 14, 607),
(7, 14, 637),
(6, 14, 669),

-- Auction 16 (Camping Tent): Concluded
(3, 16, 158),
(4, 16, 166),
(6, 16, 174),
(8, 16, 183),

-- Auction 17 (Board Game): Active
(5, 17, 84),
(7, 17, 88),
(8, 17, 92),
(6, 17, 97),
(4, 17, 102),

-- Auction 18 (Mountain Bike): Concluded
(2, 18, 52500),
(4, 18, 55125),
(5, 18, 57881),
(6, 18, 60775),
(7, 18, 63814),

-- Auction 20 (Kitchen Utensils Set): Active
(3, 20, 105),
(4, 20, 110),
(5, 20, 115),
(7, 20, 121),
(8, 20, 127),

-- Auction 23 (Electric Car): Active
(2, 23, 157500),
(4, 23, 165375),
(5, 23, 173644),
(6, 23, 182326),

-- Auction 24 (Office Chair): Concluded
(3, 24, 368),
(5, 24, 386),
(7, 24, 405),

-- Auction 26 (Pen): Active
(4, 26, 5),
(7, 26, 6),
(8, 26, 7),
(5, 26, 8),

-- Auction 27 (Professional Camera): Active
(3, 27, 7350),
(5, 27, 7717),
(7, 27, 8103),

-- Auction 28 (Vintage Book): Active
(4, 28, 2),
(5, 28, 3),
(7, 28, 4),
(8, 28, 5),

-- Auction 30 (Electric Kettle): Active
(2, 30, 315),
(4, 30, 331),
(5, 30, 347),
(7, 30, 364),

-- Auction 36 (Vintage Porcelain Set): Active
(3, 36, 1260),
(4, 36, 1323),
(7, 36, 1389),
(8, 36, 1458),

-- Auction 42 (Impressionist Painting): Active
(3, 42, 262500),
(4, 42, 275625),
(7, 42, 289406),

-- Auction 43 (Vintage Candle Holder): Active
(4, 43, 158),
(5, 43, 166),
(7, 43, 174),
(8, 43, 183),

-- Auction 44 (Antique Mirror): Concluded
(3, 44, 4725),
(5, 44, 4961),
(7, 44, 5209),

-- Auction 46 (Vintage Typewriter): Concluded
(5, 46, 315),
(7, 46, 331),
(8, 46, 347),

-- Auction 47 (Antique Jewelry Box): Active
(3, 47, 1050),
(5, 47, 1103),
(7, 47, 1158),

-- Auction 48 (Masterpiece Painting): Concluded
(3, 48, 1050000),
(5, 48, 1102500),
(7, 48, 1157625);




-- Insert Report Reasons
INSERT INTO ReportReason (name) VALUES 
('Inappropriate content'),
('Misleading description'),
('Fake product'),
('Spam'),
('Fraud');



-- Insert AuctionCategory
INSERT INTO AuctionCategory (auction_id, category_id) VALUES
(1, 13), -- Luxury Car -> Automotive
(2, 5), -- Antique Vase -> Home Decor
(3, 18), -- Designer Watch -> Jewelry
(3, 25), -- Designer Watch -> Fitness
(4, 5), -- Vintage Mug -> Home Decor
(5, 1), -- Electric Scooter -> Electronics
(5, 27), -- Electric Scooter -> Outdoor
(6, 13), -- Motorcycle -> Automotive
(7, 30), -- Kitchen Blender -> Kitchenware
(8, 1), -- Gaming Console -> Electronics
(8, 22), -- Gaming Console -> Games
(9, 6), -- Luxury Apartment -> Furniture
(9, 5), -- Luxury Apartment -> Home Decor
(10, 19), -- Sports Shoes -> Footwear
(10, 25), -- Sports Shoes -> Fitness
(11, 18), -- Handmade Jewelry -> Jewelry
(12, 27), -- Luxury Yacht -> Outdoor
(12, 13), -- Luxury Yacht -> Automotive
(13, 28), -- Notebook -> Stationery
(14, 1), -- Wireless Headphones -> Electronics
(14, 25), -- Wireless Headphones -> Fitness
(15, 1), -- Smartphone -> Electronics
(16, 27), -- Camping Tent -> Outdoor
(17, 22), -- Board Game -> Games
(18, 4), -- Mountain Bike -> Sports
(18, 27), -- Mountain Bike -> Outdoor
(19, 1), -- Laptop -> Electronics
(20, 30), -- Kitchen Utensils Set -> Kitchenware
(21, 6), -- Designer Sofa -> Furniture
(21, 5), -- Designer Sofa -> Home Decor
(22, 25), -- Fitness Tracker -> Fitness
(23, 13), -- Electric Car -> Automotive
(24, 6), -- Office Chair -> Furniture
(25, 5), -- Coffee Mug -> Home Decor
(26, 28), -- Pen -> Stationery
(27, 31), -- Professional Camera -> Photography
(28, 2), -- Vintage Book -> Books
(29, 13), -- Private Jet -> Automotive
(29, 27), -- Private Jet -> Outdoor
(30, 30), -- Electric Kettle -> Kitchenware
(31, 21), -- Abstract Painting -> Art Supplies
(32, 5), -- Vintage Statue -> Home Decor
(33, 2), -- Rare Coin Collection -> Books
(34, 5), -- Antique Clock -> Home Decor
(34, 21), -- Antique Clock -> Art Supplies
(35, 21), -- Renaissance Painting -> Art Supplies
(36, 5), -- Vintage Porcelain Set -> Home Decor
(37, 21), -- Handcrafted Vase -> Art Supplies
(38, 9), -- Vintage Guitar -> Music
(38, 5), -- Vintage Guitar -> Home Decor
(39, 21), -- Rare Sculpture -> Art Supplies
(40, 5), -- Handwoven Rug -> Home Decor
(41, 22), -- Vintage Chess Set -> Games
(42, 21), -- Impressionist Painting -> Art Supplies
(43, 5), -- Vintage Candle Holder -> Home Decor
(44, 5), -- Antique Mirror -> Home Decor
(45, 2), -- Rare Stamp Collection -> Books
(46, 30), -- Vintage Typewriter -> Office Supplies
(46, 2), -- Vintage Typewriter -> Books
(47, 18), -- Antique Jewelry Box -> Jewelry
(48, 21), -- Masterpiece Painting -> Art Supplies
(49, 5), -- Vintage Teapot -> Home Decor
(50, 5), -- Antique Globe -> Home Decor
(51, 2), -- Rare Book Collection -> Books
(52, 21), -- Baroque Painting -> Art Supplies
(53, 5), -- Vintage Wall Clock -> Home Decor
(54, 5), -- Rare Artifact -> Home Decor
(55, 5), -- Antique Lamp -> Home Decor
(56, 5), -- Vintage Wine Collection -> Home Decor
(57, 21), -- Famous Artwork -> Art Supplies
(58, 5), -- Vintage Candelabra -> Home Decor
(59, 5), -- Antique Cabinet -> Home Decor
(60, 21), -- Rare Tapestry -> Art Supplies
(60, 5); -- Rare Tapestry -> Home Decor

-- Populate the Rating table
INSERT INTO Rating (rating_value, comment, rater_id, rated_auction_id, date) VALUES
-- Auction 1 (Luxury Car)
(5, 'The car was in excellent condition, as described. Very satisfied!', 8, 1, NOW() - INTERVAL '3 days'),

-- Auction 5 (Electric Scooter)
(4, 'Good condition, but minor scratches were not mentioned.', 8, 5, NOW() - INTERVAL '2 days'),

-- Auction 9 (Luxury Apartment)
(5, 'The apartment is stunning! Everything was perfect.', 5, 9, NOW() - INTERVAL '4 days'),

-- Auction 14 (Wireless Headphones)
(3, 'The sound quality is good, but the headphones are slightly uncomfortable.', 7, 14, NOW() - INTERVAL '1 day'),

-- Auction 16 (Camping Tent)
(4, 'Great tent for outdoor use. Lightweight and durable.', 8, 16, NOW() - INTERVAL '2 days'),

-- Auction 18 (Mountain Bike)
(5, 'The bike is amazing! Perfect for mountain trails.', 7, 18, NOW() - INTERVAL '5 days'),

-- Auction 24 (Office Chair)
(4, 'Comfortable and as described, but delivery was delayed.', 7, 24, NOW() - INTERVAL '3 days'),

-- Auction 44 (Antique Mirror)
(5, 'The antique mirror is beautiful and in excellent condition.', 7, 44, NOW() - INTERVAL '6 days'),

-- Auction 46 (Vintage Typewriter)
(3, 'The typewriter works, but it needed some repairs.', 8, 46, NOW() - INTERVAL '4 days'),

-- Auction 48 (Masterpiece Painting)
(5, 'A stunning masterpiece! Very happy with the purchase.', 7, 48, NOW() - INTERVAL '10 days'),

-- Auction 49 (Vintage Teapot)
(4, 'The teapot is lovely and exactly as pictured.', 7, 49, NOW() - INTERVAL '5 days');



-- Populate the ItemRarity table
INSERT INTO ItemRarity (name, color) VALUES
    ('Common', '#C0C0C0'),
    ('Rare', '#FFD700'),
    ('Epic', '#8A2BE2'),
    ('Legendary', '#FF4500');



INSERT INTO Admin (username, password,remember_token) VALUES
    ('admin_account', '$2y$10$mXReWl0UYdLsdaRlM0N1Bek.r7qBwl.xNN6Fgb53pgWz3/sdBpPou', '' );

DELETE FROM AuctionNotification;
DELETE FROM MemberNotification;
DELETE FROM RatingNotification;
DELETE FROM TransactionNotification;
DELETE FROM Notification;