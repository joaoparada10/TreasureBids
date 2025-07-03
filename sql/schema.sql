-- Set the search path to your schema
SET search_path TO lbaw24114;

-- Drop existing tables if they exist
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
    blocked BOOL NOT NULL DEFAULT False
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
    --PRIMARY KEY (buyer_id, seller_id, date)  -- Composite primary key (adjust if needed)
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
    password TEXT NOT NULL
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
    date TIMESTAMP NOT NULL DEFAULT NOW()
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
BEGIN
    -- Loop over all auctions that have ended but are not yet marked as 'Concluded'
    FOR auction_record IN 
        SELECT id, owner_id 
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
            'Your auction has concluded.',
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
                'Congratulations! You won the auction!',
                '/auctions/' || auction_record.id,  -- URL to view the auction
                'auction',
                auction_record.id
            );

            -- Insert a transaction record for the auction sale
            INSERT INTO Transaction (buyer_id, auction_id, price, date)
            VALUES (highest_bid.user_id, auction_record.id, highest_bid.value, NOW());
        END IF;
    END LOOP;
END;
$$ LANGUAGE plpgsql;



----------------------------------------TRIGGERS----------------------------------------



--CREATES NOTIFICATION FOR OWNER AND HIGHEST BIDDER AFTER INSERTING BID--
CREATE OR REPLACE FUNCTION notify_owner_and_highest_bidder() RETURNS TRIGGER AS $$
DECLARE
    auction_owner_id INTEGER;
    previous_highest_bidder_id INTEGER;
    auction_url TEXT := '/auction/' || NEW.auction_id;  -- Construct URL for the auction
BEGIN
    -- Fetch the auction owner's ID
    SELECT owner_id INTO auction_owner_id
    FROM Auction
    WHERE id = NEW.auction_id;

    -- Insert notification for the auction owner using insert_notification function
    PERFORM insert_notification(
        auction_owner_id,
        'Medium',
        'A new bid has been placed on your auction.',
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
            'You have been outbid on an auction.',
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
    auction_url TEXT := '/auction/' || NEW.auction_id;  -- Construct URL for the auction
BEGIN
    -- Fetch auction details and owner
    SELECT owner_id, end_date INTO auction_owner_id, new_end_date
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
            'Your auction''s duration has been extended.',
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
BEGIN

    -- Get the auction owner ID based on the auction ID in the transaction
    SELECT owner_id INTO seller_id FROM Auction WHERE id = NEW.auction_id;

    --notify the buyer
    PERFORM insert_notification(
    NEW.buyer_id,
    'Medium'::notification_urgency,
    'You successfully bought the item.',
    '/transactions/' || NEW.id,
    'transaction',  
    NEW.id  
    );

    --notify the seller
    PERFORM insert_notification(
    seller_id,
    'Medium'::notification_urgency,
    'You successfully sold the item.',
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
    -- Set the transaction isolation level for consistent credit balance
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;

    BEGIN
        -- Update the member's credit balance
        UPDATE Member
        SET credit = credit + p_amount
        WHERE id = p_member_id;

        INSERT INTO Transaction (buyer_id, auction_id, price, date)
        VALUES (p_member_id, NULL, p_amount, NOW());

        -- Notify the member of credit addition
        PERFORM insert_notification(
            p_member_id,
            'Medium',
            'Credit has been added to your account.',
            '/profile',
            'member',
            p_member_id
        );
        
    EXCEPTION WHEN OTHERS THEN
        RAISE NOTICE 'An error occurred while adding credit. No changes were applied.';
        RETURN;
    END;
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