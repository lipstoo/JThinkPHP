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
        <header class="j-header">
            <h1>UI Components</h1>
            <p>Glassmorphism Design System</p>
        </header>

        <main class="j-main">
            <section class="j-section">
                <h2 class="j-section-title">Buttons</h2>
                <div class="j-card">
                    <div class="j-demo">
                        <button class="j-btn j-btn-primary">Primary</button>
                        <button class="j-btn j-btn-secondary">Secondary</button>
                        <button class="j-btn j-btn-outline">Outline</button>
                        <button class="j-btn j-btn-primary" disabled>Disabled</button>
                        <br><br>
                        <button class="j-btn j-btn-primary j-btn-sm">Small</button>
                        <button class="j-btn j-btn-primary">Normal</button>
                        <button class="j-btn j-btn-primary j-btn-lg">Large</button>
                    </div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Icons</h2>
                <div class="j-card">
                    <div class="j-demo j-icon-grid">
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-home"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-user"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-settings"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-search"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-mail"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-lock"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-edit"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-trash"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-plus"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-check"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-info"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-alert"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-success"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-warning"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-arrow-right"/>
                        </svg>
                        <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <use href="/public/svg/icons.svg#icon-download"/>
                        </svg>
                    </div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Form Elements</h2>
                <div class="j-card">
                    <div class="j-form-group">
                        <label class="j-label">Input Field</label>
                        <input type="text" class="j-input" placeholder="Enter text...">
                    </div>
                    <div class="j-form-group">
                        <label class="j-label">Select Menu</label>
                        <select class="j-select">
                            <option>Option 1</option>
                            <option>Option 2</option>
                            <option>Option 3</option>
                        </select>
                    </div>
                    <div class="j-form-group">
                        <label class="j-label">Textarea</label>
                        <textarea class="j-textarea" placeholder="Enter your message..."></textarea>
                    </div>
                    <div class="j-form-group">
                        <label class="j-label">
                            <input type="checkbox" class="j-checkbox" checked> Checkbox
                        </label>
                    </div>
                    <div class="j-form-group">
                        <label class="j-label">
                            <input type="radio" class="j-radio" name="radio" checked> Option A
                        </label>
                        <label class="j-label">
                            <input type="radio" class="j-radio" name="radio"> Option B
                        </label>
                    </div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Cards</h2>
                <div class="j-grid">
                    <div class="j-card">
                        <h3>Default Card</h3>
                        <p>This is a glassmorphism card with floating effect.</p>
                    </div>
                    <div class="j-card">
                        <div class="j-card-icon">
                            <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <use href="/public/svg/icons.svg#icon-star"/>
                            </svg>
                        </div>
                        <h3>Icon Card</h3>
                        <p>Card with icon and gradient background.</p>
                    </div>
                    <div class="j-card">
                        <h3>Interactive Card</h3>
                        <p>Hover to see the glassmorphism effect.</p>
                        <button class="j-btn j-btn-primary j-btn-sm" style="margin-top:12px;">Click Me</button>
                    </div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Badges</h2>
                <div class="j-card">
                    <span class="j-badge j-badge-primary">Primary</span>
                    <span class="j-badge j-badge-success">Success</span>
                    <span class="j-badge j-badge-warning">Warning</span>
                    <span class="j-badge j-badge-danger">Danger</span>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Avatars</h2>
                <div class="j-card">
                    <div class="j-avatar j-avatar-sm">JD</div>
                    <div class="j-avatar">JD</div>
                    <div class="j-avatar j-avatar-lg">JD</div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Progress</h2>
                <div class="j-card">
                    <div class="j-progress">
                        <div class="j-progress-bar" style="width: 75%"></div>
                    </div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Alerts</h2>
                <div class="j-alert j-alert-info">
                    <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <use href="/public/svg/icons.svg#icon-info"/>
                    </svg>
                    <div>Info alert message. This is an informational notification.</div>
                </div>
                <div class="j-alert j-alert-success">
                    <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <use href="/public/svg/icons.svg#icon-success"/>
                    </svg>
                    <div>Success alert message. Operation completed successfully.</div>
                </div>
                <div class="j-alert j-alert-warning">
                    <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <use href="/public/svg/icons.svg#icon-warning"/>
                    </svg>
                    <div>Warning alert message. Please review your input.</div>
                </div>
                <div class="j-alert j-alert-danger">
                    <svg class="j-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <use href="/public/svg/icons.svg#icon-error"/>
                    </svg>
                    <div>Danger alert message. Something went wrong.</div>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">List</h2>
                <div class="j-card">
                    <ul class="j-list">
                        <li class="j-list-item active">Active Item</li>
                        <li class="j-list-item">Second Item</li>
                        <li class="j-list-item">Third Item</li>
                        <li class="j-list-item">Fourth Item</li>
                    </ul>
                </div>
            </section>

            <section class="j-section">
                <h2 class="j-section-title">Modal</h2>
                <div class="j-card">
                    <button class="j-btn j-btn-primary" onclick="JThink.modal({title:'Demo Modal', content:'This is a glassmorphism modal dialog.'})">
                        Open Modal
                    </button>
                </div>
            </section>
        </main>

        <footer class="j-footer">
            <p>&copy; 2024 JThinkPHP. All rights reserved.</p>
        </footer>
    </div>

    <script src="/public/js/jthink.js"></script>
    <style>
        .j-section {
            margin-bottom: 32px;
        }
        
        .j-section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--j-text);
        }
        
        .j-demo {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        
        .j-icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
            gap: 24px;
            padding: 16px 0;
        }
        
        .j-icon-grid .j-icon {
            width: 48px;
            height: 48px;
            color: var(--j-text-secondary);
            transition: var(--j-transition);
        }
        
        .j-icon-grid .j-icon:hover {
            color: var(--j-primary);
            transform: scale(1.2);
        }
        
        .j-card .j-avatar {
            display: inline-flex;
            margin-right: 12px;
        }
        
        .j-card .j-progress {
            margin-top: 8px;
        }
        
        .j-alert .j-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
    </style>
</body>
</html>
?>