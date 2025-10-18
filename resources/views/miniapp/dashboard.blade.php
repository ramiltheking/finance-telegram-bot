<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Finance MiniApp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="/js/checkFromTelegram.js"></script>
    <script src="/js/editOperation.js"></script>
</head>

<body>
    <header class="header">
        <div class="subscription-info" id="subStatus">

        </div>

        <a href="{{ route('miniapp.profile') }}" class="profile" id="userProfile">
            <img src="" alt="avatar" id="userPhoto" class="avatar">
            <span id="username" class="username"></span>
        </a>
    </header>

    <main class="main">
        <div class="card">
            <h3>{{ __('dashboard.stats') }}</h3>
            <canvas id="chart"></canvas>
            <div id="legend" class="legend"></div>
        </div>

        <div class="card">
            <div class="operations-header">
                <h3>{{ __('dashboard.operations') }}</h3>
                <div class="operations-controls">
                    <div class="period-selector">
                        <label class="period-label">{{ __('dashboard.period') }}:</label>
                        <select id="period-select" class="period-select">
                            <option value="7days">{{ __('dashboard.period_7days') }}</option>
                            <option value="30days" selected>{{ __('dashboard.period_30days') }}</option>
                            <option value="90days">{{ __('dashboard.period_90days') }}</option>
                            <option value="all">{{ __('dashboard.period_all') }}</option>
                        </select>
                    </div>
                    <div class="operations-filter">
                        <button class="filter-btn active" data-filter="all">{{ __('dashboard.all') }}</button>
                        <button class="filter-btn" data-filter="income">{{ __('dashboard.income') }}</button>
                        <button class="filter-btn" data-filter="expense">{{ __('dashboard.expense') }}</button>
                    </div>
                </div>
            </div>
            <div class="operations-list" id="operations"></div>
        </div>

        <div class="card">
            <h3>{{ __('dashboard.export') }}</h3>
            <div class="export-links">
                <a href="/miniapp/export/xlsx">Excel</a>
                <a href="/miniapp/export/pdf">PDF</a>
                <a href="/miniapp/export/docx">Word</a>
            </div>
        </div>
    </main>

    <div id="editOperationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">{{ __('dashboard.edit_operation') }}</h3>
                <span class="close">&times;</span>
            </div>
            <form id="editOperationForm" class="modal-form">
                <input type="hidden" id="editOperationId">
                <input type="hidden" id="editOperationType">

                <div class="form-group">
                    <label for="editAmount">{{ __('dashboard.amount') }} *</label>
                    <input type="number" id="editAmount" name="amount" step="0.01" min="0.01" required>
                    <div class="error-message" id="amountError"></div>
                </div>

                <div class="form-group">
                    <label for="editCategory">{{ __('dashboard.category') }} *</label>
                    <select id="editCategory" name="category" required>
                        <option value="">{{ __('dashboard.choose_category') }}</option>
                    </select>
                    <div class="error-message" id="categoryError"></div>
                </div>

                <div class="form-group" id="customCategoryGroup" style="display: none;">
                    <label for="editCustomCategory">{{ __('dashboard.new_category') }} *</label>
                    <input type="text" id="editCustomCategory" name="custom_category" maxlength="50">
                    <div class="error-message" id="customCategoryError"></div>
                </div>

                <div class="form-group">
                    <label for="editDescription">{{ __('dashboard.description') }}</label>
                    <textarea id="editDescription" name="description" rows="3" maxlength="255"
                              placeholder="Необязательное описание операции"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelEdit">{{ __('dashboard.cancel') }}</button>
                    <button type="submit" class="btn-save" id="saveEdit">{{ __('dashboard.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'app-toast',
                        title: 'app-toast-title',
                        timerProgressBar: 'app-toast-progress'
                    }
                });
            });
        </script>
    @elseif (session('fail'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'warning',
                    title: "{{ session('fail') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'app-toast warning',
                        title: 'app-toast-title',
                        timerProgressBar: 'app-toast-progress'
                    }
                });
            });
        </script>
    @endif

    <script>
        window.i18n = @json(__('dashboard'));
    </script>
    <script src="/js/dashboard.js"></script>
</body>

</html>
