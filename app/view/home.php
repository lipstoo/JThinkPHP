<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - JThinkPHP</title>
    <link rel="stylesheet" href="/public/css/jthink.css">
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

    <script src="/public/js/jthink.js"></script>
    <style>
        .j-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: var(--j-bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--j-radius-lg);
            margin-bottom: 32px;
        }
        
        .j-nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .j-nav-brand .j-icon {
            width: 28px;
            height: 28px;
            color: var(--j-primary);
        }
        
        .j-nav-links {
            display: flex;
            gap: 24px;
        }
        
        .j-nav-link {
            color: var(--j-text-secondary);
            text-decoration: none;
            transition: var(--j-transition);
        }
        
        .j-nav-link:hover {
            color: var(--j-primary);
        }
        
        .j-hero {
            text-align: center;
            padding: 60px 20px;
        }
        
        .j-hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--j-primary), var(--j-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 8px 0;
        }
        
        .j-hero p {
            font-size: 1.2rem;
            color: var(--j-text-secondary);
            margin: 20px 0 32px;
        }
        
        .j-hero-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 48px;
        }
        
        .j-hero-stats {
            display: flex;
            justify-content: center;
            gap: 48px;
        }
        
        .j-stat {
            text-align: center;
        }
        
        .j-stat-value {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--j-primary), var(--j-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .j-stat-label {
            color: var(--j-text-secondary);
            font-size: 0.95rem;
            margin-top: 4px;
        }
        
        .j-features, .j-cards-showcase {
            margin: 48px 0;
        }
        
        .j-features h2, .j-cards-showcase h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 24px;
        }
        
        .j-card-highlight {
            border-color: rgba(99, 102, 241, 0.3);
        }
        
        .j-footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--j-border);
        }
        
        .j-footer-content div:first-child {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .j-footer-content .j-icon {
            width: 24px;
            height: 24px;
            color: var(--j-primary);
        }
        
        .j-footer-links {
            display: flex;
            gap: 24px;
        }
        
        .j-footer-links a {
            color: var(--j-text-secondary);
            text-decoration: none;
            transition: var(--j-transition);
        }
        
        .j-footer-links a:hover {
            color: var(--j-primary);
        }
        
        @media (max-width: 768px) {
            .j-nav-links {
                display: none;
            }
            
            .j-hero h1 {
                font-size: 2.2rem;
            }
            
            .j-hero-stats {
                flex-direction: column;
                gap: 24px;
            }
        }
    </style>
</body>
</html>
?>