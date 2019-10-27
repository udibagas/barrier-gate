import Vue from 'vue'
import Vuex from 'vuex'
import { Message } from 'element-ui';

Vue.use(Vuex)

let currentUser = JSON.parse(window.localStorage.getItem('user'))

export default new Vuex.Store({
    state: {
        base_url: BASE_URL,
        appName: APP_NAME,
        user: currentUser || {},
        token: window.localStorage.getItem('token'),
        is_logged_in: !!currentUser,
        barrierGateList: [],
        departmentList: [],
        userList: [],
        navigationList: [],
        setting: {}
    },
    mutations: {
        getDepartmentList(state) {
            axios.get('/department/getList').then(r => state.departmentList = r.data)
                .catch(e => console.log(e))
        },
        getUserList(state) {
            axios.get('/parkingUser/getList').then(r => state.userList = r.data)
                .catch(e => console.log(e))
        },
        getBarrierGateList(state) {
            axios.get('/barrierGate/getList').then(r => state.barrierGateList = r.data)
                .catch(e => console.log(e))
        },
        getNavigationList(state) {
            axios.get('/getNavigation').then(r => state.navigationList = r.data)
                .catch(e => console.log(e))
        },
        getSetting(state) {
            axios.get('/setting').then(r => state.setting = r.data)
                .catch(e => {
                    Message({
                        message: 'BELUM ADA SETTING',
                        type: 'error',
                        showClose: true,
                        duration: 10000
                    })
                })
        }
    }
})
