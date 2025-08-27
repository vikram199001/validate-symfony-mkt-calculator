# 🧮 MKT Calculator

A professional web application for calculating **Mean Kinetic Temperature (MKT)** from temperature data files. Built with Symfony PHP framework, PostgreSQL database, and modern frontend technologies.

## 📋 Table of Contents

-   [Overview](#-overview)
-   [Features](#-features)
-   [Tech Stack](#-tech-stack)
-   [Prerequisites](#-prerequisites)
-   [Installation](#-installation)
-   [Quick Start](#-quick-start)
-   [Docker Setup](#-docker-setup)
-   [Usage](#-usage)
-   [Supported File Formats](#-supported-file-formats)
-   [API Documentation](#-api-documentation)
-   [Testing](#-testing)
-   [Current Status & Recent Updates](#-current-status--recent-updates)
-   [Development Commands](#-development-commands)
-   [Project Structure](#-project-structure)
-   [Key Features Explained](#-key-features-explained)
-   [Production Deployment](#-production-deployment)
-   [License](#-license)

## 🔬 Overview

The MKT Calculator is designed for pharmaceutical, chemical, and research industries to calculate Mean Kinetic Temperature from time-series temperature data. The application supports various file formats, provides interactive visualizations, and delivers precise calculations following industry standards.

### 🌡️ What is Mean Kinetic Temperature?

Mean Kinetic Temperature (MKT) is a simplified way of expressing the overall effect of temperature fluctuations during storage or transport on the degradation of pharmaceutical products. Unlike simple arithmetic mean, MKT considers the Arrhenius equation and gives more weight to higher temperatures because they cause more damage to pharmaceutical products.

**Formula:**

```
MKT = -ΔH/R × (1/ln(Σ(e^(-ΔH/R × 1/T_i))/n))
```

Where:

-   **ΔH** = Activation Energy (83.144 kJ/mol)
-   **R** = Gas Constant (8.314 J/(mol·K))
-   **T_i** = Temperature readings in Kelvin
-   **n** = Number of temperature readings

### 🎯 Key Applications

-   **Pharmaceutical Storage**: Temperature monitoring for drug storage validation
-   **Chemical Industry**: Reaction kinetics and stability analysis
-   **Research Labs**: Environmental monitoring and compliance
-   **Quality Control**: Temperature excursion analysis

## ✨ Features

### 🏗️ Core Functionality

-   📊 **MKT Calculation**: Precise Mean Kinetic Temperature calculations using Arrhenius equation
-   � **Multi-format Support**: CSV and Excel file processing (.csv, .xlsx, .xls)
-   📈 **Interactive Charts**: Temperature trend visualization with Chart.js
-   � **Statistical Analysis**: Min, max, average temperature calculations
-   � **Real-time Processing**: Instant calculations and results display

### 🎨 User Experience

-   🎨 **Modern UI**: Bootstrap 5 with responsive design
-   📱 **Mobile Friendly**: Fully responsive across all devices
-   🖱️ **Drag & Drop**: Easy file upload interface
-   📊 **Data Visualization**: Interactive temperature charts
-   ⚡ **Real-time Updates**: Live calculation updates

### 🔧 Technical Features

-   🗄️ **Database**: PostgreSQL with Docker containerization
-   🔍 **API**: RESTful JSON API for data access
-   🧪 **Testing**: Comprehensive test suite with PHPUnit
-   🛡️ **Security**: File validation, CSRF protection, input sanitization
-   � **Containerized**: Docker Compose for easy deployment

## 🚀 Tech Stack

### **Backend**

-   **PHP 8.2.12** - Modern PHP with latest features
-   **Symfony 7.3** - Full-stack web framework
-   **Doctrine ORM** - Database abstraction and migrations
-   **PostgreSQL 16** - Reliable relational database

### **Frontend**

-   **HTML5 & CSS3** - Modern web standards
-   **Bootstrap 5** - Responsive CSS framework
-   **JavaScript ES6+** - Modern JavaScript features
-   **Chart.js** - Interactive data visualization
-   **Axios** - HTTP client for API calls

### **Development & Deployment**

-   **Docker & Docker Compose** - Containerization
-   **Symfony CLI** - Development server and tools
-   **PHPUnit** - Testing framework
-   **Composer** - PHP dependency management

## 📋 Prerequisites

### **Option 1: Full Docker Setup (Recommended)**

For the complete containerized setup, you only need:

-   **Docker & Docker Compose** (latest version)
-   **Git** (for cloning)

Everything else (PHP, PostgreSQL, dependencies) runs in containers!

### **Option 2: Hybrid Setup (Database in Docker, Symfony Native)**

If you prefer running Symfony natively with Docker database:

-   **PHP 8.2+** with required extensions (see below)
-   **Composer** (latest version)
-   **Docker & Docker Compose** (for database only)
-   **Symfony CLI** (recommended for development)
-   **Git** (for cloning)

### 🔧 Required PHP Extensions

While Symfony packages handle most functionality, some core PHP extensions are still required:

```bash
# On Ubuntu/Debian
sudo apt install php8.2-pgsql php8.2-intl php8.2-xml php8.2-zip php8.2-curl php8.2-mbstring

# On Windows (with XAMPP)
# Enable in php.ini:
extension=pdo_pgsql
extension=intl
extension=zip
extension=curl
extension=mbstring

# Required extensions:
# - pdo_pgsql: PostgreSQL database connectivity
# - intl: Internationalization support
# - zip: File compression/decompression
# - curl: HTTP client functionality
# - mbstring: Multi-byte string handling
```

### 📝 Note on Extensions

-   **Symfony packages** handle high-level database operations
-   **Basic PHP extensions** are still required for core connectivity
-   **PDO PostgreSQL driver** is essential for database access

## 🚀 Installation

## ⚡ Quick Start

### **Option A: Full Docker Setup (Recommended)**

```bash
# 1. Clone repository
git clone <your-repository-url>
cd mkt-calculator

# 2. Build and start all services
docker compose up -d --build

# 3. Run database migrations
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction

# 4. Access application
# Main App: http://localhost:8000
# Database Admin: http://localhost:8080
# Email Testing: http://localhost:8025
```

### **Option B: Hybrid Setup (Symfony Native + Docker Database)**

```bash
# 1. Clone repository
git clone <your-repository-url>
cd mkt-calculator

# 2. Install PHP dependencies
composer install

# 3. Environment configuration
cp .env .env.local
# Edit .env.local and set DATABASE_URL to:
# DATABASE_URL="postgresql://mkt_user:mkt_password@127.0.0.1:5432/mkt_calculator?serverVersion=16&charset=utf8"

# 4. Start database only
docker compose up -d database

# 5. Wait for database to be ready (10-15 seconds)
sleep 15

# 6. Run database migrations
symfony console doctrine:migrations:migrate --no-interaction

# 7. Create test database
docker compose exec database createdb -U mkt_user mkt_calculator_test
symfony console doctrine:migrations:migrate --no-interaction --env=test

# 8. Create uploads directory
mkdir -p public/uploads
chmod 777 public/uploads

# 9. Start Symfony server
symfony serve -d
# Application will be available at: http://127.0.0.1:8000
```

## 📖 Detailed Installation

### 1. **Clone the Repository**

```bash
git clone <your-repository-url>
cd mkt-calculator
```

### 2. **Install PHP Dependencies**

```bash
composer install
```

### 3. **Environment Configuration**

```bash
# Copy environment file
cp .env .env.local

# Generate APP_SECRET (or use the existing one)
# APP_SECRET=your-secret-key-here
```

### 4. **Database Setup with Docker**

```bash
# Start PostgreSQL container
docker compose up -d database

# Wait for database to be ready (about 10-15 seconds)
sleep 15

# Create test database (for running tests)
docker compose exec database createdb -U mkt_user mkt_calculator_test
```

### 5. **Run Database Migrations**

```bash
# Run migrations for main database
symfony console doctrine:migrations:migrate --no-interaction

# Run migrations for test database
symfony console doctrine:migrations:migrate --no-interaction --env=test
```

### 6. **Create Uploads Directory**

```bash
# Create and set permissions for uploads
mkdir -p public/uploads
chmod 755 public/uploads
```

## 🏃‍♂️ Quick Start

### **Method 1: Using Symfony CLI (Recommended)**

```bash
# Start the development server
symfony serve

# Application will be available at:
# http://127.0.0.1:8000
```

### **Method 2: Using PHP Built-in Server**

```bash
# Start PHP development server
php -S localhost:8000 -t public/

# Application will be available at:
# http://localhost:8000
```

### **Method 3: Full Docker Stack**

```bash
# Start all services (web + database + adminer)
docker compose up -d

# Application will be available at:
# http://localhost:8000
# Database admin at: http://localhost:8080 (Adminer)
```

## 🐳 Docker Setup

### **Database Only (Recommended for Development)**

```bash
# Start only PostgreSQL database
docker compose up -d database

# Stop database
docker compose down

# View database logs
docker compose logs database
```

### **Full Stack with Docker**

```bash
# Build and start all services
docker compose up -d

# Available services:
# - PostgreSQL Database: localhost:5432
# - Adminer (DB Admin): http://localhost:8080
# - Mailpit (Email Testing): http://localhost:8025

# View all logs
docker compose logs -f

# Stop all services
docker compose down

# Rebuild after code changes
docker compose up -d --build
```

### **Database Management**

```bash
# Access database directly
docker compose exec database psql -U mkt_user -d mkt_calculator

# Backup database
docker compose exec database pg_dump -U mkt_user mkt_calculator > backup.sql

# Restore database
cat backup.sql | docker compose exec -T database psql -U mkt_user -d mkt_calculator

# Create test database (for running tests)
docker compose exec database createdb -U mkt_user mkt_calculator_test
```

## 📖 Usage

### **1. Upload Temperature Data**

1. Navigate to **Upload Dataset** page
2. Choose a CSV or Excel file with temperature data
3. Required format: `timestamp, temperature` columns
4. Supported timestamp formats:
    - `YYYY-MM-DD HH:MM:SS`
    - `MM/DD/YYYY HH:MM`
    - `DD-MM-YYYY HH:MM`

### **2. View Results**

-   **MKT Value**: Calculated Mean Kinetic Temperature
-   **Statistics**: Min, max, average temperatures
-   **Interactive Chart**: Temperature trends over time
-   **Data Table**: Complete temperature readings

### **3. API Access**

```bash
# Get all datasets
curl http://localhost:8000/datasets/api/datasets

# Get specific dataset
curl http://localhost:8000/datasets/api/datasets/1

# Get temperature readings
curl http://localhost:8000/datasets/api/datasets/1/readings
```

## 📁 Supported File Formats

### CSV Format

```csv
timestamp,temperature
2024-01-01 00:00:00,25.1
2024-01-01 01:00:00,24.8
2024-01-01 02:00:00,24.5
```

### Excel Files

-   First row should contain headers
-   Timestamp column (various formats supported)
-   Temperature column (numeric values)
-   .xlsx and .xls formats supported

## � API Documentation

### **Endpoints**

| Method | Endpoint                      | Description         |
| ------ | ----------------------------- | ------------------- |
| `GET`  | `/datasets/api/datasets`      | List all datasets   |
| `GET`  | `/datasets/api/datasets/{id}` | Get dataset details |
| `POST` | `/datasets/upload`            | Upload new dataset  |
| `POST` | `/datasets/{id}/calculate`    | Recalculate MKT     |

### **Example Responses**

```json
{
    "id": 1,
    "name": "Temperature Data Set 1",
    "fileType": "csv",
    "fileSize": 1024,
    "mktValue": "25.67",
    "temperatureReadingsCount": 24,
    "minTemperature": "20.5",
    "maxTemperature": "30.2",
    "avgTemperature": "25.1",
    "uploadedAt": "2025-08-27T00:00:00+00:00"
}
```

## 🧪 Testing

### **Run All Tests**

```bash
# Run complete test suite
php vendor/bin/phpunit

# Run with coverage (requires Xdebug)
php vendor/bin/phpunit --coverage-html coverage/
```

### **Run Specific Test Categories**

```bash
# Unit tests only
php vendor/bin/phpunit tests/Unit/

# Integration tests only
php vendor/bin/phpunit tests/Controller/

# Specific test class
php vendor/bin/phpunit tests/Service/MktCalculatorServiceTest.php
```

### **Test Database**

Tests automatically use the `mkt_calculator_test` database. Ensure it exists:

```bash
# Create test database if not exists
docker compose exec database createdb -U mkt_user mkt_calculator_test || true

# Run test migrations
symfony console doctrine:migrations:migrate --no-interaction --env=test
```

## 🚀 Current Status & Recent Updates

### ✅ **What's Working (Verified August 2025)**

-   **✅ File Upload**: Fixed "SplFileInfo::getSize()" error - uploads working perfectly
-   **✅ Database**: PostgreSQL with Docker - all connections working
-   **✅ Vue.js Charts**: Template syntax errors resolved - charts rendering properly
-   **✅ Docker Setup**: Updated compose.yaml with all services (database, adminer, mailer)
-   **✅ API Endpoints**: All REST API routes functioning correctly
-   **✅ Tests**: Complete test suite passing (9/9 tests, 20 assertions)
-   **✅ Twig Extensions**: Custom formatFileSize function working
-   **✅ Security**: CSRF protection, input validation, file sanitization active

### 🔧 **Recent Fixes Applied**

1. **File Upload Service**: Modified to store file size before moving files
2. **Vue.js Integration**: Fixed template syntax conflicts with Twig
3. **Docker Configuration**: Streamlined compose.yaml, removed conflicting files
4. **Database Schema**: All migrations applied and validated
5. **Extension Requirements**: Clarified PHP extension dependencies

### 🌐 **Available Services**

| Service              | URL                     | Purpose                         |
| -------------------- | ----------------------- | ------------------------------- |
| **Main Application** | `http://127.0.0.1:8000` | MKT Calculator web interface    |
| **Database Admin**   | `http://127.0.0.1:8080` | Adminer - PostgreSQL management |
| **Email Testing**    | `http://127.0.0.1:8025` | Mailpit - Email debugging       |
| **Database Direct**  | `localhost:5432`        | PostgreSQL connection           |

## 🔧 Development Commands

### **Database Commands**

```bash
# Create new migration
symfony console make:migration

# Run migrations
symfony console doctrine:migrations:migrate

# Check migration status
symfony console doctrine:migrations:status

# Create database schema
symfony console doctrine:schema:create --force
```

### **Cache Commands**

```bash
# Clear cache
symfony console cache:clear

# Warm up cache
symfony console cache:warmup

# Clear cache for production
symfony console cache:clear --env=prod
```

### **Debug Commands**

```bash
# List all routes
symfony console debug:router

# Check services
symfony console debug:container

# Validate configuration
symfony console lint:yaml config/
symfony console lint:twig templates/
```

## 📁 Project Structure

```
mkt-calculator/
├── config/              # Symfony configuration
├── migrations/          # Database migrations
├── public/              # Web accessible files
│   ├── uploads/         # Uploaded temperature files
│   └── index.php        # Application entry point
├── src/
│   ├── Controller/      # Web controllers
│   ├── Entity/          # Database entities
│   ├── Repository/      # Data repositories
│   ├── Service/         # Business logic
│   └── Twig/           # Twig extensions
├── templates/           # Twig templates
├── tests/              # Test suite
├── var/                # Cache and logs
├── vendor/             # Dependencies
├── compose.yaml        # Docker configuration
├── .env                # Environment variables
└── README.md          # This file
```

## 🌟 Key Features Explained

### **MKT Calculation Algorithm**

The application uses the standard Arrhenius equation for MKT calculation:

```
MKT = ΔH/R × ln(Σ(e^(-ΔH/R × Ti))) / n
```

Where:

-   `ΔH` = Activation energy (default: 83.144 kJ/mol)
-   `R` = Gas constant (8.314 J/mol·K)
-   `Ti` = Individual temperature readings (Kelvin)
-   `n` = Number of readings

### **File Processing**

-   **CSV Files**: Automatic delimiter detection
-   **Excel Files**: Support for .xlsx and .xls formats
-   **Timestamp Parsing**: Multiple format recognition
-   **Data Validation**: Temperature range and format validation
-   **Error Handling**: Comprehensive error reporting

### **Security Features**

-   **File Upload Validation**: Type, size, and content validation
-   **CSRF Protection**: Form submission protection
-   **SQL Injection Prevention**: Doctrine ORM parameterized queries
-   **Input Sanitization**: All user inputs validated and sanitized

## 🚀 Production Deployment

### **Environment Setup**

```bash
# Production environment
APP_ENV=prod
APP_DEBUG=false

# Database configuration
DATABASE_URL="postgresql://user:password@host:5432/mkt_calculator"

# Generate strong secret
APP_SECRET=$(openssl rand -hex 32)
```

### **Optimization Commands**

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Build production cache
symfony console cache:clear --env=prod
symfony console cache:warmup --env=prod

# Optimize autoloader
composer dump-autoload --optimize --no-dev
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Built with ❤️ using Symfony, PostgreSQL, and modern web technologies**
