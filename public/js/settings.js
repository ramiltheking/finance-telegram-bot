const TokenCSRF = document.querySelector('meta[name="csrf-token"]').content;

function getUtcOffset() {
    const offsetMinutes = new Date().getTimezoneOffset();
    const offsetHours = -(offsetMinutes / 60);
    return "UTC" + (offsetHours >= 0 ? "+" + offsetHours : offsetHours);
}

document.getElementById('detectTimezone').addEventListener('click', () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (pos) => {
            const { latitude, longitude } = pos.coords;

            const response = await fetch('/detect-timezone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': TokenCSRF
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
            } else {
                alert("Не удалось определить часовой пояс");
            }
        });
    } else {
        alert("Геолокация не поддерживается вашим устройством");
    }
});

window.addEventListener('DOMContentLoaded', () => {
    const settingsData = window.userSettings;

    document.querySelectorAll('.card:nth-child(1) .buttons__group button').forEach(btn => {
        if (btn.textContent === settingsData.currency) {
            btn.classList.add('active');
        }
        btn.addEventListener('click', () => {
            saveSetting('currency', btn.textContent);
            document.querySelectorAll('.card:nth-child(1) .buttons__group button')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    document.querySelectorAll('.card:nth-child(4) .buttons__group button').forEach(btn => {
        if (btn.dataset.lang === settingsData.language) {
            btn.classList.add('active');
        }

        btn.addEventListener('click', () => {
            saveSetting('language', btn.dataset.lang).then(() => {
                document.querySelectorAll('.card:nth-child(4) .buttons__group button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                location.reload();
            });
        });
    });

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

    toggle.addEventListener('change', (e) => {
        timeSettings.classList.toggle('hidden', !e.target.checked);
        saveSetting('reminders_enabled', e.target.checked);
    });

    hourSelect.addEventListener('change', (e) => {
        saveSetting('reminder_hour', parseInt(e.target.value));
    });

    minuteSelect.addEventListener('change', (e) => {
        saveSetting('reminder_minute', parseInt(e.target.value));
    });
});

async function saveSetting(key, value) {
    await fetch('/miniapp/settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': TokenCSRF
        },
        body: JSON.stringify({
            key,
            value
        })
    });
}
