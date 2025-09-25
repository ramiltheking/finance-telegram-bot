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
        const dataRes = await fetch('/miniapp/dashboard/data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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
                subscription = `<h3>${window.i18n.trial}</h3><p>${window.i18n.trial_until.replace(':date', sub.trial_ends_at)}</p>`;
                break;
            case 'active':
                subscription = `<h3>${window.i18n.active}</h3><p>${window.i18n.active_until.replace(':date', sub.subscription_ends_at)}</p>`;
                break;
            case 'expired':
                subscription = `<h3>${window.i18n.expired}</h3><a href="/miniapp/tarifs" class="pay-btn">${window.i18n.pay_again}</a>`;
                break;
            default:
                subscription = `<h3>${window.i18n.no_subscription}</h3><a href="/miniapp/tarifs" class="pay-btn">${window.i18n.pay}</a>`;
        }

        document.getElementById('subStatus').innerHTML = subscription;

        const ctx = document.getElementById('chart').getContext('2d');
        const labels = Object.keys(data.categories);
        const values = Object.values(data.categories);
        const colors = ['#f66', '#6f6', '#66f', '#fc6','#6cf', '#c6f', '#ff6','#6ff', '#f6c', '#9f6'];

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
            document.getElementById('chart').remove();
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
