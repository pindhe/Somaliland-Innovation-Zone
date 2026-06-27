<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }

$slug = $routeParam ?? (string)input('slug', '');
$slug = preg_replace('#[^a-zA-Z0-9\-_]#', '', (string)$slug);

// Course is optional (general application) but recommended.
$course = $slug ? db_one("SELECT * FROM courses WHERE slug = ? AND status='published' LIMIT 1", [$slug]) : null;

// Block applications when the registration deadline has passed.
if ($course && deadline_passed($course['registration_deadline'] ?? null)) {
    flash('error', 'Registration for this course is closed — the deadline has passed.');
    redirect('course/' . $course['slug']);
}

// Block re-applying from the same device for the same course.
if ($course && has_applied((int)$course['id'])) {
    flash('info', 'You have already applied for this course on this device.');
    redirect('course/' . $course['slug']);
}

// Applications are course-specific. Without a chosen course, send the visitor
// to the courses page to pick one first.
if (!$course) {
    flash('info', 'Please choose a course to apply for.');
    redirect('courses');
}

// -------------------------------------------------------------------------
// Handle submission
// -------------------------------------------------------------------------
if (is_post()) {
    csrf_check();

    $errors = [];

    $fullName = clean((string)input('full_name'));
    $email    = filter_var((string)input('email'), FILTER_VALIDATE_EMAIL) ?: '';
    $phone    = clean((string)input('phone'));
    $courseId = (int)input('course_id', $course['id'] ?? 0);
    $confirm  = input('confirm');

    if ($fullName === '')          $errors[] = 'Full name is required.';
    if ($email === '')             $errors[] = 'A valid email address is required.';
    if ($phone === '')             $errors[] = 'Phone number is required.';
    if ($courseId <= 0)            $errors[] = 'Please select a course.';
    if (!$confirm)                 $errors[] = 'You must confirm the declaration.';

    $courseRow = $courseId ? db_one("SELECT id, registration_deadline FROM courses WHERE id = ? AND status='published'", [$courseId]) : null;
    if (!$courseRow) {
        $errors[] = 'Selected course is not available.';
    } elseif (deadline_passed($courseRow['registration_deadline'] ?? null)) {
        $errors[] = 'Registration for this course is closed — the deadline has passed.';
    } elseif (has_applied($courseId)) {
        $errors[] = 'You have already applied for this course on this device.';
    }

    if (!$errors) {
        $reference = 'SIZ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $appId = db_insert(
            "INSERT INTO applications
             (reference, course_id, full_name, gender, date_of_birth, national_id, phone, whatsapp, email,
              education_level, school, university, faculty, department, graduation_year,
              region, district, address, skills, experience, certifications, portfolio_url,
              why_join, goals, expectations, status, ip_address)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending', ?)",
            [
                $reference, $courseId, $fullName,
                clean((string)input('gender')) ?: null,
                input('date_of_birth') ?: null,
                clean((string)input('national_id')) ?: null,
                $phone,
                clean((string)input('whatsapp')) ?: null,
                $email,
                clean((string)input('education_level')) ?: null,
                (clean((string)input('institution_type')) === 'School')     ? (clean((string)input('institution_name')) ?: null) : null,
                (clean((string)input('institution_type')) === 'University') ? (clean((string)input('institution_name')) ?: null) : null,
                clean((string)input('faculty')) ?: null,
                null,
                null,
                clean((string)input('region')) ?: null,
                clean((string)input('district')) ?: null,
                clean((string)input('address')) ?: null,
                clean((string)input('skills')) ?: null,
                clean((string)input('experience')) ?: null,
                clean((string)input('certifications')) ?: null,
                filter_var((string)input('portfolio_url'), FILTER_VALIDATE_URL) ?: null,
                clean((string)input('why_join')) ?: null,
                clean((string)input('goals')) ?: null,
                clean((string)input('expectations')) ?: null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );

        // Handle document uploads — only the documents the admin selected for the course.
        $allowedDocs = ['National ID','High School Certificate','Diploma Certificate','University Degree','Passport Photo','CV / Resume','Recommendation Letter','Birth Certificate'];
        $docLabels   = (array)input('doc_labels', []);
        foreach ($docLabels as $i => $rawLabel) {
            $label = clean((string)$rawLabel);
            if (!in_array($label, $allowedDocs, true)) continue;
            $field = 'doc_' . (int)$i;
            if (!empty($_FILES[$field]['name'])) {
                $res = handle_upload($_FILES[$field], 'documents');
                if ($res['success']) {
                    db_exec(
                        "INSERT INTO application_documents (application_id, doc_type, file_path, original_name) VALUES (?,?,?,?)",
                        [$appId, $label, $res['path'], $_FILES[$field]['name']]
                    );
                }
            }
        }

        log_activity('application_received', "New application {$reference} from {$fullName}", null);

        mark_applied($courseId);
        $_SESSION['application_ref'] = $reference;
        redirect('success');
    }

    set_old($_POST);
    foreach ($errors as $err) flash('error', $err);
}

$pageTitle = 'Apply' . ($course ? ' - ' . $course['title'] : '');
$activeNav = 'courses';
$metaDesc  = 'Submit your application to join a Somaliland Innovation Zone program. No account required.';

require INCLUDES_PATH . '/header.php';

$steps = ['Personal', 'Education', 'Documents', 'Confirm'];
?>

<section class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="text-center mb-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white">Application Form</h1>
    <p class="mt-2 text-slate-500">Applying for <span class="font-semibold text-primary dark:text-secondary"><?= e($course['title']) ?></span></p>
  </div>

  <!-- Stepper -->
  <div class="mb-8 overflow-x-auto">
    <ol id="stepper" class="flex items-center gap-2 min-w-max justify-center">
      <?php foreach ($steps as $i => $label): ?>
        <li class="flex items-center gap-2" data-step-indicator="<?= $i ?>">
          <span class="step-dot grid place-items-center h-9 w-9 rounded-full text-sm font-bold border-2 transition <?= $i === 0 ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-white/10' ?>"><?= $i + 1 ?></span>
          <span class="hidden sm:block text-xs font-semibold text-slate-500"><?= $label ?></span>
          <?php if ($i < count($steps) - 1): ?><span class="w-6 h-px bg-slate-200 dark:bg-white/10"></span><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>

  <form id="applyForm" method="post" action="<?= e(url('apply' . ($course ? '/' . $course['slug'] : ''))) ?>" enctype="multipart/form-data"
        class="rounded-3xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 shadow-lg p-6 sm:p-8">
    <?= csrf_field() ?>

    <!-- Step 1: Personal -->
    <div class="step-pane active" data-step="0">
      <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-5">Personal Information</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="form-label">Full Name *</label><input name="full_name" required value="<?= old('full_name') ?>" class="form-input"></div>
        <div><label class="form-label">Gender</label>
          <select name="gender" class="form-select">
            <option value="">Select</option>
            <?php foreach (['Male','Female','Other'] as $g): ?><option <?= old('gender')===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?>
          </select>
        </div>
        <div><label class="form-label">Phone Number *</label><input name="phone" required value="<?= old('phone') ?>" class="form-input"></div>
        <div><label class="form-label">Email Address *</label><input type="email" name="email" required value="<?= old('email') ?>" class="form-input"></div>
        <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
        <div class="sm:col-span-2">
          <label class="form-label">Course</label>
          <div class="form-input flex items-center gap-2 bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-slate-300 cursor-default">
            <svg class="h-5 w-5 text-primary dark:text-secondary shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span class="font-semibold"><?= e($course['title']) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Step 2: Education -->
    <div class="step-pane" data-step="1">
      <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-5">Education Information</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="form-label">Education Level</label>
          <select name="education_level" class="form-select">
            <option value="">Select</option>
            <?php foreach (['Secondary','Diploma','Bachelor','Master','PhD','Other'] as $lvl): ?><option <?= old('education_level')===$lvl?'selected':'' ?>><?= $lvl ?></option><?php endforeach; ?>
          </select>
        </div>
        <div><label class="form-label">Institution Type</label>
          <select name="institution_type" class="form-select">
            <option value="">Select</option>
            <?php foreach (['School','University'] as $it): ?><option <?= old('institution_type')===$it?'selected':'' ?>><?= $it ?></option><?php endforeach; ?>
          </select>
        </div>
        <div><label class="form-label">Institution Name</label><input name="institution_name" value="<?= old('institution_name') ?>" class="form-input" placeholder="Name of your school / university"></div>
        <div class="sm:col-span-2"><label class="form-label">Faculty</label><input name="faculty" value="<?= old('faculty') ?>" class="form-input"></div>
      </div>
    </div>

    <!-- Step 3: Documents -->
    <div class="step-pane" data-step="2">
      <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Document Upload</h2>
      <?php
      // Only show the documents the admin selected for this course.
      $courseReqDocs = $course
          ? array_filter(array_map('trim', explode(',', (string)($course['required_documents'] ?? ''))))
          : [];
      $uploadDocs = $courseReqDocs ?: ['CV / Resume', 'Passport Photo'];
      ?>
      <p class="text-sm text-slate-500 mb-5">Allowed: PDF, JPG, PNG (max 5MB each). Please upload the required document<?= count($uploadDocs) === 1 ? '' : 's' ?> below.</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach (array_values($uploadDocs) as $i => $label): ?>
          <div>
            <label class="form-label"><?= e($label) ?></label>
            <input type="hidden" name="doc_labels[]" value="<?= e($label) ?>">
            <input type="file" name="doc_<?= $i ?>" accept=".pdf,.jpg,.jpeg,.png"
                   class="block w-full text-sm text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold hover:file:bg-primary/20 cursor-pointer">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Step 4: Confirm -->
    <div class="step-pane" data-step="3">
      <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-5">Confirmation</h2>
      <div class="rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 p-5">
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="checkbox" name="confirm" value="1" required class="mt-1 h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
          <span class="text-sm text-slate-700 dark:text-slate-300">I confirm that all the information I have provided is correct and complete. I understand that providing false information may disqualify my application.</span>
        </label>
      </div>
    </div>

    <!-- Nav buttons -->
    <div class="mt-8 flex items-center justify-between gap-3">
      <button type="button" id="prevBtn" class="px-6 py-3 rounded-xl border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 font-semibold opacity-0 pointer-events-none transition">&larr; Back</button>
      <div class="text-sm text-slate-400" id="stepCount">Step 1 of <?= count($steps) ?></div>
      <button type="button" id="nextBtn" class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold hover:shadow-lg transition">Next &rarr;</button>
      <button type="submit" id="submitBtn" class="hidden px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold hover:shadow-lg transition">Submit Application</button>
    </div>
  </form>
</section>

<script>
(function () {
  const form = document.getElementById('applyForm');
  const panes = Array.from(form.querySelectorAll('.step-pane'));
  const dots = Array.from(document.querySelectorAll('#stepper .step-dot'));
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  const stepCount = document.getElementById('stepCount');
  let current = 0;
  const total = panes.length;

  function show(idx) {
    panes.forEach((p, i) => p.classList.toggle('active', i === idx));
    dots.forEach((d, i) => {
      d.classList.toggle('bg-primary', i <= idx);
      d.classList.toggle('text-white', i <= idx);
      d.classList.toggle('border-primary', i <= idx);
      d.classList.toggle('text-slate-400', i > idx);
    });
    prevBtn.classList.toggle('opacity-0', idx === 0);
    prevBtn.classList.toggle('pointer-events-none', idx === 0);
    nextBtn.classList.toggle('hidden', idx === total - 1);
    submitBtn.classList.toggle('hidden', idx !== total - 1);
    stepCount.textContent = 'Step ' + (idx + 1) + ' of ' + total;
    window.scrollTo({ top: form.offsetTop - 120, behavior: 'smooth' });
  }

  function validateStep(idx) {
    const pane = panes[idx];
    const fields = pane.querySelectorAll('input[required], select[required], textarea[required]');
    for (const f of fields) {
      if (!f.checkValidity()) { f.reportValidity(); return false; }
    }
    return true;
  }

  nextBtn.addEventListener('click', () => {
    if (!validateStep(current)) return;
    if (current < total - 1) { current++; show(current); }
  });
  prevBtn.addEventListener('click', () => {
    if (current > 0) { current--; show(current); }
  });

  form.addEventListener('submit', (e) => {
    if (!validateStep(current)) { e.preventDefault(); return; }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Submitting...';
  });

  show(0);
})();
</script>

<?php require INCLUDES_PATH . '/footer.php'; clear_old(); ?>
