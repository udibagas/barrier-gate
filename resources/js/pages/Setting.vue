<template>
    <div>
        <el-page-header @back="$emit('back')" content="SETTING"> </el-page-header>
        <br>
        <el-tabs type="card">
            <el-tab-pane lazy label="Global Setting" v-loading="loading">
                <el-card>
                    <el-form label-position="left" label-width="270px">
                        <el-form-item label="Nama Lokasi" :class="formErrors.nama_lokasi ? 'is-error' : ''">
                            <el-input placeholder="Nama Lokasi" v-model="formModel.nama_lokasi"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.nama_lokasi">{{formErrors.nama_lokasi[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Alamat Lokasi">
                            <el-input placeholder="Alamat Lokasi" type="textarea" rows="3" v-model="formModel.alamat_lokasi"></el-input>
                        </el-form-item>

                        <el-form-item label="Info Tambahan Tiket">
                            <el-input placeholder="Info Tambahan Tiket" type="textarea" rows="3" v-model="formModel.info_tambahan_tiket"></el-input>
                        </el-form-item>

                        <el-form-item label="Plat Nomor Default" :class="formErrors.plat_nomor_default ? 'is-error' : ''">
                            <el-input placeholder="Plat Nomor Default" v-model="formModel.plat_nomor_default"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.plat_nomor_default">{{formErrors.plat_nomor_default[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Mode Buka Gate Untuk Staff" :class="formErrors.staff_buka_otomatis ? 'is-error' : ''">
                            <el-select placeholder="Mode Buka Gate Untuk Staff" v-model="formModel.staff_buka_otomatis" style="width:100%">
                                <el-option v-for="(l, i) in ['Ketik Plat Nomor (Buka oleh operator)', 'Tempel Kartu (Otomatis)']" :key="i" :value="i" :label="l"></el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.staff_buka_otomatis">{{formErrors.staff_buka_otomatis[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Mode Buka Gate Untuk Pengunjung" :class="formErrors.pengunjung_buka_otomatis ? 'is-error' : ''">
                            <el-select placeholder="Mode Buka Gate Untuk Pengunjung" v-model="formModel.pengunjung_buka_otomatis" style="width:100%">
                                <el-option v-for="(l, i) in ['Ketik Plat Nomor (Buka oleh operator)', 'Tempel Tiket (Otomatis)']" :key="i" :value="i" :label="l"></el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.pengunjung_buka_otomatis">{{formErrors.pengunjung_buka_otomatis[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Hapus Snapshot dalam x hari" :class="formErrors.hapus_snapshot_dalam_hari ? 'is-error' : ''">
                            <el-input type="number" placeholder="Hapus Snapshot dalam x hari (0 untuk hapus manual)" v-model="formModel.hapus_snapshot_dalam_hari"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.hapus_snapshot_dalam_hari">{{formErrors.hapus_snapshot_dalam_hari[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Hapus Log dalam x hari" :class="formErrors.hapus_log_dalam_hari ? 'is-error' : ''">
                            <el-input type="number" placeholder="Hapus Transaksi dalam x hari" v-model="formModel.hapus_log_dalam_hari"></el-input>
                            <div class="el-form-item__error" v-if="formErrors.hapus_log_dalam_hari">{{formErrors.hapus_log_dalam_hari[0]}}</div>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" style="width:100%" @click="() => { !!formModel.id ? update() : store(); }" icon="el-icon-success">SIMPAN</el-button>
                </el-card>
            </el-tab-pane>
            <el-tab-pane lazy label="Gate">
                <BarrierGate />
            </el-tab-pane>
            <!-- <el-tab-pane lazy label="Backup &amp; Restore Database">
                <Backup />
            </el-tab-pane> -->
        </el-tabs>
    </div>
</template>

<script>
import BarrierGate from './BarrierGate'
import Backup from './Backup'

export default {
    components: { BarrierGate, Backup },
    data() {
        return {
            formModel: {},
            formErrors: {},
            loading: false
        }
    },
    methods: {
        requestData() {
            axios.get('/setting').then(r => {
                this.formModel = r.data
            }).catch(e => {
                this.$message({
                    message: e.response.data.message,
                    type: 'error',
                    showClose: true
                })
            })
        },
        store() {
            this.loading = true;
            axios.post('/setting', this.formModel).then(r => {
                this.$message({
                    message: 'Data berhasil disimpan.',
                    type: 'success',
                    showClose: true
                });
                this.requestData();
            }).catch(e => {
                if (e.response.status == 422) {
                    this.formErrors = e.response.data.errors;
                } else {
                    this.formErrors = {}
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    })
                }
            }).finally(() => {
                this.loading = false
            })
        },
        update() {
            this.loading = true;
            axios.put('/setting/' + this.formModel.id, this.formModel).then(r => {
                this.$message({
                    message: 'Data berhasil disimpan.',
                    type: 'success',
                    showClose: true
                });
                this.requestData()
            }).catch(e => {
                if (e.response.status == 422) {
                    this.formErrors = e.response.data.errors;
                } else {
                    this.formErrors = {}
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    })
                }
            }).finally(() => {
                this.loading = false
            })
        },
    },
    mounted() {
        this.requestData()
    }
}
</script>
