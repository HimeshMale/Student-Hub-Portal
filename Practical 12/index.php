<?php
require 'config.php';

// Only logged-in admins may use this page
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }

/* ---------- Poster upload with type + size validation ----------
   Returns: filename (saved), null (no file chosen), or false (invalid; $err filled) */
function save_poster(array $f, ?string &$err) {
    if ($f['error'] === UPLOAD_ERR_NO_FILE) return null;

    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        $err = 'Poster is too large. Maximum size is ' . MAX_POSTER_MB . ' MB.'; return false;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $err = 'Poster upload failed (error code ' . $f['error'] . ').'; return false;
    }
    if ($f['size'] > MAX_POSTER_MB * 1024 * 1024) {
        $err = 'Poster is too large. Maximum size is ' . MAX_POSTER_MB . ' MB.'; return false;
    }

    // Check the real file content, not the name the browser sent
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(ALLOWED_TYPES[$mime]) || @getimagesize($f['tmp_name']) === false) {
        $err = 'Invalid poster type. Only JPG, PNG or WEBP images are allowed.'; return false;
    }

    $name = bin2hex(random_bytes(12)) . '.' . ALLOWED_TYPES[$mime];   // random safe name
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) {
        $err = 'Could not save the poster. Check that the uploads folder is writable.'; return false;
    }
    return $name;
}
function remove_poster(?string $name) {
    if ($name && is_file(UPLOAD_DIR . basename($name))) @unlink(UPLOAD_DIR . basename($name));
}

/* ---------- POST actions: add / update / delete ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('error', 'Invalid request token. Please try again.');
        header('Location: index.php'); exit;
    }
    $action = $_POST['action'] ?? '';

    /* DELETE */
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $conn->prepare('SELECT poster FROM events WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            remove_poster($old['poster'] ?? null);
            flash('success', 'Event deleted successfully.');
        } else {
            flash('error', 'Delete failed: event not found or database error.');
        }
        $stmt->close();
        header('Location: index.php'); exit;
    }

    /* ADD / UPDATE */
    if ($action === 'add' || $action === 'update') {
        $id     = (int)($_POST['id'] ?? 0);
        $title  = trim($_POST['title'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $date   = trim($_POST['event_date'] ?? '');
        $venue  = trim($_POST['venue'] ?? '');
        $status = $_POST['status'] ?? '';
        $back   = 'index.php' . ($action === 'update' ? "?edit=$id" : '');

        $errors = [];
        if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Title is required (max 150 characters).';
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date)      $errors[] = 'Enter a valid event date.';
        if ($venue === '' || mb_strlen($venue) > 150)  $errors[] = 'Venue is required (max 150 characters).';
        if (!in_array($status, STATUSES, true))        $errors[] = 'Choose a valid status.';
        if ($errors) { flash('error', implode(' ', $errors)); header("Location: $back"); exit; }

        // Validate + save poster only after the other fields are OK
        $err = null;
        $poster = save_poster($_FILES['poster'] ?? ['error' => UPLOAD_ERR_NO_FILE], $err);
        if ($poster === false) { flash('error', $err); header("Location: $back"); exit; }

        if ($action === 'add') {
            $stmt = $conn->prepare('INSERT INTO events (title, description, event_date, venue, status, poster) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $title, $desc, $date, $venue, $status, $poster);
            $okMsg = 'Event added successfully.';
        } else {
            // Remember the old poster so it can be replaced
            $q = $conn->prepare('SELECT poster FROM events WHERE id = ?');
            $q->bind_param('i', $id);
            $q->execute();
            $old = $q->get_result()->fetch_assoc();
            $q->close();

            if ($poster !== null) {
                $stmt = $conn->prepare('UPDATE events SET title=?, description=?, event_date=?, venue=?, status=?, poster=? WHERE id=?');
                $stmt->bind_param('ssssssi', $title, $desc, $date, $venue, $status, $poster, $id);
            } else {
                $stmt = $conn->prepare('UPDATE events SET title=?, description=?, event_date=?, venue=?, status=? WHERE id=?');
                $stmt->bind_param('sssssi', $title, $desc, $date, $venue, $status, $id);
            }
            $okMsg = 'Event updated successfully.';
        }

        if ($stmt->execute()) {
            if ($action === 'update' && $poster !== null) remove_poster($old['poster'] ?? null);
            flash('success', $okMsg);
        } else {
            if ($poster) remove_poster($poster);   // don't leave an orphan file
            flash('error', 'Database error: ' . $stmt->error);
        }
        $stmt->close();
        header('Location: index.php'); exit;
    }
}

/* ---------- Load event for edit form ---------- */
$editing = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$editing) flash('error', 'Event not found.');
}

/* ---------- List (optional status filter) ---------- */
$filter = $_GET['status'] ?? '';
if (in_array($filter, STATUSES, true)) {
    $stmt = $conn->prepare('SELECT * FROM events WHERE status = ? ORDER BY event_date DESC');
    $stmt->bind_param('s', $filter);
} else {
    $filter = '';
    $stmt = $conn->prepare('SELECT * FROM events ORDER BY event_date DESC');
}
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Event Management</title>
<style>
  :root { --ink:#1d2433; --muted:#667085; --line:#d9dee7; --bg:#f5f7fb; --card:#fff;
          --accent:#1f5eff; --ok:#137a3d; --okbg:#e6f6ec; --err:#b42318; --errbg:#fdecea; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:system-ui,Segoe UI,Arial,sans-serif; background:var(--bg); color:var(--ink); }
  .wrap { max-width:1040px; margin:0 auto; padding:24px 16px 48px; }
  .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
  h1 { font-size:1.6rem; margin:0; }
  h2 { font-size:1.1rem; margin:0 0 12px; }
  .card { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:16px; margin-bottom:16px; }
  .msg { padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:.95rem; }
  .msg.success { background:var(--okbg); color:var(--ok); border:1px solid #b7e3c7; }
  .msg.error   { background:var(--errbg); color:var(--err); border:1px solid #f4b8b2; }
  .row { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
  label { display:flex; flex-direction:column; font-size:.85rem; color:var(--muted); gap:4px; flex:1 1 200px; }
  label.wide { flex-basis:100%; }
  input, select, textarea { padding:8px 10px; border:1px solid var(--line); border-radius:6px; font-size:.95rem; font-family:inherit; }
  input:focus, select:focus, textarea:focus, button:focus-visible, a:focus-visible { outline:2px solid var(--accent); outline-offset:1px; }
  button, .btn { padding:8px 14px; border:0; border-radius:6px; background:var(--accent); color:#fff;
                 font-size:.9rem; cursor:pointer; text-decoration:none; display:inline-block; }
  .btn.secondary { background:#e8ecf3; color:var(--ink); }
  button.danger { background:var(--err); }
  .hint { font-size:.8rem; color:var(--muted); }
  .table-wrap { overflow-x:auto; }
  table { width:100%; border-collapse:collapse; font-size:.93rem; }
  th, td { text-align:left; padding:10px 8px; border-bottom:1px solid var(--line); vertical-align:middle; }
  th { color:var(--muted); font-weight:600; }
  td img { width:56px; height:72px; object-fit:cover; border-radius:4px; border:1px solid var(--line); display:block; }
  .noimg { width:56px; height:72px; border:1px dashed var(--line); border-radius:4px; display:flex;
           align-items:center; justify-content:center; font-size:.7rem; color:var(--muted); text-align:center; }
  .badge { padding:2px 8px; border-radius:10px; font-size:.8rem; background:#e8ecf3; }
  .badge.Upcoming { background:#e0ebff; color:#1a4fd6; }
  .badge.Ongoing { background:#e6f6ec; color:var(--ok); }
  .badge.Cancelled { background:var(--errbg); color:var(--err); }
  td.actions { white-space:nowrap; }
  td.actions > * { display:inline-block; vertical-align:middle; }
  form.inline { margin:0 0 0 6px; }
  .empty { color:var(--muted); text-align:center; padding:24px; }
  .current { display:flex; align-items:center; gap:10px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h1>Event Management</h1>
    <div>Signed in as <strong><?= e($_SESSION['admin']) ?></strong> &nbsp;<a class="btn secondary" href="logout.php">Log out</a></div>
  </div>

  <?php if ($flash): ?>
    <div class="msg <?= e($flash['type']) ?>" role="alert"><?= e($flash['msg']) ?></div>
  <?php endif; ?>

  <!-- Add / Edit form -->
  <div class="card">
    <h2><?= $editing ? 'Edit event' : 'Add event' ?></h2>
    <form method="post" enctype="multipart/form-data" class="row">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><?php endif; ?>

      <label>Title
        <input name="title" required maxlength="150" value="<?= e($editing['title'] ?? '') ?>">
      </label>
      <label>Event date
        <input type="date" name="event_date" required value="<?= e($editing['event_date'] ?? '') ?>">
      </label>
      <label>Venue
        <input name="venue" required maxlength="150" value="<?= e($editing['venue'] ?? '') ?>">
      </label>
      <label>Status
        <select name="status" required>
          <?php foreach (STATUSES as $st): ?>
            <option <?= ($editing['status'] ?? 'Upcoming') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="wide">Description
        <textarea name="description" rows="3"><?= e($editing['description'] ?? '') ?></textarea>
      </label>
      <label class="wide">Poster (JPG, PNG or WEBP, max <?= MAX_POSTER_MB ?> MB)
        <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <?php if ($editing && $editing['poster']): ?>
          <span class="current">
            <img src="uploads/<?= e($editing['poster']) ?>" alt="Current poster" width="40">
            <span class="hint">Current poster. Choose a file only if you want to replace it.</span>
          </span>
        <?php endif; ?>
      </label>
      <button type="submit"><?= $editing ? 'Save changes' : 'Add event' ?></button>
      <?php if ($editing): ?><a class="btn secondary" href="index.php">Cancel</a><?php endif; ?>
    </form>
  </div>

  <!-- Filter -->
  <div class="card">
    <form method="get" class="row">
      <label>Show events with status
        <select name="status">
          <option value="">All</option>
          <?php foreach (STATUSES as $st): ?>
            <option <?= $st === $filter ? 'selected' : '' ?>><?= e($st) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button type="submit">Apply</button>
      <a class="btn secondary" href="index.php">Reset</a>
    </form>
  </div>

  <!-- Event list -->
  <div class="card">
    <h2>Events (<?= count($events) ?>)</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Poster</th><th>Title</th><th>Date</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$events): ?>
          <tr><td colspan="6" class="empty">No events found. Add one above or change the filter.</td></tr>
        <?php endif; ?>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td>
              <?php if ($ev['poster']): ?>
                <img src="uploads/<?= e($ev['poster']) ?>" alt="Poster for <?= e($ev['title']) ?>">
              <?php else: ?><div class="noimg">No poster</div><?php endif; ?>
            </td>
            <td><strong><?= e($ev['title']) ?></strong><br><span class="hint"><?= e(mb_strimwidth((string)$ev['description'], 0, 70, '...')) ?></span></td>
            <td><?= e(date('d M Y', strtotime($ev['event_date']))) ?></td>
            <td><?= e($ev['venue']) ?></td>
            <td><span class="badge <?= e($ev['status']) ?>"><?= e($ev['status']) ?></span></td>
            <td class="actions">
              <a class="btn secondary" href="?edit=<?= (int)$ev['id'] ?>">Edit</a>
              <form method="post" class="inline"
                    onsubmit="return confirm('Delete &quot;<?= e(addslashes($ev['title'])) ?>&quot; and its poster? This cannot be undone.');">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
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
</body>
</html>