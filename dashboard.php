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
    
    // Fetch all employees
    $sql = "SELECT * FROM employees ORDER BY id ASC";
    $stmt = $conn->query($sql);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique departments for the sidebar
    $deptStmt = $conn->query("SELECT DISTINCT department FROM employees");
    $departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Connection Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
    
    <style>
        :root { --primary: #2563eb; --bg: #f3f4f6; --text: #1f2937; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: white; padding: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 20px; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        
        /* Navigation Buttons */
        .nav-btn { 
            display: block; width: 100%; padding: 12px 15px; border-radius: 8px; 
            text-decoration: none; margin-bottom: 5px; font-size: 14px; transition: 0.2s; 
            display: flex; align-items: center; gap: 10px; cursor: pointer; border: none; text-align: left;
        }
        
        /* Special Table View Button */
        .btn-table-view { background: #dbeafe; color: #1e40af; font-weight: 600; margin-bottom: 20px; }
        .btn-table-view:hover { background: #bfdbfe; }

        /* Filter Buttons */
        .filter-btn { background: transparent; color: #4b5563; font-weight: 500; }
        .filter-btn:hover, .filter-btn.active { background: #eff6ff; color: var(--primary); font-weight: 600; }

        /* Main Content */
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }

        /* Cards */
        .card { 
            background: white; border-radius: 12px; padding: 20px; text-align: center; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.3s; cursor: pointer; border: 1px solid white;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.05); border-color: #bfdbfe; }
        .avatar { width: 70px; height: 70px; background: #e0e7ff; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 15px; }
        .dept-tag { display: inline-block; background: #f3f4f6; padding: 4px 10px; border-radius: 20px; font-size: 11px; color: #4b5563; margin-top: 10px; }

        /* Modal */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .modal { background: white; width: 600px; max-width: 90%; border-radius: 16px; overflow: hidden; animation: slideUp 0.3s ease; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { background: var(--primary); padding: 20px; color: white; display: flex; align-items: center; gap: 15px; position: relative; }
        .modal-body { padding: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 8px; }
        .salary-box { grid-column: 1 / -1; background: #f0f9ff; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; border: 1px solid #b9e6fe; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><i class="fas fa-users-rectangle"></i> HR Dashboard</h2>
        
        <a href="index.php" class="nav-btn btn-table-view">
            <i class="fas fa-table"></i> Switch to Table View
        </a>

        <div style="border-top: 1px solid #e5e7eb; margin: 10px 0;"></div>

        <button class="nav-btn filter-btn active" onclick="filterEmployees('all', this)">
            <i class="fas fa-layer-group"></i> All Departments
        </button>
        <?php foreach ($departments as $dept): ?>
        <button class="nav-btn filter-btn" onclick="filterEmployees('<?php echo htmlspecialchars($dept); ?>', this)">
            <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($dept); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="main">
        <div class="grid" id="employeeGrid">
            <?php foreach ($employees as $emp): 
                // Flag Logic
                $flagCode = 'sa'; 
                if(strpos($emp['nationality'], 'Egypt') !== false) $flagCode = 'eg';
                if(strpos($emp['nationality'], 'Jordan') !== false) $flagCode = 'jo';
                if(strpos($emp['nationality'], 'India') !== false) $flagCode = 'in';
                if(strpos($emp['nationality'], 'Pakistan') !== false) $flagCode = 'pk';
                if(strpos($emp['nationality'], 'Philippines') !== false) $flagCode = 'ph';
                
                // Salary Logic
                $hourly_rate = ($emp['base_salary_sar'] / 30) / 8;
                $deduction = $hourly_rate * $emp['missing_man_hour'];
                $net_salary = $emp['base_salary_sar'] - $deduction;
            ?>
            <div class="card" data-dept="<?php echo htmlspecialchars($emp['department']); ?>" 
                 onclick='openModal(<?php echo json_encode($emp); ?>, "<?php echo $flagCode; ?>", <?php echo $net_salary; ?>)'>
                <div class="avatar"><i class="fas fa-user"></i></div>
                <h3 style="margin:0 0 5px;"><?php echo htmlspecialchars($emp['name']); ?></h3>
                <p style="margin:0; font-size:13px; color:#6b7280;"><?php echo htmlspecialchars($emp['grade']); ?></p>
                <span class="dept-tag"><?php echo htmlspecialchars($emp['department']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal">
            <div class="modal-header">
                <div class="avatar" style="width:60px; height:60px; background:white; color:var(--primary); margin:0; font-size:28px;"><i class="fas fa-user"></i></div>
                <div>
                    <h2 style="margin:0; font-size:20px;" id="mName">Name</h2>
                    <span style="font-size:13px; opacity:0.9;" id="mJob">Job Title</span>
                </div>
                <button onclick="document.getElementById('modalOverlay').style.display='none'" style="position:absolute; right:15px; top:15px; background:rgba(255,255,255,0.2); border:none; color:white; width:30px; height:30px; border-radius:50%; cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="info-item"><i class="fas fa-id-card" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">ID</span><br><strong id="mID"></strong></div></div>
                    <div class="info-item"><i class="fas fa-globe" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">Nationality</span><br><span id="mFlag" class="flag-icon"></span> <strong id="mNat"></strong></div></div>
                    <div class="info-item"><i class="fas fa-phone" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">Phone</span><br><strong id="mPhone"></strong></div></div>
                    <div class="info-item"><i class="fas fa-map-marker-alt" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">Location</span><br><strong id="mLoc"></strong></div></div>
                </div>
                <div>
                    <div class="info-item"><i class="fas fa-birthday-cake" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">Age</span><br><strong id="mAge"></strong></div></div>
                    <div class="info-item"><i class="fas fa-calendar-alt" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">Joined</span><br><strong id="mJoin"></strong></div></div>
                    <div class="info-item"><i class="fas fa-briefcase" style="color:#9ca3af; width:20px;"></i> <div><span style="font-size:12px; color:#6b7280;">Experience</span><br><strong id="mExp"></strong></div></div>
                </div>
                <div class="salary-box">
                    <div><span style="font-size:12px; color:#6b7280;">Base Salary</span><br><strong style="color:#2563eb;" id="mBase"></strong></div>
                    <div style="text-align:right;"><span style="font-size:12px; color:#6b7280;">Net Salary</span><br><strong style="color:#059669;" id="mNet"></strong></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterEmployees(dept, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.card').forEach(card => {
                card.style.display = (dept === 'all' || card.getAttribute('data-dept') === dept) ? 'block' : 'none';
            });
        }
        function openModal(data, flagCode, netSalary) {
            document.getElementById('mName').innerText = data.name;
            document.getElementById('mJob').innerText = data.grade + " - " + data.department;
            document.getElementById('mID').innerText = data.employee_no;
            document.getElementById('mNat').innerText = data.nationality;
            document.getElementById('mPhone').innerText = data.phone_number;
            document.getElementById('mLoc').innerText = data.location;
            document.getElementById('mAge').innerText = data.age + " Years";
            document.getElementById('mJoin').innerText = data.join_date;
            document.getElementById('mExp').innerText = data.years_experience + " Years";
            document.getElementById('mBase').innerText = parseInt(data.base_salary_sar).toLocaleString() + " SAR";
            document.getElementById('mNet').innerText = netSalary.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + " SAR";
            document.getElementById('mFlag').className = `flag-icon flag-icon-${flagCode}`;
            document.getElementById('modalOverlay').style.display = 'flex';
        }
        function closeModal(e) { if(e.target.id === 'modalOverlay') document.getElementById('modalOverlay').style.display = 'none'; }
    </script>
</body>
</html>