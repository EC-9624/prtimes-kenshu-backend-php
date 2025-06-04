<?php require(VIEW_PATH . 'partials/head.php') ?>

<div class="flex items-center justify-center">
    <div class="space-y-6 m-4 flex flex-col items-center w-full max-w-2xl">
        <!-- Back button -->
        <div class="self-start">
            <a href="/" class="text-blue-500 hover:underline text-sm flex items-center">
                ← Top
            </a>
        </div>
        <!-- Post content -->
        <div class="space-y-4 w-full">
            <div class="pb-4 w-full">
                <!-- Title -->
                <h1 class="text-2xl font-semibold text-gray-900">
                    <?= htmlspecialchars($data->title) ?>
                </h1>

                <!-- Thumbnail -->
                <?php if ($data->thumbnail_image_path): ?>
                    <img
                        src="<?= htmlspecialchars($data->thumbnail_image_path) ?>"
                        alt="<?= htmlspecialchars($data->title) ?>"
                        class="mt-2 w-full rounded">
                <?php else: ?>
                    <img
                        src="/img/image-placeholder.svg"
                        alt="No thumbnail"
                        class="mt-2 w-full rounded">
                <?php endif; ?>

                <!-- Author & Date -->
                <div class="text-sm text-gray-500 mt-1">
                    <p>
                        Created by
                        <a
                            href="/users/<?= htmlspecialchars($data->user_name) ?>"
                            class="text-blue-400 hover:cursor-pointer hover:border-b">
                            <?= htmlspecialchars($data->user_name) ?>
                        </a>
                    </p>
                    <div>
                        Published on
                        <time><?= $data->created_at->format('Y-m-d') ?></time>
                    </div>
                </div>
                <!-- Tags -->
                <?php if (!empty($data->tags_json)): ?>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php foreach ($data->tags_json as $tag): ?>
                            <a href="/categories/<?= htmlspecialchars($tag['slug']) ?>">
                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                    <?= htmlspecialchars($tag['name']) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- Post Text -->
                <div class="mt-4 text-gray-800 leading-relaxed">
                    <?= nl2br(htmlspecialchars($data->text)) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require(VIEW_PATH . 'partials/footer.php') ?>
