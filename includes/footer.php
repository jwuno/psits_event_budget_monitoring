<footer style="text-align:center; padding:15px; color:#555;">
  &copy; 2025 PSITS Event Budget Monitoring System
</footer>

<script>
const profileIcon = document.getElementById('profileIcon');
const dropdownMenu = document.getElementById('dropdownMenu');
profileIcon.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
window.addEventListener('click', e => {
  if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
    dropdownMenu.classList.remove('show');
  }
});
</script>
</body>
</html>
