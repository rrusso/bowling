#!/usr/bin/env php
<?php

require_once __DIR__ . '/src/scraper.php';

$scraper = new Scraper();
if ($scraper->run()) {
    echo "Scrape completed successfully.\n";
} else {
    echo "Scrape failed.\n";
    exit(1);
}
