# TreasureBids

Real-time luxury items auction platform in Laravel

Key features include real-time bidding and notification system, concurrent database access protection, exact-mach & full text searching with filters, data anonymization on user deletion.

**ONLY FOR DEMONSTRATIVE PURPOSES**

Check [docs](docs/) for video demo and other useful documents.

## Libraries Used

Below is a list of libraries and frameworks used in the product, including their descriptions and examples of where they are applied.

* **Bootstrap**\
  [Bootstrap](https://getbootstrap.com/) was used to enhance the application's visual appeal, making it clean, attractive, and consistent with minimal effort. It also ensured responsiveness, making it suitable for smaller screens.
* **Laravel**\
  [Laravel](https://laravel.com/) simplifies tasks like authentication, routing, and session management. It was utilized to create routes and controllers for handling requests.
* **Stripe**\
  [Stripe](https://stripe.com/) is a tool for simulating payments. It was used for transactions, such as when a user wants to add credit to their account.
* **Mailtrap**\
  [Mailtrap](https://mailtrap.io/) was used for testing email functionality, ensuring emails are formatted correctly and delivered in a simulated environment. This allowed users to recover their passwords effectively.


## Conceptual Data Model

This artifact presents the Conceptual Data Model for the TreasureBids web application, designed to represent the core entities, their attributes, and the relationships that exist between them. The goal of this artifact is to provide a high-level overview of the system’s data requirements, ensuring a clear understanding of the business domain.


### 1. Class diagram

![Class_Diagram](https://github.com/user-attachments/assets/ef943cc3-1a50-4987-a610-b98e755e2d54)



### 2. Additional Business Rules

| **Identifier** | **Name**                        | **Description**                                                                                           |
|----------------|--------------------------------|-----------------------------------------------------------------------------------------------------------|
| BR01           | Minimum Credit Requirement for Bidding | A `Member` must have sufficient credits in their account to place a `Bid`.                |
| BR02           | No Self-Bidding                 | A `Member` cannot place a `Bid` on an `Auction` they created.                                             |
| BR03           | Minimum Increment in Bidding    | Each new `Bid` must be greater than the previous bid on the same `Auction` by a minimum increment.                              |
| BR04           | Rating Only for Auction Winner | A `Member` can only rate (`Rating`) an `Auction` if they won it.               |
| BR05           | No Self-Rating                  | A `Member` cannot rate themselves as a seller.                                                            |
| BR06           | Following Auctions              | A `Member` can follow (`FollowedAuction`) any `Auction` of interest except those they created.            |

---

## Relational Schema, Validation, and Schema Refinement

This artifact presents the Relational Schema for the TreasureBids web application, mapped from the Conceptual Data Model. It includes relation schemas, attributes, domains, primary keys, foreign keys, and integrity constraints such as NOT NULL, UNIQUE, and CHECK, ensuring data consistency and supporting efficient data operations.

### 1. Relational Schema

| Relation Reference | Relation Compact Notation |
|--------------------|---------------------------|
| R01 | Auction(<ins>id</ins>, starting_date **NN**, end_date **NN CK** end_date > starting_date, starting_price **NN CK** starting_price ≥ 0, buyout_price **NN CK** buyout_price ≥ starting_price, title **NN**, discount, description **NN**, picture **NN**, status **Status**, id_category → Category **NN**, id_item_rarity → Item Rarity **NN**, owner_id → Member) |
| R02 | Member(<ins>id</ins>, username **NN UK**, email **NN UK**, first_name, last_name, password **NN**, profile_pic, credit **NN DF** 0 **CK** credit >= 0, blocked **NN DF** False) |
| R03 | Bid(<ins>id</ins>, value **NN**, date **NN DF now**, auction_id → Auction **NN**, member_id → Member **NN**) |
| R04 | Transaction(<ins>id</ins>, price **NN CK** price > 0, date **NN DF now**, auction_id → Auction **NN**, buyer_id → Member) |
| R05 | Notification(<ins>id</ins>, urgency **Urgency**, text **NN**, url, date **NN DF now**, notified_id -> Member **NN**) |
| R06 | MemberNotification(<ins>notification_id</ins> → Notification **NN**, member_id → Member) |
| R07 | AuctionNotification(<ins>notification_id</ins> → Notification **NN**, auction_id → Auction **NN**) |
| R08 | RatingNotification(<ins>notification_id</ins> → Notification **NN**, rating_id → Rating **NN**) |
| R09 | TransactionNotification(<ins>notification_id</ins> → Notification **NN**, transaction_id → Transaction **NN**) |
| R10 | Rating(<ins>id</ins>, rating_value **NN CK** rating_value >= 1 AND rating_value <= 5, comment, date **NN DF now**, auction_id → Auction **NN**, rater_id → Member) |
| R11 | Category(<ins>id</ins>, name **UK NN**, color **UK NN**) |
| R12 | ItemRarity(<ins>id</ins>, name **UK NN**, color **UK NN**) |
| R13 | ReportReason(<ins>id</ins>, name **UK NN**) |
| R14 | Report(<ins>id</ins>, comment, date **NN DF now**, reason_id → ReportReason **NN**, auction_id → Auction **NN**, reporter_id → Member) |
| R15 | Admin(<ins>id</ins>, username **UK NN**, password **NN**) |
| R16 | FollowedAuction(<ins>id</ins>, member_id → Member **NN**, auction_id → Auction **NN**, date **NN DF now**) |

Legend:
- UK = UNIQUE KEY
- NN = NOT NULL
- DF = DEFAULT
- CK = CHECK


### 2. Domains

| Domain Name  | Domain Specification                        |
|--------------|--------------------------------------------|
| Urgency      | ENUM ('Low', 'Medium', 'High') NOT NULL |
| Status       | ENUM ('Scheduled', 'Active', 'Concluded', 'Cancelled') NOT NULL |
| now          | DATE DEFAULT CURRENT_TIMESTAMP             |

### 3. Schema Validation

 To validate the Relational Schema obtained from the Conceptual Model, all functional dependencies are identified, and the normalization of all relation schemas is accomplished. If any schema is not in Boyce–Codd Normal Form (BCNF), it is refined using normalization.

#### Table R01 (Auction)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0101                    | id → {starting_date, end_date, starting_price, buyout_price, title, discount, description, picture, status, category_name, item_rarity_name, owner_id} |
| **Normal Form**           | BCNF |

#### Table R02 (Member)

| **Keys**                  | {id}, {username}, {email} |
|---------------------------|---------------------------|
| FD0201                    | id → {username, email, first_name, last_name, password, profile_pic, credit, blocked} |
| FD0202                    | username → {id, email, first_name, last_name, password, profile_pic, credit, blocked} |
| FD0203                    | email → {id, username, first_name, last_name, password, profile_pic, credit, blocked} |
| **Normal Form**           | BCNF |

#### Table R03 (Bid)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0301                    | id → {value, date, auction_id, member_id} |
| **Normal Form**           | BCNF |

#### Table R04 (Transaction)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0401                    | id → {price, date, auction_id, buyer_id} |
| **Normal Form**           | BCNF |

#### Table R05 (Notification)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0501                    | id → {urgency, text, url, date} |
| **Normal Form**           | BCNF |

#### Table R06 (MemberNotification)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0601                    | id → {notification_id, member_id} |
| **Normal Form**           | BCNF |

#### Table R07 (AuctionNotification)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0701                    | id → {notification_id, auction_id} |
| **Normal Form**           | BCNF |

#### Table R08 (RatingNotification)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0801                    | id → {notification_id, rating_id} |
| **Normal Form**           | BCNF |

#### Table R09 (TransactionNotification)

| **Keys**                  | {id} |
|---------------------------|------|
| FD0901                    | id → {notification_id, transaction_id} |
| **Normal Form**           | BCNF |

#### Table R10 (Rating)

| **Keys**                  | {id} |
|---------------------------|------|
| FD1001                    | id → {rating_value, comment, date, auction_id, reviewer_id} |
| **Normal Form**           | BCNF |

#### Table R11 (Category)

| **Keys**                  | {name} |
|---------------------------|--------|
| FD1101                    | name → {color} |
| **Normal Form**           | BCNF |

#### Table R12 (ItemRarity)

| **Keys**                  | {name} |
|---------------------------|--------|
| FD1201                    | name → {color} |
| **Normal Form**           | BCNF |

#### Table R13 (ReportReason)

| **Keys**                  | {name} |
|---------------------------|--------|
| FD1301                    | name → {} |
| **Normal Form**           | BCNF |

#### Table R14 (Report)

| **Keys**                  | {id} |
|---------------------------|------|
| FD1401                    | id → {comment, date, reason_name, auction_id, reporter_id} |
| **Normal Form**           | BCNF |

#### Table R15 (Admin)

| **Keys**                  | {username} |
|---------------------------|------------|
| FD1501                    | username → {password, member_id} |
| **Normal Form**           | BCNF |

#### Table R16 (FollowedAuction)

| **Keys**                  | {id} |
|---------------------------|------|
| FD1601                    | id → {member_id, auction_id, date} |
| **Normal Form**           | BCNF |


Because all relations are in Boyce–Codd Normal Form (BCNF), the relational schema does not need further normalization. Each relation has been validated to meet BCNF criteria by ensuring that for each functional dependency, the left side is a superkey.

The generalization for `Notification` was mapped using a main table (`Notification`) to store common attributes, and several specialized tables (`MemberNotification`, `AuctionNotification`, `RatingNotification`, `TransactionNotification`) so that each notification is associated with an individual instance of the correct class.

## Web Resources Specification

The goal of this artifact is to identify and describe the modules that will be part of the TreasureBids web application. Each module specifies the web resources necessary for its implementation and the associated system features.

---

### 1. Overview

The following table outlines the modules that will be part of the TreasureBids application, along with a brief description of their functionalities.

| Module Reference | Module Name | Description |
|------------------|-------------|-------------|
| M01 | Authentication and Profile | Web resources for user authentication and profile management. Includes login/logout, registration, and profile updates. |
| M02 | Auction Management | Web resources for creating, editing, viewing, and deleting auctions. Supports browsing and searching auctions. |
| M03 | Bidding and Followed Auctions | Web resources for placing bids and managing followed auctions. Ensures compliance with bidding rules. |
| M04 | User Administration | Web resources for admin-level user management. Includes blocking/unblocking users and viewing user activity logs. |
| M05 | Transaction and Payment | Web resources for viewing transaction history, initiating buyouts, and processing payments. |
| M06 | Notifications | Web resources for managing and viewing notifications, including auction updates and system alerts. |
| M07 | Rating and Reporting | Web resources for submitting ratings and reporting auctions or users. Ensures content moderation. |
| M08 | Static Pages | Web resources for viewing static pages, such as About, Contact, FAQs, and Terms of Service. |

### 2. Permissions

This section defines the permissions used in the modules to establish the conditions of access to resources.

| Permission Reference | Role | Description |
|----------------------|------|-------------|
| GUEST | Guest | Public permission / Unauthenticated users. Can browse public pages and view public auction listings. |
| MEMBER | Member | Authenticated users. Can place bids, follow auctions, and manage their profiles. |
| SELF | Self | Members managing their own resources (e.g., editing their profile or viewing personal information). |
| AUCTION_OWNER | Auction Owner | Members who created an auction are granted this permission for that auction. The Auction Owner can edit or close their own auction. |
| BIDDER | Bidder | Member who placed a bid on an auction are granted this permission for that auction. A Bidder can view the auction's detailed bid history. |
| ADMIN | Administrator | System administrators. Can manage users, auctions, reports, and system settings. |

### 3. OpenAPI Specification

OpenAPI specification in YAML format to describe the vertical prototype's web resources.

[TreasureBids OpenAPI Specification](https://github.com/joaoparada10/TreasureBids/blob/main/docs/a7_openapi.yaml)

## Installation

Docker command to start the image available at the GitLab Container Registry using the production database: `docker run -d --name lbaw24114 -p 8001:80 gitlab.up.pt:5050/lbaw/lbaw2425/lbaw24114`  

## Usage

### Administration Credentials

Administration URL: &lt;http://localhost:8001/admin/dashboard&gt;  

| Username | Password |
| -------- | -------- |
| admin_account    | 12345678 |

### User Credentials

| Type          | Username  | Password |
| ------------- | --------- | -------- |
| member | basic    | 12345678 |
| member   | user    | password |

_basic_ member has no credit and no auctions created/bidded, while _user_ has a lot of credit and many auctions created/bidded.
