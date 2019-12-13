<template>
    <el-dialog title="PROFIL SAYA" v-loading="loading" :visible="show" :before-close="(done) => { $emit('close') }" fullscreen>
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
                        <el-input disabled placeholder="NIP" v-model="formModel.nip"></el-input>
                        <div class="el-form-item__error" v-if="formErrors.nip">{{formErrors.nip[0]}}</div>
                    </el-form-item>

                    <el-form-item label="Departemen" :class="formErrors.department_id ? 'is-error' : ''">
                        <el-select disabled v-model="formModel.department_id" placeholder="Departemen" style="width:100%">
                            <el-option v-for="(d, i) in $store.state.departmentList" :value="d.id" :label="d.nama" :key="i"> </el-option>
                        </el-select>
                        <div class="el-form-item__error" v-if="formErrors.department_id">{{formErrors.department_id[0]}}</div>
                    </el-form-item>

                    <el-form-item label="Level" :class="formErrors.role ? 'is-error' : ''">
                        <el-select disabled v-model="formModel.role" placeholder="Level" style="width:100%">
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
                        <el-input disabled placeholder="Nomor Kartu" v-model="formModel.nomor_kartu"></el-input>
                        <div class="el-form-item__error" v-if="formErrors.nomor_kartu">{{formErrors.nomor_kartu[0]}}</div>
                    </el-form-item>

                    <el-form-item label="Masa Aktif Kartu" :class="formErrors.masa_aktif_kartu ? 'is-error' : ''">
                        <el-date-picker
                        disabled
                        style="width:100%"
                        format="dd-MMM-yyyy" value-format="yyyy-MM-dd"
                        placeholder="Masa Aktif Kartu" v-model="formModel.masa_aktif_kartu">
                        </el-date-picker>
                        <div class="el-form-item__error" v-if="formErrors.masa_aktif_kartu">{{formErrors.masa_aktif_kartu[0]}}</div>
                    </el-form-item>

                    <el-form-item label="Plat Nomor" :class="formErrors.plat_nomor ? 'is-error' : ''">
                        <el-input disabled placeholder="Plat Nomor" v-model="formModel.plat_nomor"></el-input>
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
                        <el-tag effect="dark" :type="formModel.status ? 'success' : 'info'" style="width:100px;text-align:center;">{{!!formModel.status ? 'Aktif' : 'Nonaktif'}}</el-tag>
                        <div class="el-form-item__error" v-if="formErrors.status">{{formErrors.status[0]}}</div>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <span slot="footer">
            <el-button type="primary" @click="save" icon="el-icon-success">SIMPAN</el-button>
            <el-button type="info" @click="$emit('close')" icon="el-icon-error">TUTUP</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: ['show'],
    data() {
        return {
            formModel: JSON.parse(window.localStorage.getItem('user')),
            loading: false,
            formErrors: {},
            error: {},
        }
    },
    methods: {
        save() {
            this.loading = true
            axios.put('/user/' + this.formModel.id, this.formModel).then(r => {
                this.$message({
                    message: 'Data berhasil diupdate',
                    type: 'success',
                    showClose: true
                })
                window.localStorage.setItem('user', r.data)
                this.$store.state.user = r.data
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
        }
    },
    mounted() {
        this.$store.commit('getDepartmentList')
    }
}
</script>
