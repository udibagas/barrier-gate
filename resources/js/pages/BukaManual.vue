<template>
    <div>
        <el-page-header @back="$emit('back')" content="LOG BUKA MANUAL"> </el-page-header>

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
                <el-input
                clearable
                size="small"
                v-model="keyword"
                placeholder="Cari"
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
                    {{scope.row.created_at | readableDateTime }}
                </template>
            </el-table-column>
            <el-table-column prop="gate" label="Gate" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="user" label="User" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="alasan" label="Alasan" sortable="custom"></el-table-column>
            <el-table-column fixed="right" width="70px" v-if="$store.state.user.role == 2" align="center" header-align="center">
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
                            <el-dropdown-item icon="el-icon-edit-outline" @click.native.prevent="openForm(scope.row)">Edit</el-dropdown-item>
                            <el-dropdown-item icon="el-icon-delete" @click.native.prevent="deleteData(scope.row.id)">Hapus</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
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
            order: 'descending',
            loading: false,
            dateRange: ''
        }
    },
    methods: {
        sortChange(c) {
            if (c.prop != this.sort || c.order != this.order) {
                this.sort = c.prop; this.order = c.order; this.requestData()
            }
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

            axios.get('bukaManual', { params }).then(r => {
                const data = r.data.data.map(d => {
                    return {
                        "Waktu": d.created_at,
                        "Gate": d.gate,
                        "User": d.user,
                        "Alasan": d.alasan,
                    }
                });

                exportFromJSON({ data, fileName: 'log-buka-manual', exportType: 'xls' })
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
                dateRange: this.dateRange
            }

            axios.get('/bukaManual', { params }).then(r => this.tableData = r.data).catch(e => {
                if (e.response.status == 500) {
                    this.$message({
                        message: e.response.data.message + '\n' + e.response.data.file + ':' + e.response.data.line,
                        type: 'error',
                        showClose: true
                    });
                }
            }).finally(() => this.loading = false)
        }
    },
    mounted() {
        this.requestData();
        this.$store.commit('getBarrierGateList');
    }
}
</script>

<style scoped>

</style>

