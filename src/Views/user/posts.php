<?php require(VIEW_PATH . 'partials/head.php') ?>
<div class="flex items-center justify-center px-4 mx-auto my-4">
    <div class="w-full max-w-3xl">
        <h1 class="text-4xl font-bold mb-6 text-center"><?= $user->getUserName() ?>'s posts</h1>

        <div class="grid grid-cols-1 gap-6 ">
            <?php foreach ($posts as $post): ?>
                <div class="flex gap-4 items-start">
                    <div>
                        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="rounded-lg aspect-[16/9] max-w-md w-full h-28 mb-4" />
                    </div>
                    <div class="bg-white rounded-2xl flex flex-col flex-1">
                        <h2 class="text-xl font-semibold mb-1"><?= htmlspecialchars($post['title']) ?></h2>
                        <p class="text-sm text-gray-600 mb-2">By <?= htmlspecialchars($post['author']) ?></p>
                        <p class="text-xs text-gray-500 mb-2"><?= date('F j, Y, g:i a', strtotime($post['created_at'])) ?></p>
                        <a href="/posts/<?= htmlspecialchars($post['slug']) ?>" class="mt-auto text-sm text-blue-600 hover:underline">Read more →</a>
                    </div>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $post['author_id']): ?>
                        <div class="flex gap-2">
                            <a href="/posts/<?= $post['slug'] ?>/edit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">Edit</a>
                            <a href="/posts/<?= $post['slug'] ?>/delete" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require(VIEW_PATH . 'partials/footer.php') ?>
