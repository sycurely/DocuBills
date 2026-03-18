@extends('layouts.app')

@php
  $activeMenu = 'settings';
  $activeTab = 'reminders';
@endphp

@section('title', 'Settings - Reminder Rules')

@push('styles')
<style>
  .material-icons-outlined {
    font-size: var(--icon-size);
    line-height: 1;
    vertical-align: middle;
  }
  .rem-shell {
    display: grid;
    gap: 1rem;
  }
  .rem-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.2rem;
  }
  .rem-card-title {
    margin: 0;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 0.45rem;
  }
  .rem-card-subtitle {
    margin: 0.3rem 0 0;
    color: var(--gray);
    font-size: 0.93rem;
  }
  .rem-section-header {
    margin-bottom: 1rem;
  }
  .rem-inline-alert {
    border-radius: 8px;
    padding: 0.65rem 0.8rem;
    margin: 0.75rem 0 0;
    font-size: 0.92rem;
    display: none;
  }
  .rem-inline-alert.show {
    display: block;
  }
  .rem-inline-alert.success {
    background: rgba(76, 201, 240, 0.15);
    color: var(--success);
    border: 1px solid var(--success);
  }
  .rem-inline-alert.error {
    background: rgba(247, 37, 133, 0.15);
    color: var(--danger);
    border: 1px solid var(--danger);
  }
  .rem-rules-list {
    display: grid;
    gap: 0.85rem;
  }
  .rem-rule-card {
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    background: var(--card-bg);
    transition: var(--transition);
  }
  .rem-rule-card:hover {
    box-shadow: var(--shadow-hover);
  }
  .rem-rule-header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.5rem;
    align-items: center;
    padding: 0.65rem 0.75rem;
    border-bottom: 1px solid transparent;
  }
  .rem-rule-card.is-expanded .rem-rule-header {
    border-bottom-color: var(--border);
  }
  .rem-rule-toggle {
    border: none;
    background: transparent;
    text-align: left;
    display: grid;
    gap: 0.45rem;
    padding: 0.15rem;
    cursor: pointer;
    color: inherit;
  }
  .rem-rule-toggle:focus-visible,
  .rem-icon-btn:focus-visible,
  .rem-preview-toggle:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
    border-radius: 6px;
  }
  .rem-rule-heading {
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
  }
  .rem-chip-row {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
  }
  .rem-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    padding: 0.18rem 0.55rem;
    font-size: 0.79rem;
    color: var(--gray);
    background: rgba(67, 97, 238, 0.06);
  }
  .rem-chip.is-enabled {
    border-color: rgba(76, 201, 240, 0.7);
    color: #0f7f9a;
    background: rgba(76, 201, 240, 0.14);
  }
  .rem-chip.is-disabled {
    border-color: rgba(247, 37, 133, 0.5);
    color: #b01f63;
    background: rgba(247, 37, 133, 0.1);
  }
  .rem-rule-header-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }
  .rem-header-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-right: 0.2rem;
    color: var(--gray);
    font-size: 0.8rem;
  }
  .rem-switch-btn {
    border: 1px solid var(--border);
    background: rgba(67, 97, 238, 0.08);
    color: var(--primary);
    min-width: 70px;
    height: 33px;
    border-radius: 999px;
    padding: 0 0.55rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    cursor: pointer;
    font-size: 0.78rem;
    font-weight: 600;
    transition: var(--transition);
  }
  .rem-switch-btn[aria-checked="false"] {
    background: rgba(247, 37, 133, 0.08);
    color: var(--danger);
    border-color: rgba(247, 37, 133, 0.45);
  }
  .rem-switch-btn:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
  }
  .rem-icon-btn {
    border: 1px solid var(--border);
    background: transparent;
    color: var(--gray);
    width: 33px;
    height: 33px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
  }
  .rem-icon-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(67, 97, 238, 0.08);
  }
  .rem-icon-btn.rem-icon-danger:hover {
    border-color: var(--danger);
    color: var(--danger);
    background: rgba(247, 37, 133, 0.09);
  }
  .rem-rule-body {
    padding: 0.95rem 0.9rem 1rem;
    background: linear-gradient(
      180deg,
      color-mix(in srgb, var(--card-bg) 92%, #d5dbe3 8%) 0%,
      color-mix(in srgb, var(--card-bg) 97%, #d5dbe3 3%) 100%
    );
  }
  .rem-rule-body[hidden] {
    display: none;
  }
  .rem-grid {
    display: grid;
    gap: 0.8rem 0.85rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .field label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: var(--gray);
    margin-bottom: 0.28rem;
  }
  .field input,
  .field select {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.52rem 0.62rem;
    background: var(--card-bg);
    color: var(--dark);
  }
  .field small {
    display: block;
    margin-top: 0.24rem;
    color: var(--gray);
    font-size: 0.76rem;
  }
  .rem-preview-wrap {
    border-top: 1px dashed var(--border);
    margin-top: 0.8rem;
    padding-top: 0.8rem;
  }
  .rem-preview-toggle {
    width: 100%;
    border: none;
    background: transparent;
    color: inherit;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    padding: 0;
    text-align: left;
    font-weight: 600;
  }
  .rem-preview-toggle strong {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--primary);
  }
  .rem-preview-body {
    margin-top: 0.9rem;
  }
  .rem-preview-body[hidden] {
    display: none;
  }
  .rem-preview-actions {
    display: flex;
    align-items: flex-end;
  }
  .rem-preview-output {
    margin-top: 1rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0.75rem;
    background: rgba(67, 97, 238, 0.03);
    display: none;
  }
  .rem-preview-output.show {
    display: block;
  }
  .rem-preview-output h4 {
    margin: 0 0 0.45rem;
    color: var(--primary);
  }
  .rem-preview-body-content {
    margin-top: 0.45rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.7rem;
    background: var(--card-bg);
  }
  .rem-sticky-actions {
    position: sticky;
    bottom: 0;
    z-index: 12;
    margin-top: 1rem;
    background: color-mix(in srgb, var(--card-bg) 94%, transparent);
    backdrop-filter: blur(6px);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0.7rem 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin-bottom: 1rem;
  }
  .rem-sticky-actions .actions-left,
  .rem-sticky-actions .actions-right {
    display: inline-flex;
    gap: 0.5rem;
  }
  #reminderRulesForm {
    padding-bottom: 0.25rem;
  }
  @media (max-width: 1024px) {
    .rem-grid {
      grid-template-columns: 1fr;
    }
  }
  @media (max-width: 768px) {
    .rem-rule-header {
      grid-template-columns: 1fr;
      gap: 0.65rem;
    }
    .rem-rule-header-actions {
      justify-content: flex-end;
    }
    .rem-sticky-actions {
      position: sticky;
      bottom: 0.6rem;
      padding-bottom: calc(0.7rem + env(safe-area-inset-bottom, 0px));
    }
    .rem-sticky-actions .actions-left,
    .rem-sticky-actions .actions-right {
      width: 100%;
    }
    .rem-sticky-actions .actions-right .btn {
      width: 100%;
    }
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <h1 class="page-title">Reminder Settings</h1>
  <p class="page-subtitle">Configure reminder rules used by scheduler.</p>
  <p class="help-text mt-xs">Rule IDs are generated and managed automatically by the system.</p>
</div>

@if($errors->any())
  <div class="alert alert-danger">Please correct the highlighted errors and try again.</div>
@endif

<form method="POST" action="{{ route('settings.reminders.update') }}" id="reminderRulesForm">
  @csrf
  <div class="rem-shell">
    <section class="rem-card" aria-label="Reminder Rules">
      <div class="rem-section-header">
        <h2 class="rem-card-title"><span class="material-icons-outlined">notifications_active</span> Reminder Rules</h2>
        <p class="rem-card-subtitle">Review status at a glance with chips, then expand a card to edit details.</p>
      </div>

      <div id="rulesList" class="rem-rules-list" data-rules-list>
        @foreach($rules as $idx => $rule)
          <article class="rem-rule-card rem-rule-row {{ $idx === 0 ? 'is-expanded' : '' }}" data-rule-card data-rule-index="{{ $idx }}">
            <div class="rem-rule-header">
              <button
                type="button"
                class="rem-rule-toggle"
                data-role="toggle"
                aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}"
                aria-controls="rule-panel-{{ $idx }}"
              >
                <span class="rem-rule-heading">
                  <span class="material-icons-outlined">schedule</span>
                  <span data-role="rule-number">Rule #{{ $idx + 1 }}</span>
                  <span data-role="summary-name">{{ trim((string) ($rule['name'] ?? '')) !== '' ? $rule['name'] : 'Untitled Rule' }}</span>
                </span>
                <span class="rem-chip-row">
                  <span class="rem-chip {{ !empty($rule['enabled']) ? 'is-enabled' : 'is-disabled' }}" data-role="summary-enabled">
                    <span class="material-icons-outlined">notifications</span>
                    {{ !empty($rule['enabled']) ? 'Enabled' : 'Disabled' }}
                  </span>
                  <span class="rem-chip" data-role="summary-direction">
                    <span class="material-icons-outlined">event</span>
                    {{ ucfirst((string) $rule['direction']) }}
                  </span>
                  <span class="rem-chip" data-role="summary-days">
                    <span class="material-icons-outlined">today</span>
                    {{ (int) $rule['days'] }} day(s)
                  </span>
                </span>
              </button>

              <div class="rem-rule-header-actions">
                <div class="rem-header-toggle-wrap">
                  <span>Enabled</span>
                  <input type="hidden" data-field="enabled" name="rules[{{ $idx }}][enabled]" value="{{ !empty($rule['enabled']) ? '1' : '0' }}">
                  <button
                    type="button"
                    class="rem-switch-btn"
                    data-role="enabled-toggle"
                    role="switch"
                    aria-checked="{{ !empty($rule['enabled']) ? 'true' : 'false' }}"
                    aria-label="Toggle rule enabled"
                  >
                    <span class="material-icons-outlined">{{ !empty($rule['enabled']) ? 'toggle_on' : 'toggle_off' }}</span>
                    <span data-role="enabled-toggle-label">{{ !empty($rule['enabled']) ? 'On' : 'Off' }}</span>
                  </button>
                </div>
                <button type="button" class="rem-icon-btn rem-icon-danger" data-role="remove" aria-label="Remove rule">
                  <span class="material-icons-outlined">delete</span>
                </button>
                <button
                  type="button"
                  class="rem-icon-btn"
                  data-role="chevron"
                  aria-label="{{ $idx === 0 ? 'Collapse rule details' : 'Expand rule details' }}"
                  aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}"
                  aria-controls="rule-panel-{{ $idx }}"
                >
                  <span class="material-icons-outlined">{{ $idx === 0 ? 'expand_less' : 'expand_more' }}</span>
                </button>
              </div>
            </div>

            <div id="rule-panel-{{ $idx }}" class="rem-rule-body" data-role="panel" @if($idx !== 0) hidden @endif>
              <input type="hidden" data-field="id" name="rules[{{ $idx }}][id]" value="{{ $rule['id'] }}">
              <div class="rem-grid">
                <div class="field">
                  <label><span class="material-icons-outlined">label</span> Name</label>
                  <input type="text" data-field="name" name="rules[{{ $idx }}][name]" value="{{ $rule['name'] }}" required>
                </div>
                <div class="field">
                  <label><span class="material-icons-outlined">event</span> Direction</label>
                  <select data-field="direction" name="rules[{{ $idx }}][direction]" required>
                    <option value="before" {{ $rule['direction'] === 'before' ? 'selected' : '' }}>Before</option>
                    <option value="on" {{ $rule['direction'] === 'on' ? 'selected' : '' }}>On</option>
                    <option value="after" {{ $rule['direction'] === 'after' ? 'selected' : '' }}>After</option>
                  </select>
                </div>
                <div class="field">
                  <label><span class="material-icons-outlined">calendar_month</span> Days</label>
                  <input type="number" data-field="days" min="0" max="365" name="rules[{{ $idx }}][days]" value="{{ $rule['days'] }}" required>
                </div>
                <div class="field">
                  <label><span class="material-icons-outlined">sync_alt</span> Offset Days</label>
                  <input type="number" data-field="offset_days" min="-365" max="365" name="rules[{{ $idx }}][offset_days]" value="{{ $rule['offset_days'] }}" required>
                  <small>Use a negative number for days before due date and positive for days after due date.</small>
                </div>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      <div id="rulesFeedback" class="rem-inline-alert" role="status" aria-live="polite"></div>
    </section>

    <section class="rem-card" aria-label="Preview Reminder Rendering">
      <div class="rem-section-header">
        <h2 class="rem-card-title"><span class="material-icons-outlined">preview</span> Preview Reminder Rendering</h2>
        <p class="rem-card-subtitle">Generate a sample message to confirm recipient, subject, and body rendering.</p>
      </div>

      <div class="rem-preview-wrap">
        <button
          type="button"
          id="previewToggle"
          class="rem-preview-toggle"
          aria-expanded="true"
          aria-controls="previewConfigBody"
        >
          <strong><span class="material-icons-outlined">settings</span> Preview Configuration</strong>
          <span class="material-icons-outlined" id="previewToggleIcon">expand_less</span>
        </button>

        <div id="previewConfigBody" class="rem-preview-body">
          <div class="rem-grid">
            <div class="field">
              <label><span class="material-icons-outlined">receipt_long</span> Invoice Number</label>
              <select id="previewInvoiceNumber">
                <option value="">Select an invoice</option>
                @foreach($previewInvoices as $invoice)
                  <option value="{{ $invoice->invoice_number }}">{{ $invoice->invoice_number }} - {{ $invoice->bill_to_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label><span class="material-icons-outlined">rule</span> Rule</label>
              <select id="previewRuleId">
                <option value="">Select a rule</option>
                @foreach($rules as $rule)
                  <option value="{{ $rule['id'] }}">{{ $rule['name'] }} ({{ $rule['id'] }})</option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label><span class="material-icons-outlined">mail</span> Template</label>
              <select id="previewTemplateId">
                <option value="">Select a template</option>
                @foreach($templates as $tpl)
                  <option value="{{ $tpl->id }}">{{ $tpl->template_name }} (#{{ $tpl->id }})</option>
                @endforeach
              </select>
            </div>
            <div class="field rem-preview-actions">
              <button type="button" id="previewBtn" class="btn btn-primary btn-sm">
                <span class="material-icons-outlined">preview</span> Generate Preview
              </button>
            </div>
          </div>

          <div id="previewAlert" class="rem-inline-alert" role="status" aria-live="polite"></div>

          <div id="previewResult" class="rem-preview-output">
            <h4>Preview</h4>
            <div><strong>Recipient:</strong> <span id="previewRecipient"></span></div>
            <div><strong>Subject:</strong> <span id="previewSubject"></span></div>
            <div class="mt-sm"><strong>Body:</strong></div>
            <div id="previewBody" class="rem-preview-body-content"></div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="rem-sticky-actions" role="group" aria-label="Reminder rule actions">
    <div class="actions-left">
      <button type="button" id="addRuleBtn" class="btn btn-outline">
        <span class="material-icons-outlined">add</span> Add Rule
      </button>
    </div>
    <div class="actions-right">
      <button type="submit" class="btn btn-primary">
        <span class="material-icons-outlined">save</span> Save Reminder Settings
      </button>
    </div>
  </div>
</form>
@endsection

@push('scripts')
<script>
  (function () {
    const rulesList = document.querySelector('[data-rules-list]');
    const addRuleBtn = document.getElementById('addRuleBtn');
    const rulesFeedback = document.getElementById('rulesFeedback');
    const previewBtn = document.getElementById('previewBtn');
    const previewAlert = document.getElementById('previewAlert');
    const previewResult = document.getElementById('previewResult');

    function getRuleCards() {
      return Array.from(rulesList.querySelectorAll('[data-rule-card]'));
    }

    function showInlineAlert(target, message, type) {
      if (!target) {
        return;
      }
      target.textContent = message;
      target.classList.remove('success', 'error', 'show');
      target.classList.add(type === 'success' ? 'success' : 'error', 'show');
    }

    function clearInlineAlert(target) {
      if (!target) {
        return;
      }
      target.textContent = '';
      target.classList.remove('success', 'error', 'show');
    }

    function setExpanded(card, expanded) {
      const panel = card.querySelector('[data-role="panel"]');
      const toggle = card.querySelector('[data-role="toggle"]');
      const chevron = card.querySelector('[data-role="chevron"]');
      const chevronIcon = chevron ? chevron.querySelector('.material-icons-outlined') : null;

      card.classList.toggle('is-expanded', expanded);
      if (panel) {
        panel.hidden = !expanded;
      }
      if (toggle) {
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      }
      if (chevron) {
        chevron.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        chevron.setAttribute('aria-label', expanded ? 'Collapse rule details' : 'Expand rule details');
      }
      if (chevronIcon) {
        chevronIcon.textContent = expanded ? 'expand_less' : 'expand_more';
      }
    }

    function capitalizeDirection(value) {
      if (value === 'before') {
        return 'Before';
      }
      if (value === 'after') {
        return 'After';
      }
      return 'On';
    }

    function updateRuleSummary(card) {
      const idx = Number(card.dataset.ruleIndex || 0) + 1;
      const nameField = card.querySelector('[data-field="name"]');
      const enabledField = card.querySelector('[data-field="enabled"]');
      const directionField = card.querySelector('[data-field="direction"]');
      const daysField = card.querySelector('[data-field="days"]');

      const name = nameField && nameField.value ? nameField.value.trim() : '';
      const enabledValue = enabledField && enabledField.value === '1';
      const directionValue = directionField && directionField.value ? directionField.value : 'on';
      const daysRaw = daysField ? daysField.value : '0';
      const parsedDays = Number.isFinite(Number(daysRaw)) ? Number(daysRaw) : 0;
      const daysValue = Math.max(0, parsedDays);

      const ruleNumEl = card.querySelector('[data-role="rule-number"]');
      const summaryNameEl = card.querySelector('[data-role="summary-name"]');
      const summaryEnabledEl = card.querySelector('[data-role="summary-enabled"]');
      const summaryDirectionEl = card.querySelector('[data-role="summary-direction"]');
      const summaryDaysEl = card.querySelector('[data-role="summary-days"]');

      if (ruleNumEl) {
        ruleNumEl.textContent = `Rule #${idx}`;
      }
      if (summaryNameEl) {
        summaryNameEl.textContent = name || 'Untitled Rule';
      }
      if (summaryEnabledEl) {
        summaryEnabledEl.classList.toggle('is-enabled', enabledValue);
        summaryEnabledEl.classList.toggle('is-disabled', !enabledValue);
        summaryEnabledEl.innerHTML = `<span class="material-icons-outlined">notifications</span>${enabledValue ? 'Enabled' : 'Disabled'}`;
      }
      if (summaryDirectionEl) {
        summaryDirectionEl.innerHTML = `<span class="material-icons-outlined">event</span>${capitalizeDirection(directionValue)}`;
      }
      if (summaryDaysEl) {
        summaryDaysEl.innerHTML = `<span class="material-icons-outlined">today</span>${daysValue} day(s)`;
      }
    }

    function renumberRules() {
      getRuleCards().forEach((card, idx) => {
        card.dataset.ruleIndex = String(idx);
        const panel = card.querySelector('[data-role="panel"]');
        const toggle = card.querySelector('[data-role="toggle"]');
        const chevron = card.querySelector('[data-role="chevron"]');
        const panelId = `rule-panel-${idx}`;

        if (panel) {
          panel.id = panelId;
        }
        if (toggle) {
          toggle.setAttribute('aria-controls', panelId);
        }
        if (chevron) {
          chevron.setAttribute('aria-controls', panelId);
        }

        card.querySelectorAll('[data-field]').forEach((input) => {
          const field = input.getAttribute('data-field');
          input.setAttribute('name', `rules[${idx}][${field}]`);
        });

        updateRuleSummary(card);
      });
    }

    function bindRuleCardEvents(card) {
      const toggle = card.querySelector('[data-role="toggle"]');
      const chevron = card.querySelector('[data-role="chevron"]');
      const remove = card.querySelector('[data-role="remove"]');
      const enabledToggle = card.querySelector('[data-role="enabled-toggle"]');
      const enabledField = card.querySelector('[data-field="enabled"]');
      const summarySourceFields = card.querySelectorAll('[data-field="name"], [data-field="direction"], [data-field="days"]');

      if (toggle) {
        toggle.addEventListener('click', () => {
          setExpanded(card, !card.classList.contains('is-expanded'));
        });
      }

      if (chevron) {
        chevron.addEventListener('click', () => {
          setExpanded(card, !card.classList.contains('is-expanded'));
        });
      }

      if (remove) {
        remove.addEventListener('click', () => {
          if (getRuleCards().length <= 1) {
            showInlineAlert(rulesFeedback, 'At least one reminder rule is required.', 'error');
            return;
          }
          card.remove();
          clearInlineAlert(rulesFeedback);
          renumberRules();
        });
      }

      if (enabledToggle && enabledField) {
        enabledToggle.addEventListener('click', () => {
          const next = enabledField.value === '1' ? '0' : '1';
          const isOn = next === '1';
          enabledField.value = next;
          enabledToggle.setAttribute('aria-checked', isOn ? 'true' : 'false');
          const icon = enabledToggle.querySelector('.material-icons-outlined');
          const text = enabledToggle.querySelector('[data-role="enabled-toggle-label"]');
          if (icon) {
            icon.textContent = isOn ? 'toggle_on' : 'toggle_off';
          }
          if (text) {
            text.textContent = isOn ? 'On' : 'Off';
          }
          updateRuleSummary(card);
        });
      }

      summarySourceFields.forEach((field) => {
        field.addEventListener('input', () => updateRuleSummary(card));
        field.addEventListener('change', () => updateRuleSummary(card));
      });
    }

    function escapeHtml(value) {
      return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function createRuleCard(index, data) {
      const name = escapeHtml(data.name || '');
      const direction = data.direction || 'after';
      const days = Number.isFinite(Number(data.days)) ? Number(data.days) : 0;
      const offsetDays = Number.isFinite(Number(data.offset_days)) ? Number(data.offset_days) : 0;
      const enabled = String(data.enabled || '1') === '1';

      const template = document.createElement('template');
      template.innerHTML = `
        <article class="rem-rule-card rem-rule-row" data-rule-card data-rule-index="${index}">
          <div class="rem-rule-header">
            <button type="button" class="rem-rule-toggle" data-role="toggle" aria-expanded="false" aria-controls="rule-panel-${index}">
              <span class="rem-rule-heading">
                <span class="material-icons-outlined">schedule</span>
                <span data-role="rule-number">Rule #${index + 1}</span>
                <span data-role="summary-name">${name || 'Untitled Rule'}</span>
              </span>
              <span class="rem-chip-row">
                <span class="rem-chip ${enabled ? 'is-enabled' : 'is-disabled'}" data-role="summary-enabled"><span class="material-icons-outlined">notifications</span>${enabled ? 'Enabled' : 'Disabled'}</span>
                <span class="rem-chip" data-role="summary-direction"><span class="material-icons-outlined">event</span>${capitalizeDirection(direction)}</span>
                <span class="rem-chip" data-role="summary-days"><span class="material-icons-outlined">today</span>${days} day(s)</span>
              </span>
            </button>
            <div class="rem-rule-header-actions">
              <div class="rem-header-toggle-wrap">
                <span>Enabled</span>
                <input type="hidden" data-field="enabled" name="rules[${index}][enabled]" value="${enabled ? '1' : '0'}">
                <button type="button" class="rem-switch-btn" data-role="enabled-toggle" role="switch" aria-checked="${enabled ? 'true' : 'false'}" aria-label="Toggle rule enabled">
                  <span class="material-icons-outlined">${enabled ? 'toggle_on' : 'toggle_off'}</span>
                  <span data-role="enabled-toggle-label">${enabled ? 'On' : 'Off'}</span>
                </button>
              </div>
              <button type="button" class="rem-icon-btn rem-icon-danger" data-role="remove" aria-label="Remove rule">
                <span class="material-icons-outlined">delete</span>
              </button>
              <button type="button" class="rem-icon-btn" data-role="chevron" aria-label="Expand rule details" aria-expanded="false" aria-controls="rule-panel-${index}">
                <span class="material-icons-outlined">expand_more</span>
              </button>
            </div>
          </div>
          <div id="rule-panel-${index}" class="rem-rule-body" data-role="panel" hidden>
            <input type="hidden" data-field="id" name="rules[${index}][id]" value="">
            <div class="rem-grid">
              <div class="field">
                <label><span class="material-icons-outlined">label</span> Name</label>
                <input type="text" data-field="name" name="rules[${index}][name]" value="${name}" required>
              </div>
              <div class="field">
                <label><span class="material-icons-outlined">event</span> Direction</label>
                <select data-field="direction" name="rules[${index}][direction]" required>
                  <option value="before"${direction === 'before' ? ' selected' : ''}>Before</option>
                  <option value="on"${direction === 'on' ? ' selected' : ''}>On</option>
                  <option value="after"${direction === 'after' ? ' selected' : ''}>After</option>
                </select>
              </div>
              <div class="field">
                <label><span class="material-icons-outlined">calendar_month</span> Days</label>
                <input type="number" data-field="days" min="0" max="365" name="rules[${index}][days]" value="${days}" required>
              </div>
              <div class="field">
                <label><span class="material-icons-outlined">sync_alt</span> Offset Days</label>
                <input type="number" data-field="offset_days" min="-365" max="365" name="rules[${index}][offset_days]" value="${offsetDays}" required>
                <small>Use a negative number for days before due date and positive for days after due date.</small>
              </div>
            </div>
          </div>
        </article>
      `;

      return template.content.firstElementChild;
    }

    if (addRuleBtn) {
      addRuleBtn.addEventListener('click', () => {
        const idx = getRuleCards().length;
        const newCard = createRuleCard(idx, {
          id: '',
          name: '',
          enabled: '1',
          direction: 'after',
          days: 0,
          offset_days: 0,
        });
        rulesList.appendChild(newCard);
        bindRuleCardEvents(newCard);
        renumberRules();
        setExpanded(newCard, false);
        clearInlineAlert(rulesFeedback);
      });
    }

    getRuleCards().forEach((card, idx) => {
      bindRuleCardEvents(card);
      setExpanded(card, idx === 0);
      updateRuleSummary(card);
    });
    renumberRules();

    const previewToggle = document.getElementById('previewToggle');
    const previewConfigBody = document.getElementById('previewConfigBody');
    const previewToggleIcon = document.getElementById('previewToggleIcon');

    function setPreviewExpanded(expanded) {
      if (!previewConfigBody || !previewToggle || !previewToggleIcon) {
        return;
      }
      previewConfigBody.hidden = !expanded;
      previewToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      previewToggleIcon.textContent = expanded ? 'expand_less' : 'expand_more';
    }

    if (previewToggle && previewConfigBody) {
      previewToggle.addEventListener('click', () => {
        setPreviewExpanded(previewConfigBody.hidden);
      });
    }

    if (previewBtn) {
      previewBtn.addEventListener('click', async () => {
        const invoiceNumber = document.getElementById('previewInvoiceNumber').value.trim();
        const ruleId = document.getElementById('previewRuleId').value.trim();
        const templateId = document.getElementById('previewTemplateId').value.trim();

        if (!invoiceNumber || !ruleId || !templateId) {
          showInlineAlert(previewAlert, 'Invoice Number, Rule, and Template are required.', 'error');
          return;
        }

        clearInlineAlert(previewAlert);
        previewBtn.disabled = true;

        try {
          const res = await fetch(@json(route('settings.reminders.preview')), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': @json(csrf_token()),
              'Accept': 'application/json',
            },
            body: JSON.stringify({ invoice_number: invoiceNumber, rule_id: ruleId, template_id: templateId }),
          });

          const json = await res.json();
          if (!res.ok || !json.success) {
            showInlineAlert(previewAlert, json.message || 'Preview failed.', 'error');
            return;
          }

          document.getElementById('previewRecipient').textContent = json.recipient || '';
          document.getElementById('previewSubject').textContent = json.subject || '';
          document.getElementById('previewBody').innerHTML = json.body || '';
          previewResult.classList.add('show');
          showInlineAlert(previewAlert, 'Preview generated.', 'success');
        } catch (error) {
          showInlineAlert(previewAlert, 'Preview request failed. Please try again.', 'error');
        } finally {
          previewBtn.disabled = false;
        }
      });
    }
  })();
</script>
@endpush
