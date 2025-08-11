document.addEventListener('DOMContentLoaded', function() {
    // Элементы DOM
    const tableBody = document.getElementById('partnersTableBody');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const noResults = document.getElementById('noResults');
    const searchInput = document.getElementById('searchInput');
    const archiveFilter = document.getElementById('archiveFilter');
    const sortSelect = document.getElementById('sortSelect');

    // Состояние приложения
    let partnersData = [];
    let currentFilter = {
        showArchived: localStorage.getItem('showArchived') === 'true' || false,
        searchQuery: '',
        sortField: localStorage.getItem('sortField') || 'name',
        sortOrder: localStorage.getItem('sortOrder') || 'ASC'
    };

    // Инициализация UI из localStorage
    archiveFilter.checked = currentFilter.showArchived;
    sortSelect.value = `${currentFilter.sortField}_${currentFilter.sortOrder.toLowerCase()}`;

    // Загрузка данных
    function loadPartners() {
        loadingIndicator.style.display = 'flex';
        noResults.style.display = 'none';
        tableBody.innerHTML = '';

        const params = new URLSearchParams();
        params.append('showArchived', currentFilter.showArchived);
        if (currentFilter.searchQuery) params.append('search', currentFilter.searchQuery);
        params.append('sortField', currentFilter.sortField);
        params.append('sortOrder', currentFilter.sortOrder);

        fetch(`partner.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    partnersData = data.data;
                    renderPartners(partnersData);
                    if (data.count === 0) {
                        noResults.style.display = 'block';
                    }
                } else {
                    throw new Error(data.message || 'Failed to load data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                noResults.style.display = 'block';
                noResults.innerHTML = `<p>Ошибка при загрузке данных: ${error.message}</p>`;
            })
            .finally(() => {
                loadingIndicator.style.display = 'none';
            });
    }

    // Рендеринг таблицы
    function renderPartners(data) {
        tableBody.innerHTML = '';
        
        if (data.length === 0) {
            noResults.style.display = 'block';
            return;
        }

        data.forEach(partner => {
            const row = document.createElement('tr');
            if (partner.is_archived) {
                row.classList.add('archived-row');
            }

            row.innerHTML = `
                <td>${partner.name}</td>
                <td><a href="mailto:${partner.email}">${partner.email}</a></td>
                <td>${partner.phone}</td>
                <td>${partner.contract}</td>
                <td>${formatDate(partner.date_start)} - ${formatDate(partner.date_end)}</td>
                <td>${partner.bank}</td>
                <td class="${partner.is_archived ? 'status-archived' : 'status-active'}">${partner.status}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Форматирование даты
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU');
    }

    // Обработчики событий
    searchInput.addEventListener('input', debounce(() => {
        currentFilter.searchQuery = searchInput.value.trim();
        loadPartners();
    }, 300));

    archiveFilter.addEventListener('change', () => {
        currentFilter.showArchived = archiveFilter.checked;
        localStorage.setItem('showArchived', currentFilter.showArchived);
        loadPartners();
    });

    sortSelect.addEventListener('change', () => {
        const [field, order] = sortSelect.value.split('_');
        currentFilter.sortField = field;
        currentFilter.sortOrder = order.toUpperCase();
        
        localStorage.setItem('sortField', currentFilter.sortField);
        localStorage.setItem('sortOrder', currentFilter.sortOrder);
        
        loadPartners();
    });

    // Функция debounce для оптимизации поиска
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

    // Первоначальная загрузка данных
    loadPartners();
});