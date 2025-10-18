const tg = window.Telegram.WebApp;
tg.expand();

const userId = tg.initDataUnsafe?.user?.id;
const userPhoto = tg.initDataUnsafe?.user?.photo_url;
const username = tg.initDataUnsafe?.user?.username;

if (userPhoto) {
    document.getElementById('userPhoto').src = userPhoto;
}

if (username) {
    document.getElementById('username').textContent = username;
}

let allOperations = [];
let currentFilter = 'all';
let currentPeriod = '30days';
let currentPage = 1;
let isLoading = false;
let hasMore = false;
let chart;

function initializeControls() {
    if (typeof initOperationEditor === 'function') {
        initOperationEditor(tg);
    }

    document.getElementById('period-select').addEventListener('change', function (e) {
        currentPeriod = e.target.value;
        currentPage = 1;
        reloadAllData();
    });

    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterOperations(btn.dataset.filter);
        });
    });
}

function filterOperations(filterType) {
    currentFilter = filterType;

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === filterType);
    });

    const filteredOperations = filterType === 'all'
        ? allOperations
        : allOperations.filter(op => op.type === filterType);

    renderOperations(filteredOperations);
}

function renderOperations(operations) {
    const operationsContainer = document.getElementById('operations');

    if (operations.length === 0) {
        let message = '';
        if (currentFilter === 'all') {
            message = window.i18n.no_operations;
        } else if (currentFilter === 'income') {
            message = window.i18n.no_income_operations;
        } else {
            message = window.i18n.no_expense_operations;
        }

        operationsContainer.innerHTML = `
            <div class="empty-state">
                <div class="icon">📊</div>
                <p>${message}</p>
            </div>
        `;
        return;
    }

    operationsContainer.innerHTML = operations.map(op => {
        const isIncome = op.type === 'income';
        const typeClass = isIncome ? 'op-income' : 'op-expense';
        const formattedAmount = new Intl.NumberFormat('ru-RU').format(op.amount);

        const isStandard = isStandardCategory(op.type, op.category);

        return `
            <div class="operation editable"
                 data-operation-id="${op.id}"
                 onclick="openEditModal(${JSON.stringify(op).replace(/"/g, '&quot;')})">
                <div class="op-icon ${typeClass}">
                    ${isIncome ? '+' : '−'}
                </div>
                <div class="op-details">
                    <div class="op-category">
                        ${op.category}
                        ${isStandard ? `<span class="standard-badge">${window.i18n.standard_badge}</span>` : ''}
                    </div>
                    <div class="op-date">${op.occurred_at || ''}</div>
                    ${op.description ? `<div class="op-description">${op.description}</div>` : ''}
                </div>
                <div class="op-amount ${isIncome ? 'income' : 'expense'}">
                    ${isIncome ? '+' : '−'}${formattedAmount} ${op.currency}
                </div>
                <button class="delete-btn" onclick="event.stopPropagation(); deleteOperation('${op.id}', this.parentElement)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                    </svg>
                </button>
            </div>
        `;
    }).join('');
}

function isStandardCategory(type, category) {
    const standardCategories = {
        income: ['Зарплата', 'Фриланс', 'Инвестиции', 'Подарки', 'Возврат долга', 'Прочие доходы'],
        expense: ['Продукты', 'Транспорт', 'Жилье', 'Развлечения', 'Здоровье', 'Одежда', 'Образование', 'Путешествия', 'Рестораны', 'Прочие расходы']
    };

    return standardCategories[type]?.includes(category) || false;
}
async function loadMoreOperations() {
    if (isLoading || !hasMore) return;

    try {
        isLoading = true;

        const data = await loadData(currentPage + 1, currentPeriod);

        if (data.operations && data.operations.length > 0) {
            allOperations = [...allOperations, ...data.operations];
            currentPage++;
            hasMore = data.pagination?.has_more || false;

            filterOperations(currentFilter);
        }

    } catch (error) {
        console.error('Ошибка загрузки дополнительных операций:', error);
        showError(window.i18n.load_error || 'Ошибка загрузки операций');
    } finally {
        isLoading = false;
    }
}

async function loadData(page = 1, period = '30days') {
    const dataRes = await fetch('/miniapp/dashboard/data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            initData: tg.initData,
            period: period,
            page: page
        })
    });

    return await dataRes.json();
}

async function reloadAllData() {
    try {
        isLoading = true;
        showLoading();

        const data = await loadData(1, currentPeriod);

        processData(data);

    } catch (error) {
        console.error('Ошибка перезагрузки данных:', error);
        showError(window.i18n.load_error || 'Ошибка загрузки данных');
    } finally {
        isLoading = false;
        hideLoading();
    }
}

function processData(data) {
    allOperations = data.operations || [];
    currentPage = 1;
    hasMore = data.pagination?.has_more || false;

    if (chart && !data.emptyOperations) {
        updateChart(data.categories);
    } else if (data.emptyOperations) {
        document.getElementById('chart').style.display = 'none';
        document.getElementById('legend').innerHTML = `<p class="message">${data.messageOperations}</p>`;
    }

    updateSubscription(data.subscription);

    filterOperations(currentFilter);
}

function updateChart(categories) {
    document.getElementById('chart').style.display = 'block';
    chart.data.labels = Object.keys(categories);
    chart.data.datasets[0].data = Object.values(categories);
    chart.update();
    updateLegend(categories);
}

function updateSubscription(sub) {
    let subscription = '';
    switch (sub.status) {
        case 'trial':
            subscription = `<h3>${window.i18n.trial}</h3><p>${window.i18n.trial_until.replace(':date', sub.trial_ends_at)}</p>`;
            break;
        case 'active':
            subscription = `<h3>${window.i18n.active}</h3><p>${window.i18n.active_until.replace(':date', sub.subscription_ends_at)}</p>`;
            break;
        case 'expired':
            subscription = `<h3>${window.i18n.expired}</h3><a class="pay-btn" id="pay-btn">${window.i18n.pay_again}</a>`;
            break;
        default:
            subscription = `<h3>${window.i18n.no_subscription}</h3><a class="pay-btn" id="pay-btn">${window.i18n.pay}</a>`;
    }

    document.getElementById('subStatus').innerHTML = subscription;

    const payBtn = document.getElementById('pay-btn');
    if (payBtn) {
        payBtn.addEventListener('click', function () {
            window.location.href = "/miniapp/tarifs";
            setTimeout(() => {
                if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
                    Telegram.WebApp.close();
                } else if (typeof tg !== 'undefined' && tg.WebApp) {
                    tg.WebApp.close();
                } else {
                    window.close();
                }
            }, 1000);
        });
    }
}

function updateLegend(categories) {
    const legend = document.getElementById('legend');
    legend.innerHTML = '';

    const labels = Object.keys(categories);
    const colors = ['#f66', '#6f6', '#66f', '#fc6', '#6cf', '#c6f', '#ff6', '#6ff', '#f6c', '#9f6'];

    labels.forEach((label, i) => {
        const item = document.createElement('div');
        item.classList.add('legend-item');
        item.innerHTML = `
            <span class="legend-color" style="background:${colors[i]}"></span>
            ${label}
        `;
        item.addEventListener('click', () => {
            const hidden = chart.getDataVisibility(i) === false;
            chart.toggleDataVisibility(i);
            chart.update();
            item.classList.toggle('disabled', !hidden);
        });
        legend.appendChild(item);
    });
}

async function deleteOperation(operationId, operationElement) {
    try {
        const result = await Swal.fire({
            title: window.i18n.delete_confirm_title,
            text: window.i18n.delete_confirm_text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#6c757d',
            confirmButtonText: window.i18n.delete_confirm_yes,
            cancelButtonText: window.i18n.delete_confirm_no
        });

        if (result.isConfirmed) {
            operationElement.classList.add('removing');

            const deleteRes = await fetch('/miniapp/operations/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    operationId: operationId,
                    initData: tg.initData
                })
            });

            const deleteResult = await deleteRes.json();

            if (deleteResult.success) {
                await reloadAllData();

                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: window.i18n.delete_success,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'app-toast',
                        title: 'app-toast-title',
                        timerProgressBar: 'app-toast-progress'
                    }
                });
            } else {
                operationElement.classList.remove('removing');
                throw new Error(deleteResult.message || 'Ошибка удаления');
            }
        }
    } catch (error) {
        console.error('Ошибка удаления:', error);
        operationElement.classList.remove('removing');

        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'error',
            title: window.i18n.delete_error,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'app-toast warning',
                title: 'app-toast-title',
                timerProgressBar: 'app-toast-progress'
            }
        });
    }
}

function showLoading() {
    document.getElementById('operations').innerHTML = `
        <div class="loading-state">
            <div class="loading-spinner"></div>
            <p>${window.i18n.loading || 'Загрузка...'}</p>
        </div>
    `;
}

function hideLoading() {
}

function showError(message) {
    Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon: 'error',
        title: message,
        showConfirmButton: false,
        timer: 3000
    });
}

window.reloadAllData = reloadAllData;

(async () => {
    try {
        initializeControls();

        const data = await loadData(1, currentPeriod);

        const ctx = document.getElementById('chart').getContext('2d');
        const colors = ['#f66', '#6f6', '#66f', '#fc6', '#6cf', '#c6f', '#ff6', '#6ff', '#f6c', '#9f6'];

        chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: colors
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
            }
        });

        processData(data);

    } catch (error) {
        console.error('Ошибка инициализации:', error);
        showError(window.i18n.load_error || 'Ошибка загрузки данных');
    }
})();
