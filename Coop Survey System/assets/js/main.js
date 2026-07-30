// ============================================================
// Cooperative Survey System - client-side behaviors
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  initRatingWidgets();
  initDeleteConfirmations();
  initQuestionBuilder();
  initSurveyFormValidation();
});

/* ---------- Star rating widgets on the survey-taking form ---------- */
function initRatingWidgets() {
  document.querySelectorAll('.rating-widget').forEach(function (widget) {
    const input = widget.querySelector('input[type="hidden"]');
    const stars = widget.querySelectorAll('.rating-star');

    function paint(value) {
      stars.forEach(function (star) {
        star.classList.toggle('active', parseInt(star.dataset.value, 10) <= value);
      });
    }

    if (input.value) paint(parseInt(input.value, 10));

    stars.forEach(function (star) {
      star.addEventListener('click', function () {
        input.value = star.dataset.value;
        paint(parseInt(star.dataset.value, 10));
      });
      star.addEventListener('mouseenter', function () {
        paint(parseInt(star.dataset.value, 10));
      });
    });

    widget.addEventListener('mouseleave', function () {
      paint(parseInt(input.value || 0, 10));
    });
  });
}

/* ---------- Confirm before destructive actions ---------- */
function initDeleteConfirmations() {
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!window.confirm(msg)) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });
}

/* ---------- Survey submission: require every "required" question be answered ---------- */
function initSurveyFormValidation() {
  const form = document.getElementById('surveyResponseForm');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    let valid = true;
    let firstInvalid = null;

    form.querySelectorAll('.question-block[data-required="1"]').forEach(function (block) {
      const type = block.dataset.type;
      let answered = false;

      if (type === 'multiple_choice' || type === 'yes_no') {
        answered = !!block.querySelector('input[type="radio"]:checked');
      } else if (type === 'rating') {
        const hidden = block.querySelector('input[type="hidden"]');
        answered = hidden && hidden.value !== '';
      } else if (type === 'short_answer') {
        const textarea = block.querySelector('textarea');
        answered = textarea && textarea.value.trim().length > 0;
      }

      block.classList.toggle('border-danger', !answered);
      if (!answered) {
        valid = false;
        if (!firstInvalid) firstInvalid = block;
      }
    });

    if (!valid) {
      e.preventDefault();
      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      showFormAlert(form, 'Please answer all required questions before submitting.');
    }
  });
}

function showFormAlert(form, message) {
  let alertBox = form.querySelector('.js-validation-alert');
  if (!alertBox) {
    alertBox = document.createElement('div');
    alertBox.className = 'alert alert-danger js-validation-alert';
    form.prepend(alertBox);
  }
  alertBox.textContent = message;
}

/* ---------- Dynamic question/choice builder on survey create & edit pages ---------- */
let questionIndex = window.__existingQuestionCount || 0;

function initQuestionBuilder() {
  const container = document.getElementById('questionsContainer');
  const addBtn = document.getElementById('addQuestionBtn');
  if (!container || !addBtn) return;

  addBtn.addEventListener('click', function () {
    container.insertAdjacentHTML('beforeend', buildQuestionBlock(questionIndex));
    questionIndex++;
    attachQuestionEvents(container);
  });

  attachQuestionEvents(container);
}

function buildQuestionBlock(idx) {
  return `
  <div class="card mb-3 question-editor" data-index="${idx}">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h6 class="mb-0">Question ${idx + 1}</h6>
        <button type="button" class="btn btn-sm btn-outline-danger remove-question">Remove</button>
      </div>
      <div class="mb-2">
        <label class="form-label small">Question text</label>
        <input type="text" name="questions[${idx}][text]" class="form-control" required>
      </div>
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label small">Type</label>
          <select name="questions[${idx}][type]" class="form-select question-type-select">
            <option value="multiple_choice">Multiple Choice</option>
            <option value="yes_no">Yes / No</option>
            <option value="rating">Rating Scale (1-5)</option>
            <option value="short_answer">Short Answer</option>
          </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="questions[${idx}][required]" value="1" checked id="req${idx}">
            <label class="form-check-label small" for="req${idx}">Required</label>
          </div>
        </div>
      </div>
      <div class="choices-wrapper">
        <label class="form-label small">Choices</label>
        <div class="choices-list">
          <div class="input-group input-group-sm mb-1">
            <input type="text" name="questions[${idx}][choices][]" class="form-control" placeholder="Choice 1">
            <button type="button" class="btn btn-outline-secondary remove-choice">&times;</button>
          </div>
          <div class="input-group input-group-sm mb-1">
            <input type="text" name="questions[${idx}][choices][]" class="form-control" placeholder="Choice 2">
            <button type="button" class="btn btn-outline-secondary remove-choice">&times;</button>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-link add-choice p-0">+ Add choice</button>
      </div>
    </div>
  </div>`;
}

function attachQuestionEvents(container) {
  container.querySelectorAll('.question-editor').forEach(function (block) {
    if (block.dataset.wired) return;
    block.dataset.wired = '1';

    const typeSelect = block.querySelector('.question-type-select');
    const choicesWrapper = block.querySelector('.choices-wrapper');

    function toggleChoices() {
      if (!typeSelect || !choicesWrapper) return;
      choicesWrapper.style.display = typeSelect.value === 'multiple_choice' ? 'block' : 'none';
    }
    if (typeSelect) {
      typeSelect.addEventListener('change', toggleChoices);
      toggleChoices();
    }

    const removeBtn = block.querySelector('.remove-question');
    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        block.remove();
      });
    }

    const addChoiceBtn = block.querySelector('.add-choice');
    if (addChoiceBtn) {
      addChoiceBtn.addEventListener('click', function () {
        const list = block.querySelector('.choices-list');
        const idx = block.dataset.index;
        const div = document.createElement('div');
        div.className = 'input-group input-group-sm mb-1';
        div.innerHTML = `<input type="text" name="questions[${idx}][choices][]" class="form-control" placeholder="Choice">
          <button type="button" class="btn btn-outline-secondary remove-choice">&times;</button>`;
        list.appendChild(div);
        wireRemoveChoice(div.querySelector('.remove-choice'));
      });
    }

    block.querySelectorAll('.remove-choice').forEach(wireRemoveChoice);
  });
}

function wireRemoveChoice(btn) {
  if (!btn || btn.dataset.wired) return;
  btn.dataset.wired = '1';
  btn.addEventListener('click', function () {
    const list = btn.closest('.choices-list');
    if (list.querySelectorAll('.input-group').length > 1) {
      btn.closest('.input-group').remove();
    }
  });
}
