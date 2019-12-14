<template>
    <div>
        <el-page-header @back="$emit('back')" content="LOG KARCIS HILANG"> </el-page-header>

        <el-form inline class="text-right" @submit.native.prevent="() => { return }">
            <!-- <el-form-item>
                <el-button size="small" icon="el-icon-download" @click="download" type="primary">EXPORT KE EXCEL</el-button>
            </el-form-item> -->
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
                <el-input size="small" v-model="keyword" placeholder="Cari" prefix-icon="el-icon-search" :clearable="true" @change="(v) => { keyword = v; requestData(); }">
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
            <el-table-column
            prop="status"
            label="Status"
            sortable="custom"
            width="150px"
            align="center"
            header-align="center">
                <template slot-scope="scope">
                    <el-tag effect="dark" size="small" style="width:100%;" :type="scope.row.status ? 'success' : 'danger'">
                        {{scope.row.status ? 'SUDAH' : 'BELUM'}} DIAMBIL
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="Waktu" sortable="custom" width="150px" header-align="center" align="center">
                <template slot-scope="scope">
                    {{scope.row.created_at | readableDateTime }}
                </template>
            </el-table-column>
            <el-table-column prop="nama" label="Nama" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="jenis_kartu_identitas" label="Jenis Identitas" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="no_hp" label="No HP" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="no_plat" label="No Plat" sortable="custom" width="100px"></el-table-column>
            <el-table-column prop="alamat" label="Alamat" sortable="custom"></el-table-column>
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
                            <el-dropdown-item v-if="scope.row.status == 0" icon="el-icon-check" @click.native.prevent="sudahDiambil(scope.row.id)">Identitas Sudah Diambil</el-dropdown-item>
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

            axios.get('karcisHilang', { params }).then(r => {
                const data = r.data.data.map(d => {
                    return {
                        "Waktu": d.created_at,
                        "Nama": d.nama,
                        "Jenis Identitas": d.jenis_kartu_identitas,
                        "No. HP": d.no_hp,
                        "No. Plat": d.no_plat,
                        "Alamat": d.alamat,
                        "Status": d.status ? 'Identitas sudah diambil' : 'Identitas belum diambil',
                    }
                });

                exportFromJSON({ data, fileName: 'log-karcis-hilang', exportType: 'xls' })
            }).catch(e => console.log(e)).finally(() => this.loading = false)
        },
        requestData() {
            this.loading = true;
            let params = {
                page: this.page,
                keyword: this.keyword,
                pageSize: this.pageSize,
                sort: this.sort,
                order: this.order,
                dateRange: this.dateRange
            }

            axios.get('karcisHilang', { params }).then(r => this.tableData = r.data).catch(e => {
                if (e.response.status == 500) {
                    this.$message({
                        message: e.response.data.message + '\n' + e.response.data.file + ':' + e.response.data.line,
                        type: 'error',
                        showClose: true
                    });
                }
            }).finally(() => this.loading = false)
        },
        sudahDiambil(id) {
            this.$confirm('Anda yakin?', 'Perhatian', { type: 'warning' }).then(() => {
                axios.put('karcisHilang/sudahDiambil/' + id).then(r => {
                    this.$message({
                        message: 'Data berhasil disimpan',
                        type: 'success',
                        showClose: true
                    });
                    this.requestData();
                })
            }).catch(e => console.log(e))
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

