<template>
  <div class="thong-ke-bao-cao">
    <div class="header-actions">
      <h2>Thống kê báo cáo</h2>
      <div class="filters">
        <select v-model="period" @change="fetchData" class="period-select">
          <option value="today">Hôm nay</option>
          <option value="yesterday">Hôm qua</option>
          <option value="this_week">Tuần này</option>
          <option value="this_month">Tháng này</option>
        </select>
        <button class="btn-export" @click="exportReport">
          <i class="fas fa-download"></i> Xuất báo cáo
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards" v-if="overview">
      <div class="card summary-card revenue-card">
        <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="card-info">
          <h3>Doanh thu</h3>
          <p class="value">{{ formatCurrency(overview.revenue) }}</p>
          <span class="sub-text">Tổng doanh thu {{ periodLabel }}</span>
        </div>
      </div>
      <div class="card summary-card orders-card">
        <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="card-info">
          <h3>Đơn hàng</h3>
          <p class="value">{{ overview.orderCount }}</p>
          <span class="sub-text">Đơn hàng hoàn thành</span>
        </div>
      </div>
      <div class="card summary-card customers-card">
        <div class="card-icon"><i class="fas fa-users"></i></div>
        <div class="card-info">
          <h3>Khách hàng mới</h3>
          <p class="value">{{ overview.newCustomers }}</p>
          <span class="sub-text">Người dùng đăng ký</span>
        </div>
      </div>
      <div class="card summary-card avg-card">
        <div class="card-icon"><i class="fas fa-chart-line"></i></div>
        <div class="card-info">
          <h3>Giá trị TB đơn</h3>
          <p class="value">{{ formatCurrency(overview.averageOrderValue) }}</p>
          <span class="sub-text">Trung bình mỗi đơn</span>
        </div>
      </div>
    </div>

    <!-- Charts Row 1: Revenue Line Chart -->
    <div class="chart-section single-col">
      <div class="card chart-box wide-chart" v-if="revenueChartData">
        <h3><i class="fas fa-chart-area"></i> Biểu đồ doanh thu</h3>
        <div class="chart-wrapper">
          <Line :data="revenueChartData" :options="lineChartOptions" />
        </div>
      </div>
    </div>

    <!-- Charts Row 2: Pie Charts -->
    <div class="chart-section two-col">
      <div class="card chart-box" v-if="categoryChartData">
        <h3><i class="fas fa-chart-pie"></i> Sản phẩm theo danh mục</h3>
        <div class="chart-wrapper">
          <Pie :data="categoryChartData" :options="pieChartOptions" />
        </div>
      </div>
      <div class="card chart-box" v-if="statusChartData">
        <h3><i class="fas fa-clipboard-check"></i> Trạng thái đơn hàng</h3>
        <div class="chart-wrapper">
          <Doughnut :data="statusChartData" :options="pieChartOptions" />
        </div>
      </div>
    </div>

    <!-- Data Tables Row -->
    <div class="data-section two-col-bias">
       <!-- Top Selling Products -->
      <div class="card data-box">
        <h3><i class="fas fa-trophy"></i> Top Sản Phẩm Bán Chạy</h3>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>Đã bán</th>
                <th>Doanh thu</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in topProducts" :key="product.id">
                <td class="product-cell">
                  <div class="product-info">
                    <span class="product-name">{{ product.name }}</span>
                  </div>
                </td>
                <td class="text-center">{{ product.total_sold }}</td>
                <td class="text-right">{{ formatCurrency(product.total_revenue) }}</td>
              </tr>
              <tr v-if="topProducts.length === 0">
                <td colspan="3" class="text-center">Chưa có dữ liệu</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

       <!-- Recent Orders & Low Stock -->
      <div class="right-column-stack">
        <div class="card data-box">
           <h3><i class="fas fa-clock"></i> Đơn Hàng Gần Đây</h3>
           <div class="simple-list">
             <div class="list-item" v-for="order in recentOrders" :key="order.id">
               <div class="list-left">
                 <span class="order-id">#{{ order.id }}</span>
                 <span class="order-user">{{ order.user?.name || 'Khách lẻ' }}</span>
               </div>
               <div class="list-right">
                 <span class="order-price">{{ formatCurrency(order.total_price) }}</span>
                 <span class="status-badge" :class="order.status">{{ order.status }}</span>
               </div>
             </div>
             <div v-if="recentOrders.length === 0" class="text-center pad-10">Chưa có đơn hàng</div>
           </div>
        </div>

        <div class="card data-box warning-box" v-if="lowStockProducts.length > 0">
            <h3><i class="fas fa-exclamation-triangle"></i> Cảnh báo kho hàng ({{lowStockProducts.length}})</h3>
            <ul class="warning-list">
              <li v-for="prod in lowStockProducts" :key="prod.id">
                <span>{{ prod.name }}</span>
                <span class="stock-badge">Còn: {{ prod.stock_quantity }}</span>
              </li>
            </ul>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
} from "chart.js";
import { Line, Pie, Doughnut } from "vue-chartjs";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  Filler
);

const period = ref("this_month");
const overview = ref(null);
const revenueDataRaw = ref([]);
const categoryDataRaw = ref([]);
const statusDataRaw = ref([]);
const topProducts = ref([]);
const recentOrders = ref([]);
const lowStockProducts = ref([]);

const periodLabel = computed(() => {
    const map = {
        'today': 'Hôm nay',
        'yesterday': 'Hôm qua',
        'this_week': 'Tuần này',
        'this_month': 'Tháng này'
    };
    return map[period.value];
});

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
        mode: 'index',
        intersect: false,
        callbacks: {
            label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                    label += ': ';
                }
                if (context.parsed.y !== null) {
                    label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                }
                return label;
            }
        }
    }
  },
  scales: {
    y: {
        beginAtZero: true,
        ticks: {
            callback: function(value) {
                if (value >= 1000000) return (value / 1000000) + 'M';
                if (value >= 1000) return (value / 1000) + 'k';
                return value;
            }
        }
    }
  }
};

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'right' }
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(value);
};

const fetchData = async () => {
  try {
    const token = localStorage.getItem("token");
    const headers = {
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    };
    const baseUrl = '/api/admin/statistics';
    const params = `?period=${period.value}`;

    // Parallel Fetching for speed
    const [overviewRes, revenueRes, categoryRes, statusRes, topProdRes, activityRes] = await Promise.all([
        fetch(`${baseUrl}/overview${params}`, { headers }),
        fetch(`${baseUrl}/revenue-over-time${params}`, { headers }),
        fetch(`${baseUrl}/sales-by-category${params}`, { headers }),
        fetch(`${baseUrl}/order-status-distribution${params}`, { headers }),
        fetch(`${baseUrl}/top-selling-products${params}&limit=5`, { headers }),
        fetch(`${baseUrl}/recent-activities`, { headers }), // Activities usually aren't filtered by period in same way, or can be if needed. Let's assume global recent.
    ]);

    overview.value = await overviewRes.json();
    revenueDataRaw.value = await revenueRes.json();
    categoryDataRaw.value = await categoryRes.json();
    statusDataRaw.value = await statusRes.json();
    topProducts.value = await topProdRes.json();
    
    const activityData = await activityRes.json();
    recentOrders.value = activityData.recentOrders;
    lowStockProducts.value = activityData.lowStockProducts;

  } catch (error) {
    console.error("Error fetching statistics:", error);
  }
};

// --- Computed Chart Data ---

const revenueChartData = computed(() => {
  if (!revenueDataRaw.value || !revenueDataRaw.value.length) return null;
  return {
    labels: revenueDataRaw.value.map((item) => item.date), // Format date closer if needed
    datasets: [
      {
        label: "Doanh thu",
        backgroundColor: "rgba(66, 165, 245, 0.2)",
        borderColor: "#42A5F5",
        pointBackgroundColor: "#fff",
        pointBorderColor: "#42A5F5",
        pointHoverBackgroundColor: "#42A5F5",
        pointHoverBorderColor: "#fff",
        fill: true,
        tension: 0.4, // Smooth curve
        data: revenueDataRaw.value.map((item) => item.total),
      },
    ],
  };
});

const categoryChartData = computed(() => {
  if (!categoryDataRaw.value.length) return null;
  return {
    labels: categoryDataRaw.value.map((item) => item.name),
    datasets: [
      {
        backgroundColor: ["#41B883", "#E46651", "#00D8FF", "#DD1B16", "#F7C600", "#9B59B6"],
        data: categoryDataRaw.value.map((item) => item.total_quantity),
      },
    ],
  };
});

const statusChartData = computed(() => {
    if (!statusDataRaw.value.length) return null;
    const statusLabels = {
        'pending': 'Chờ xử lý',
        'processing': 'Đang xử lý',
        'shipped': 'Đang giao',
        'delivered': 'Đã giao',
        'cancelled': 'Đã hủy'
    };
    const statusColors = {
        'pending': '#f39c12',
        'processing': '#3498db',
        'shipped': '#9b59b6',
        'delivered': '#2ecc71',
        'cancelled': '#e74c3c'
    };
    
    return {
        labels: statusDataRaw.value.map(item => statusLabels[item.status] || item.status),
        datasets: [{
            backgroundColor: statusDataRaw.value.map(item => statusColors[item.status] || '#95a5a6'),
            data: statusDataRaw.value.map(item => item.count)
        }]
    };
});

const exportReport = async () => {
    alert("Tính năng xuất báo cáo đang được phát triển!");
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.thong-ke-bao-cao {
  padding: 20px;
  background-color: #f5f7fa; /* Light grey styling background */
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.header-actions h2 {
    font-size: 24px;
    color: #2c3e50;
    margin: 0;
}

.filters {
  display: flex;
  gap: 10px;
}

.period-select {
  padding: 8px 15px;
  border-radius: 6px;
  border: 1px solid #ddd;
  background-color: white;
  cursor: pointer;
  outline: none;
}

.btn-export {
    background-color: #2c3e50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-export:hover {
    background-color: #34495e;
}

/* CARDS */
.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0,0,0,0.02);
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
    display: flex;
    align-items: center;
    padding: 25px;
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 20px;
    background-color: #f0f3f6;
    color: #7f8c8d;
}

.revenue-card .card-icon { background-color: #e3f2fd; color: #42a5f5; }
.orders-card .card-icon { background-color: #e8f5e9; color: #66bb6a; }
.customers-card .card-icon { background-color: #f3e5f5; color: #ab47bc; }
.avg-card .card-icon { background-color: #fff3e0; color: #ffa726; }

.card-info h3 {
    margin: 0 0 5px;
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 500;
}
.card-info .value {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    color: #2c3e50;
}
.sub-text {
    font-size: 12px;
    color: #bdc3c7;
    margin-top: 5px;
    display: block;
}

/* CHARTS */
.chart-section {
    margin-bottom: 30px;
}
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.single-col {
    width: 100%;
}
.chart-box h3, .data-box h3 {
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-wrapper {
    position: relative;
    height: 300px;
    width: 100%;
}

.wide-chart .chart-wrapper {
    height: 350px;
}

/* DATA TABLES & LISTS */
.two-col-bias {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.table-responsive {
    overflow-x: auto;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th, .data-table td {
    padding: 12px 10px;
    text-align: left;
    border-bottom: 1px solid #f1f2f6;
}
.data-table th {
    font-weight: 600;
    color: #7f8c8d;
    font-size: 13px;
}
.data-table td {
    font-size: 14px;
    color: #2c3e50;
}
.text-center { text-align: center; }
.text-right { text-align: right; }

.product-info {
    display: flex;
    align-items: center;
}
.product-name {
    font-weight: 500;
}

/* Right Column Lists */
.right-column-stack {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.simple-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px dashed #eee;
}
.list-item:last-child {
    border-bottom: none;
}
.list-left {
    display: flex;
    flex-direction: column;
}
.order-id { font-weight: bold; font-size: 13px; color: #2c3e50; }
.order-user { font-size: 12px; color: #95a5a6; }

.list-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.order-price { font-weight: 600; font-size: 13px; color: #2ecc71; }
.status-badge {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
    margin-top: 4px;
    text-transform: uppercase;
}
.status-badge.pending { background: #fff3e0; color: #f39c12; }
.status-badge.processing { background: #e3f2fd; color: #3498db; }
.status-badge.delivered { background: #e8f5e9; color: #2ecc71; }
.status-badge.cancelled { background: #ffebee; color: #e74c3c; }

/* Warnings */
.warning-box {
    border-left: 4px solid #e74c3c;
}
.warning-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.warning-list li {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.stock-badge {
    color: #e74c3c;
    font-weight: bold;
    font-size: 13px;
}

@media (max-width: 900px) {
    .charts-container, .two-col, .two-col-bias {
        grid-template-columns: 1fr;
    }
}
</style>
