<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check login
if (!is_logged_in()) {
    http_response_code(403);
    exit('Not logged in');
}

$user_id = $_SESSION['user_id'];
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$posts = get_feed_posts($user_id, $limit, $offset);

foreach ($posts as $post): ?>
    <div class="card post">
        <div class="card-body">
            <?php if (!empty($post['shared_post_id'])): // Shared post ?>
                <?php
                // Fetch the original post and user
                $orig_stmt = $conn->prepare("SELECT p.*, u.username, u.full_name, u.profile_pic FROM posts p JOIN users u ON p.user_id = u.user_id WHERE p.post_id = ?");
                $orig_stmt->bind_param("i", $post['shared_post_id']);
                $orig_stmt->execute();
                $orig_result = $orig_stmt->get_result();
                $original = $orig_result->fetch_assoc();
                // Fetch the sharer's details
                $sharer_stmt = $conn->prepare("SELECT username, full_name, profile_pic FROM users WHERE user_id = ?");
                $sharer_stmt->bind_param("i", $post['user_id']);
                $sharer_stmt->execute();
                $sharer_result = $sharer_stmt->get_result();
                $sharer = $sharer_result->fetch_assoc();
                ?>
                <div class="post-header d-flex align-items-center">
                    <a href="profile.php?username=<?php echo htmlspecialchars($sharer['username']); ?>" class="text-decoration-none">
                        <img src="assets/images/<?php echo $sharer['profile_pic']; ?>" alt="Sharer" class="post-avatar">
                    </a>
                    <div class="post-user flex-grow-1">
                        <h6 class="post-username">
                            <a href="profile.php?username=<?php echo htmlspecialchars($sharer['username']); ?>" class="text-decoration-none text-white">
                                <?php echo $sharer['full_name']; ?>
                            </a> 
                            <span style='font-weight:400;color:#F187EA;'>shared</span> 
                            <?php echo $original ? $original['full_name'] : '[Deleted]'; ?>'s post
                        </h6>
                        <p class="post-time">
                            <a href="profile.php?username=<?php echo htmlspecialchars($sharer['username']); ?>" class="text-decoration-none text-white">
                                @<?php echo $sharer['username']; ?>
                            </a> · 
                            <?php echo format_date($post['created_at']); ?>
                            <span class="privacy-indicator" title="<?php echo ucfirst($post['visibility']); ?>">
                                <?php if ($post['visibility'] === 'public'): ?>
                                    <i class="fas fa-globe"></i>
                                <?php elseif ($post['visibility'] === 'friends'): ?>
                                    <i class="fas fa-user-friends"></i>
                                <?php else: ?>
                                    <i class="fas fa-user-lock"></i>
                                <?php endif; ?>
                            </span>
                        </p>
                    </div>
                    <div class="post-menu ms-auto">
                        <button class="menu-trigger" type="button" tabindex="0"><i class="fas fa-ellipsis-h"></i></button>
                        <div class="dropdown-menu">
                            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                                <button class="dropdown-item edit-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Edit Post</button>
                                <button class="dropdown-item edit-privacy-btn" data-post-id="<?php echo $post['post_id']; ?>" data-visibility="<?php echo $post['visibility']; ?>">Edit Privacy</button>
                                <button class="dropdown-item delete-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Delete Post</button>
                            <?php else: ?>
                                <button class="dropdown-item report-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Report Post</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="post-content" style="background:rgba(255,255,255,0.05);border-radius:10px;padding:10px;">
                    <?php if ($original): ?>
                        <div class="d-flex align-items-center mb-2">
                            <img src="assets/images/<?php echo $original['profile_pic']; ?>" alt="Original User" class="post-avatar" style="width:32px;height:32px;margin-right:8px;">
                            <strong><?php echo $original['full_name']; ?></strong> <span style="color:#F187EA;">@<?php echo $original['username']; ?></span>
                        </div>
                        <p><?php echo nl2br(html_entity_decode(htmlspecialchars($original['content']))); ?></p>
                        <?php if ($original['image']): ?>
                            <img src="assets/images/<?php echo $original['image']; ?>" alt="Post image" class="post-image">
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">[Original post deleted]</p>
                    <?php endif; ?>
                </div>
            <?php else: // Normal post ?>
                <div class="post-header d-flex align-items-center">
                    <a href="profile.php?username=<?php echo htmlspecialchars($post['username']); ?>" class="text-decoration-none">
                        <img src="assets/images/<?php echo $post['profile_pic']; ?>" alt="User" class="post-avatar">
                    </a>
                    <div class="post-user flex-grow-1">
                        <h6 class="post-username">
                            <a href="profile.php?username=<?php echo htmlspecialchars($post['username']); ?>" class="text-decoration-none text-white">
                                <?php echo $post['full_name']; ?>
                            </a>
                        </h6>
                        <p class="post-time">
                            <a href="profile.php?username=<?php echo htmlspecialchars($post['username']); ?>" class="text-decoration-none text-white">
                                @<?php echo $post['username']; ?>
                            </a> · 
                            <?php echo format_date($post['created_at']); ?>
                            <span class="privacy-indicator" title="<?php echo ucfirst($post['visibility']); ?>">
                                <?php if ($post['visibility'] === 'public'): ?>
                                    <i class="fas fa-globe"></i>
                                <?php elseif ($post['visibility'] === 'friends'): ?>
                                    <i class="fas fa-user-friends"></i>
                                <?php else: ?>
                                    <i class="fas fa-user-lock"></i>
                                <?php endif; ?>
                            </span>
                        </p>
                    </div>
                    <div class="post-menu ms-auto">
                        <button class="menu-trigger" type="button" tabindex="0"><i class="fas fa-ellipsis-h" style="color: var(--accent);"></i></button>
                        <div class="dropdown-menu">
                            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                                <button class="dropdown-item edit-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Edit Post</button>
                                <button class="dropdown-item edit-privacy-btn" data-post-id="<?php echo $post['post_id']; ?>" data-visibility="<?php echo $post['visibility']; ?>">Edit Privacy</button>
                                <button class="dropdown-item delete-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Delete Post</button>
                            <?php else: ?>
                                <button class="dropdown-item report-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Report Post</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="post-content">
                    <p><?php echo nl2br(html_entity_decode(htmlspecialchars($post['content']))); ?></p>
                    <?php if ($post['image']): ?>
                        <img src="assets/images/<?php echo $post['image']; ?>" alt="Post image" class="post-image">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="post-footer">
                <button class="post-action <?php echo has_user_liked_post($user_id, $post['post_id']) ? 'text-primary' : ''; ?> like-btn" data-post-id="<?php echo $post['post_id']; ?>">
                    <i class="<?php echo has_user_liked_post($user_id, $post['post_id']) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                    <span class="like-count"><?php echo $post['like_count']; ?></span>
                </button>
                <button class="post-action comment-btn" data-post-id="<?php echo $post['post_id']; ?>">
                    <i class="bi bi-chat-fill"></i>
                    <span class="comment-count"><?php echo $post['comment_count']; ?></span>
                </button>
                <button class="share-btn <?php echo has_user_shared_post($_SESSION['user_id'], $post['post_id']) ? 'shared' : ''; ?>" data-post-id="<?php echo $post['post_id']; ?>">
                    <i class="fas fa-share"></i>
                    <span class="share-count"><?php echo $post['share_count']; ?></span>
                </button>
            </div>
            <div class="card-footer">
                <div class="comment-section" id="comments-<?php echo $post['post_id']; ?>">
                    <?php if ($post['comment_count'] > 0): ?>
                        <div class="text-center mb-2">
                            <button class="btn btn-sm btn-link load-comments" data-post-id="<?php echo $post['post_id']; ?>">
                                <i class="fa-solid fa-caret-down"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <form class="comment-form mt-3" data-post-id="<?php echo $post['post_id']; ?>">
                    <div class="input-group">
                        <input type="text" class="form-control comment-input" placeholder="Write a comment..." required>
                        <button class="btn btn-outline" type="submit"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
