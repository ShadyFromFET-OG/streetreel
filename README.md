# STREET REEL

Street-punk action movie store. HTML + Tailwind CDN + vanilla JS on the front, PHP (PDO) + MySQL/MariaDB on the back.

## Run it in XAMPP

1. Copy the `streetreel` folder into `C:\xampp\htdocs\` (macOS/Linux: `/Applications/XAMPP/htdocs/` or `/opt/lampp/htdocs/`).
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Go to **http://localhost/streetreel/install.php**, enter an admin name, email and password, and hit Install.
   It creates the `streetreel` database, tables, 15 sample movies and your admin account.
4. **Delete `install.php`.**
5. Open **http://localhost/streetreel/index.html**.

Always use the `http://localhost/...` address. Double-clicking `index.html` (a `file://` address) can't reach the PHP API.

### Prefer phpMyAdmin?
Open http://localhost/phpmyadmin, create a database called `streetreel` (utf8mb4_general_ci), select it, Import `database/schema.sql`.
Then register on the site and promote yourself in phpMyAdmin: `UPDATE users SET role='admin' WHERE email='you@example.com';`

### Config you might need
Everything lives in `includes/config.php`:

| Setting | Default | Change when |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | almost never |
| `DB_USER` / `DB_PASS` | `root` / empty | you set a MySQL root password, or XAMPP uses a different port (use `127.0.0.1:3307` style via `DB_HOST` plus port in the DSN) |
| `DB_NAME` | `streetreel` | you want another database name |
| `BRAND_NAME` | `STREET REEL` | rebranding. The last word gets the red slab |
| `BRAND_TAGLINE`, `CURRENCY` | see file | rebranding |

PHP 8.0 or newer (every current XAMPP). `pdo_mysql` is enabled by default.

## Structure

```
index.html            only HTML file. Talks to the API with fetch()
movie.php             movie detail + add to cart
login.php register.php logout.php
cart.php checkout.php account.php   (account = library + order history)
admin.php             movies CRUD, genres, orders, stats (admin role only)
install.php           one-time setup, delete afterwards
api/session.php       user, cart count, CSRF token, brand settings
api/movies.php        search / genre filter / sort / featured
api/cart.php          add / remove (POST JSON + CSRF header)
api/poster.php        generates a poster SVG per movie (no image files needed)
includes/             config, PDO connection, auth + CSRF helpers, shared layout
database/schema.sql   tables + seed data
```

## Notes

- Posters are generated SVGs so the site works with zero image files. In Admin, paste a Poster URL on any movie to use a real image instead.
- Poster text uses Impact (Windows/macOS ship it). SVGs loaded through `<img>` can't use Google Fonts.
- Checkout is a demo. It records orders and unlocks films in the library but takes no payment and collects no card data.
- Security in place: PDO prepared statements everywhere, `password_hash`, CSRF tokens on every POST, session ID regeneration on login, escaped output, redirect allow-list for `?next=`.
- The CSS/fonts/Tailwind config block is repeated in `index.html` and `includes/layout.php`, since the brief rules out a shared CSS file and `index.html` can't include PHP. Change both if you tweak the palette.
- Tailwind, Google Fonts load from CDNs, so the pages need internet access.
