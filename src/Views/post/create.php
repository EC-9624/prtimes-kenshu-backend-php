<?php require(VIEW_PATH . 'partials/head.php') ?>
<div class="max-w-3xl my-4 mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Create New Post</h1>
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc pl-5 space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/create-post" method="POST" class="space-y-6" enctype="multipart/form-data">

        <div>
            <label for="post_title" class="block text-sm font-medium text-gray-700 mb-1">Post Title:</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($old['title'] ?? '') ?>"

                placeholder="My First Post"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="post_slug" class="block text-sm font-medium text-gray-700 mb-1">Post URL:</label>
            <input
                type="text"
                id="slug"
                name="slug"
                value="<?= htmlspecialchars($old['slug'] ?? '') ?>"

                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="post_text" class="block text-sm font-medium text-gray-700 mb-1">Post Content:</label>
            <textarea
                id="text"
                name="text"
                rows="10"
                cols="50"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($old['text'] ?? '') ?></textarea>
        </div>

        <div>
            <label for="thumbnail_image" class="block text-sm font-medium text-gray-700 mb-1">Thumbnail Image:</label>
            <input
                type="file"
                id="thumbnail_image"
                name="thumbnail_image"
                accept="image/jpeg, image/png, image/gif, image/webp"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG, GIF, WebP</p>
        </div>

        <div>
            <label for="alt_text" class="block text-sm font-medium text-gray-700 mb-1">Image Alt Text (for accessibility):</label>
            <input
                type="text"
                id="alt_text"
                name="alt_text"
                value="<?= htmlspecialchars($old['alt_text'] ?? '') ?>"
                placeholder="A descriptive text for the image"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <?php
        $oldTagSlugs = $old['tag_slugs'] ?? [];
        function isChecked($slug, $oldTagSlugs)
        {
            return in_array($slug, $oldTagSlugs) ? 'checked' : '';
        }
        ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Tags:</label>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-y-2">
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_technology"
                        name="tag_slugs[]"
                        value="technology"
                        <?= isChecked('technology', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_technology" class="ml-2 block text-sm text-gray-900">テクノロジー</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_mobile"
                        name="tag_slugs[]"
                        value="mobile"
                        <?= isChecked('mobile', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_mobile" class="ml-2 block text-sm text-gray-900">モバイル</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_apps"
                        name="tag_slugs[]"
                        value="apps"
                        <?= isChecked('apps', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_apps" class="ml-2 block text-sm text-gray-900">アプリ</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_entertainment"
                        name="tag_slugs[]"
                        value="entertainment"
                        <?= isChecked('entertainment', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_entertainment" class="ml-2 block text-sm text-gray-900">エンタメ</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_beauty"
                        name="tag_slugs[]"
                        value="beauty"
                        <?= isChecked('beauty', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_beauty" class="ml-2 block text-sm text-gray-900">ビューティー</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_fashion"
                        name="tag_slugs[]"
                        value="fashion"
                        <?= isChecked('fashion', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_fashion" class="ml-2 block text-sm text-gray-900">ファッション</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_lifestyle"
                        name="tag_slugs[]"
                        value="lifestyle"
                        <?= isChecked('lifestyle', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_lifestyle" class="ml-2 block text-sm text-gray-900">ライフスタイル</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_business"
                        name="tag_slugs[]"
                        value="business"
                        <?= isChecked('business', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_business" class="ml-2 block text-sm text-gray-900">ビジネス</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_gourmet"
                        name="tag_slugs[]"
                        value="gourmet"
                        <?= isChecked('gourmet', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_gourmet" class="ml-2 block text-sm text-gray-900">グルメ</label>
                </div>
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="tag_sports"
                        name="tag_slugs[]"
                        value="sports"
                        <?= isChecked('sports', $oldTagSlugs) ?>
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="tag_sports" class="ml-2 block text-sm text-gray-900">スポーツ</label>
                </div>
            </div>
        </div>

        <div>
            <input
                type="submit"
                value="Submit Post"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
        </div>
    </form>
</div>
<?php require(VIEW_PATH . 'partials/footer.php') ?>
