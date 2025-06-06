<?php
// echo '<pre>';
// print_r($post);
// print_r($tags);
// echo '</pre>';
?>
<?php require(VIEW_PATH . 'partials/head.php') ?>

<div class="max-w-3xl my-4 mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Edit Post</h1>

    <?php if (count($errors) > 0): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc pl-5 space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/posts/<?= htmlspecialchars($post['slug']) ?>/edit" method="POST" class="space-y-6">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
        <input type="hidden" name="author_id" value="<?= htmlspecialchars($post['author_id']) ?>">

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Post Title:</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($old['title'] ?? $post['title']) ?>"
                placeholder="My First Post"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="text" class="block text-sm font-medium text-gray-700 mb-1">Post Content:</label>
            <textarea
                id="text"
                name="text"
                rows="10"
                cols="50"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($old['text'] ?? $post['text']) ?></textarea>
        </div>


        <?php
        $oldTagSlugs = $old['tag_slugs'] ?? array_column($tags, 'slug');
        function isChecked($slug, $oldTagSlugs)
        {
            return in_array($slug, $oldTagSlugs) ? 'checked' : '';
        }
        ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Tags:</label>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-y-2">
                <?php
                $tags = [
                    'technology' => 'テクノロジー',
                    'mobile' => 'モバイル',
                    'apps' => 'アプリ',
                    'entertainment' => 'エンタメ',
                    'beauty' => 'ビューティー',
                    'fashion' => 'ファッション',
                    'lifestyle' => 'ライフスタイル',
                    'business' => 'ビジネス',
                    'gourmet' => 'グルメ',
                    'sports' => 'スポーツ'
                ];
                foreach ($tags as $slug => $label): ?>
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="tag_<?= $slug ?>"
                            name="tag_slugs[]"
                            value="<?= $slug ?>"
                            <?= isChecked($slug, $oldTagSlugs) ?>
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="tag_<?= $slug ?>" class="ml-2 block text-sm text-gray-900"><?= $label ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <input
                type="submit"
                value="Update Post"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
        </div>
    </form>
</div>

<?php require(VIEW_PATH . 'partials/footer.php') ?>
