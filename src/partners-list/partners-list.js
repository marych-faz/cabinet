document.addEventListener('DOMContentLoaded', function() {
    // Элементы DOM
    const tableBody = document.getElementById('partnersBody');
    const editBtn = document.getElementById('editBtn');
    const archiveBtn = document.getElementById('archiveBtn');
    const searchInput = document.getElementById('searchInput');
    const archiveFilter = document.getElementById('archiveFilter');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const modalClose = document.getElementById('modalClose');
    
    // Состояние приложения
    let partnersData = [];
    let selectedPartnerId = null;
    let currentFilters = {
        showArchived: localStorage.getItem('showArchived') === 'true' || false,
        searchQuery: ''
    };
    
    // Инициализация при загрузке
    init();
    
    function init() {
        // Настройка начального состояния
        archiveFilter.checked = currentFilters.showArchived;
        
        // Назначение обработчиков событий
        setupEventListeners();
        
        // Первая загрузка данных
        loadPartners();
    }
    
    function setupEventListeners() {
        editBtn.addEventListener('click', editSelectedPartner);
        archiveBtn.addEventListener('click', toggleArchiveStatus);
        searchInput.addEventListener('input', debounce(applyFilters, 300));
        archiveFilter.addEventListener('change', toggleArchiveFilter);
        saveBtn.addEventListener('click', savePartner);
        cancelBtn.addEventListener('click', closeModal);
        modalClose.addEventListener('click', closeModal);
    }
    
    // Загрузка данных с сервера
    async function loadPartners() {
        try {
            showLoadingState(true);
            
            const response = await fetch('partners-list.php?action=getAll');
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const responseText = await response.text();
            let data;
            
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON:', responseText);
                throw new Error('Invalid server response format');
            }
            
            if (!data || !data.success) {
                throw new Error(data?.message || 'Unknown server error');
            }
            
            partnersData = data.data || [];
            renderTable();
            
        } catch (error) {
            console.error('Load partners error:', error);
            showErrorState(error.message);
        } finally {
            showLoadingState(false);
        }
    }
    
    // Отображение состояния загрузки
    function showLoadingState(show) {
        if (show) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center;">
                        <div class="loading-spinner"></div>
                        Загрузка данных...
                    </td>
                </tr>
            `;
        }
    }
    
    // Отображение состояния ошибки
    function showErrorState(message) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; color: #ef233c;">
                    ${message || 'Ошибка загрузки данных'}
                </td>
            </tr>
        `;
    }
    
    // Рендер таблицы с партнерами
    function renderTable() {
        if (!partnersData.length) {
            showErrorState('Нет данных для отображения');
            return;
        }
        
        const filteredData = getFilteredData();
        
        if (!filteredData.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center;">
                        Нет данных, соответствующих вашему запросу
                    </td>
                </tr>
            `;
            return;
        }
        
        tableBody.innerHTML = filteredData.map(partner => `
            <tr class="${partner.is_archived ? 'archived' : ''} ${selectedPartnerId === partner.id ? 'selected' : ''}" 
                data-id="${partner.id}">
                <td>${escapeHtml(partner.name)}</td>
                <td><a href="mailto:${escapeHtml(partner.email)}">${escapeHtml(partner.email)}</a></td>
                <td>${escapeHtml(partner.phone)}</td>
                <td>${escapeHtml(partner.dog_num)}</td>
                <td>${formatDate(partner.dog_beg_date)} - ${formatDate(partner.dog_end_date)}</td>
                <td>${escapeHtml(partner.bank_name)}</td>
                <td>
                    <span class="status-badge ${partner.is_archived ? 'status-archived' : 'status-active'}">
                        ${partner.is_archived ? 'Архив' : 'Активен'}
                    </span>
                </td>
            </tr>
        `).join('');
        
        // Назначение обработчиков для строк
        document.querySelectorAll('#partnersBody tr').forEach(row => {
            row.addEventListener('click', () => {
                selectedPartnerId = row.dataset.id;
                document.querySelectorAll('#partnersBody tr').forEach(r => 
                    r.classList.remove('selected'));
                row.classList.add('selected');
            });
        });
    }
    
    // Открытие модального окна редактирования
    function editSelectedPartner() {
        if (!selectedPartnerId) {
            alert('Пожалуйста, выберите партнера для редактирования');
            return;
        }
        
        const partner = partnersData.find(p => p.id == selectedPartnerId);
        if (!partner) {
            alert('Выбранный партнер не найден');
            return;
        }
        
        // Заполнение формы данными
        fillEditForm(partner);
        
        // Открытие модального окна
        document.getElementById('editModal').style.display = 'flex';
    }
    
    // Заполнение формы редактирования
    function fillEditForm(partner) {
        document.getElementById('partnerId').value = partner.id;
        document.getElementById('name').value = partner.name;
        document.getElementById('login').value = partner.login;
        document.getElementById('email').value = partner.email;
        document.getElementById('phone').value = partner.phone;
        document.getElementById('dogNum').value = partner.dog_num;
        document.getElementById('dogBegDate').value = partner.dog_beg_date;
        document.getElementById('dogEndDate').value = partner.dog_end_date;
        document.getElementById('bankName').value = partner.bank_name;
        document.getElementById('bankBik').value = partner.bank_bik;
        document.getElementById('bankKs').value = partner.bank_ks;
        document.getElementById('bankRs').value = partner.bank_rs;
        document.getElementById('isArchived').checked = partner.is_archived;
    }
    
    // Переключение статуса архивации
    async function toggleArchiveStatus() {
        if (!selectedPartnerId) {
            alert('Пожалуйста, выберите партнера');
            return;
        }
        
        const partner = partnersData.find(p => p.id == selectedPartnerId);
        if (!partner) {
            alert('Выбранный партнер не найден');
            return;
        }
        
        const newStatus = !partner.is_archived;
        const confirmMessage = newStatus 
            ? `Вы уверены, что хотите переместить партнера "${partner.name}" в архив?` 
            : `Вы уверены, что хотите восстановить партнера "${partner.name}" из архива?`;
        
        if (!confirm(confirmMessage)) return;
        
        try {
            const response = await fetch('partners-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggleArchive',
                    id: partner.id,
                    is_archived: newStatus ? 1 : 0
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Ошибка обновления статуса');
            }
            
            // Обновляем локальные данные
            partner.is_archived = newStatus;
            renderTable();
            
        } catch (error) {
            console.error('Toggle archive error:', error);
            alert('Не удалось изменить статус: ' + error.message);
        }
    }
    
    // Сохранение изменений партнера
    async function savePartner() {
        const formData = getFormData();
        
        if (!validateForm(formData)) {
            return;
        }
        
        try {
            const response = await fetch('partners-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    ...formData
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Ошибка сохранения данных');
            }
            
            // Обновляем локальные данные
            updateLocalPartnerData(formData);
            
            // Закрываем модальное окно
            closeModal();
            
            // Обновляем таблицу
            renderTable();
            
        } catch (error) {
            console.error('Save partner error:', error);
            alert('Не удалось сохранить данные: ' + error.message);
        }
    }
    
    // Получение данных из формы
    function getFormData() {
        return {
            id: document.getElementById('partnerId').value,
            name: document.getElementById('name').value,
            login: document.getElementById('login').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            dog_num: document.getElementById('dogNum').value,
            dog_beg_date: document.getElementById('dogBegDate').value,
            dog_end_date: document.getElementById('dogEndDate').value,
            bank_name: document.getElementById('bankName').value,
            bank_bik: document.getElementById('bankBik').value,
            bank_ks: document.getElementById('bankKs').value,
            bank_rs: document.getElementById('bankRs').value,
            is_archived: document.getElementById('isArchived').checked ? 1 : 0
        };
    }
    
    // Валидация формы
    function validateForm(formData) {
        if (!formData.name || !formData.email || !formData.phone) {
            alert('Пожалуйста, заполните обязательные поля: ФИО, Email и Телефон');
            return false;
        }
        
        if (!/^[\w.-]+@[\w.-]+\.\w+$/.test(formData.email)) {
            alert('Пожалуйста, введите корректный email');
            return false;
        }
        
        return true;
    }
    
    // Обновление локальных данных
    function updateLocalPartnerData(formData) {
        const index = partnersData.findIndex(p => p.id == formData.id);
        if (index !== -1) {
            partnersData[index] = { ...partnersData[index], ...formData };
        }
    }
    
    // Закрытие модального окна
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Фильтрация данных
    function getFilteredData() {
        return partnersData.filter(partner => {
            const archiveMatch = currentFilters.showArchived || !partner.is_archived;
            
            if (!currentFilters.searchQuery) {
                return archiveMatch;
            }
            
            const searchQuery = currentFilters.searchQuery.toLowerCase();
            return archiveMatch && (
                partner.name.toLowerCase().includes(searchQuery) ||
                partner.email.toLowerCase().includes(searchQuery) ||
                partner.phone.toLowerCase().includes(searchQuery)
            );
        });
    }
    
    // Применение фильтров
    function applyFilters() {
        currentFilters.searchQuery = searchInput.value.trim();
        renderTable();
    }
    
    // Переключение фильтра архива
    function toggleArchiveFilter() {
        currentFilters.showArchived = archiveFilter.checked;
        localStorage.setItem('showArchived', currentFilters.showArchived);
        renderTable();
    }
    
    // Вспомогательные функции
    function formatDate(dateString) {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU');
        } catch {
            return dateString;
        }
    }
    
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
});