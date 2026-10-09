<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>
        Welcome,
        {{ session('username') }}
    </h2>

    <a href="/logout" class="btn btn-danger mt-3">
        Logout
    </a>

</div>

</body>
</html>