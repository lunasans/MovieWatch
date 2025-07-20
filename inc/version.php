<?php
/**
 * MovieWatch - Versionsverwaltung
 * Zentrale Stelle für alle Versionsinformationen
 */

// Version Information
define('MOVIEWATCH_VERSION', '1.0.1');
define('MOVIEWATCH_CODENAME', 'Nebula');
define('MOVIEWATCH_BUILD_DATE', '2025.07.20');
define('MOVIEWATCH_RELEASE_DATE', '20. Juli 2025');

// Build Information
define('MOVIEWATCH_BUILD_TYPE', 'Release'); // Release, Beta, Alpha, Development
define('MOVIEWATCH_BRANCH', 'main');
define('MOVIEWATCH_COMMIT', 'a7f3b2c'); // Git commit hash (ersten 7 Zeichen)

// Feature Flags
define('MOVIEWATCH_FEATURES', [
    'modern_design' => true,
    'theme_switcher' => true,
    'glassmorphism' => true,
    'advanced_search' => false,
    'collections' => false,
    'user_profiles' => false,
    'api_v2' => false,
    'mobile_app' => false
]);

// System Requirements
define('MOVIEWATCH_REQUIREMENTS', [
    'php_min' => '7.4.0',
    'php_recommended' => '8.0.0',
    'mysql_min' => '5.7.0',
    'browser_support' => [
        'Chrome' => '90+',
        'Firefox' => '88+',
        'Safari' => '14+',
        'Edge' => '90+'
    ]
]);

// Changelog (letzten 5 Versionen)
define('MOVIEWATCH_CHANGELOG', [
    '1.0.1' => [
        'date' => '2025-07-05',
        'type' => 'minor',
        'changes' => [
            'Added rating system (like/neutral/dislike)',
            'Implemented user authentication',
            'Added watch logs functionality',
            'Basic CRUD operations for movies',
            'Added tag system',
            'Implemented search functionality',
            'Added movie statistics',
            'Improved user interface',
            'Fixed database connection issues',
            'Improved tag handling',
            'Better error messages',
            'Security improvements',
            'Complete UI redesign with glassmorphism',
            'Added animated background circles',
            'Implemented 5 different themes',
            'Modern card-based layout',
            'Improved mobile responsiveness',
            'Enhanced rating system with animations',
            'Added elegant footer with version information',
            'Improved error handling in API endpoints', 
            'Fixed JSON parsing issues',
            'Enhanced theme persistence',
            'Added Konami code easter egg'
        ]
    ]
]);

// Helper Functions
function getMovieWatchVersion() {
    return MOVIEWATCH_VERSION;
}

function getMovieWatchVersionFull() {
    $version = MOVIEWATCH_VERSION;
    $codename = MOVIEWATCH_CODENAME;
    $buildType = MOVIEWATCH_BUILD_TYPE;
    $buildDate = MOVIEWATCH_BUILD_DATE;
    
    if ($buildType !== 'Release') {
        return "{$version}-{$buildType} \"{$codename}\" (Build {$buildDate})";
    }
    
    return "{$version} \"{$codename}\"";
}

function getMovieWatchBuildInfo() {
    return [
        'version' => MOVIEWATCH_VERSION,
        'codename' => MOVIEWATCH_CODENAME,
        'build_date' => MOVIEWATCH_BUILD_DATE,
        'build_type' => MOVIEWATCH_BUILD_TYPE,
        'branch' => MOVIEWATCH_BRANCH,
        'commit' => MOVIEWATCH_COMMIT,
        'php_version' => PHP_VERSION,
        'features' => MOVIEWATCH_FEATURES
    ];
}

function isFeatureEnabled($feature) {
    return MOVIEWATCH_FEATURES[$feature] ?? false;
}

function getLatestChangelog($limit = 3) {
    return array_slice(MOVIEWATCH_CHANGELOG, 0, $limit, true);
}

function checkSystemRequirements() {
    $requirements = MOVIEWATCH_REQUIREMENTS;
    $results = [
        'php' => version_compare(PHP_VERSION, $requirements['php_min'], '>='),
        'mysql' => true, // Wird später implementiert
        'overall' => true
    ];
    
    $results['overall'] = $results['php'] && $results['mysql'];
    
    return $results;
}

// Exportiere wichtige Variablen für Templates
$version = MOVIEWATCH_VERSION;
$buildDate = MOVIEWATCH_BUILD_DATE;
$codename = MOVIEWATCH_CODENAME;
$buildType = MOVIEWATCH_BUILD_TYPE;
$fullVersion = getMovieWatchVersionFull();
$buildInfo = getMovieWatchBuildInfo();
?>