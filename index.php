<?php
// --- 1. SETUP ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "employee-employeeproject.h.aivencloud.com";
$port = "10229";
$dbname = "defaultdb";
$user = "avnadmin";
$password = "AVNS_e4hZhF8kuX3_FGlivx8"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $conn = new PDO($dsn, $user, $password);
    
    $sql = "SELECT * FROM employees ORDER BY id ASC";
    $stmt = $conn->query($sql);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Payroll Table</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f2f5; padding: 20px; color: #333; }
        .container { width: 98%; margin: 0 auto; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        /* Header & Buttons */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .btn-group { display: flex; gap: 10px; }
        
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px; border: none; text-decoration: none; color: white; }
        .btn-excel { background-color: #107c41; }
        .btn-excel:hover { background-color: #0c5e31; }
        .btn-dash { background-color: #2563eb; }
        .btn-dash:hover { background-color: #1e40af; }

        /* Compact Table */
        table { width: 100%; border-collapse: collapse; }
        thead { background-color: #f8fafc; }
        th { padding: 10px 5px; text-align: center; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
        td { padding: 8px 5px; border-bottom: 1px solid #edf2f7; font-size: 12px; color: #4a5568; text-align: center; }
        tr:hover { background-color: #f1f5f9; }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .badge-red { background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 11px; }
        .col-money { font-family: 'Consolas', monospace; font-weight: 600; color: #2d3748; }
        .text-red { color: #e53e3e; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2 style="font-size:20px; font-weight:600; margin:0;"><i class="fas fa-users-cog"></i> Payroll Report <span style="font-size:0.7em; color:#718096;">(Jan 2026)</span></h2>
            
            <div class="btn-group">
                <a href="dashboard.php" class="btn btn-dash">
                    <i class="fas fa-th-large"></i> Dashboard View
                </a>
                <button class="btn btn-excel" onclick="exportTableToExcel('payrollTable')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        
        <table id="payrollTable">
            <thead>
                <tr>
                    <th class="text-left">Emp ID</th>
                    <th class="text-left">Name</th>
                    <th>Date</th>
                    <th>Bus. Hrs</th>
                    <th>B-Code</th>
                    <th>Missing</th>
                    <th>M-Code</th>
                    <th>Early</th>
                    <th>A-Code</th>
                    <th>Total In</th>
                    <th>Deduct</th>
                    <th>Net Work</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Base Salary</th>
                    <th class="text-right">Deduction</th>
                    <th class="text-right">Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <?php 
                    $base_salary = $emp['base_salary_sar'];
                    $missing_hours = $emp['missing_man_hour'];
                    $hourly_rate = ($base_salary / 30) / 8;
                    $deduction_amount = $hourly_rate * $missing_hours;
                    $final_salary = $base_salary - $deduction_amount;
                ?>
                <tr>
                    <td class="text-left" style="color:#2b6cb0; font-weight:600;"><?php echo $emp['employee_no']; ?></td>
                    <td class="text-left" style="font-weight:600; white-space:nowrap;"><?php echo $emp['name']; ?></td>
                    <td style="white-space:nowrap;"><?php echo $emp['date']; ?></td>
                    <td><?php echo $emp['business_man_hour']; ?></td>
                    <td><?php echo $emp['b_code']; ?></td>
                    <td><?php if($missing_hours > 0): ?><span class="badge-red"><?php echo $missing_hours; ?></span><?php else: ?>-<?php endif; ?></td>
                    <td><?php echo $emp['m_code']; ?></td>
                    <td><?php echo $emp['early_departure']; ?></td>
                    <td><?php echo $emp['a_code']; ?></td>
                    <td><?php echo $emp['total_time_in']; ?></td>
                    <td style="color:#e53e3e;"><?php echo $emp['total_deduction_time']; ?></td>
                    <td style="font-weight:bold;"><?php echo $emp['total_working_time']; ?></td>
                    <td class="col-money text-right" style="color:#718096;"><?php echo number_format($hourly_rate, 2); ?></td>
                    <td class="col-money text-right"><?php echo number_format($base_salary); ?></td>
                    <td class="col-money text-right text-red"><?php echo ($deduction_amount > 0 ? '-' : '') . number_format($deduction_amount, 2); ?></td>
                    <td class="col-money text-right"><?php echo number_format($final_salary, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function exportTableToExcel(tableID, filename = 'Payroll_Report_Jan2026') {
            var tableSelect = document.getElementById(tableID);
            var tableClone = tableSelect.cloneNode(true);
            var tableHTML = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body>' + tableClone.outerHTML + '</body></html>';
            var downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            var blob = new Blob(['\ufeff', tableHTML], { type: 'application/vnd.ms-excel' });
            if (navigator.msSaveOrOpenBlob) { navigator.msSaveOrOpenBlob(blob, filename + '.xls'); } 
            else { downloadLink.href = URL.createObjectURL(blob); downloadLink.download = filename + '.xls'; downloadLink.click(); }
        }
    </script>
</body>
</html>