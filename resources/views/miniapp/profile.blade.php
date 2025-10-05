<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/css/profile.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #1cc88a;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #343a40;
            --text: #444;
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
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .main {
            flex: 1 1 auto;
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
        }

        .prev-btn {
            position: absolute;
            left: 0px;
            top: 5px;
            padding: 10px 20px;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .settings-btn {
            position: absolute;
            right: 0px;
            top: 5px;
            padding: 10px 20px;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .prev-btn:hover {
            transform: translate(-5px, 0);
            opacity: 0.8;
        }

        .settings-btn:hover {
            transform: scale(1.1);
            opacity: 0.8;
        }

        .header-title {
            font-size: 24px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #4e73df;
        }

        .username {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .pay-btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            border: none;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .pay-btn:hover {
            background: linear-gradient(135deg, #43A047, #1B5E20);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .pay-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .message {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .list-item {
            background: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            font-size: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-item span {
            font-weight: 600;
            color: var(--primary);
        }

        .danger-btn {
            background: #d9534f;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .danger-btn:hover {
            background: #c9302c;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .list-item {
                font-size: 14px;
                padding: 10px 12px;
            }

            h2,
            h3 {
                font-size: 18px;
            }
        }
    </style>
    <script src="/js/checkFromTelegram.js"></script>
</head>

<body>
    <header class="header">
        <a href="{{ route('miniapp.index') }}" class="prev-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
            </svg>
        </a>
        <div class="header-title">
            <strong>{{ __('profile.title') }}</strong>
        </div>
        <a href="{{ route('miniapp.settings') }}" class="settings-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-gear"
                viewBox="0 0 16 16">
                <path
                    d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
                <path
                    d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
            </svg>
        </a>
    </header>

    <main class="main">
        <div class="card" style="text-align: center;">
            <img id="userPhoto" class="avatar" src="" alt="avatar">
            <div class="username" id="username"></div>
            <div id="fullname"></div>
            <div id="telegramId"></div>
        </div>

        <div class="card" id="subscription"></div>

        <div class="card">
            <h3>{{ __('profile.payments_title') }}</h3>
            <div class="list payments" id="payments"></div>
        </div>

        <div class="card">
            <h3>{{ __('profile.account_title') }}</h3>
            <button id="deleteUserBtn" class="danger-btn">{{ __('profile.delete_btn') }}</button>
        </div>
    </main>

    <script>
        window.i18n = @json(__('profile'));
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
                        html = `<h3>${window.i18n.expired}</h3><a class="pay-btn" id="pay-btn">${window.i18n.pay_again}</a>`;
                        break;
                    default:
                        html = `<h3>${window.i18n.no_subscription}</h3><a class="pay-btn" id="pay-btn">${window.i18n.pay}</a>`;
                }
                document.getElementById('subscription').innerHTML = html;

                const payBtn = document.getElementById('pay-btn');
                if (payBtn) {
                    payBtn.addEventListener('click', function() {
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
                }

                if (data.emptyPayments) {
                    document.getElementById('payments').innerHTML = `<p class="message">${data.messagePayments}</p>`;
                } else {
                    document.getElementById('payments').innerHTML = data.payments.map(p => `
                        <div class="list-item">
                            ID: ${p.telegram_payment_charge_id ? `${p.telegram_payment_charge_id.slice(0, 5)}...${p.telegram_payment_charge_id.slice(-3)}` : 'N/A'} <span>${p.amount} ${p.currency == 'XTR' ? '⭐' : p.currency} (${p.status})</span>
                        </div>
                    `).join('');
                }
            });

        document.getElementById("deleteUserBtn").addEventListener("click", function() {
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
    </script>
</body>

</html>
