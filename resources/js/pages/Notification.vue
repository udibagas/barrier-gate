<template>
    <div>
        <el-page-header @back="$emit('back')" content="NOTIFIKASI"> </el-page-header>

        <el-form inline class="text-right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-button size="small" @click="clearNotification" type="danger" icon="el-icon-delete">HAPUS NOTIFIKASI</el-button>
            </el-form-item>
            <el-form-item>
                <el-date-picker
                size="small"
                @change="requestData"
                v-model="dateRange"
                format="dd/MMM/yyyy"
                value-format="yyyy-MM-dd"
                type="daterange"
                range-separator="-"
                start-placeholder="Dari tanggal"
                end-placeholder="Sampai tanggal">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-input
                clearable
                size="small"
                v-model="keyword"
                placeholder="Search"
                prefix-icon="el-icon-search"
                @change="(v) => { keyword = v; requestData(); }">
                </el-input>
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
        :default-sort = "{prop: sort, order: order}"
        height="calc(100vh - 190px)"
        v-loading="loading"
        @sort-change="sortChange">
            <el-table-column prop="created_at" label="Waktu" sortable="custom" width="150px">
                <template slot-scope="scope">
                    {{ scope.row.created_at | readableDateTime }}
                </template>
            </el-table-column>
            <el-table-column prop="type" label="Jenis"></el-table-column>
            <el-table-column prop="data.message" label="Pesan" min-width="150px"></el-table-column>
            <el-table-column width="70px" align="center" header-align="center">
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
                    <el-button size="small" type="text" class="text-danger" icon="el-icon-delete" @click="deleteData(scope.row.id)"></el-button>
                </template>
            </el-table-column>
        </el-table>
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
            sort: 'created_at',
            order: 'ascending',
            loading: false,
            dateRange: [moment().format('YYYY-MM-01'), moment().format('YYYY-MM-DD')],
        }
    },
    methods: {
        sortChange(c) {
            if (c.prop != this.sort || c.order != this.order) {
                this.sort = c.prop; this.order = c.order; this.requestData()
            }
        },
        deleteData(id) {
            this.$confirm('Anda yakin akan menghapus data ini?', 'Warning', { type: 'warning' }).then(() => {
                axios.delete('/notification/' + id).then(r => {
                    this.requestData();
                    this.$message({
                        message: r.data.message,
                        type: 'success',
                        showClose: true
                    });
                }).catch(e => {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    });
                })
            }).catch(() => console.log(e));
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

            axios.get('notification', { params }).then(r => {
                const data = r.data.data.map(d => {
                    return {
                        "Waktu": d.created_at,
                        "Jenis": d.type,
                        "Pesan": d.data.message,
                    }
                });

                exportFromJSON({ data, fileName: 'notifikasi', exportType: 'xls' })
            }).catch(e => console.log(e)).finally(() => this.loading = false)
        },
        requestData() {
            this.loading = true;
            const params = {
                page: this.page,
                keyword: this.keyword,
                pageSize: this.pageSize,
                sort: this.sort,
                order: this.order,
                dateRange: this.dateRange,
                read: 1
            }

            axios.get('notification', { params: params }).then(r => this.tableData = r.data).catch(e => {
                if (e.response.status == 500) {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    });
                }
            }).finally(() => this.loading = false)
        },
        clearNotification() {
            this.$confirm('Anda yakin akan menghapus semua notifikasi?', 'Warning', { type: 'warning' }).then(() => {
                axios.delete('/notification/clear').then(r => {
                    this.requestData();
                    this.$message({
                        message: r.data.message,
                        type: 'success',
                        showClose: true
                    });
                }).catch(e => {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    });
                })
            }).catch(() => console.log(e));
        }
    },
    mounted() {
        this.requestData();
    }
}
</script>
