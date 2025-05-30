<?php require(VIEW_PATH . 'partials/head.php') ?>

<div class="flex items-center justify-center">
    <ul class="space-y-6 m-4 flex flex-col items-center">
        <?php foreach ($data as $post): ?>
            <li class="border-b pb-4 w-full max-w-2xl last:border-none">
                <h2 class="text-2xl font-semibold">
                    <a href="/posts/<?= htmlspecialchars($post->slug) ?>" class="text-blue-600 hover:underline">
                        <?= htmlspecialchars($post->title) ?>
                    </a>
                </h2>
                <?php if ($post->thumbnail_image_path): ?>
                    <img src="<?= htmlspecialchars($post->thumbnail_image_path) ?>" alt="<?= htmlspecialchars($post->title) ?>" class="mt-2 w-full max-w-md rounded">
                <?php else: ?>
                    <img src="/img/image-placeholder.svg" class="mt-2 w-full max-w-md rounded">
                <?php endif; ?>
                <div class="text-sm text-gray-500 mt-1">
                    Published on <?= $post->created_at->format('Y-m-d') ?>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <?php foreach ($post->tags_json as $tag): ?>
                        <a href="/">
                            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                <?= htmlspecialchars($tag['name']) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php require(VIEW_PATH . 'partials/footer.php') ?>
