<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPA Sample</title>
    <style>
        .page {
            display: none;
        }
        .active {
            display: block;
        }
    </style>
</head>
<body>
    <nav>
        <button onclick="navigate('home')">Home</button>
        <button onclick="navigate('about')">About</button>
        <button onclick="navigate('contact')">Contact</button>
    </nav>
    <div id="content">
        <div id="home" class="page">
            <h1>Home</h1>
            <p>Welcome to the home page.</p>
        </div>
        <div id="about" class="page active">
            <h1>About</h1>
            <p>This is the about page.</p>
        </div>
        <div id="contact" class="page">
            <h1>Contact</h1>
            <p>Contact us at contact@example.com.</p>
        </div>
    </div>

    <script>
        function navigate(pageId) {
            // すべてのページを非表示にする
            const pages = document.querySelectorAll('.page');
            pages.forEach(page => page.classList.remove('active'));

            // 指定されたページのみを表示する
            const activePage = document.getElementById(pageId);
            if (activePage) {
                activePage.classList.add('active');
            }
        }
    </script>
</body>
</html>
