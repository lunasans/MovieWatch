<?php
// inc/footer.php - Sauberer Footer ohne eingebettete Styles
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

<script>
// Footer JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Aktuelles Theme im Footer anzeigen
    const currentThemeEl = document.getElementById('currentTheme');
    if (currentThemeEl) {
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
    
    // Scroll Progress Indicator (optional)
    const footer = document.querySelector('.app-footer');
    const scrollIndicator = document.createElement('div');
    scrollIndicator.className = 'scroll-indicator';
    footer.appendChild(scrollIndicator);
    
    window.addEventListener('scroll', function() {
        const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        scrollIndicator.style.width = scrollPercent + '%';
    });
    
    // Auto-hide Footer beim Scrollen (optional - auskommentiert)
    /*
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > lastScrollY && currentScrollY > 100) {
            // Scrolling down
            footer.classList.add('footer-hidden');
        } else {
            // Scrolling up
            footer.classList.remove('footer-hidden');
        }
        
        lastScrollY = currentScrollY;
    });
    */
    
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
            document.querySelector('.footer-logo')?.classList.add('konami-active');
            showToast('🎮 Konami Code aktiviert! Du bist ein echter Retro-Gamer!', 'info');
            
            setTimeout(() => {
                document.querySelector('.footer-logo')?.classList.remove('konami-active');
            }, 1000);
            
            konamiCode = [];
        }
    });
    
    // Version Badge Click Event
    document.querySelector('.version-badge')?.addEventListener('click', function() {
        const versionInfo = `MovieWatch v<?= $version ?> "<?= $codename ?>"
Build: <?= $buildDate ?>
PHP: <?= PHP_VERSION ?>
User Agent: ${navigator.userAgent.substring(0, 50)}...`;
        
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