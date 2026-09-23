<?php
/**
 * Admin - Reports & Analytics
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'reports';
$page_title   = 'Reports & Analytics';

// Properties by type
$by_type=[];
$res=$conn->query("SELECT pt.type_name, COUNT(p.id) as count FROM properties p JOIN property_types pt ON p.property_type_id=pt.id GROUP BY pt.type_name ORDER BY count DESC");
if ($res) $by_type = $res->fetchAll(PDO::FETCH_ASSOC);

// Properties by city
$by_city=[];
$res=$conn->query("SELECT l.city, COUNT(p.id) as count FROM properties p JOIN locations l ON p.location_id=l.id GROUP BY l.city ORDER BY count DESC LIMIT 10");
if ($res) $by_city = $res->fetchAll(PDO::FETCH_ASSOC);

// Inquiries by month (12 months)
$monthly_inq=[];
for ($i=11;$i>=0;$i--) {
    $month=date('Y-m',strtotime("-$i months"));
    $label=date('M',strtotime("-$i months"));
    $st=$conn->prepare("SELECT COUNT(*) as c FROM inquiries WHERE DATE_FORMAT(created_at,'%Y-%m')=?");
    $st->execute([$month]);
    $monthly_inq[]=[$label,(int)$st->fetchColumn()];
}

// Users registered by month
$monthly_users=[];
for ($i=5;$i>=0;$i--) {
    $month=date('Y-m',strtotime("-$i months"));
    $label=date('M Y',strtotime("-$i months"));
    $st=$conn->prepare("SELECT COUNT(*) as c FROM users WHERE DATE_FORMAT(created_at,'%Y-%m')=?");
    $st->execute([$month]);
    $monthly_users[]=[$label,(int)$st->fetchColumn()];
}

// Inquiry status breakdown
$inq_status=['pending'=>0,'responded'=>0,'closed'=>0];
$sr=$conn->query("SELECT status,COUNT(*) as c FROM inquiries GROUP BY status");
if ($sr) {
    while ($r=$sr->fetch(PDO::FETCH_ASSOC)) {
        $inq_status[$r['status']]=(int)$r['c'];
    }
}

// Total property value
$total_val_res = $conn->query("SELECT SUM(price) as total FROM properties WHERE status='available'");
$total_val = $total_val_res ? (float)$total_val_res->fetchColumn() : 0;

// Top performing agents
$top_agents=[];
$res=$conn->query("SELECT a.name, a.properties_sold, a.rating, COUNT(p.id) as listed FROM agents a LEFT JOIN properties p ON p.agent_id=a.id GROUP BY a.id ORDER BY a.properties_sold DESC LIMIT 5");
if ($res) $top_agents = $res->fetchAll(PDO::FETCH_ASSOC);

$total_props = (int)$conn->query("SELECT COUNT(*) as c FROM properties")->fetchColumn();
$total_inqs = (int)$conn->query("SELECT COUNT(*) as c FROM inquiries")->fetchColumn();
$total_users = (int)$conn->query("SELECT COUNT(*) as c FROM users")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><span>Reports</span>
</div>

<!-- Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="stat-icon-box teal"><i class="bi bi-building"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_props ?></div>
                <div class="stat-label">Total Properties</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="stat-icon-box green"><i class="bi bi-currency-rupee"></i></div>
            <div class="stat-info">
                <div class="stat-number" style="font-size:1.3rem;"><?= formatPrice($total_val) ?></div>
                <div class="stat-label">Total Portfolio Value</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="stat-icon-box orange"><i class="bi bi-envelope"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_inqs ?></div>
                <div class="stat-label">Total Inquiries</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="stat-icon-box teal"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_users ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Monthly Inquiries -->
    <div class="col-xl-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-bar-chart-line" style="color:#729CA2;"></i> Monthly Inquiries (12 Months)</h5>
            </div>
            <div class="admin-card-body">
                <div style="height:260px;"><canvas id="monthlyInqChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Inquiry Status -->
    <div class="col-xl-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-pie-chart" style="color:#729CA2;"></i> Inquiry Status</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="inqStatusChart" style="max-height:200px;"></canvas>
                <div class="mt-3">
                    <?php foreach ($inq_status as $st=>$cnt): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:0.85rem;color:#465461;"><?= ucfirst($st) ?></span>
                            <strong style="color:#2D343D;"><?= $cnt ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties by Type -->
    <div class="col-xl-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-tag" style="color:#729CA2;"></i> Properties by Type</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="typeChart" style="max-height:220px;"></canvas>
                <div class="mt-3">
                    <?php foreach ($by_type as $t): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:0.85rem;color:#465461;"><?= htmlspecialchars($t['type_name']) ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:80px;height:6px;background:#C4DCDF;border-radius:3px;overflow:hidden;">
                                    <div style="width:<?= ($by_type[0]['count']>0?($t['count']/$by_type[0]['count']*100):0) ?>%;height:100%;background:#FF893B;border-radius:3px;"></div>
                                </div>
                                <strong style="font-size:0.85rem;min-width:20px;color:#2D343D;"><?= $t['count'] ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties by City -->
    <div class="col-xl-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-geo-alt" style="color:#729CA2;"></i> Properties by City</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="cityChart" style="max-height:220px;"></canvas>
                <div class="mt-3">
                    <?php foreach (array_slice($by_city,0,6) as $c): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:0.85rem;color:#465461;"><?= htmlspecialchars($c['city']) ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:80px;height:6px;background:#C4DCDF;border-radius:3px;overflow:hidden;">
                                    <div style="width:<?= ($by_city[0]['count']>0?($c['count']/$by_city[0]['count']*100):0) ?>%;height:100%;background:#729CA2;border-radius:3px;"></div>
                                </div>
                                <strong style="font-size:0.85rem;min-width:20px;color:#2D343D;"><?= $c['count'] ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Agents -->
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-trophy"></i> Top Performing Agents</h5>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Agent Name</th><th>Properties Sold</th><th>Currently Listed</th><th>Rating</th><th>Performance</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_agents as $i=>$ag): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($ag['name']) ?></td>
                                <td><span class="status-badge available"><?= $ag['properties_sold'] ?></span></td>
                                <td><span class="status-badge pending"><?= $ag['listed'] ?></span></td>
                                <td style="color:#FF893B;"><i class="bi bi-star-fill"></i> <?= number_format($ag['rating'],1) ?>/5</td>
                                <td style="width:150px;">
                                    <div style="height:8px;background:#C4DCDF;border-radius:4px;overflow:hidden;">
                                        <div style="width:<?= min(100,($ag['properties_sold']/max(1,$top_agents[0]['properties_sold']))*100) ?>%;height:100%;background:#FF893B;border-radius:4px;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Helpers
function drawBarChart(id, labels, data, color1, color2) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = canvas.parentElement.offsetWidth;
    canvas.height = 260;
    const maxVal = Math.max(...data, 1);
    const barW = (canvas.width - 80) / labels.length - 8;
    const barMaxH = 190;
    const grad = ctx.createLinearGradient(0, 0, 0, barMaxH);
    grad.addColorStop(0, color1); grad.addColorStop(1, color2);
    labels.forEach((lbl, i) => {
        const x = 40 + i * (barW + 8);
        const h = (data[i] / maxVal) * barMaxH;
        const y = 210 - h;
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.roundRect ? ctx.roundRect(x, y, barW, h, 4) : ctx.rect(x, y, barW, h);
        ctx.fill();
        ctx.fillStyle = '#111827'; ctx.font = 'bold 10px Inter'; ctx.textAlign = 'center';
        if (data[i] > 0) ctx.fillText(data[i], x + barW/2, y - 4);
        ctx.fillStyle = '#6B7280'; ctx.font = '9px Inter';
        ctx.fillText(lbl, x + barW/2, 228);
    });
    window.addEventListener('resize', () => drawBarChart(id, labels, data, color1, color2));
}

function drawDonut(id, data, colors) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const cx = canvas.offsetWidth/2||150, cy=100, r=70, inner=40;
    canvas.width = cx*2; canvas.height = 200;
    const total = data.reduce((a,b)=>a+b,0)||1;
    let start = -Math.PI/2;
    data.forEach((val,i) => {
        const slice = (val/total)*Math.PI*2;
        ctx.beginPath(); ctx.moveTo(cx,cy);
        ctx.arc(cx,cy,r,start,start+slice);
        ctx.closePath(); ctx.fillStyle=colors[i]; ctx.fill();
        ctx.strokeStyle='#fff'; ctx.lineWidth=3; ctx.stroke();
        start+=slice;
    });
    ctx.beginPath(); ctx.arc(cx,cy,inner,0,Math.PI*2);
    ctx.fillStyle='#fff'; ctx.fill();
}

// Draw charts
<?php
$mInqLabels = array_column($monthly_inq,0);
$mInqData   = array_column($monthly_inq,1);
?>
drawBarChart('monthlyInqChart', <?= json_encode($mInqLabels) ?>, <?= json_encode($mInqData) ?>, '#729CA2', '#465461');
drawDonut('inqStatusChart',
    [<?= $inq_status['pending'] ?>, <?= $inq_status['responded'] ?>, <?= $inq_status['closed'] ?>],
    ['#FF893B','#729CA2','#C4DCDF']
);
drawDonut('typeChart',
    [<?= implode(',', array_column($by_type,'count')) ?>],
    ['#2D343D','#465461','#729CA2','#C4DCDF','#FF893B','#E9782C']
);
drawBarChart('cityChart',
    <?= json_encode(array_column($by_city,'city')) ?>,
    <?= json_encode(array_column($by_city,'count')) ?>,
    '#729CA2','#2D343D'
);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
