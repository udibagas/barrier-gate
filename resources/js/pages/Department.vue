<template>
    <div>
        <el-page-header @back="$emit('back')" content="DEPARTEMEN"> </el-page-header>

        <el-form inline class="text-right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-button size="small" @click="openForm({})" type="primary" icon="el-icon-plus">TAMBAH DEPARTEMEN</el-button>
            </el-form-item>
            <el-form-item>
                <el-input
                size="small"
                v-model="keyword"
                placeholder="Cari"
                prefix-icon="el-icon-search"
                clearable
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
        height="calc(100vh - 210px)"
        v-loading="loading"
        @sort-change="sortChange">
            <el-table-column type="index" :index="tableData.from"></el-table-column>
            <el-table-column prop="kode" label="Kode" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="nama" label="Nama" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column prop="keterangan" label="Keterangan" sortable="custom" show-overflow-tooltip min-width="150px"></el-table-column>
            <el-table-column fixed="right" width="40px" align="center" header-align="center">
                <template slot="header">
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

        <el-dialog :visible.sync="showForm" :title="!!formModel.id ? 'EDIT DEPARTEMEN' : 'TAMBAH DEPARTEMEN'" width="500px" v-loading="loading" :close-on-click-modal="false">
            <el-alert type="error" title="ERROR"
                :description="error.message + '\n' + error.file + ':' + error.line"
                v-show="error.message"
                style="margin-bottom:15px;">
            </el-alert>

            <el-form label-width="150px" label-position="left">
                <el-form-item label="Kode" :class="formErrors.kode ? 'is-error' : ''">
                    <el-input placeholder="Kode" v-model="formModel.kode"></el-input>
                    <div class="el-form-item__error" v-if="formErrors.kode">{{formErrors.kode[0]}}</div>
                </el-form-item>

                <el-form-item label="Nama" :class="formErrors.nama ? 'is-error' : ''">
                    <el-input placeholder="Nama" v-model="formModel.nama"></el-input>
                    <div class="el-form-item__error" v-if="formErrors.nama">{{formErrors.nama[0]}}</div>
                </el-form-item>

                <el-form-item label="Keterangan">
                    <el-input placeholder="Keterangan" v-model="formModel.keterangan"></el-input>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" icon="el-icon-success" @click="() => !!formModel.id ? update() : store()">SIMPAN</el-button>
                <el-button type="info" icon="el-icon-error" @click="showForm = false">BATAL</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            showForm: false,
            formErrors: {},
            error: {},
            formModel: {},
            keyword: '',
            page: 1,
            pageSize: 10,
            tableData: {},
            sort: 'nama',
            order: 'ascending',
            loading: false,
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
        store() {
            this.loading = true;
            axios.post('/department', this.formModel).then(r => {
                this.showForm = false;
                this.$message({
                    message: 'Data berhasil disimpan.',
                    type: 'success',
                    showClose: true
                });
                this.requestData();
            }).catch(e => {
                if (e.response.status == 422) {
                    this.error = {}
                    this.formErrors = e.response.data.errors;
                }

                if (e.response.status == 500) {
                    this.formErrors = {}
                    this.error = e.response.data;
                }
            }).finally(() => {
                this.loading = false
            })
        },
        update() {
            this.loading = true;
            axios.put('/department/' + this.formModel.id, this.formModel).then(r => {
                this.showForm = false
                this.$message({
                    message: 'Data berhasil disimpan.',
                    type: 'success',
                    showClose: true
                });
                this.requestData()
            }).catch(e => {
                if (e.response.status == 422) {
                    this.error = {}
                    this.formErrors = e.response.data.errors;
                }

                if (e.response.status == 500) {
                    this.formErrors = {}
                    this.error = e.response.data;
                }
            }).finally(() => {
                this.loading = false
            })
        },
        deleteData(id) {
            this.$confirm('Anda yakin akan menghapus data ini?', 'Warning', { type: 'warning' }).then(() => {
                axios.delete('/department/' + id).then(r => {
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
        requestData() {
            let params = {
                page: this.page,
                keyword: this.keyword,
                pageSize: this.pageSize,
                sort: this.sort,
                order: this.order,
            }

            this.loading = true;
            axios.get('/department', {params: params})
                .then(r => this.tableData = r.data)
                .catch(e => {
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
    }
}
</script>

<style scoped>

</style>

