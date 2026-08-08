<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Image Gallery ✨</h1>
            <p class="text-sm text-slate-500 mt-1">Manage all product images across the system.</p>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Delete Button -->
            <button id="btnBulkDelete" class="btn bg-rose-500 hover:bg-rose-600 text-white hidden">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V3h4V1c0-.6.4-1 1-1h6c.6 0 1 .4 1 1zM3 5v10h10V5H3zm3-2V2h4v1H6z"/>
                </svg>
                <span class="ml-2">Delete Selected (<span id="selectedCount">0</span>)</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center justify-between mb-4 border-b border-slate-200 pb-4">
        <div class="flex items-center space-x-4">
            <button class="text-sm font-medium text-indigo-500 filter-btn" data-filter="all">All Images</button>
            <button class="text-sm font-medium text-slate-500 hover:text-slate-600 filter-btn" data-filter="used">Used</button>
            <button class="text-sm font-medium text-slate-500 hover:text-slate-600 filter-btn" data-filter="unused">Unused</button>
        </div>
        <div class="flex items-center space-x-2">
            <input type="checkbox" id="selectAll" class="form-checkbox text-indigo-500">
            <label for="selectAll" class="text-sm text-slate-600 cursor-pointer">Select All Visible</label>
        </div>
    </div>

    <!-- Image Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4" id="galleryGrid">
        <?php foreach ($data['images'] as $img): ?>
            <div class="bg-white shadow-sm rounded-sm border border-slate-200 overflow-hidden gallery-item relative group" data-status="<?php echo $img['is_used'] ? 'used' : 'unused'; ?>" data-filename="<?php echo htmlspecialchars($img['filename']); ?>">
                <!-- Checkbox -->
                <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                    <input type="checkbox" class="image-checkbox form-checkbox text-indigo-500 w-5 h-5 bg-white border-slate-300 rounded shadow-sm" value="<?php echo htmlspecialchars($img['filename']); ?>">
                </div>

                <!-- Status Badge -->
                <div class="absolute top-2 right-2 z-10">
                    <?php if ($img['is_used']): ?>
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Used</span>
                    <?php else: ?>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">Unused</span>
                    <?php endif; ?>
                </div>

                <!-- Image -->
                <div class="aspect-w-1 aspect-h-1 w-full bg-slate-100 cursor-pointer img-preview" data-url="<?php echo $img['url']; ?>" data-name="<?php echo htmlspecialchars($img['filename']); ?>">
                    <img src="<?php echo $img['url']; ?>" alt="<?php echo htmlspecialchars($img['filename']); ?>" class="w-full h-full object-contain" loading="lazy">
                </div>

                <!-- Details -->
                <div class="p-3 border-t border-slate-200">
                    <p class="text-xs font-medium text-slate-800 truncate" title="<?php echo htmlspecialchars($img['filename']); ?>">
                        <?php echo htmlspecialchars($img['filename']); ?>
                    </p>
                    <p class="text-[10px] text-slate-500 mt-1">
                        <?php echo number_format($img['size'] / 1024, 1); ?> KB &middot; <?php echo date('M d, Y', $img['upload_date']); ?>
                    </p>
                    
                    <?php if ($img['is_used'] && !empty($img['usages'])): ?>
                        <div class="mt-2 text-[10px] text-slate-600 bg-slate-50 p-1.5 rounded border border-slate-100 max-h-16 overflow-y-auto custom-scrollbar">
                            <?php foreach ($img['usages'] as $usage): ?>
                                <div class="truncate" title="<?php echo htmlspecialchars($usage['name']); ?>">
                                    <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($usage['type']); ?>:</span> 
                                    <?php echo htmlspecialchars($usage['name']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($data['images'])): ?>
            <div class="col-span-full py-12 text-center text-slate-500">
                <i class="fa-regular fa-image text-4xl mb-3 text-slate-300"></i>
                <p>No images found in the uploads directory.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900 bg-opacity-75 transition-opacity">
    <div class="relative bg-white rounded shadow-lg max-w-4xl w-full max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center p-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800 truncate" id="previewTitle">Image Preview</h3>
            <button id="closePreviewBtn" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                    <path d="M18.3 5.71a.996.996 0 00-1.41 0L12 10.59 7.11 5.7a.996.996 0 10-1.41 1.41L10.59 12 5.7 16.89a.996.996 0 101.41 1.41L12 13.41l4.89 4.89a.996.996 0 101.41-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/>
                </svg>
            </button>
        </div>
        <div class="p-4 flex-1 overflow-auto flex items-center justify-center bg-slate-50">
            <img id="previewImg" src="" alt="Preview" class="max-w-full max-h-full object-contain shadow-sm">
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
    const items = document.querySelectorAll('.gallery-item');
    
    // Filtering logic
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;
            
            // Update active state
            filterBtns.forEach(b => {
                b.classList.remove('text-indigo-500');
                b.classList.add('text-slate-500', 'hover:text-slate-600');
            });
            btn.classList.add('text-indigo-500');
            btn.classList.remove('text-slate-500', 'hover:text-slate-600');
            
            items.forEach(item => {
                if (filter === 'all' || item.dataset.status === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                    // Uncheck if hidden
                    const cb = item.querySelector('.image-checkbox');
                    if(cb.checked) {
                        cb.checked = false;
                    }
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
        
        // Keep checkboxes visible if checked
        checkboxes.forEach(cb => {
            const container = cb.closest('div');
            if(cb.checked) {
                container.classList.remove('opacity-0', 'group-hover:opacity-100');
                container.classList.add('opacity-100');
            } else {
                container.classList.add('opacity-0', 'group-hover:opacity-100');
                container.classList.remove('opacity-100');
            }
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    selectAllCb.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        items.forEach(item => {
            if (item.style.display !== 'none') {
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
        
        // Check if any used images are selected to show an extra warning
        let hasUsed = false;
        checked.forEach(cb => {
            if (cb.closest('.gallery-item').dataset.status === 'used') {
                hasUsed = true;
            }
        });
        
        let msg = `Are you sure you want to permanently delete ${checked.length} image(s)?`;
        if (hasUsed) {
            msg += `\n\nWARNING: You have selected USED images. Deleting them will permanently remove them from the server and unlink them from all associated products. This action cannot be undone.`;
        }
        
        if (!confirm(msg)) return;
        
        const files = Array.from(checked).map(cb => cb.value);
        
        // CSRF Token - required by App router
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
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
                // Remove elements from DOM
                checked.forEach(cb => {
                    cb.closest('.gallery-item').remove();
                });
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
        });
    });

    // Preview Logic
    const modal = document.getElementById('previewModal');
    const previewImg = document.getElementById('previewImg');
    const previewTitle = document.getElementById('previewTitle');
    const closeBtn = document.getElementById('closePreviewBtn');

    document.querySelectorAll('.img-preview').forEach(preview => {
        preview.addEventListener('click', (e) => {
            // Prevent opening preview if clicking on checkbox
            if(e.target.closest('.image-checkbox') || e.target.closest('input')) return;
            
            previewImg.src = preview.dataset.url;
            previewTitle.textContent = preview.dataset.name;
            modal.classList.remove('hidden');
        });
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>
