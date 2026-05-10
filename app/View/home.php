<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - JThinkPHP</title>
    <link rel="stylesheet" href="/css/jthink.css">
</head>
<body>
    <div class="j-container">
        <nav class="j-nav">
            <div class="j-nav-brand">
                <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>JThink</span>
            </div>
            <div class="j-nav-links">
                <a href="/index/index" class="j-nav-link">Home</a>
                <a href="/ui/index" class="j-nav-link">Components</a>
                <a href="#" class="j-nav-link">Docs</a>
            </div>
            <button class="j-btn j-btn-primary j-btn-sm">Get Started</button>
        </nav>

        <header class="j-hero">
            <div class="j-hero-content">
                <h1>Build Fast.</h1>
                <h1>Build Beautiful.</h1>
                <p>A minimalist PHP framework with Glassmorphism design system.</p>
                <div class="j-hero-actions">
                    <button class="j-btn j-btn-primary">Start Building</button>
                    <button class="j-btn j-btn-outline">View Demo</button>
                </div>
            </div>
            <div class="j-hero-stats">
                <div class="j-stat">
                    <div class="j-stat-value">50+</div>
                    <div class="j-stat-label">Components</div>
                </div>
                <div class="j-stat">
                    <div class="j-stat-value">Lightweight</div>
                    <div class="j-stat-label">~50KB</div>
                </div>
                <div class="j-stat">
                    <div class="j-stat-value">Fast</div>
                    <div class="j-stat-label">Blazing Speed</div>
                </div>
            </div>
        </header>

        <section class="j-features">
            <h2>Features</h2>
            <div class="j-grid">
                <div class="j-card">
                    <div class="j-card-icon">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                    </div>
                    <h3>Minimalist</h3>
                    <p>Clean and simple API design. No bloat, no complexity.</p>
                </div>
                <div class="j-card">
                    <div class="j-card-icon">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3>Fast</h3>
                    <p>Optimized for performance. Lightning fast response times.</p>
                </div>
                <div class="j-card">
                    <div class="j-card-icon">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3>Modern UI</h3>
                    <p>Glassmorphism design system with premium aesthetics.</p>
                </div>
            </div>
        </section>

        <section class="j-cards-showcase">
            <h2>UI Components</h2>
            <div class="j-grid">
                <div class="j-card j-card-highlight">
                    <h3>Buttons</h3>
                    <p>Multiple styles: primary, secondary, outline, sizes</p>
                </div>
                <div class="j-card j-card-highlight">
                    <h3>Forms</h3>
                    <p>Inputs, selects, checkboxes, radio buttons</p>
                </div>
                <div class="j-card j-card-highlight">
                    <h3>Layout</h3>
                    <p>Grid, flex, cards with glass effect</p>
                </div>
            </div>
        </section>

        <footer class="j-footer">
            <div class="j-footer-content">
                <div>
                    <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>JThinkPHP</span>
                </div>
                <div class="j-footer-links">
                    <a href="#">Documentation</a>
                    <a href="#">GitHub</a>
                    <a href="#">License</a>
                </div>
            </div>
            <p>&copy; 2024 JThinkPHP. All rights reserved.</p>
        </footer>
    </div>

    <script src="/js/jthink.js"></script>
</body>
</html>