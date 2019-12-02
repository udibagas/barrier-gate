<template>
    <div>
        <el-form :inline="true" style="text-align:right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-button @click="openForm({printer_type: 'local'})" type="primary"><i class="el-icon-plus"></i> TAMBAH GATE</el-button>
            </el-form-item>
            <el-form-item style="margin-right:0;">
                <el-input v-model="keyword" placeholder="Search" prefix-icon="el-icon-search" :clearable="true" @change="(v) => { keyword = v; requestData(); }">
                    <el-button @click="() => { page = 1; keyword = ''; requestData(); }" slot="append" icon="el-icon-refresh"></el-button>
                </el-input>
            </el-form-item>
        </el-form>

        <el-table :data="tableData.data" stripe
        :default-sort = "{prop: sort, order: order}"
        height="calc(100vh - 345px)"
        v-loading="loading"
        @sort-change="sortChange">
            <el-table-column prop="jenis" label="Jenis" min-width="80px" show-overflow-tooltip></el-table-column>
            <el-table-column prop="nama" label="Nama" min-width="100px" show-overflow-tooltip></el-table-column>
            <el-table-column prop="controller_ip_address" label="Controller" min-width="150px" show-overflow-tooltip>
                <template slot-scope="scope">
                    {{scope.row.controller_ip_address}}:{{scope.row.controller_port}}
                </template>
            </el-table-column>
            <el-table-column label="Printer" min-width="120px" show-overflow-tooltip>
                <template slot-scope="scope">
                    {{scope.row.printer_type == 'network' ? scope.row.printer_ip_address : scope.row.printer_device }}
                </template>
            </el-table-column>
            <el-table-column prop="camera_snapshot_url" label="URL Snapshot Kamera" min-width="180px" show-overflow-tooltip></el-table-column>
            <el-table-column label="User/Pass Kamera" min-width="150px" show-overflow-tooltip>
                <template slot-scope="scope">
                    {{scope.row.camera_username }}/{{ scope.row.camera_password }}
                </template>
            </el-table-column>
            <el-table-column prop="printer_status" label="Status Printer" min-width="120px" align="center" header-align="center">
                <template slot-scope="scope">
                    <el-tag size="small" effect="dark" style="border-radius:13px;width:100%;" :type="scope.row.printer_status ? 'success' : 'info'">{{scope.row.printer_status ? 'Aktif' : 'Nonaktif'}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="camera_status" label="Status Kamera" min-width="120px" align="center" header-align="center">
                <template slot-scope="scope">
                    <el-tag size="small" effect="dark" style="border-radius:13px;width:100%;" :type="scope.row.camera_status ? 'success' : 'info'">{{scope.row.camera_status ? 'Aktif' : 'Nonaktif'}}</el-tag>
                </template>
            </el-table-column>

            <el-table-column fixed="right" width="40px">
                <template slot-scope="scope">
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            <i class="el-icon-more"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item icon="el-icon-camera" @click.native.prevent="testDevice('testCamera', scope.row.id)">Test Kamera</el-dropdown-item>
                            <el-dropdown-item icon="el-icon-printer" @click.native.prevent="testDevice('testPrinter', scope.row.id)">Test Printer</el-dropdown-item>
                            <el-dropdown-item icon="el-icon-minus" @click.native.prevent="testDevice('openGate', scope.row.id)">Test Gate</el-dropdown-item>
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

        <el-dialog top="60px" :visible.sync="showForm" :title="!!formModel.id ? 'EDIT GATE' : 'TAMBAH GATE'" width="950px" v-loading="loading" :close-on-click-modal="false">
            <el-alert type="error" title="ERROR"
                :description="error.message + '\n' + error.file + ':' + error.line"
                v-show="error.message"
                style="margin-bottom:15px;">
            </el-alert>

            <el-form label-width="180px" label-position="left">
                <el-row :gutter="30">
                    <el-col :span="12">
                        <el-form-item label="Jenis" :class="formErrors.jenis ? 'is-error' : ''">
                            <el-select v-model="formModel.jenis" placeholder="Jenis" style="width:100%">
                                <el-option v-for="(t, i) in ['IN', 'OUT']" :value="t" :label="t" :key="i"></el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.jenis">{{formErrors.jenis[0]}}</div>
                        </el-form-item>
                        <el-form-item label="Nama" :class="formErrors.nama ? 'is-error' : ''">
                            <el-input placeholder="Nama" v-model="formModel.nama"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.nama">{{formErrors.nama[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Controller IP" :class="formErrors.controller_ip_address ? 'is-error' : ''">
                            <el-input placeholder="Alamat IP" v-model="formModel.controller_ip_address" style="width:60%"></el-input>
                            <el-input type="number" placeholder="Port" v-model="formModel.controller_port" style="width:38%;float:right;clear:right;"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.controller_ip_address">{{formErrors.controller_ip_address[0]}}</div>
                            <div class="el-form-item__error" v-if="formErrors.controller_port">{{formErrors.controller_port[0]}}</div>
                        </el-form-item>

                        <el-form-item v-show="formModel.jenis == 'OUT'" label="Serial" :class="formErrors.serial_device || formErrors.serial_baudrate ? 'is-error' : ''">
                            <el-input placeholder="Device" v-model="formModel.serial_device" style="width:49%"></el-input>
                            <el-input placeholder="Baudrate" v-model="formModel.serial_baudrate" style="width:49%;float:right;clear:right"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.serial_device">{{formErrors.serial_device[0]}}</div>
                            <div class="el-form-item__error" v-if="formErrors.serial_baudrate">{{formErrors.serial_baudrate[0]}}</div>
                        </el-form-item>

                        <el-form-item v-show="formModel.jenis == 'OUT'" label="Perintah" :class="formErrors.cmd_open || formErrors.cmd_close ? 'is-error' : ''">
                            <el-input placeholder="Buka" v-model="formModel.cmd_open" style="width:49%"></el-input>
                            <el-input placeholder="Tutup" v-model="formModel.cmd_close" style="width:49%;float:right;clear:right"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.cmd_open">{{formErrors.cmd_open[0]}}</div>
                            <div class="el-form-item__error" v-if="formErrors.cmd_close">{{formErrors.cmd_close[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Jenis Printer" :class="formErrors.printer_type || formErrors.printer_device || formErrors.printer_ip_address ? 'is-error' : ''">
                            <el-select v-model="formModel.printer_type" placeholder="Jenis Printer" style="width:40%">
                                <el-option v-for="(t, i) in ['local', 'network']" :value="t" :label="t" :key="i"></el-option>
                            </el-select>
                            <el-input v-show="formModel.printer_type == 'local'" placeholder="Device Printer" v-model="formModel.printer_device" style="width:58%;float:right;clear:right;"></el-input>
                            <el-input v-show="formModel.printer_type == 'network'" placeholder="Alamat IP Printer" v-model="formModel.printer_ip_address" style="width:58%;float:right;clear:right;"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.printer_type">{{formErrors.printer_type[0]}}</div>
                            <div class="el-form-item__error" v-if="formErrors.printer_device">{{formErrors.printer_device[0]}}</div>
                            <div class="el-form-item__error" v-if="formErrors.printer_ip_address">{{formErrors.printer_ip_address[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Status Printer" :class="formErrors.camera_status ? 'is-error' : ''">
                            <el-switch
                            :active-value="1"
                            :inactive-value="0"
                            v-model="formModel.printer_status"
                            active-color="#13ce66">
                            </el-switch>
                            <el-tag effect="dark" :type="formModel.printer_status ? 'success' : 'info'" size="small" style="margin-left:10px;border-radius:13px;">{{!!formModel.printer_status ? 'Aktif' : 'Nonaktif'}}</el-tag>
                            <div class="el-form-item__error" v-if="formErrors.printer_status">{{formErrors.printer_status[0]}}</div>
                        </el-form-item>
                    </el-col>

                    <el-col :span="12">
                        <el-form-item label="URL Snapshot Kamera" :class="formErrors.camera_snapshot_url ? 'is-error' : ''">
                            <el-input placeholder="URL Snapshot Kamera" v-model="formModel.camera_snapshot_url"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.camera_snapshot_url">{{formErrors.camera_snapshot_url[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Username Kamera" :class="formErrors.camera_username ? 'is-error' : ''">
                            <el-input placeholder="Username Kamera" v-model="formModel.camera_username"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.camera_username">{{formErrors.camera_username[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Password Kamera" :class="formErrors.camera_password ? 'is-error' : ''">
                            <el-input placeholder="Password Kamera" v-model="formModel.camera_password"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.camera_password">{{formErrors.camera_password[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Status Kamera" :class="formErrors.camera_status ? 'is-error' : ''">
                            <el-switch
                            :active-value="1"
                            :inactive-value="0"
                            v-model="formModel.camera_status"
                            active-color="#13ce66">
                            </el-switch>
                            <el-tag effect="dark" :type="formModel.camera_status ? 'success' : 'info'" size="small" style="margin-left:10px;border-radius:13px;">{{!!formModel.camera_status ? 'Aktif' : 'Nonaktif'}}</el-tag>
                            <div class="el-form-item__error" v-if="formErrors.camera_status">{{formErrors.camera_status[0]}}</div>
                        </el-form-item>
                    </el-col>
                </el-row>

            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button icon="el-icon-success" type="primary" @click="() => !!formModel.id ? update() : store()">SIMPAN</el-button>
                <el-button icon="el-icon-error" type="info" @click="showForm = false">BATAL</el-button>
            </span>
        </el-dialog>

        <el-dialog title="SNAPSHOT KAMERA" center :visible.sync="snapshotPreview">
            <img :src="snapshot" alt="" style="width:100%">
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
            snapshot: '',
            snapshotPreview: false
        }
    },
    methods: {
        testDevice(action, id) {
            axios.post('/barrierGate/' + action + '/' + id).then(r => {
                this.$message({
                    message: r.data.message,
                    type: 'success',
                    showClose: true
                });

                if (action == 'testCamera') {
                    this.snapshot = 'data:image/jpeg;base64,' + r.data.snapshot
                    this.snapshotPreview = true
                }
            }).catch(e => {
                this.$message({
                    message: e.response.data.message,
                    type: 'error',
                    showClose: true
                });
            })
        },
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
            axios.post('/barrierGate', this.formModel).then(r => {
                this.showForm = false;
                this.$message({
                    message: 'Data BERHASIL disimpan.',
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
            axios.put('/barrierGate/' + this.formModel.id, this.formModel).then(r => {
                this.showForm = false
                this.$message({
                    message: 'Data BERHASIL disimpan.',
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
                axios.delete('/barrierGate/' + id).then(r => {
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
            axios.get('/barrierGate', {params: params}).then(r => {
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
        this.requestData();
    }
}
</script>

<style scoped>
</style>
