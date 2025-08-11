document.addEventListener('DOMContentLoaded', function() {
    // Установка имени пользователя
    setUserName();
    
    // Установка периода по умолчанию
    setDefaultPeriod();
    
    // Обработчики событий
    document.getElementById('setMonthPeriod').addEventListener('click', setCurrentMonthPeriod);
    document.getElementById('savePeriod').addEventListener('click', savePeriod);
});

// Установка имени пользователя из сессии
function setUserName() {
    fetch('get_user_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.name) {
                document.getElementById('userGreeting').innerHTML = `Добро пожаловать, <span>${data.name}</span>`;
            }
        })
        .catch(error => console.error('Ошибка при получении данных пользователя:', error));
}

// Установка периода по умолчанию
function setDefaultPeriod() {
    // Проверяем LocalStorage
    const savedStart = localStorage.getItem('periodStart');
    const savedEnd = localStorage.getItem('periodEnd');
    
    if (savedStart && savedEnd) {
        document.getElementById('periodStart').value = savedStart;
        document.getElementById('periodEnd').value = savedEnd;
    } else {
        // Если нет сохраненных значений, устанавливаем текущий месяц
        setCurrentMonthPeriod();
    }
}

// Установка периода текущего месяца
function setCurrentMonthPeriod() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    document.getElementById('periodStart').value = formatDate(firstDay);
    document.getElementById('periodEnd').value = formatDate(lastDay);
}

// Сохранение периода
function savePeriod() {
    const startDate = document.getElementById('periodStart').value;
    const endDate = document.getElementById('periodEnd').value;
    
    if (!startDate || !endDate) {
        showNotification('Пожалуйста, выберите начало и конец периода', 'error');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        showNotification('Дата начала не может быть больше даты окончания', 'error');
        return;
    }
    
    // Сохраняем в LocalStorage
    localStorage.setItem('periodStart', startDate);
    localStorage.setItem('periodEnd', endDate);
    
    // Отправляем на сервер для сохранения в сессии
    fetch('save_period.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `periodStart=${encodeURIComponent(startDate)}&periodEnd=${encodeURIComponent(endDate)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Период успешно сохранен', 'success');
        } else {
            showNotification('Ошибка при сохранении периода', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении периода', 'error');
    });
}

// Показать уведомление
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