<!-- Premium Glassmorphic Loading Overlay -->
<div id="saveLoadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:99999; flex-direction:column; align-items:center; justify-content:center; transition: opacity 0.3s ease;">
    <div style="background:#fff; padding:30px 40px; border-radius:16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border:1px solid rgba(0,0,0,0.05); display:flex; flex-direction:column; align-items:center; gap:20px; max-width: 400px; text-align: center;">
        <div class="spinner-resilient" style="width:45px; height:45px; border:4px solid #e0eaf5; border-top:4px solid #0066cc; border-radius:50%; animation:spin-resilient 0.8s linear infinite;"></div>
        <div style="display:flex; flex-direction:column; gap:6px;">
            <h4 style="margin:0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size:16px; font-weight:700; color:#111;">Saving Record Safely</h4>
            <p style="margin:0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size:12px; color:#666; line-height:1.4;">Connecting to server and writing transaction ledger. Please do not close or refresh this page.</p>
        </div>
    </div>
</div>
<style>
@keyframes spin-resilient {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spinner-resilient {
    box-sizing: border-box;
}
</style>
<script>
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Ignore GET forms, target="blank", or forms marked to ignore
    if (form.tagName !== 'FORM' || 
        form.method.toUpperCase() === 'GET' || 
        form.getAttribute('target') === '_blank' || 
        form.getAttribute('data-ajax-ignore') === 'true' ||
        form.getAttribute('data-resilient') === 'false') {
        return;
    }

    // If another script already intercepted and prevented default, ignore
    if (e.defaultPrevented) {
        return;
    }

    e.preventDefault();

    const overlay = document.getElementById('saveLoadingOverlay');
    if (overlay) overlay.style.display = 'flex';

    // Construct FormData capturing submitter button if present
    const formData = new FormData(form);
    const submitter = e.submitter;
    if (submitter && submitter.name) {
        formData.append(submitter.name, submitter.value);
    }

    fetch(form.action || window.location.href, {
        method: form.method || 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned HTTP status: ' + response.status);
        }

        // Check if it's a file download
        const disposition = response.headers.get('content-disposition');
        const contentType = response.headers.get('content-type') || '';
        if ((disposition && disposition.indexOf('attachment') !== -1) || 
            contentType.indexOf('csv') !== -1 || 
            contentType.indexOf('octet-stream') !== -1 ||
            contentType.indexOf('excel') !== -1 ||
            contentType.indexOf('spreadsheet') !== -1) {
            
            return response.blob().then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                let filename = 'download';
                const match = disposition ? disposition.match(/filename="?([^"]+)"?/) : null;
                if (match && match[1]) filename = match[1];
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                if (overlay) overlay.style.display = 'none';
            });
        }

        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.text().then(html => {
                document.open();
                document.write(html);
                document.close();
            });
        }
    })
    .catch(error => {
        if (overlay) overlay.style.display = 'none';
        console.error('Safe save failed:', error);
        alert('⚠ Save Failed: A network or connection issue occurred. Please check your internet connection and try again. Your typed data has been kept safe.');
    });
});
</script>
