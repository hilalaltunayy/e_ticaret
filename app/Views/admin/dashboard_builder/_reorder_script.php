<?php
$reorderGridId = (string) ($reorderGridId ?? '');
$reorderStatusId = (string) ($reorderStatusId ?? '');
$reorderToggleId = (string) ($reorderToggleId ?? '');
$reorderBadgeId = (string) ($reorderBadgeId ?? '');
$reorderEnabledByDefault = (bool) ($reorderEnabledByDefault ?? false);
?>
<script>
  (function () {
    function initBuilderReorder() {
    var grid = document.getElementById('<?= esc($reorderGridId) ?>');
    if (!grid) {
      return;
    }

    var statusBox = document.getElementById('<?= esc($reorderStatusId) ?>');
    var toggleButton = <?= $reorderToggleId !== '' ? "document.getElementById('" . esc($reorderToggleId) . "')" : 'null' ?>;
    var modeBadge = <?= $reorderBadgeId !== '' ? "document.getElementById('" . esc($reorderBadgeId) . "')" : 'null' ?>;
    var reorderEndpoint = '<?= site_url('admin/dashboard-builder/reorder') ?>';
    var csrfTokenName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var draggingItem = null;
    var saveTimer = null;
    var enabled = <?= $reorderEnabledByDefault ? 'true' : 'false' ?>;

    function items() {
      return Array.prototype.slice.call(grid.querySelectorAll('.builder-draggable-item'));
    }

    function setStatus(message, tone) {
      if (!statusBox) {
        return;
      }

      statusBox.className = 'builder-status small mt-3';
      if (tone === 'success') {
        statusBox.classList.add('text-success');
      } else if (tone === 'error') {
        statusBox.classList.add('text-danger');
      } else {
        statusBox.classList.add('text-muted');
      }

      statusBox.textContent = message;
    }

    function setToggleState() {
      if (!toggleButton) {
      } else {
        toggleButton.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        toggleButton.classList.toggle('btn-primary', enabled);
        toggleButton.classList.toggle('btn-outline-primary', !enabled);
        toggleButton.textContent = enabled ? 'Düzenleme Modu Açık' : 'Düzenleme Modu';
      }

      if (!modeBadge) {
        return;
      }

      modeBadge.className = enabled ? 'badge bg-light-success text-success' : 'badge bg-light-warning text-warning';
      modeBadge.textContent = enabled ? 'Düzenleme modu açık' : 'Düzenleme modu kapalı';
    }

    function refreshOrderLabels() {
      items().forEach(function (item, index) {
        var label = item.querySelector('[data-order-label]');
        if (label) {
          label.textContent = '#' + index;
        }
      });
    }

    function payloadFromDom() {
      return items().map(function (item, index) {
        return {
          id: item.getAttribute('data-block-id') || '',
          order_index: index,
          position_x: parseInt(item.getAttribute('data-position-x') || '0', 10) || 0,
          position_y: index
        };
      });
    }

    function persistOrder() {
      var blocks = payloadFromDom();
      var formData = new FormData();
      formData.append('blocks', JSON.stringify(blocks));
      formData.append(csrfTokenName, csrfHash);

      setStatus('Kaydediliyor...', 'muted');

      fetch(reorderEndpoint, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
        .then(function (response) {
          return response.json().then(function (payload) {
            return { ok: response.ok, payload: payload };
          });
        })
        .then(function (result) {
          if (result.payload && result.payload.csrf && result.payload.csrf.hash) {
            csrfHash = result.payload.csrf.hash;
          }

          if (!result.ok || !result.payload.success) {
            throw new Error((result.payload && result.payload.message) || 'Siralama kaydedilemedi.');
          }

          refreshOrderLabels();
          setStatus(result.payload.message || 'Kaydedildi.', 'success');
        })
        .catch(function (error) {
          setStatus(error.message || 'Siralama kaydedilemedi.', 'error');
        });
    }

    function queueSave() {
      window.clearTimeout(saveTimer);
      saveTimer = window.setTimeout(persistOrder, 120);
    }

    function clearDragState() {
      draggingItem = null;
      items().forEach(function (item) {
        item.classList.remove('is-dragging');
        item.classList.remove('drag-over');
      });
    }

    function applyMode() {
      grid.classList.toggle('builder-edit-mode', enabled);
      items().forEach(function (item) {
        item.setAttribute('draggable', enabled ? 'true' : 'false');
      });
      setToggleState();

      if (!enabled) {
        clearDragState();
        setStatus('Düzenleme modu kapalı. Dashboard normal görünümde.', 'muted');
        return;
      }

      setStatus('Bloklari surukleyip birakarak siralayabilirsiniz.', 'muted');
    }

    function bindItem(item) {
      item.addEventListener('dragstart', function (event) {
        if (!enabled) {
          event.preventDefault();
          return;
        }

        draggingItem = item;
        item.classList.add('is-dragging');
        setStatus('Blok yeni konumuna birakildiginda sira kaydedilecek.', 'muted');
      });

      item.addEventListener('dragend', function () {
        clearDragState();
        if (enabled) {
          setStatus('Bloklari surukleyip birakarak siralayabilirsiniz.', 'muted');
        }
      });

      item.addEventListener('dragover', function (event) {
        if (!enabled) {
          return;
        }

        event.preventDefault();
        if (!draggingItem || draggingItem === item) {
          return;
        }

        item.classList.add('drag-over');
      });

      item.addEventListener('dragleave', function () {
        item.classList.remove('drag-over');
      });

      item.addEventListener('drop', function (event) {
        if (!enabled) {
          return;
        }

        event.preventDefault();
        item.classList.remove('drag-over');

        if (!draggingItem || draggingItem === item) {
          return;
        }

        var currentItems = items();
        var draggingIndex = currentItems.indexOf(draggingItem);
        var targetIndex = currentItems.indexOf(item);

        if (draggingIndex < targetIndex) {
          grid.insertBefore(draggingItem, item.nextSibling);
        } else {
          grid.insertBefore(draggingItem, item);
        }

        refreshOrderLabels();
        queueSave();
      });
    }

    items().forEach(bindItem);
    refreshOrderLabels();

    if (toggleButton) {
      toggleButton.addEventListener('click', function (event) {
        event.preventDefault();
        enabled = !enabled;
        applyMode();
      });
    }

    if (items().length < 2) {
      setToggleState();
      setStatus(enabled ? 'Siralama icin en az iki blok gerekli.' : 'Düzenleme modu kapalı. Dashboard normal görünümde.', 'muted');
      items().forEach(function (item) {
        item.setAttribute('draggable', 'false');
      });
      return;
    }

    applyMode();
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initBuilderReorder, { once: true });
      return;
    }

    initBuilderReorder();
  })();
</script>
