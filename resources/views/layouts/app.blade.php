<!DOCTYPE html>
<html>
<head>
    <title>APRM System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            background: #f4f6f9;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: #1e2a38;
            color: white;
        }

        .sidebar h3 {
            padding: 25px;
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: #0e8203;
        }

        .sidebar .active {
            background: #14ad06;
        }

        .content {
            margin-left: 260px;
            padding: 30px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h3>
        APRM System
    </h3>

    <a href="{{ route('home') }}"
       class="{{ request()->routeIs('home') ? 'active' : '' }}">

        <i class="fa fa-chart-line"></i>
        Dashboard
    </a>

    <a href="{{ route('students.index') }}"
       class="{{ request()->routeIs('students.*') ? 'active' : '' }}">

        <i class="fa fa-users"></i>
        Students
    </a>

    <a href="{{ route('reports.index') }}"
       class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">

        <i class="fa fa-file"></i>
        Reports
    </a>

    {{-- SCHOOL YEAR MANAGEMENT --}}
    <a href="{{ route('semesters.index') }}"
       class="{{ request()->routeIs('semesters.*') ? 'active' : '' }}">

        <i class="fa fa-calendar-days"></i>
        School Year Management
    </a>

    <form action="{{ route('logout') }}" method="POST">
        @csrf

        <button type="submit"
                style="
                    background:none;
                    border:none;
                    color:white;
                    width:100%;
                    text-align:left;
                    padding:15px 25px;
                    cursor:pointer;
                ">

            <i class="fa fa-right-from-bracket"></i>
            Logout
        </button>
    </form>

</div>

<!-- PAGE CONTENT -->
<div class="content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>