    </div> <!-- Close container -->
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Machemba Accessories. All rights reserved.</p>
        <p>Email: info@machemba.com | Phone: +255 747 015 150</p>
    </div>
    
    <script>
    // JavaScript for form validation
    function validateForm() {
        var inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
        for(var i = 0; i < inputs.length; i++) {
            if(inputs[i].value === "") {
                alert("Please fill in all required fields");
                inputs[i].focus();
                return false;
            }
        }
        return true;
    }
    
    function confirmAction(message) {
        return confirm(message || "Are you sure?");
    }
    </script>
</body>
</html>