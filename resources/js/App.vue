<template>
    <div>
        <Login v-if="!$store.state.is_logged_in" />
        <el-container v-else>
            <Profile v-if="$store.state.is_logged_in" :show="showProfile" @close="showProfile = false" />
            <el-aside width="auto">
                <div v-show="!collapse" class="brand-box">
                    <el-avatar :size="80" icon="el-icon-user"></el-avatar>
                    <br><br>
                    <div>
                        <strong>{{$store.state.user.name}}</strong><br>
                        <small>{{$store.state.user.email}}</small>
                    </div>
                </div>
                <el-menu
                :collapse="collapse"
                default-active="1"
                background-color="#005c73"
                text-color="#fff"
                class="sidebar"
                active-text-color="#f7ff00">
                    <el-menu-item v-for="(m, i) in menus" :index="(++i).toString()" :key="i" @click="$router.push(m.path)">
                        <i :class="m.icon"></i><span slot="title">{{m.label}}</span>
                    </el-menu-item>
                </el-menu>
            </el-aside>
            <el-container>
                <el-header>
                    <el-row>
                        <el-col :span="12">
                            <el-button type="text" class="btn-big text-white" @click.prevent="collapse = !collapse" :icon="collapse ? 'el-icon-s-unfold' : 'el-icon-s-fold'"></el-button>
                            <span class="brand">
                                <img src="/images/logo.png" style="height:50px;margin-right:5px;vertical-align:middle;" alt="">
                                {{appName}}
                            </span>
                        </el-col>
                        <el-col :span="12" class="text-right">
                            <el-popover v-if="notifications.length > 0" style="margin-right:20px" placement="top-start" width="250" trigger="click">
                                <el-button slot="reference" type="danger" size="mini" round icon="el-icon-bell" style="color:#fff;">{{notifications.length}}</el-button>
                                <div style="height:calc(100vh - 300px);overflow:auto;padding-right:10px;">
                                    <el-button type="danger" style="width:100%" round size="mini" @click="readAllNotification">Tandai Sudah Dibaca Semua</el-button>
                                    <div v-for="n in notifications" :key="n.id">
                                        <p style="margin-bottom:0"><strong>{{n.created_at | readableDateTime}}</strong> {{n.data.message}}</p>
                                        <el-button type="text" size="small" @click="readNotification(n.id)">Tandai sudah dibaca</el-button><br>
                                    </div>
                                </div>
                            </el-popover>

                            <el-dropdown @command="handleCommand">
                                <span class="el-dropdown-link" style="cursor:pointer">Selamat Datang, {{$store.state.user.name}}!</span>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item command="profile"><i class="el-icon-user"></i> Profil Saya</el-dropdown-item>
                                    <el-dropdown-item command="logout"><i class="el-icon-arrow-right"></i> Keluar</el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </el-col>
                    </el-row>
                </el-header>
                <el-main style="padding:20px;height: calc(100vh - 60px);overflow:auto;">
                    <el-collapse-transition>
                        <router-view @back="goBack"></router-view>
                    </el-collapse-transition>
                </el-main>
            </el-container>
        </el-container>
    </div>
</template>

<script>
import Login from './pages/Login'
import Profile from './pages/Profile'

export default {
    components: { Login, Profile },
    computed: {
        menus() {
            return this.$store.state.navigationList
        }
    },
    data() {
        return {
            collapse: false,
            appName: APP_NAME,
            showProfile: false,
            loginForm: !this.$store.state.is_logged_in,
            notifications: []
        }
    },
    methods: {
        goBack() {
            window.history.back();
        },
        handleCommand(command) {
            if (command === 'logout') {
                this.logout()
            }

            if(command === 'profile') {
                this.showProfile = true
            }
        },
        logout() {
            axios.post('/logout').then(r => {
                window.localStorage.removeItem('user')
                window.localStorage.removeItem('token')
                this.$store.state.user = {}
                this.$store.state.token = ''
                this.$store.state.is_logged_in = false
            })
        },
        getNotification() {
            if (!this.$store.state.is_logged_in) {
                return
            }

            const params = { type: 'App\\Notifications\\GateNotification' }
            axios.get('/notification/unread', { params }).then(r => {
                // kalau jumlah lebih dari sebelumnya tampilkan record terakhir
                if (r.data.length > this.notifications.length) {
                    this.$notify.warning({
                        title: 'Notifikasi',
                        message: '[' + r.data[0].created_at + '] ' + r.data[0].data.message
                    })
                }
                this.notifications = r.data;
            }).catch(e => console.log(e))
        },
        readNotification(id) {
            axios.put('/notification/markAsRead/' + id)
                .then(r => console.log(r))
                .catch(e => console.log(e))
        },
        readAllNotification(id) {
            axios.put('/notification/markAllAsRead')
                .then(r => this.notifications = [])
                .catch(e => console.log(e))
        }
    },
    mounted() {
        // window.onbeforeunload = (e) => {
        //     window.localStorage.removeItem('user')
        //     window.localStorage.removeItem('token')
        // }

        this.$store.commit('getNavigationList')
        setInterval(this.getNotification, 3000)
    }
}
</script>

<style lang="css" scoped>
.brand {
    font-size: 22px;
    margin-left: 10px;
}

.brand-box {
    height: 150px;
    background-color: #005c73;
    text-align: center;
    color: #fff;
    margin-top: 20px;
}

.btn-big {
    font-size: 22px;
}

.el-header {
    background-color: #00A2BB;
    color: #fff;
    line-height: 60px;
}

.sidebar {
    background-color: #005c73;
    border-color: #005c73;
    height: calc(100vh - 170px);
    overflow: auto;
}

.sidebar:not(.el-menu--collapse) {
    width: 200px;
}

.el-aside {
    height: 100vh;
    background-color: #005c73;
}

.el-main {
    background-color: #FFF;
}

.el-dropdown-link {
    color: #fff;
}
</style>
