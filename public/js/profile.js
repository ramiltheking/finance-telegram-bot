const TokenCSRF = document.querySelector('meta[name="csrf-token"]').content;
const tg = window.Telegram.WebApp;
tg.expand();

const user = tg.initDataUnsafe?.user;

if (user) {
    document.getElementById('userPhoto').src = user.photo_url;
    document.getElementById('username').textContent = '@' + (user.username || 'Без ника');
    document.getElementById('fullname').textContent = (user.first_name || '') + ' ' + (user.last_name || '');
    document.getElementById('telegramId').textContent = "ID: " + user.id;
}

fetch('/miniapp/profile/data', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': TokenCSRF
    },
    body: JSON.stringify({
        initData: tg.initData
    })
})
    .then(res => res.json())
    .then(data => {
        let html = '';
        switch (data.status) {
            case 'trial':
                html = `<h3>⏳ Пробный период</h3><p>Активен до <b>${data.trial_ends_at}</b></p>`;
                break;
            case 'active':
                html = `<h3>💳 Подписка</h3><p>Активна до <b>${data.subscription_ends_at}</b></p>`;
                break;
            case 'expired':
                html = `<h3>❌ Подписка закончилась</h3><a href="/miniapp/tarifs" class="pay-btn">Оплатить</a>`;
                break;
            default:
                html = `<h3>❌ Подписка отсутствует</h3><a href="/miniapp/tarifs" class="pay-btn">Оформить</a>`;
        }
        document.getElementById('subscription').innerHTML = html;

        if (data.emptyPayments) {
            document.getElementById('payments').innerHTML = `<p class="message">${data.messagePayments}</p>`;
        } else {
            document.getElementById('payments').innerHTML = data.payments.map(p => `
                <div class="list-item">
                    ID: ${p.inv_id} <span>${p.amount} (${p.status})</span>
                </div>
            `).join('');
        }
    });

document.getElementById("deleteUserBtn").addEventListener("click", function () {
    Swal.fire({
        title: "Удалить данные?",
        text: "⚠️ Вы уверены, что хотите удалить все данные? Это действие необратимо!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Да, удалить",
        cancelButtonText: "Отмена"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("/miniapp/profile/delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                    "X-CSRF-TOKEN": TokenCSRF
                },
                body: JSON.stringify({})
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "✅ Успех!",
                            text: "Все данные удалены",
                            icon: "success",
                            confirmButtonText: "Ок"
                        }).then(() => {
                            location.href = "/miniapp";
                        });
                    } else {
                        Swal.fire({
                            title: "❌ Ошибка",
                            text: data.message || "Не удалось удалить данные",
                            icon: "error",
                            confirmButtonText: "Ок"
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: "⚠️ Ошибка",
                        text: "Произошла ошибка при удалении данных",
                        icon: "error",
                        confirmButtonText: "Ок"
                    });
                });
        }
    });
});

