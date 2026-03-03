<?php
/*
 * unified upload hub
 */
require_once '../includes/db_connect.php';
session_start();

// need to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$root = "../";
$error = "";
$success = "";
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Student";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type']; // audio, video, blog
    $title = trim($_POST['title']);
    $author = trim($_POST['author']); // Custom author name or user name
    $description = ($type === 'blog') ? $_POST['rich_description'] : trim($_POST['description']);

    // override author name if needed
    $display_author = !empty($author) ? $author : $user_name;

    if (empty($title) || empty($type)) {
        $error = "Title and Type are required.";
    } else {
        $upload_dir = "../uploads/" . $type . "s/";
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $target_file = "";
        $thumb_path = "assets/images/image.jpg"; // Default

        // handle audio or video files
        if ($type !== 'blog') {
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['media_file'];
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);

                // prefix filename for audio filters
                $prefix = "";
                if ($type === 'audio' && isset($_POST['audio_category'])) {
                    $prefix = $_POST['audio_category'] . "_";
                }

                $file_name = $prefix . time() . "_" . uniqid() . "." . $file_ext;
                $target_file = "uploads/" . $type . "s/" . $file_name;

                if (!move_uploaded_file($file['tmp_name'], "../" . $target_file)) {
                    $error = "Failed to upload " . $type . " file.";
                }
            } else {
                $error = "Please select a " . $type . " file to upload.";
            }
        }

        // handle cover image
        if (empty($error)) {
            if (isset($_FILES['thumb_file']) && $_FILES['thumb_file']['error'] === UPLOAD_ERR_OK) {
                $thumb = $_FILES['thumb_file'];
                $thumb_ext = pathinfo($thumb['name'], PATHINFO_EXTENSION);
                $thumb_name = time() . "_" . uniqid() . "." . $thumb_ext;
                $thumb_target = "uploads/thumbs/" . $thumb_name;

                if (!is_dir("../uploads/thumbs/"))
                    mkdir("../uploads/thumbs/", 0777, true);

                if (move_uploaded_file($thumb['tmp_name'], "../" . $thumb_target)) {
                    $thumb_path = $thumb_target;
                }
            }
        }

        // save to database
        if (empty($error)) {
            try {
                // blogs use a dummy path
                $final_content_path = ($type === 'blog') ? 'assets/Blogs/Posts.txt' : $target_file;

                $stmt = $pdo->prepare("INSERT INTO posts (user_id, type, title, description, content_path, thumbnail_path, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $type, $title, $description, $final_content_path, $thumb_path]);

                $success = "Amazing! Your " . ucfirst($type) . " is now live on TalentHUB.";
            } catch (PDOException $e) {
                $error = "System Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - UIU TalentHUB</title>
    <link rel="stylesheet" href="Auth/auth.css">
    <link rel="stylesheet" href="../shared/navbar.css">
    <!-- Quill.js CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        .upload-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 120px auto 40px;
        }

        .upload-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .upload-header h1 {
            font-size: 32px;
            font-weight: 900;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .upload-header p {
            color: #666;
            font-weight: 600;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 16px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            border-radius: 12px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .tab-btn.active {
            background: white;
            color: #ff5e00;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width {
            grid-column: span 2;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
            margin-left: 4px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 14px 18px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #ff5e00;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 94, 0, 0.1);
        }

        /* File Input Styling */
        .file-input-wrapper {
            position: relative;
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
        }

        .file-input-wrapper:hover {
            border-color: #ff5e00;
            background: #fffaf7;
        }

        .file-input-wrapper input {
            opacity: 0;
            position: absolute;
            inset: 0;
            cursor: pointer;
        }

        .file-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }

        /* Quill Styles */
        #quill-editor {
            height: 350px;
            background: white;
            border-radius: 0 0 14px 14px;
            border: 1.5px solid #e2e8f0;
            border-top: none;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        .ql-toolbar.ql-snow {
            border: 1.5px solid #e2e8f0;
            border-radius: 14px 14px 0 0;
            background: #f8fafc;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff5e00, #ff8c00);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 800;
            margin-top: 30px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 94, 0, 0.2);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(255, 94, 0, 0.3);
        }

        .status-msg {
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }

        .error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="upload-card fade-in">
        <div class="upload-header">
            <h1>Showcase Your Genius</h1>
            <p>Upload your latest work to UIU TalentHUB</p>
        </div>

        <?php if ($error): ?>
            <div class="status-msg error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="status-msg success">
                <?php echo $success; ?>
                <br><a href="../index.php" style="color: inherit; text-decoration: underline;">View it on Trending →</a>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('audio')">Audio</button>
            <button class="tab-btn" onclick="switchTab('video')">Video</button>
            <button class="tab-btn" onclick="switchTab('blog')">Journal</button>
        </div>

        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="type" id="post_type" value="audio">

            <div class="form-grid">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Give it a catchy title" required>
                </div>
                <div class="form-group">
                    <label>Author / Credit</label>
                    <input type="text" name="author" placeholder="Optional override">
                </div>

                <div class="form-group full-width" id="audio-category-field">
                    <label>Category</label>
                    <select name="audio_category">
                        <option value="music">Song / Music</option>
                        <option value="poem">Poem / Poetry</option>
                        <option value="podcast">Podcast</option>
                        <option value="story">Story</option>
                    </select>
                </div>

                <!-- Media File (Hidden for Blog) -->
                <div class="form-group full-width" id="media-field">
                    <label id="media-label">Media File</label>
                    <div class="file-input-wrapper">
                        <span class="file-label" id="file-status">Click or drag to upload audio file</span>
                        <input type="file" name="media_file" id="media_file" onchange="updateFileLabel(this)">
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="form-group full-width">
                    <label>Cover Image</label>
                    <div class="file-input-wrapper">
                        <span class="file-label" id="thumb-status">Upload a beautiful cover (Optional)</span>
                        <input type="file" name="thumb_file" id="thumb_file" onchange="updateThumbLabel(this)">
                    </div>
                </div>

                <!-- Description (Textarea for A/V, Quill for Blog) -->
                <div class="form-group full-width" id="desc-container">
                    <label>Description</label>
                    <textarea name="description" id="plain-desc" rows="5"
                        placeholder="Tell us about this work..."></textarea>

                    <div id="quill-wrapper" style="display: none;">
                        <div id="quill-editor"></div>
                        <input type="hidden" name="rich_description" id="rich_description">
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="submit-text">Publish Audio</button>
        </form>
    </div>

    <script>
        // Tab Management
        function switchTab(type) {
            // Update buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.innerText.toLowerCase() === type || (btn.innerText === 'Journal' && type === 'blog'));
            });

            // Update hidden field
            document.getElementById('post_type').value = type;

            // Update UI elements
            const mediaField = document.getElementById('media-field');
            const mediaLabel = document.getElementById('media-label');
            const fileStatus = document.getElementById('file-status');
            const plainDesc = document.getElementById('plain-desc');
            const quillWrapper = document.getElementById('quill-wrapper');
            const submitBtn = document.getElementById('submit-text');
            const audioCategoryField = document.getElementById('audio-category-field');

            if (type === 'blog') {
                mediaField.style.display = 'none';
                audioCategoryField.style.display = 'none';
                plainDesc.style.display = 'none';
                quillWrapper.style.display = 'block';
                submitBtn.innerText = 'Publish Journal';
                document.getElementById('media_file').required = false;
            } else if (type === 'video') {
                mediaField.style.display = 'block';
                audioCategoryField.style.display = 'none';
                plainDesc.style.display = 'block';
                quillWrapper.style.display = 'none';
                submitBtn.innerText = 'Publish Video';
                mediaLabel.innerText = 'Video File';
                fileStatus.innerText = 'Click or drag to upload video file';
                document.getElementById('media_file').required = true;
            } else {
                mediaField.style.display = 'block';
                audioCategoryField.style.display = 'block';
                plainDesc.style.display = 'block';
                quillWrapper.style.display = 'none';
                submitBtn.innerText = 'Publish Audio';
                mediaLabel.innerText = 'Audio File';
                fileStatus.innerText = 'Click or drag to upload audio file';
                document.getElementById('media_file').required = true;
            }
        }

        function updateFileLabel(input) {
            const label = document.getElementById('file-status');
            label.innerText = input.files.length > 0 ? input.files[0].name : "Click or drag to upload file";
        }

        function updateThumbLabel(input) {
            const label = document.getElementById('thumb-status');
            label.innerText = input.files.length > 0 ? input.files[0].name : "Upload a beautiful cover (Optional)";
        }

        // Quill Initialization
        var quill = new Quill('#quill-editor', {
            theme: 'snow',
            placeholder: 'Start writing your story...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });

        // Sync Quill to hidden input
        document.getElementById('uploadForm').onsubmit = function () {
            if (document.getElementById('post_type').value === 'blog') {
                document.getElementById('rich_description').value = quill.root.innerHTML;
            }
        };

        // Initialize default tab
        switchTab('audio');
    </script>

    <?php include '../includes/navbar.php'; ?>
    <script src="../shared/navbar.js"></script>
</body>

</html>