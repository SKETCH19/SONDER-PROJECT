document.addEventListener('DOMContentLoaded', () => {
    const demoForms = document.querySelectorAll('[data-demo-form]');
    demoForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            alert('Demo estatica: esta accion requiere un backend en PHP.');
        });
    });

    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    const menuItems = document.querySelectorAll('.menu-item');
    if (menuItems.length > 0) {
        menuItems.forEach((item) => {
            item.addEventListener('click', () => {
                const section = item.getAttribute('data-section');
                if (section) {
                    showSection(section);
                }
            });
        });
    }

    setupFriendTabs();

    function showSection(sectionName) {
        document.querySelectorAll('.chat-area, .content-area').forEach((section) => {
            section.style.display = 'none';
        });

        const target = document.getElementById(sectionName + '-section');
        if (target) {
            target.style.display = 'flex';
        }

        document.querySelectorAll('.menu-item').forEach((item) => {
            item.classList.remove('active');
            if (item.getAttribute('data-section') === sectionName) {
                item.classList.add('active');
            }
        });

        if (sidebar) {
            sidebar.classList.remove('open');
        }
    }

    function setupFriendTabs() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        if (tabBtns.length === 0 || tabContents.length === 0) {
            return;
        }

        tabBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const tabName = btn.getAttribute('data-tab');

                tabBtns.forEach((tab) => tab.classList.remove('active'));
                tabContents.forEach((content) => content.classList.remove('active'));

                btn.classList.add('active');
                const target = document.getElementById(tabName);
                if (target) {
                    target.classList.add('active');
                }
            });
        });
    }
});
