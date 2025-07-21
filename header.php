<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MovieWatchList</title>

    <!-- Preload Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1a1a2e">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M18 4v1h-2V4c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v1H4v11c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4h-2zM8 4h6v1H8V4zm10 13H6V6h2v1h6V6h2v11z'/%3E%3C/svg%3E">

    <!-- Tagify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <!-- Meta -->
    <meta name="description" content="Moderne Film-Verwaltung mit eleganter Benutzeroberfläche">
    <meta name="author" content="MovieWatchList">

    <!-- Open Graph -->
    <meta property="og:title" content="MovieWatchList">
    <meta property="og:description" content="Verwalten Sie Ihre Filmsammlung mit Stil">
    <meta property="og:type" content="website">

    <style>
        /* Tagify Custom Styling */
        .tagify {
            --tag-bg: var(--glass-bg);
            --tag-hover: var(--clr-surface-hover);
            --tag-text-color: var(--clr-text);
            --tag-border-color: var(--glass-border);
            --tags-border-color: var(--glass-border);
            --placeholder-color: var(--clr-text-muted);
            --tag-remove-bg: var(--clr-accent);
            border-radius: var(--radius-md);
            background: var(--glass-bg);
            backdrop-filter: var(--glass-backdrop);
        }

        .tagify__input {
            color: var(--clr-text);
        }

        .tagify__tag {
            background: var(--clr-accent);
            color: white;
            border-radius: var(--radius-sm);
            box-shadow: 0 2px 4px var(--clr-shadow);
        }

        .tagify__tag:hover {
            background: var(--clr-primary);
        }

        .tagify__dropdown {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-backdrop);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            box-shadow: 0 8px 32px var(--clr-shadow);
        }

        .tagify__dropdown__item {
            color: var(--clr-text);
        }

        .tagify__dropdown__item:hover {
            background: var(--clr-surface-hover);
        }
    </style>
</head>

<body>