<?php
// inc/footer.php - Dezenter Footer mit Versionsinformationen
require_once __DIR__ . '/version.php';
?>

<footer class="app-footer">
    <div class="footer-content">
        <div class="footer-left">
            <div class="footer-logo">
                <i class="bi bi-film"></i>
                <span>MovieWatch</span>
            </div>
            <div class="footer-tagline">
                Moderne Film-Verwaltung
            </div>
        </div>
        
        <div class="footer-center">
            <div class="footer-stats">
                <span class="stat-item">
                    <i class="bi bi-collection"></i>
                    <?= $totalMovies ?> Filme
                </span>
                <span class="stat-item">
                    <i class="bi bi-eye"></i>
                    <?= $totalWatches ?> Sichtungen
                </span>
                <span class="stat-item">
                    <i class="bi bi-tags"></i>
                    <?= $totalTags ?? 0 ?> Tags
                </span>
            </div>
        </div>
        
        <div class="footer-right">
            <div class="version-info">
                <span class="version-badge" title="<?= getMovieWatchVersionFull() ?>">
                    v<?= $version ?>
                </span>
                <span class="codename"><?= $codename ?></span>
            </div>
            <div class="build-info">
                Build <?= $buildDate ?>
            </div>
        </div>
    </div>
    
    <!-- Erweiterte Info beim Hover -->
    <div class="footer-extended" id="footerExtended">
        <div class="tech-info">
            <div class="tech-item">
                <strong>Frontend:</strong> Vanilla JS, Modern CSS
            </div>
            <div class="tech-item">
                <strong>Backend:</strong> PHP <?= PHP_VERSION ?>
            </div>
            <div class="tech-item">
                <strong>Features:</strong> 
                <?php 
                $enabledFeatures = array_keys(array_filter(MOVIEWATCH_FEATURES));
                echo count($enabledFeatures) . ' aktiv';
                ?>
            </div>
            <div class="tech-item">
                <strong>Theme:</strong> <span id="currentTheme">Standard</span>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.app-footer {
    margin-top: var(--spacing-2xl);
    background: var(--glass-bg);
    backdrop-filter: var(--glass-backdrop);
    border-top: 1px solid var(--glass-border);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    padding: var(--spacing-lg);
    position: relative;
    overflow: hidden;
    transition: var(--transition-smooth);
}

.app-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--clr-accent), var(--clr-primary));
    opacity: 0.5;
}

.footer-content {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: var(--spacing-lg);
    max-width: 1400px;
    margin: 0 auto;
}

.footer-left {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--clr-accent);
}

.footer-tagline {
    font-size: 0.85rem;
    color: var(--clr-text-muted);
    font-style: italic;
}

.footer-center {
    text-align: center;
}

.footer-stats {
    display: flex;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
    justify-content: center;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.85rem;
    color: var(--clr-text-muted);
    padding: var(--spacing-xs) var(--spacing-sm);
    background: var(--clr-surface);
    border-radius: var(--radius-sm);
    transition: var(--transition-fast);
}

.stat-item:hover {
    background: var(--clr-surface-hover);
    color: var(--clr-text);
    transform: translateY(-1px);
}

.footer-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--spacing-xs);
}

.version-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.version-badge {
    background: linear-gradient(135deg, var(--clr-accent), var(--clr-primary));
    color: white;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    font-family: 'Courier New', monospace;
    box-shadow: 0 2px 4px var(--clr-shadow);
}

.codename {
    font-size: 0.8rem;
    color: var(--clr-text-muted);
    font-style: italic;
    font-weight: 500;
}

.build-info {
    font-size: 0.75rem;
    color: var(--clr-text-muted);
    font-family: 'Courier New', monospace;
    opacity: 0.7;
}

/* Extended Footer Info */
.footer-extended {
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--glass-border);
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.app-footer:hover .footer-extended {
    opacity: 1;
    max-height: 100px;
}

.tech-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-sm);
    text-align: center;
}

.tech-item {
    font-size: 0.75rem;
    color: var(--clr-text-muted);
    padding: var(--spacing-xs);
    background: var(--clr-surface);
    border-radius: var(--radius-sm);
    transition: var(--transition-fast);
}

.tech-item:hover {
    background: var(--clr-surface-hover);
    color: var(--clr-text);
}

.tech-item strong {
    color: var(--clr-accent);
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: var(--spacing-md);
    }
    
    .footer-right {
        align-items: center;
    }
    
    .footer-stats {
        gap: var(--spacing-sm);
    }
    
    .stat-item {
        font-size: 0.8rem;
    }
    
    .tech-info {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .footer-stats {
        flex-direction: column;
        gap: var(--spacing-xs);
    }
    
    .version-info {
        flex-direction: column;
        gap: var(--spacing-xs);
    }
}

/* Theme Indicator Animation */
#currentTheme {
    position: relative;
    overflow: hidden;
}

#currentTheme::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--clr-accent);
    animation: themeIndicator 2s ease-in-out infinite;
}

@keyframes themeIndicator {
    0%, 100% { transform: scaleX(0); }
    50% { transform: scaleX(1); }
}

/* Version Badge Glow Effect */
.version-badge {
    position: relative;
    overflow: hidden;
}

.version-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: var(--transition-smooth);
}

.version-badge:hover::before {
    left: 100%;
}

/* Easter Egg: Konami Code Support */
.footer-logo.konami-active {
    animation: konamiGlow 0.5s ease;
}

@keyframes konamiGlow {
    0%, 100% { 
        color: var(--clr-accent);
        text-shadow: none;
    }
    50% { 
        color: #ff6b8a;
        text-shadow: 0 0 10px #ff6b8a;
    }
}
</style>

<script>
// Footer JavaScript für erweiterte Funktionalität
document.addEventListener('DOMContentLoaded', function() {
    // Aktuelles Theme im Footer anzeigen
    const currentThemeEl = document.getElementById('currentTheme');
    if (currentThemeEl) {
        // Theme aus localStorage lesen
        const savedTheme = localStorage.getItem('movieWatchTheme');
        if (savedTheme) {
            const theme = JSON.parse(savedTheme);
            const themeNames = {
                '#1a1a2e': 'Standard',
                '#461220': 'Romantic',
                '#192A51': 'Ocean',
                '#2d1b69': 'Royal',
                '#0c5460': 'Forest'
            };
            
            const themeName = themeNames[theme.background] || 'Custom';
            currentThemeEl.textContent = themeName;
        }
    }
    
    // Konami Code Easter Egg
    let konamiCode = [];
    const konamiSequence = [
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
        'KeyB', 'KeyA'
    ];
    
    document.addEventListener('keydown', function(e) {
        konamiCode.push(e.code);
        
        if (konamiCode.length > konamiSequence.length) {
            konamiCode.shift();
        }
        
        if (konamiCode.join(',') === konamiSequence.join(',')) {
            // Konami Code aktiviert!
            document.querySelector('.footer-logo').classList.add('konami-active');
            showToast('🎮 Konami Code aktiviert! Du bist ein echter Retro-Gamer!', 'info');
            
            setTimeout(() => {
                document.querySelector('.footer-logo').classList.remove('konami-active');
            }, 1000);
            
            konamiCode = [];
        }
    });
    
    // Version Badge Click Event
    document.querySelector('.version-badge')?.addEventListener('click', function() {
        const versionInfo = `
MovieWatch v<?= $version ?> "<?= $codename ?>"
Build: <?= $buildDate ?>
PHP: <?= PHP_VERSION ?>
User Agent: ${navigator.userAgent.substring(0, 50)}...
        `.trim();
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(versionInfo).then(() => {
                showToast('📋 Versionsinformationen kopiert!', 'success');
            });
        } else {
            showToast('ℹ️ ' + versionInfo.replace(/\n/g, ' | '), 'info');
        }
    });
});
</script>