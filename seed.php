<?php
// --- 1. SETUP ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 Generating Data with GUARANTEED Deductions...</h1>";

$host = "employee-employeeproject.h.aivencloud.com";
$port = "10229";
$dbname = "defaultdb";
$user = "avnadmin";
$password = "AVNS_e4hZhF8kuX3_FGlivx8"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- 2. RESET TABLE ---
    $conn->exec("DROP TABLE IF EXISTS employees");
    
    $sqlCreate = "CREATE TABLE employees (
        id SERIAL PRIMARY KEY,
        employee_no VARCHAR(20),
        name VARCHAR(100),
        age INT,
        nationality VARCHAR(50),
        department VARCHAR(50),
        grade VARCHAR(20),
        join_date DATE,
        years_experience INT,
        phone_number VARCHAR(20),
        location VARCHAR(50),
        date DATE,
        business_man_hour DECIMAL(5,2),
        b_code VARCHAR(10),
        missing_man_hour DECIMAL(5,2),
        m_code VARCHAR(10),
        early_departure DECIMAL(5,2),
        a_code VARCHAR(10),
        total_time_in DECIMAL(5,2),
        total_deduction_time DECIMAL(5,2),
        total_working_time DECIMAL(5,2),
        base_salary_sar INT,
        housing_sar DECIMAL(10,2),
        transportation_sar DECIMAL(10,2)
    )";
    $conn->exec($sqlCreate);

    // --- 3. DATA LISTS ---
    $names = ['Ahmed Al-Farsi', 'Sara Khalid', 'Mohammed Zaid', 'Layla Omar', 'Omar Hawsawi', 'Noura Al-Saud', 'Fahad Al-Harbi', 'Reem Abdullah', 'Khalid Al-Otaibi', 'Maha Al-Shehri'];
    $depts = ['IT Department', 'HR & Admin', 'Finance', 'Engineering', 'Sales & Marketing'];
    $grades = ['Junior', 'Senior', 'Manager', 'Lead', 'Director'];
    $nations = ['Saudi Arabia', 'Egypt', 'Jordan', 'Philippines', 'India', 'Pakistan'];
    $locs = ['Riyadh - HQ', 'Jeddah Branch', 'Dammam Branch', 'Remote'];

    $stmt = $conn->prepare("INSERT INTO employees (
        employee_no, name, age, nationality, department, grade, join_date, years_experience, phone_number, location,
        date, business_man_hour, b_code, missing_man_hour, m_code, early_departure, a_code, total_time_in, total_deduction_time, total_working_time, base_salary_sar, housing_sar, transportation_sar
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    for ($i = 0; $i < 10; $i++) {
        $empNo = "EMP-" . (1001 + $i);
        $name = $names[$i];
        $age = rand(23, 55);
        $nat = $nations[array_rand($nations)];
        $dept = $depts[array_rand($depts)];
        $grade = $grades[array_rand($grades)];
        $loc = $locs[array_rand($locs)];
        $phone = "+966 5" . rand(0, 9) . " " . rand(100, 999) . " " . rand(1000, 9999);
        $joinYear = rand(2015, 2024);
        $join_date = "$joinYear-" . rand(1,12) . "-" . rand(1,28);
        $years_exp = 2026 - $joinYear;

        $current_date = "2026-01-" . sprintf("%02d", rand(1, 31));
        $base = rand(8, 30) * 500; 
        $housing = $base * 0.25;
        $transport = $base * 0.10;

        // --- UPDATED LOGIC: FORCE DEDUCTIONS ---
        // 1. Force the first 4 people ($i < 4) to ALWAYS have deductions
        // 2. OR 60% chance for everyone else
        if ($i < 4 || rand(1, 100) <= 60) {
            $missing = rand(1, 4) + (rand(0, 1) ? 0.5 : 0); // 1.5, 2.0, 3.5 hours
            $m_code = "M" . rand(1, 3);
        } else {
            $missing = 0;
            $m_code = "-";
        }

        $working_time = 8.00 - $missing;
        $deduction = $missing; 

        $stmt->execute([
            $empNo, $name, $age, $nat, $dept, $grade, $join_date, $years_exp, $phone, $loc,
            $current_date, 
            8.00, "B1", 
            $missing, $m_code, 
            0.00, "-", 
            8.00, $deduction, $working_time, 
            $base, $housing, $transport
        ]);
    }
    echo "<h2>✅ Success! Created 10 employees (Many are late!)</h2>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Error: " . $e->getMessage() . "</h2>";
}
?>