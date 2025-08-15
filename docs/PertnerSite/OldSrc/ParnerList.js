document.addEventListener('DOMContentLoaded', function() {
    const partnerTable = document.getElementById('partnerTable').getElementsByTagName('tbody')[0];
    const editBtn = document.getElementById('editBtn');
    const blockBtn = document.getElementById('blockBtn');
    const newBtn = document.getElementById('newBtn');
    const shownCount = document.getElementById('shownCount');
    const totalCount = document.getElementById('totalCount');
    
    let selectedRow = null;
    let partners = [];

    // Load partners data
    fetch('users.json')
        .then(response => response.json())
        .then(data => {
            partners = data.filter(user => user.is_admin !== 1);
            displayPartners(partners);
            updateCounters();
        })
        .catch(error => console.error('Ошибка загрузки партнеров:', error));

    function displayPartners(partners) {
        partnerTable.innerHTML = '';
        
        partners.forEach((partner, index) => {
            const row = partnerTable.insertRow();
            row.dataset.id = partner.id;
            
            const contractPeriod = `${partner.dog_beg_date} - ${partner.dog_end_date}`;
            const status = Math.random() > 0.3 ? 'Активен' : 'Заблокирован';
            const statusClass = status === 'Активен' ? 'status-active' : 'status-blocked';
            
            row.innerHTML = `
                <td>${partner.id}</td>
                <td title="${partner.name}">${partner.name}</td>
                <td title="${partner.login}">${partner.login}</td>
                <td title="${partner.email}"><a href="mailto:${partner.email}">${partner.email}</a></td>
                <td title="${partner.phone}"><a href="tel:${partner.phone.replace(/\D/g, '')}">${partner.phone}</a></td>
                <td>${partner.dog_num}</td>
                <td>${contractPeriod}</td>
            `;
            
            row.addEventListener('click', function() {
                const rows = partnerTable.getElementsByTagName('tr');
                for (let r of rows) {
                    r.classList.remove('selected');
                }
                
                this.classList.add('selected');
                selectedRow = this;
                editBtn.disabled = false;
                blockBtn.disabled = false;
            });
        });
    }

    function updateCounters() {
        shownCount.textContent = partners.length;
        totalCount.textContent = partners.length;
    }

    newBtn.addEventListener('click', function() {
        alert('Функция "Новый партнер" будет реализована позже');
    });
    
    editBtn.addEventListener('click', function() {
        if (selectedRow) {
            const partnerId = selectedRow.dataset.id;
            const partner = partners.find(p => p.id == partnerId);
            alert(`Редактирование партнера: ${partner.name}\nID: ${partner.id}`);
        }
    });
    
    blockBtn.addEventListener('click', function() {
        if (selectedRow) {
            const partnerId = selectedRow.dataset.id;
            const partner = partners.find(p => p.id == partnerId);
            if (confirm(`Вы уверены, что хотите заблокировать партнера ${partner.name}?`)) {
                alert(`Партнер ${partner.name} заблокирован`);
            }
        }
    });
});