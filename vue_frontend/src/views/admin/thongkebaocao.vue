<template>
  <div class="thong-ke-bao-cao">
    <h2>Thống kê báo cáo</h2>

    <div class="filters">
      <select v-model="period" @change="fetchData">
        <option value="today">Hôm nay</option>
        <option value="yesterday">Hôm qua</option>
        <option value="this_week">Tuần này</option>
        <option value="this_month">Tháng này</option>
      </select>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards" v-if="overview">
      <div class="card">
        <h3>Doanh thu</h3>
        <p class="value">{{ formatCurrency(overview.revenue) }}</p>
      </div>
      <div class="card">
        <h3>Đơn hàng</h3>
        <p class="value">{{ overview.orderCount }}</p>
      </div>
      <div class="card">
        <h3>Khách hàng mới</h3>
        <p class="value">{{ overview.newCustomers }}</p>
      </div>
      <div class="card">
        <h3>Giá trị TB đơn</h3>
        <p class="value">{{ formatCurrency(overview.averageOrderValue) }}</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="charts-container">
      <div class="chart-box" v-if="revenueChartData">
        <h3>Biểu đồ doanh thu</h3>
        <Line :data="revenueChartData" :options="chartOptions" />
      </div>
      <div class="chart-box" v-if="categoryChartData">
        <h3>Sản phẩm theo danh mục</h3>
        <Pie :data="categoryChartData" :options="chartOptions" />
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
} from "chart.js";
import { Line, Pie } from "vue-chartjs";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const period = ref("this_month");
const overview = ref(null);
const revenueDataRaw = ref([]);
const categoryDataRaw = ref([]);

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(value);
};

const fetchData = async () => {
  try {
    const token = localStorage.getItem('token'); // Adjust based on your auth implementation
    const headers = {
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    };

    // Fetch Overview
    const overviewRes = await fetch(
      `/api/admin/statistics/overview?period=${period.value}`,
      { headers }
    );
    overview.value = await overviewRes.json();

    // Fetch Revenue Trend
    const revenueRes = await fetch(
      `/api/admin/statistics/revenue-over-time?period=${period.value}`,
      { headers }
    );
    revenueDataRaw.value = await revenueRes.json();

    // Fetch Sales by Category
    const categoryRes = await fetch(
      `/api/admin/statistics/sales-by-category?period=${period.value}`,
      { headers }
    );
    categoryDataRaw.value = await categoryRes.json();
  } catch (error) {
    console.error("Error fetching statistics:", error);
  }
};

const revenueChartData = computed(() => {
  if (!revenueDataRaw.value.length) return null;
  return {
    labels: revenueDataRaw.value.map((item) => item.date),
    datasets: [
      {
        label: "Doanh thu",
        backgroundColor: "#42A5F5",
        borderColor: "#42A5F5",
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
        backgroundColor: [
          "#41B883",
          "#E46651",
          "#00D8FF",
          "#DD1B16",
          "#F7C600",
        ],
        data: categoryDataRaw.value.map((item) => item.total_quantity),
      },
    ],
  };
});

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.thong-ke-bao-cao {
  padding: 20px;
}
.filters {
  margin-bottom: 20px;
}
.filters select {
  padding: 8px;
  font-size: 16px;
}
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}
.card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  text-align: center;
}
.card h3 {
  margin: 0 0 10px;
  color: #666;
  font-size: 14px;
}
.card .value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin: 0;
}
.charts-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.chart-box {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  height: 400px;
}
@media (max-width: 768px) {
  .charts-container {
    grid-template-columns: 1fr;
  }
}
</style>
