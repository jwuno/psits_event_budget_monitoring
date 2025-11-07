// File upload helper functions
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('attachment');
    
    if (fileInput) {
        const filePreview = document.createElement('div');
        filePreview.className = 'file-preview';
        fileInput.parentNode.appendChild(filePreview);

        fileInput.addEventListener('change', function(e) {
            filePreview.innerHTML = '';
            
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileInfo = document.createElement('div');
                fileInfo.className = 'file-info';
                fileInfo.innerHTML = `
                    <strong>Selected File:</strong> ${file.name}<br>
                    <strong>Size:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
                    <strong>Type:</strong> ${file.type || 'Unknown'}
                `;
                filePreview.appendChild(fileInfo);
            }
        });
    }
});