# Lifestyle Store

An online store built with PHP and MySQL for my Database Systems course. You can browse products,
register, log in, add things to a cart, place an order, and look back at your order history.

Stack: HTML, CSS, Bootstrap 3, jQuery, PHP (procedural mysqli), MySQL/MariaDB.

## Screenshots

**Home page**

![Home page](screenshots/01-home.jpg)

**Products**

![Products page](screenshots/02-products.jpg)

![Shirts section](screenshots/03-products-shirts.jpg)

**Login and sign up**

![Login page](screenshots/04-login.jpg)

![Sign up page](screenshots/05-signup.jpg)

**Cart and checkout**

![Cart page](screenshots/06-cart.jpg)

![Order success page](screenshots/08-order-success.jpg)

**Order history**

![Order history page](screenshots/07-order-history.jpg)

**Settings**

![Settings page](screenshots/09-settings.jpg)

**About and contact**

![About us page](screenshots/10-about-us.jpg)

![Contact page](screenshots/11-contact.jpg)

## What it does

- Sign up with validation on the email, password and phone number. Duplicate emails get rejected.
- Session based login and logout.
- 12 products across cameras, watches and shirts.
- Search products by name, with category, price range and sort filters.
- Add and remove cart items, with a running total. The same item can't be added twice.
- Confirm an order, which flips those cart rows to Confirmed.
- Order history with timestamps and a total.
- Change your password after verifying the old one.
- Cart, settings, order history and success pages bounce you to the home page if you aren't logged in.
- The navbar changes depending on whether you're logged in.

## Database

The database is called `store` and has three tables.

**items** - the products. `id`, `name`, `category`, `price`, `image`.

**users** - registered customers. `id`, `name`, `email`, `password` (MD5), `contact`, `city`, `address`.

**user_item** - links users to items. `id`, `user_id`, `item_id`, `status`, `date_time`.

`user_item` is the interesting one. It's a many-to-many join between users and items, and it stores
the cart *and* the orders in the same place. There's no separate orders table. A row is created with
status `Added to cart`, and checkout promotes it to `Confirmed`, so `date_time` ends up being the
order timestamp too.

```
users ──1───< user_item >───1── items
                  │
                  ├── 'Added to cart'  → cart
                  └── 'Confirmed'      → order history
```

## Setup

You need Apache, PHP and MySQL. XAMPP, MAMP or WAMP all work.

1. Clone the project into your web root, e.g. `C:/xampp/htdocs/lifestyle-store` on Windows or
   `/Applications/XAMPP/htdocs/lifestyle-store` on Mac.

2. Start Apache and MySQL, then create the database and import the dump:

   ```bash
   mysql -u root -p -e "CREATE DATABASE store;"
   mysql -u root -p store < database/store.sql
   mysql -u root -p store < database/migrations/001_add_product_search.sql
   ```

   The second file adds the `category` and `image` columns that the search page needs. You can also
   do all of this through phpMyAdmin if you prefer clicking.

3. Open `includes/common.php` and fix the connection line to match your setup:

   ```php
   $con = mysqli_connect("localhost:3307", "root", "", "store");
   ```

   XAMPP and WAMP normally use port 3306 with user `root` and no password. MAMP uses 8889 with
   `root`/`root`. Change 3307 to whatever yours is actually running on.

4. Go to `http://localhost/lifestyle-store/index.php`.

The seeded users all have MD5 passwords you don't know, so either sign up for a new account or
overwrite one:

```sql
UPDATE users SET password = MD5('YourPassword1') WHERE email = 'vish@gmail.com';
```

Sign up needs a password of at least 8 characters with an uppercase letter, a lowercase letter and a
digit, plus a 10 digit phone number starting with 7, 8 or 9.

## Known issues

Things I'd fix given more time:

- Most queries still build SQL by string concatenation with `mysqli_real_escape_string`. Only
  `search.php` uses prepared statements so far. The rest should follow.
- Passwords are MD5, which is fast and unsalted and therefore a bad choice. `password_hash()` and
  `password_verify()` are the right answer.
- The database credentials are hardcoded in `includes/common.php` instead of sitting in a config
  file that isn't committed.
- Error messages travel through the URL as raw HTML and get echoed back unescaped, which is an XSS
  hole.
- The cart tracks which items you added, not how many, so you can only order one of each at a time.
- The contact form has no `action`, so nothing happens when you submit it.
- `orderhistory.php` loops over two separate result sets side by side, so with enough orders the
  timestamp column drifts out of line with the item rows.
- `check_if_added_to_cart()` opens a new database connection every time it's called, so a page of 12
  products opens 12 connections.

## Credits

Based on [this e-commerce project](https://github.com/VishwaduttMS/eCommerce-website-using-HTML-CSS-PHP-and-MySQL-database)
by Vishwadutt M S, which is where the original pages, the MySQL schema and the cart and order logic
came from. There's no licence file on that repo, so I'm using it for coursework rather than
republishing it, and I've left its commit history in place here.

Bootstrap 3, Glyphicons and jQuery are all MIT licensed.

What I added on top: reorganised the flat root into `css/`, `js/`, `img/`, `fonts/`, `includes/`,
`database/` and `screenshots/`; wrote this README and took the screenshots; rebranded the site and
rewrote the About and Contact pages; built the product search (`search.php`, the migration and the
card renderer); fixed a stray `?>` that was printing into the navbar and a `source.GIF` reference
that broke on case sensitive filesystems; added a `.gitignore` and dropped the committed `Thumbs.db`.

To see exactly what changed from the original:

```bash
git diff 9652f95 HEAD --stat
```
