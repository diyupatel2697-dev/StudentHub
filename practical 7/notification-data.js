let notifications = [];
let currentPage = 1;
const notificationsPerPage = 4;

const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const sortSelect = document.getElementById('sortSelect');
const notificationList = document.getElementById('notificationList');
const pagination = document.getElementById('pagination');

fetch('notifications.json')
    .then(response => {
        if (!response.ok) {
            throw new Error('Unable to load notifications.json');
        }

        return response.json();
    })
    .then(data => {
        notifications = data;
        displayNotifications();
    })
    .catch(error => {
        notificationList.innerHTML =
            '<p class="no-results">Unable to load notifications.</p>';

        console.error(error);
    });

function displayNotifications() {

    const searchText = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value;
    const selectedSort = sortSelect.value;

    let filteredNotifications = notifications.filter(notification => {

        const matchesSearch =
            notification.title.toLowerCase().includes(searchText);

        const matchesCategory =
            selectedCategory === '' ||
            notification.category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    if (selectedSort === 'dateNewest') {

        filteredNotifications.sort((a, b) =>
            new Date(b.date) - new Date(a.date)
        );
    }

    if (selectedSort === 'dateOldest') {

        filteredNotifications.sort((a, b) =>
            new Date(a.date) - new Date(b.date)
        );
    }

    if (selectedSort === 'titleAsc') {

        filteredNotifications.sort((a, b) =>
            a.title.localeCompare(b.title)
        );
    }

    if (selectedSort === 'titleDesc') {

        filteredNotifications.sort((a, b) =>
            b.title.localeCompare(a.title)
        );
    }

    const totalPages =
        Math.ceil(filteredNotifications.length / notificationsPerPage);

    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
    }

    const startIndex =
        (currentPage - 1) * notificationsPerPage;

    const endIndex =
        startIndex + notificationsPerPage;

    const pageNotifications =
        filteredNotifications.slice(startIndex, endIndex);

    notificationList.innerHTML = '';

    if (pageNotifications.length === 0) {

        notificationList.innerHTML =
            '<p class="no-results">No notifications found.</p>';

        pagination.innerHTML = '';

        return;
    }

    pageNotifications.forEach(notification => {

        const card = document.createElement('div');

        card.className = 'notification-card';

        card.innerHTML = `
            <h3>${notification.title}</h3>
            <p><strong>Category:</strong> ${notification.category}</p>
            <p><strong>Date:</strong> ${notification.date}</p>
            <p><strong>Priority:</strong> ${notification.priority}</p>
        `;

        notificationList.appendChild(card);
    });

    createPagination(totalPages);
}

function createPagination(totalPages) {

    pagination.innerHTML = '';

    const previousButton =
        document.createElement('button');

    previousButton.textContent = 'Previous';

    previousButton.disabled =
        currentPage === 1;

    previousButton.addEventListener('click', () => {

        if (currentPage > 1) {
            currentPage--;
            displayNotifications();
        }
    });

    pagination.appendChild(previousButton);

    for (let i = 1; i <= totalPages; i++) {

        const pageButton =
            document.createElement('button');

        pageButton.textContent = i;

        if (i === currentPage) {
            pageButton.classList.add('active');
        }

        pageButton.addEventListener('click', () => {

            currentPage = i;
            displayNotifications();
        });

        pagination.appendChild(pageButton);
    }

    const nextButton =
        document.createElement('button');

    nextButton.textContent = 'Next';

    nextButton.disabled =
        currentPage === totalPages;

    nextButton.addEventListener('click', () => {

        if (currentPage < totalPages) {
            currentPage++;
            displayNotifications();
        }
    });

    pagination.appendChild(nextButton);
}

searchInput.addEventListener('input', () => {

    currentPage = 1;
    displayNotifications();
});

categoryFilter.addEventListener('change', () => {

    currentPage = 1;
    displayNotifications();
});

sortSelect.addEventListener('change', () => {

    currentPage = 1;
    displayNotifications();
});