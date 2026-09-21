(function () {
  const form = document.getElementById('nbp-form');
  if (!form || !window.newspackBedrockPack) {
    return;
  }
  const all = document.getElementById('nbp-all');
  const message = document.getElementById('nbp-message');
  if (all) {
    all.addEventListener('change', function () {
      form.querySelectorAll('.nbp-slug:not(:disabled)').forEach(function (box) {
        box.checked = all.checked;
      });
    });
  }
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    const slugs = Array.from(form.querySelectorAll('.nbp-slug:checked:not(:disabled)')).map(function (box) {
      return box.value;
    });
    if (!slugs.length) {
      show('Select at least one plugin, or skip.', true);
      return;
    }
    show('Installing…', false);
    fetch(window.newspackBedrockPack.rest, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.newspackBedrockPack.nonce,
      },
      body: JSON.stringify({ slugs: slugs }),
    })
      .then(function (res) {
        return res.json().then(function (body) {
          return { ok: res.ok, body: body };
        });
      })
      .then(function (result) {
        if (!result.ok) {
          show((result.body && result.body.message) || 'Install failed.', true);
          return;
        }
        const failed = Object.keys(result.body.results || {}).filter(function (slug) {
          return !result.body.results[slug].ok;
        });
        (result.body.status || []).forEach(function (row) {
          const cell = document.querySelector('.nbp-status[data-slug="' + row.slug + '"]');
          if (cell) {
            cell.textContent = row.status_label;
          }
          if (row.status === 'active') {
            const box = form.querySelector('.nbp-slug[value="' + row.slug + '"]');
            if (box) {
              box.checked = false;
              box.disabled = true;
            }
          }
        });
        if (failed.length) {
          show('Some plugins could not be installed: ' + failed.join(', '), true);
          return;
        }
        show('Selected plugins are active.', false);
      })
      .catch(function () {
        show('Install failed.', true);
      });
  });
  function show(text, isError) {
    message.hidden = false;
    message.textContent = text;
    message.classList.toggle('is-error', isError);
    message.classList.toggle('is-ok', !isError);
  }
})();
