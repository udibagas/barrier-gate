<template>
    <el-dialog
    :visible.sync="show"
    :before-close="(done) => { closeForm() }"
    title="FORM KARCIS HILANG"
    width="500px"
    v-loading="loading">

        <el-alert type="error" title="ERROR"
            :description="error.message + '\n' + error.file + ':' + error.line"
            v-show="error.message"
            style="margin-bottom:15px;">
        </el-alert>

        <el-form label-width="170px" label-position="left">
            <el-form-item label="Nama" :class="formErrors.nama ? 'is-error' : ''">
                <el-input placeholder="Nama" v-model="formModel.nama"></el-input>
                <div class="el-form-item__error" v-if="formErrors.nama">{{formErrors.nama[0]}}</div>
            </el-form-item>

            <el-form-item label="Jenis Kartu Identitas" :class="formErrors.jenis_kartu_identitas ? 'is-error' : ''">
                <el-input placeholder="SIM, KTP, Passport, dsb" v-model="formModel.jenis_kartu_identitas"></el-input>
                <div class="el-form-item__error" v-if="formErrors.jenis_kartu_identitas">{{formErrors.jenis_kartu_identitas[0]}}</div>
            </el-form-item>

            <!-- <el-form-item label="Nomor Kartu Identitas" :class="formErrors.nomor_kartu_identitas ? 'is-error' : ''">
                <el-input placeholder="Nomor Kartu Identitas" v-model="formModel.nomor_kartu_identitas"></el-input>
                <div class="el-form-item__error" v-if="formErrors.nomor_kartu_identitas">{{formErrors.nomor_kartu_identitas[0]}}</div>
            </el-form-item> -->

            <el-form-item label="Nomor HP" :class="formErrors.no_hp ? 'is-error' : ''">
                <el-input placeholder="Nomor HP" v-model="formModel.no_hp"></el-input>
                <div class="el-form-item__error" v-if="formErrors.no_hp">{{formErrors.no_hp[0]}}</div>
            </el-form-item>

            <el-form-item label="Nomor Plat" :class="formErrors.no_plat ? 'is-error' : ''">
                <el-input placeholder="Nomor Plat" v-model="formModel.no_plat"></el-input>
                <div class="el-form-item__error" v-if="formErrors.no_plat">{{formErrors.no_plat[0]}}</div>
            </el-form-item>

            <el-form-item label="Alamat" :class="formErrors.alamat ? 'is-error' : ''">
                <el-input type="textarea" rows="3" placeholder="Alamat" v-model="formModel.alamat"></el-input>
                <div class="el-form-item__error" v-if="formErrors.alamat">{{formErrors.alamat[0]}}</div>
            </el-form-item>

            <el-form-item v-show="!!formModel.id" label="Status" :class="formErrors.status ? 'is-error' : ''">
                <el-select placeholder="Status" v-model="formModel.status" style="width:100%">
                    <el-option v-for="(s, i) in ['BELUM DIAMBIL', 'SUDAH DIAMBIL']" :key="i" :value="i" :label="s"></el-option>
                </el-select>
                <div class="el-form-item__error" v-if="formErrors.nama">{{formErrors.nama[0]}}</div>
            </el-form-item>
        </el-form>

        <span slot="footer" class="dialog-footer">
            <el-button type="primary" icon="el-icon-success" @click="store">SIMPAN</el-button>
            <el-button type="info" icon="el-icon-error" @click="closeForm">BATAL</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: ['show'],
    data() {
        return {
            formErrors: {},
            error: {},
            formModel: {},
            loading: false
        }
    },
    methods: {
        closeForm() {
            this.formErrors = {}
            this.error = {}
            this.formModel = {}
            this.$emit('close-form');
            setTimeout(() => { document.getElementById('nomor-barcode').focus() }, 100)
        },
        store() {
            this.loading = true;
            axios.post('/karcisHilang', this.formModel).then(r => {
                this.$emit('open-gate');
                this.closeForm();
                this.$message({
                    message: 'Data berhasil disimpan.',
                    type: 'success',
                    showClose: true
                });
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
    }
}
</script>
