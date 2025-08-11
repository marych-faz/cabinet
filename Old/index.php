<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Перечень партнеров</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --hover-color: #3a56d4;
            --danger-color: #ef233c;
            --archive-color: #6c757d;
            --text-color: #2b2d42;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
            --modal-bg: rgba(0, 0, 0, 0.5);
            --zebra-even: #f8f9fa;
            --zebra-odd: #ffffff;
            --status-active: #d1e7dd;
            --status-archived: #e2e3e5;
            --status-active-text: #0a3622;
            --status-archived-text: #383d41;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 20px;
            color: var(--text-color);
            background-color: #f5f7ff;
        }
        
        .header-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        h1 {
            color: var(--text-color);
            margin: 0;
            font-weight: 600;
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--hover-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #d90429;
        }
        
        .btn-archive {
            background-color: var(--archive-color);
            color: white;
        }
        
        .btn-archive:hover {
            background-color: #5a6268;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: var(--status-active);
            color: var(--status-active-text);
        }
        
        .status-archived {
            background-color: var(--status-archived);
            color: var(--status-archived-text);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: white;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }
        
        th {
            position: sticky;
            top: 0;
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 10px 12px;
            text-align: left;
            font-size: 14px;
        }
        
        th:first-child {
            border-top-left-radius: 8px;
        }
        
        th:last-child {
            border-top-right-radius: 8px;
        }
        
        td {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background-color: var(--zebra-even);
        }
        
        tr:nth-child(odd) {
            background-color: var(--zebra-odd);
        }
        
        tr:hover td {
            background-color: rgba(67, 97, 238, 0.08);
        }
        
        tr.selected td {
            background-color: rgba(67, 97, 238, 0.15) !important;
        }
        
        .scroll-shadow {
            position: relative;
        }
        
        .scroll-shadow::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 20px;
            background: linear-gradient(to right, rgba(255,255,255,0), rgba(255,255,255,1));
            pointer-events: none;
        }

        /* Стили для чекбокса архива */
        .filter-checkbox {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            cursor: pointer;
        }
        
        /* Стили для поиска */
        .search-container {
            position: relative;
            width: 300px;
        }
        
        .search-container input {
            width: 100%;
            padding: 8px 12px 8px 32px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .search-container input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 14px;
        }
        
        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--modal-bg);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
/* Стили для модального окна */
/* Модальное окно */
.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.7);
  z-index: 1000;
  justify-content: center;
  align-items: center;
  font-family: 'Segoe UI', Roboto, sans-serif;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 650px;
  box-shadow: 0 5px 30px rgba(0,0,0,0.3);
  overflow: hidden;
  animation: modalFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: translateY(-50px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Заголовок */
.modal-header {
  padding: 18px 25px;
  background: #4361ee;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.modal-close {
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.modal-close:hover {
  opacity: 1;
}

/* Тело модального окна */
.modal-body {
  padding: 25px;
  max-height: 70vh;
  overflow-y: auto;
}

/* Форма */
.form-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-row.triple {
  grid-template-columns: 1fr 1fr 1fr;
}

.form-group {
  margin-bottom: 0;
}

.form-group.full-width {
  grid-column: span 2;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #555;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: all 0.2s;
  background: #f9fafc;
}

.form-control:focus {
  outline: none;
  border-color: #4361ee;
  box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
  background: white;
}

/* Секции */
.form-section-title {
  font-size: 14px;
  font-weight: 600;
  color: #4361ee;
  margin: 10px 0 15px;
  padding-bottom: 5px;
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Переключатель статуса */
.form-status {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.status-switch {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.status-label {
  font-size: 14px;
  color: #555;
  display: flex;
  align-items: center;
  gap: 8px;
}

.slider {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}

.slider:before {
  content: "";
  position: absolute;
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #4361ee;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

/* Футер модального окна */
.modal-footer {
  padding: 15px 25px;
  background: #f9fafc;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn {
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-outline {
  background: white;
  border: 1px solid #ddd;
  color: #555;
}

.btn-outline:hover {
  background: #f5f5f5;
}

.btn-primary {
  background: #4361ee;
  border: 1px solid #4361ee;
  color: white;
}

.btn-primary:hover {
  background: #3a56d4;
}

/* Адаптивность */
@media (max-width: 768px) {
  .form-row,
  .form-row.triple {
    grid-template-columns: 1fr;
  }
  
  .form-group.full-width {
    grid-column: span 1;
  }
  
  .modal-content {
    width: 95%;
    max-width: 95%;
  }
}
</style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <i class="fas fa-handshake" style="font-size: 24px; color: var(--primary-color);"></i>
            <h1>Перечень партнеров</h1>
        </div>
        
        <div class="controls">
            <div class="action-buttons">
                <button class="btn btn-primary" id="editBtn">
                    <i class="fas fa-edit"></i>
                    Редактировать
                </button>
                <button class="btn btn-archive" id="archiveBtn">
                    <i class="fas fa-archive"></i>
                    В архив
                </button>
            </div>
            
            <div class="filter-controls">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="Поиск по имени, email или телефону...">
                </div>
                
                <label class="filter-checkbox">
                    <input type="checkbox" id="archiveFilter">
                    Показать архивных
                </label>
            </div>
        </div>
        
        <div class="table-container scroll-shadow">
            <table id="partnersTable">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Договор</th>
                        <th>Срок действия</th>
                        <th>Банк</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody id="partnersBody">
                    <!-- Данные будут загружены через JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div class="modal" id="editModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">
        <i class="fas fa-user-edit"></i> Редактирование партнера
      </h3>
      <button class="modal-close" id="modalClose">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div class="modal-body">
      <form id="partnerForm" class="form-grid">
        <input type="hidden" id="partnerId">
        
        <!-- Строка 1 -->
        <div class="form-row">
          <div class="form-group">
            <label for="name">
              <i class="fas fa-user"></i> ФИО
            </label>
            <input type="text" id="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="login">
              <i class="fas fa-sign-in-alt"></i> Логин
            </label>
            <input type="text" id="login" class="form-control" required>
          </div>
        </div>
        
        <!-- Строка 2 -->
        <div class="form-row">
          <div class="form-group">
            <label for="email">
              <i class="fas fa-envelope"></i> Email
            </label>
            <input type="email" id="email" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="phone">
              <i class="fas fa-phone"></i> Телефон
            </label>
            <input type="tel" id="phone" class="form-control" required>
          </div>
        </div>
        
        <!-- Строка 3 - Договор -->
        <div class="form-section-title">
          <i class="fas fa-file-contract"></i> Договор
        </div>
        <div class="form-row triple">
          <div class="form-group">
            <label for="dogNum">Номер</label>
            <input type="text" id="dogNum" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="dogBegDate">Начало</label>
            <input type="date" id="dogBegDate" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="dogEndDate">Окончание</label>
            <input type="date" id="dogEndDate" class="form-control" required>
          </div>
        </div>
        
        <!-- Строка 4 - Банк -->
        <div class="form-section-title">
          <i class="fas fa-university"></i> Банковские реквизиты
        </div>
        <div class="form-group full-width">
          <label for="bankName">Название банка</label>
          <input type="text" id="bankName" class="form-control" required>
        </div>
        
        <div class="form-row triple">
          <div class="form-group">
            <label for="bankBik">БИК</label>
            <input type="text" id="bankBik" class="form-control" required maxlength="9">
          </div>
          <div class="form-group">
            <label for="bankKs">Корр. счет</label>
            <input type="text" id="bankKs" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="bankRs">Расч. счет</label>
            <input type="text" id="bankRs" class="form-control" required>
          </div>
        </div>
        
        <!-- Статус -->
        <div class="form-status">
          <label class="status-switch">
            <input type="checkbox" id="isArchived">
            <span class="slider round"></span>
            <span class="status-label">
              <i class="fas fa-archive"></i> В архиве
            </span>
          </label>
        </div>
      </form>
    </div>
    
    <div class="modal-footer">
      <button class="btn btn-outline" id="cancelBtn">
        <i class="fas fa-times"></i> Отмена
      </button>
      <button class="btn btn-primary" id="saveBtn">
        <i class="fas fa-save"></i> Сохранить
      </button>
    </div>
  </div>
</div>

    <script>
        let partnersData = [];
        let selectedPartnerId = null;
        let selectedRowIndex = -1;
        let currentFilters = {
            showArchived: localStorage.getItem('showArchived') === 'true' || false,
            searchQuery: ''
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация UI
            document.getElementById('archiveFilter').checked = currentFilters.showArchived;
            
            // Загрузка данных
            loadData().then(() => {
                renderTable();
                setupEventListeners();
            });
        });
        
        function setupEventListeners() {
            // Кнопки действий
            document.getElementById('editBtn').addEventListener('click', editSelectedPartner);
            document.getElementById('archiveBtn').addEventListener('click', toggleArchiveStatus);
            
            // Фильтры
            document.getElementById('archiveFilter').addEventListener('change', function() {
                currentFilters.showArchived = this.checked;
                localStorage.setItem('showArchived', this.checked);
                renderTable();
            });
            
            document.getElementById('searchInput').addEventListener('input', debounce(function() {
                currentFilters.searchQuery = this.value.trim().toLowerCase();
                renderTable();
            }, 300));
            
            // Модальное окно
            document.getElementById('modalClose').addEventListener('click', closeModal);
            document.getElementById('cancelBtn').addEventListener('click', closeModal);
            document.getElementById('saveBtn').addEventListener('click', savePartner);
        }
        
        async function loadData() {
            try {
                const response = await fetch('partner.php?action=getAll');
                const data = await response.json();
                if (data.success) {
                    partnersData = data.data;
                } else {
                    throw new Error(data.message || 'Ошибка загрузки данных');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Не удалось загрузить данные: ' + error.message);
                partnersData = [];
            }
        }
        
        function getFilteredData() {
            return partnersData.filter(partner => {
                const archiveMatch = currentFilters.showArchived || !partner.is_archived;
                
                if (!currentFilters.searchQuery) return archiveMatch;
                
                const searchMatch = 
                    partner.name.toLowerCase().includes(currentFilters.searchQuery) ||
                    partner.email.toLowerCase().includes(currentFilters.searchQuery) ||
                    partner.phone.toLowerCase().includes(currentFilters.searchQuery);
                
                return archiveMatch && searchMatch;
            });
        }
        
        function renderTable() {
            const tableBody = document.getElementById('partnersBody');
            tableBody.innerHTML = '';
            
            const filteredData = getFilteredData();
            selectedRowIndex = -1;
            
            if (filteredData.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="7" style="text-align: center;">Нет данных, соответствующих вашему запросу</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            filteredData.forEach((partner, index) => {
                const row = document.createElement('tr');
                if (partner.is_archived) {
                    row.classList.add('archived-row');
                }
                
                row.innerHTML = `
                    <td>${partner.name}</td>
                    <td><a href="mailto:${partner.email}">${partner.email}</a></td>
                    <td>${partner.phone}</td>
                    <td>${partner.dog_num}</td>
                    <td>${formatDate(partner.dog_beg_date)} - ${formatDate(partner.dog_end_date)}</td>
                    <td>${partner.bank_name}</td>
                    <td><span class="status-badge ${partner.is_archived ? 'status-archived' : 'status-active'}">
                        ${partner.is_archived ? 'Архив' : 'Активен'}
                    </span></td>
                `;
                
                row.addEventListener('click', () => {
                    document.querySelectorAll('#partnersBody tr').forEach(r => 
                        r.classList.remove('selected'));
                    row.classList.add('selected');
                    selectedPartnerId = partner.id;
                    selectedRowIndex = index;
                });
                
                tableBody.appendChild(row);
            });
            
            // Автовыбор первой строки
            if (tableBody.firstChild) {
                tableBody.firstChild.click();
            }
        }
        
        function editSelectedPartner() {
            if (!selectedPartnerId) {
                alert('Пожалуйста, выберите партнера для редактирования');
                return;
            }
            
            const partner = partnersData.find(p => p.id == selectedPartnerId);
            if (!partner) return;
            
            // Заполняем форму данными
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
            
            // Открываем модальное окно
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function toggleArchiveStatus() {
            if (!selectedPartnerId) {
                alert('Пожалуйста, выберите партнера');
                return;
            }
            
            const partner = partnersData.find(p => p.id == selectedPartnerId);
            if (!partner) return;
            
            const newStatus = !partner.is_archived;
            const confirmMessage = newStatus 
                ? `Вы уверены, что хотите переместить партнера "${partner.name}" в архив?` 
                : `Вы уверены, что хотите восстановить партнера "${partner.name}" из архива?`;
            
            if (confirm(confirmMessage)) {
                // Отправляем запрос на сервер
                fetch('partner.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'toggleArchive',
                        id: partner.id,
                        is_archived: newStatus ? 1 : 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        partner.is_archived = newStatus;
                        renderTable();
                    } else {
                        throw new Error(data.message || 'Ошибка обновления статуса');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Не удалось изменить статус: ' + error.message);
                });
            }
        }
        
        function savePartner() {
            const formData = {
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
            
            // Валидация (можно добавить более сложную)
            if (!formData.name || !formData.email || !formData.phone) {
                alert('Пожалуйста, заполните обязательные поля');
                return;
            }
            
            // Отправляем данные на сервер
            fetch('partner.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    ...formData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем данные в клиенте
                    const index = partnersData.findIndex(p => p.id == formData.id);
                    if (index !== -1) {
                        partnersData[index] = { ...partnersData[index], ...formData };
                    }
                    renderTable();
                    closeModal();
                } else {
                    throw new Error(data.message || 'Ошибка сохранения данных');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Не удалось сохранить данные: ' + error.message);
            });
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Вспомогательные функции
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU');
        }
        
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(context, args);
                }, wait);
            };
        }
    </script>
</body>
</html>