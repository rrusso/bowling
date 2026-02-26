# Bowling League Scraper

This project scrapes the Baton Rouge Varsity Bowling League data from LeagueSecretary.com and displays it in a sortable HTML table.

## Requirements

- PHP 7.4+ with `curl` and `dom` extensions enabled.
- Web server (Apache/Nginx) or PHP built-in server.

## Installation

1.  Clone the repository or download the files.
2.  Ensure the `data` directory is writable by the web server or the user running the scraper.
    ```bash
    mkdir -p data
    chmod 775 data
    ```

## Usage

### 1. Initial Scrape

Run the scraper manually to fetch the initial data:

```bash
php cron.php
```

This will create `data/bowlers.json`.

### 2. View Data

Open `index.php` in your browser. If you are using a local PHP server:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`.

### 3. Automated Hourly scrape

To keep the data updated hourly, set up a cron job.

Edit your crontab:

```bash
crontab -e
```

Add the following line (adjust the path to your project):

```cron
0 * * * * /usr/bin/php /path/to/project/cron.php >> /path/to/project/cron.log 2>&1
```

This will run the scraper every hour at minute 0.

## Project Structure

- `src/Scraper.php`: The main scraper class.
- `cron.php`: The executable script for scraping.
- `index.php`: The frontend to view the data.
- `data/`: Directory where `bowlers.json` is stored.

## Customization

The scraper is configured for a specific league URL. To change it, modify the `$leagueUrl` property in `src/Scraper.php`.
