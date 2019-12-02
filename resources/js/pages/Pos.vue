<template>
    <div id="gate-out-app">
        <el-row :gutter="20">
            <el-col :span="12">
                <el-card style="height:calc(100vh - 105px)">
                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">NO. KARCIS/KARTU</div>
                        </el-col>
                        <el-col :span="14">
                            <input id="nomor-barcode" autocomplete="off" @keyup.enter="checkTicket" type="text" placeholder="NO. KARCIS/KARTU" v-model="formModel.nomor_barcode" class="my-input">
                        </el-col>
                    </el-row>

                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">NO. PLAT</div>
                        </el-col>
                        <el-col :span="14">
                            <input id="plat-nomor" autocomplete="off" @keyup.enter="checkPlate" type="text" placeholder="NO. PLAT" v-model="formModel.plat_nomor" class="my-input">
                        </el-col>
                    </el-row>

                    <button id="submit-btn" @keydown.enter="openGate" class="my-big-btn" @click="openGate">BUKA GATE</button>
                    <button id="submit-btn" @keydown.enter="submit" class="my-big-btn" @click="submit">KARCIS HILANG</button>
                    <button id="submit-btn" @keydown.enter="submit" class="my-big-btn" @click="submit">BUKA MANUAL</button>

                </el-card>
            </el-col>
            <el-col :span="12">
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
            formModel: { nomor_barcode: '' },
            formErrors: {},
            snapshot_in: null,
            snapshot_out: null,
            parkingGateList: [],
            vehicleTypeList: [],
            setting: {},
            ws: null,
            gateOut: null
        }
    },
    methods: {
        checkPlate() {
            let params = { plat_nomor: this.formModel.plat_nomor }
            axios.get('/user/search', { params: params }).then(r => {
                this.formModel.is_staff = 1
                document.getElementById('submit-btn').focus()
                // TODO: tampilkan info expiry, tampilkan info member
            }).catch(e => {
                this.formModel.is_staff = 0
                if (e.response.status == 404) {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    })
                }
            })
        },
        checkTicket() {
            const now = moment().format('YYYY-MM-DD HH:mm:ss')
            const params = { nomor_barcode: this.formModel.nomor_barcode }
            axios.get('/accessLog/search', { params: params }).then(r => {
                if (r.data.is_staff) {
                    if (r.data.user.expired) {
                        this.$alert('Kartu telah habis masa berlaku', 'Perhatian', {
                            type: 'warning',
                            center: true,
                            roundButton: true,
                            confirmButtonText: 'OK',
                            confirmButtonClass: 'bg-red',
                        })
                        return
                    }

                    if (!r.data.user.expired && r.data.user.expired_in <= 5) {
                        this.$alert('Kartu akan habis masa berlaku dalam ' + r.data.member.expired_in + ' hari', 'Perhatian', {
                            type: 'warning',
                            center: true,
                            roundButton: true,
                            confirmButtonText: 'OK',
                            confirmButtonClass: 'bg-red',
                        })
                    }

                }

                this.snapshot_in = r.data.snapshot_in
                this.formModel.id = r.data.id
                this.formModel.time_in = r.data.time_in
                this.formModel.is_staff = r.data.is_staff
                this.formModel.time_out = now

                document.getElementById('plat-nomor').focus()
            }).then(() => {
                this.takeSnapshot(this.formModel.id)
            }).catch(e => {
                this.$message({
                    message: e.response.data.message,
                    type: 'error',
                    showClose: true,
                })
            })
        },
        resetForm() {
            let default_vehicle = this.vehicleTypeList.find(v => v.is_default == 1)
            this.formModel.plat_nomor = this.setting.default_plat_nomor
            this.formModel.nomor_barcode = ''
            this.formModel.time_out = ''
            this.formModel.time_in = ''
            this.snapshot_in = ''
            this.snapshot_out = ''

            document.getElementById('nomor-barcode').focus()
        },
        submit() {
            if (!!this.formModel.id) {
                this.update()
            } else {
                this.store()
            }
        },
        store() {
            axios.post('/accessLogs', this.formModel).then(r => {
                this.takeSnapshot(r.data.id)
                this.openGate()
            }).catch(e => {
                this.$message({
                    message: 'DATA GAGAL DISIMPAN',
                    type: 'error',
                    showClose: true
                })
            })
        },
        update() {
            axios.put('/accessLogs/' + this.formModel.id, this.formModel).then(r => {
                this.openGate()
            }).catch(e => {
                this.$message({
                    message: 'DATA GAGAL DISIMPAN',
                    type: 'error',
                    showClose: true
                })
            })
        },
        takeSnapshot(id) {
            axios.post('/barrierGate/takeSnapshot/' + this.gateOut.id).then(r => {
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
            this.ws.send([
                'open',
                this.gateOut.serial_device,
                this.gateOut.serial_baudrate,
                this.gateOut.cmd_open,
                this.gateOut.cmd_close
            ].join(';'));
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
        },
        getGate() {
            axios.get('barrierGate/getList').then(r => {
                const gateOut = r.data.find(d => d.jenis == 'OUT');
                if (!gateOut) {
                    this.$message({
                        message: 'GATE OUT BELUM DISETTING',
                        type: 'error',
                        showClose: true,
                    })
                } else {
                    this.gateOut = gateOut;
                }
            }).catch(e => console.log(e))
        },
        connectToWebSocket(gate) {
            this.ws = new WebSocket("ws://localhost:5678/");
            this.ws.onerror = (event) => {
                console.log(event)
                this.$message({
                    message: 'KONEKSI KE CONTROLLER GATE KELUAR GAGAL',
                    type: 'error',
                    showClose: true,
                    duration: 10000
                })
            }
            this.ws.onmessage = (event) => {
                let data = JSON.parse(event.data)
                this.$message({
                    message: data.message,
                    type: data.status ? 'success' : 'error',
                    showClose: true
                })

                this.resetForm()
            }
        }
    },
    mounted() {
        this.getSetting()
        this.connectToWebSocket()
        this.$store.commit('getBarrierGateList');
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
    },
    destroyed() {
        this.ws.close(1000, 'Leaving app')
    }


}
</script>

<style lang="scss" scoped>
.block {
    background-color: #eee;
    height: calc(50vh - 73px);
}

.my-input {
    border: 2px solid #00adcc;
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

.label-big {
    box-sizing: border-box;
    background-color: #00adcc;
    color: #fff;
    padding-left: 15px;
    font-size: 20px;
    height: 43px;
    line-height: 43px;
}

.my-big-btn {
    box-sizing: border-box;
    width: 100%;
    border: none;
    font-size: 20px;
    height: 43px;
    line-height: 43px;
    background-color: #005c73;
    color: #fff;
    border-radius: 4px;
    margin-top: 10px;
}

.my-big-btn:focus, .my-big-btn:hover {
    background-color: #cd0000;
}

.text-center {
    text-align: center;
}
</style>
