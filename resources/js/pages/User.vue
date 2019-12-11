<template>
    <div>
        <el-page-header @back="$emit('back')" content="USER"> </el-page-header>
        <el-divider></el-divider>
        <el-form :inline="true" style="text-align:right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-button @click="openForm({role: 0, password: ''})" type="primary" icon="el-icon-plus">TAMBAH USER</el-button>
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
            <el-table-column prop="status" label="Status" sortable="custom" align="center" header-align="center" width="100px">
                <template slot-scope="scope">
                    <el-tag size="small" effect="dark" style="border-radius:13px;width:100%" :type="scope.row.status ? 'success' : 'error'">{{scope.row.status ? 'AKTIF' : 'NONAKTIF'}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="Nama" sortable="custom" width="120px" show-overflow-tooltip></el-table-column>
            <el-table-column prop="nip" label="NIP" sortable="custom" width="100px"></el-table-column>
            <el-table-column prop="role" label="Level" sortable="custom" align="center" header-align="center" width="120px">
                <template slot-scope="scope">
                    {{roles[scope.row.role]}}
                </template>
            </el-table-column>
            <el-table-column prop="department.nama" label="Department" width="100px" show-overflow-tooltip=""></el-table-column>
            <el-table-column prop="nomor_kartu" label="Nomor Kartu" sortable="custom" width="120px"></el-table-column>
            <el-table-column label="Masa Aktif Kartu" sortable="custom" width="150px" align="center" header-align="center">
                <template slot-scope="scope">
                    {{scope.row.masa_aktif_kartu | readableDate}}
                    <!-- <el-tag size="small" class="rounded full-width" type="danger" effect="dark" v-if="scope.row.expired">EXPIRED</el-tag> -->
                </template>
            </el-table-column>
            <el-table-column prop="plat_nomor" label="Plat Nomor" sortable="custom"  width="120px"></el-table-column>
            <el-table-column prop="email" label="Alamat Email" sortable="custom" width="130px" show-overflow-tooltip></el-table-column>
            <el-table-column prop="phone" label="Nomor HP" sortable="custom" width="120px"></el-table-column>

            <el-table-column width="40px" fixed="right">
                <template slot-scope="scope">
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            <i class="el-icon-more"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item icon="el-icon-zoom-in" @click.native.prevent="showDetail = true; user = scope.row;">Lihat Detail</el-dropdown-item>
                            <el-dropdown-item icon="el-icon-edit-outline" divided @click.native.prevent="openForm(scope.row)">Edit</el-dropdown-item>
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

        <el-dialog fullscreen :visible.sync="showForm" :title="!!formModel.id ? 'EDIT USER' : 'TAMBAH USER'" v-loading="loading" :close-on-click-modal="false">
            <el-alert type="error" title="ERROR"
                :description="error.message + '\n' + error.file + ':' + error.line"
                v-show="error.message"
                style="margin-bottom:15px;">
            </el-alert>

            <el-form label-width="160px" label-position="left">
                <el-row :gutter="20">
                    <el-col :span="8">
                        <el-form-item label="Nama" :class="formErrors.name ? 'is-error' : ''">
                            <el-input placeholder="Nama" v-model="formModel.name"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.name">{{formErrors.name[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Jenis Kelamin" :class="formErrors.jenis_kelamin ? 'is-error' : ''">
                            <el-select v-model="formModel.jenis_kelamin" placeholder="Jenis Kelamin" style="width:100%">
                                <el-option v-for="(t, i) in [{value: 'L', label: 'LAKI - LAKI'}, {value: 'P', label: 'PEREMPUAN'}]"
                                :value="t.value"
                                :label="t.label"
                                :key="i">
                                </el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.type">{{formErrors.jenis_kelamin[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Tempat Lahir" :class="formErrors.tempat_lahir ? 'is-error' : ''">
                            <el-input placeholder="Tempat Lahir" v-model="formModel.tempat_lahir"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.tempat_lahir">{{formErrors.tempat_lahir[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Tanggal Lahir" :class="formErrors.tanggal_lahir ? 'is-error' : ''">
                            <el-date-picker
                            style="width:100%"
                            format="dd-MMM-yyyy" value-format="yyyy-MM-dd"
                            placeholder="Tanggal Lahir" v-model="formModel.tanggal_lahir">
                            </el-date-picker>
                            <div class="el-form-item__error" v-if="formErrors.tanggal_lahir">{{formErrors.tanggal_lahir[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Alamat">
                            <el-input placeholder="Alamat" type="textarea" rows="4" v-model="formModel.alamat"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Alamat Email" :class="formErrors.email ? 'is-error' : ''">
                            <el-input placeholder="Alamat Email" v-model="formModel.email"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.email">{{formErrors.email[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Nomor HP" :class="formErrors.phone ? 'is-error' : ''">
                            <el-input placeholder="Nomor HP" v-model="formModel.phone"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.phone">{{formErrors.phone[0]}}</div>
                        </el-form-item>

                        <el-form-item label="NIP" :class="formErrors.nip ? 'is-error' : ''">
                            <el-input placeholder="NIP" v-model="formModel.nip"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.nip">{{formErrors.nip[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Departemen" :class="formErrors.department_id ? 'is-error' : ''">
                            <el-select clearable filterable v-model="formModel.department_id" placeholder="Departemen" style="width:100%">
                                <el-option v-for="(d, i) in $store.state.departmentList" :value="d.id" :label="d.nama" :key="i"> </el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.department_id">{{formErrors.department_id[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Level" :class="formErrors.role ? 'is-error' : ''">
                            <el-select v-model="formModel.role" placeholder="Level" style="width:100%">
                                <el-option v-for="(t, i) in [{value: 0, label: 'STAFF'}, {value: 1, label: 'OPERATOR'}, {value: 2, label: 'ADMIN'}]"
                                :value="t.value"
                                :label="t.label"
                                :key="i">
                                </el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.type">{{formErrors.role[0]}}</div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="Nomor Kartu" :class="formErrors.nomor_kartu ? 'is-error' : ''">
                            <el-input placeholder="Nomor Kartu" v-model="formModel.nomor_kartu"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.nomor_kartu">{{formErrors.nomor_kartu[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Masa Aktif Kartu" :class="formErrors.masa_aktif_kartu ? 'is-error' : ''">
                            <el-date-picker
                            style="width:100%"
                            format="dd-MMM-yyyy" value-format="yyyy-MM-dd"
                            placeholder="Masa Aktif Kartu" v-model="formModel.masa_aktif_kartu">
                            </el-date-picker>
                            <div class="el-form-item__error" v-if="formErrors.masa_aktif_kartu">{{formErrors.masa_aktif_kartu[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Plat Nomor" :class="formErrors.plat_nomor ? 'is-error' : ''">
                            <el-input placeholder="Plat Nomor" v-model="formModel.plat_nomor"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.plat_nomor">{{formErrors.plat_nomor[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Password" :class="formErrors.password ? 'is-error' : ''">
                            <el-input type="password" placeholder="Password" v-model="formModel.password"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.password">{{formErrors.password[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Konfirmasi Password" :class="formErrors.password ? 'is-error' : ''">
                            <el-input type="password" placeholder="Konfirmasi Password" v-model="formModel.password_confirmation"></el-input>
                        </el-form-item>

                        <el-form-item label="Status" :class="formErrors.status ? 'is-error' : ''">
                            <el-switch
                            :active-value="1"
                            :inactive-value="0"
                            v-model="formModel.status"
                            active-color="#13ce66">
                            </el-switch>
                            <el-tag :type="formModel.status ? 'success' : 'info'" size="small" style="margin-left:10px">{{!!formModel.status ? 'Aktif' : 'Nonaktif'}}</el-tag>
                            <div class="el-form-item__error" v-if="formErrors.status">{{formErrors.status[0]}}</div>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" icon="el-icon-success" @click="() => !!formModel.id ? update() : store()">SIMPAN</el-button>
                <el-button type="info" icon="el-icon-error" @click="showForm = false">BATAL</el-button>
            </span>
        </el-dialog>

        <el-dialog :visible.sync="showDetail" v-if="user" title="DETAIL USER">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="label">Nama</td>
                        <td class="value">{{user.name}}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Kelamin</td>
                        <td class="value">{{user.jenis_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN'}}</td>
                    </tr>
                    <tr>
                        <td class="label">Tempat/Tanggal Lahir</td>
                        <td class="value">{{user.tempat_lahir}}/{{user.tanggal_lahir | readableDate}}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat</td>
                        <td class="value">{{user.alamat}}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat Email</td>
                        <td class="value">{{user.email}}</td>
                    </tr>
                    <tr>
                        <td class="label">No. HP</td>
                        <td class="value">{{user.nomor_hp}}</td>
                    </tr>
                    <tr>
                        <td class="label">NIP</td>
                        <td class="value">{{user.nip}}</td>
                    </tr>
                    <tr>
                        <td class="label">Department</td>
                        <td class="value">{{user.department.nama}}</td>
                    </tr>
                    <tr>
                        <td class="label">Level</td>
                        <td class="value">{{roles[user.role]}}</td>
                    </tr>
                    <tr>
                        <td class="label">Nomor Kartu</td>
                        <td class="value">{{user.nomor_kartu}}</td>
                    </tr>
                    <tr>
                        <td class="label">Masa Aktif Kartu</td>
                        <td class="value">
                            {{user.masa_aktif_kartu | readableDate}}
                            <el-tag size="small" class="rounded" type="danger" effect="dark" v-if="user.expired">EXPIRED</el-tag>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Plat Nomor</td>
                        <td class="value">{{user.plat_nomor}}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td class="value">
                            <el-tag size="small" class="rounded" :type="user.status ? 'success' : 'error'" effect="dark">{{user.status ? 'AKTIF' : 'NONAKTIF'}}</el-tag>
                        </td>
                    </tr>
                </tbody>
            </table>
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
            sort: 'name',
            order: 'ascending',
            loading: false,
            roles: ['STAFF', 'OPERATOR', 'ADMIN'],
            user: null,
            showDetail: false,
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
            axios.post('/user', this.formModel).then(r => {
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
            axios.put('/user/' + this.formModel.id, this.formModel).then(r => {
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
                axios.delete('/user/' + id).then(r => {
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
            axios.get('/user', {params: params}).then(r => {
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
        }
    },
    mounted() {
        this.$store.commit('getDepartmentList')
        this.requestData();
    }
}
</script>


<style lang="scss" scoped>
td.label {
    font-weight: bold;
    width:150px;
    border-right:2px solid #ddd;
}
</style>
