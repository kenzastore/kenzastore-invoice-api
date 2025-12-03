</div> <!-- End main-container -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const darkModeToggle = document.getElementById("darkModeToggle");
        const darkModeIcon = document.getElementById("darkModeIcon");
        const body = document.body;
        
        // Initial check
        if (localStorage.getItem("darkMode") === "enabled") {
            body.classList.add("dark-mode");
            if(darkModeIcon) darkModeIcon.classList.replace("fa-moon", "fa-sun");
        }

        if(darkModeToggle) {
            darkModeToggle.addEventListener("click", function () {
                body.classList.toggle("dark-mode");
                if (body.classList.contains("dark-mode")) {
                    localStorage.setItem("darkMode", "enabled");
                    if(darkModeIcon) darkModeIcon.classList.replace("fa-moon", "fa-sun");
                } else {
                    localStorage.setItem("darkMode", "disabled");
                    if(darkModeIcon) darkModeIcon.classList.replace("fa-sun", "fa-moon");
                }
            });
        }
    });
</script>
</body>
</html>
