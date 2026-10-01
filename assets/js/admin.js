(function(){
  const sidebar = document.querySelector('.admin-sidebar');
  const backdrop = document.querySelector('.admin-backdrop');
  const openButtons = document.querySelectorAll('[data-admin-open]');
  const closeButtons = document.querySelectorAll('[data-admin-close]');

  function openSidebar(){
    if(!sidebar || !backdrop) return;
    sidebar.classList.add('is-open');
    backdrop.classList.add('is-active');
    openButtons.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar(){
    if(!sidebar || !backdrop) return;
    sidebar.classList.remove('is-open');
    backdrop.classList.remove('is-active');
    openButtons.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    document.body.style.overflow = '';
  }

  openButtons.forEach(btn => btn.addEventListener('click', openSidebar));
  closeButtons.forEach(btn => btn.addEventListener('click', closeSidebar));
  document.addEventListener('keydown', event => { if(event.key === 'Escape') closeSidebar(); });

  document.querySelectorAll('[data-confirm]').forEach(link => {
    link.addEventListener('click', event => {
      if(!confirm(link.dataset.confirm || 'Are you sure?')) event.preventDefault();
    });
  });



  document.querySelectorAll('[data-confirm-form]').forEach(form => {
    form.addEventListener('submit', event => {
      if(!confirm(form.dataset.confirmForm || 'Are you sure?')) event.preventDefault();
    });
  });
  document.querySelectorAll('[data-table-filter]').forEach(input => {
    const table = document.querySelector(input.dataset.tableFilter);
    if(!table) return;
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    input.addEventListener('input', () => {
      const value = input.value.trim().toLowerCase();
      rows.forEach(row => {
        row.hidden = value && !row.innerText.toLowerCase().includes(value);
      });
    });
  });

  document.querySelectorAll('[data-image-preview]').forEach(input => {
    const preview = document.querySelector(input.dataset.imagePreview);
    if(!preview) return;
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if(!file) return;
      if(!file.type.startsWith('image/')) return;
      preview.src = URL.createObjectURL(file);
      preview.hidden = false;
    });
  });

  document.querySelectorAll('[data-toggle-passwords]').forEach(button => {
    button.addEventListener('click', () => {
      const fields = document.querySelectorAll('[data-password-field]');
      const shouldShow = Array.from(fields).some(field => field.type === 'password');
      fields.forEach(field => { field.type = shouldShow ? 'text' : 'password'; });
    });
  });


  document.querySelectorAll('[data-copy-text]').forEach(button => {
    button.addEventListener('click', async () => {
      const text = button.dataset.copyText || '';
      if(!text) return;
      try {
        await navigator.clipboard.writeText(text);
        const old = button.textContent;
        button.textContent = 'Copied';
        setTimeout(() => { button.textContent = old; }, 1200);
      } catch(err) {
        const input = document.createElement('textarea');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
      }
    });
  });

  const richEditor = document.querySelector('[data-rich-editor]');
  document.querySelectorAll('[data-editor-insert]').forEach(button => {
    button.addEventListener('click', () => {
      const target = richEditor || document.querySelector('textarea[name="content_en"]');
      if(!target) return;
      const snippet = button.dataset.editorInsert || '';
      const start = target.selectionStart || target.value.length;
      const end = target.selectionEnd || start;
      const before = target.value.slice(0, start);
      const after = target.value.slice(end);
      const prefix = before && !before.endsWith('\n') ? '\n\n' : '';
      const suffix = after && !after.startsWith('\n') ? '\n\n' : '';
      target.value = before + prefix + snippet + suffix + after;
      target.focus();
      const next = (before + prefix + snippet).length;
      target.setSelectionRange(next, next);
      target.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });

  function updateWordCount(field){
    const target = document.querySelector(field.dataset.wordCount || '');
    if(!target) return;
    const words = (field.value.trim().match(/\S+/g) || []).length;
    const chars = field.value.length;
    target.textContent = words + ' words · ' + chars + ' chars';
  }
  document.querySelectorAll('[data-word-count]').forEach(field => {
    field.addEventListener('input', () => updateWordCount(field));
    updateWordCount(field);
  });

})();
