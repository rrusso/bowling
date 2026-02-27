<?php

class Scraper {
    private $leagueUrl = 'https://www.leaguesecretary.com/bowling-centers/traveling-league/bowling-leagues/2026-baton-rouge-varsity/bowler/list/130575';
    private $apiUrl = 'https://www.leaguesecretary.com/Bowler/BowlerByWeekList_Read';
    private $historyApiUrl = 'https://www.leaguesecretary.com/Bowler/BowlerHistory_Read';
    private $dataFile = 'data/bowlers.json';
    private $historyFile = 'data/bowler_history.json';

    public function run() {
        echo "Starting scrape...\n";

        // 1. Fetch main page to get parameters
        $html = $this->fetchUrl($this->leagueUrl);
        if (!$html) {
            echo "Error: Failed to fetch league page.\n";
            return false;
        }

        // 2. Parse HTML to extract parameters
        $params = $this->extractParams($html);
        if (!$params) {
            echo "Error: Failed to extract parameters from HTML.\n";
            return false;
        }

        echo "Extracted parameters: " . json_encode($params) . "\n";

        // 3. Fetch Bowler Data from API
        $bowlerData = $this->fetchBowlerData($params);
        if (!$bowlerData) {
            echo "Error: Failed to fetch bowler data from API.\n";
            return false;
        }

        // 3a. Fix missing team data
        $this->fixMissingTeamData($bowlerData);

        // Add metadata to the saved data
        $outputData = [
            'meta' => $params,
            'data' => $bowlerData
        ];

        // 4. Save to JSON file
        if (file_put_contents($this->dataFile, json_encode($outputData, JSON_PRETTY_PRINT))) {
            echo "Data saved to {$this->dataFile}\n";
        } else {
            echo "Error: Failed to save data to file.\n";
            return false;
        }

        // 5. Fetch History for all bowlers
        echo "Fetching history for bowlers...\n";
        $historyData = $this->fetchAllBowlersHistory($bowlerData['Data'], $params);
        
        if (file_put_contents($this->historyFile, json_encode($historyData, JSON_PRETTY_PRINT))) {
            echo "History saved to {$this->historyFile}\n";
            return true;
        } else {
            echo "Error: Failed to save history data.\n";
            return false;
        }
    }

    private function fetchUrl($url, $postData = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch) . "\n";
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $response;
        } else {
            echo "HTTP Error: $httpCode for URL: $url\n";
            return null;
        }
    }

    private function extractParams($html) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $div = $xpath->query('//div[@id="div_bowlerlist"]')->item(0);

        if ($div) {
            return [
                'leagueId' => $div->getAttribute('data-league'),
                'year' => $div->getAttribute('data-year'),
                'season' => $div->getAttribute('data-season'),
                'weekNum' => $div->getAttribute('data-week'),
                'page' => 1,
                'pageSize' => 20000 // Get all records
            ];
        }
        return null;
    }

    private function fetchBowlerData($params) {
        $jsonResponse = $this->fetchUrl($this->apiUrl, $params);
        if (!$jsonResponse) return null;

        $data = json_decode($jsonResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON Decode Error: " . json_last_error_msg() . "\n";
            return null;
        }

        return $data;
    }

    private function fixMissingTeamData(&$bowlerData) {
        if (!isset($bowlerData['Data']) || !is_array($bowlerData['Data'])) {
            return;
        }

        // 1. Build lookup map of TeamNum => Team Details
        $teamMap = [];
        foreach ($bowlerData['Data'] as $bowler) {
            if (!empty($bowler['TeamNum']) && $bowler['TeamNum'] > 0) {
                $teamMap[$bowler['TeamNum']] = [
                    'TeamID' => $bowler['TeamID'],
                    'TeamName' => $bowler['TeamName'],
                    'TeamNameURLFormated' => $bowler['TeamNameURLFormated']
                ];
            }
        }

        $fixedCount = 0;
        // 2. Iterate and fix missing data
        foreach ($bowlerData['Data'] as &$bowler) {
            if (empty($bowler['TeamNum']) || $bowler['TeamNum'] == 0) {
                // Check for (TeamNum) in BowlerName
                if (preg_match('/\s*\((\d+)\)$/', $bowler['BowlerName'], $matches)) {
                    $extractedTeamNum = (int)$matches[1];

                    if (isset($teamMap[$extractedTeamNum])) {
                        $teamDetails = $teamMap[$extractedTeamNum];
                        
                        $bowler['TeamNum'] = $extractedTeamNum;
                        $bowler['TeamID'] = $teamDetails['TeamID'];
                        $bowler['TeamName'] = $teamDetails['TeamName'];
                        $bowler['TeamNameURLFormated'] = $teamDetails['TeamNameURLFormated'];
                        
                        $fixedCount++;
                    }
                }
            }
        }

        if ($fixedCount > 0) {
            echo "Fixed team data for $fixedCount bowlers.\n";
        }
    }

    private function fetchAllBowlersHistory($bowlers, $params) {
        $allHistory = [];
        $total = count($bowlers);
        $count = 0;

        foreach ($bowlers as $bowler) {
            $count++;
            $bowlerId = $bowler['BowlerID'];
            
            // Basic rate limiting/logging
            if ($count % 10 == 0) echo "Processed $count / $total bowlers...\n";
            
            $history = $this->fetchSingleBowlerHistory($bowlerId, $params);
            if ($history) {
                $allHistory[$bowlerId] = $history;
            }
            
            // Be polite
            usleep(50000); // 50ms
        }
        return $allHistory;
    }

    private function fetchSingleBowlerHistory($bowlerId, $params) {
        $postData = [
            'LeagueId' => $params['leagueId'],
            'Year' => $params['year'],
            'Season' => $params['season'],
            'BowlerId' => $bowlerId
        ];

        $jsonResponse = $this->fetchUrl($this->historyApiUrl, $postData);
        if (!$jsonResponse) return null;

        $data = json_decode($jsonResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['Data'])) {
            return null;
        }

        $games = [];
        foreach ($data['Data'] as $weekData) {
            $date = $weekData['DateBowled'];
            $weekNum = $weekData['WeekNum'];
            
            // Check games 1 through 6
            for ($i = 1; $i <= 6; $i++) {
                $scoreKey = "Score$i";
                $gameNumKey = "GameNum$i";
                
                if (isset($weekData[$scoreKey]) && $weekData[$scoreKey] > 0) {
                    $games[] = [
                        'Date' => $date,
                        'Week' => $weekNum,
                        'Game' => $weekData[$gameNumKey],
                        'Score' => $weekData[$scoreKey]
                    ];
                }
            }
        }
        
        // Sort chronologically (should be already, but just in case)
        usort($games, function($a, $b) {
            $dateCmp = strcmp($a['Date'], $b['Date']);
            if ($dateCmp === 0) {
                return $a['Game'] - $b['Game'];
            }
            return $dateCmp;
        });

        return $games;
    }
}
