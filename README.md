# Open-Meteo Weather Cache API

A Symfony-based weather caching layer for the Open-Meteo API, designed to prevent rate limiting when serving weather data to 500+ employees simultaneously.

## 🎯 Purpose

This service acts as a caching proxy between internal application and the Open-Meteo Weather API. It prevents HTTP 429 (rate limit) errors by caching weather forecasts for 5 minutes, allowing hundreds of concurrent requests without hitting API limits.

## ✨ Features

- 🚀 **5-minute Redis caching** to prevent rate limiting
- 📊 **RESTful API** with a simple JSON endpoint
- 🐳 **Docker-ready** with complete setup

## 🛠️ Tech Stack

- **PHP 8.3**
- **Symfony 7.4**
- **Redis** (cache backend)
- **PHPUnit 12** (testing)
- **Docker & Docker Compose**

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Redis server
- PHP Redis extension

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd open-meteo-cache-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env .env.local
```

Edit `.env.local`:

```env
REDIS_URL=redis://localhost:6379
OPEN_METEO_API_BASE_URL=https://api.open-meteo.com/v1
```

### 4. Start Redis

**Using Docker:**
```bash
docker compose up -d redis
```

**Using Homebrew (macOS):**
```bash
brew services start redis
```

**Using apt (Ubuntu/Debian):**
```bash
sudo systemctl start redis-server
```

### 5. Run the Application

**Using Symfony CLI:**
```bash
symfony server:start
```

**Using PHP built-in server:**
```bash
php -S localhost:8000 -t public
```

**Using Docker Compose:**
```bash
docker compose up -d
```

The API will be available at:
- **Symfony CLI / PHP server:** `http://localhost:8000`
- **Docker:** `http://localhost:8080`

## 📡 API Endpoints

### Get Berlin Weather Forecast

```http
GET /api/weather
```

**Success Response:**

```json
{
    "success": true,
    "data": {
        "latitude": 52.52,
        "longitude": 13.42,
        "generationtime_ms": 1.628,
        "utc_offset_seconds": 0,
        "timezone": "GMT",
        "timezone_abbreviation": "GMT",
        "elevation": 38,
        "current_units": {
            "time": "iso8601",
            "interval": "seconds",
            "temperature_2m": "°C"
        },
        "current": {
            "time": "2025-12-23T18:15",
            "interval": 900,
            "temperature_2m": 2.7
        },
        "hourly_units": {
            "time": "iso8601",
            "temperature_2m": "°C"
        },
        "hourly": {
            "time": ["2025-12-23T18:00"],
            "temperature_2m": [2.6]
        }
    },
    "meta": []
}
```

**Error Response:**

```json
{
    "success": false,
    "message": "Internal server error", 
    "errors": []
}
```

## 🏗️ How It Works

```
┌──────────────────────────────────────────────────────────────┐
│                     Employee Dashboard                       │
│                        (users)                               │
└───────────────────────────┬──────────────────────────────────┘
                            │
                            │ GET /api/weather
                            ▼
                ┌───────────────────────┐
                │  WeatherController    │
                │   (HTTP Endpoint)     │
                └───────────┬───────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │   WeatherService      │
                │  (Business Logic)     │
                └───────────┬───────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │    CacheService       │
                │   (Check Redis)       │
                └───────┬───────────────┘
                        │
            ┌───────────┴───────────┐
            │                       │
         Cache HIT              Cache MISS
            │                       │
            ▼                       ▼
    ┌───────────────┐     ┌──────────────────┐
    │  Return       │     │ OpenMeteoClient  │
    │  Cached Data  │     │  (Fetch from API)│
    │  (Instant!)   │     └────────┬─────────┘
    └───────────────┘              │
                                   ▼
                          ┌──────────────────┐
                          │  Cache Result    │
                          │  (5 min TTL)     │
                          └────────┬─────────┘
                                   │
                                   ▼
                          ┌──────────────────┐
                          │  Return Data     │
                          └──────────────────┘
```

## 📁 Project Structure

```
src/
├── Constant/
│   └── WeatherConfig.php           # Configuration constants
├── Controller/
│   └── WeatherController.php       # API endpoint
├── DTO/
│   └── OpenMeteo/
│       └── ForecastOptions.php     # Type-safe API options request builder
├── Enum/
│   └── ApiMessage.php              # API response messages
├── Service/
│   ├── CacheService.php            # Cache abstraction
│   ├── WeatherService.php          # Business logic
│   └── External/
│       └── OpenMeteo/
│           └── OpenMeteoClient.php # Open-Meteo API HTTP client
└── Trait/
    └── ApiResponseTrait.php        # Standardized JSON response formatter

tests/
├── Controller/
│   └── WeatherControllerTest.php
└── Service/
    ├── CacheServiceTest.php
    ├── WeatherServiceTest.php
    └── External/
        └── OpenMeteo/
            └── OpenMeteoClientTest.php
```

## 🧪 Testing

### Run All Tests

```bash
php bin/phpunit
```

### Run Specific Test Suite

```bash
# Service tests
php bin/phpunit tests/Service/

# Controller tests
php bin/phpunit tests/Controller/

# Specific test file
php bin/phpunit tests/Service/CacheServiceTest.php
```

### Run with Coverage

```bash
php bin/phpunit --coverage-text
```

### Test Output

```
PHPUnit 12.5.4 by Sebastian Bergmann and contributors.
.............................................            47 / 47 (100%)

OK (47 tests, 138 assertions)
```
**Overall Line Coverage: 98.69% ✨**

## ⚙️ Configuration

### Cache Settings

Edit `src/Constant/WeatherConfig.php`:

```php
final class WeatherConfig
{
    public const string BERLIN_FORECAST_CACHE_KEY = 'weather.berlin.forecast';
    public const int CACHE_TTL = 300; // 5 minutes in seconds
}
```

### Redis Configuration

Edit `config/packages/cache.yaml`:

```yaml
framework:
    cache:
        app: cache.adapter.redis
        default_redis_provider: '%env(REDIS_URL)%'
```

## 🐳 Docker Deployment

### Build and Run

```bash
docker-compose up -d
```

### Services

- **App**: `http://localhost:8080`
- **Redis**: `localhost:6379`

### Environment Variables

```env
APP_ENV=prod
REDIS_URL=redis://redis:6379
```

## 📝 API Usage Examples

### cURL

```bash
# Using Symfony CLI or PHP server
curl http://localhost:8000/api/weather

# Using Docker
curl http://localhost:8080/api/weather
```

### JavaScript (Fetch)

```javascript
fetch('http://localhost:8000/api/weather')
  .then(response => response.json())
  .then(data => console.log(data));
```
### Python

```python
import requests

response = requests.get('http://localhost:8000/api/weather')
weather = response.json()
```

**Built with ❤️ using Symfony & Redis**
