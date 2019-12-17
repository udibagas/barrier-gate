<template>
    <div>
        <el-page-header @back="$emit('back')" content="LOG AKSES"> </el-page-header>

        <el-form inline class="text-right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-date-picker
                size="small"
                @change="requestData"
                v-model="dateRange"
                format="dd/MMM/yyyy"
                value-format="yyyy-MM-dd"
                type="daterange"
                range-separator="-"
                start-placeholder="Dari Tanggal"
                end-placeholder="Sampai Tanggal">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-button size="small" type="primary" icon="el-icon-finished" @click="setSudahKeluarSemua">SET KENDARAAN SUDAH KELUAR SEMUA</el-button>
            </el-form-item>
            <el-form-item>
                <el-input size="small"
                v-model="keyword"
                placeholder="Cari"
                prefix-icon="el-icon-search"
                clearable
                @change="(v) => { keyword = v; requestData(); }">
                </el-input>
            </el-form-item>
            <el-form-item>
                <el-button-group>
                    <el-button
                    type="primary"
                    size="small"
                    title="Export Ke Excel"
                    @click="download"
                    icon="el-icon-download">
                    </el-button>

                    <el-button
                    type="primary"
                    size="small"
                    title="Export ke PDF"
                    @click="print('pdf')"
                    icon="el-icon-document">
                    </el-button>
                    <el-button
                    type="primary"
                    size="small"
                    title="Print"
                    @click="print('print')"
                    icon="el-icon-printer">
                    </el-button>
                </el-button-group>
            </el-form-item>
            <el-form-item style="margin-right:0;padding-right:0;">
                <el-pagination
                hide-on-single-page
                background
                style="margin-top:6px;padding:0"
                @current-change="(p) => { page = p; requestData(); }"
                @size-change="(s) => { pageSize = s; requestData(); }"
                layout="total, sizes, prev, next"
                :page-size="pageSize"
                :page-sizes="[10, 25, 50, 100]"
                :total="tableData.total">
                </el-pagination>
            </el-form-item>
        </el-form>

        <el-table :data="tableData.data" stripe
        @row-dblclick="(row, column, event) => { trx = row; showTrxDetail = true }"
        :default-sort = "{prop: sort, order: order}"
        height="calc(100vh - 190px)"
        v-loading="loading"
        @sort-change="sortChange">
            <el-table-column
            prop="time_out"
            label="Status"
            sortable="custom"
            width="130px"
            align="center"
            header-align="center">
                <template slot-scope="scope">
                    <el-tag effect="dark" size="small" style="width:100%;" :type="scope.row.time_out ? 'success' : 'danger'">
                        {{scope.row.time_out ? 'SUDAH KELUAR' : 'PARKIR'}}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="nomor_barcode" label="No. Tiket" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="plat_nomor" label="Plat Nomor" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="nomor_kartu" label="Nomor Kartu" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="user.name" label="Nama Staff" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="time_in" label="Waktu Masuk" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="time_out" label="Waktu Keluar" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="durasi" label="Durasi" show-overflow-tooltip min-width="100px"></el-table-column>
            <el-table-column prop="keterangan" label="Keterangan" show-overflow-tooltip min-width="100px"></el-table-column>
            <el-table-column prop="operator" label="Operator" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>

            <el-table-column fixed="right" width="70px" align="center" header-align="center">
                <template slot="header">
                    <el-button
                    title="Export Ke Excel"
                    class="text-white"
                    type="text"
                    @click="download"
                    icon="el-icon-download">
                    </el-button>

                    <el-button
                    title="Refresh"
                    class="text-white"
                    type="text" @click="() => { page = 1; keyword = ''; requestData(); }"
                    icon="el-icon-refresh">
                    </el-button>
                </template>
                <template slot-scope="scope">
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            <i class="el-icon-more"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item @click.native.prevent="() => { trx = scope.row; showTrxDetail = true }"><i class="el-icon-zoom-in"></i> Lihat Detail</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.time_out" @click.native.prevent="setSudahKeluar(scope.row.id)"><i class="el-icon-check"></i> Set Sudah Keluar</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog center top="60px" width="70%" v-if="trx" :visible.sync="showTrxDetail" :title="'DETAIL TRANSAKSI ' + trx.nomor_barcode">
            <el-row :gutter="20">
                <el-col :span="14">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><td class="td-label">Nomor Barcode</td><td class="td-value">{{trx.nomor_barcode}}</td></tr>
                            <tr><td class="td-label">Plat Nomor</td><td class="td-value">{{trx.plat_nomor}}</td></tr>
                            <tr v-if="trx.is_staff"><td class="td-label">Nama Staff</td><td class="td-value">{{trx.user.name}}</td></tr>
                            <tr v-if="trx.is_staff"><td class="td-label">Nomor Kartu</td><td class="td-value">{{trx.nomor_kartu}}</td></tr>
                            <tr><td class="td-label">Waktu Masuk</td><td class="td-value">{{trx.time_in}}</td></tr>
                            <tr><td class="td-label">Waktu Keluar</td><td class="td-value">{{trx.time_out}}</td></tr>
                            <tr><td class="td-label">Durasi</td><td class="td-value">{{trx.durasi}}</td></tr>
                            <tr><td class="td-label">Keterangan</td><td class="td-value">{{trx.keterangan}}</td></tr>
                            <tr><td class="td-label">Operator</td><td class="td-value">{{trx.operator}}</td></tr>
                        </tbody>
                    </table>
                </el-col>
                <el-col :span="10">
                    <div class="block">
                        <el-image :src="trx.snapshot_in" style="width: 100%; height: 100%" fit="cover">
                            <div slot="error" class="el-image__error">
                                <i class="el-icon-picture-outline"></i>
                            </div>
                        </el-image>
                    </div>
                    <div class="block">
                        <el-image :src="trx.snapshot_out" style="width: 100%; height: 100%" fit="cover">
                            <div slot="error" class="el-image__error">
                                <i class="el-icon-picture-outline"></i>
                            </div>
                        </el-image>
                    </div>
                </el-col>
            </el-row>
        </el-dialog>
    </div>
</template>

<script>
import exportFromJSON from 'export-from-json'

export default {
    data() {
        return {
            keyword: '',
            page: 1,
            pageSize: 10,
            tableData: {},
            sort: 'updated_at',
            order: 'descending',
            loading: false,
            trx: null,
            showTrxDetail: false,
            transaction: [],
            income: [],
            parkedVehicle: [],
            date: moment().format('YYYY-MM-DD'),
            dateRange: [moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')]
        }
    },
    methods: {
        sortChange(c) {
            if (c.prop != this.sort || c.order != this.order) {
                this.sort = c.prop; this.order = c.order; this.requestData()
            }
        },
        setSudahKeluar(id) {
            this.$confirm('Anda yakin?', 'Confirm', { type: 'warning' }).then(() => {
                axios.put('accessLog/setSudahKeluar/' + id).then(r => {
                    this.$message({
                        message: r.data.message,
                        type: 'success',
                        showClose: true
                    });
                    this.requestData()
                }).catch(e => {
                    this.$message({
                        message: r.response.data.message,
                        type: 'error',
                        showClose: true
                    });
                })
            }).catch(() => console.log(e))
        },
        setSudahKeluarSemua() {
            this.$confirm('Anda yakin?', 'Confirm', { type: 'warning' }).then(() => {
                axios.put('accessLog/setSudahKeluarSemua', { dateRange: this.dateRange }).then(r => {
                    this.$message({
                        message: r.data.message,
                        type: 'success',
                        showClose: true
                    });
                    this.requestData()
                }).catch(e => {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    });
                })
            }).catch(() => console.log(e))
        },
        download() {
            this.loading = true
            const params = {
                keyword: this.keyword,
                pageSize: 1000000,
                sort: this.sort,
                order: this.order,
                dateRange: this.dateRange
            }

            axios.get('accessLog', { params }).then(r => {
                const data = r.data.data.map(d => {
                    return {
                        "No. Tiket": d.nomor_barcode,
                    }
                });

                exportFromJSON({ data, fileName: 'akses-log', exportType: 'xls' })
            }).catch(e => console.log(e)).finally(() => this.loading = false)
        },
        print(action) {
            window.open('accessLogs?action='+action+'&sort='+this.sort+'&order='+this.order+'&pageSize='+1000000+'&token='+this.$store.state.token)
        },
        requestData() {
            this.loading = true;
            const params = {
                page: this.page,
                keyword: this.keyword,
                pageSize: this.pageSize,
                sort: this.sort,
                order: this.order,
                dateRange: this.dateRange
            }

            axios.get('accessLogs', { params }).then(r => this.tableData = r.data).catch(e => {
                if (e.response.status == 500) {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    });
                }
            }).finally(() => this.loading = false)
        }
    },
    mounted() {
        this.requestData();
    }
}
</script>

<style scoped>

.block {
    background-color: #eee;
    height: calc(50vh - 120px);
}

.td-label {
    width: 200px;
    font-weight: bold;
    padding: 5px 10px;
}

.td-value {
    padding: 5px 10px;
}

.col-value, .col-label {
    font-size: 16px;
    color: #fff;
}

.summary-container {
    height: 150px;
}

.summary-info {
    font-size: 30px;
}

.text-center {
    text-align: center;
}
</style>
