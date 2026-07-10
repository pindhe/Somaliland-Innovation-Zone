<?php
/**
 * SIZSR Admin - Add / Edit Course
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$id = (int)input('id', 0);
$course = $id ? db_one("SELECT * FROM courses WHERE id=?", [$id]) : null;
if ($id && !$course) { flash('error', 'Course not found.'); redirect('admin/courses.php'); }

$adminPage = 'courses';
$pageTitle = $course ? 'Edit Course' : 'Add Course';

$categories = db_all("SELECT id, name FROM categories WHERE status='active' ORDER BY name ASC");

if (is_post()) {
    csrf_check();

    $title = clean((string)input('title'));
    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';

    // Unique slug
    $slug = slugify($title);
    $existing = db_one("SELECT id FROM courses WHERE slug=? AND id<>?", [$slug, $id]);
    if ($existing) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);

    // Image upload
    $imageName = $course['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $res = handle_upload($_FILES['image'], 'courses', ALLOWED_IMAGE_TYPES);
        if ($res['success']) {
            if ($imageName) @unlink(UPLOAD_PATH . '/courses/' . $imageName);
            $imageName = $res['filename'];
        } else {
            $errors[] = 'Image: ' . $res['error'];
        }
    }

    if (!$errors) {
        $allowedDays  = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday'];
        $scheduleDays = array_values(array_intersect($allowedDays, (array)input('schedule_days', [])));

        $allowedDocs  = ['National ID','High School Certificate','Diploma Certificate','University Degree','Passport Photo','CV / Resume','Recommendation Letter','Birth Certificate'];
        $requiredDocs = array_values(array_intersect($allowedDocs, (array)input('required_documents', [])));

        $fields = [
            'title'             => $title,
            'slug'              => $slug,
            'category_id'       => (int)input('category_id') ?: null,
            'image'             => $imageName,
            'short_description' => clean((string)input('short_description')) ?: null,
            'description'       => trim((string)input('description')) ?: null,
            'objectives'        => trim((string)input('objectives')) ?: null,
            'requirements'      => trim((string)input('requirements')) ?: null,
            'required_documents' => $requiredDocs ? implode(', ', $requiredDocs) : null,
            'benefits'          => trim((string)input('benefits')) ?: null,
            'outcomes'          => trim((string)input('outcomes')) ?: null,
            'trainer'           => clean((string)input('trainer')) ?: null,
            'trainer_bio'       => clean((string)input('trainer_bio')) ?: null,
            'duration'          => clean((string)input('duration')) ?: null,
            'start_date'        => input('start_date') ?: null,
            'end_date'          => input('end_date') ?: null,
            'registration_deadline' => input('registration_deadline') ?: null,
            'location'          => clean((string)input('location')) ?: null,
            'seats_available'   => (int)input('seats_available'),
            'schedule'          => $scheduleDays ? implode(', ', $scheduleDays) : null,
            'session_time'      => in_array(input('session_time'), ['Morning','Afternoon','Evening'], true) ? (string)input('session_time') : null,
            'whatsapp_group_link' => clean((string)input('whatsapp_group_link')) ?: null,
            'certificate'       => input('certificate') === 'Yes' ? 'Yes' : 'No',
            'is_featured'       => input('is_featured') ? 1 : 0,
            'status'            => in_array(input('status'), ['draft','published','archived'], true) ? (string)input('status') : 'draft',
        ];

        if ($id) {
            $set = implode(', ', array_map(fn($k) => "`$k`=?", array_keys($fields)));
            db_exec("UPDATE courses SET $set WHERE id=?", [...array_values($fields), $id]);
            log_activity('course_updated', "Updated course: {$title}");
            flash('success', 'Course updated successfully.');
        } else {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($fields)));
            $ph   = implode(', ', array_fill(0, count($fields), '?'));
            $id = db_insert("INSERT INTO courses ($cols) VALUES ($ph)", array_values($fields));
            log_activity('course_created', "Created course: {$title}");
            flash('success', 'Course created successfully.');
        }
        redirect('admin/courses.php');
    }

    foreach ($errors as $e) flash('error', $e);
    $course = array_merge((array)$course, $_POST); // keep input
}

$val = fn($k, $d = '') => e((string)($course[$k] ?? $d));

require __DIR__ . '/includes/header.php';
?>

<div class="mb-5">
  <a href="<?= e(admin_url('courses.php')) ?>" class="text-sm font-semibold text-slate-500 hover:text-primary">&larr; Back to courses</a>
</div>

<form method="post" enctype="multipart/form-data" class="max-w-5xl">
  <?= csrf_field() ?>
  <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

  <?php $submitLabel = $id ? 'Save Changes' : 'Save Course'; require __DIR__ . '/includes/_course_form_fields.php'; ?>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
