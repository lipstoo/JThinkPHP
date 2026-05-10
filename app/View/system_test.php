<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- 引入框架全局设计系统 -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/jthink.css">
    <style>
        /* 针对测试页面的微调 */
        body {
            font-family: 'Outfit', sans-serif;
        }
        .j-header h1 {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -1px;
        }
        .test-grid {
            margin-top: 20px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .status-error {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body class="j-bg">
    <div class="j-container">
        <header class="j-header">
            <h1>JThinkPHP 健康诊断</h1>
            <p>自动化测试框架核心组件运行状态</p>
        </header>

        <main class="j-main">
            <div class="j-grid test-grid">
                <?php foreach ($results as $module => $test): ?>
                <div class="j-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; text-transform: capitalize;"><?= $module ?> 模块</h3>
                        <span class="status-badge status-<?= $test['status'] ?>">
                            <?= $test['status'] === 'success' ? '✓ Passed' : '✕ Failed' ?>
                        </span>
                    </div>
                    <p><?= $test['message'] ?></p>
                    
                    <?php if ($test['status'] === 'success'): ?>
                        <div class="j-progress" style="margin-top: 15px;">
                            <div class="j-progress-bar" style="width: 100%;"></div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </main>

        <footer class="j-footer">
            <div class="j-divider"></div>
            <p>&copy; <?= date('Y') ?> JThinkPHP Framework. Powered by Antigravity Engine.</p>
        </footer>
    </div>
</body>
</html>
