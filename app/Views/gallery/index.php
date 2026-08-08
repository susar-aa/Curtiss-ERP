<?php
// ==========================================
// IMAGE GALLERY (PREMIUM DESIGN)
// ==========================================

$galleryImages = $data['images'] ?? [];
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — GALLERY
   ============================================================ */

:root {
    /* True iOS system palette */
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.12);
    --c-fill2:        rgba(120,120,128,0.16);
    --c-separator:    rgba(60,60,67,0.12);
    
    /* iOS system colors */
    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    /* Typography */
    --f-system: -apple-system, 'SF Pro Display', 'Inter', sans-serif;
    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    
    /* Elevation */
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    
    /* Geometry */
    --r-sm: 10px;
    --r-md: 14px;
}

/* Reset & Scoped Wrapper */
.gallery-wrapper {
    font-family: var(--f-system);
    color: var(--t-primary);
    padding: 24px;
    max-width: 1400px;
    margin: 0 auto;
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header */
.gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
}
.gallery-title h1 {
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin: 0 0 4px 0;
}
.gallery-title p {
    color: var(--t-secondary);
    font-size: 14px;
    margin: 0;
}

/* Controls */
.gallery-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--c-surface);
    padding: 12px 16px;
    border-radius: var(--r-md);
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
    border: 1px solid var(--c-separator);
}

.filter-tabs {
    display: flex;
    gap: 8px;
}

.filter-btn {
    background: transparent;
    border: none;
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 14px;
    font-weight: 500;
    color: var(--t-secondary);
    cursor: pointer;
    transition: all 0.2s;
}
.filter-btn.active {
    background: var(--c-fill);
    color: var(--t-primary);
}
.filter-btn:hover:not(.active) {
    background: var(--c-fill2);
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 16px;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--t-secondary);
    cursor: pointer;
}

.btn-delete {
    background: var(--c-red-light);
    color: var(--c-red);
    border: 1px solid rgba(255,59,48,0.2);
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-delete:hover {
    background: var(--c-red);
    color: #fff;
}
.btn-delete.hidden {
    display: none;
}

/* Grid */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

/* Card */
.gallery-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 1px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}
.gallery-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.gallery-card.hidden {
    display: none;
}

/* Image Checkbox */
.card-checkbox {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    opacity: 0;
    transform: scale(0.9);
    transition: all 0.2s;
    width: 20px;
    height: 20px;
    cursor: pointer;
}
.gallery-card:hover .card-checkbox,
.card-checkbox:checked {
    opacity: 1;
    transform: scale(1);
}

/* Badges */
.card-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    padding: 2px 8px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.badge-used {
    background: var(--c-green-light);
    color: var(--c-green);
    border: 1px solid rgba(52, 199, 89, 0.2);
}
.badge-unused {
    background: var(--c-fill);
    color: var(--t-secondary);
    border: 1px solid var(--c-separator);
}

/* Image Container */
.card-img-wrapper {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: var(--c-surface2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: zoom-in;
    position: relative;
    border-bottom: 1px solid var(--c-separator);
}
.card-img-wrapper img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    padding: 10px;
}

/* Card Info */
.card-info {
    padding: 12px;
}
.card-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--t-primary);
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.card-meta {
    font-size: 11px;
    color: var(--t-secondary);
    margin: 0;
    display: flex;
    justify-content: space-between;
}

.card-usages {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed var(--c-separator);
    max-height: 60px;
    overflow-y: auto;
    font-size: 11px;
    color: var(--t-secondary);
}
.card-usages div {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.card-usages span {
    font-weight: 600;
    color: var(--t-primary);
}

/* Custom Scrollbar for usages */
.card-usages::-webkit-scrollbar { width: 4px; }
.card-usages::-webkit-scrollbar-track { background: transparent; }
.card-usages::-webkit-scrollbar-thumb { background: var(--c-separator); border-radius: 4px; }

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 1px dashed var(--c-separator);
    color: var(--t-secondary);
}
.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}
.empty-state p {
    margin: 0;
    font-size: 15px;
}

/* Modal */
.preview-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.8);
    backdrop-filter: blur(8px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}
.preview-modal.active {
    opacity: 1;
    pointer-events: auto;
}
.preview-content {
    background: var(--c-surface);
    border-radius: var(--r-md);
    width: 90vw;
    max-width: 900px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.95);
    transition: transform 0.3s;
}
.preview-modal.active .preview-content {
    transform: scale(1);
}
.preview-header {
    padding: 16px;
    border-bottom: 1px solid var(--c-separator);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.preview-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}
.preview-close {
    background: var(--c-fill);
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--t-secondary);
}
.preview-close:hover {
    background: var(--c-fill2);
}
.preview-body {
    flex: 1;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--c-surface2);
    overflow: auto;
}
.preview-body img {
    max-width: 100%;
    max-height: calc(90vh - 120px);
    object-fit: contain;
    box-shadow: var(--shadow-sm);
}
</style>

<div class="gallery-wrapper">
    <!-- Header -->
    <div class="gallery-header">
        <div class="gallery-title">
            <h1>Image Gallery ✨</h1>
            <p>Manage all product and variation images across the system.</p>
        </div>
    </div>

    <!-- Controls -->
    <div class="gallery-controls">
        <div class="filter-tabs">
            <button class="filter-btn active" data-filter="all">All Images</button>
            <button class="filter-btn" data-filter="used">Used</button>
            <button class="filter-btn" data-filter="unused">Unused</button>
        </div>
        
        <div class="action-buttons">
            <label class="checkbox-wrapper">
                <input type="checkbox" id="selectAll" style="width:16px;height:16px;">
                Select All Visible
            </label>
            
            <button id="btnBulkDelete" class="btn-delete hidden">
                <i class="fa-solid fa-trash-can"></i>
                Delete Selected (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>

    <!-- Grid -->
    <div class="gallery-grid" id="galleryGrid">
        <?php foreach ($galleryImages as $img): ?>
            <div class="gallery-card" data-status="<?php echo $img['is_used'] ? 'used' : 'unused'; ?>" data-filename="<?php echo htmlspecialchars($img['filename']); ?>">
                <!-- Checkbox -->
                <input type="checkbox" class="card-checkbox image-checkbox" value="<?php echo htmlspecialchars($img['filename']); ?>">
                
                <!-- Badge -->
                <?php if ($img['is_used']): ?>
                    <span class="card-badge badge-used">Used</span>
                <?php else: ?>
                    <span class="card-badge badge-unused">Unused</span>
                <?php endif; ?>

                <!-- Image -->
                <div class="card-img-wrapper img-preview" data-url="<?php echo $img['url']; ?>" data-name="<?php echo htmlspecialchars($img['filename']); ?>">
                    <img src="<?php echo $img['url']; ?>" alt="<?php echo htmlspecialchars($img['filename']); ?>" loading="lazy">
                </div>

                <!-- Info -->
                <div class="card-info">
                    <h4 class="card-title" title="<?php echo htmlspecialchars($img['filename']); ?>">
                        <?php echo htmlspecialchars($img['filename']); ?>
                    </h4>
                    <p class="card-meta">
                        <span><?php echo number_format($img['size'] / 1024, 1); ?> KB</span>
                        <span><?php echo date('M d, Y', $img['upload_date']); ?></span>
                    </p>
                    
                    <?php if ($img['is_used'] && !empty($img['usages'])): ?>
                        <div class="card-usages">
                            <?php foreach ($img['usages'] as $usage): ?>
                                <div title="<?php echo htmlspecialchars($usage['name']); ?>">
                                    <span><?php echo htmlspecialchars($usage['type']); ?>:</span> 
                                    <?php echo htmlspecialchars($usage['name']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($galleryImages)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-image"></i>
                <p>No images found in the uploads directory.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal">
    <div class="preview-content">
        <div class="preview-header">
            <h3 id="previewTitle">Image Preview</h3>
            <button id="closePreviewBtn" class="preview-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="preview-body">
            <img id="previewImg" src="" alt="Preview">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    const selectAllCb = document.getElementById('selectAll');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCountSpan = document.getElementById('selectedCount');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.gallery-card');
    
    // Filtering
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;
            
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            items.forEach(item => {
                if (filter === 'all' || item.dataset.status === filter) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                    // Uncheck if hidden
                    const cb = item.querySelector('.image-checkbox');
                    if(cb.checked) cb.checked = false;
                }
            });
            
            updateSelectedCount();
            selectAllCb.checked = false;
        });
    });

    // Selection logic
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.image-checkbox:checked');
        selectedCountSpan.textContent = checked.length;
        if (checked.length > 0) {
            btnBulkDelete.classList.remove('hidden');
        } else {
            btnBulkDelete.classList.add('hidden');
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    selectAllCb.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        items.forEach(item => {
            if (!item.classList.contains('hidden')) {
                const cb = item.querySelector('.image-checkbox');
                cb.checked = isChecked;
            }
        });
        updateSelectedCount();
    });

    // Delete Logic
    btnBulkDelete.addEventListener('click', () => {
        const checked = document.querySelectorAll('.image-checkbox:checked');
        if (checked.length === 0) return;
        
        let hasUsed = false;
        checked.forEach(cb => {
            if (cb.closest('.gallery-card').dataset.status === 'used') {
                hasUsed = true;
            }
        });
        
        let msg = `Are you sure you want to permanently delete ${checked.length} image(s)?`;
        if (hasUsed) {
            msg += `\n\nWARNING: You have selected USED images. Deleting them will permanently remove them from the server and unlink them from all associated products. This action cannot be undone.`;
        }
        
        if (!confirm(msg)) return;
        
        const files = Array.from(checked).map(cb => cb.value);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        // Show loading state
        const originalText = btnBulkDelete.innerHTML;
        btnBulkDelete.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
        btnBulkDelete.disabled = true;
        
        fetch('<?php echo APP_URL; ?>/gallery/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ files: files, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                checked.forEach(cb => cb.closest('.gallery-card').remove());
                updateSelectedCount();
                selectAllCb.checked = false;
                alert(data.message);
            } else {
                alert(data.message || 'Error deleting files');
            }
        })
        .catch(err => {
            console.error(err);
            alert('A network error occurred.');
        })
        .finally(() => {
            btnBulkDelete.innerHTML = originalText;
            btnBulkDelete.disabled = false;
        });
    });

    // Preview Logic
    const modal = document.getElementById('previewModal');
    const previewImg = document.getElementById('previewImg');
    const previewTitle = document.getElementById('previewTitle');
    const closeBtn = document.getElementById('closePreviewBtn');

    document.querySelectorAll('.img-preview').forEach(preview => {
        preview.addEventListener('click', (e) => {
            if(e.target.closest('.image-checkbox') || e.target.closest('input')) return;
            previewImg.src = preview.dataset.url;
            previewTitle.textContent = preview.dataset.name;
            modal.classList.add('active');
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('active'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });
});
</script>
