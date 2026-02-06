# DeckTrove

DeckTrove is a Laravel full-stack web application for managing and sharing trading card game (TCG) decks. 
It integrates multiple third‑party card APIs (such as Yu‑Gi‑Oh! and Magic: The Gathering) and normalizes their heterogeneous data structures into a unified domain model, allowing the rest of the application to remain clean, extensible, and API‑agnostic.

This project was built to explore real‑world backend challenges such as external API integration, data normalization, and maintainable application architecture.
## Features

* **Card Database:** Browse large card collections with detailed metadata, images, and pricing.
* **Deck Builder:** Build decks using structured zones (main, extra, side decks).
* **Public Decks:** Share and explore decks created by other users.
* **User Accounts:** Authentication, profile management, and deck ownership.
* **Responsive Design:** Mobile‑friendly interface built with Tailwind CSS.
* **API Integration:** Fetches data from APIs like Scryfall for Magic cards.
* **Multi‑TCG Support** Designed to support multiple card games with minimal coupling.

# Architecture & Design Decisions
## API Normalization (Core Design Decision)

Different TCG APIs expose card data using inconsistent field names and schemas (e.g. atk vs power, different rarity formats, image structures, etc.).
To solve this, DeckTrove uses a config‑driven mapping layer that normalizes external API responses into a unified internal card representation. This ensures:

* The rest of the application does not depend on API‑specific field names
* Adding a new TCG requires only configuration changes, not application rewrites
* Controllers and views work with a consistent domain model

This approach avoids conditional logic scattered throughout the codebase and keeps integrations maintainable.

## Service Layer

Business logic is extracted into service classes to avoid fat controllers. Services coordinate:

* External API calls
* Data normalization
* Persistence logic

This separation improves readability, testability, and long‑term maintainability.

# Tech Stack

* **Backend:** Laravel (PHP), MySQL
* **Frontend:** HTML, CSS, JavaScript, Tailwind CSS
* **Build Tool:** Vite
* **Other Tools:** Composer, NPM

# Installation

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

* Browse supported TCGs on the homepage
* Register or log in to build and manage decks
* Search card databases and add cards to decks
* Explore publicly shared decks
  
# Project Status

Actively developed — core features implemented, with ongoing improvements and refinements planned.

# Project Structure

* **app:** Models, services, controllers.
* **views:** Blade templates.
* **js:** JavaScript files.
* **css:** Stylesheets.
* **routes:** Web routes.
* **database:** Migrations and seeders.
* **public:** Static assets.

# License

MIT License. See LICENSE file for details.

