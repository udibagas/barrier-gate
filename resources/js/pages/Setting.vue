<template>
    <div>
        <el-page-header @back="$emit('back')" content="SETTING"> </el-page-header>
        <el-divider></el-divider>

        <el-tabs type="card">
            <el-tab-pane lazy label="Global Setting" v-loading="loading">
                <el-card style="height:calc(100vh - 235px);overflow:auto;">
                    <el-form label-position="left" label-width="250px">
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

                        <!-- <el-form-item label="Mode Transaksi Member" :class="formErrors.must_checkout ? 'is-error' : ''">
                            <el-select placeholder="Mode Transaksi Member" v-model="formModel.must_checkout" style="width:100%">
                                <el-option v-for="(l, i) in ['Tidak harus check out', 'Harus check out']" :key="i" :value="i" :label="l"></el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.must_checkout">{{formErrors.must_checkout[0]}}</div>
                        </el-form-item>

                        <el-form-item label="Mode Buka Gate Untuk Member" :class="formErrors.member_auto_open ? 'is-error' : ''">
                            <el-select placeholder="Mode Buka Gate Untuk Member" v-model="formModel.member_auto_open" style="width:100%">
                                <el-option v-for="(l, i) in ['Ketik Plat Nomor (Buka oleh operator)', 'Tempel Kartu (Otomatis)']" :key="i" :value="i" :label="l"></el-option>
                            </el-select>
                            <div class="el-form-item__error" v-if="formErrors.member_auto_open">{{formErrors.member_auto_open[0]}}</div>
                        </el-form-item> -->

                        <el-form-item>
                            <el-button type="primary" @click="() => { !!formModel.id ? update() : store(); }" icon="el-icon-success">SIMPAN</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
            </el-tab-pane>
            <el-tab-pane lazy label="Gate">
                <BarrierGate />
            </el-tab-pane>
            <el-tab-pane lazy label="Department">
                <Department />
            </el-tab-pane>
            <el-tab-pane lazy label="User">
                <User />
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import BarrierGate from './BarrierGate'
import Department from './Department'
import User from './User'

export default {
    components: { BarrierGate, Department, User },
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
