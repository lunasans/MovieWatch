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
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M18 4v1h-2V4c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v1H4v11c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4h-2zM8 4h6v1H8V4zm10 13H6V6h2v1h6V6h2v11z'/%3E%3C/svg%3E">
    
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
        /* Verbesserte Tagify Custom Styling für bessere Lesbarkeit */
        .tagify {
            --tag-bg: var(--clr-accent) !important;
            --tag-hover: var(--clr-primary) !important;
            --tag-text-color: #ffffff !important;
            --tag-text-color--edit: #ffffff !important;
            --tag-pad: 0.3em 0.5em !important;
            --tag-border-color: var(--clr-accent) !important;
            --tags-border-color: var(--glass-border) !important;
            --placeholder-color: var(--clr-text-muted) !important;
            --tag-remove-bg: rgba(255, 255, 255, 0.3) !important;
            --tag-remove-btn-color: #ffffff !important;
            
            border-radius: var(--radius-md) !important;
            background: var(--glass-bg) !important;
            backdrop-filter: var(--glass-backdrop) !important;
            border: 1px solid var(--glass-border) !important;
            color: var(--clr-text) !important;
            min-height: 45px !important;
            padding: 4px 8px !important;
            font-size: 0.9rem !important;
            font-family: inherit !important;
        }
        
        .tagify:focus-within {
            border-color: var(--clr-accent) !important;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2) !important;
        }
        
        .tagify__input {
            color: var(--clr-text) !important;
            background: transparent !important;
            font-size: 0.9rem !important;
            line-height: 1.4 !important;
            padding: 0.25em !important;
        }
        
        .tagify__input::before {
            color: var(--clr-text-muted) !important;
            font-size: 0.9rem !important;
            opacity: 0.8 !important;
        }
        
        .tagify__tag {
            background: var(--clr-accent) !important;
            color: #ffffff !important;
            border: 1px solid var(--clr-accent) !important;
            border-radius: var(--radius-sm) !important;
            padding: 0.25em 0.5em !important;
            margin: 0.15em 0.1em !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            box-shadow: 0 2px 4px var(--clr-shadow) !important;
            transition: all 0.2s ease !important;
        }
        
        .tagify__tag:hover {
            background: var(--clr-primary) !important;
            border-color: var(--clr-primary) !important;
            transform: translateY(-1px) !important;
        }
        
        .tagify__tag__text {
            color: #ffffff !important;
            font-weight: 500 !important;
        }
        
        .tagify__tag__removeBtn {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 50% !important;
            width: 16px !important;
            height: 16px !important;
            margin-left: 0.3em !important;
            font-size: 12px !important;
            line-height: 1 !important;
            transition: all 0.2s ease !important;
        }
        
        .tagify__tag__removeBtn:hover {
            background: rgba(255, 255, 255, 0.4) !important;
            transform: scale(1.1) !important;
        }
        
        /* DROPDOWN FIX - Höchste Priorität */
        .tagify__dropdown,
        .modal .tagify__dropdown,
        .modal.is-active .tagify__dropdown,
        div[id*="Modal"] .tagify__dropdown {
            background: var(--clr-background, #1a1a2e) !important;
            border: 2px solid var(--clr-accent, #3498db) !important;
            border-radius: var(--radius-md) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8) !important;
            z-index: 999999 !important;
            backdrop-filter: blur(20px) !important;
            max-height: 200px !important;
            overflow-y: auto !important;
        }
        
        /* DROPDOWN ITEMS - KRITISCH */
        .tagify__dropdown__item,
        .modal .tagify__dropdown__item,
        .modal.is-active .tagify__dropdown__item,
        div[id*="Modal"] .tagify__dropdown__item {
            color: var(--clr-text, #ffffff) !important;
            background: transparent !important;
            padding: 12px 16px !important;
            border-bottom: 1px solid var(--clr-border, rgba(255,255,255,0.1)) !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            line-height: 1.4 !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            text-shadow: none !important;
            font-family: inherit !important;
        }
        
        /* DROPDOWN HOVER STATE */
        .tagify__dropdown__item:hover,
        .tagify__dropdown__item.tagify__dropdown__item--active,
        .modal .tagify__dropdown__item:hover,
        .modal.is-active .tagify__dropdown__item:hover {
            background: var(--clr-accent, #3498db) !important;
            color: #ffffff !important;
            transform: translateX(4px) !important;
        }
        
        /* Scrollbar für Dropdown */
        .tagify__dropdown::-webkit-scrollbar {
            width: 8px !important;
        }
        
        .tagify__dropdown::-webkit-scrollbar-track {
            background: var(--clr-surface, rgba(255,255,255,0.1)) !important;
            border-radius: 4px !important;
        }
        
        .tagify__dropdown::-webkit-scrollbar-thumb {
            background: var(--clr-accent, #3498db) !important;
            border-radius: 4px !important;
        }
        
        /* Responsive Anpassungen */
        @media (max-width: 768px) {
            .tagify {
                font-size: 0.8rem !important;
                min-height: 40px !important;
            }
            
            .tagify__tag {
                font-size: 0.75rem !important;
                padding: 0.2em 0.4em !important;
            }
            
            .tagify__dropdown {
                max-height: 150px !important;
            }
            
            .tagify__dropdown__item {
                padding: 10px 12px !important;
                font-size: 0.85rem !important;
            }
        }
        
        /* Debug-Styles für bessere Sichtbarkeit */
        .modal.is-active .tagify {
            border: 2px solid var(--clr-accent) !important;
        }
        
        .modal.is-active .tagify__tag {
            background: var(--clr-accent) !important;
            border: 1px solid var(--clr-accent) !important;
        }
        
        /* Animation für Dropdown */
        .tagify__dropdown {
            animation: dropdownFadeIn 0.2s ease-out !important;
        }
        
        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
</head>
<body>