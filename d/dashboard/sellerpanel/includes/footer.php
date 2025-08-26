<script>
  // Simple animation for form elements
  document.addEventListener('DOMContentLoaded', function() {
    const formGroups = document.querySelectorAll('.form-group');
    
    formGroups.forEach((group, index) => {
      group.style.animationDelay = `${index * 0.1 + 0.1}s`;
      group.classList.add('animate-fadeIn');
    });
    
    // File upload hover effect
    const fileUpload = document.querySelector('.file-upload');
    const fileInput = document.querySelector('.file-input');
    
    fileInput.addEventListener('change', function() {
      if(this.files.length > 0) {
        fileUpload.innerHTML = `
          <i class="fa-solid fa-check-circle text-success text-4xl"></i>
          <div class="file-upload-text text-center">
            <h3 class="text-lg text-dark">${this.files.length} file(s) selected</h3>
            <p class="text-sm text-gray">${this.files[0].name}</p>
          </div>
        `;
      }
    });

    // Client-side form validation
    const productForm = document.getElementById('productForm');
    if(productForm) {
      productForm.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('part_image');
        if (fileInput.files.length === 0) {
          alert('Please select a product image');
          e.preventDefault();
          return false;
        }
        return true;
      });
    }
  });
</script>
</body>
</html>