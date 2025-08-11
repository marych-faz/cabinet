document.addEventListener('DOMContentLoaded', () => {
    const sendBtn = document.getElementById('send-btn');
    const messageText = document.getElementById('message-text');
    const statusElement = document.getElementById('agent-status');
    
    sendBtn.addEventListener('click', () => {
        const message = messageText.value.trim();
        
        if (!message) {
            statusElement.textContent = 'Введите сообщение';
            statusElement.style.color = 'red';
            return;
        }
        
        statusElement.textContent = 'Отправка...';
        statusElement.style.color = '#666';
        
        fetch('php/save_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusElement.textContent = 'Уведомление отправлено администратору';
                statusElement.style.color = 'green';
                messageText.value = '';
            } else {
                statusElement.textContent = 'Ошибка при отправке';
                statusElement.style.color = 'red';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusElement.textContent = 'Ошибка соединения';
            statusElement.style.color = 'red';
        });
    });
});