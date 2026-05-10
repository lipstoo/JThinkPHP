<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="/public/css/jthink.css">
</head>
<body>
    <div class="j-container">
        <header class="j-header">
            <h1><?= $title ?></h1>
            <p><?= $desc ?></p>
        </header>
        
        <main class="j-main">
            <div class="j-grid">
                <div class="j-card">
                    <div class="j-card-icon">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                    <h3>简洁路由</h3>
                    <p>极简URL映射，快速开发</p>
                </div>
                
                <div class="j-card">
                    <div class="j-card-icon">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                    </div>
                    <h3>响应式设计</h3>
                    <p>适配各种设备尺寸</p>
                </div>
                
                <div class="j-card">
                    <div class="j-card-icon">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3>高性能</h3>
                    <p>轻量级架构，快速响应</p>
                </div>
            </div>
        </main>
        
        <footer class="j-footer">
            <p>&copy; 2024 JThinkPHP. All rights reserved.</p>
        </footer>
    </div>
    <script src="/public/js/jthink.js"></script>
</body>
</html>
?>