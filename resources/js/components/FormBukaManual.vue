<template>
    <el-dialog :visible.sync="show"
    :close-on-click-modal="false"
    title="FORM BUKA MANUAL"
    width="500px"
    :before-close="(done) => { closeForm() }"
    v-loading="loading">

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
            axios.post('/bukaManual', this.formModel).then(r => {
                const gate = this.$store.state.barrierGateList.find(g => g.id == this.formModel.barrier_gate_id)
                if (gate) this.$emit('open-gate');
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
    },
    mounted() {
        this.$store.commit('getBarrierGateList')
    }
}
</script>
