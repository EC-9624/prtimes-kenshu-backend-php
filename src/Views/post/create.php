<?php require(VIEW_PATH . 'partials/head.php') ?>
<div class="max-w-3xl mt-10 mx-auto p-6 bg-white  ">
    <h1 class="text-2xl font-semibold mb-4">Create New Post</h1>

    <form action="/create-post" method="POST" class="space-y-6">
        <div>
            <label for="post_title" class="block text-sm font-medium text-gray-700 mb-1">Post Title:</label>
            <input
                type="text"
                id="post_title"
                name="title"
                value="My Awesome Post"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="post_text" class="block text-sm font-medium text-gray-700 mb-1">Post Content:</label>
            <textarea
                id="post_text"
                name="text"
                rows="5"
                cols="50"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">This is the detailed content of my post.</textarea>
        </div>

        <div>
            <label for="tag_slugs" class="block text-sm font-medium text-gray-700 mb-1">Select Tags:</label>
            <select
                name="tag_slugs[]"
                id="tag_slugs"
                multiple
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-48">
                <option value="technology">テクノロジー</option>
                <option value="mobile">モバイル</option>
                <option value="apps">アプリ</option>
                <option value="entertainment">エンタメ</option>
                <option value="beauty">ビューティー</option>
                <option value="fashion">ファッション</option>
                <option value="lifestyle">ライフスタイル</option>
                <option value="business">ビジネス</option>
                <option value="gourmet">グルメ</option>
                <option value="sports">スポーツ</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">
                On Windows/Linux: Hold <kbd class="font-semibold">Ctrl</kbd> and click to select multiple.<br>
                On Mac: Hold <kbd class="font-semibold">Command</kbd> and click to select multiple.
            </p>
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
