<template>
    <div id="gate-out-app">
        <el-row :gutter="20">
            <el-col :span="14">
                <el-card style="height:calc(100vh - 105px)">
                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">GATE IN</div>
                        </el-col>
                        <el-col :span="14">
                            <select :disabled="formModel.nomor_barcode.toLowerCase() != 'xxxxx'" v-model="formModel.gate_in_id" id="gate-in" class="my-input">
                                <option v-for="g in parkingGateList" :value="g.id" :key="g.id">{{g.name}}</option>
                            </select>
                        </el-col>
                    </el-row>

                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">GATE OUT</div>
                        </el-col>
                        <el-col :span="14">
                            <select v-model="formModel.gate_out_id" id="gate-out" class="my-input">
                                <option v-for="g in parkingGateList.filter(g => g.type == 'OUT')" :value="g.id" :key="g.id">{{g.name}}</option>
                            </select>
                        </el-col>
                    </el-row>

                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">[-] NO. PLAT</div>
                        </el-col>
                        <el-col :span="14">
                            <input id="plat-nomor" autocomplete="off" @keyup.enter="checkPlate" type="text" placeholder="NO. PLAT" v-model="formModel.plat_nomor" class="my-input">
                        </el-col>
                    </el-row>

                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">[+] NO. TIKET/KARTU</div>
                        </el-col>
                        <el-col :span="14">
                            <input id="nomor-barcode" autocomplete="off" @keyup.enter="checkTicket" type="text" placeholder="NO. TIKET/KARTU" v-model="formModel.nomor_barcode" class="my-input">
                        </el-col>
                    </el-row>

                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">[*] JENIS KENDARAAN</div>
                        </el-col>
                        <el-col :span="14">
                            <select @change="setFare" v-model="formModel.vehicle_type" id="vehicle-type" class="my-input">
                                <option v-for="g in vehicleTypeList" :value="g.name" :key="g.id">{{g.shortcut_key}} - {{g.name}}</option>
                            </select>
                            <!-- <div style="padding:3px 10px;font-weight:bold;" class="bg-yellow">
                                {{vehicleTypeList.map(vt => vt.shortcut_key + ' = ' + vt.name).join(', ')}}
                            </div> -->
                        </el-col>
                    </el-row>

                    <button id="submit-btn" @keyup.right="nextBtn" @keyup.down="nextBtn" @keydown.enter="submit(false)" class="my-big-btn" @click="submit(false)">BUKA GATE</button>

                </el-card>
            </el-col>
            <el-col :span="10">
                <el-card style="height:calc(100vh - 105px)">
                    <div class="block">
                        <el-image :src="snapshot_in" style="width: 100%; height: 100%" fit="cover">
                            <div slot="error" class="el-image__error">
                                <h1>SNAPSHOT IN</h1>
                            </div>
                        </el-image>
                    </div>
                    <div class="block">
                        <el-image :src="snapshot_out" style="width: 100%; height: 100%" fit="cover">
                            <div slot="error" class="el-image__error">
                                <h1>SNAPSHOT OUT</h1>
                            </div>
                        </el-image>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script>
export default {
    data() {
        return {
            showTicketLostForm: false,
            formModel: { nomor_barcode: '' },
            formErrors: {},
            snapshot_in: null,
            snapshot_out: null,
            parkingGateList: [],
            vehicleTypeList: [],
            setting: {}
        }
    },
    methods: {
        nextBtn() {
            document.getElementById('submit-btn1').focus()
        },
        prevBtn() {
            document.getElementById('submit-btn').focus()
        },
        checkPlate() {
            let params = { plat_nomor: this.formModel.plat_nomor }
            axios.get('/parkingMember/search', { params: params }).then(r => {
                this.formModel.is_member = 1
                this.formModel.fare = 0
            }).catch(e => {
                this.formModel.is_member = 0
            }).finally(() => {
                document.getElementById('nomor-barcode').focus()
                this.$forceUpdate()
            })
        },
        checkTicket() {
            let now = moment().format('YYYY-MM-DD HH:mm:ss')

            if (this.formModel.nomor_barcode.toLowerCase() == 'xxxxx') {
                this.formModel.time_out = now;
                document.getElementById('vehicle-type').focus()
            } else {
                let params = { nomor_barcode: this.formModel.nomor_barcode }
                axios.get('/parkingTransaction/search', { params: params }).then(r => {
                    if (r.data.is_member) {
                        if (r.data.member.expired) {
                            this.$alert('Kartu telah habis masa berlaku', 'Perhatian', {
                                type: 'warning',
                                center: true,
                                roundButton: true,
                                confirmButtonText: 'OK',
                                confirmButtonClass: 'bg-red',
                            })
                            return
                        }

                        if (!r.data.member.expired && r.data.member.expired_in <= 5) {
                            this.$alert('Kartu akan habis masa berlaku dalam ' + r.data.member.expired_in + ' hari', 'Perhatian', {
                                type: 'warning',
                                center: true,
                                roundButton: true,
                                confirmButtonText: 'OK',
                                confirmButtonClass: 'bg-red',
                            })
                        }

                        let vehicle = r.data.member.vehicles.find(v => v.plat_nomor == this.formModel.plat_nomor)

                        if (!vehicle) {
                            this.$alert('Nomor plat tidak cocok dengan kartu. Nomor plat yang terdaftar adalah '
                            + r.data.member.vehicles.map(v => v.plat_nomor).join(', '), 'Notifikasi', {
                                type: 'warning',
                                center: true,
                                roundButton: true,
                                confirmButtonText: 'SAYA TELAH MEMBACA NOTIFIKASI INI',
                                confirmButtonClass: 'bg-red'
                            })
                        }

                        this.formModel.fare = 0
                    }

                    this.snapshot_in = r.data.snapshot_in
                    this.formModel.id = r.data.id
                    this.formModel.gate_in_id = r.data.gate_in_id
                    this.formModel.time_in = r.data.time_in
                    this.formModel.is_member = r.data.is_member
                    this.formModel.time_out = now
                    this.$forceUpdate()
                    this.setDuration()

                    // member auto open sesuai setingan
                    if (r.data.is_member && !r.data.member.expired && this.setting.member_auto_open) {
                        this.update(false)
                    }

                    document.getElementById('vehicle-type').focus()
                }).then(() => {
                    this.takeSnapshot(this.formModel.id)
                }).catch(e => {
                    console.log(e)
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true,
                    })
                })
            }
        },
        resetForm() {
            let default_vehicle = this.vehicleTypeList.find(v => v.is_default == 1)
            this.formModel.gate_in_id = null
            this.formModel.plat_nomor = this.setting.default_plat_nomor
            this.formModel.nomor_barcode = ''
            this.formModel.time_out = ''
            this.formModel.time_in = ''
            this.formModel.duration = ''
            this.snapshot_in = ''
            this.snapshot_out = ''

            if (default_vehicle) {
                this.formModel.vehicle_type = default_vehicle.name
                this.formModel.fare = default_vehicle.tarif_flat
            } else {
                this.formModel.vehicle_type = ''
                this.formModel.fare = ''
            }

            document.getElementById('plat-nomor').focus()
        },
        submit(ticket) {
            // kalau tiket hilang harus isi time in dulu
            if (this.formModel.nomor_barcode.toLowerCase() == 'xxxxx' && !this.formModel.time_in) {
                document.getElementById('time-in').focus()
                return
            } else {
                document.getElementById('submit-btn').blur()
            }

            if (!this.formModel.gate_in_id) {
                this.$message({
                    message: 'MOHON ISI GATE IN',
                    type: 'error',
                    showClose: true,
                })
                return
            }

            if (!!this.formModel.id) {
                this.update()
            } else {
                this.store()
            }
        },
        store(ticket) {
            axios.post('/parkingTransaction', this.formModel).then(r => {
                this.takeSnapshot(r.data.id)
                if (ticket) {
                    this.printTicket(r.data.id)
                }
            }).catch(e => {
                // kecil kemungkinan
                this.$message({
                    message: 'DATA GAGAL DISIMPAN',
                    type: 'error',
                    showClose: true
                })
            }).finally(() => {
                this.openGate()
            })
        },
        update(ticket) {
            axios.put('/parkingTransaction/' + this.formModel.id, this.formModel).then(r => {
                // print tiket hanya untuk non member
                if (r.data.is_member == 0 && ticket) {
                    this.printTicket(r.data.id)
                }
            }).catch(e => {
                this.$message({
                    message: 'DATA GAGAL DISIMPAN',
                    type: 'error',
                    showClose: true
                })
            }).finally(() => {
                this.openGate()
            })
        },
        takeSnapshot(id) {
            axios.post('/parkingTransaction/takeSnapshot/' + id, { gate_out_id: this.formModel.gate_out_id }).then(r => {
                this.snapshot_out = r.data.snapshot_out
            }).catch(e => {
                this.$message({
                    message: e.response.data.message,
                    type: 'error',
                    showClose: true
                })
            })
        },
        openGate() {
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
        },
        getSetting(state) {
            axios.get('/setting').then(r => {
                this.setting = r.data
                this.formModel.plat_nomor = r.data.default_plat_nomor
            }).catch(e => {
                this.$message({
                    message: 'BELUM ADA SETTING',
                    type: 'error',
                    showClose: true,
                    duration: 10000
                })
            })
        }
    },
    mounted() {
        this.getSetting()
        document.getElementById('plat-nomor').focus()

        document.getElementById('gate-out-app').onkeydown = (e) => {
            // console.log(e.key)
            // ke field nomor plat
            if (e.key == '-') {
                e.preventDefault()
                this.resetForm()
                this.$forceUpdate()
            }

            // ke field nomor tiket
            if (e.key == '+') {
                e.preventDefault()
                this.formModel.nomor_barcode = ''
                this.formModel.time_out = ''
                document.getElementById('nomor-barcode').focus()
            }
        }
    }

}
</script>

<style lang="scss" scoped>
.block {
    background-color: #eee;
    height: calc(50vh - 73px);
}

.my-input {
    border: 2px solid #160047;
    height: 43px;
    line-height: 43px;
    font-size: 30px;
    display: block;
    width: 100%;
    padding: 0px 15px;
    box-sizing: border-box;
}

.my-input:focus, .my-input-time:focus {
    background: rgb(255, 246, 122);
}

.my-input-time {
    border: 2px solid #160047;
    height: 43px;
    line-height: 43px;
    font-size: 20px;
    display: block;
    width: 100%;
    padding: 0px 10px;
    box-sizing: border-box;
}

.label-big {
    box-sizing: border-box;
    background-color: #160047;
    color: #fff;
    padding-left: 15px;
    font-size: 20px;
    height: 43px;
    line-height: 43px;
}

.tarif-input {
    background: rgb(199, 24, 24);
    color: #fff;
}

.my-big-btn {
    box-sizing: border-box;
    width: 100%;
    border: none;
    font-size: 20px;
    height: 43px;
    line-height: 43px;
    background-color: #254ec1;
    color: #fff;
    border-radius: 4px;
    margin-top: 10px;
}

.my-big-btn:focus {
    // border: 3px dotted red;
    background-color: #cd0000;
}

.label {
    box-sizing: border-box;
    background-color: #160047;
    color: #fff;
    text-align: center;
    padding: 10px;
}

.text-center {
    text-align: center;
}
</style>
