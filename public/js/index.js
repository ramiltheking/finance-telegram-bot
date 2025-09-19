const TokenCSRF = document.querySelector('meta[name="csrf-token"]').content;
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

(async () => {
    try {
        const authRes = await fetch('/miniapp/auth', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': TokenCSRF
            },
            body: JSON.stringify({
                initData: tg.initData
            })
        });

        const authData = await authRes.json();

        if (!authData.success) {
            console.error('Ошибка авторизации', authData);
            return;
        }

        console.log('Авторизация успешна');

        const dataRes = await fetch('/miniapp/data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': TokenCSRF
            },
            body: JSON.stringify({
                initData: tg.initData
            })
        });

        const data = await dataRes.json();

        const sub = data.subscription;

        let subscription = '';
        switch (sub.status) {
            case 'trial':
                subscription =
                    `<h3>⏳ Пробный период</h3> Активен до: <strong>${sub.trial_ends_at}</strong>`;
                break;
            case 'active':
                subscription =
                    `<h3>💰 Подписка</h3> Активна до: <strong>${sub.subscription_ends_at}</strong>`;
                break;
            case 'expired':
                subscription =
                    `<h3>❌ Подписка неактивна</h3><a href="/miniapp/tarifs?initData=${encodeURIComponent(tg.initData)}" class="pay-btn">Оплатить тариф</a>`;
                break;
            case 'cancelled':
                subscription = `<h3>Подписка отменена</h3>`;
                break;
            default:
                subscription =
                    `<h3>❌ Нет подписки</h3> <br><a href="/miniapp/tarifs?initData=${encodeURIComponent(tg.initData)}" class="pay-btn">Оплатить тариф</a>`;
        }

        document.getElementById('subStatus').innerHTML = subscription;

        const ctx = document.getElementById('chart').getContext('2d');
        const labels = Object.keys(data.categories);
        const values = Object.values(data.categories);
        const colors = ['#f66', '#6f6', '#66f', '#fc6', '#6cf'];

        const chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
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

        if (data.emptyOperations) {
            document.getElementById('operations').innerHTML =
                `<p class="message">${data.messageOperations}</p>`;
            document.getElementById('legend').innerHTML =
                `<p class="message">${data.messageOperations}</p>`;
        } else {
            const legend = document.getElementById('legend');
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

            document.getElementById('operations').innerHTML =
                data.operations.map(op => {
                    const isIncome = op.type === 'income';
                    return `
                        <div class="operation">
                        <div class="op-icon ${isIncome ? 'op-income' : 'op-expense'}">
                            ${isIncome ? '+' : '−'}
                        </div>
                        <div class="op-text">${op.category}</div>
                        <div class="op-amount">${op.amount} ${op.currency}</div>
                        </div>
                    `;
                }).join('');
        }
    } catch (error) {
        console.error('Ошибка запроса:', error);
    }
})();
