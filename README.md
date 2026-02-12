# URL Shortener


## Prerequisites

Before you begin, ensure you have the following installed:

- PHP 8.2 or higher
- Composer
- Node.js 20.19+ or 22.12+
- npm
- MySQL 8.0+

## Local Setup

### 1. Clone the Repository

```bash
git clone https://github.com/MohdumaR125/url-shortner.git
cd url-shortner/my-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` and update the following:

```env
APP_NAME="URL Shortener"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=url_shortener
DB_USERNAME=your_username
DB_PASSWORD=your_password
```



### 5. Create Database

Create a MySQL database:

```sql
CREATE DATABASE url_shortener;
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Seed the Database

```bash
php artisan db:seed
```



### 9. Build Frontend Assets


```bash
npm run build
```

### 10. Start the Development Server


```bash
composer run dev
```

Visit `http://localhost:8000` in your browser.

## Default Login Credentials

After seeding, you can log in with:

| Role | Email | Password |
|------|-------|----------|
| SuperAdmin | `admin@example.com` | `password@123` |

