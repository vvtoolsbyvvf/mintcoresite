<?php
$bridgeSecret = getenv('MINTCORE_BRIDGE_SECRET');
$dashboardPassword = getenv('MINTCORE_DASHBOARD_PASSWORD');
$secondFactorPassword = getenv('MINTCORE_SECOND_FACTOR_PASSWORD');

return [
    'bridge_secret' => $bridgeSecret !== false && $bridgeSecret !== ''
        ? $bridgeSecret
        : 'REPLACE_WITH_A_RANDOM_SECRET_OF_AT_LEAST_64_CHARACTERS',
    'dashboard_password' => $dashboardPassword !== false && $dashboardPassword !== ''
        ? $dashboardPassword
        : 'CHANGE_THIS_TO_A_LONG_UNIQUE_PASSWORD',
    'second_factor_password' => $secondFactorPassword !== false && $secondFactorPassword !== ''
        ? $secondFactorPassword
        : '',
];
