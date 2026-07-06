<?php if (isset($pagination)): 
    $p = $pagination;
?>
<div class="pagination-bar" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; border-top: 0.5px solid var(--c-separator); background: var(--c-surface2); flex-shrink: 0; width: 100%; box-sizing: border-box;">
    <button type="button" onclick="changePage(<?= $p['current_page'] - 1 ?>)" <?= $p['current_page'] <= 1 ? 'disabled' : '' ?> class="pag-btn" style="padding: 6px 12px; border: 0.5px solid var(--c-separator); background: var(--c-surface); color: var(--c-blue); border-radius: var(--r-xs); font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all var(--dur-fast);">
        <i class="ph ph-caret-left"></i> Prev
    </button>
    <span style="font-size: 12px; font-weight: 500; color: var(--t-secondary);">
        Page <strong><?= $p['current_page'] ?></strong> of <strong><?= $p['total_pages'] ?></strong>
    </span>
    <button type="button" onclick="changePage(<?= $p['current_page'] + 1 ?>)" <?= $p['current_page'] >= $p['total_pages'] ? 'disabled' : '' ?> class="pag-btn" style="padding: 6px 12px; border: 0.5px solid var(--c-separator); background: var(--c-surface); color: var(--c-blue); border-radius: var(--r-xs); font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all var(--dur-fast);">
        Next <i class="ph ph-caret-right"></i>
    </button>
</div>
<?php endif; ?>
