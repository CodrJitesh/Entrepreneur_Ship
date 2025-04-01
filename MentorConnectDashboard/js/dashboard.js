// Theme toggle functionality
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;

// Check for saved theme preference in localStorage
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    body.classList.add(savedTheme);
    themeToggle.textContent = savedTheme === 'dark-mode' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
}

// Toggle theme on button click
themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDarkMode = body.classList.contains('dark-mode');
    themeToggle.textContent = isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    localStorage.setItem('theme', isDarkMode ? 'dark-mode' : '');
});

// Load mentors
fetch('backend/get_mentors.php')
    .then(response => response.json())
    .then(data => {
        const mentorGrid = document.getElementById('mentor-grid');
        data.forEach(mentor => {
            const mentorCard = document.createElement('div');
            mentorCard.classList.add('mentor-card');
            mentorCard.innerHTML = `
                <img src="${mentor.profile_image}" alt="${mentor.name}">
                <h3>${mentor.name}</h3>
                <p>${mentor.expertise}</p>
                <p>${mentor.experience}</p>
            `;
            mentorGrid.appendChild(mentorCard);
        });
    })
    .catch(error => console.error('Error loading mentors:', error));

// Load user progress
fetch('backend/get_progress.php')
    .then(response => response.json())
    .then(data => {
        const progressList = document.getElementById('progress-list');
        data.forEach(item => {
            const progressItem = document.createElement('div');
            progressItem.classList.add('progress-item');
            if (item.completed) {
                progressItem.classList.add('completed');
            }
            progressItem.innerHTML = `
                <span>${item.milestone}</span>
                <span>${item.completed ? 'Completed' : 'In Progress'}</span>
            `;
            progressList.appendChild(progressItem);
        });
    })
    .catch(error => console.error('Error loading progress:', error));