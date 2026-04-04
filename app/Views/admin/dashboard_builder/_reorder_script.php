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
    var resizeEndpoint = '<?= site_url('admin/dashboard-builder/resize') ?>';
    var csrfTokenName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var draggingItem = null;
    var saveTimer = null;
    var resizeState = null;
    var enabled = <?= $reorderEnabledByDefault ? 'true' : 'false' ?>;
    var minWidthUnits = 2;
    var maxWidthUnits = 12;
    var minHeightUnits = 1;
    var maxHeightUnits = 6;
    var heightUnitPixels = 170;

    function items() {
      return Array.prototype.slice.call(grid.querySelectorAll('.builder-draggable-item'));
    }

    function clamp(value, min, max) {
      return Math.max(min, Math.min(max, value));
    }

    function currentWidth(item) {
      return clamp(parseInt(item.getAttribute('data-width') || '4', 10) || 4, minWidthUnits, maxWidthUnits);
    }

    function currentHeight(item) {
      return clamp(parseInt(item.getAttribute('data-height') || '2', 10) || 2, minHeightUnits, maxHeightUnits);
    }

    function syncSizeLabels(item, width, height) {
      item.querySelectorAll('[data-size-label]').forEach(function (label) {
        label.textContent = width + ' x ' + height;
      });
    }

    function applyItemDimensions(item) {
      var width = currentWidth(item);
      var height = currentHeight(item);
      var card = item.querySelector('.card');
      var basis = window.innerWidth < 768 ? '100%' : ((width / 12) * 100).toFixed(4) + '%';

      item.style.flex = '0 0 ' + basis;
      item.style.maxWidth = basis;
      item.style.width = basis;

      if (card) {
        card.style.minHeight = String(height * heightUnitPixels) + 'px';
      }

      syncSizeLabels(item, width, height);
    }

    function applyAllDimensions() {
      items().forEach(applyItemDimensions);
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

    function clearResizeState() {
      if (!resizeState) {
        return;
      }

      if (resizeState.item) {
        resizeState.item.classList.remove('is-resizing');
      }

      document.body.classList.remove('builder-resize-active');
      resizeState = null;
    }

    function persistResize(item) {
      var formData = new FormData();
      formData.append('id', item.getAttribute('data-block-id') || '');
      formData.append('width', String(currentWidth(item)));
      formData.append('height', String(currentHeight(item)));
      formData.append(csrfTokenName, csrfHash);

      setStatus('Kaydediliyor...', 'muted');

      fetch(resizeEndpoint, {
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
            throw new Error((result.payload && result.payload.message) || 'Blok boyutu kaydedilemedi.');
          }

          if (typeof result.payload.width !== 'undefined') {
            item.setAttribute('data-width', String(result.payload.width));
          }

          if (typeof result.payload.height !== 'undefined') {
            item.setAttribute('data-height', String(result.payload.height));
          }

          applyItemDimensions(item);
          setStatus(result.payload.message || 'Kaydedildi.', 'success');
        })
        .catch(function (error) {
          applyItemDimensions(item);
          setStatus(error.message || 'Blok boyutu kaydedilemedi.', 'error');
        });
    }

    function handleResizeMove(event) {
      if (!resizeState) {
        return;
      }

      var nextWidth = clamp(resizeState.startWidth + Math.round((event.clientX - resizeState.startX) / resizeState.columnWidth), minWidthUnits, maxWidthUnits);
      var nextHeight = clamp(resizeState.startHeight + Math.round((event.clientY - resizeState.startY) / heightUnitPixels), minHeightUnits, maxHeightUnits);

      resizeState.item.setAttribute('data-width', String(nextWidth));
      resizeState.item.setAttribute('data-height', String(nextHeight));
      applyItemDimensions(resizeState.item);
      setStatus('Boyut birakildiginda kaydedilecek.', 'muted');
    }

    function handleResizeEnd() {
      if (!resizeState) {
        return;
      }

      var item = resizeState.item;
      clearResizeState();
      persistResize(item);
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
        if (!enabled || resizeState) {
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

      var resizeHandle = item.querySelector('[data-resize-handle]');
      if (resizeHandle) {
        resizeHandle.addEventListener('mousedown', function (event) {
          if (!enabled) {
            return;
          }

          event.preventDefault();
          event.stopPropagation();

          var rect = grid.getBoundingClientRect();
          var columnWidth = rect.width > 0 ? rect.width / 12 : 80;

          resizeState = {
            item: item,
            startX: event.clientX,
            startY: event.clientY,
            startWidth: currentWidth(item),
            startHeight: currentHeight(item),
            columnWidth: Math.max(columnWidth, 40)
          };

          item.classList.add('is-resizing');
          document.body.classList.add('builder-resize-active');
          setStatus('Boyutlandirma aktif. Fareyi biraktiginizda kaydedilecek.', 'muted');
        });
      }
    }

    items().forEach(bindItem);
    refreshOrderLabels();
    applyAllDimensions();
    window.addEventListener('resize', applyAllDimensions);
    document.addEventListener('mousemove', handleResizeMove);
    document.addEventListener('mouseup', handleResizeEnd);

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
