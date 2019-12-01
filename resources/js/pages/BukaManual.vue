<template>
    <div>
        <el-page-header @back="$emit('back')" content="LOG BUKA MANUAL"> </el-page-header>
        <el-divider></el-divider>

        <el-form :inline="true" style="text-align:right" @submit.native.prevent="() => { return }">
            <el-form-item>
                <el-button icon="el-icon-plus" @click="openForm({})" type="primary">BUKA MANUAL</el-button>
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
            <el-table-column prop="created_at" label="Waktu" sortable="custom" width="150px">
                <template slot-scope="scope">
                    {{scope.row.created_at | readableDateTime }}
                </template>
            </el-table-column>
            <el-table-column prop="gate" label="Gate" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="user" label="User" sortable="custom" width="150px"></el-table-column>
            <el-table-column prop="alasan" label="Alasan" sortable="custom"></el-table-column>
            <!-- <el-table-column fixed="right" width="40px">
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
            </el-table-column> -->
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

        <el-dialog :visible.sync="showForm" :title="!!formModel.id ? 'EDIT DATA BUKA MANUAL' : 'FORM BUKA MANUAL'" width="500px" v-loading="loading" :close-on-click-modal="false">
            <el-alert type="error" title="ERROR"
                :description="error.message + '\n' + error.file + ':' + error.line"
                v-show="error.message"
                style="margin-bottom:15px;">
            </el-alert>

            <el-form label-width="150px" label-position="left">
                <el-form-item label="Gate" :class="formErrors.barrier_gate_id ? 'is-error' : ''">
                    <el-select placeholder="Gate" v-model="formModel.barrier_gate_id" style="width:100%">
                        <el-option v-for="g in $store.state.barrierGateList" :key="g.id" :value="g.id" :label="g.nama"></el-option>
                    </el-select>
                    <div class="el-form-item__error" v-if="formErrors.nama">{{formErrors.nama[0]}}</div>
                </el-form-item>

                <el-form-item label="Alasan" :class="formErrors.nama ? 'is-error' : ''">
                    <el-input type="textarea" rows="3" placeholder="Alasan" v-model="formModel.alasan"></el-input>
                    <div class="el-form-item__error" v-if="formErrors.alasan">{{formErrors.alasan[0]}}</div>
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
        store() {
            this.loading = true;
            axios.post('/bukaManual', this.formModel).then(r => {
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
            axios.put('/bukaManual/' + this.formModel.id, this.formModel).then(r => {
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
                axios.delete('/bukaManual/' + id).then(r => {
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
        bukaManual() {
            this.$confirm('Aksi ini akan dicatat oleh sistem. Anda yakin?', 'Peringatan', { type: 'warning'} ).then(() => {
                this.formModelManualOpen.parking_gate_id = this.formModel.gate_out_id
                axios.post('/bukaManual', this.formModel).then(r => {
                    this.openGate();
                    this.showManualOpenForm = false
                    this.formModelManualOpen = {}
                }).catch(e => {
                    if (e.response.status == 422) {
                        this.formErrors = e.response.data.errors;
                    } else {
                        this.$message({
                            message: e.response.data.message,
                            type: 'error',
                            showClose: true,
                        })
                    }
                })
            }).catch(() => console.log(e))
        },
        openGate() {
            const gate = this.$store.state.barrierGateList.find(g => g.id == this.formModel.barrier_gate_id);

            if (!gate) {
                this.$message({
                    message: 'MOHON PILIH GATE OUT',
                    type: 'error',
                    showClose: true
                })
                return
            }

            // kalau ga ada ip berarti langsung nancep
            if (!gate.controller_ip_address) {
                axios.post('/parkingGate/openGate/' + this.formModel.gate_out_id).then(r => {
                    this.$message({
                        message: r.data.message,
                        type: 'success',
                        showClose: true
                    })
                }).catch(e => {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    })
                }).finally(() => {
                    this.resetForm()
                })
            } else {
                this.ws.send([
                    'open',
                    gate.serial_device,
                    gate.serial_baudrate,
                    gate.cmd_open,
                    gate.cmd_close
                ].join(';'));
            }
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
            axios.get('/bukaManual', {params: params}).then(r => {
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
        this.$store.commit('getBarrierGateList');
    }
}
</script>

<style scoped>

</style>

