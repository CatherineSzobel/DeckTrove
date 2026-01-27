# DeckTrove

DeckTrove is a Laravel web application for managing and sharing trading card game (TCG) decks. It supports series like **Yu-Gi-Oh!**, **Magic: The Gathering**, and more in the works, allowing users to browse card databases, build decks, and explore public collections.

## Features

* **Card Database:** Browse large card collections with detailed info, images, and pricing.
* **Deck Builder:** Drag-and-drop interface for main, extra, and side decks.
* **Public Decks:** Explore and share community-created decks.
* **User Accounts:** Register, log in, and manage profiles and decks.
* **Responsive Design:** Tailwind CSS for mobile-friendly UI.
* **API Integration:** Fetches data from APIs like Scryfall for Magic cards.

## Tech Stack

* **Backend:** Laravel (PHP), MySQL
* **Frontend:** HTML, CSS, JavaScript, Tailwind CSS
* **Build Tool:** Vite
* **Other Tools:** Composer, NPM

## Installation

1. **Clone the repository:**

```bash
git clone https://github.com/CatherineSzobel/DeckTrove
cd <repository_folder>
```

2. **Install PHP dependencies:**

```bash
composer install
```

3. **Install Node.js dependencies:**

```bash
npm install
```

4. **Set up environment variables:**

```bash
cp .env.example .env
```

Configure your database and settings in `.env`.
5. **Generate an app key:**

```bash
php artisan key:generate
```

6. **Run migrations:**

```bash
php artisan migrate
```

7. **Build assets:**

```bash
npm run build
```

8. **Start development server:**

```bash
php artisan serve
```

9. **For asset watching:**

```bash
npm run dev
```

## Usage

* Explore TCGs on the homepage.
* Register to access deck building and collections.
* Drag cards into zones to build decks.
* Browse public decks and card databases.

## Project Structure

* **app:** Models, services, controllers.
* **views:** Blade templates.
* **js:** JavaScript files.
* **css:** Stylesheets.
* **routes:** Web routes.
* **database:** Migrations and seeders.
* **public:** Static assets.

## License

MIT License. See LICENSE file for details.

