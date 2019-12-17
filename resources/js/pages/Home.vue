<template>
    <div>
        <el-row>
            <el-col :span="6">
                <el-page-header @back="$emit('back')" content="DASHBOARD"> </el-page-header>
            </el-col>
            <el-col :span="18">
                <el-form inline style="float:right;clear:right;">
                    <el-form-item label="PILIH PERIODE" style="margin-bottom:0">
                        <el-date-picker
                        @change="fetchAllData"
                        v-model="dateRange"
                        format="dd/MMM/yyyy"
                        value-format="yyyy-MM-dd"
                        type="daterange"
                        range-separator="-"
                        start-placeholder="Dari Tanggal"
                        end-placeholder="Sampai Tanggal">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item style="margin-bottom:0">
                        <el-button type="primary" icon="el-icon-refresh" @click="fetchAllData">REFRESH</el-button>
                    </el-form-item>
                    <el-form-item style="margin-bottom:0">
                        <el-button type="primary" icon="el-icon-download" @click="download">EXPORT KE EXCEL</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>

        <el-divider></el-divider>

        <el-row :gutter="10" style="margin-top:10px;margin-bottom:10px;">
            <el-col :span="6">
                <el-card class="text-center summary-container bg-teal" header="KENDARAAN TERPARKIR">
                    <el-row :gutter="10" v-for="(t, i) in terparkir" :key="i">
                        <el-col :span="12" class="text-right">{{ t.is_staff ? 'STAFF' : 'TAMU' }} :</el-col>
                        <el-col :span="12" class="text-left">{{ t.jumlah | formatNumber }}</el-col>
                    </el-row>
                    <el-row :gutter="10">
                        <el-col :span="12" class="text-right">TOTAL :</el-col>
                        <el-col :span="12" class="text-left">{{ totalTerparkir | formatNumber }}</el-col>
                    </el-row>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card class="text-center summary-container bg-orange" header="BUKA MANUAL">
                    <el-row :gutter="10" v-for="(t, i) in bukaManual" :key="i">
                        <el-col :span="12" class="text-right">{{ t.gate }} :</el-col>
                        <el-col :span="12" class="text-left">{{ t.jumlah | formatNumber }}</el-col>
                    </el-row>
                    <el-row :gutter="10">
                        <el-col :span="12" class="text-right">TOTAL : </el-col>
                        <el-col :span="12" class="text-left">{{ totalBukaManual | formatNumber }}</el-col>
                    </el-row>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card class="text-center summary-container bg-cyan" header="KARCIS HILANG">
                    <el-row :gutter="10" v-for="(t, i) in karcisHilang" :key="i">
                        <el-col :span="14" class="text-right">{{ t.status ? 'SUDAH DIAMBIL' : 'BELUM DIAMBIL' }} :</el-col>
                        <el-col :span="10" class="text-left">{{ t.jumlah | formatNumber }}</el-col>
                    </el-row>
                    <el-row :gutter="10">
                        <el-col :span="14" class="text-right">TOTAL :</el-col>
                        <el-col :span="10" class="text-left">{{ totalKarcisHilang | formatNumber }}</el-col>
                    </el-row>
                </el-card>
            </el-col>
            <el-col :span="6">
                <el-card class="text-center summary-container bg-pink" header="AKSES TANPA KARTU">
                    <div style="font-size:50px">{{tanpaKartu.jumlah}}</div>
                </el-card>
            </el-col>
        </el-row>

        <el-card>
            <v-chart :options="chartOptions" class="echarts"></v-chart>
            <el-table border :data="tableData" stripe show-summary :summary-method="getSummaries">
                <el-table-column prop="tanggal" label="Tanggal" align="center" header-align="center" min-width="100px">
                    <template slot-scope="scope">
                        {{ scope.row.tanggal | readableDate }}
                    </template>
                </el-table-column>
                <el-table-column label="Staff" align="center" header-align="center">
                    <template slot-scope="scope">
                        {{ parseInt(scope.row.staff) | formatNumber }}
                    </template>
                </el-table-column>
                <el-table-column label="Tamu" align="center" header-align="center">
                    <template slot-scope="scope">
                        {{ parseInt(scope.row.tamu) | formatNumber }}
                    </template>
                </el-table-column>
                <el-table-column label="Total" align="center" header-align="center">
                    <template slot-scope="scope">
                        {{ parseInt(scope.row.staff) + parseInt(scope.row.tamu) | formatNumber }}
                    </template>
                </el-table-column>
            </el-table>
        </el-card>
    </div>
</template>

<script>
import ECharts from 'vue-echarts/components/ECharts'
import 'echarts/lib/chart/bar'
import 'echarts/lib/chart/pie'
import 'echarts/lib/chart/line'
import 'echarts/lib/component/tooltip'
import 'echarts/lib/component/legend'
import 'echarts/lib/component/title'
import exportFromJSON from 'export-from-json'

export default {
    components: { 'v-chart': ECharts },
    computed: {
        totalTerparkir() {
            return this.terparkir.reduce((prev, curr) => prev + parseInt(curr.jumlah), 0)
        },
        totalBukaManual() {
            return this.bukaManual.reduce((prev, curr) => prev + parseInt(curr.jumlah), 0)
        },
        totalKarcisHilang() {
            return this.karcisHilang.reduce((prev, curr) => prev + parseInt(curr.jumlah), 0)
        },
    },
    data() {
        return {
            dateRange: [moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')],
            tableData: [],
            terparkir: [],
            bukaManual: [],
            karcisHilang: [],
            tanpaKartu: {},
            chartOptions: {
                title: {
                    text: 'STATISTIK KEHADIRAN',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' }
                },
                legend: {
                    y: 'bottom',
                    orient: 'horizontal'
                },
                grid: {
                    right: '250px',
                    left: '15px',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: [],
                },
                yAxis: { type: 'value', name: 'Jumlah' },
                series: []
            },
        }
    },
    methods: {
        readableDate(v) {
            return v ? moment(v).format('DD-MMM-YYYY') : ''
        },
        formatNumber(v) {
            try {
                v += '';
                var x = v.split('.');
                var x1 = x[0];
                var x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            } catch (error) {
                return 0
            }
        },
        getSummaries(param) {
            const { columns, data } = param;
            const sums = [];

            columns.forEach((column, index) => {
                if (index === 0) {
                    sums[index] = 'TOTAL';
                    return;
                }

                if (index === 1) {
                    let total = this.tableData.reduce((prev, curr) => prev + parseInt(curr.staff), 0)
                    sums[index] = this.formatNumber(total)
                }

                if (index === 2) {
                    let total = this.tableData.reduce((prev, curr) => prev + parseInt(curr.tamu), 0)
                    sums[index] = this.formatNumber(total)
                }

                if (index === 3) {
                    let total = this.tableData.reduce((prev, curr) => prev + parseInt(curr.staff) + parseInt(curr.tamu), 0)
                    sums[index] = this.formatNumber(total)
                }
            });

            return sums;
        },
        download() {
            const params = { dateRange: this.dateRange }
            axios.get('report', { params }).then(r => {
                const totalTamu = r.data.reduce((prev, curr) => prev + parseInt(curr.tamu), 0)
                const totalStaff = r.data.reduce((prev, curr) => prev + parseInt(curr.staff), 0)

                let data = r.data.map(d => {
                    return {
                        "Tanggal": d.tanggal,
                        "Staff": d.staff,
                        "Tamu": d.tamu,
                        "Total": parseInt(d.tamu) + parseInt(d.staff),
                    }
                })

                data.push({
                    "Tanggal": "TOTAL",
                    "Tamu": totalTamu,
                    "Staff": totalStaff,
                    "Total": totalTamu + totalStaff,
                })

                exportFromJSON({ data, fileName: 'report', exportType: 'xls' })
            }).catch(e => console.log(e))
        },
        requestData() {
            const params = { dateRange: this.dateRange }
            axios.get('report', { params }).then(r => {
                this.tableData = r.data
                this.chartOptions.xAxis.data = r.data.map(d => this.readableDate(d.tanggal))
                this.chartOptions.series = [{
                    name: 'STAFF',
                    type: r.data.length <= 10 ? 'bar' : 'line',
                    smooth: true,
                    barGap: 0,
                    // stack: true,
                    data: r.data.map(d => d.staff),
                    // color: '#005a74',
                    label: { show: r.data.length <= 10, position: 'inside', color: '#fff' }
                }, {
                    name: 'TAMU',
                    type: r.data.length <= 10 ? 'bar' : 'line',
                    smooth: true,
                    barGap: 0,
                    // stack: true,
                    data: r.data.map(d => d.tamu),
                    // color: '#00A2BB',
                    label: { show: r.data.length <= 10, position: 'inside', color: '#fff' }
                }, {
                    name: 'TOTAL',
                    type: r.data.length <= 10 ? 'bar' : 'line',
                    smooth: true,
                    barGap: 0,
                    data: r.data.map(d => parseInt(d.tamu) + parseInt(d.staff)),
                    // color: '#00A2BB',
                    label: { show: r.data.length <= 10, position: 'inside', color: '#fff' }
                }, {
                    name: 'PIE',
                    type: 'pie',
                    radius : '100px',
                    center: ['88%', '50%'],
                    data: [
                        {
                            name: 'STAFF',
                            value: r.data.reduce((prev, curr) => prev + parseInt(curr.staff), 0),
                            label: { position: 'inside', color: '#fff' }
                        },
                        {
                            name: 'TAMU',
                            value: r.data.reduce((prev, curr) => prev + parseInt(curr.tamu), 0),
                            label: { position: 'inside', color: '#fff' }
                        },
                    ],
                }];
            }).catch(e => console.log(e));
        },
        getTerparkir() {
            const params = { dateRange: this.dateRange }
            axios.get('report/terparkir', { params }).then(r => {
                this.terparkir = r.data
            }).catch(e => console.log(e))
        },
        getBukaManual() {
            const params = { dateRange: this.dateRange }
            axios.get('report/bukaManual', { params }).then(r => {
                this.bukaManual = r.data
            }).catch(e => console.log(e))
        },
        getKarcisHilang() {
            const params = { dateRange: this.dateRange }
            axios.get('report/karcisHilang', { params }).then(r => {
                this.karcisHilang = r.data
            }).catch(e => console.log(e))
        },
        getTanpaKartu() {
            const params = { dateRange: this.dateRange }
            axios.get('report/tanpaKartu', { params }).then(r => {
                this.tanpaKartu = r.data
            }).catch(e => console.log(e))
        },
        fetchAllData() {
            this.requestData()
            this.getTerparkir()
            this.getBukaManual()
            this.getKarcisHilang()
            this.getTanpaKartu()
        }
    },
    mounted() {
        this.$store.commit('getNavigationList')
        this.fetchAllData()
    }
}
</script>

<style lang="scss" scoped>
.echarts {
    width:100%;
    height:400px;
    margin:15px 0;
    padding-bottom:15px;
}

.summary-container {
    height: 150px;
}
</style>
