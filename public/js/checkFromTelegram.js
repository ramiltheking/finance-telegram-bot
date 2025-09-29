if (!window.Telegram.WebApp || !window.Telegram.WebApp.initDataUnsafe?.user) {
    window.location.href = "/telegram-required";
}
