<template>
    <el-container style="background-image:url('images/bg.jpg');" class="main-container">
        <el-main>
            <div class="form-container">
                <img src="images/logo.png" alt="" style="width:100px;">
                <h2>{{appName}}</h2>
                <el-form id="login-form">
                    <el-form-item>
                        <el-input v-model="email" placeholder="Email/Username"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-input type="password" v-model="password" placeholder="Password"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="login" style="width:100%">LOGIN</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-main>
    </el-container>
</template>
6
<script>
import moment from 'moment'

export default {
    data() {
        return {
            appName: APP_NAME,
            email: '',
            password: '',
            year: moment().format('YYYY')
        }
    },
    methods: {
        login() {
            if (!this.email || !this.password) {
                return
            }

            let data = {
                email: this.email,
                password: this.password
            }

            axios.post('login', data).then(r => {
                window.localStorage.setItem('user', JSON.stringify(r.data.user))
                window.localStorage.setItem('token', r.data.token)
                window.axios.defaults.headers.common['Authorization'] = 'bearer ' + r.data.token;
                this.$store.state.user = r.data.user
                this.$store.state.token = r.data.token
                this.$store.state.is_logged_in = true
                this.$router.push('home')
            }).catch(e => {
                this.$message({
                    message: e.response.data.message || e.response.message,
                    type: 'error',
                    showClose: true
                })
            })
        }
    },
    mounted() {
        document.getElementById('login-form').onkeypress = (e) => {
            if (e.key == 'Enter') {
                this.login()
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.main-container {
    height: 100vh;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

.form-container {
    width:350px;
    margin: 80px auto 0;
    text-align:center;
    padding:20px;
    border-radius: 4px;
    background-color: rgba(255, 255, 255, .7)
}
</style>
