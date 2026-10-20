<?php
require 'config.php';

// Branch code in the Student ID -> course name
$COURSES = [
    'DCE' => 'Computer Engineering',
    'DCS' => 'Computer Science',
    'DIT' => 'Information Technology',
];

/* ---------- Handle POST actions: add / update / delete ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('error', 'Invalid request token. Please try again.');
        header('Location: index.php'); exit;
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $sid = trim($_POST['student_id'] ?? '');
        $stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
        $stmt->bind_param('s', $sid);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            flash('success', 'Student deleted successfully.');
        } else {
            flash('error', 'Delete failed: record not found or database error.');
        }
        $stmt->close();
        header('Location: index.php'); exit;
    }

    if ($action === 'add' || $action === 'update') {
        $sid    = strtoupper(trim($_POST['student_id'] ?? ''));
        $name   = trim($_POST['name'] ?? '');
        $course = trim($_POST['course'] ?? '');

        // Server-side validation
        $errors = [];
        if (!preg_match('/^\d{2}(DCE|DCS|DIT)\d{3}$/', $sid, $m)) {
            $errors[] = 'Student ID must look like 25DCE011 (year + DCE/DCS/DIT + 3 digits).';
        } elseif ($course !== $COURSES[$m[1]]) {
            $errors[] = "ID $sid belongs to " . $COURSES[$m[1]] . ', so the course must match.';
        }
        if ($name === '' || mb_strlen($name) > 100)  $errors[] = 'Name is required (max 100 characters).';

        if ($errors) {
            flash('error', implode(' ', $errors));
            header('Location: index.php' . ($action === 'update' ? '?edit=' . urlencode($sid) : '')); exit;
        }

        if ($action === 'add') {
            $stmt = $conn->prepare('INSERT INTO students (student_id, name, course) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $sid, $name, $course);
            $okMsg = 'Student added successfully.';
        } else {
            $stmt = $conn->prepare('UPDATE students SET name = ?, course = ? WHERE student_id = ?');
            $stmt->bind_param('sss', $name, $course, $sid);
            $okMsg = 'Student updated successfully.';
        }

        if ($stmt->execute()) {
            flash('success', $okMsg);
        } elseif ($stmt->errno === 1062) {
            flash('error', 'A student with this ID already exists.');
        } else {
            flash('error', 'Database error: ' . $stmt->error);
        }
        $stmt->close();
        header('Location: index.php'); exit;
    }
}

/* ---------- Load student for edit form (SELECT one) ---------- */
$editing = null;
if (isset($_GET['edit'])) {
    $eid = trim($_GET['edit']);
    $stmt = $conn->prepare('SELECT student_id, name, course FROM students WHERE student_id = ?');
    $stmt->bind_param('s', $eid);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$editing) flash('error', 'Student not found.');
}

/* ---------- Search (ID/name/course) + filter (course) ---------- */
$search = trim($_GET['q'] ?? '');
$filter = trim($_GET['course'] ?? '');

$sql    = 'SELECT student_id, name, course FROM students WHERE 1=1';
$types  = '';
$params = [];

if ($search !== '') {
    $sql .= ' AND (student_id LIKE ? OR name LIKE ? OR course LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'sss';
    array_push($params, $like, $like, $like);
}
if ($filter !== '') {
    $sql .= ' AND course = ?';
    $types .= 's';
    $params[] = $filter;
}
$sql .= ' ORDER BY student_id ASC';

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Distinct courses for the filter dropdown
$courses = [];
$res = $conn->query('SELECT DISTINCT course FROM students ORDER BY course');
while ($r = $res->fetch_assoc()) $courses[] = $r['course'];

// Flash message (shown once)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Management</title>
<style>
  :root { --ink:#1d2433; --muted:#667085; --line:#d9dee7; --bg:#f5f7fb; --card:#fff;
          --accent:#1f5eff; --ok:#137a3d; --okbg:#e6f6ec; --err:#b42318; --errbg:#fdecea; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:system-ui,Segoe UI,Arial,sans-serif; background:var(--bg); color:var(--ink); }
  .wrap { max-width:960px; margin:0 auto; padding:24px 16px 48px; }
  h1 { font-size:1.6rem; margin:0 0 16px; }
  h2 { font-size:1.1rem; margin:0 0 12px; }
  .card { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:16px; margin-bottom:16px; }
  .msg { padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:.95rem; }
  .msg.success { background:var(--okbg); color:var(--ok); border:1px solid #b7e3c7; }
  .msg.error   { background:var(--errbg); color:var(--err); border:1px solid #f4b8b2; }
  .row { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
  label { display:flex; flex-direction:column; font-size:.85rem; color:var(--muted); gap:4px; flex:1 1 180px; }
  input, select { padding:8px 10px; border:1px solid var(--line); border-radius:6px; font-size:.95rem; }
  input:focus, select:focus, button:focus-visible, a:focus-visible { outline:2px solid var(--accent); outline-offset:1px; }
  button, .btn { padding:8px 14px; border:0; border-radius:6px; background:var(--accent); color:#fff;
                 font-size:.9rem; cursor:pointer; text-decoration:none; display:inline-block; }
  .btn.secondary { background:#e8ecf3; color:var(--ink); }
  button.danger { background:var(--err); }
  .table-wrap { overflow-x:auto; }
  table { width:100%; border-collapse:collapse; font-size:.93rem; }
  th, td { text-align:left; padding:10px 8px; border-bottom:1px solid var(--line); }
  th { color:var(--muted); font-weight:600; }
  td.actions { white-space:nowrap; display:flex; gap:6px; }
  form.inline { margin:0; }
  .empty { color:var(--muted); text-align:center; padding:24px; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Student Management</h1>

  <?php if ($flash): ?>
    <div class="msg <?= e($flash['type']) ?>" role="alert"><?= e($flash['msg']) ?></div>
  <?php endif; ?>

  <!-- Add / Edit form -->
  <div class="card">
    <h2><?= $editing ? 'Edit student' : 'Add student' ?></h2>
    <form method="post" class="row">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
      <label>Student ID
        <input name="student_id" required maxlength="20" placeholder="e.g. 25DCE011"
               pattern="[0-9]{2}(DCE|DCS|DIT|dce|dcs|dit)[0-9]{3}" title="Format: 25DCE011" id="sid"
               value="<?= e($editing['student_id'] ?? '') ?>" <?= $editing ? 'readonly' : '' ?>>
      </label>
      <label>Name
        <input name="name" required maxlength="100" placeholder="e.g. Rudra" value="<?= e($editing['name'] ?? '') ?>">
      </label>
      <label>Course
        <select name="course" id="course" required>
          <option value="">Select course</option>
          <?php foreach ($COURSES as $code => $cname): ?>
            <option value="<?= e($cname) ?>" <?= ($editing['course'] ?? '') === $cname ? 'selected' : '' ?>><?= e($cname) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button type="submit"><?= $editing ? 'Save changes' : 'Add student' ?></button>
      <?php if ($editing): ?><a class="btn secondary" href="index.php">Cancel</a><?php endif; ?>
    </form>
  </div>

  <!-- Search & filter -->
  <div class="card">
    <h2>Search and filter</h2>
    <form method="get" class="row">
      <label>Search by ID, name or course
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="e.g. 25DCE, rudra">
      </label>
      <label>Course
        <select name="course">
          <option value="">All courses</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= e($c) ?>" <?= $c === $filter ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button type="submit">Apply</button>
      <a class="btn secondary" href="index.php">Reset</a>
    </form>
  </div>

  <!-- Student list -->
  <div class="card">
    <h2>Students (<?= count($students) ?>)</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Student ID</th><th>Name</th><th>Course</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$students): ?>
          <tr><td colspan="4" class="empty">No students found. Add one above or change your search.</td></tr>
        <?php endif; ?>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= e($s['student_id']) ?></td>
            <td><?= e($s['name']) ?></td>
            <td><?= e($s['course']) ?></td>
            <td class="actions">
              <a class="btn secondary" href="?edit=<?= urlencode($s['student_id']) ?>">Edit</a>
              <form method="post" class="inline"
                    onsubmit="return confirm('Delete <?= e(addslashes($s['student_id'] . ' - ' . $s['name'])) ?>? This cannot be undone.');">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="student_id" value="<?= e($s['student_id']) ?>">
                <button type="submit" class="danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
  // Pick the course automatically from the branch code in the Student ID
  const map = { DCE: 'Computer Engineering', DCS: 'Computer Science', DIT: 'Information Technology' };
  document.getElementById('sid').addEventListener('input', function () {
    const m = this.value.toUpperCase().match(/^\d{2}(DCE|DCS|DIT)/);
    if (m) document.getElementById('course').value = map[m[1]];
  });
</script>
</body>
</html>