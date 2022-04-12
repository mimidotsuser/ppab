<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="./css/reports/main.css">
@yield('styles')

<body>

@yield('body')

@hasSection('footer')
<footer class="mt-0">
    @yield('footer')
</footer>
@endif


</body>
</html>
