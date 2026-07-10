<?php
/**
 * Shared course form fields — multi-step wizard.
 * Expects: $course (array|null), $categories (array), $val (callable), $submitLabel (string|null).
 * Used by course-form.php (full page) and courses.php (modal).
 */
if (!isset($val)) {
    $val = fn($k, $d = '') => e((string)($course[$k] ?? $d));
}
$categories  = $categories ?? [];
$submitLabel = $submitLabel ?? 'Save Course';

$wizardSteps = ['Basics', 'Curriculum', 'Requirements', 'Trainer', 'Schedule', 'Publish'];

$cardCls = 'rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4';

// Documents an applicant may be required to provide
$docOptions   = ['National ID', 'High School Certificate', 'Diploma Certificate', 'University Degree', 'Passport Photo', 'CV / Resume', 'Recommendation Letter', 'Birth Certificate'];
$selectedDocs = array_filter(array_map('trim', explode(',', (string)($course['required_documents'] ?? ''))));
?>
<div data-wizard>
  <!-- Stepper -->
  <ol class="flex items-center w-full mb-7" data-wizard-steps>
    <?php foreach ($wizardSteps as $i => $sLabel): $last = $i === count($wizardSteps) - 1; ?>
      <li class="flex items-center <?= $last ? '' : 'flex-1' ?>" data-step-indicator data-index="<?= $i ?>">
        <div class="flex items-center gap-2.5">
          <span class="step-dot grid place-items-center h-9 w-9 rounded-full text-sm font-bold border-2 border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-400 transition-all shrink-0">
            <span class="step-num"><?= $i + 1 ?></span>
            <svg class="step-check hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg>
          </span>
          <span class="step-text hidden sm:block text-sm font-semibold text-slate-400 transition-colors whitespace-nowrap"><?= e($sLabel) ?></span>
        </div>
        <?php if (!$last): ?><span class="step-bar flex-1 mx-3 h-0.5 rounded bg-slate-200 dark:bg-white/10 transition-colors"></span><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>

  <!-- STEP 1: Basics -->
  <div class="step-pane active space-y-5" data-step>
    <div class="<?= $cardCls ?>">
      <h3 class="font-bold text-slate-900 dark:text-white">Basic Information</h3>
      <div><label class="form-label">Course Title *</label><input name="title" required value="<?= $val('title') ?>" class="form-input"></div>
      <div><label class="form-label">Short Description</label><textarea name="short_description" rows="2" class="form-textarea" maxlength="400"><?= $val('short_description') ?></textarea></div>
      <div><label class="form-label">Full Description</label><textarea name="description" rows="6" class="form-textarea"><?= $val('description') ?></textarea></div>
    </div>
  </div>

  <!-- STEP 2: Curriculum -->
  <div class="step-pane space-y-5" data-step>
    <div class="<?= $cardCls ?>">
      <h3 class="font-bold text-slate-900 dark:text-white">Curriculum Details</h3>
      <p class="text-xs text-slate-400 -mt-2">Enter one item per line for list fields.</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="form-label">Objectives</label><textarea name="objectives" rows="4" class="form-textarea"><?= $val('objectives') ?></textarea></div>
        <div><label class="form-label">Learning Outcomes</label><textarea name="outcomes" rows="4" class="form-textarea"><?= $val('outcomes') ?></textarea></div>
        <div><label class="form-label">Benefits</label><textarea name="benefits" rows="4" class="form-textarea"><?= $val('benefits') ?></textarea></div>
        <div><label class="form-label">Requirements</label><textarea name="requirements" rows="4" class="form-textarea"><?= $val('requirements') ?></textarea></div>
      </div>
    </div>
  </div>

  <!-- STEP 3: Requirements (documents applicants must provide) -->
  <div class="step-pane space-y-5" data-step>
    <div class="<?= $cardCls ?>">
      <h3 class="font-bold text-slate-900 dark:text-white">Required Documents</h3>
      <p class="text-xs text-slate-400 -mt-2">Select the documents applicants must provide to enroll in this course.</p>
      <div class="grid sm:grid-cols-2 gap-2.5">
        <?php foreach ($docOptions as $doc): ?>
          <label class="flex items-center gap-3 px-3.5 py-3 rounded-xl border border-slate-200 dark:border-white/10 text-sm text-slate-700 dark:text-slate-200 cursor-pointer hover:border-primary/40 hover:bg-primary/5 dark:hover:bg-white/5 transition has-[:checked]:border-primary has-[:checked]:bg-primary/5 dark:has-[:checked]:bg-primary/10">
            <input type="checkbox" name="required_documents[]" value="<?= e($doc) ?>" <?= in_array($doc, $selectedDocs, true) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
            <?= e($doc) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- STEP 4: Trainer -->
  <div class="step-pane space-y-5" data-step>
    <div class="<?= $cardCls ?>">
      <h3 class="font-bold text-slate-900 dark:text-white">Trainer</h3>
      <div><label class="form-label">Trainer Name</label><input name="trainer" value="<?= $val('trainer') ?>" class="form-input"></div>
      <div><label class="form-label">Trainer Bio</label><textarea name="trainer_bio" rows="4" class="form-textarea"><?= $val('trainer_bio') ?></textarea></div>
    </div>
  </div>

  <!-- STEP 5: Schedule & Details -->
  <div class="step-pane space-y-5" data-step>
    <div class="<?= $cardCls ?>">
      <h3 class="font-bold text-slate-900 dark:text-white">Schedule &amp; Details</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="form-label">Category</label>
          <select name="category_id" class="form-select">
            <option value="">Uncategorized</option>
            <?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= ($course['category_id'] ?? 0)==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div><label class="form-label">Duration</label><input name="duration" value="<?= $val('duration') ?>" class="form-input" placeholder="e.g. 8 Weeks"></div>
        <div><label class="form-label">Location</label><input name="location" value="<?= $val('location') ?>" class="form-input"></div>
        <div><label class="form-label">Seats Available</label><input type="number" name="seats_available" value="<?= $val('seats_available','0') ?>" class="form-input" min="0"></div>
        <div><label class="form-label">Start Date</label><input type="date" name="start_date" value="<?= $val('start_date') ?>" class="form-input"></div>
        <div><label class="form-label">End Date</label><input type="date" name="end_date" value="<?= $val('end_date') ?>" class="form-input"></div>
        <div><label class="form-label">Registration Deadline</label><input type="date" name="registration_deadline" value="<?= $val('registration_deadline') ?>" class="form-input"></div>
        <?php $sessions = ['Morning', 'Afternoon', 'Evening']; $curSession = (string)($course['session_time'] ?? ''); ?>
        <div><label class="form-label">Session</label>
          <select name="session_time" class="form-select">
            <option value="">Select session...</option>
            <?php foreach ($sessions as $s): ?>
              <option value="<?= $s ?>" <?= $curSession === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="form-label">WhatsApp Group Invite Link</label>
        <div class="relative">
          <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.25.69-1.45 1.32-1.99 1.36-.53.05-.53.42-3.34-.7-2.81-1.12-4.6-3.99-4.74-4.17-.14-.18-1.13-1.5-1.13-2.87 0-1.36.71-2.03.97-2.31.25-.28.55-.35.74-.35.18 0 .37 0 .53.01.17.01.4-.06.62.48.25.59.83 2.04.9 2.19.07.14.11.31.02.49-.08.18-.13.3-.25.45-.13.15-.27.34-.39.45-.13.13-.26.27-.11.52.14.25.64 1.05 1.37 1.7.94.83 1.73 1.09 1.98 1.21.25.12.39.1.53-.06.14-.16.61-.71.78-.95.16-.25.32-.21.54-.13.22.08 1.41.66 1.65.78.25.12.41.18.47.28.06.1.06.59-.19 1.28Z"/></svg>
          </span>
          <input name="whatsapp_group_link" value="<?= $val('whatsapp_group_link') ?>" class="form-input pl-10" placeholder="https://chat.whatsapp.com/xxxxxxxxxxxxxxx">
        </div>
        <p class="mt-1 text-xs text-slate-400">Sent automatically to a student's WhatsApp when their application for this course is approved.</p>
      </div>

      <?php
      $weekDays     = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday'];
      $selectedDays = array_filter(array_map('trim', explode(',', (string)($course['schedule'] ?? ''))));
      ?>
      <div>
        <label class="form-label">Schedule (Days of the week)</label>
        <div class="relative" data-daydrop>
          <div data-daydrop-btn class="form-input flex flex-wrap items-center gap-1.5 min-h-[2.7rem] pr-9 cursor-pointer relative">
            <span data-daydrop-placeholder class="text-slate-400">Select days...</span>
            <span data-daydrop-chips class="flex flex-wrap items-center gap-1.5"></span>
            <svg data-daydrop-chevron class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </div>
          <div data-daydrop-menu class="hidden absolute z-30 mt-1.5 w-full rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 shadow-xl p-1.5 space-y-0.5">
            <?php foreach ($weekDays as $d): ?>
              <label class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-200 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5 transition">
                <input type="checkbox" name="schedule_days[]" value="<?= $d ?>" <?= in_array($d, $selectedDays, true) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" data-daydrop-cb>
                <?= $d ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <?php $certYes = !empty($course['certificate']) && strcasecmp((string)$course['certificate'], 'no') !== 0; ?>
      <div class="sm:max-w-[12rem]"><label class="form-label">Certificate</label>
        <select name="certificate" class="form-select">
          <option value="Yes" <?= $certYes ? 'selected' : '' ?>>Yes</option>
          <option value="No" <?= $certYes ? '' : 'selected' ?>>No</option>
        </select>
      </div>
    </div>
  </div>

  <!-- STEP 6: Publish & Media -->
  <div class="step-pane space-y-5" data-step>
    <div class="grid sm:grid-cols-2 gap-5">
      <div class="<?= $cardCls ?>">
        <h3 class="font-bold text-slate-900 dark:text-white">Publish</h3>
        <div><label class="form-label">Status</label>
          <select name="status" class="form-select">
            <?php foreach (['draft'=>'Draft','published'=>'Published','archived'=>'Archived'] as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= ($course['status'] ?? 'draft')===$k?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
          <input type="checkbox" name="is_featured" value="1" <?= !empty($course['is_featured'])?'checked':'' ?> class="h-4 w-4 rounded border-slate-300 text-primary"> Featured course
        </label>
      </div>

      <div class="<?= $cardCls ?>">
        <h3 class="font-bold text-slate-900 dark:text-white">Course Image</h3>
        <?php if (!empty($course['image']) && is_string($course['image']) && !str_contains($course['image'], "\n")): ?>
          <img src="<?= e(UPLOAD_URL . '/courses/' . $course['image']) ?>" class="rounded-xl w-full h-32 object-cover" alt="">
        <?php endif; ?>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold">
      </div>
    </div>
  </div>

  <!-- Wizard navigation -->
  <div class="flex items-center justify-between gap-3 mt-6 pt-5 border-t border-slate-200 dark:border-white/10">
    <button type="button" data-wizard-back class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition invisible">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
      Back
    </button>
    <span class="text-xs font-semibold text-slate-400" data-wizard-progress>Step 1 of <?= count($wizardSteps) ?></span>
    <div class="flex items-center gap-2">
      <button type="button" data-wizard-next class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow-lg shadow-primary/20 hover:-translate-y-0.5 transition">
        Next
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </button>
      <button type="submit" data-wizard-finish class="hidden items-center gap-1.5 px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow-lg shadow-primary/20 hover:-translate-y-0.5 transition">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg>
        <?= e($submitLabel) ?>
      </button>
    </div>
  </div>
</div>

<script>
(function () {
  /* ---- Day multi-select dropdown ---- */
  document.querySelectorAll('[data-daydrop]').forEach(function (root) {
    if (root.dataset.dropReady) return;
    root.dataset.dropReady = '1';
    var btn         = root.querySelector('[data-daydrop-btn]');
    var menu        = root.querySelector('[data-daydrop-menu]');
    var chips       = root.querySelector('[data-daydrop-chips]');
    var placeholder = root.querySelector('[data-daydrop-placeholder]');
    var chevron     = root.querySelector('[data-daydrop-chevron]');
    var boxes       = root.querySelectorAll('[data-daydrop-cb]');

    function sync() {
      var picked = Array.from(boxes).filter(function (c) { return c.checked; });
      placeholder.style.display = picked.length ? 'none' : '';
      chips.innerHTML = '';
      picked.forEach(function (c) {
        var chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded-md bg-primary/10 text-primary dark:bg-secondary/15 dark:text-secondary text-xs font-semibold';
        chip.textContent = c.value;
        var x = document.createElement('button');
        x.type = 'button';
        x.className = 'grid place-items-center h-4 w-4 rounded hover:bg-primary/20 dark:hover:bg-secondary/25';
        x.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>';
        x.addEventListener('click', function (e) { e.stopPropagation(); c.checked = false; sync(); });
        chip.appendChild(x);
        chips.appendChild(chip);
      });
    }
    function open()  { menu.classList.remove('hidden'); chevron.classList.add('rotate-180'); }
    function close() { menu.classList.add('hidden'); chevron.classList.remove('rotate-180'); }
    btn.addEventListener('click', function (e) { e.stopPropagation(); menu.classList.contains('hidden') ? open() : close(); });
    boxes.forEach(function (c) { c.addEventListener('change', sync); });
    document.addEventListener('click', function (e) { if (!root.contains(e.target)) close(); });
    sync();
  });

  /* ---- Step wizard ---- */
  document.querySelectorAll('[data-wizard]').forEach(function (wiz) {
    if (wiz.dataset.wizReady) return;
    wiz.dataset.wizReady = '1';
    var form        = wiz.closest('form');
    var panes       = Array.prototype.slice.call(wiz.querySelectorAll('[data-step]'));
    var indicators  = Array.prototype.slice.call(wiz.querySelectorAll('[data-step-indicator]'));
    var backBtn     = wiz.querySelector('[data-wizard-back]');
    var nextBtn     = wiz.querySelector('[data-wizard-next]');
    var finishBtn   = wiz.querySelector('[data-wizard-finish]');
    var progress    = wiz.querySelector('[data-wizard-progress]');
    var total       = panes.length;
    var current     = 0;
    if (form) form.noValidate = true;

    function updateIndicators() {
      indicators.forEach(function (ind, i) {
        var dot  = ind.querySelector('.step-dot');
        var num  = ind.querySelector('.step-num');
        var chk  = ind.querySelector('.step-check');
        var text = ind.querySelector('.step-text');
        var bar  = ind.querySelector('.step-bar');
        dot.classList.remove('border-primary','bg-primary','text-white','border-slate-200','dark:border-white/10','bg-white','dark:bg-slate-800','text-slate-400','ring-4','ring-primary/15');
        if (i < current) {
          dot.classList.add('border-primary','bg-primary','text-white');
          if (num) num.classList.add('hidden');
          if (chk) chk.classList.remove('hidden');
          if (text) { text.classList.remove('text-slate-400'); text.classList.add('text-slate-700','dark:text-slate-200'); }
        } else if (i === current) {
          dot.classList.add('border-primary','bg-primary','text-white','ring-4','ring-primary/15');
          if (num) num.classList.remove('hidden');
          if (chk) chk.classList.add('hidden');
          if (text) { text.classList.remove('text-slate-400'); text.classList.add('text-primary','dark:text-secondary'); }
        } else {
          dot.classList.add('border-slate-200','dark:border-white/10','bg-white','dark:bg-slate-800','text-slate-400');
          if (num) num.classList.remove('hidden');
          if (chk) chk.classList.add('hidden');
          if (text) { text.classList.remove('text-slate-700','dark:text-slate-200','text-primary','dark:text-secondary'); text.classList.add('text-slate-400'); }
        }
        if (bar) { bar.classList.toggle('bg-primary', i < current); bar.classList.toggle('bg-slate-200', i >= current); bar.classList.toggle('dark:bg-white/10', i >= current); }
      });
    }
    function show(i) {
      current = Math.max(0, Math.min(i, total - 1));
      panes.forEach(function (p, idx) { p.classList.toggle('active', idx === current); });
      backBtn.classList.toggle('invisible', current === 0);
      nextBtn.classList.toggle('hidden', current === total - 1);
      finishBtn.classList.toggle('hidden', current !== total - 1);
      finishBtn.classList.toggle('inline-flex', current === total - 1);
      if (progress) progress.textContent = 'Step ' + (current + 1) + ' of ' + total;
      updateIndicators();
      var sc = wiz.closest('.overflow-y-auto') || wiz;
      if (sc && sc.scrollTo) sc.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function validatePane(pane) {
      var fields = pane.querySelectorAll('input, select, textarea');
      for (var i = 0; i < fields.length; i++) {
        if (!fields[i].checkValidity()) { fields[i].reportValidity(); return false; }
      }
      return true;
    }

    nextBtn.addEventListener('click', function () { if (validatePane(panes[current])) show(current + 1); });
    backBtn.addEventListener('click', function () { show(current - 1); });
    indicators.forEach(function (ind, i) {
      ind.addEventListener('click', function () { if (i <= current) show(i); }); // allow jumping back only
      ind.style.cursor = 'pointer';
    });
    if (form) {
      form.addEventListener('submit', function (e) {
        for (var i = 0; i < panes.length; i++) {
          var fields = panes[i].querySelectorAll('input, select, textarea');
          for (var j = 0; j < fields.length; j++) {
            if (!fields[j].checkValidity()) { e.preventDefault(); show(i); fields[j].reportValidity(); return; }
          }
        }
      });
    }
    show(0);
  });
})();
</script>
