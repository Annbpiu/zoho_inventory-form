# Laravel Application with Vue.js Frontend

---

## 📜 Table of Contents

- [Requirements](#requirements)
- [Project Setup](#project-setup)
- [Development Environment](#development-environment)
- [Zoho Integration Setup](#zoho-integration-setup)
- [Running the Application](#running-the-application)
- [Building for Production](#building-for-production)
- [Project Structure](#project-structure)

---

## Requirements

Before starting, ensure you have the following tools installed:

- **PHP** >= 8.2
- **Composer**
- **Node.js** >= 18.x and **npm**
- **SQLite** (used as the database)
- **Vite** (for frontend asset compilation)

---

## Project Setup

1. **Clone the repository:**

   ```bash
   git clone <REPO_URL>
   cd <PROJECT_NAME>
   ```

2. **Install PHP dependencies using Composer:**

   ```bash
   composer install
   ```

3. **Install Node.js dependencies via npm:**

   ```bash
   npm install
   ```

4. **Copy the example `.env.example` file into `.env` and configure the environment variables:**

   ```bash
   cp .env.example .env
   ```

   For SQLite, ensure the following configuration in `.env`:

   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=./database/database.sqlite
   ```

   Then, set up the SQLite database file:

   ```bash
   touch database/database.sqlite
   ```

5. **Run database migrations:**

   ```bash
   php artisan migrate
   ```

---

## Development Environment

This application supports Laravel Sail (Docker-based container environment). Setting up the development environment:

1. **Install Sail (if not already installed):**

   ```bash
   composer require laravel/sail --dev
   ```

2. **Spin up the containers:**

   ```bash
   ./vendor/bin/sail up
   ```

3. **Use Sail commands (e.g., npm or Composer):**

   ```bash
   ./vendor/bin/sail npm install
   ```

You can also run the application without using Sail if Docker is not available.

---

## Zoho Integration Setup

This application integrates with the **Zoho API** to synchronize data and perform operations. Follow these steps to enable Zoho Integration:

1. **Obtain Zoho API client credentials:**
    - Visit the [Zoho Developer Console](https://accounts.zoho.com/developerconsole).
    - Create a new client application and retrieve the **Client ID** and **Client Secret**.

2. **Update Zoho credentials in `.env`:**

   Add the following keys to the `.env` file and replace `<PLACEHOLDER>` values with your Zoho information:

   ```env
   ZOHO_CLIENT_ID=<your_zoho_client_id>
   ZOHO_CLIENT_SECRET=<your_zoho_client_secret>
   ZOHO_REDIRECT_URI=<your_redirect_uri>
   ZOHO_API_BASE_URL=https://www.zohoapis.com
   ```

    - `ZOHO_REDIRECT_URI` should match the URI set in your Zoho Developer Console.

3. **Authenticate with Zoho API:**

    - Use the endpoint `/zoho/authenticate` to authorize the application with Zoho. Follow the instructions provided in the application.

4. **Custom Zoho Functionality:**
    - Zoho-related functionality is handled via the `ZohoAuthController` and synchronized in the `SyncController`.

---

## Running the Application

1. **Start the Laravel development server:**

   ```bash
   php artisan serve
   ```

   The application will be accessible at: [http://localhost:8000](http://localhost:8000)

2. **Start the Vite development server:**

   ```bash
   npm run dev
   ```

   This starts the Vite server at [http://localhost:5173](http://localhost:5173), providing hot module replacement (HMR) for the frontend.

---

## Building for Production

For production deployment:

1. **Build frontend assets with Vite:**

   ```bash
   npm run build
   ```

2. **Deploy the built assets along with the backend.** All required files will be placed in the `public` folder.

---

## Project Structure

Here’s an overview of the main directories and files in the project:

- **`app/Http/Controllers`:**
  Contains backend controllers such as `InventoryController.php`, `PurchaseOrderController.php`, and `ZohoAuthController.php`.

- **`routes/`:**
  Application routes, organized into:
    - `web.php` for web routes
    - `api.php` for REST API routes

- **`resources/js/components`:**
  Vue components like `CustomerVendorDetails.vue` and `SalesOrderForm.vue`.

- **`database`:**
  SQLite database and migrations located in this directory.

- **`resources/views`:**
  Blade templates for the HTML structure.

- **`tests`:**
  For unit and feature tests to ensure code reliability.

---
