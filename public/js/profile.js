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
                html = `<h3>${window.i18n.trial}</h3><p>${window.i18n.trial_until.replace(':date', data.trial_ends_at)}</p>`;
                break;
            case 'active':
                html = `<h3>${window.i18n.active}</h3><p>${window.i18n.active_until.replace(':date', data.subscription_ends_at)}</p>`;
                break;
            case 'expired':
                html = `<h3>${window.i18n.expired}</h3><a href="/miniapp/tarifs" class="pay-btn">${window.i18n.pay_again}</a>`;
                break;
            default:
                html = `<h3>${window.i18n.no_subscription}</h3><a href="/miniapp/tarifs" class="pay-btn">${window.i18n.pay}</a>`;
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
        title: window.i18n.delete_confirm,
        text: window.i18n.delete_text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: window.i18n.delete_yes,
        cancelButtonText: window.i18n.delete_cancel
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("/miniapp/profile/delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    initData: tg.initData
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: window.i18n.delete_success,
                            text: window.i18n.delete_success_text,
                            icon: "success",
                            confirmButtonText: window.i18n.ok
                        }).then(() => {
                            location.href = "/miniapp";
                        });
                    } else {
                        Swal.fire({
                            title: window.i18n.delete_error,
                            text: data.message || window.i18n.delete_error_text,
                            icon: "error",
                            confirmButtonText: window.i18n.ok
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: window.i18n.delete_unknown,
                        text: window.i18n.delete_unknown_text,
                        icon: "error",
                        confirmButtonText: window.i18n.ok
                    });
                });
        }
    });
});
