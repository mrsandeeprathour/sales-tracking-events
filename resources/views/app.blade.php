<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">


        <meta name="shopify-api-key" content="" />
        <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
            <title>Laravel</title>
            <script>
        </script>
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        <!-- In this article, we are going to use JSX syntax for React components -->
        @inertiaHead
    </head>
    <body>
        @inertia
        <div id="app"></div>
    </body>
</html>
