<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Откройте в Telegram</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .telegram-icon {
            width: 80px;
            height: 80px;
            background: #0088cc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .telegram-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: 600;
        }

        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
            font-size: 16px;
        }

        .button {
            display: inline-block;
            background: #0088cc;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 136, 204, 0.3);
        }

        .button:hover {
            background: #0077b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 136, 204, 0.4);
        }

        .button:active {
            transform: translateY(0);
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #eee;
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #0088cc;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .step-text {
            font-size: 12px;
            color: #666;
            text-align: center;
            max-width: 80px;
        }

        .countdown {
            margin-top: 20px;
            color: #999;
            font-size: 14px;
        }

        .qr-code {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            display: inline-block;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 20px;
            }

            p {
                font-size: 14px;
            }

            .steps {
                flex-direction: column;
                gap: 20px;
            }

            .steps::before {
                display: none;
            }

            .step {
                flex-direction: row;
                gap: 15px;
            }

            .step-number {
                margin-bottom: 0px;
            }

            .step-text {
                text-align: left;
                max-width: none;
                flex: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="telegram-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telegram"
                viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.287 5.906q-1.168.486-4.666 2.01-.567.225-.595.442c-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294q.39.01.868-.32 3.269-2.206 3.374-2.23c.05-.012.12-.026.166.016s.042.12.037.141c-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8 8 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629q.14.092.27.187c.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.4 1.4 0 0 0-.013-.315.34.34 0 0 0-.114-.217.53.53 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09" />
            </svg>
        </div>

        <h1>Откройте приложение в Telegram</h1>
        <p>Это приложение работает только внутри Telegram. Пожалуйста, откройте его через Telegram бота для продолжения.
        </p>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">Откройте Telegram</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Найдите нашего бота</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Запустите приложение</div>
            </div>
        </div>

        <button class="button" onclick="redirectToTelegram()">
            Открыть в Telegram
        </button>

        <div class="countdown">
            Автоматическая переадресация через: <span id="countdown">10</span> сек.
        </div>
    </div>

    <script>
        const botUsername = "VoiceFinanceTestBot";
        let timeLeft = 10;
        const countdownElement = document.getElementById('countdown');

        function updateCountdown() {
            countdownElement.textContent = timeLeft;
            timeLeft--;

            if (timeLeft < 0) {
                redirectToTelegram();
            } else {
                setTimeout(updateCountdown, 1000);
            }
        }

        function redirectToTelegram() {
            const telegramUrl = `https://t.me/${botUsername}?start=app`;

            window.location.href = telegramUrl;

            setTimeout(() => {
                if (!document.hidden) {
                    alert('Не удалось открыть Telegram. Пожалуйста, откройте приложение вручную через бота.');
                }
            }, 1000);
        }

        updateCountdown();

        function isTelegramInstalled() {
            return new Promise((resolve) => {
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = 'tg://resolve?domain=telegram';
                document.body.appendChild(iframe);

                setTimeout(() => {
                    document.body.removeChild(iframe);
                    resolve(false);
                }, 1000);

                window.addEventListener('blur', function handler() {
                    window.removeEventListener('blur', handler);
                    resolve(true);
                });
            });
        }

        window.addEventListener('load', async () => {
            const hasTelegram = await isTelegramInstalled();
            if (!hasTelegram) {
                document.querySelector('p').innerHTML +=
                    '<br><br><strong>Telegram не обнаружен на устройстве.</strong>';
            }
        });
    </script>
</body>

</html>
