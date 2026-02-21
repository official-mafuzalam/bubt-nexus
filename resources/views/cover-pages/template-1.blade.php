<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cover Page</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            min-height: 100vh;
        }

        .cover-page {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px 50px;
            position: relative;
        }

        .university-name {
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .commitment-text {
            text-align: left;
            font-size: 10px;
            color: #333;
            margin-bottom: 40px;
            font-style: italic;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .main-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin: 40px 0 30px;
            text-transform: uppercase;
        }

        .assignment-details {
            text-align: left;
            font-size: 12px;
            margin: 20px 0;
            line-height: 1.8;
        }

        .assignment-details p {
            margin: 5px 0;
        }

        .submitted-section {
            margin: 40px 0;
            display: flex;
            justify-content: space-between;
        }

        .submitted-by,
        .submitted-to {
            width: 45%;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        .info-item {
            margin: 8px 0;
            font-size: 12px;
            line-height: 1.6;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 70px;
        }

        .teacher-info {
            margin-top: 15px;
            font-size: 12px;
            line-height: 1.6;
        }

        .teacher-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .teacher-designation {
            font-style: italic;
            margin-bottom: 3px;
        }

        .teacher-dept {
            margin-top: 5px;
        }

        .university-footer {
            margin-top: 60px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="cover-page">
        <!-- University Name -->
        <div class="university-name">
            BANGLADESH UNIVERSITY OF BUSINESS & TECHNOLOGY
        </div>

        <!-- Commitment to Academic Excellence -->
        <div class="commitment-text">
            Commitment to Academic Excellence
        </div>

        <!-- Assignment Title -->
        <div class="main-title">
            ASSIGNMENT
        </div>

        <!-- Assignment Details -->
        <div class="assignment-details">
            <p>Assignment No: {{ $assignment_no ?? '1' }}Course Code : {{ $course_code ?? 'CSE 101' }}Course Title :
                {{ $course_title ?? 'Advance Program' }}</p>
        </div>

        <!-- Submitted By and Submitted To Section -->
        <div class="submitted-section">
            <!-- Submitted By -->
            <div class="submitted-by">
                <div class="section-title">Submitted By:</div>
                <div class="info-item">
                    <span class="info-label">Name :</span> {{ $name }}
                </div>
                <div class="info-item">
                    <span class="info-label">ID No :</span> {{ $id }}
                </div>
                <div class="info-item">
                    <span class="info-label">Intake :</span> {{ $intake ?? '45' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Section :</span> {{ $section ?? '1' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Program :</span> {{ $program ?? 'B.Sc. in CSE' }}
                </div>
            </div>

            <!-- Submitted To -->
            <div class="submitted-to">
                <div class="section-title">Submitted To:</div>
                <div class="teacher-info">
                    <div class="teacher-name">Name : {{ $teacher_name ?? 'Saifur Rahman' }}</div>
                    <div class="teacher-designation">({{ $teacher_designation ?? 'Assistant Professor' }})</div>
                    <div class="teacher-dept">Dept. of {{ $teacher_dept ?? 'CSE' }}</div>
                </div>
            </div>
        </div>

        <!-- University Footer -->
        <div class="university-footer">
            Bangladesh University of Business & Technology
        </div>
    </div>
</body>

</html>
