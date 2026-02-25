<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>League Bowler Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .table-container { margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4">League Bowler Data</h1>

    <?php
    $dataFile = 'data/bowlers.json';
    $lastUpdated = '';
    $bowlers = [];
    $meta = [];

    if (file_exists($dataFile)) {
        $jsonData = file_get_contents($dataFile);
        $data = json_decode($jsonData, true);

        // Handle new structure with meta
        if (isset($data['meta']) && isset($data['data']['Data'])) {
            $bowlers = $data['data']['Data'];
            $meta = $data['meta'];
        }
        // Handle legacy structure (just in case)
        elseif (isset($data['Data'])) {
            $bowlers = $data['Data'];
            // Mock meta if missing
            $meta = [
                'leagueId' => '130575',
                'year' => '2026',
                'season' => 'w'
            ];
        }

        $lastUpdated = date("F j, Y, g:i a", filemtime($dataFile));
    } else {
        echo '<div class="alert alert-warning">Data file not found. Please run the scraper.</div>';
    }
    ?>

    <?php if (!empty($lastUpdated)): ?>
        <p class="text-muted">
            Last Updated: <?php echo $lastUpdated; ?>
            <?php if (!empty($meta)): ?>
                (Week: <?php echo htmlspecialchars($meta['weekNum'] ?? 'N/A'); ?>)
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <div class="table-container">
        <table id="bowlerTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Bowler Name</th>
                    <th>Team Name</th>
                    <th>Team #</th>
                    <th>Gender</th>
                    <th>Total Pins</th>
                    <th>Total Games</th>
                    <th>Average</th>
                    <th>High Scratch Game</th>
                    <th>High Scratch Series</th>
                    <th>Most Improved</th>
                    <th>Points Won</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bowlers as $bowler): ?>
                <tr>
                    <td>
                        <a href="https://www.leaguesecretary.com/bowling-centers/traveling-league/bowling-leagues/2026-baton-rouge-varsity/bowler/history/<?php echo htmlspecialchars($meta['leagueId']); ?>/<?php echo htmlspecialchars($meta['year']); ?>/<?php echo htmlspecialchars($meta['season']); ?>/<?php echo htmlspecialchars($bowler['BowlerID']); ?>" target="_blank">
                            <?php echo htmlspecialchars($bowler['BowlerName']); ?>
                        </a>
                    </td>
                    <td>
                        <a href="https://www.leaguesecretary.com/bowling-centers/traveling-league/bowling-leagues/2026-baton-rouge-varsity/team/history/<?php echo htmlspecialchars($meta['leagueId']); ?>/<?php echo htmlspecialchars($meta['year']); ?>/<?php echo htmlspecialchars($meta['season']); ?>/<?php echo htmlspecialchars($bowler['TeamID']); ?>" target="_blank">
                            <?php echo htmlspecialchars($bowler['TeamName']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($bowler['TeamNum']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['Gender']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['TotalPins']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['TotalGames']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['Average']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['HighScratchGame']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['HighScratchSeries']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['MostImproved']); ?></td>
                    <td><?php echo htmlspecialchars($bowler['PointsWonDec']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#bowlerTable').DataTable({
            "pageLength": 25,
            "order": [[ 6, "desc" ]]
        });
    });
</script>

</body>
</html>
