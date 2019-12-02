<template>
    <div>
        <el-page-header @back="$emit('back')" content="LOG KARCIS HILANG"> </el-page-header>
        <el-divider></el-divider>

        <el-form :inline="true" style="text-align:right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-button icon="el-icon-plus" @click="openForm({})" type="primary">KARCIS HILANG</el-button>
            </el-form-item>
            <el-form-item>
                <el-date-picker
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
            <el-form-item style="margin-right:0;">
                <el-input v-model="keyword" placeholder="Cari" prefix-icon="el-icon-search" :clearable="true" @change="(v) => { keyword = v; requestData(); }">
                    <el-button @click="() => { page = 1; keyword = ''; requestData(); }" slot="append" icon="el-icon-refresh"></el-button>
                </el-input>
            </el-form-item>
        </el-form>

        <el-table :data="tableData.data" stripe
        :default-sort = "{prop: sort, order: order}"
        height="calc(100vh - 290px)"
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
                    <el-tag effect="dark" size="small" style="width:100%;border-radius:13px" :type="scope.row.status ? 'success' : 'danger'">
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
            <el-table-column fixed="right" width="40px">
                <template slot-scope="scope">
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            <i class="el-icon-more"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <!-- <el-dropdown-item icon="el-icon-printer" @click.native.prevent="printTicket(scope.row.id)">Print Struk</el-dropdown-item> -->
                            <el-dropdown-item v-if="scope.row.status == 0" icon="el-icon-check" @click.native.prevent="sudahDiambil(scope.row.id)">Identitas Sudah Diambil</el-dropdown-item>
                            <el-dropdown-item icon="el-icon-edit-outline" @click.native.prevent="openForm(scope.row)">Edit</el-dropdown-item>
                            <el-dropdown-item icon="el-icon-delete" @click.native.prevent="deleteData(scope.row.id)">Hapus</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </template>
            </el-table-column>
        </el-table>

        <br>

        <el-pagination background
        @current-change="(p) => { page = p; requestData(); }"
        @size-change="(s) => { pageSize = s; requestData(); }"
        layout="prev, pager, next, sizes, total"
        :page-size="pageSize"
        :page-sizes="[10, 25, 50, 100]"
        :total="tableData.total">
        </el-pagination>
    </div>
</template>

<script>
export default {
    data() {
        return {
            showForm: false,
            keyword: '',
            page: 1,
            pageSize: 10,
            tableData: {},
            sort: 'created_at',
            order: 'descending',
            loading: false,
            dateRange: [moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')]
        }
    },
    methods: {
        sortChange(c) {
            if (c.prop != this.sort || c.order != this.order) {
                this.sort = c.prop; this.order = c.order; this.requestData()
            }
        },
        openForm(data) {
            this.error = {}
            this.formErrors = {}
            this.formModel = JSON.parse(JSON.stringify(data));
            this.showForm = true
        },
        requestData() {
            let params = {
                page: this.page,
                keyword: this.keyword,
                pageSize: this.pageSize,
                sort: this.sort,
                order: this.order,
                dateRange: this.dateRange
            }

            this.loading = true;
            axios.get('/karcisHilang', {params: params}).then(r => {
                this.loading = false;
                this.tableData = r.data
            }).catch(e => {
                this.loading = false;
                if (e.response.status == 500) {
                    this.$message({
                        message: e.response.data.message + '\n' + e.response.data.file + ':' + e.response.data.line,
                        type: 'error',
                        showClose: true
                    });
                }
            })
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

