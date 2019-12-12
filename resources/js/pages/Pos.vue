<template>
    <div id="gate-out-app">
        <el-row :gutter="20">
            <el-col :span="12">
                <el-card>
                    <el-row :gutter="10" style="margin-bottom:10px;">
                        <el-col :span="10">
                            <div class="label-big">NO. KARCIS</div>
                        </el-col>
                        <el-col :span="14">
                            <input id="nomor-barcode" autocomplete="off" @keyup.enter="checkTicket" type="text" placeholder="NO. KARCIS" v-model="formModel.nomor_barcode" class="my-input">
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

                    <button :disabled="!formModel.id" id="btn-open-gate" @keydown.enter="submit" class="my-big-btn" @click="submit">BUKA GATE</button>

                    <el-row :gutter="10">
                        <el-col :span="12">
                            <button id="btn-karcis-hilang" @keydown.enter="formKarcisHilang = true" class="my-big-btn" @click="formKarcisHilang = true">KARCIS HILANG</button>
                        </el-col>
                        <el-col :span="12">
                            <button id="btn-buka-manual" @keydown.enter="formBukaManual = true" class="my-big-btn" @click="formBukaManual = true">BUKA MANUAL</button>
                        </el-col>
                    </el-row>
                </el-card>
                <br>
                <el-card class="text-center" v-if="showUserInfo">
                    <el-avatar icon="el-icon-user-solid" :size="100"></el-avatar>
                    <div style="font-size:20px;margin-bottom:5px;margin-top:10px;font-weight:bold;">{{user.name}}</div>
                    <div style="font-size:16px;"> NIP: {{user.nip}}<br> Dept. : {{user.department.nama}}</div>
                    <div style="margin:10px 0 20px 0;">
                        <el-tag effect="dark" type="success" style="font-size:20px;"> {{user.plat_nomor}} </el-tag>
                    </div>
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

        <FormBukaManual :show="formBukaManual" @close-form="formBukaManual = false" @open-gate="openGate" />
        <FormKarcisHilang :show="formKarcisHilang" @close-form="formKarcisHilang = false" @open-gate="openGate" />
    </div>
</template>

<script>
import FormBukaManual from '../components/FormBukaManual'
import FormKarcisHilang from '../components/FormKarcisHilang'

export default {
    components: { FormBukaManual, FormKarcisHilang },
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
            gateOut: null,
            formBukaManual: false,
            formKarcisHilang: false,
            showUserInfo: false,
            user: {},
            getQueueInterval: null
        }
    },
    methods: {
        // plat nomor hanya khusus staff
        checkPlate() {
            // cari user yag aktif saja
            if (!!this.formModel.id) this.submit()
            const params = { plat_nomor: this.formModel.plat_nomor, status: 1 }
            axios.get('/user/search', { params }).then(r => {
                const user = r.data;
                this.user = user;
                this.showUserInfo = true;

                if (user.expired) {
                    this.$alert('Kartu telah habis masa berlaku', 'Perhatian', {
                        type: 'warning',
                        center: true,
                        roundButton: true,
                        confirmButtonText: 'OK',
                    })
                    return false
                }

                if (!user.expired && user.expired_in <= 5) {
                    this.$alert('Kartu akan habis masa berlaku dalam ' + user.expired_in + ' hari', 'Perhatian', {
                        type: 'warning',
                        center: true,
                        roundButton: true,
                        confirmButtonText: 'OK',
                    })
                }

                return user;
            }).then(user => {
                if (user) {
                    const params = { nomor_kartu: user.nomor_kartu }
                    // ambil transaksi terkahir, kalau ga ada maka otomatis create baru
                    axios.get('accessLog/search', { params }).then(r => {
                        this.formModel = r.data
                        this.snapshot_in = r.data.snapshot_in
                        this.takeSnapshot()
                        document.getElementById('btn-open-gate').focus()
                    }).catch(e => console.log(e))
                }
            }).catch(e => {
                if (e.response.status == 404) {
                    this.$message({
                        message: e.response.data.message,
                        type: 'error',
                        showClose: true
                    })
                }
            })
        },
        // check tiket hanya khusus tamu
        checkTicket() {
            if (!!this.formModel.id) this.submit()
            const now = moment().format('YYYY-MM-DD HH:mm:ss')
            const params = { nomor_barcode: this.formModel.nomor_barcode }
            axios.get('/accessLog/search', { params: params }).then(r => {
                this.formModel = r.data
                this.snapshot_in = r.data.snapshot_in
                this.formModel.time_out = now
                this.takeSnapshot()
                document.getElementById('btn-open-gate').focus()
            }).catch(e => {
                this.$message({
                    message: e.response.data.message,
                    type: 'error',
                    showClose: true,
                })
            })
        },
        resetForm() {
            this.formModel = { nomor_barcode: '' }
            this.snapshot_in = ''
            this.snapshot_out = ''
            this.showUserInfo = false
            this.user = {}
            document.getElementById('nomor-barcode').focus()
        },
        submit() {
            this.formModel.on_queue = 0;
            this.formModel.operator = this.$store.state.user.name;
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
        takeSnapshot() {
            axios.post('/barrierGate/takeSnapshot/' + this.gateOut.id).then(r => {
                this.snapshot_out = this.formModel.snapshot_out = r.data.filename
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

            this.resetForm()
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
                    this.connectToWebSocket()
                }
            }).catch(e => console.log(e))
        },
        connectToWebSocket() {
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
        },
        getQueue() {
            if (this.formModel.id) return
            axios.get('accessLog/getQueue').then(r => {
                this.formModel = r.data;
                this.formModel.time_out = moment().format('YYYY-MM-DD HH:mm:ss')
                this.snapshot_in = r.data.snapshot_in
                this.snapshot_out = r.data.snapshot_out

                if (r.data.is_staff) {
                    this.user = r.data.user
                    this.showUserInfo = true
                }

                document.getElementById('btn-open-gate').focus()
            }).catch(e => console.log(e))
        }
    },
    mounted() {
        this.getSetting()
        this.getGate()
        document.getElementById('btn-open-gate').focus()
        this.getQueueInterval = setInterval(this.getQueue, 2000)

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
        clearInterval(this.getQueueInterval)
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
