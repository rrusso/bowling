<?php

function getBowlerData() {
    $dataFile = __DIR__ . '/../data/bowlers.json';
    if (!file_exists($dataFile)) {
        return [];
    }
    $json = file_get_contents($dataFile);
    $data = json_decode($json, true);

    // Support both structures just in case (though we saw it's data.Data)
    if (isset($data['data']['Data'])) {
        return $data['data']['Data'];
    } elseif (isset($data['Data'])) {
        return $data['Data'];
    }

    return [];
}
