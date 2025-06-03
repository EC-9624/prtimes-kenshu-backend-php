<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/js/tailwindcss.js"></script>

    <title><?= htmlspecialchars($title) ?></title>
</head>

<body>
    <header class="flex justify-between bg-gray-100 px-10 py-4 shadow-md">
        <a href="/">
            <img class="mx-auto h-10 w-auto" src="/img/mark.svg" alt="Your Company">
        </a>
        <div class="flex items-center justify-end gap-4">
            <?php if (isset($_SESSION['user_name'], $_SESSION['email'])): ?>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-700">
                        <div><span class="font-semibold">Name:</span> <?= htmlspecialchars($_SESSION['user_name']) ?></div>
                        <div><span class="font-semibold">Email:</span> <?= htmlspecialchars($_SESSION['email']) ?></div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md" href="/create-post">
                        Post
                    </a>
                    <form action="/logout" method="GET">
                        <button class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md">
                            Logout
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <a class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md" href="/login">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </header>
