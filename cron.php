#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/src/scraper.php';

$scraper = new Scraper();
if ($scraper->run()) {
    echo "Scrape completed successfully.\n";
} else {
    echo "Scrape failed.\n";
    exit(1);
}
