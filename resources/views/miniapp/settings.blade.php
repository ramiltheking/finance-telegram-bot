<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Настройки</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/settings.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #1cc88a;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #343a40;
            --text: #444;
            --border-radius: 12px;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--light);
            color: var(--text);
            margin: 0;
            padding: 20px;
        }

        .header {
            max-width: 1160px;
            width: 95%;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .header-title {
            font-size: 24px;
        }

        .prev-btn {
            position: absolute;
            left: 0px;
            top: 5px;
            padding: 10px 20px;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .prev-btn:hover {
            transform: translate(-5px, 0);
            opacity: 0.8;
        }

        .main {
            flex: 1 1 auto;
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
        }

        .card {
            background: #fff;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .buttons__group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .buttons__group button {
            flex: 1;
            min-width: 80px;
            font-weight: 600;
            background: var(--light);
            border: 2px solid var(--primary);
            border-radius: 8px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 15px;
            color: var(--primary);
            transition: all 0.2s ease;
        }

        .buttons__group button:hover {
            background: var(--primary);
            color: #fff;
        }

        .buttons__group button.active {
            background: var(--primary);
            color: #fff;
            font-weight: bold;
        }

        #userTimezone {
            font-size: 16px;
            font-weight: 500;
            color: var(--dark);
        }

        #detectTimezone {
            background: var(--secondary);
            border: none;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        #detectTimezone:hover {
            background: #17a673;
        }

        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-input {
            display: none;
        }

        .toggle-label {
            position: relative;
            display: inline-block;
            min-width: 48px;
            height: 26px;
            background: #ccc;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .toggle-label::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .toggle-input:checked+.toggle-label {
            background: var(--primary);
        }

        .toggle-input:checked+.toggle-label::after {
            transform: translateX(22px);
        }

        .toggle-text {
            font-size: 15px;
            color: var(--dark);
        }

        .time-settings {
            margin-top: 12px;
            display: flex;
            gap: 10px;
            align-items: center;
            font-size: 14px;
            margin-left: 58px;
        }

        .time-settings label {
            display: flex;
            flex-direction: column;
            font-weight: 500;
            color: #333;
        }

        .time-settings select {
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .hidden {
            display: none;
        }

        .subscription-card {
            border-left: 4px solid var(--primary);
        }

        .subscription-info {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--dark);
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--primary);
        }

        .recurring-toggle {
            margin: 15px 0;
        }

        .recurring-info {
            margin-top: 15px;
            padding: 15px;
            background: var(--light);
            border-radius: 8px;
            border-left: 4px solid var(--secondary);
        }

        .info-note {
            margin: 0 0 12px 0;
            font-size: 14px;
            color: var(--dark);
        }

        .manage-btn,
        .subscribe-btn {
            width: 90%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .manage-btn {
            background: var(--primary);
            color: white;
        }

        .manage-btn:hover {
            background: #3a5fcd;
        }

        .subscribe-btn {
            background: var(--secondary);
            color: white;
        }

        .subscribe-btn:hover {
            background: #17a673;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: var(--secondary);
            color: white;
        }

        .status-trial {
            background: #FF9800;
            color: white;
        }

        .status-expired {
            background: var(--danger);
            color: white;
        }

        .app-toast {
            background: rgba(28, 200, 138, 0.9);
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 12px 16px;
            margin-top: 10px;
            margin-right: 10px;
            font-size: 14px;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .warning {
            background: #ff9c47;
        }

        .app-toast-title {
            font-weight: 600;
            font-size: 14px;
            color: #fff;
        }

        .app-toast-progress {
            background: #fff !important;
            opacity: 0.8;
            height: 3px;
            border-radius: 0 0 12px 12px;
        }

        .categories-section {
            margin-bottom: 25px;
        }

        .categories-section h4 {
            margin: 0 0 12px 0;
            color: var(--dark);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .category-item {
            gap: 10px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 12px 15px;
            background: var(--light);
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.2s ease;
        }

        .category-item:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 6px rgba(78, 115, 223, 0.1);
        }

        .category-info {
            flex: 1;
        }

        .category-name {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 4px;
            word-break: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }

        .category-description {
            font-size: 12px;
            color: #666;
            font-style: italic;
            word-break: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }

        .category-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
            position: relative;
            z-index: 1;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
        }

        .btn-edit:hover {
            background: #3a5fcd;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #d32f2f;
        }

        .add-category-btn {
            width: 100%;
            padding: 12px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }

        .add-category-btn:hover {
            background: #17a673;
        }

        .empty-categories {
            text-align: center;
            padding: 30px 20px;
            color: var(--dark);
            font-style: italic;
        }

        .empty-categories p {
            margin: 0;
            opacity: 0.7;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
            pointer-events: auto;
        }

        .modal {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal h3 {
            margin: 0 0 20px 0;
            color: var(--primary);
            word-break: break-word;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-select,
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s ease;
            font-family: inherit;
            background: white;
            color: var(--dark);
            box-sizing: border-box;
            max-width: 100%;
        }

        .form-select:focus,
        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            outline: 2px solid transparent;
        }

        .form-textarea {
            resize: vertical;
            min-height: 60px;
            max-height: 200px;
        }

        .form-input:invalid,
        .form-textarea:invalid {
            border-color: var(--danger);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .modal-actions button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            z-index: 1;
        }

        .btn-save {
            background: var(--primary);
            color: white;
        }

        .btn-save:hover {
            background: #3a5fcd;
        }

        .btn-cancel {
            background: var(--light);
            color: var(--dark);
            border: 2px solid #e0e0e0;
        }

        .btn-cancel:hover {
            background: #e9ecef;
        }

        .hidden {
            display: none !important;
        }

        * {
            box-sizing: border-box;
        }

        .category-name,
        .category-description,
        .modal h3 {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-overlay {
            rel: "noopener noreferrer";
        }

        .modal {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .form-input,
        .form-textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        .form-input[autocomplete="off"] {
            -webkit-autocomplete: off;
            -moz-autocomplete: off;
            -ms-autocomplete: off;
            autocomplete: off;
        }
    </style>
    <script src="/js/checkFromTelegram.js"></script>
</head>

<body>
    <header class="header">
        <a href="{{ route('miniapp.profile') }}" class="prev-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
            </svg>
        </a>
        <div class="header-title">
            <strong>{{ __('settings.title') }}</strong>
        </div>
    </header>

    <main class="main">
        <div class="card subscription-card">
            <h3>{{ __('settings.subscription_management') }}</h3>

            <div class="subscription-info">
                <div class="info-row">
                    <span class="info-label">{{ __('settings.status') }}</span>
                    <span class="info-value" id="subscriptionStatus">{{ __('settings.status_loading') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('settings.subscription_until') }}</span>
                    <span class="info-value" id="subscriptionEnds">{{ __('settings.status_loading') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('settings.next_payment') }}</span>
                    <span class="info-value" id="nextPayment">{{ __('settings.status_loading') }}</span>
                </div>
            </div>

            <div class="toggle-wrapper recurring-toggle">
                <input type="checkbox" id="recurringToggle" class="toggle-input">
                <label for="recurringToggle" class="toggle-label"></label>
                <span class="toggle-text">{{ __('settings.auto_renewal') }}</span>
            </div>

            <div id="recurringInfo" class="recurring-info hidden">
                <p class="info-note">{{ __('settings.auto_renewal_enabled') }}</p>
                <button id="manageSubscription" class="manage-btn">{{ __('settings.manage_subscription') }}</button>
            </div>

            <div id="recurringDisabled" class="recurring-info">
                <p class="info-note">{{ __('settings.auto_renewal_disabled') }}</p>
                <a class="subscribe-btn" id="pay-btn">{{ __('settings.subscribe') }}</a>
            </div>
        </div>

        <div class="card currency-card">
            <h3>{{ __('settings.currency') }}</h3>
            <div class="buttons__group">
                <button data-currency="KZT">KZT</button>
                <button data-currency="RUB">RUB</button>
                <button data-currency="USD">USD</button>
                <button data-currency="EUR">EUR</button>
            </div>
        </div>

        <div class="card language-card">
            <h3>{{ __('settings.language') }}</h3>
            <div class="buttons__group">
                <button data-lang="ru">Русский</button>
                <button data-lang="en">English</button>
                {{-- <button data-lang="kz">Қазақша</button> --}}
            </div>
        </div>

        <div class="card custom-categories">
            <h3>{{ __('settings.my_categories') }}</h3>

            <div class="categories-section">
                <h4>{{ __('settings.income') }}</h4>
                <div class="category-list" id="incomeCategories">
                    <div class="empty-categories">
                        <p>{{ __('settings.no_income_categories') }}</p>
                    </div>
                </div>
            </div>

            <div class="categories-section">
                <h4>{{ __('settings.expense') }}</h4>
                <div class="category-list" id="expenseCategories">
                    <div class="empty-categories">
                        <p>{{ __('settings.no_expense_categories') }}</p>
                    </div>
                </div>
            </div>

            <button class="add-category-btn" onclick="showCategoryModal()">
                <span>+</span> {{ __('settings.add_category') }}
            </button>
        </div>

        <div id="categoryModal" class="modal-overlay hidden">
            <div class="modal">
                <h3 id="modalTitle">{{ __('settings.add_category') }}</h3>

                <div class="form-group">
                    <label for="categoryType">{{ __('settings.category_type') }}</label>
                    <select id="categoryType" class="form-select">
                        <option value="EXPENSE">{{ __('settings.expense') }}</option>
                        <option value="INCOME">{{ __('settings.income') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="categoryName">{{ __('settings.category_name') }} *</label>
                    <input type="text" id="categoryName" class="form-input"
                        placeholder="{{ __('settings.category_name_placeholder') }}"
                        maxlength="50" required>
                    <small style="color: #666; font-size: 12px;">{{ __('settings.category_name_hint') }}</small>
                </div>

                <div class="form-group">
                    <label for="categoryTitle">{{ __('settings.category_description') }}</label>
                    <textarea id="categoryTitle" class="form-textarea"
                            placeholder="{{ __('settings.category_description_placeholder') }}"
                            maxlength="255" rows="3"></textarea>
                    <small style="color: #666; font-size: 12px;">{{ __('settings.category_description_hint') }}</small>
                </div>

                <div class="modal-actions">
                    <button class="btn-save" onclick="saveCategory()">💾 {{ __('settings.save') }}</button>
                    <button class="btn-cancel" onclick="closeCategoryModal()">❌ {{ __('settings.cancel') }}</button>
                </div>
            </div>
        </div>

        <div class="card timezone-card">
            <h3>{{ __('settings.timezone') }}</h3>
            <strong id="userTimezone"></strong><br><br>
            <button id="detectTimezone">{{ __('settings.detect_timezone') }}</button>
        </div>

        <div class="card reminders-card">
            <h3>{{ __('settings.reminders') }}</h3>
            <div class="toggle-wrapper">
                <input type="checkbox" id="reminderToggle" class="toggle-input">
                <label for="reminderToggle" class="toggle-label"></label>
                <span class="toggle-text">{{ __('settings.enable_reminders') }}</span>
            </div>
            <div id="reminderTime" class="time-settings hidden">
                <label>
                    <select id="reminderHour"></select>
                </label>

                <label>
                    <select id="reminderMinute"></select>
                </label>
            </div>
        </div>
    </main>

    <script>
        window.i18n = @json(__('settings'));
        window.userSettings = @json($settings);
        window.subscriptionInfo = @json($subscriptionInfo ?? []);

        const tg = window.Telegram.WebApp;
        tg.expand();

        const rootStyles = getComputedStyle(document.documentElement);
        const primaryColor = rootStyles.getPropertyValue('--primary').trim();
        const dangerColor = rootStyles.getPropertyValue('--danger').trim();

        function getUtcOffset() {
            const offsetMinutes = new Date().getTimezoneOffset();
            const offsetHours = -(offsetMinutes / 60);
            return "UTC" + (offsetHours >= 0 ? "+" + offsetHours : offsetHours);
        }

        async function saveSetting(key, value) {
            try {
                const response = await fetch('/miniapp/settings/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        initData: tg.initData,
                        key,
                        value
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    console.error('Error saving setting:', data);
                    throw new Error(data.error || 'Failed to save setting');
                }

                return data;
            } catch (error) {
                console.error('Error saving setting:', error);
                throw error;
            }
        }

        function showToast(message, type = 'success') {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: type === 'error' ? 'app-toast warning' : 'app-toast',
                    title: 'app-toast-title',
                    timerProgressBar: 'app-toast-progress'
                }
            });
        }

        function sanitizeHTML(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }

        function validateInput(input, maxLength = 255, allowEmpty = false) {
            if (!input && !allowEmpty) return false;
            if (input === null || input === undefined) return allowEmpty;

            const str = String(input).trim();
            if (!allowEmpty && str.length === 0) return false;
            if (str.length > maxLength) return false;

            const allowedPattern = /^[a-zA-Zа-яА-ЯёЁ0-9\s]*$/;

            return allowedPattern.test(str);
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        let categories = [];
        let editingCategoryId = null;

        async function loadCategories() {
            try {
                const response = await fetch('/miniapp/categories', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    categories = Array.isArray(data.categories) ? data.categories : [];
                    renderCategories();
                } else {
                    console.error('Error loading categories:', data.error);
                    showToast(window.i18n.load_categories_error || 'Ошибка загрузки категорий', 'error');
                }
            } catch (error) {
                console.error('Error loading categories:', error);
                showToast(window.i18n.load_categories_error || 'Ошибка загрузки категорий', 'error');
            }
        }

        function renderCategories() {
            const incomeContainer = document.getElementById('incomeCategories');
            const expenseContainer = document.getElementById('expenseCategories');

            if (!incomeContainer || !expenseContainer) {
                console.error('Category containers not found');
                return;
            }

            const incomeCategories = categories.filter(cat => cat && cat.type === 'INCOME');
            const expenseCategories = categories.filter(cat => cat && cat.type === 'EXPENSE');

            if (incomeCategories.length > 0) {
                incomeContainer.innerHTML = incomeCategories.map(category => {
                    if (!category || !category.id) return '';

                    const safeName = sanitizeHTML(category.name);
                    const safeDescription = category.title ? sanitizeHTML(category.title) : '';
                    const safeId = escapeHtml(category.id.toString());

                    return `
                        <div class="category-item" data-category-id="${safeId}">
                            <div class="category-info">
                                <div class="category-name">${safeName}</div>
                                ${safeDescription ? `<div class="category-description">${safeDescription}</div>` : ''}
                            </div>
                            <div class="category-actions">
                                <button class="btn-edit" onclick="editCategory(${safeId})" aria-label="${window.i18n.edit_category || 'Редактировать категорию'}">✏️</button>
                                <button class="btn-delete" onclick="deleteCategory(${safeId})" aria-label="${window.i18n.delete_category || 'Удалить категорию'}">🗑️</button>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                incomeContainer.innerHTML =
                    `<div class="empty-categories"><p>${window.i18n.no_income_categories || 'Пока нет категорий доходов'}</p></div>`;
            }

            if (expenseCategories.length > 0) {
                expenseContainer.innerHTML = expenseCategories.map(category => {
                    if (!category || !category.id) return '';

                    const safeName = sanitizeHTML(category.name);
                    const safeDescription = category.title ? sanitizeHTML(category.title) : '';
                    const safeId = escapeHtml(category.id.toString());

                    return `
                        <div class="category-item" data-category-id="${safeId}">
                            <div class="category-info">
                                <div class="category-name">${safeName}</div>
                                ${safeDescription ? `<div class="category-description">${safeDescription}</div>` : ''}
                            </div>
                            <div class="category-actions">
                                <button class="btn-edit" onclick="editCategory(${safeId})" aria-label="${window.i18n.edit_category || 'Редактировать категорию'}">✏️</button>
                                <button class="btn-delete" onclick="deleteCategory(${safeId})" aria-label="${window.i18n.delete_category || 'Удалить категорию'}">🗑️</button>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                expenseContainer.innerHTML =
                    `<div class="empty-categories"><p>${window.i18n.no_expense_categories || 'Пока нет категорий расходов'}</p></div>`;
            }
        }

        function showCategoryModal(category = null) {
            const modal = document.getElementById('categoryModal');
            const modalTitle = document.getElementById('modalTitle');
            const typeSelect = document.getElementById('categoryType');
            const nameInput = document.getElementById('categoryName');
            const titleInput = document.getElementById('categoryTitle');

            if (!modal || !modalTitle || !typeSelect || !nameInput || !titleInput) {
                console.error('Modal elements not found');
                return;
            }

            nameInput.value = '';
            titleInput.value = '';
            typeSelect.value = 'EXPENSE';

            if (category && category.id) {
                modalTitle.textContent = window.i18n.edit_category_title || '✏️ Редактировать категорию';
                typeSelect.value = validateInput(category.type) ? category.type : 'EXPENSE';
                nameInput.value = validateInput(category.name, 50) ? category.name : '';
                titleInput.value = validateInput(category.title, 255, true) ? category.title : '';
                editingCategoryId = parseInt(category.id);

                if (isNaN(editingCategoryId)) {
                    console.error('Invalid category ID');
                    editingCategoryId = null;
                    return;
                }
            } else {
                modalTitle.textContent = window.i18n.add_category_title || '➕ Добавить категорию';
                editingCategoryId = null;
            }

            modal.classList.remove('hidden');

            setTimeout(() => {
                if (nameInput && nameInput.focus) {
                    nameInput.focus();
                }
            }, 100);
        }

        async function saveCategory() {
            const typeSelect = document.getElementById('categoryType');
            const nameInput = document.getElementById('categoryName');
            const titleInput = document.getElementById('categoryTitle');

            if (!typeSelect || !nameInput || !titleInput) {
                showToast(window.i18n.form_elements_not_found || 'Ошибка: элементы формы не найдены', 'error');
                return;
            }

            const type = typeSelect.value;
            const name = nameInput.value.trim();
            const title = titleInput.value.trim();

            if (!['INCOME', 'EXPENSE'].includes(type)) {
                showToast(window.i18n.invalid_category_type || 'Некорректный тип категории', 'error');
                return;
            }

            if (!validateInput(name, 50)) {
                showToast(window.i18n.invalid_category_name || 'Некорректное название категории', 'error');
                return;
            }

            if (title && !validateInput(title, 255, true)) {
                showToast(window.i18n.invalid_category_description || 'Некорректное описание категории', 'error');
                return;
            }

            try {
                const url = editingCategoryId && Number.isInteger(editingCategoryId) ?
                    `/miniapp/categories/${editingCategoryId}` :
                    '/miniapp/categories';

                const method = editingCategoryId ? 'PUT' : 'POST';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken && {
                            'X-CSRF-TOKEN': csrfToken
                        })
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: type,
                        name: name,
                        title: title || null
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    showToast(editingCategoryId ?
                        (window.i18n.category_updated || 'Категория обновлена') :
                        (window.i18n.category_added || 'Категория добавлена')
                    );
                    closeCategoryModal();
                    await loadCategories();
                } else {
                    if (data.error === 'category_exists') {
                        showToast(window.i18n.category_exists || 'Категория с таким названием уже существует', 'error');
                    } else {
                        showToast(data.error || (window.i18n.save_category_error || 'Ошибка сохранения категории'),
                            'error');
                    }
                }
            } catch (error) {
                console.error('Error saving category:', error);
                showToast(window.i18n.save_category_error || 'Ошибка сохранения категории', 'error');
            }
        }

        async function deleteCategory(categoryId) {
            if (!categoryId || isNaN(parseInt(categoryId))) {
                console.error('Invalid category ID');
                return;
            }

            const category = categories.find(cat => cat && cat.id === parseInt(categoryId));
            if (!category) {
                showToast(window.i18n.category_not_found || 'Категория не найдена', 'error');
                return;
            }

            const safeName = sanitizeHTML(category.name);

            const result = await Swal.fire({
                title: window.i18n.delete_category_confirm || 'Удалить категорию?',
                html: `${window.i18n.delete_category_text || 'Категория'} <strong>"${safeName}"</strong> ${window.i18n.delete_category_warning || 'будет удалена. Это действие нельзя отменить.'}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: window.i18n.delete || 'Удалить',
                cancelButtonText: window.i18n.cancel || 'Отмена',
                confirmButtonColor: dangerColor,
                cancelButtonColor: primaryColor,
                customClass: {
                    htmlContainer: 'swal-html-container'
                }
            });

            if (result.isConfirmed) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    const response = await fetch(`/miniapp/categories/${categoryId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken && {
                                'X-CSRF-TOKEN': csrfToken
                            })
                        },
                        credentials: 'same-origin'
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (data.error === 'category_in_use') {
                            Swal.fire({
                                title: window.i18n.cannot_delete_category || 'Невозможно удалить категорию',
                                html: `
                                    <div style="text-align: left;">
                                        <p>${window.i18n.category_in_use_text || 'Категория'} <strong>"${safeName}"</strong> ${window.i18n.category_in_use_warning || 'используется в существующих операциях и не может быть удалена.'}</p>
                                        <p><strong>${window.i18n.what_can_you_do || 'Что можно сделать:'}</strong></p>
                                        <ul style="margin: 10px 0; padding-left: 20px;">
                                            <li>${window.i18n.delete_or_change_operations || 'Удалите или измените операции с этой категорией'}</li>
                                            <li>${window.i18n.use_list_command || 'Используйте команду /list для просмотра операций'}</li>
                                        </ul>
                                    </div>
                                `,
                                icon: 'error',
                                confirmButtonText: window.i18n.understand || 'Понятно',
                                width: 500
                            });
                        } else {
                            throw new Error(data.error || `HTTP error! status: ${response.status}`);
                        }
                        return;
                    }

                    if (data.success) {
                        showToast(window.i18n.category_deleted || 'Категория удалена');
                        await loadCategories();
                    } else {
                        showToast(data.error || (window.i18n.delete_category_error || 'Ошибка удаления категории'),
                            'error');
                    }
                } catch (error) {
                    console.error('Error deleting category:', error);
                    showToast(window.i18n.delete_category_error || 'Ошибка удаления категории', 'error');
                }
            }
        }

        function editCategory(categoryId) {
            if (!categoryId || isNaN(parseInt(categoryId))) {
                console.error('Invalid category ID');
                return;
            }

            const category = categories.find(cat => cat && cat.id === parseInt(categoryId));
            if (category) {
                showCategoryModal(category);
            } else {
                showToast(window.i18n.category_not_found, 'error');
            }
        }

        function closeCategoryModal() {
            const modal = document.getElementById('categoryModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            editingCategoryId = null;
        }

        function setupEventListeners() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeCategoryModal();
                }
            });

            document.addEventListener('click', (e) => {
                if (e.target.id === 'categoryModal') {
                    closeCategoryModal();
                }
            });

            let isProcessing = false;

            const originalSaveCategory = window.saveCategory;
            window.saveCategory = async function() {
                if (isProcessing) return;

                isProcessing = true;
                try {
                    await originalSaveCategory();
                } finally {
                    setTimeout(() => {
                        isProcessing = false;
                    }, 1000);
                }
            };

            const originalDeleteCategory = window.deleteCategory;
            window.deleteCategory = async function(categoryId) {
                if (isProcessing) return;

                isProcessing = true;
                try {
                    await originalDeleteCategory(categoryId);
                } finally {
                    setTimeout(() => {
                        isProcessing = false;
                    }, 1000);
                }
            };
        }

        document.getElementById('detectTimezone').addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    const {
                        latitude,
                        longitude
                    } = pos.coords;

                    try {
                        const response = await fetch('/miniapp/detect-timezone', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                lat: latitude,
                                lon: longitude
                            })
                        });

                        const data = await response.json();

                        if (data.timezone) {
                            const offsetStr = getUtcOffset();
                            document.getElementById('userTimezone').textContent =
                                `${data.timezone} (${offsetStr})`;
                            await saveSetting('timezone', data.timezone);
                            showToast(window.i18n.detected_success.replace(':timezone', data.timezone)
                                .replace(':offset', offsetStr));
                        } else {
                            showToast(window.i18n.detect_failed, "error");
                        }
                    } catch (error) {
                        console.error('Error detecting timezone:', error);
                        showToast(window.i18n.detection_error, "error");
                    }
                }, (error) => {
                    showToast(window.i18n.geolocation_error, "error");
                });
            } else {
                showToast(window.i18n.geolocation_unsupported, "error");
            }
        });

        function updateSubscriptionInfo(info) {
            const statusElement = document.getElementById('subscriptionStatus');
            const statusMap = {
                'active': {
                    text: window.i18n.status_active,
                    class: 'status-active'
                },
                'trial': {
                    text: window.i18n.status_trial,
                    class: 'status-trial'
                },
                'expired': {
                    text: window.i18n.status_expired,
                    class: 'status-expired'
                },
                'cancelled': {
                    text: window.i18n.status_cancelled,
                    class: 'status-expired'
                }
            };

            const status = statusMap[info.status] || statusMap.expired;
            statusElement.innerHTML = `<span class="status-badge ${status.class}">${status.text}</span>`;

            document.getElementById('subscriptionEnds').textContent = info.subscription_ends_at || window.i18n.not_active;
            document.getElementById('nextPayment').textContent = info.next_payment_date || window.i18n.not_scheduled;

            const recurringToggle = document.getElementById('recurringToggle');
            const recurringInfo = document.getElementById('recurringInfo');
            const recurringDisabled = document.getElementById('recurringDisabled');

            if (info.has_recurring_token) {
                recurringToggle.checked = info.recurring_enabled;
                recurringInfo.classList.toggle('hidden', !info.recurring_enabled);
                recurringDisabled.classList.add('hidden');
                recurringToggle.disabled = false;
            } else {
                recurringToggle.checked = false;
                recurringToggle.disabled = true;
                recurringInfo.classList.add('hidden');
                recurringDisabled.classList.remove('hidden');
            }
        }

        async function saveRecurringSetting(enabled) {
            try {
                const response = await fetch('/miniapp/settings/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        initData: tg.initData,
                        key: 'recurring_enabled',
                        value: enabled
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        title: 'Успех!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                    loadSubscriptionInfo();
                } else {
                    if (data.requires_payment) {
                        Swal.fire({
                            title: 'Требуется платеж',
                            text: data.message,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Оформить подписку',
                            cancelButtonText: 'Отмена'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('miniapp.tarifs') }}";
                            } else {
                                document.getElementById('recurringToggle').checked = false;
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Ошибка!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        document.getElementById('recurringToggle').checked = !enabled;
                    }
                }
            } catch (error) {
                console.error('Error saving recurring setting:', error);
                Swal.fire({
                    title: 'Ошибка!',
                    text: 'Не удалось сохранить настройки',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                document.getElementById('recurringToggle').checked = !enabled;
            }
        }

        async function loadSubscriptionInfo() {
            try {
                const response = await fetch('/miniapp/settings/subscription/details', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    updateSubscriptionInfo(data.subscription);
                }
            } catch (error) {
                console.error('Error loading subscription info:', error);
            }
        }

        window.addEventListener('DOMContentLoaded', async () => {
            if (typeof fetch === 'undefined') {
                console.error('Fetch API is not supported');
                return;
            }

            document.getElementById('pay-btn').addEventListener('click', function() {
                window.location.href = "{{ route('miniapp.tarifs') }}";

                setTimeout(function() {
                    if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
                        Telegram.WebApp.close();
                    } else if (typeof tg !== 'undefined' && tg.WebApp) {
                        tg.WebApp.close();
                    } else {
                         window.close();
                    }
                }, 1000);
            });

            try {
                const settingsData = window.userSettings;

                await loadCategories();
                setupEventListeners();

                if (window.subscriptionInfo) {
                    updateSubscriptionInfo(window.subscriptionInfo);
                } else {
                    await loadSubscriptionInfo();
                }

                const recurringToggle = document.getElementById('recurringToggle');
                recurringToggle.addEventListener('change', (e) => {
                    saveRecurringSetting(e.target.checked);
                });

                const manageBtn = document.getElementById('manageSubscription');
                if (manageBtn) {
                    manageBtn.addEventListener('click', () => {
                        Swal.fire({
                            title: 'Управление подпиской',
                            html: `
                            <p>Вы можете отключить автопродление в любой момент.</p>
                            <p>При отключении автопродления подписка будет активна до конца оплаченного периода.</p>
                        `,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Отключить автопродление',
                            cancelButtonText: 'Отмена'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                saveRecurringSetting(false);
                            }
                        });
                    });
                }

                const currencyCard = document.querySelector('.currency-card');
                if (currencyCard) {
                    const currencyButtons = currencyCard.querySelectorAll('.buttons__group button');
                    currencyButtons.forEach(btn => {
                        if (btn.dataset.currency === settingsData.currency) {
                            btn.classList.add('active');
                        }
                        btn.addEventListener('click', async () => {
                            try {
                                await saveSetting('currency', btn.dataset.currency);
                                currencyButtons.forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');

                                showToast(window.i18n.currency_changed_success);
                            } catch (error) {
                                console.error('Error saving currency:', error);
                                showToast(window.i18n.currency_changed_error, 'error');
                            }
                        });
                    });
                }

                const languageCard = document.querySelector('.language-card');
                if (languageCard) {
                    const languageButtons = languageCard.querySelectorAll('.buttons__group button');
                    languageButtons.forEach(btn => {
                        if (btn.dataset.lang === settingsData.language) {
                            btn.classList.add('active');
                        }
                        btn.addEventListener('click', async () => {
                            try {
                                await saveSetting('language', btn.dataset.lang);
                                languageButtons.forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');

                                showToast(window.i18n.language_changed_success);
                                setTimeout(() => {
                                    location.reload();
                                }, 3000);
                            } catch (error) {
                                console.error('Error saving language:', error);
                                showToast(window.i18n.language_changed_error, 'error');
                            }
                        });
                    });
                }

                if (settingsData.timezone) {
                    document.getElementById('userTimezone').textContent = settingsData.timezone;
                }

                const toggle = document.getElementById('reminderToggle');
                const timeSettings = document.getElementById('reminderTime');
                const hourSelect = document.getElementById('reminderHour');
                const minuteSelect = document.getElementById('reminderMinute');

                for (let h = 0; h < 24; h++) {
                    const opt = document.createElement('option');
                    opt.value = h;
                    opt.textContent = h.toString().padStart(2, '0');
                    hourSelect.appendChild(opt);
                }

                for (let m = 0; m < 60; m += 5) {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m.toString().padStart(2, '0');
                    minuteSelect.appendChild(opt);
                }

                toggle.checked = settingsData.reminders_enabled;
                if (toggle.checked) {
                    timeSettings.classList.remove('hidden');
                }
                hourSelect.value = settingsData.reminder_hour ?? 22;
                minuteSelect.value = settingsData.reminder_minute ?? 0;

                toggle.addEventListener('change', async (e) => {
                    timeSettings.classList.toggle('hidden', !e.target.checked);
                    try {
                        await saveSetting('reminders_enabled', e.target.checked);
                    } catch (error) {
                        console.error('Error saving reminder setting:', error);
                        e.target.checked = !e.target.checked;
                        timeSettings.classList.toggle('hidden', !e.target.checked);
                    }
                });

                hourSelect.addEventListener('change', async (e) => {
                    try {
                        await saveSetting('reminder_hour', parseInt(e.target.value));
                    } catch (error) {
                        console.error('Error saving reminder hour:', error);
                    }
                });

                minuteSelect.addEventListener('change', async (e) => {
                    try {
                        await saveSetting('reminder_minute', parseInt(e.target.value));
                    } catch (error) {
                        console.error('Error saving reminder minute:', error);
                    }
                });
            } catch (error) {
                console.error('Initialization error:', error);
            }
        });

        Object.freeze(window.sanitizeHTML);
        Object.freeze(window.validateInput);
        Object.freeze(window.escapeHtml);
    </script>

</body>

</html>
