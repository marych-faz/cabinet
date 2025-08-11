// Функция для отображения уведомления
function showNotification(message, type = 'info') {
    const container = document.getElementById('notifications-container');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <p>${message}</p>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Автоматическое закрытие через 5 секунд
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

// Функция для проверки уведомлений (long polling)
function checkNotifications() {
    const statusElement = document.getElementById('connection-status');
    
    fetch('php/check_notifications.php')
        .then(response => {
            if (!response.ok) throw new Error('Ошибка сети');
            return response.json();
        })
        .then(data => {
            if (data.success && data.message) {
                showNotification(data.message);
                statusElement.textContent = 'новое уведомление получено';
                statusElement.style.color = 'green';
            } else {
                statusElement.textContent = 'ожидание уведомлений...';
                statusElement.style.color = '#666';
            }
            
            // Снова проверяем сразу после получения ответа
            checkNotifications();
        })
        .catch(error => {
            console.error('Error:', error);
            statusElement.textContent = 'проблемы с соединением, попытка переподключения...';
            statusElement.style.color = 'red';
            
            // Повтор через 5 секунд при ошибке
            setTimeout(checkNotifications, 5000);
        });
}

// Запускаем проверку при загрузке страницы
document.addEventListener('DOMContentLoaded', checkNotifications);