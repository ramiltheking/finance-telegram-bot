const tg = window.Telegram.WebApp;
tg.expand();

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

document.getElementById('detectTimezone').addEventListener('click', () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (pos) => {
            const { latitude, longitude } = pos.coords;

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
                    document.getElementById('userTimezone').textContent = `${data.timezone} (${offsetStr})`;

                    await saveSetting('timezone', data.timezone);
                } else {
                    alert("Не удалось определить часовой пояс");
                }
            } catch (error) {
                console.error('Error detecting timezone:', error);
                alert("Ошибка при определении часового пояса");
            }
        }, (error) => {
            alert("Ошибка получения геолокации: " + error.message);
        });
    } else {
        alert("Геолокация не поддерживается вашим устройством");
    }
});

function updateSubscriptionInfo(info) {
    const statusElement = document.getElementById('subscriptionStatus');
    const statusMap = {
        'active': { text: 'Активна', class: 'status-active' },
        'trial': { text: 'Пробный период', class: 'status-trial' },
        'expired': { text: 'Истекла', class: 'status-expired' },
        'cancelled': { text: 'Отменена', class: 'status-expired' }
    };

    const status = statusMap[info.status] || statusMap.expired;
    statusElement.innerHTML = `<span class="status-badge ${status.class}">${status.text}</span>`;

    document.getElementById('subscriptionEnds').textContent = info.subscription_ends_at || 'Не активна';
    document.getElementById('nextPayment').textContent = info.next_payment_date || 'Не запланирован';

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

window.addEventListener('DOMContentLoaded', () => {
    const settingsData = window.userSettings;

    if (window.subscriptionInfo) {
        updateSubscriptionInfo(window.subscriptionInfo);
    } else {
        loadSubscriptionInfo();
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

                    Swal.fire({
                        title: 'Успех!',
                        text: 'Валюта изменена',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } catch (error) {
                    console.error('Error saving currency:', error);
                    Swal.fire({
                        title: 'Ошибка!',
                        text: 'Не удалось изменить валюту',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
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

                    Swal.fire({
                        title: 'Успех!',
                        text: 'Язык изменен. Страница будет перезагружена.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } catch (error) {
                    console.error('Error saving language:', error);
                    Swal.fire({
                        title: 'Ошибка!',
                        text: 'Не удалось изменить язык',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
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
});
