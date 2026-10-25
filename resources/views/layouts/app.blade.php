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
            background: #a1a4a7;
            color: #212529;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #ffffff;
            color: #212529;
            border-right: 1px solid #eeeeee;
            padding: 12px 14px;
            box-sizing: border-box;
            overflow-y: auto;
        }

        /* SYSTEM TITLE */
        .sidebar h3 {
            padding: 20px 12px;
            margin: 0 0 22px;
            font-size: 23px;
            font-weight: 700;
            color: #111111;
            text-align: center;
            letter-spacing: 0.3px;
        }

        /* SIDEBAR LINKS */
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #212529;
            background: transparent;
            text-decoration: none;
            padding: 13px 18px;
            margin-bottom: 8px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            transition:
                background-color 0.25s ease,
                color 0.25s ease,
                transform 0.2s ease;
        }

        /* ICONS */
        .sidebar a i,
        .sidebar form button i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            transition: color 0.25s ease;
        }

        /* HOVER EFFECT */
        .sidebar a:hover {
            background: #0e8203;
            color: #ffffff;
            transform: translateX(3px);
        }

        .sidebar a:hover i {
            color: #ffffff;
        }

        /* ACTIVE PAGE */
        .sidebar a.active {
            background: #0e8203;
            color: #ffffff;
            font-weight: 600;
        }

        .sidebar a.active i {
            color: #ffffff;
        }

        /* LOGOUT FORM */
        .sidebar form {
            margin-top: 18px;
        }

        .sidebar form button {
            display: flex;
            align-items: center;
            gap: 13px;
            background: transparent;
            border: none;
            color: #212529;
            width: 100%;
            text-align: left;
            padding: 13px 18px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition:
                background-color 0.25s ease,
                color 0.25s ease,
                transform 0.2s ease;
        }

        /* LOGOUT HOVER */
        .sidebar form button:hover {
            background: #0e8203;
            color: #ffffff;
            transform: translateX(3px);
        }

        .sidebar form button:hover i {
            color: #ffffff;
        }

        /* PAGE CONTENT */
        .content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
            background: #8f969d;
        }

        /* KEEP SIDEBAR USABLE ON SMALL SCREENS */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
                padding: 10px;
            }

            .sidebar h3 {
                font-size: 20px;
            }

            .sidebar a,
            .sidebar form button {
                padding: 12px 14px;
                font-size: 14px;
            }

            .content {
                margin-left: 220px;
                padding: 20px;
            }
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

        <button type="submit">

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