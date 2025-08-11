document.addEventListener('DOMContentLoaded', function() {
    // Загрузка имени пользователя из сессии
    function loadUserName() {
        fetch('get_user_info.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.user_name) {
                    const greetingElement = document.getElementById('userGreeting');
                    greetingElement.innerHTML = `Добро пожаловать, <span>${data.user_name}</span>`;
                }
            })
            .catch(error => {
                console.error('Error loading user name:', error);
            });
    }

    // Загрузка сохраненного периода из localStorage
    const loadPeriod = () => {
        const periodStart = localStorage.getItem('periodStart');
        const periodEnd = localStorage.getItem('periodEnd');
        
        if (periodStart) document.getElementById('periodStart').value = periodStart;
        if (periodEnd) document.getElementById('periodEnd').value = periodEnd;
    };

    // Установка текущего месяца (исправленная версия)
    document.getElementById('setMonthPeriod').addEventListener('click', function() {
        const now = new Date();
        // Первый день текущего месяца (с корректным месяцем)
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        // Последний день текущего месяца
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        
        // Форматирование даты в формат YYYY-MM-DD
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        document.getElementById('periodStart').value = formatDate(firstDay);
        document.getElementById('periodEnd').value = formatDate(lastDay);
    });

    // Сохранение периода
    document.getElementById('savePeriod').addEventListener('click', function() {
        const start = document.getElementById('periodStart').value;
        const end = document.getElementById('periodEnd').value;
        
        if (start && end) {
            localStorage.setItem('periodStart', start);
            localStorage.setItem('periodEnd', end);
            
            fetch('save_period.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `periodStart=${encodeURIComponent(start)}&periodEnd=${encodeURIComponent(end)}`
            })
            .then(response => response.json())
            .then(data => {
                showNotification(data.success ? 'Период сохранен' : 'Ошибка сохранения', 
                              data.success ? 'success' : 'error');
            });
        } else {
            showNotification('Укажите начало и конец периода', 'warning');
        }
    });

    // Инициализация
    loadUserName();
    loadPeriod();
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}