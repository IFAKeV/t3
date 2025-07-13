// Erweiterte Funktionalität für die Kontaktsuche ohne jQuery
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('contact_search');
    if (!searchInput) return;

    const list = document.createElement('ul');
    list.className = 'autocomplete-list';
    searchInput.parentNode.style.position = 'relative';
    searchInput.parentNode.appendChild(list);

    function hideList() {
        list.style.display = 'none';
    }

    function showList(items) {
        list.innerHTML = '';
        items.forEach(function(item) {
            const li = document.createElement('li');
            li.className = 'autocomplete-item';
            li.innerHTML =
                '<div class="autocomplete-name">' + item.name + '</div>' +
                (item.email ? '<div class="autocomplete-email">' + item.email + '</div>' : '') +
                (item.organization_info ? '<div class="organization-info">' + item.organization_info + '</div>' : '');
            li.dataset.item = JSON.stringify(item);
            list.appendChild(li);
        });
        list.style.display = items.length ? 'block' : 'none';
    }

    searchInput.addEventListener('input', function() {
        const term = searchInput.value.trim();
        if (term.length < 2) {
            hideList();
            return;
        }
        const apiBase = window.PHP_BASE ? window.PHP_BASE : '';
        fetch(apiBase + '/api/search_employees.php?term=' + encodeURIComponent(term))
            .then(function(resp) { return resp.json(); })
            .then(function(data) { showList(data); })
            .catch(function(err) { console.error('Fehler bei der Suche:', err); hideList(); });
    });

    list.addEventListener('click', function(e) {
        const li = e.target.closest('li');
        if (!li) return;
        const item = JSON.parse(li.dataset.item);
        document.getElementById('contact_name').value = item.name;
        document.getElementById('contact_phone').value = item.phone || '';
        document.getElementById('contact_email').value = item.email || '';
        document.getElementById('contact_employee_id').value = item.id || '';
        document.getElementById('facility_id').value = item.facility_id || '';
        document.getElementById('location_id').value = item.location_id || '';
        document.getElementById('department_id').value = item.department_id || '';

        const info =
            '<p><strong>' + item.name + '</strong></p>' +
            (item.phone ? '<p>Tel: ' + item.phone + '</p>' : '') +
            (item.email ? '<p>E-Mail: ' + item.email + '</p>' : '') +
            (item.organization_info ? '<p class="organization-info">' + item.organization_info + '</p>' : '');
        document.getElementById('selected_contact_info').innerHTML = info;
        document.getElementById('contact_details').style.display = 'block';

        searchInput.value = '';
        hideList();
    });

    document.addEventListener('click', function(e) {
        if (!list.contains(e.target) && e.target !== searchInput) {
            hideList();
        }
    });
});
